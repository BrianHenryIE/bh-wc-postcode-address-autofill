<?php
/**
 * Handle functions related to both WooCommerce checkout types.
 *
 * @package brianhenryie/bh-wc-postcode-address-autofill
 */

namespace BrianHenryIE\WC_Postcode_Address_Autofill\WooCommerce;

use BrianHenryIE\WC_Postcode_Address_Autofill\API_Interface;
use BrianHenryIE\WC_Postcode_Address_Autofill\Settings_Interface;

class Countries {
	/**
	 * @hooked woocommerce_get_country_locale
	 * @see \WC_Countries::get_country_locale()
	 */
	public function add_postcode_priority_to_country_locale( array $locale ): array {

		foreach ( $locale as $key => $value ) {
			if ( ! isset( $locale[ $key ]['postcode'] ) ) {
				$locale[ $key ]['postcode'] = array();
			}
			$locale[ $key ]['postcode']['priority'] = 65;
		}

		return $locale;
	}
}
