<?php

namespace BrianHenryIE\WC_Postcode_Address_Autofill\WooCommerce;

use Automattic\WooCommerce\Utilities\FeaturesUtil;
use BrianHenryIE\WC_Postcode_Address_Autofill\Settings_Interface;
use Codeception\Stub\Expected;

/**
 * @coversDefaultClass \BrianHenryIE\WC_Postcode_Address_Autofill\WooCommerce\Features
 */
class Features_Unit_Test extends \Codeception\Test\Unit {

	public function tearDown(): void {
		parent::tearDown();

		\Patchwork\restoreAll();
	}

	/**
	 * @covers ::__construct
	 * @covers ::declare_hpos_compatibility
	 */
	public function test_no_hpos_compatibility(): void {

		\Patchwork\redefine(
			'class_exists',
			function ( string $class_name ): string {
				switch ( $class_name ) {
					case \Automattic\WooCommerce\Utilities\FeaturesUtil::class:
						return false;
					default:
						return \Patchwork\relay( func_get_args() );
				}
			}
		);

		$settings = self::makeEmpty(
			Settings_Interface::class,
			array(
				'get_plugin_basename' => Expected::never(),
			)
		);

		$sut = new Features( $settings );

		$sut->declare_hpos_compatibility();
	}

	/**
	 * @covers ::__construct
	 * @covers ::declare_hpos_compatibility
	 */
	public function test_declare_hpos_compatibility(): void {

		\Patchwork\redefine(
			'class_exists',
			function ( string $class_name ): string {
				switch ( $class_name ) {
					case \Automattic\WooCommerce\Utilities\FeaturesUtil::class:
						return true;
					default:
						return \Patchwork\relay( func_get_args() );
				}
			}
		);

		$function_called = false;

		\Patchwork\redefine(
			array( FeaturesUtil::class, 'declare_compatibility' ),
			function ( string $feature_id, string $plugin_file, bool $positive_compatibility = true ) use ( &$function_called ): bool {
				$function_called = true;
				return true;
			}
		);

		$settings = self::makeEmpty(
			Settings_Interface::class,
			array(
				'get_plugin_basename' => Expected::once( 'bh-wc-postcode-address-autofill/bh-wc-postcode-address-autofill.php' ),
			)
		);

		$sut = new Features( $settings );

		$sut->declare_hpos_compatibility();

		$this->assertTrue( $function_called );
	}

	/**
	 * @covers ::__construct
	 * @covers ::declare_cart_checkout_blocks_compatibility
	 */
	public function test_no_cart_checkout_blocks_compatibility(): void {

		\Patchwork\redefine(
			'class_exists',
			function ( string $class_name ): string {
				switch ( $class_name ) {
					case \Automattic\WooCommerce\Utilities\FeaturesUtil::class:
						return false;
					default:
						return \Patchwork\relay( func_get_args() );
				}
			}
		);

		$settings = self::makeEmpty(
			Settings_Interface::class,
			array(
				'get_plugin_basename' => Expected::never(),
			)
		);

		$sut = new Features( $settings );

		$sut->declare_cart_checkout_blocks_compatibility();
	}

	/**
	 * @covers ::__construct
	 * @covers ::declare_cart_checkout_blocks_compatibility
	 */
	public function test_declare_cart_checkout_blocks_compatibility(): void {

		\Patchwork\redefine(
			'class_exists',
			function ( string $class_name ): string {
				switch ( $class_name ) {
					case \Automattic\WooCommerce\Utilities\FeaturesUtil::class:
						return true;
					default:
						return \Patchwork\relay( func_get_args() );
				}
			}
		);

		$settings = self::makeEmpty(
			Settings_Interface::class,
			array(
				'get_plugin_basename' => Expected::once( 'bh-wc-postcode-address-autofill/bh-wc-postcode-address-autofill.php' ),
			)
		);

		$function_called = false;

		\Patchwork\redefine(
			array( FeaturesUtil::class, 'declare_compatibility' ),
			function ( string $feature_id, string $plugin_file, bool $positive_compatibility = true ) use ( &$function_called ): bool {
				$function_called = true;
				return true;
			}
		);

		$sut = new Features( $settings );

		$sut->declare_cart_checkout_blocks_compatibility();

		$this->assertTrue( $function_called );
	}
}
