<?php
/**
 * Given a country and postcode, find the state and list of valid cities for that postcode
 *
 * @package brianhenryie/bh-wc-postcode-address-autofill
 */

namespace BrianHenryIE\WC_Postcode_Address_Autofill\API;

use BrianHenryIE\WC_Postcode_Address_Autofill\API_Interface;

/**
 * Reads saved files containing lookup tables.
 */
class API implements API_Interface {

	/**
	 * Uses the plugin basename to determine paths on the filesystem.
	 *
	 * @uses Settings::get_plugin_basename()
	 */
	protected Settings $settings;

	/**
	 * Constructor
	 *
	 * @param Settings $settings The plugin settings.
	 */
	public function __construct( Settings $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Look-up the list of valid cities for a given postcode in a given country.
	 *
	 * @param string $country The country, used to determine which file to load.
	 * @param string $postcode The postcode to search for.
	 *
	 * @return array{state: string, city: array<string>} Empty if not found.
	 */
	public function get_state_city_for_postcode( string $country, string $postcode ): array {

		$postcode = preg_replace( '/[^A-Za-z0-9]*/', '', sanitize_title( $postcode ) );
		$country  = strtolower( $country );
		$location = array(
			'state' => '',
			'city'  => array(),
		);

		switch ( $country ) {
			case 'jp':
				$states_index = array_flip( WC()->countries->get_states( 'JP' ) ?: array() );

				$japanese_states = array();

				$locations_by_postcode = array();
				$filename              = WP_PLUGIN_DIR . '/' . plugin_dir_path( $this->settings->get_plugin_basename() ) . 'data/jp_postal_codes.csv';
				$file                  = file( $filename ) ?: array();
				foreach ( $file as $line ) {
					$data = str_getcsv( $line );
					if ( ! isset( $locations_by_postcode[ $data[0] ] ) ) {
						$locations_by_postcode[ $data[0] ] = array();
					}
					$state_name = substr( $data[2], 0, - 4 ); // remove " ken".
					$city       = $data[1];
					$state      = isset( $states_index[ $state_name ] ) ? $states_index[ $state_name ] : '';

					$locations_by_postcode[ $data[0] ][] = array(
						'state' => $state,
						'city'  => $city,
					);

					$japanese_states[ $state_name ] = $state_name;
				}
				$locations = isset( $locations_by_postcode[ $postcode ] ) ? $locations_by_postcode[ $postcode ] : null;
				$location  = is_null( $locations ) ? null : $locations[0];

				break;

			case 'ie':
				$eircode_first_three = ucwords( substr( $postcode, 0, 3 ) );

				$counties_index = array_flip( WC()->countries->get_states( 'IE' ) ?: array() );

				$locations_by_postcode = array();
				$filename              = WP_PLUGIN_DIR . '/' . plugin_dir_path( $this->settings->get_plugin_basename() ) . 'data/postcodes-ie.csv';
				$file                  = file( $filename ) ?: array();
				foreach ( $file as $line ) {
					$data = str_getcsv( $line );
					if ( ! isset( $locations_by_postcode[ $data[0] ] ) ) {
						$locations_by_postcode[ $data[0] ] = array();
					}
					$state_name = $data[2];
					$city       = $data[1];
					$state      = isset( $counties_index[ $state_name ] ) ? $counties_index[ $state_name ] : '';

					$locations_by_postcode[ $data[0] ][] = array( $state, $city );

				}
				if ( isset( $locations_by_postcode[ $eircode_first_three ] ) ) {
					$first_location = array_pop( $locations_by_postcode[ $eircode_first_three ] );

					$location = array(
						'state' => $first_location[0],
						'city'  => $first_location[1],
					);
				}
				break;
			default:
				$file = WP_PLUGIN_DIR . '/' . plugin_dir_path( $this->settings->get_plugin_basename() ) . "data/postcodes-{$country}.json";
				if ( ! file_exists( $file ) ) {
					break;
				}

				// Legitimate use of file_get_contents().
				// phpcs:disable WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
				$json_string           = file_get_contents( $file ) ?: '';
				$locations_by_postcode = json_decode( $json_string, true );
				$locations             = isset( $locations_by_postcode[ $postcode ] ) ? $locations_by_postcode[ $postcode ] : array();
				foreach ( $locations as $found_location ) {
					$location['state']  = $found_location['state'];
					$location['city'][] = $found_location['city'];
				}
		}

		return $location;
	}
}
