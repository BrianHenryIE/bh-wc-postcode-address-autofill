<?php
/**
 * @package brianhenryie/bh-wc-postcode-address-autofill
 * @author  BrianHenryIE <BrianHenryIE@gmail.com>
 */

namespace BrianHenryIE\WC_Postcode_Address_Autofill;

use BrianHenryIE\WC_Postcode_Address_Autofill\API\API;
use BrianHenryIE\WC_Postcode_Address_Autofill\WooCommerce\Blocks;
use BrianHenryIE\WC_Postcode_Address_Autofill\WooCommerce\Checkout_Blocks;
use BrianHenryIE\WC_Postcode_Address_Autofill\WooCommerce\Checkout_Shortcode;
use BrianHenryIE\WC_Postcode_Address_Autofill\WooCommerce\Countries;
use BrianHenryIE\WC_Postcode_Address_Autofill\WooCommerce\Features;
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
	 * @covers ::__construct
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
	 * @covers ::define_woocommerce_countries_hooks
	 */
	public function test_define_woocommerce_countries_hooks(): void {
		\WP_Mock::expectFilterAdded(
			'woocommerce_get_country_locale',
			array( new AnyInstance( Countries::class ), 'add_postcode_priority_to_country_locale' )
		);

		$api      = $this->makeEmpty( API::class );
		$settings = $this->makeEmpty( Settings_Interface::class );
		new BH_WC_Postcode_Address_Autofill( $api, $settings );
	}

	/**
	 * @covers ::define_woocommerce_shortcode_checkout_hooks
	 */
	public function test_woocommerce_shortcode_checkout_hooks(): void {
		\WP_Mock::expectActionAdded(
			'wp_enqueue_scripts',
			array( new AnyInstance( Checkout_Shortcode::class ), 'enqueue_scripts' )
		);

		\WP_Mock::expectActionAdded(
			'woocommerce_checkout_update_order_review',
			array( new AnyInstance( Checkout_Shortcode::class ), 'parse_post_on_update_order_review' )
		);

		$api      = $this->makeEmpty( API::class );
		$settings = $this->makeEmpty( Settings_Interface::class );
		new BH_WC_Postcode_Address_Autofill( $api, $settings );
	}

	/**
	 * @covers ::define_woocommerce_blocks_checkout_hooks
	 */
	public function test_define_woocommerce_blocks_checkout_hooks(): void {
		\WP_Mock::expectActionAdded(
			'woocommerce_blocks_loaded',
			array( new AnyInstance( Blocks::class ), 'register_update_callback' )
		);

		\WP_Mock::expectActionAdded(
			'woocommerce_blocks_checkout_block_registration',
			array( new AnyInstance( Blocks::class ), 'register_integration' )
		);

		$api      = $this->makeEmpty( API::class );
		$settings = $this->makeEmpty( Settings_Interface::class );
		new BH_WC_Postcode_Address_Autofill( $api, $settings );
	}

	/**
	 * @covers ::define_woocommerce_features_hooks
	 */
	public function test_define_woocommerce_features_hooks(): void {
		\WP_Mock::expectActionAdded(
			'before_woocommerce_init',
			array( new AnyInstance( Features::class ), 'declare_cart_checkout_blocks_compatibility' )
		);
		\WP_Mock::expectActionAdded(
			'before_woocommerce_init',
			array( new AnyInstance( Features::class ), 'declare_hpos_compatibility' )
		);

		$api      = $this->makeEmpty( API::class );
		$settings = $this->makeEmpty( Settings_Interface::class );
		new BH_WC_Postcode_Address_Autofill( $api, $settings );
	}
}
