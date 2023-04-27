<?php

namespace BrianHenryIE\WC_Postcode_Address_Autofill\API;

/**
 * @coversDefaultClass \BrianHenryIE\WC_Postcode_Address_Autofill\API\Settings
 */
class Settings_Unit_Test extends \Codeception\Test\Unit {

	/**
	 * @covers ::get_plugin_version
	 */
	public function test_get_plugin_version(): void {
		$sut = new Settings();

		global $plugin_path_php;

		$root_plugin_file = file_get_contents( $plugin_path_php );

		if ( false === preg_match_all( '/.*version.*(\d+\.\d+\.\d+)/i', $root_plugin_file, $output_array ) ) {
			self::fail( 'Failed to parse versions from root plugin file.' );
		}

		self::assertEquals( $output_array[1][0], $sut->get_plugin_version() );
		self::assertEquals( $output_array[1][1], $sut->get_plugin_version() );
	}

	/**
	 * @covers ::get_plugin_basename
	 */
	public function test_get_plugin_basename(): void {
		$sut = new Settings();

		self::assertEquals( 'bh-wc-postcode-address-autofill/bh-wc-postcode-address-autofill.php', $sut->get_plugin_basename() );
	}
}
