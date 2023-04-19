<?php

namespace BrianHenryIE\WC_Checkout_Address_Suggestions\WooCommerce;

use Automattic\WooCommerce\Utilities\FeaturesUtil;
use BrianHenryIE\WC_Checkout_Address_Suggestions\Settings_Interface;
use Codeception\Stub\Expected;

/**
 * @coversDefaultClass \BrianHenryIE\WC_Checkout_Address_Suggestions\WooCommerce\Features
 */
class Features_WPUnit_Test extends \Codeception\TestCase\WPTestCase {

	/**
	 * @covers ::declare_compatibility
	 */
	public function test_declare_compatibility(): void {

		global $plugin_basename;

		$settings = self::makeEmpty(
			Settings_Interface::class,
			array(
				'get_plugin_basename' => Expected::once( $plugin_basename ),
			)
		);

		/**
		 * `doing_action('before_woocommerce_init')` must be true.
		 */
		global $wp_current_filter;
		$wp_current_filter[] = 'before_woocommerce_init';

		$sut = new Features( $settings );

		$sut->declare_compatibility();

		$result = FeaturesUtil::get_compatible_plugins_for_feature( 'custom_order_tables' );

		self::assertContains( 'bh-wc-checkout-address-suggestions/bh-wc-checkout-address-suggestions.php', $result['compatible'], wp_json_encode( $result['compatible'] ) );
	}

}
