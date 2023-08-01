<?php

namespace BrianHenryIE\WC_Postcode_Address_Autofill\WooCommerce;

/**
 * @coversDefaultClass \BrianHenryIE\WC_Postcode_Address_Autofill\WooCommerce\Countries
 */
class Countries_Unit_Test extends \Codeception\Test\Unit {
	/**
	 * Test data taken from `WC_Countries::get_country_locale()` and translation functions removed using
	 * regex `__\(\s('.*?'),\s'\w*'\s\)`.
	 *
	 * @covers ::add_postcode_priority_to_country_locale
	 * @see \WC_Countries::get_country_locale()
	 */
	public function test_woocommerce_get_country_locale(): void {

		$sut = new Countries();

		$locales = array(
			'IE' => array(
				'postcode' => array(
					'required' => false,
					'label'    => 'Eircode',
				),
				'state'    => array(
					'label' => 'County',
				),
			),
			'JP' => array(
				'last_name'  => array(
					'class'    => array( 'form-row-first' ),
					'priority' => 10,
				),
				'first_name' => array(
					'class'    => array( 'form-row-last' ),
					'priority' => 20,
				),
				'postcode'   => array(
					'class'    => array( 'form-row-first', 'address-field' ),
					'priority' => 65,
				),
				'state'      => array(
					'label'    => 'Prefecture',
					'class'    => array( 'form-row-last', 'address-field' ),
					'priority' => 66,
				),
				'city'       => array(
					'priority' => 67,
				),
				'address_1'  => array(
					'priority' => 68,
				),
				'address_2'  => array(
					'priority' => 69,
				),
			),
			'US' => array(
				'postcode' => array(
					'label' => 'ZIP Code',
				),
				'state'    => array(
					'label' => 'State',
				),
			),
			'ZA' => array(
				'state' => array(
					'label' => __( 'Province', 'woocommerce' ),
				),
			),
		);

		$result = $sut->add_postcode_priority_to_country_locale( $locales );

		self::assertEquals( 65, $result['US']['postcode']['priority'] );
	}
}
