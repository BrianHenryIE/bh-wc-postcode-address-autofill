<?php
/**
 * The core functions required by the plugin.
 *
 * @package brianhenryie/bh-wc-checkout-address-suggestions
 */

namespace BrianHenryIE\WC_Checkout_Address_Suggestions;

/**
 * The interface for the heavy lifting of the plugin.
 */
interface API_Interface {

	/**
	 * Get the list of valid cities for a given country's postcode.
	 *
	 * @param string $country Two-character country code.
	 * @param string $postcode The postcode to search.
	 *
	 * @return array{state:string, city:array<string>}
	 */
	public function get_state_city_for_postcode( string $country, string $postcode ): array;
}
