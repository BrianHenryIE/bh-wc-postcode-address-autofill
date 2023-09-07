<?php
/**
 * Object containing all postcode data for a specific country, with one method for querying.
 *
 * @package brianhenryie/bh-wc-postcode-address-autofill
 */

namespace BrianHenryIE\WC_Postcode_Address_Autofill\API;

use stdClass;

/**
 * Construct via json object saved in /data/ folder, query with partial, exact or extended postcode.
 */
class Country_Data {

	/**
	 * The max length that a match can be made for this country's postcode.
	 *
	 * E.g. US postcodes may be entered as nine characters but only match on the first five.
	 */
	protected int $postcode_length;

	/**
	 * A list, indexed by postcode, of state-city pairs in that postcode.
	 *
	 * @var array<string, array<Postcode_Location>>
	 */
	protected array $postcode_locations = array();

	/**
	 * Constructor
	 *
	 * @param stdClass $json JSON object from data directory.
	 */
	public function __construct( stdClass $json ) {
		if ( isset( $json->postcode_length ) ) {
			$this->postcode_length = $json->postcode_length;
		}
		if ( isset( $json->postcode_locations ) ) {
			foreach ( $json->postcode_locations as $postcode => $locations ) {
				if ( ! isset( $this->postcode_locations[ $postcode ] ) ) {
					$this->postcode_locations[ (string) $postcode ] = array();
				}
				foreach ( $locations as $location ) {
					$this->postcode_locations[ (string) $location->postcode ][] = new Postcode_Location( $location );
				}
			}
		}
	}

	/**
	 * Given a partial postcode, return all matches (e.g. 958xx),
	 * given a full postcode, return the match (e.g. 95819),
	 * given an overly specific postcode, droplast() until finding a match
	 *
	 * @param string $postcode The partial, complete, or extended postcode to match.
	 */
	public function get_locations_for_postcode( string $postcode ): ?Postcode_Locations_Result {
		if ( strlen( $postcode ) > $this->postcode_length ) {
			$postcode = substr( $postcode, 0, $this->postcode_length );
		}

		$result = new Postcode_Locations_Result();

		if ( isset( $this->postcode_locations[ $postcode ] ) ) {
			foreach ( $this->postcode_locations[ $postcode ] as $location ) {
				$result->append( $location );
			}
			return $result;
		}

		$match_length = strlen( $postcode ) - 1;
		do {
			$postcode = substr( $postcode, 0, $match_length );

			foreach ( $this->postcode_locations as $postcode_index => $postcode_location ) {
				if ( 0 === strpos( $postcode_index, $postcode ) ) {
					foreach ( $this->postcode_locations[ $postcode_index ] as $location ) {
						$result->append( $location );
					}
				}
			}
			--$match_length;
		} while ( 0 === $result->count() && $match_length > 1 );

		return 0 !== $result->count() ? $result : null;
	}
}
