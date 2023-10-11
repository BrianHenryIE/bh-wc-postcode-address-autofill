<?php

namespace BrianHenryIE\WC_Postcode_Address_Autofill\WP_Includes;

use BrianHenryIE\WC_Postcode_Address_Autofill\API\Postcode_Locations_Result;
use BrianHenryIE\WC_Postcode_Address_Autofill\API_Interface;
use Codeception\Stub\Expected;

/**
 * @coversDefaultClass \BrianHenryIE\WC_Postcode_Address_Autofill\WP_Includes\Activator
 */
class Activator_Unit_Test extends \Codeception\Test\Unit {

	protected function setup(): void {
		parent::setup();
		\WP_Mock::setUp();
	}

	protected function tearDown(): void {
		parent::tearDown();
		\WP_Mock::tearDown();
	}

	public function test_activate_early(): void {

		\WP_Mock::userFunction(
			'did_action',
			array(
				'times'  => 1,
				'args'   => array( 'woocommerce_loaded' ),
				'return' => false,
			)
		);

		\WP_Mock::expectActionAdded( 'woocommerce_loaded', array( Activator::class, 'prepare_cache' ) );

		Activator::activate();
	}

	public function test_prepare_cache(): void {

		$locations = self::make( Postcode_Locations_Result::class );

		$GLOBALS['bh_wc_postcode_address_autofill'] = self::makeEmpty(
			API_Interface::class,
			array(
				'get_locations_for_postcode' => Expected::once( $locations ),
			)
		);

		\WP_Mock::userFunction(
			'wc_get_base_location',
			array(
				'times'  => 1,
				'args'   => array(),
				'return' => array(
					'country' => 'US',
					'state'   => 'CA',
				),
			)
		);

		Activator::prepare_cache();
	}


	public function test_activate_late(): void {

		\WP_Mock::userFunction(
			'did_action',
			array(
				'times'  => 1,
				'args'   => array( 'woocommerce_loaded' ),
				'return' => true,
			)
		);

		\WP_Mock::expectActionNotAdded( 'woocommerce_loaded', array( Activator::class, 'prepare_cache' ) );

		Activator::activate();
	}
}
