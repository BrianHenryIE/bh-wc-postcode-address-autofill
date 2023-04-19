<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package brianhenryie/bh-wc-checkout-address-suggestions
 */

namespace BrianHenryIE\WC_Checkout_Address_Suggestions\Admin;

use BrianHenryIE\WC_Checkout_Address_Suggestions\Settings_Interface;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 */
class Admin_Assets {

	/**
	 * Uses basename to determine URLs and version for caching.
	 *
	 * @uses Settings::get_plugin_basename()
	 * @uses Settings::get_plugin_version()
	 */
	protected Settings_Interface $settings;

	/**
	 * Constructor.
	 *
	 * @param Settings_Interface $settings The plugin settings.
	 */
	public function __construct( Settings_Interface $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @hooked admin_enqueue_scripts
	 */
	public function enqueue_styles(): void {
		$version = $this->settings->get_plugin_version();

		wp_enqueue_style( 'bh-wc-checkout-address-suggestions', plugin_dir_url( $this->settings->get_plugin_basename() ) . 'assets/bh-wc-checkout-address-suggestions-admin.css', array(), $version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @hooked admin_enqueue_scripts
	 */
	public function enqueue_scripts(): void {
		$version = $this->settings->get_plugin_version();

		wp_enqueue_script( 'bh-wc-checkout-address-suggestions', plugin_dir_url( __FILE__ ) . '/assets/bh-wc-checkout-address-suggestions-admin.js', array( 'jquery' ), $version, true );
	}
}