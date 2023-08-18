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
		$script_asset_path = WP_PLUGIN_DIR . '/' . plugin_dir_path( $this->settings->get_plugin_basename() ) . 'build/bh-wc-postcode-address-autofill-checkout-blocks.asset.php';
		$script_asset      = file_exists( $script_asset_path )
			? require $script_asset_path
			: array(
				'dependencies' => array(),
				'version'      => $this->settings->get_plugin_version(),
			);

		wp_enqueue_script(
			'bh-wc-postcode-address-autofill-checkout-blocks',
			plugin_dir_url( $this->settings->get_plugin_basename() ) . 'build/bh-wc-postcode-address-autofill-checkout-blocks.js',
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);
	}

	public function register_update_callback(): void {
		woocommerce_store_api_register_update_callback(
			array(
				'namespace' => 'bh-wc-postcode-address-autofill',
				'callback'  => array( $this, 'update_callback' ),
			)
		);
	}

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
