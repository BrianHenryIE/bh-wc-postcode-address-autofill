<?php
/**
 * Handle functions related to the WooCommerce checkout.
 *
 * @package brianhenryie/bh-wc-checkout-address-suggestions
 */

namespace BrianHenryIE\WC_Checkout_Address_Suggestions\WooCommerce;

use BrianHenryIE\WC_Checkout_Address_Suggestions\API_Interface;
use BrianHenryIE\WC_Checkout_Address_Suggestions\Settings_Interface;

/**
 * Hook onto WooCommerce checkout functions to reorder the fields and autofill the values.
 *
 * @phpstan-type WooCommerceFormField array{label:string,required:bool,class:array<string>,autocomplete:string,priority:int,type?:string,placeholder?:string,label_class?:array<string>,validate?:array<string>,country_field?:string,country?:string}
 */
class Checkout {

	/**
	 * The core plugin functions.
	 *
	 * @uses API_Interface::get_state_city_for_postcode()
	 */
	protected API_Interface $api;

	/**
	 * Plugin settings for assets URL and caching version.
	 *
	 * @uses Settings_Interface::get_plugin_basename()
	 * @uses Settings_Interface::get_plugin_version()
	 */
	protected Settings_Interface $settings;

	/**
	 * Constructor
	 *
	 * @param API_Interface      $api The main plugin functions.
	 * @param Settings_Interface $settings The plugin settings.
	 */
	public function __construct( API_Interface $api, Settings_Interface $settings ) {
		$this->settings = $settings;
		$this->api      = $api;
	}

	/**
	 * Register the JavaScript files used for WooCommerce.
	 *
	 * This JS fires the `update_checkout` trigger when a postcode is entered.
	 *
	 * @hooked wp_enqueue_scripts
	 */
	public function enqueue_scripts(): void {

		$version = $this->settings->get_plugin_version();

		wp_enqueue_script( 'bh-wc-checkout-address-suggestions-checkout', plugin_dir_url( $this->settings->get_plugin_basename() ) . 'assets/bh-wc-checkout-address-suggestions-checkout.js', array( 'jquery' ), $version, true );

		// TODO: Also add a script containing select2 city suggestions.
	}

	/**
	 * Move the Postcode input field above the City input field.
	 *
	 * Change the billing_postcode priority value, if it has not been changed by another plugin.
	 *
	 * @see woocommerce_form_field()
	 *
	 * @param array<string,WooCommerceFormField> $fields The billing/shipping fields.
	 * @param string                             $prefix In some cases the array keys are prefixed with `billing_` or `shipping_`, sometimes unprefixed.
	 *
	 * @return array<string,WooCommerceFormField>
	 */
	protected function move_postcode_before_city_field( array $fields, string $prefix = 'billing_' ): array {

		// Priorities are typically set in increments of 10.
		$city_priority                             = $fields[ "{$prefix}city" ]['priority'];
		$fields[ "{$prefix}postcode" ]['priority'] = $city_priority - 5;

		return $fields;
	}

	/**
	 * Reorder the fields when called when rendering the form.
	 *
	 * @hooked woocommerce_checkout_fields
	 * @see WC_Checkout::get_checkout_fields()
	 * @see https://rudrastyh.com/woocommerce/reorder-checkout-fields.html
	 *
	 * @param array{billing:array<string,WooCommerceFormField>, shipping:array<string,WooCommerceFormField>, account:array<string,WooCommerceFormField>, order:array<string,WooCommerceFormField>} $checkout_fields Array of checkout fields which will later be rendered with `woocommerce_form_field()`.
	 *
	 * @return array{billing:array<string,WooCommerceFormField>, shipping:array<string,WooCommerceFormField>, account:array<string,WooCommerceFormField>, order:array<string,WooCommerceFormField>}
	 */
	public function reorder_checkout_fields( array $checkout_fields ): array {

		$checkout_fields['billing']  = $this->move_postcode_before_city_field( $checkout_fields['billing'], 'billing_' );
		$checkout_fields['shipping'] = $this->move_postcode_before_city_field( $checkout_fields['shipping'], 'shipping_' );

		return $checkout_fields;
	}

	/**
	 * Reorder the fields when called to print the local information JSON.
	 *
	 * @hooked woocommerce_get_country_locale_base
	 * @see \WC_Countries::get_country_locale()
	 * @see address-i18n.js
	 *
	 * @param array<string,WooCommerceFormField> $locale_fields The local information.
	 *
	 * @return array<string,WooCommerceFormField>
	 */
	public function reorder_woocommerce_get_country_locale_base( array $locale_fields ): array {

		if ( ! isset( $locale_fields['city']['priority'], $locale_fields['postcode']['priority'] ) ) {
			return $locale_fields;
		}

		return $this->move_postcode_before_city_field( $locale_fields, '' );
	}

	/**
	 * Parse the $_POST 'post_data' string for the zip code and set the city in the checkout object.
	 *
	 * @see WC_Ajax::update_order_review()
	 * @see assets/js/frontend/checkout.js
	 *
	 * @hooked woocommerce_checkout_update_order_review
	 *
	 * @param string $posted_data `posted_data` key of array posted by checkout.js.
	 */
	public function parse_post_on_update_order_review( string $posted_data ): void {

		$post_array = array();
		parse_str( $posted_data, $post_array );

		$postcode = $post_array['billing_postcode'];
		$country  = $post_array['billing_country'];

		if ( empty( $postcode ) || empty( $country ) || ! is_string( $postcode ) || ! is_string( $country ) ) {
			return;
		}

		$location = $this->api->get_state_city_for_postcode( $country, $postcode );

		if ( ! empty( $location['state'] ) ) {
			// Handle Puerto Rico edge case.
			if ( 'PR' === $location['state'] ) {
				$_POST['country'] = $location['state'];
			} else {
				$_POST['state'] = $location['state'];
			}
		}
		if ( ! empty( $location['city'] ) ) {
			$_POST['city'] = array_pop( $location['city'] );
		}

		add_filter( 'woocommerce_update_order_review_fragments', array( $this, 'rerender_billing_fields_fragment' ) );
	}

	/**
	 * `.woocommerce-billing-fields` is not re-rendered by default.
	 *
	 * @see WC_Ajax::update_order_review()
	 *
	 * @hooked woocommerce_update_order_review_fragments
	 *
	 * @param array<string, string> $fragments Associative array of DOM selectors => HTML to be replaced.
	 *
	 * @return array<string, string>
	 */
	public function rerender_billing_fields_fragment( array $fragments ): array {

		$checkout = WC()->checkout();

		ob_start();

		wc_get_template( 'checkout/form-billing.php', array( 'checkout' => $checkout ) );

		$woocommerce_billing_fields = ob_get_clean();

		if ( is_string( $woocommerce_billing_fields ) ) {
			$fragments['.woocommerce-billing-fields'] = $woocommerce_billing_fields;
		}

		return $fragments;
	}
}
