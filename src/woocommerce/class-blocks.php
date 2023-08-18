<?php
/**
 * Register the blocks checkout integration and handle updating the cart through `extensionCartUpdate` JavaScript.
 *
 * @package brianhenryie/bh-wc-postcode-address-autofill
 */

namespace BrianHenryIE\WC_Postcode_Address_Autofill\WooCommerce;

use Automattic\WooCommerce\Blocks\Integrations\IntegrationRegistry;
use BrianHenryIE\WC_Postcode_Address_Autofill\API_Interface;
use BrianHenryIE\WC_Postcode_Address_Autofill\Settings_Interface;

/**
 * Register with WooCommerce's `IntegrationRegistry` and `extensionCartUpdate`.
 */
class Blocks {
	/**
	 * The core plugin functions.
	 *
	 * @uses API_Interface::get_state_city_for_postcode()
	 */
	protected API_Interface $api;

	/**
	 * Plugin settings.
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
	 * Use the IntegrationRegistry to enqueue scripts wherever the checkout is displayed.
	 *
	 * @hooked woocommerce_blocks_checkout_block_registration
	 * @see https://github.com/woocommerce/woocommerce-blocks/blob/trunk/docs/third-party-developers/extensibility/checkout-block/integration-interface.md
	 *
	 * @param IntegrationRegistry $integration_registry WooCommerce core class for managing blocks' assets.
	 */
	public function register_integration( IntegrationRegistry $integration_registry ): void {
		$integration_registry->register( new Checkout_Blocks( $this->settings ) );
	}

	/**
	 * Register a function to handle updating the cart in JavaScript using the `extensionCartUpdate` function.
	 *
	 * @hooked woocommerce_blocks_loaded
	 * @see https://github.com/woocommerce/woocommerce-blocks/blob/trunk/docs/third-party-developers/extensibility/rest-api/extend-rest-api-update-cart.md
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
	 * Handle the update sent from the frontend.
	 *
	 * Find the state+city for the country+postcode and apply them to the cart address.
	 * This should only be called when a new postcode has been entered.
	 *
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
