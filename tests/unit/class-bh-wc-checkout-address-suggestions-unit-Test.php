<?php
/**
 * @package brianhenryie/bh-wc-checkout-address-suggestions
 * @author  BrianHenryIE <BrianHenryIE@gmail.com>
 */

namespace BrianHenryIE\WC_Checkout_Address_Suggestions;

use BrianHenryIE\WC_Checkout_Address_Suggestions\Admin\Admin_Assets;
use BrianHenryIE\WC_Checkout_Address_Suggestions\API\API;
use BrianHenryIE\WC_Checkout_Address_Suggestions\WooCommerce\Checkout;
use BrianHenryIE\WC_Checkout_Address_Suggestions\WP_Includes\I18n;
use WP_Mock\Matcher\AnyInstance;

/**
 * Class BH_WC_Checkout_Address_Suggestions_Unit_Test
 *
 * @coversDefaultClass \BrianHenryIE\WC_Checkout_Address_Suggestions\BH_WC_Checkout_Address_Suggestions
 */
class BH_WC_Checkout_Address_Suggestions_Unit_Test extends \Codeception\Test\Unit {

	protected function setup(): void {
		parent::setup();
		\WP_Mock::setUp();
	}

	protected function tearDown(): void {
		parent::tearDown();
		\WP_Mock::tearDown();
	}

	/**
	 * @covers ::set_locale
	 */
	public function test_set_locale_hooked(): void {

		\WP_Mock::expectActionAdded(
			'init',
			array( new AnyInstance( I18n::class ), 'load_plugin_textdomain' )
		);

		$api      = $this->makeEmpty( API::class );
		$settings = $this->makeEmpty( Settings_Interface::class );
		new BH_WC_Checkout_Address_Suggestions( $api, $settings );
	}

	/**
	 * @covers ::define_admin_hooks
	 */
	public function test_admin_hooks(): void {

		\WP_Mock::expectActionAdded(
			'admin_enqueue_scripts',
			array( new AnyInstance( Admin_Assets::class ), 'enqueue_styles' )
		);

		\WP_Mock::expectActionAdded(
			'admin_enqueue_scripts',
			array( new AnyInstance( Admin_Assets::class ), 'enqueue_scripts' )
		);

		$api      = $this->makeEmpty( API::class );
		$settings = $this->makeEmpty( Settings_Interface::class );
		new BH_WC_Checkout_Address_Suggestions( $api, $settings );
	}

	/**
	 * @covers ::define_woocommerce_checkout_hooks
	 */
	public function test_woocommerce_checkout_hooks(): void {
		\WP_Mock::expectActionAdded(
			'wp_enqueue_scripts',
			array( new AnyInstance( Checkout::class ), 'enqueue_scripts' )
		);

		$api      = $this->makeEmpty( API::class );
		$settings = $this->makeEmpty( Settings_Interface::class );
		new BH_WC_Checkout_Address_Suggestions( $api, $settings );
	}

}
