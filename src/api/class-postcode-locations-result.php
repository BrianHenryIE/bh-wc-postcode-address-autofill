<?php
/**
 * A set of valid locations for a postcode.
 *
 * @package brianhenryie/bh-wc-postcode-address-autofill
 */

namespace BrianHenryIE\WC_Postcode_Address_Autofill\API;

/**
 * @template-extends \ArrayObject<array-key, Postcode_Location>
 */
class Postcode_Locations_Result extends \ArrayObject {

	/**
	 * Simple logic to get the first correct location for a postcode.
	 *
	 * In future, this might be augmented with user-specific data, or locations might be sorted by population.
	 *
	 * @return ?Postcode_Location
	 */
	public function get_first(): ?Postcode_Location {
		return $this->getIterator()->valid() ? $this->getIterator()->current() : null;
	}
}
