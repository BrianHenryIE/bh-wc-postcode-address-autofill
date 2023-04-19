<?php
/**
 * Declare compatability with WooCommerce features, i.e. HPOS.
 *
 * @package brianhenryie/bh-wc-checkout-address-suggestions
 */

namespace BrianHenryIE\WC_Checkout_Address_Suggestions\WooCommerce;

use Automattic\WooCommerce\Utilities\FeaturesUtil;
use BrianHenryIE\WC_Checkout_Address_Suggestions\Settings_Interface;

/**
 * Declare compatibility with WooCommerce High Performance Order Storage.
 */
class Features {
	/**
	 * For the plugin basename.
	 */
	protected Settings_Interface $settings;

	/**
	 * Constructor
	 *
	 * @param Settings_Interface $settings The plugin's settings.
	 */
	public function __construct( Settings_Interface $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Register compatibility with HPOS.
	 *
	 * We do not use anything with orders.
	 *
	 * @hooked before_woocommerce_init
	 */
	public function declare_compatibility(): void {
		if ( ! class_exists( FeaturesUtil::class ) ) {
			return;
		}

		FeaturesUtil::declare_compatibility( 'custom_order_tables', $this->settings->get_plugin_basename(), true );
	}
}
