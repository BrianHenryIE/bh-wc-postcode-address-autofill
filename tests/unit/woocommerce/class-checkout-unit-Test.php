<?php

namespace BrianHenryIE\WC_Postcode_Address_Autofill\WooCommerce;

use BrianHenryIE\WC_Postcode_Address_Autofill\API_Interface;
use BrianHenryIE\WC_Postcode_Address_Autofill\Settings_Interface;

/**
 * @coversDefaultClass \BrianHenryIE\WC_Postcode_Address_Autofill\WooCommerce\Checkout
 */
class Checkout_Unit_Test extends \Codeception\Test\Unit {

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
	 * @covers ::enqueue_scripts
	 */
	public function test_enqueue_scripts(): void {

		\WP_Mock::passthruFunction( 'plugin_dir_url' );

		$handle    = 'bh-wc-postcode-address-autofill-checkout';
		$src       = 'assets/bh-wc-postcode-address-autofill-checkout.js';
		$deps      = array( 'jquery' );
		$ver       = '1.0.0';
		$in_footer = true;

		\WP_Mock::userFunction(
			'wp_enqueue_script',
			array(
				'times' => 1,
				'args'  => array( $handle, $src, $deps, $ver, $in_footer ),
			)
		);

		$api      = self::makeEmpty( API_Interface::class );
		$settings = self::makeEmpty( Settings_Interface::class, array( 'get_plugin_version' => $ver ) );
		$sut      = new Checkout( $api, $settings );

		$sut->enqueue_scripts();

		$this->assertFileExists( $src );
	}

	/**
	 * @covers ::reorder_checkout_fields
	 * @covers ::move_postcode_before_city_field
	 */
	public function test_move_postcode_input_above_city(): void {

		$api      = self::makeEmpty( API_Interface::class );
		$settings = self::makeEmpty( Settings_Interface::class );
		$sut      = new Checkout( $api, $settings );

		$checkout_fields = array(
			'billing'  => array(
				'billing_postcode' => array(
					'priority' => 90,
				),
				'billing_city'     => array(
					'priority' => 70,
				),
			),
			'shipping' => array(
				'shipping_postcode' => array(
					'priority' => 90,
				),
				'shipping_city'     => array(
					'priority' => 70,
				),
			),
		);

		$result = $sut->reorder_checkout_fields( $checkout_fields );

		self::assertEquals( 65, $result['billing']['billing_postcode']['priority'] );
		self::assertEquals( 65, $result['shipping']['shipping_postcode']['priority'] );
	}

}
