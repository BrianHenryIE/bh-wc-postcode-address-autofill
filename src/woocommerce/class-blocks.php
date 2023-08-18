<?php


namespace BrianHenryIE\WC_Postcode_Address_Autofill\WooCommerce;

use Automattic\WooCommerce\Blocks\Integrations\IntegrationRegistry;
use BrianHenryIE\WC_Postcode_Address_Autofill\API_Interface;
use BrianHenryIE\WC_Postcode_Address_Autofill\Settings_Interface;

class Blocks {

	/**
	 * The core plugin functions.
	 *
	 * @uses API_Interface::get_state_city_for_postcode()
	 */
	protected API_Interface $api;

	/**
	 * Plugin settings for assets URL.
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
	 * @hooked woocommerce_blocks_checkout_block_registration
	 */
	public function register_integration( IntegrationRegistry $integration_registry ): void {
		$integration_registry->register( new Checkout_Blocks( $this->settings ) );
	}

	/**
	 * @hooked woocommerce_blocks_loaded
	 */
	public function register_update_callback(): void {
		woocommerce_store_api_register_update_callback(
			array(
				'namespace' => 'bh-wc-postcode-address-autofill',
				'callback'  => array( $this, 'update_callback' ),
			)
		);
	}

	/**
	 * @param array{postcode:string, country:string} $data The data object as passed from our JavaScript.
	 */
	public function update_callback( array $data ): void {
		$postcode = $data['postcode'];
		$country  = $data['country'];

		$cart = WC()->cart;

		$cart->get_customer()->set_billing_postcode( $postcode );

		$state_city = $this->api->get_state_city_for_postcode( $country, $postcode );

		if ( ! empty( $state_city['state'] ) ) {
			$cart->get_customer()->set_billing_state( $state_city['state'] );
		}
		if ( ! empty( $state_city['city'] ) ) {
			$city = array_pop( $state_city['city'] );
			$cart->get_customer()->set_billing_city( $city );
		}
	}
}
