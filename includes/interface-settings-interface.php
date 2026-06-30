<?php
/**
 * The required plugin settings.
 *
 * @package brianhenryie/bh-wc-postcode-address-autofill
 */

namespace BrianHenryIE\WC_Postcode_Address_Autofill;

/**
 * The plugin settings, passed to most classes in the plugin.
 */
interface Settings_Interface {

	/**
	 * Used for caching assets.
	 */
	public function get_plugin_version(): string;

	/**
	 * Used for determining plugin directory file paths and URL paths.
	 */
	public function get_plugin_basename(): string;
}
