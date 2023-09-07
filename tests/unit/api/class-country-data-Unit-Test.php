<?php

namespace BrianHenryIE\WC_Postcode_Address_Autofill\API;

use BrianHenryIE\WC_Postcode_Address_Autofill\Settings_Interface;
use Codeception\Stub\Expected;
use function PHPUnit\Framework\assertEmpty;

/**
 * @coversDefaultClass \BrianHenryIE\WC_Postcode_Address_Autofill\API\Country_Data
 */
class Country_Data_Unit_Test extends \Codeception\Test\Unit {

	public function test_two_postcodes(): void {

		$json_string = <<<'EOD'
{
    "postcode_locations": {
        "A67": [
            {
                "postcode": "A67",
                "state": "Wicklow",
                "city": "Rathnew"
            },
            {
                "postcode": "A67",
                "state": "Wicklow",
                "city": "Wicklow"
            }
        ]
    },
    "postcode_length": 3
}
EOD;

		$sut = new Country_Data( json_decode( $json_string ) );

		$result = $sut->get_locations_for_postcode( 'A67' );

		self::assertCount( 2, $result );
	}


	public function test_partial_postcode(): void {

		$json_string = <<<'EOD'
{
    "postcode_locations": {
        "95814": [
            {
                "postcode": "95814",
                "state": "CA",
                "city": "Sacramento"
            }
        ],
        "95815": [
            {
                "postcode": "95815",
                "state": "CA",
                "city": "Sacramento"
            }
        ],
        "95816": [
            {
                "postcode": "95816",
                "state": "CA",
                "city": "Sacramento"
            }
        ]
    },
    "postcode_length": 3
}
EOD;

		$sut = new Country_Data( json_decode( $json_string ) );

		$result = $sut->get_locations_for_postcode( '958' );

		self::assertCount( 3, $result );
	}
}
