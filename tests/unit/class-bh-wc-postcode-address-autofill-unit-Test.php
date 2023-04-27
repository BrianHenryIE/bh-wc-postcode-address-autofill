<?php
/**
 * @package brianhenryie/bh-wc-postcode-address-autofill
 * @author  BrianHenryIE <BrianHenryIE@gmail.com>
 */

namespace BrianHenryIE\WC_Postcode_Address_Autofill;

use BrianHenryIE\WC_Postcode_Address_Autofill\Admin\Admin_Assets;
use BrianHenryIE\WC_Postcode_Address_Autofill\API\API;
use BrianHenryIE\WC_Postcode_Address_Autofill\WooCommerce\Checkout;
use BrianHenryIE\WC_Postcode_Address_Autofill\WP_Includes\I18n;
use WP_Mock\Matcher\AnyInstance;

/**
 * Class BH_WC_Postcode_Address_Autofill_Unit_Test
 *
 * @coversDefaultClass \BrianHenryIE\WC_Postcode_Address_Autofill\BH_WC_Postcode_Address_Autofill
 */
class BH_WC_Postcode_Address_Autofill_Unit_Test extends \Codeception\Test\Unit {

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
		new BH_WC_Postcode_Address_Autofill( $api, $settings );
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
		new BH_WC_Postcode_Address_Autofill( $api, $settings );
	}

}
