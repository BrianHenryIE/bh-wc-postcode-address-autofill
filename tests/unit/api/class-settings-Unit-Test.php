<?php

namespace BrianHenryIE\WC_Postcode_Address_Autofill\API;

/**
 * @coversDefaultClass \BrianHenryIE\WC_Postcode_Address_Autofill\API\Settings
 */
class Settings_Unit_Test extends \Codeception\Test\Unit {

	/**
	 * @covers ::get_plugin_version
	 */
	public function test_get_plugin_version_concur(): void {
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
	 * @covers ::get_plugin_version
	 */
	public function test_get_plugin_version(): void {

		\Patchwork\redefine(
			'defined',
			function ( string $constant_value ): bool {
				return false;
			}
		);

		$sut = new Settings();

		self::assertEquals( '1.1.0', $sut->get_plugin_version() );

		\Patchwork\restoreAll();
	}

	/**
	 * @covers ::get_plugin_version
	 */
	public function test_get_plugin_version_defined(): void {

		\Patchwork\redefine(
			'defined',
			function ( string $constant_value ): bool {
				return true;
			}
		);

		\Patchwork\redefine(
			'constant',
			function ( string $constant_value ): string {
				return '1.0.1d';
			}
		);

		$sut = new Settings();

		self::assertEquals( '1.0.1d', $sut->get_plugin_version() );

		\Patchwork\restoreAll();
	}

	/**
	 * @covers ::get_plugin_basename
	 */
	public function test_get_plugin_basename(): void {

		\Patchwork\redefine(
			'defined',
			function ( string $constant_value ): bool {
				return false;
			}
		);

		$sut = new Settings();

		self::assertEquals( 'bh-wc-postcode-address-autofill/bh-wc-postcode-address-autofill.php', $sut->get_plugin_basename() );

		\Patchwork\restoreAll();
	}

	/**
	 * @covers ::get_plugin_basename
	 */
	public function test_get_plugin_basename_defined(): void {

		\Patchwork\redefine(
			'defined',
			function ( string $constant_value ): bool {
				return true;
			}
		);

		\Patchwork\redefine(
			'constant',
			function ( string $constant_value ): string {
				return 'defined-bh-wc-postcode-address-autofill/bh-wc-postcode-address-autofill.php';
			}
		);

		$sut = new Settings();

		self::assertEquals( 'defined-bh-wc-postcode-address-autofill/bh-wc-postcode-address-autofill.php', $sut->get_plugin_basename() );

		\Patchwork\restoreAll();
	}
}
