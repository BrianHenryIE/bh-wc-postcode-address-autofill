<?php
/**
 * The plugin settings.
 *
 * @package brianhenryie/bh-wc-checkout-address-suggestions
 */

namespace BrianHenryIE\WC_Checkout_Address_Suggestions\API;

use BrianHenryIE\WC_Checkout_Address_Suggestions\Settings_Interface;

/**
 * Plain old object for plugin settings.
 */
class Settings implements Settings_Interface {

	/**
	 * Used to cache assets.
	 */
	public function get_plugin_version(): string {
		return defined( 'BH_WC_CHECKOUT_ADDRESS_SUGGESTIONS_VERSION' )
			? BH_WC_CHECKOUT_ADDRESS_SUGGESTIONS_VERSION
			: '1.0.0';
	}

	/**
	 * Used to determine the plugin dir path and URL.
	 */
	public function get_plugin_basename(): string {
		return defined( 'BH_WC_CHECKOUT_ADDRESS_SUGGESTIONS_BASENAME' )
			? BH_WC_CHECKOUT_ADDRESS_SUGGESTIONS_BASENAME
			: 'bh-wc-checkout-address-suggestions/bh-wc-checkout-address-suggestions.php';
	}
}
