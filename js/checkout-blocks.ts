import { subscribe, select, dispatch } from '@wordpress/data';
import { CART_STORE_KEY } from '@woocommerce/block-data';
import { extensionCartUpdate, isPostcode as isValidPostcode } from '@woocommerce/blocks-checkout';

var lastPostcode = {
    billingAddress: null,
    shippingAddress: null,
};
var isPostcodeAutofillUpdating = false

subscribe( () => {
    // If the action has already begun, wait for it to finish, or it can enter a loop of updating between postcodes.
    if( isPostcodeAutofillUpdating ) {
        return;
    }

    const { isBeforeProcessing } = select( 'wc/store/checkout' );

    if ( ! isBeforeProcessing ) {
        return;
    }

    const store = select( CART_STORE_KEY );
    const cartData = store.getCartData();

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

    extensionCartUpdate({
        namespace: 'bh-wc-postcode-address-autofill',
        data: addressData
    }).then(function ( cart: any ) {
        // The server filled the city/state on the cart address, but the block checkout's
        // controlled address inputs do not adopt the cart response on their own (and their
        // own customer-data push would re-clear them). Push the resolved city/state — read
        // from the returned cart response (Store API snake_case), not the store which gets
        // clobbered — into the editable address store so the visible fields update and persist.
        if ( didUpdateShipping && cart?.shipping_address ) {
            dispatch( CART_STORE_KEY ).setShippingAddress( {
                city: cart.shipping_address.city,
                state: cart.shipping_address.state,
            } );
        }
        if ( didUpdateBilling && cart?.billing_address ) {
            dispatch( CART_STORE_KEY ).setBillingAddress( {
                city: cart.billing_address.city,
                state: cart.billing_address.state,
            } );
        }
        isPostcodeAutofillUpdating = false;
    });

});
