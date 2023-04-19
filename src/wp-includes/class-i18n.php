<?php
/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @package brianhenryie/bh-wc-checkout-address-suggestions
 */

namespace BrianHenryIE\WC_Checkout_Address_Suggestions\WP_Includes;

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 */
class I18n {

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @hooked plugins_loaded
	 */
	public function load_plugin_textdomain(): void {

		load_plugin_textdomain(
			'bh-wc-checkout-address-suggestions',
			false,
			plugin_basename( dirname( __FILE__, 3 ) ) . '/languages/'
		);
	}
}
