<?php

namespace BrianHenryIE\WC_Postcode_Address_Autofill\WooCommerce;

use Automattic\WooCommerce\Blocks\Integrations\IntegrationRegistry;
use BrianHenryIE\WC_Postcode_Address_Autofill\API_Interface;
use BrianHenryIE\WC_Postcode_Address_Autofill\Settings_Interface;
use Codeception\Stub\Expected;

/**
 * @coversDefaultClass \BrianHenryIE\WC_Postcode_Address_Autofill\WooCommerce\Blocks
 */
class Blocks_Unit_Test extends \Codeception\Test\Unit {

	protected function setup(): void {
		parent::setup();
		\WP_Mock::setUp();
	}

	protected function tearDown(): void {
		parent::tearDown();
		\WP_Mock::tearDown();
	}

	/**
	 * @covers ::register_integration
	 * @covers ::__construct
	 */
	public function test_register_integration(): void {
		$api      = self::makeEmpty( API_Interface::class );
		$settings = self::makeEmpty( Settings_Interface::class );
		$sut      = new Blocks( $api, $settings );

		$integration_registry = self::makeEmpty(
			IntegrationRegistry::class,
			array(
				'register' => Expected::once(
					function( $checkout_blocks ) {
						assert( $checkout_blocks instanceof Checkout_Blocks );
					}
				),
			)
		);

		$sut->register_integration( $integration_registry );
	}

	/**
	 * @covers ::register_update_callback
	 */
	public function test_register_update_callback(): void {
		$api      = self::makeEmpty( API_Interface::class );
		$settings = self::makeEmpty( Settings_Interface::class );
		$sut      = new Blocks( $api, $settings );

		\WP_Mock::userFunction(
			'woocommerce_store_api_register_update_callback',
			array(
				'times' => 1,
				'args'  => function( $args ): bool {
					self::assertCount( 2, $args );
					self::assertEquals( 'bh-wc-postcode-address-autofill', $args['namespace'] );
					return true;
				},
			)
		);

		$sut->register_update_callback();
	}

	/**
	 * @covers ::update_callback
	 */
	public function test_update_callback(): void {

	}

}
