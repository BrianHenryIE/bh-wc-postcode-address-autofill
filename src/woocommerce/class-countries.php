<?php
/**
 * Manipulate the WooCommerce countries settings which affect both checkout types.
 *
 * @package brianhenryie/bh-wc-postcode-address-autofill
 */

namespace BrianHenryIE\WC_Postcode_Address_Autofill\WooCommerce;

/**
 * Filter the WooCommerce country locale settings.
 *
 * @phpstan-type FieldProperties array{priority?:int,required?:bool,hidden?:bool,label?:string,class?:array<string>}
 */
class Countries {
	/**
	 * Ensure the postcode field's priority is higher than the city field's.
	 *
	 * Every country has different settings for ordering the checkout fields. Loop over each one and set the
	 * postcode priority.
	 *
	 * @hooked woocommerce_get_country_locale
	 * @see \WC_Countries::get_country_locale()
	 *
	 * @param array<string,array<string,FieldProperties>> $locale Array keyed by country code, containing a map of field-name:properties.
	 *
	 * @return array<string,array<string,FieldProperties>>
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
