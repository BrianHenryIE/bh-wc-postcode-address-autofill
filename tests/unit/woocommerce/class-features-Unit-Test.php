<?php

namespace BrianHenryIE\WC_Postcode_Address_Autofill\WooCommerce;

use BrianHenryIE\WC_Postcode_Address_Autofill\Settings_Interface;
use Codeception\Stub\Expected;

/**
 * @coversDefaultClass \BrianHenryIE\WC_Postcode_Address_Autofill\WooCommerce\Features
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
