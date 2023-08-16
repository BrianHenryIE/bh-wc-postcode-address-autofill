<?php

namespace BrianHenryIE\WC_Postcode_Address_Autofill\WooCommerce;

use BrianHenryIE\WC_Postcode_Address_Autofill\API_Interface;
use BrianHenryIE\WC_Postcode_Address_Autofill\Settings_Interface;
use Codeception\Stub\Expected;

/**
 * @coversDefaultClass \BrianHenryIE\WC_Postcode_Address_Autofill\WooCommerce\Checkout_Shortcode
 */
class Checkout_Shortcode_WPUnit_Test extends \Codeception\TestCase\WPTestCase {

	/**
	 * @covers ::rerender_billing_fields_fragment
	 */
	public function test_rerender_billing_fields_fragment(): void {

		$api      = self::makeEmpty( API_Interface::class );
		$settings = self::makeEmpty( Settings_Interface::class );
		$sut      = new Checkout_Shortcode( $api, $settings );

		$result = $sut->rerender_billing_fields_fragment( array() );

		self::assertArrayHasKey( '.woocommerce-billing-fields', $result );
	}
}
