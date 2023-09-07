<?php

namespace BrianHenryIE\WC_Postcode_Address_Autofill\WooCommerce;

use BrianHenryIE\WC_Postcode_Address_Autofill\API_Interface;
use BrianHenryIE\WC_Postcode_Address_Autofill\Settings_Interface;
use Codeception\Stub\Expected;

/**
 * @coversDefaultClass \BrianHenryIE\WC_Postcode_Address_Autofill\WooCommerce\Checkout_Blocks
 */
class Checkout_Blocks_Unit_Test extends \Codeception\Test\Unit {

	protected function setup(): void {
		parent::setup();
		\WP_Mock::setUp();
	}

	protected function tearDown(): void {
		parent::tearDown();
		\WP_Mock::tearDown();
	}

	/**
	 * @covers ::__construct
	 * @covers ::initialize
	 * @covers ::register_script
	 */
	public function test_enqueue_scripts(): void {

		$plugin_basename = 'bh-wc-postcode-address-autofill/bh-wc-postcode-address-autofill.php';

		$handle    = 'bh-wc-postcode-address-autofill-checkout-blocks';
		$src       = 'build/bh-wc-postcode-address-autofill-checkout-blocks.js';
		$ver       = '1.0.0';
		$in_footer = true;

		\WP_Mock::userFunction(
			'plugin_dir_url',
			array(
				'times'  => 1,
				'args'   => $plugin_basename,
				'return' => '',
			)
		);

		\WP_Mock::userFunction(
			'wp_register_script',
			array(
				'times' => 1,
				'args'  => array(
					$handle,
					$src,
					\WP_Mock\Functions::type( 'array' ),
					\WP_Mock\Functions::type( 'string' ),
					$in_footer,
				),
			)
		);

		$settings = self::makeEmpty(
			Settings_Interface::class,
			array(
				'get_plugin_version'  => $ver,
				'get_plugin_basename' => $plugin_basename,
			)
		);
		$sut      = new Checkout_Blocks( $settings );

		$sut->initialize();

		$this->assertFileExists( $src );
	}

	/**
	 * @covers ::get_name
	 */
	public function test_get_name(): void {
		$settings = self::makeEmpty( Settings_Interface::class );
		$sut      = new Checkout_Blocks( $settings );

		self::assertEquals( 'bh-wc-postcode-address-autofill-checkout-blocks', $sut->get_name() );
	}

	/**
	 * @covers ::get_script_handles
	 */
	public function test_get_script_handles(): void {
		$settings = self::makeEmpty( Settings_Interface::class );
		$sut      = new Checkout_Blocks( $settings );

		$result = $sut->get_script_handles();

		self::assertIsArray( $result );
		self::assertCount( 1, $result );
		self::assertEquals( 'bh-wc-postcode-address-autofill-checkout-blocks', $result[0] );
	}

	/**
	 * @covers ::get_editor_script_handles
	 */
	public function test_get_editor_script_handles(): void {
		$settings = self::makeEmpty( Settings_Interface::class );
		$sut      = new Checkout_Blocks( $settings );

		self::assertIsArray( $sut->get_editor_script_handles() );
		self::assertEmpty( $sut->get_editor_script_handles() );
	}

	/**
	 * @covers ::get_script_data
	 */
	public function test_get_script_data(): void {
		$settings = self::makeEmpty( Settings_Interface::class );
		$sut      = new Checkout_Blocks( $settings );

		self::assertIsArray( $sut->get_script_data() );
		self::assertEmpty( $sut->get_script_data() );
	}
}
