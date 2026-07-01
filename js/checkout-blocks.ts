import { subscribe, select, dispatch } from '@wordpress/data';
import { CART_STORE_KEY } from '@woocommerce/block-data';
import { extensionCartUpdate, isPostcode as isValidPostcode } from '@woocommerce/blocks-checkout';

var lastPostcode = {
    billingAddress: null,
    shippingAddress: null,
};
var isPostcodeAutofillUpdating = false

// While an autofill request is in flight, the user may edit the city/state. WooCommerce's
// response handling (receiveCart) overwrites the editable address in the store with the
// server's values, so the store cannot be trusted to still hold the user's edit by the time
// the request resolves. Record the address on every tick while the request is pending so we
// can tell what the user last entered and preserve it.
type AddressSnapshot = { city: string, state: string, postcode: string };
var inFlightShippingSnapshots: AddressSnapshot[] = [];
var inFlightBillingSnapshots: AddressSnapshot[] = [];

/**
 * Decide which value a field should end up with once the autofill response arrives.
 *
 * If the user changed the field after the request was sent, keep their value; otherwise use
 * the server's suggestion. `snapshots` are the values seen while the request was in flight;
 * the most recent one that differs from the server's value is the user's latest edit (the
 * final snapshot is typically the server value applied by receiveCart).
 */
function resolveFieldValue(
    snapshots: AddressSnapshot[],
    field: 'city' | 'state' | 'postcode',
    sentValue: string,
    serverValue: string
): string {
    for ( let i = snapshots.length - 1; i >= 0; i-- ) {
        const value = snapshots[ i ][ field ];
        if ( value !== serverValue ) {
            // The user edited the field after the request was sent — keep their value.
            // If it merely matches what was there when we sent, use the server value.
            return value !== sentValue ? value : serverValue;
        }
    }
    return serverValue;
}

subscribe( () => {

    const store = select( CART_STORE_KEY );
    const cartData = store.getCartData();

    // If a request is already in flight, record the address (the user may be editing it) and
    // wait for it to finish, or it can enter a loop of updating between postcodes.
    if( isPostcodeAutofillUpdating ) {
        inFlightShippingSnapshots.push( {
            city: cartData.shippingAddress.city,
            state: cartData.shippingAddress.state,
            postcode: cartData.shippingAddress.postcode,
        } );
        inFlightBillingSnapshots.push( {
            city: cartData.billingAddress.city,
            state: cartData.billingAddress.state,
            postcode: cartData.billingAddress.postcode,
        } );
        return;
    }

    const { isBeforeProcessing } = select( 'wc/store/checkout' );

    if ( ! isBeforeProcessing ) {
        return;
    }

    isPostcodeAutofillUpdating = true;

    var addressData = {
        billing: {
            country: cartData.billingAddress.country,
            postcode: cartData.billingAddress.postcode,
        },
        shipping: {
            country: cartData.shippingAddress.country,
            postcode: cartData.shippingAddress.postcode,
        },
    };

    // If either are empty, there is nothing to do.
    if( !cartData.billingAddress.country || !cartData.billingAddress.postcode ) {
        delete addressData.billing;
    }
    if( !cartData.shippingAddress.country || !cartData.shippingAddress.postcode ) {
        delete addressData.shipping;
    }

    // If the postcode has not changed, there is nothing to do.
    if( cartData.billingAddress.postcode === lastPostcode.billingAddress ) {
        delete addressData.billing;
    } else {
        lastPostcode.billingAddress = cartData.billingAddress.postcode;
    }
    if( cartData.shippingAddress.postcode === lastPostcode.shippingAddress ) {
        delete addressData.shipping;
    } else {
        lastPostcode.shippingAddress = cartData.shippingAddress.postcode;
    }

    // If it's not a valid postcode, there is nothing to do.
    if ( !isValidPostcode( {
        postcode: cartData.billingAddress.postcode,
        country: cartData.billingAddress.country,
    } ) ) {
        delete addressData.billing;
    }
    // If it's not a valid postcode, there is nothing to do.
    if ( !isValidPostcode( {
        postcode: cartData.shippingAddress.postcode,
        country: cartData.shippingAddress.country,
    } ) ) {
        delete addressData.shipping;
    }

    if( 0 === Object.keys(addressData).length ) {
        isPostcodeAutofillUpdating = false;
        return;
    }

    const didUpdateBilling = !! addressData.billing;
    const didUpdateShipping = !! addressData.shipping;

    // Capture the city/state as they are when the request is sent, and start recording edits
    // the user makes while the request is in flight.
    const sentShipping = {
        city: cartData.shippingAddress.city,
        state: cartData.shippingAddress.state,
        postcode: cartData.shippingAddress.postcode,
    };
    const sentBilling = {
        city: cartData.billingAddress.city,
        state: cartData.billingAddress.state,
        postcode: cartData.billingAddress.postcode,
    };
    inFlightShippingSnapshots = [];
    inFlightBillingSnapshots = [];

    extensionCartUpdate({
        namespace: 'bh-wc-postcode-address-autofill',
        data: addressData
    }).then(function ( cart: any ) {
        // The server filled the city/state on the cart address, but the block checkout's
        // controlled address inputs do not adopt the cart response on their own. Push the
        // resolved city/state (from the response, Store API snake_case) into the editable
        // address store — but:
        //  - if the user changed the postcode while the request was in flight, the response is
        //    stale (it describes a postcode no longer entered), so do not apply it; and
        //  - otherwise preserve any city/state the user changed while it was in flight (see
        //    resolveFieldValue).
        const shippingPostcode = resolveFieldValue( inFlightShippingSnapshots, 'postcode', sentShipping.postcode, cart?.shipping_address?.postcode ?? sentShipping.postcode );
        if ( didUpdateShipping && cart?.shipping_address && shippingPostcode === sentShipping.postcode ) {
            dispatch( CART_STORE_KEY ).setShippingAddress( {
                city: resolveFieldValue( inFlightShippingSnapshots, 'city', sentShipping.city, cart.shipping_address.city ),
                state: resolveFieldValue( inFlightShippingSnapshots, 'state', sentShipping.state, cart.shipping_address.state ),
            } );
        }
        const billingPostcode = resolveFieldValue( inFlightBillingSnapshots, 'postcode', sentBilling.postcode, cart?.billing_address?.postcode ?? sentBilling.postcode );
        if ( didUpdateBilling && cart?.billing_address && billingPostcode === sentBilling.postcode ) {
            dispatch( CART_STORE_KEY ).setBillingAddress( {
                city: resolveFieldValue( inFlightBillingSnapshots, 'city', sentBilling.city, cart.billing_address.city ),
                state: resolveFieldValue( inFlightBillingSnapshots, 'state', sentBilling.state, cart.billing_address.state ),
            } );
        }
        isPostcodeAutofillUpdating = false;
    });

});
