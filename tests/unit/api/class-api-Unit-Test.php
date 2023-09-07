<?php

namespace BrianHenryIE\WC_Postcode_Address_Autofill\API;

use BrianHenryIE\WC_Postcode_Address_Autofill\Settings_Interface;
use Codeception\Stub\Expected;
use function PHPUnit\Framework\assertEmpty;

/**
 * @coversDefaultClass \BrianHenryIE\WC_Postcode_Address_Autofill\API\API
 */
class API_Unit_Test extends \Codeception\Test\Unit {

	/**
	 * @covers ::get_locations_for_postcode
	 */
	public function test_get_locations_for_postcode_accepts_lowercase_country(): void {

		$data_loader = self::makeEmpty(
			Data_Loader::class,
			array(
				'get_data_for_country' => Expected::once(
					function ( string $country ) {
						self::assertEquals( $country, 'US' );
						return self::makeEmpty( Country_Data::class );
					}
				),
			)
		);
		$settings    = self::makeEmpty( Settings_Interface::class );

		$sut = new API( $data_loader, $settings );

		$sut->get_locations_for_postcode( 'us', '95819' );
	}

	/**
	 * @covers ::get_locations_for_postcode
	 */
	public function test_get_locations_for_postcode_returns_null_for_no_country_data(): void {

		$data_loader = self::makeEmpty(
			Data_Loader::class,
			array(
				'get_data_for_country' => Expected::once(
					function ( string $country ) {
						return null;
					}
				),
			)
		);
		$settings    = self::makeEmpty( Settings_Interface::class );

		$sut = new API( $data_loader, $settings );

		$result = $sut->get_locations_for_postcode( 'xx', '95819' );

		self::assertNull( $result );
	}

	/**
	 * @covers ::get_locations_for_postcode
	 */
	public function test_get_locations_for_postcode_returns_null_for_no_postcode_data(): void {

		$data_loader = self::makeEmpty(
			Data_Loader::class,
			array(
				'get_data_for_country' => Expected::once(
					function ( string $country ) {
						return self::makeEmpty(
							Country_Data::class,
							array(
								'get_locations_for_postcode' => Expected::once(
									function ( $postcode ) {
										self::assertEquals( '95819', $postcode );
										return null; }
								),
							)
						);
					}
				),
			)
		);
		$settings    = self::makeEmpty( Settings_Interface::class );

		$sut = new API( $data_loader, $settings );

		$result = $sut->get_locations_for_postcode( 'xx', '95819' );

		self::assertNull( $result );
	}
}
