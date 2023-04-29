<?php

namespace BrianHenryIE\WC_Postcode_Address_Autofill\API;

/**
 * @coversDefaultClass \BrianHenryIE\WC_Postcode_Address_Autofill\API\API
 */
class API_WPUnit_Test extends \Codeception\TestCase\WPTestCase {
	/**
	 * @covers ::get_state_city_for_postcode
	 */
	public function test_get_state_city_for_postcode_ireland(): void {
		$settings = self::makeEmpty(
			Settings::class,
			array(
				'get_plugin_basename' => 'bh-wc-postcode-address-autofill/bh-wc-postcode-address-autofill.php',
			)
		);

		$sut = new API( $settings );

		$result = $sut->get_state_city_for_postcode( 'ie', 'A67 TK38' );

		$first_city = array_pop( $result['city'] );
		self::assertEquals( 'Wicklow', $first_city );
	}
	/**
	 * @covers ::get_state_city_for_postcode
	 */
	public function test_get_state_city_for_postcode_usa(): void {
		$settings = self::makeEmpty(
			Settings::class,
			array(
				'get_plugin_basename' => 'bh-wc-postcode-address-autofill/bh-wc-postcode-address-autofill.php',
			)
		);

		$sut = new API( $settings );

		$result = $sut->get_state_city_for_postcode( 'us', '95816' );

		$first_city = array_pop( $result['city'] );
		self::assertEquals( 'SACRAMENTO', $first_city );
	}
	/**
	 * @covers ::get_state_city_for_postcode
	 */
	public function test_get_state_city_for_postcode_japan(): void {
		$settings = self::makeEmpty(
			Settings::class,
			array(
				'get_plugin_basename' => 'bh-wc-postcode-address-autofill/bh-wc-postcode-address-autofill.php',
			)
		);

		$sut = new API( $settings );

		$result = $sut->get_state_city_for_postcode( 'jp', '496-0856' );

		self::assertContains( 'Rurikojicho', $result['city'], 'Result: ' . implode( ',', $result['city'] ) );
	}
}
