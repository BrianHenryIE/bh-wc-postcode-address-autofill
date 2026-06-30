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
	 * @uses API_Interface::get_locations_for_postcode()
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

		foreach ( array( 'billing', 'shipping' ) as $address_type ) {
			if ( ! empty( $post_array[ "{$address_type}_postcode" ] )
				&& ! empty( $post_array[ "{$address_type}_country" ] )
				&& is_string( $post_array[ "{$address_type}_postcode" ] )
				&& is_string( $post_array[ "{$address_type}_country" ] )
			) {
				$this->process_address_update( $address_type, $post_array );
			}
		}
	}

	/**
	 * Given a non-empty postcode and country, update the state and city. And add a filter to re-render the fields.
	 *
	 * @param string               $address_type Shipping or billing.
	 * @param array<string,string> $post_array The $_POST array.
	 */
	protected function process_address_update( string $address_type, array $post_array ): void {
		$postcode = $post_array[ "{$address_type}_postcode" ];
		$country  = $post_array[ "{$address_type}_country" ];

		$customer_session_data_prefix = 'shipping' === $address_type ? 'shipping_' : '';

		// If the postcode did not change on this request, do not alter the address.
		$customer_session_data = WC()->session->get( 'customer' );
		if ( ! is_null( $customer_session_data ) && isset( $customer_session_data[ "{$customer_session_data_prefix}postcode" ] ) && $postcode === $customer_session_data[ "{$customer_session_data_prefix}postcode" ] ) {
			return;
		}

		$locations = $this->api->get_locations_for_postcode( $country, $postcode );

		if ( empty( $locations ) ) {
			return;
		}

		// Crude! One postcode could represent multiple towns/cities but for v1 we only work with one.
		$location = $locations->get_first();

		if ( empty( $location ) ) {
			return;
		}

		$new_state = $location->get_state();
		$new_city  = $location->get_city();

		// If the correct city and state are already set, there is nothing to do.
		if (
			isset( $post_array[ "{$address_type}_state" ] ) && $post_array[ "{$address_type}_state" ] === $new_state
			&& isset( $post_array[ "{$address_type}_city" ] ) && is_string( $post_array[ "{$address_type}_city" ] ) && stripos( $post_array[ "{$address_type}_city" ], $new_city ) === 0
		) {
			return;
		}

		$post_prefix = 'shipping' === $address_type ? 's_' : '';

		// Handle Puerto Rico edge case.
		if ( 'PR' === $new_state ) {
			$_POST[ "{$post_prefix}country" ] = $new_state;
		} else {
			$_POST[ "{$post_prefix}state" ] = $new_state;
		}
		$_POST[ "{$post_prefix}city" ] = $new_city;

		'shipping' === $address_type
			? add_filter( 'woocommerce_update_order_review_fragments', array( $this, 'rerender_shipping_fields_fragment' ) )
			: add_filter( 'woocommerce_update_order_review_fragments', array( $this, 'rerender_billing_fields_fragment' ) );
	}

	/**
	 * `.woocommerce-billing-fields` is not re-rendered by default.
	 *
	 * @see WC_Ajax::update_order_review()
	 * @hooked woocommerce_update_order_review_fragments
	 *
	 * @param array<string, string> $fragments Associative array of DOM selectors => HTML to be replaced.
	 *
	 * @return array<string, string>
	 */
	public function rerender_billing_fields_fragment( array $fragments ): array {

		$billing_fragment = $this->get_fragment( 'billing' );

		if ( ! empty( $billing_fragment ) ) {
			$fragments['.woocommerce-billing-fields'] = $billing_fragment;
		}

		return $fragments;
	}

	/**
	 * `.woocommerce-shipping-fields` is not re-rendered by default.
	 *
	 * @see WC_Ajax::update_order_review()
	 * @hooked woocommerce_update_order_review_fragments
	 *
	 * @param array<string, string> $fragments Associative array of DOM selectors => HTML to be replaced.
	 *
	 * @return array<string, string>
	 */
	public function rerender_shipping_fields_fragment( array $fragments ): array {

		$shipping_fragment = $this->get_fragment( 'shipping' );

		if ( ! empty( $shipping_fragment ) ) {
			$fragments['.woocommerce-shipping-fields'] = $shipping_fragment;
		}

		return $fragments;
	}

	/**
	 * Get the HTML for the billing or shipping fields of the shortcode checkout.
	 *
	 * @param string $address_type Billing or shipping.
	 *
	 * @return ?string
	 */
	protected function get_fragment( string $address_type ): ?string {

		$checkout = WC()->checkout();

		ob_start();

		wc_get_template( "checkout/form-{$address_type}.php", array( 'checkout' => $checkout ) );

		$woocommerce_address_fields = ob_get_clean();

		return is_string( $woocommerce_address_fields ) ? $woocommerce_address_fields : null;
	}
}
