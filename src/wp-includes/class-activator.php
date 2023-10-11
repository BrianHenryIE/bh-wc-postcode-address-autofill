<?php
/**
 * Fired during plugin activation
 *
 * @package brianhenryie/bh-wc-postcode-address-autofill
 */

namespace BrianHenryIE\WC_Postcode_Address_Autofill\WP_Includes;

use BrianHenryIE\WC_Postcode_Address_Autofill\API_Interface;

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 */
class Activator {

	/**
	 * Load the postcode data for the store's country into cache.
	 */
	public static function activate(): void {
		if ( did_action( 'woocommerce_loaded' ) ) {
			self::prepare_cache();
		} else {
			add_action( 'woocommerce_loaded', array( __CLASS__, 'prepare_cache' ) );
		}
	}

	/**
	 * Load the postcode data for the store's base country into cache.
	 */
	public static function prepare_cache(): void {

		if ( ! isset( $GLOBALS['bh_wc_postcode_address_autofill'] )
			|| ! $GLOBALS['bh_wc_postcode_address_autofill'] instanceof API_Interface ) {
			return;
		}

		$api = $GLOBALS['bh_wc_postcode_address_autofill'];

		$store_country = wc_get_base_location()['country'];

		$api->get_locations_for_postcode( $store_country, '' );
	}
}
