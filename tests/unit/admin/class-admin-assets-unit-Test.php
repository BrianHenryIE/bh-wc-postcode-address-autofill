<?php
/**
 * Tests for Admin.
 *
 * @see Admin_Assets
 *
 * @package brianhenryie/bh-wc-postcode-address-autofill
 * @author Brian Henry <BrianHenryIE@gmail.com>
 */

namespace BrianHenryIE\WC_Postcode_Address_Autofill\Admin;

use BrianHenryIE\WC_Postcode_Address_Autofill\Settings_Interface;

/**
 * Class Admin_Test
 *
 * @coversDefaultClass \BrianHenryIE\WC_Postcode_Address_Autofill\Admin\Admin_Assets
 */
class Admin_Assets_Test extends \Codeception\Test\Unit {

	protected function setup(): void {
		parent::setup();
		\WP_Mock::setUp();
	}

	protected function tearDown(): void {
		parent::tearDown();
		\WP_Mock::tearDown();
	}

	/**
	 * Verifies enqueue_styles() calls wp_enqueue_style() with appropriate parameters.
	 * Verifies the .css file exists.
	 *
	 * @covers ::enqueue_styles
	 * @see wp_enqueue_style()
	 */
	public function test_enqueue_styles(): void {

		global $plugin_root_dir;

		// Return any old url.
		\WP_Mock::userFunction(
			'plugin_dir_url',
			array(
				'return' => $plugin_root_dir . '/',
			)
		);

		$css_file = $plugin_root_dir . '/assets/bh-wc-postcode-address-autofill-admin.css';

		\WP_Mock::userFunction(
			'wp_enqueue_style',
			array(
				'times' => 1,
				'args'  => array( 'bh-wc-postcode-address-autofill', $css_file, array(), '1.0.0', 'all' ),
			)
		);

		$settings = $this->makeEmpty( Settings_Interface::class, array( 'get_plugin_version' => '1.0.0' ) );
		$admin    = new Admin_Assets( $settings );

		$admin->enqueue_styles();

		$this->assertFileExists( $css_file );
	}

	/**
	 * Verifies enqueue_scripts() calls wp_enqueue_script() with appropriate parameters.
	 * Verifies the .js file exists.
	 *
	 * @covers ::enqueue_scripts
	 * @see wp_enqueue_script()
	 */
	public function test_enqueue_scripts(): void {

		global $plugin_root_dir;

		// Return any old url.
		\WP_Mock::userFunction(
			'plugin_dir_url',
			array(
				'return' => $plugin_root_dir,
			)
		);

		$handle    = 'bh-wc-postcode-address-autofill';
		$src       = $plugin_root_dir . '/assets/bh-wc-postcode-address-autofill-admin.js';
		$deps      = array( 'jquery' );
		$ver       = '1.0.0';
		$in_footer = true;

		\WP_Mock::userFunction(
			'wp_enqueue_script',
			array(
				'times' => 1,
				'args'  => array( $handle, $src, $deps, $ver, $in_footer ),
			)
		);

		$settings = $this->makeEmpty( Settings_Interface::class, array( 'get_plugin_version' => $ver ) );
		$admin    = new Admin_Assets( $settings );

		$admin->enqueue_scripts();

		$this->assertFileExists( $src );
	}
}
