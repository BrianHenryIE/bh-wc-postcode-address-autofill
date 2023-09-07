<?php

namespace BrianHenryIE\WC_Postcode_Address_Autofill\WooCommerce;

use BrianHenryIE\WC_Postcode_Address_Autofill\API\Postcode_Location;
use BrianHenryIE\WC_Postcode_Address_Autofill\API\Postcode_Locations_Result;
use BrianHenryIE\WC_Postcode_Address_Autofill\API_Interface;
use BrianHenryIE\WC_Postcode_Address_Autofill\Settings_Interface;
use Codeception\Stub\Expected;

/**
 * @coversDefaultClass \BrianHenryIE\WC_Postcode_Address_Autofill\WooCommerce\Checkout_Shortcode
 *
 * phpcs:disable Squiz.Commenting.VariableComment.Missing
 * phpcs:disable WordPress.Security.NonceVerification.Missing
 */
class Checkout_Shortcode_Unit_Test extends \Codeception\Test\Unit {

	protected function setup(): void {
		parent::setup();
		\WP_Mock::setUp();
	}

	protected function tearDown(): void {
		parent::tearDown();
		\WP_Mock::tearDown();
	}

	/**
	 * @covers ::__construct
	 * @covers ::enqueue_scripts
	 */
	public function test_enqueue_scripts(): void {

		\WP_Mock::passthruFunction( 'plugin_dir_url' );

		$handle    = 'bh-wc-postcode-address-autofill-checkout';
		$src       = 'assets/bh-wc-postcode-address-autofill-checkout.js';
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

		\WP_Mock::userFunction(
			'is_checkout',
			array(
				'times'  => 1,
				'return' => true,
			)
		);

		$api      = self::makeEmpty( API_Interface::class );
		$settings = self::makeEmpty( Settings_Interface::class, array( 'get_plugin_version' => $ver ) );
		$sut      = new Checkout_Shortcode( $api, $settings );

		$sut->enqueue_scripts();

		$this->assertFileExists( $src );
	}

	/**
	 * @covers ::__construct
	 * @covers ::enqueue_scripts
	 */
	public function test_enqueue_scripts_not_on_checkout(): void {

		\WP_Mock::passthruFunction( 'plugin_dir_url' );

		\WP_Mock::userFunction(
			'wp_enqueue_script',
			array(
				'times' => 0,
			)
		);

		\WP_Mock::userFunction(
			'is_checkout',
			array(
				'times'  => 1,
				'return' => false,
			)
		);

		$api      = self::makeEmpty( API_Interface::class );
		$settings = self::makeEmpty( Settings_Interface::class );
		$sut      = new Checkout_Shortcode( $api, $settings );

		$sut->enqueue_scripts();
	}

	/**
	 * @covers ::parse_post_on_update_order_review
	 */
	public function test_parse_post_on_update_order_review(): void {

		$posted_data = 'billing_first_name=&billing_last_name=&billing_company=&billing_country=US&billing_address_1=&billing_address_2=&billing_postcode=10001&billing_city=BEVERLY%20HILLS&billing_state=CA&billing_phone=&billing_email=admin%40example.org&order_comments=&woocommerce-process-checkout-nonce=e05ebe4c4c&_wp_http_referer=%2Fbh-wc-postcode-address-autofill%2F%3Fwc-ajax%3Dupdate_order_review';

		$api      = self::makeEmpty(
			API_Interface::class,
			array(
				'get_locations_for_postcode' => Expected::once(
					self::makeEmpty(
						Postcode_Locations_Result::class,
						array(
							'get_first' => Expected::once(
								self::makeEmpty(
									Postcode_Location::class,
									array(
										'get_state' => 'CA',
										'get_city'  => 'Sacramento',
									)
								)
							),
						)
					),
				),
			)
		);
		$settings = self::makeEmpty( Settings_Interface::class );
		$sut      = new Checkout_Shortcode( $api, $settings );

		$wc = new class() {
			public $session;
		};

		$wc->session = new class() {
			public function get( string $key ) {
				return null;
			}
		};

		\WP_Mock::userFunction(
			'WC',
			array(
				'times'  => 1,
				'return' => $wc,
			)
		);

		\WP_Mock::expectFilterAdded(
			'woocommerce_update_order_review_fragments',
			array( $sut, 'rerender_billing_fields_fragment' )
		);

		$sut->parse_post_on_update_order_review( $posted_data );

		self::assertEquals( $_POST['city'], 'Sacramento' );
	}

	/**
	 * @covers ::parse_post_on_update_order_review
	 */
	public function test_parse_post_on_update_order_review_postcode_not_updated(): void {

		$posted_data = 'billing_first_name=&billing_last_name=&billing_company=&billing_country=US&billing_address_1=&billing_address_2=&billing_postcode=10001&billing_city=BEVERLY%20HILLS&billing_state=CA&billing_phone=&billing_email=admin%40example.org&order_comments=&woocommerce-process-checkout-nonce=e05ebe4c4c&_wp_http_referer=%2Fbh-wc-postcode-address-autofill%2F%3Fwc-ajax%3Dupdate_order_review';

		$api      = self::makeEmpty( API_Interface::class, array( 'get_locations_for_postcode' => Expected::never() ) );
		$settings = self::makeEmpty( Settings_Interface::class );
		$sut      = new Checkout_Shortcode( $api, $settings );

		$wc = new class() {
			public $session;
		};

		$wc->session = new class() {
			public function get( string $key ) {
				return array( 'postcode' => '10001' );
			}
		};

		\WP_Mock::userFunction(
			'WC',
			array(
				'times'  => 1,
				'return' => $wc,
			)
		);

		$sut->parse_post_on_update_order_review( $posted_data );
	}

	/**
	 * @covers ::parse_post_on_update_order_review
	 */
	public function test_parse_post_on_update_order_review_no_postcode_data(): void {

		$posted_data = 'billing_first_name=&billing_last_name=&billing_company=&billing_country=US&billing_address_1=&billing_address_2=&billing_postcode=10001&billing_city=BEVERLY%20HILLS&billing_state=CA&billing_phone=&billing_email=admin%40example.org&order_comments=&woocommerce-process-checkout-nonce=e05ebe4c4c&_wp_http_referer=%2Fbh-wc-postcode-address-autofill%2F%3Fwc-ajax%3Dupdate_order_review';

		$api      = self::makeEmpty(
			API_Interface::class,
			array(
				'get_locations_for_postcode' => Expected::once(
					null
				),
			)
		);
		$settings = self::makeEmpty( Settings_Interface::class );
		$sut      = new Checkout_Shortcode( $api, $settings );

		$wc = new class() {
			public $session;
		};

		$wc->session = new class() {
			public function get( string $key ) {
				return null;
			}
		};

		\WP_Mock::userFunction(
			'WC',
			array(
				'times'  => 1,
				'return' => $wc,
			)
		);

		\WP_Mock::expectFilterNotAdded(
			'woocommerce_update_order_review_fragments',
			array( $sut, 'rerender_billing_fields_fragment' )
		);

		$sut->parse_post_on_update_order_review( $posted_data );
	}

	/**
	 * @covers ::parse_post_on_update_order_review
	 */
	public function test_parse_post_on_update_order_review_not_enough_posted_data(): void {

		$posted_data = 'billing_first_name=&billing_last_name=&billing_company=&billing_country=US&billing_address_1=&billing_address_2=&billing_postcode=&billing_city=BEVERLY%20HILLS&billing_state=CA&billing_phone=&billing_email=admin%40example.org&order_comments=&woocommerce-process-checkout-nonce=e05ebe4c4c&_wp_http_referer=%2Fbh-wc-postcode-address-autofill%2F%3Fwc-ajax%3Dupdate_order_review';

		$api      = self::makeEmpty(
			API_Interface::class,
			array(
				'get_locations_for_postcode' => Expected::never(),
			)
		);
		$settings = self::makeEmpty( Settings_Interface::class );
		$sut      = new Checkout_Shortcode( $api, $settings );

		$sut->parse_post_on_update_order_review( $posted_data );
	}

	/**
	 * @covers ::parse_post_on_update_order_review
	 */
	public function test_parse_post_on_update_order_review_city_state_already_correct(): void {

		$posted_data = 'billing_first_name=&billing_last_name=&billing_company=&billing_country=US&billing_address_1=&billing_address_2=&billing_postcode=90210&billing_city=BEVERLY%20HILLS&billing_state=CA&billing_phone=&billing_email=admin%40example.org&order_comments=&woocommerce-process-checkout-nonce=e05ebe4c4c&_wp_http_referer=%2Fbh-wc-postcode-address-autofill%2F%3Fwc-ajax%3Dupdate_order_review';

		$api      = self::makeEmpty(
			API_Interface::class,
			array(
				'get_locations_for_postcode' => Expected::once(
					self::makeEmpty(
						Postcode_Locations_Result::class,
						array(
							'get_first' => Expected::once(
								self::makeEmpty(
									Postcode_Location::class,
									array(
										'get_state' => 'CA',
										'get_city'  => 'BEVERLY HILLS',
									)
								)
							),
						)
					),
				),
			)
		);
		$settings = self::makeEmpty( Settings_Interface::class );
		$sut      = new Checkout_Shortcode( $api, $settings );

		$wc = new class() {
			public $session;
		};

		$wc->session = new class() {
			public function get( string $key ) {
				return null;
			}
		};

		\WP_Mock::userFunction(
			'WC',
			array(
				'times'  => 1,
				'return' => $wc,
			)
		);

		\WP_Mock::expectFilterNotAdded(
			'woocommerce_update_order_review_fragments',
			array( $sut, 'rerender_billing_fields_fragment' )
		);

		$sut->parse_post_on_update_order_review( $posted_data );
	}
}
