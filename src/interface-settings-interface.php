<?php
/**
 * The required plugin settings.
 *
 * @package brianhenryie/bh-wc-checkout-address-suggestions
 */

namespace BrianHenryIE\WC_Checkout_Address_Suggestions;

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
