import { subscribe, select } from '@wordpress/data';
import { CART_STORE_KEY } from '@woocommerce/block-data';
import { extensionCartUpdate, isPostcode as isValidPostcode } from '@woocommerce/blocks-checkout';

var lastPostcode = null;
var isPostcodeAutofillUpdating = false

subscribe( () => {
    // If the action has already begun, wait for it to finish, or it can enter a loop of updating between postcodes.
    if( isPostcodeAutofillUpdating ) {
        return;
    }

    const { isBeforeProcessing } = select( 'wc/store/checkout' );

    if ( isBeforeProcessing ) {
        const store = select( CART_STORE_KEY );
        const cartData = store.getCartData();

        // If either are empty, there is nothing to do.
        if( !cartData.billingAddress.country || !cartData.billingAddress.postcode ) {
            return;
        }

        // If the postcode has not changed, there is nothing to do.
        if( cartData.billingAddress.postcode === lastPostcode ) {
            return;
        }
        lastPostcode = cartData.billingAddress.postcode;

        // If it's not a valid postcode, there is nothing to do.
        if ( !isValidPostcode( {
                postcode: cartData.billingAddress.postcode,
                country: cartData.billingAddress.country,
            } ) ) {
            return;
        }

        isPostcodeAutofillUpdating = true;
        extensionCartUpdate({
            namespace: 'bh-wc-postcode-address-autofill',
            data: {
                "country": cartData.billingAddress.country,
                "postcode": cartData.billingAddress.postcode,
            },
        }).then(function(){
            isPostcodeAutofillUpdating = false;
        })
    }
});
