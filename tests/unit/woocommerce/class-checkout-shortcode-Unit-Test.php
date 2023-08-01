<?php

namespace BrianHenryIE\WC_Postcode_Address_Autofill\WooCommerce;

use BrianHenryIE\WC_Postcode_Address_Autofill\API_Interface;
use BrianHenryIE\WC_Postcode_Address_Autofill\Settings_Interface;

/**
 * @coversDefaultClass \BrianHenryIE\WC_Postcode_Address_Autofill\WooCommerce\Checkout_Shortcode
 */
class Checkout_Shortcode_Unit_Test extends \Codeception\Test\Unit {

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

		\WP_Mock::userFunction(
			'is_checkout',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		$api      = self::makeEmpty( API_Interface::class );
		$settings = self::makeEmpty( Settings_Interface::class, array( 'get_plugin_version' => $ver ) );
		$sut      = new Checkout_Shortcode( $api, $settings );

		$sut->enqueue_scripts();

		$this->assertFileExists( $src );
	}
}
