<?php
/**
 * The file that defines the actions and filters for the plugin.
 *
 * @package brianhenryie/bh-wc-postcode-address-autofill
 */

namespace BrianHenryIE\WC_Postcode_Address_Autofill;

use BrianHenryIE\WC_Postcode_Address_Autofill\WooCommerce\Checkout;
use BrianHenryIE\WC_Postcode_Address_Autofill\WooCommerce\Features;
use BrianHenryIE\WC_Postcode_Address_Autofill\WP_Includes\I18n;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * frontend-facing site hooks.
 */
class BH_WC_Postcode_Address_Autofill {

	/**
	 * The plugin settings.
	 */
	protected Settings_Interface $settings;

	/**
	 * The main plugin functions.
	 */
	protected API_Interface $api;

	/**
	 * Constructor
	 *
	 * @param API_Interface      $api The main plugin functions.
	 * @param Settings_Interface $settings The plugin settings.
	 */
	public function __construct( API_Interface $api, Settings_Interface $settings ) {

		$this->settings = $settings;
		$this->api      = $api;

		$this->set_locale();

		$this->define_woocommerce_checkout_hooks();
		$this->define_woocommerce_features_hooks();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 */
	protected function set_locale(): void {

		$plugin_i18n = new I18n();

		add_action( 'init', array( $plugin_i18n, 'load_plugin_textdomain' ) );
	}

	/**
	 * Define the WooCommerce checkout hooks.
	 */
	protected function define_woocommerce_checkout_hooks(): void {

		$woocommerce_checkout = new Checkout( $this->api, $this->settings );

		add_action( 'wp_enqueue_scripts', array( $woocommerce_checkout, 'enqueue_scripts' ) );

		add_filter( 'woocommerce_get_country_locale_base', array( $woocommerce_checkout, 'reorder_woocommerce_get_country_locale_base' ) );
		add_filter( 'woocommerce_checkout_fields', array( $woocommerce_checkout, 'reorder_checkout_fields' ) );

		add_action( 'woocommerce_checkout_update_order_review', array( $woocommerce_checkout, 'parse_post_on_update_order_review' ) );
	}

	/**
	 * Declare compatibility with WooCommerce High Performace Order Storage.
	 */
	protected function define_woocommerce_features_hooks(): void {

		$features = new Features( $this->settings );

		add_action( 'before_woocommerce_init', array( $features, 'declare_compatibility' ) );
	}
}
