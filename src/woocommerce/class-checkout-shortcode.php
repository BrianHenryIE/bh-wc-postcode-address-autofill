<?php
/**
 * Handle functions specific to the WooCommerce shortcode (traditional/PHP) checkout.
 *
 * @package brianhenryie/bh-wc-postcode-address-autofill
 */

namespace BrianHenryIE\WC_Postcode_Address_Autofill\WooCommerce;

use BrianHenryIE\WC_Postcode_Address_Autofill\API_Interface;
use BrianHenryIE\WC_Postcode_Address_Autofill\Settings_Interface;

/**
 * Hook onto WooCommerce checkout functions to reorder the fields and autofill the values.
 */
class Checkout_Shortcode {

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

		if ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) {
			return;
		}

		$version = $this->settings->get_plugin_version();

		wp_enqueue_script( 'bh-wc-postcode-address-autofill-checkout', plugin_dir_url( $this->settings->get_plugin_basename() ) . 'assets/bh-wc-postcode-address-autofill-checkout.js', array( 'jquery' ), $version, true );

		// TODO: Also add a script containing select2 city suggestions.
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

		// In the future, it would be good to detect country from postcode.
		if ( empty( $post_array['billing_postcode'] ) || empty( $post_array['billing_country'] )
		|| ! is_string( $post_array['billing_postcode'] ) || ! is_string( $post_array['billing_country'] )
		) {
			return;
		}

		$postcode = $post_array['billing_postcode'];
		$country  = $post_array['billing_country'];

		// If the postcode did not change on this request, do not alter the address.
		$customer_session_data = WC()->session->get( 'customer' );
		if ( ! is_null( $customer_session_data ) && isset( $customer_session_data['postcode'] ) && $postcode === $customer_session_data['postcode'] ) {
			return;
		}

		$location = $this->api->get_state_city_for_postcode( $country, $postcode );

		if ( empty( $location['state'] ) || empty( $location['city'] ) ) {
			return;
		}

		$new_state  = $location['state'];
		$new_cities = $location['city'];

		// If the correct city and state are already set, there is nothing to do.
		if (
			isset( $post_array['billing_state'] ) && $post_array['billing_state'] === $new_state
			&& isset( $post_array['billing_city'] ) && in_array( strtoupper( $post_array['billing_city'] ), $new_cities, true )
		) {
			return;
		}

		// Crude! One postcode could represent multiple towns/cities but for v1 we only return one.
		$new_city = array_pop( $new_cities );

		// Handle Puerto Rico edge case.
		if ( 'PR' === $new_state ) {
			$_POST['country'] = $new_state;
		} else {
			$_POST['state'] = $new_state;
		}
		$_POST['city'] = $new_city;

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
