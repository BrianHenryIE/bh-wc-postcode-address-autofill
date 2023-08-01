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
use WC_Customer;
use WP_REST_Request;

class Checkout_Blocks {

	protected API_Interface $api;

	public function __construct( API_Interface $api ) {
		$this->api = $api;
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
