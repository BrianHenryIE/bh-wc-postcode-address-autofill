<?php
/**
 * Handle autofill on postcode entry on the WooCommerce Blocks checkout.
 *
 * When the postcode entered, a HTTP POST request is sent to `/wc/store/v1/cart/update-customer` (via `wp-json/wc/store/v1/batch`).
 *
 * @package brianhenryie/bh-wc-postcode-address-autofill
 */

namespace BrianHenryIE\WC_Postcode_Address_Autofill\WooCommerce;

use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;
use BrianHenryIE\WC_Postcode_Address_Autofill\Settings_Interface;

class Checkout_Blocks implements IntegrationInterface {

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
	 * @param Settings_Interface $settings The plugin settings.
	 */
	public function __construct( Settings_Interface $settings ) {
		$this->settings = $settings;
	}

	public function get_name() {
		return 'bh-wc-postcode-address-autofill-checkout-blocks';
	}

	public function initialize() {
		$this->register_script();
	}

	protected function register_script(): void {
		$script_asset_path = '../../build/bh-wc-postcode-address-autofill-checkout-blocks.asset.php';
		$script_asset      = file_exists( $script_asset_path )
			? require $script_asset_path
			: array(
				'dependencies' => array(),
				'version'      => $this->settings->get_plugin_version(),
			);

		wp_register_script(
			'bh-wc-postcode-address-autofill-checkout-blocks',
			plugin_dir_url( $this->settings->get_plugin_basename() ) . 'build/bh-wc-postcode-address-autofill-checkout-blocks.js',
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);
	}

	public function get_script_handles() {
		return array( 'bh-wc-postcode-address-autofill-checkout-blocks' );
	}

	public function get_editor_script_handles() {
		return array();
	}

	// The script does not need any data.
	public function get_script_data() {
		return array();
	}
}
