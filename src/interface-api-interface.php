<?php
/**
 * The core functions required by the plugin.
 *
 * @package brianhenryie/bh-wc-postcode-address-autofill
 */

namespace BrianHenryIE\WC_Postcode_Address_Autofill;

use BrianHenryIE\WC_Postcode_Address_Autofill\API\Postcode_Locations_Result;

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
	 * @return ?Postcode_Locations_Result
	 */
	public function get_locations_for_postcode( string $country, string $postcode ): ?Postcode_Locations_Result;
}
