import { subscribe, select, dispatch } from '@wordpress/data';
import { CART_STORE_KEY } from '@woocommerce/block-data';
import { extensionCartUpdate, isPostcode as isValidPostcode } from '@woocommerce/blocks-checkout';

type Address = { country: string, postcode: string, city: string, state: string };
type AddressType = 'billing' | 'shipping';

// Postcodes we have already sent a lookup for, keyed by address type + country + postcode.
// Acts as a cache so repeated/echoed values are not looked up again, and prevents the
// fill -> store change -> re-lookup loop. Intentionally unbounded: it lives only for the
// lifetime of the (short-lived) checkout page and only grows by distinct postcodes entered.
const queriedPostcodes = new Set<string>();

// Only one lookup is in flight at a time. While it is, the address is recorded on every store
// change so we know what the user actually has when the (possibly slow) response arrives — the
// store itself cannot be trusted because WooCommerce's receiveCart overwrites it with the
// response's values.
var isQuerying = false;
var inFlightSnapshots: { billing: Address[], shipping: Address[] } = { billing: [], shipping: [] };

function toAddress( address: any ): Address {
    return {
        country: address.country,
        postcode: address.postcode,
        city: address.city,
        state: address.state,
    };
}

function postcodeKey( type: AddressType, address: Address ): string {
    return type + '|' + address.country + '|' + address.postcode;
}

function isLookupNeeded( type: AddressType, address: Address ): boolean {
    return !! address.country
        && !! address.postcode
        && isValidPostcode( { postcode: address.postcode, country: address.country } )
        && ! queriedPostcodes.has( postcodeKey( type, address ) );
}

/**
 * Only autofill while the checkout is idle. Once the customer starts placing the order
 * (before/processing/after processing, or complete) we must not mutate their address.
 */
function isCheckoutIdle(): boolean {
    const checkout: any = select( 'wc/store/checkout' );
    return typeof checkout.isIdle === 'function' ? checkout.isIdle() : true;
}

/**
 * Decide the value a field should end up with once the autofill response arrives.
 *
 * `snapshots` are the values seen while the request was in flight. The most recent one that
 * differs from the server's value is the user's latest edit (the final snapshot is typically
 * the server value applied by receiveCart). If the user changed the field after the request
 * was sent, keep their value; otherwise use the server's suggestion.
 */
function resolveFieldValue(
    snapshots: Address[],
    field: keyof Address,
    sentValue: string,
    serverValue: string
): string {
    for ( let i = snapshots.length - 1; i >= 0; i-- ) {
        const value = snapshots[ i ][ field ];
        if ( value !== serverValue ) {
            return value !== sentValue ? value : serverValue;
        }
    }
    return serverValue;
}

/**
 * The value the user actually has for a field now, reconstructed from the in-flight snapshots.
 *
 * Unlike resolveFieldValue this never returns the server's suggestion: the most recent snapshot
 * that differs from the server value is the user's own value; if the field was never touched it
 * is whatever it was when the request was sent. Used to build the baseline for a follow-up
 * lookup, so the server's value from one request does not leak into the next.
 */
function currentUserValue(
    snapshots: Address[],
    field: keyof Address,
    sentValue: string,
    serverValue: string
): string {
    for ( let i = snapshots.length - 1; i >= 0; i-- ) {
        const value = snapshots[ i ][ field ];
        if ( value !== serverValue ) {
            return value;
        }
    }
    return sentValue;
}

/** The address the user actually has now, reconstructed from the in-flight snapshots. */
function userAddressFromSnapshots( snapshots: Address[], sent: Address, server: Address ): Address {
    return {
        country: currentUserValue( snapshots, 'country', sent.country, server.country ),
        postcode: currentUserValue( snapshots, 'postcode', sent.postcode, server.postcode ),
        city: currentUserValue( snapshots, 'city', sent.city, server.city ),
        state: currentUserValue( snapshots, 'state', sent.state, server.state ),
    };
}

/**
 * Apply the server's city/state for one address type, unless the postcode changed while the
 * request was in flight (in which case the response is stale) or the user edited the field.
 */
function applyAutofill( type: AddressType, snapshots: Address[], sent: Address, server: Address ): void {
    // If the postcode changed while the request was in flight the response is stale — the city
    // it describes is for a postcode that is no longer entered.
    if ( resolveFieldValue( snapshots, 'postcode', sent.postcode, server.postcode ) !== sent.postcode ) {
        return;
    }

    const setter = type === 'shipping' ? 'setShippingAddress' : 'setBillingAddress';
    dispatch( CART_STORE_KEY )[ setter ]( {
        city: resolveFieldValue( snapshots, 'city', sent.city, server.city ),
        state: resolveFieldValue( snapshots, 'state', sent.state, server.state ),
    } );
}

/**
 * Look up the city/state for any address whose postcode we have not queried yet.
 *
 * `addresses` is the user's current address for each type. It comes from the cart store on a
 * normal store change, or is reconstructed from the snapshots when re-evaluating after a
 * response (so a postcode the user corrected to while a request was in flight is picked up).
 */
function evaluate( addresses: { billing: Address, shipping: Address } ): void {
    if ( isQuerying ) {
        return;
    }

    var addressData: any = {};
    if ( isLookupNeeded( 'billing', addresses.billing ) ) {
        addressData.billing = { country: addresses.billing.country, postcode: addresses.billing.postcode };
    }
    if ( isLookupNeeded( 'shipping', addresses.shipping ) ) {
        addressData.shipping = { country: addresses.shipping.country, postcode: addresses.shipping.postcode };
    }

    const didUpdateBilling = !! addressData.billing;
    const didUpdateShipping = !! addressData.shipping;

    if ( ! didUpdateBilling && ! didUpdateShipping ) {
        return;
    }

    isQuerying = true;
    inFlightSnapshots = { billing: [], shipping: [] };

    const sentBilling = addresses.billing;
    const sentShipping = addresses.shipping;

    const billingKey = didUpdateBilling ? postcodeKey( 'billing', addresses.billing ) : null;
    const shippingKey = didUpdateShipping ? postcodeKey( 'shipping', addresses.shipping ) : null;
    if ( billingKey ) {
        queriedPostcodes.add( billingKey );
    }
    if ( shippingKey ) {
        queriedPostcodes.add( shippingKey );
    }

    extensionCartUpdate( {
        namespace: 'bh-wc-postcode-address-autofill',
        data: addressData,
    } ).then( function ( cart: any ) {
        // The server filled the city/state on the cart address, but the block checkout's
        // controlled address inputs do not adopt the cart response on their own, so push the
        // resolved values into the editable address store (see applyAutofill). Skip this if the
        // customer has since started placing the order — we must not mutate the address then.
        const serverShipping = cart?.shipping_address ? toAddress( cart.shipping_address ) : sentShipping;
        const serverBilling = cart?.billing_address ? toAddress( cart.billing_address ) : sentBilling;

        if ( isCheckoutIdle() ) {
            if ( didUpdateShipping && cart?.shipping_address ) {
                applyAutofill( 'shipping', inFlightSnapshots.shipping, sentShipping, serverShipping );
            }
            if ( didUpdateBilling && cart?.billing_address ) {
                applyAutofill( 'billing', inFlightSnapshots.billing, sentBilling, serverBilling );
            }
        }

        isQuerying = false;

        // Re-evaluate against what the user actually has now — reconstructed from the snapshots
        // because receiveCart has overwritten the store with this response's values. This looks
        // up a postcode the user corrected to while this request was in flight.
        evaluate( {
            billing: userAddressFromSnapshots( inFlightSnapshots.billing, sentBilling, serverBilling ),
            shipping: userAddressFromSnapshots( inFlightSnapshots.shipping, sentShipping, serverShipping ),
        } );
    } ).catch( function () {
        // The request failed. Release the lock and un-cache the postcodes so the lookup can be
        // retried on the next change, rather than silently stopping autofill for the session.
        if ( billingKey ) {
            queriedPostcodes.delete( billingKey );
        }
        if ( shippingKey ) {
            queriedPostcodes.delete( shippingKey );
        }
        isQuerying = false;
    } );
}

subscribe( () => {

    // Do not autofill once the customer has started placing the order.
    if ( ! isCheckoutIdle() ) {
        return;
    }

    const cartData = select( CART_STORE_KEY ).getCartData();
    const addresses = {
        billing: toAddress( cartData.billingAddress ),
        shipping: toAddress( cartData.shippingAddress ),
    };

    // While a lookup is in flight, record the address (the user may be editing it) so the
    // response handler can tell what the user currently has.
    if ( isQuerying ) {
        inFlightSnapshots.billing.push( addresses.billing );
        inFlightSnapshots.shipping.push( addresses.shipping );
        return;
    }

    evaluate( addresses );
} );
