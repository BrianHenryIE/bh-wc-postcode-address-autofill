<?php
/**
 * The file that defines the actions and filters for the plugin.
 *
 * @package brianhenryie/bh-wc-postcode-address-autofill
 */

namespace BrianHenryIE\WC_Postcode_Address_Autofill;

use BrianHenryIE\WC_Postcode_Address_Autofill\WooCommerce\Blocks;
use BrianHenryIE\WC_Postcode_Address_Autofill\WooCommerce\Countries;
use BrianHenryIE\WC_Postcode_Address_Autofill\WooCommerce\Checkout_Blocks;
use BrianHenryIE\WC_Postcode_Address_Autofill\WooCommerce\Checkout_Shortcode;
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

		$this->define_woocommerce_countries_hooks();
		$this->define_woocommerce_shortcode_checkout_hooks();
		$this->define_woocommerce_blocks_checkout_hooks();
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
	protected function define_woocommerce_countries_hooks(): void {

		$countries = new Countries();

		add_filter( 'woocommerce_get_country_locale', array( $countries, 'add_postcode_priority_to_country_locale' ) );
	}

	/**
	 * Register hooks for operation on traditional WooCommerce "shortcode" checkout.
	 * Enqueue the required JavaScript and handle the checkout update.
	 */
	protected function define_woocommerce_shortcode_checkout_hooks(): void {
		$woocommerce_shortcode_checkout = new Checkout_Shortcode( $this->api, $this->settings );

		add_action( 'wp_enqueue_scripts', array( $woocommerce_shortcode_checkout, 'enqueue_scripts' ) );
		add_action( 'woocommerce_checkout_update_order_review', array( $woocommerce_shortcode_checkout, 'parse_post_on_update_order_review' ) );
	}

	/**
	 * Register hooks for operation on the WooCommerce Blocks checkout.
	 * Use IntegrationsRegistry to enqueue the script and Store API to handle updates from JavaScript.
	 */
	protected function define_woocommerce_blocks_checkout_hooks(): void {
		$blocks = new Blocks( $this->api, $this->settings );

		add_action( 'woocommerce_blocks_checkout_block_registration', array( $blocks, 'register_integration' ) );
		add_action( 'woocommerce_blocks_loaded', array( $blocks, 'register_update_callback' ) );
	}

	/**
	 * Declare compatibility with WooCommerce features: High Performance Order Storage and Checkout Blocks.
	 */
	protected function define_woocommerce_features_hooks(): void {

		$features = new Features( $this->settings );

		add_action( 'before_woocommerce_init', array( $features, 'declare_cart_checkout_blocks_compatibility' ) );
		add_action( 'before_woocommerce_init', array( $features, 'declare_hpos_compatibility' ) );
	}
}
