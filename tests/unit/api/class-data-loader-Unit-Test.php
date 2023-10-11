<?php

namespace BrianHenryIE\WC_Postcode_Address_Autofill\API;

use BrianHenryIE\WC_Postcode_Address_Autofill\Settings_Interface;

/**
 * @coversDefaultClass \BrianHenryIE\WC_Postcode_Address_Autofill\API\Data_Loader
 */
class Data_Loader_Unit_Test extends \Codeception\Test\Unit {

	protected function setup(): void {
		parent::setup();
		\WP_Mock::setUp();
	}

	protected function tearDown(): void {
		parent::tearDown();
		\WP_Mock::tearDown();
		\Patchwork\restoreAll();
	}

	public function test_get_data_for_country_no_data(): void {

		\WP_Mock::userFunction(
			'wp_cache_get',
			array(
				'times'  => 1,
				'args'   => array( 'NaN', 'bh-wc-postcode-address-autofill' ),
				'return' => false,
			)
		);

		$settings = self::makeEmpty(
			Settings_Interface::class,
			array(
				'get_plugin_basename' => 'bh-wc-postcode-address-autofill/bh-wc-postcode-address-autofill.php',
			)
		);

		global $project_root_dir;

		\Patchwork\redefine(
			'constant',
			function ( string $constant_value ) use ( $project_root_dir ): string {
				return $project_root_dir;
			}
		);

		\WP_Mock::userFunction(
			'plugin_dir_path',
			array(
				'times'  => 1,
				'args'   => array( 'bh-wc-postcode-address-autofill/bh-wc-postcode-address-autofill.php' ),
				'return' => 'bh-wc-postcode-address-autofill/',
			)
		);

		$sut = new Data_Loader( $settings );

		$result = $sut->get_data_for_country( 'NaN' );

		self::assertNull( $result );
	}

	public function test_get_data_for_country_data_ie(): void {

		$settings = self::makeEmpty(
			Settings_Interface::class,
			array(
				'get_plugin_basename' => 'bh-wc-postcode-address-autofill/bh-wc-postcode-address-autofill.php',
			)
		);

		\WP_Mock::userFunction(
			'wp_cache_get',
			array(
				'times'  => 1,
				'args'   => array( 'ie', 'bh-wc-postcode-address-autofill' ),
				'return' => false,
			)
		);

		global $project_root_dir;

		\Patchwork\redefine(
			'constant',
			function ( string $constant_value ) use ( $project_root_dir ): string {
				return dirname( $project_root_dir );
			}
		);

		\WP_Mock::userFunction(
			'plugin_dir_path',
			array(
				'times'  => 2,
				'args'   => array( 'bh-wc-postcode-address-autofill/bh-wc-postcode-address-autofill.php' ),
				'return' => 'bh-wc-postcode-address-autofill/',
			)
		);

		// `ob_get_clean` was being appended with `;\Patchwork\CodeManipulation\Stream::reinstateWrapper();` making the JSON invalid.
		\Patchwork\redefine(
			'ob_get_clean',
			function () use ( $project_root_dir ): string {
				\Patchwork\relay();
				return file_get_contents( $project_root_dir . '/data/ie.json' );
			}
		);

		\WP_Mock::userFunction(
			'wp_cache_set',
			array(
				'times'  => 1,
				'args'   => array(
					'ie',
					\WP_Mock\Functions::type( Country_Data::class ),
					'bh-wc-postcode-address-autofill',
				),
				'return' => true,
			)
		);

		$sut = new Data_Loader( $settings );

		$result = $sut->get_data_for_country( 'ie' );

		self::assertNotNull( $result );
	}

	public function test_get_data_for_country_cached(): void {

		$cached_data = self::make( Country_Data::class );

		\WP_Mock::userFunction(
			'wp_cache_get',
			array(
				'times'  => 1,
				'args'   => array( 'ie', 'bh-wc-postcode-address-autofill' ),
				'return' => $cached_data,
			)
		);

		$settings = self::makeEmpty( Settings_Interface::class );

		$sut = new Data_Loader( $settings );

		$result = $sut->get_data_for_country( 'ie' );

		self::assertSame( $cached_data, $result );
	}
}
