<?php
/**
 * Given a country and postcode, find the state and list of valid cities for that postcode
 *
 * @package brianhenryie/bh-wc-postcode-address-autofill
 */

namespace BrianHenryIE\WC_Postcode_Address_Autofill\API;

use BrianHenryIE\WC_Postcode_Address_Autofill\API_Interface;
use BrianHenryIE\WC_Postcode_Address_Autofill\Settings_Interface;

/**
 * Reads saved files containing lookup tables.
 */
class API implements API_Interface {

	/**
	 * Had been using the plugin basename to determine paths on the filesystem.
	 *
	 * @uses Settings::get_plugin_basename()
	 */
	protected Settings_Interface $settings;

	/**
	 * Object to fetch data from cache/db/disk.
	 */
	protected Data_Loader $data_loader;

	/**
	 * Constructor
	 *
	 * @param Data_Loader        $data_loader Object to fetch data from cache/db/disk.
	 * @param Settings_Interface $settings The plugin settings.
	 */
	public function __construct( Data_Loader $data_loader, Settings_Interface $settings ) {
		$this->settings    = $settings;
		$this->data_loader = $data_loader;
	}

	/**
	 * Look-up the list of valid cities for a given postcode in a given country.
	 *
	 * @param string $country The country, used to determine which file to load.
	 * @param string $postcode The postcode to search for.
	 *
	 * @return ?Postcode_Locations_Result Null if no data available, empty if nothing found.
	 */
	public function get_locations_for_postcode( string $country, string $postcode ): ?Postcode_Locations_Result {
		$country = strtoupper( $country );

		$country_data = $this->data_loader->get_data_for_country( $country );

		if ( is_null( $country_data ) ) {
			return null;
		}

		return $country_data->get_locations_for_postcode( $postcode );
	}
}
