
(function( $ ) {
	'use strict';

	// Keep the previously set postcode so the form is only refreshed when it is changed.
	var previous_billing_postcode_value = null;

	// Attempt to remember what the user was focusing on after refreshing the form.
	var nextActiveElementId = null;

	// When the page loads, save the currently set value of the postcode.
	$( function() {
		previous_billing_postcode_value = $('#billing_postcode').val();
	});

	$('body').on('focusout', '#billing_postcode', function (e) {

		nextActiveElementId = e.relatedTarget.id;

		var updated_billing_postcode_value = $('#billing_postcode').val();

		if( previous_billing_postcode_value === updated_billing_postcode_value ) {
			return;
		}
		previous_billing_postcode_value = updated_billing_postcode_value;

		// Display loading indicator while an account is checked for.
		$('.woocommerce-billing-fields__field-wrapper').block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});

		// Fire the ajax for update_order_review.
		$(document.body).trigger('update_checkout');
	});

	$( document.body ).bind( 'updated_checkout', function( data ) {

		// Remove the loading indicator.
		$('.woocommerce-billing-fields__field-wrapper').unblock();

		previous_billing_postcode_value = $('#billing_postcode').val();

		// Without this, the country dropdown etc. are no longer rendered correctly.
		$('select').select2();

		// Refocus the element that took focus to cause the blur.
		if( nextActiveElementId ) {
			document.getElementById( nextActiveElementId ).focus();
			nextActiveElementId = null;
		}
	});

})( jQuery );
