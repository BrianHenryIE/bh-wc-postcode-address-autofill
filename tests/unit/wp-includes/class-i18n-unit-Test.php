<?php
/**
 *
 *
 * @package brianhenryie/bh-wc-checkout-address-suggestions
 * @author  BrianHenryIE <BrianHenryIE@gmail.com>
 */

namespace BrianHenryIE\WC_Checkout_Address_Suggestions\WP_Includes;

/**
 * Class Plugin_WP_Mock_Test
 *
 * @coversDefaultClass \BrianHenryIE\WC_Checkout_Address_Suggestions\WP_Includes\I18n
 */
class I18n_Unit_Test extends \Codeception\Test\Unit {

	protected function setup(): void {
		parent::setup();
		\WP_Mock::setUp();
	}

	protected function tearDown(): void {
		parent::tearDown();
		\WP_Mock::tearDown();
	}

	/**
	 * Verify load_plugin_textdomain is correctly called.
	 *
	 * @covers ::load_plugin_textdomain
	 */
	public function test_load_plugin_textdomain(): void {

		global $plugin_root_dir;

		\WP_Mock::userFunction(
			'plugin_basename',
			array(
				'args'   => array(
					\WP_Mock\Functions::type( 'string' ),
				),
				'return' => 'bh-wc-checkout-address-suggestions',
				'times'  => 1,
			)
		);

		\WP_Mock::userFunction(
			'load_plugin_textdomain',
			array(
				'times' => 1,
				'args'  => array(
					'bh-wc-checkout-address-suggestions',
					false,
					'bh-wc-checkout-address-suggestions/languages/',
				),
			)
		);

		$i18n = new I18n();
		$i18n->load_plugin_textdomain();
	}
}
