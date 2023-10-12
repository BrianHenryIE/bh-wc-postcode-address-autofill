<?php
/**
 * The plugin settings.
 *
 * @package brianhenryie/bh-wc-postcode-address-autofill
 */

namespace BrianHenryIE\WC_Postcode_Address_Autofill\API;

use BrianHenryIE\WC_Postcode_Address_Autofill\Settings_Interface;

/**
 * Plain old object for plugin settings.
 */
class Settings implements Settings_Interface {

	/**
	 * Used to cache assets.
	 */
	public function get_plugin_version(): string {
		return defined( 'BH_WC_POSTCODE_ADDRESS_AUTOFILL_VERSION' )
			? constant( 'BH_WC_POSTCODE_ADDRESS_AUTOFILL_VERSION' )
			: '1.4.0';
	}

	/**
	 * Used to determine the plugin dir path and URL.
	 */
	public function get_plugin_basename(): string {
		return defined( 'BH_WC_POSTCODE_ADDRESS_AUTOFILL_BASENAME' )
			? constant( 'BH_WC_POSTCODE_ADDRESS_AUTOFILL_BASENAME' )
			: 'bh-wc-postcode-address-autofill/bh-wc-postcode-address-autofill.php';
	}
}
