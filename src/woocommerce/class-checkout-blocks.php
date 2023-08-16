<?php
/**
 * Handle autofill on postcode entry on the WooCommerce Blocks checkout.
 *
 * When the postcode entered, a HTTP POST request is sent to `/wc/store/v1/cart/update-customer` (via `wp-json/wc/store/v1/batch`).
 *
 * @see CartUpdateCustomer
 *
 * @package brianhenryie/bh-wc-postcode-address-autofill
 */

namespace BrianHenryIE\WC_Postcode_Address_Autofill\WooCommerce;

use BrianHenryIE\WC_Postcode_Address_Autofill\API_Interface;
use BrianHenryIE\WC_Postcode_Address_Autofill\Settings_Interface;
use WC_Customer;
use WP_REST_Request;

class Checkout_Blocks {

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
	 * Register the JavaScript files used for WooCommerce Blocks checkout.
	 *
	 * @hooked woocommerce_blocks_enqueue_checkout_block_scripts_after
	 * @see \Automattic\WooCommerce\Blocks\BlockTypes\Checkout::enqueue_assets()
	 */
	public function enqueue_scripts(): void {
		$version = $this->settings->get_plugin_version();

		wp_enqueue_script(
			'bh-wc-postcode-address-autofill-checkout-blocks',
			plugin_dir_url( $this->settings->get_plugin_basename() ) . 'assets/bh-wc-postcode-address-autofill-checkout-blocks.js',
			array(),
			$version,
			true
		);
	}

	/**
	 *
	 * Edit the request because objects are passed by reference.
	 *
	 * @hooked rest_request_before_callbacks
	 * @see \WP_REST_Server::respond_to_request()
	 */
	public function add_state_city_from_zip( $response, array $handler, WP_REST_Request $request ) {

		$route = $request->get_route();

		if ( '/wc/store/v1/cart/update-customer' !== $route ) {
			return $response;
		}

		$data = $request->get_body_params();

		if ( ! isset( $data['billing_address'] ) || ! isset( $data['billing_address']['postcode'] ) ) {
			return $response;
		}

		$updated_postcode = $data['billing_address']['postcode'];

		wc_load_cart();

		$cart = WC()->cart;

		$customer = wc()->customer;

		if ( is_null( $cart ) ) {
			return $response;
		}

		$country = $cart->get_customer()->get_country();

		$state_city = $this->api->get_state_city_for_postcode( $country, $updated_postcode );
		if ( ! empty( $state_city['state'] ) ) {
			$cart->get_customer()->set_state( $state_city['state'] );
		}
		if ( ! empty( $state_city['city'] ) ) {
			$city = array_pop( $state_city['city'] );
			$cart->get_customer()->set_billing_city( $city );
		}

		// /wc/store/v1/cart/update-customer
		return $response;
	}

}
