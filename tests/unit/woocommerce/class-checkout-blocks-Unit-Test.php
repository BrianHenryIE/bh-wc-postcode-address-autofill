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
	 * @covers ::enqueue_scripts
	 */
	public function test_enqueue_scripts(): void {

		\WP_Mock::passthruFunction( 'plugin_dir_url' );

		$handle    = 'bh-wc-postcode-address-autofill-checkout-blocks';
		$src       = 'assets/bh-wc-postcode-address-autofill-checkout-blocks.js';
		$deps      = array();
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
		$sut      = new Checkout_Blocks( $api, $settings );

		$sut->enqueue_scripts();

		$this->assertFileExists( $src );
	}

}
