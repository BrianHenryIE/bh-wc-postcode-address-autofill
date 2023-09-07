<?php

namespace BrianHenryIE\WC_Postcode_Address_Autofill\WooCommerce;

use Automattic\WooCommerce\Utilities\FeaturesUtil;
use BrianHenryIE\WC_Postcode_Address_Autofill\Settings_Interface;
use Codeception\Stub\Expected;

/**
 * @coversDefaultClass \BrianHenryIE\WC_Postcode_Address_Autofill\WooCommerce\Features
 */
class Features_WPUnit_Test extends \Codeception\TestCase\WPTestCase {

	/**
	 * @covers ::declare_hpos_compatibility
	 */
	public function test_declare_hpos_compatibility(): void {

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

		$sut->declare_hpos_compatibility();

		$result = FeaturesUtil::get_compatible_plugins_for_feature( 'custom_order_tables' );

		self::assertContains( 'bh-wc-postcode-address-autofill/bh-wc-postcode-address-autofill.php', $result['compatible'], wp_json_encode( $result['compatible'] ) );
	}

	/**
	 * @covers ::declare_cart_checkout_blocks_compatibility
	 */
	public function test_declare_cart_checkout_blocks_compatibility(): void {

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

		$sut->declare_cart_checkout_blocks_compatibility();

		$result = FeaturesUtil::get_compatible_plugins_for_feature( 'cart_checkout_blocks' );

		self::assertContains( 'bh-wc-postcode-address-autofill/bh-wc-postcode-address-autofill.php', $result['compatible'], wp_json_encode( $result['compatible'] ) );
	}
}
