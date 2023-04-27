<?php
/**
 * Declare compatability with WooCommerce features, i.e. HPOS.
 *
 * @package brianhenryie/bh-wc-postcode-address-autofill
 */

namespace BrianHenryIE\WC_Postcode_Address_Autofill\WooCommerce;

use Automattic\WooCommerce\Utilities\FeaturesUtil;
use BrianHenryIE\WC_Postcode_Address_Autofill\Settings_Interface;

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
