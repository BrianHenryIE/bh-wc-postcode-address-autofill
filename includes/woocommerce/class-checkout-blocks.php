<?php
/**
 * Register the Blocks checkout JavaScript with WooCommerce.
 *
 * @package brianhenryie/bh-wc-postcode-address-autofill
 */

namespace BrianHenryIE\WC_Postcode_Address_Autofill\WooCommerce;

use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;
use BrianHenryIE\WC_Postcode_Address_Autofill\Settings_Interface;

/**
 * Conventional way to register scripts for WooCommerce Blocks.
 *
 * @see \Automattic\WooCommerce\Blocks\BlockTypes\AbstractBlock::register_block_type_assets()
 */
class Checkout_Blocks implements IntegrationInterface {

	/**
	 * Plugin settings for assets URL and sometimes for caching version.
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

	/**
	 * Return a unique name for the integration.
	 *
	 * @see IntegrationInterface::get_name()
	 * @return string
	 */
	public function get_name() {
		return 'bh-wc-postcode-address-autofill-checkout-blocks';
	}

	/**
	 * Initialize the integration – in this case, just register the script.
	 *
	 * @see IntegrationInterface::initialize()
	 * @return void
	 */
	public function initialize() {
		$this->register_script();
	}

	/**
	 * Register the checkout script with WordPress, to later be enqueued by WooCommerce.
	 */
	protected function register_script(): void {
		$script_asset_path = realpath( __DIR__ . '/../../build/bh-wc-postcode-address-autofill-checkout-blocks.asset.php' );
		$script_asset      = $script_asset_path && file_exists( $script_asset_path )
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

	/**
	 * Return the handle of the script registered above.
	 *
	 * @see IntegrationInterface::get_script_handles()
	 * @return string[]
	 */
	public function get_script_handles() {
		return array( 'bh-wc-postcode-address-autofill-checkout-blocks' );
	}

	/**
	 * The script does not run in the admin editor.
	 *
	 * @see IntegrationInterface::get_editor_script_handles()
	 * @return string[]
	 */
	public function get_editor_script_handles() {
		return array();
	}

	/**
	 * The script does not need any data.
	 *
	 * @see IntegrationInterface::get_script_data()
	 * @return array{}
	 */
	public function get_script_data() {
		return array();
	}
}
