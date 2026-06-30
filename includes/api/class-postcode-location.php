<?php
/**
 * A single location in a postcode (there can be many).
 *
 * @package brianhenryie/bh-wc-postcode-address-autofill
 */

namespace BrianHenryIE\WC_Postcode_Address_Autofill\API;

use stdClass;

/**
 * Plain old object. Construct from JSON object array entry in data dir.
 */
class Postcode_Location {

	/**
	 * Full or partial postcode for this area.
	 */
	protected string $postcode;

	/**
	 * The state.
	 */
	protected string $state;

	/**
	 * The city.
	 */
	protected string $city;

	/**
	 * Constructor
	 *
	 * @param stdClass $json JSON sub-field from `/data/` directory.
	 */
	public function __construct( stdClass $json ) {
		$this->postcode = $json->postcode;
		$this->state    = $json->state;
		$this->city     = $json->city;
	}

	/**
	 * Get the postcode (may be full or partial) that matches this location.
	 */
	public function get_postcode(): string {
		return $this->postcode;
	}

	/**
	 * Get the state.
	 */
	public function get_state(): string {
		return $this->state;
	}

	/**
	 * Get the city.
	 */
	public function get_city(): string {
		return $this->city;
	}
}
