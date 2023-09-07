<?php
/**
 * Tests for BH_WC_Postcode_Address_Autofill main setup class. Tests the actions are correctly added.
 *
 * @package brianhenryie/bh-wc-postcode-address-autofill
 * @author  BrianHenryIE <BrianHenryIE@gmail.com>
 */

namespace BrianHenryIE\WC_Postcode_Address_Autofill;

use BrianHenryIE\WC_Postcode_Address_Autofill\WooCommerce\Checkout_Shortcode;
use BrianHenryIE\WC_Postcode_Address_Autofill\WP_Includes\I18n;

/**
 * Class Develop_Test
 */
class BH_WC_Postcode_Address_Autofill_Integration_Test extends \Codeception\TestCase\WPTestCase {

	public function hooks(): array {
		$hooks = array(
			array( 'init', I18n::class, 'load_plugin_textdomain' ),
			array( 'wp_enqueue_scripts', Checkout_Shortcode::class, 'enqueue_scripts' ),
		);
		return $hooks;
	}

	/**
	 * @dataProvider hooks
	 *
	 * @param string $action_name The expected action or filter name.
	 * @param string $class_type The class type hooked to the action.
	 * @param string $method_name The method name expected.
	 * @param int    $expected_priority The priority used to hook the action.
	 */
	public function test_is_function_hooked_on_action( string $action_name, string $class_type, string $method_name, int $expected_priority = 10 ): void {

		global $wp_filter;

		$this->assertArrayHasKey( $action_name, $wp_filter, "$method_name definitely not hooked to $action_name" );

		$actions_hooked = $wp_filter[ $action_name ];

		$this->assertArrayHasKey( $expected_priority, $actions_hooked, "$method_name definitely not hooked to $action_name priority $expected_priority" );

		$hooked_method = null;
		foreach ( $actions_hooked[ $expected_priority ] as $action ) {
			$action_function = $action['function'];
			if ( is_array( $action_function ) ) {
				if ( $action_function[0] instanceof $class_type ) {
					if ( $method_name === $action_function[1] ) {
						$hooked_method = $action_function[1];
						break;
					}
				}
			}
		}

		$this->assertNotNull( $hooked_method, "No methods on an instance of $class_type hooked to $action_name" );

		$this->assertEquals( $method_name, $hooked_method, "Unexpected method name for $class_type class hooked to $action_name" );
	}
}
