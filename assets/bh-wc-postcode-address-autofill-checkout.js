
(function( $ ) {
	'use strict';

	// Keep the previously set postcode so the form is only refreshed when it is changed.
	var previous_values = {
		billing_postcode: null,
		shipping_postcode: null,
	};

	// Attempt to remember what the user was focusing on after refreshing the form.
	var nextActiveElementId = null;

	// When the page loads, save the currently set value of the postcode.
	$( function() {
		previous_values.billing_postcode = $('#billing_postcode').val();
		previous_values.shipping_postcode = $('#shipping_postcode').val();
	});

	$('body').on('focusout', '#billing_postcode', { previous_values: previous_values }, on_update_postcode );
	$('body').on('focusout', '#shipping_postcode', { previous_values: previous_values }, on_update_postcode );

	function on_update_postcode(e) {

		// Always "billing_postcode" or "shipping_postcode".
		let target = e.target.id;

		nextActiveElementId = e.relatedTarget?.id;

		var updated_value = $('#'+target).val();

		if( previous_values.target === updated_value ) {
			return;
		}
		previous_values.target = updated_value;

		if( '' === updated_value ) {
			return;
		}

		// Display loading indicator while an account is checked for.
		$('.woocommerce-billing-fields__field-wrapper').block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});
		$('.woocommerce-shipping-fields__field-wrapper').block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});

		// Fire the ajax for update_order_review.
		$(document.body).trigger('update_checkout');
	}

	$( document.body ).bind( 'updated_checkout', function( data ) {

		// Remove the loading indicator.
		$('.woocommerce-billing-fields__field-wrapper').unblock();
		$('.woocommerce-shipping-fields__field-wrapper').unblock();

		previous_values.billing_postcode = $('#billing_postcode').val();
		previous_values.shipping_postcode = $('#shipping_postcode').val();

		// Without this, the country dropdown etc. are no longer rendered correctly.
		$('select').select2();

		// Refocus the element that took focus to cause the blur.
		if( nextActiveElementId ) {
			document.getElementById( nextActiveElementId ).focus();
			nextActiveElementId = null;
		}
	});

})( jQuery );
