<?php

namespace BrianHenryIE\WC_Checkout_Address_Suggestions\WooCommerce;

use BrianHenryIE\WC_Checkout_Address_Suggestions\Settings_Interface;
use Codeception\Stub\Expected;

/**
 * @coversDefaultClass \BrianHenryIE\WC_Checkout_Address_Suggestions\WooCommerce\Features
 */
class Features_Unit_Test extends \Codeception\Test\Unit {

	/**
	 * @covers ::__construct
	 * @covers ::declare_compatibility
	 */
	public function test_no_features_class(): void {

		$settings = self::makeEmpty(
			Settings_Interface::class,
			array(
				'get_plugin_basename' => Expected::never(),
			)
		);

		$sut = new Features( $settings );

		$sut->declare_compatibility();
	}
}
