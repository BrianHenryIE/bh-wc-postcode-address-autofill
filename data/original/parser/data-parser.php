<?php
/**
 * Parse different countries' datasets into a common JSON format.
 * Map states from datasets to valid states in WooCommerce.
 *
 * @package brianhenryie/bh-wc-postcode-address-autofill
 */

$countries = array( 'in', 'ie', 'jp', 'us' );

define( 'ABSPATH', 'fake-abspath!' );
function __( $text, $translation_domain ) {
	return $text;
}
function _x( $text, $context, $translation_domain ) {
	return $text;
}
$plugin_root_dir = __DIR__ . '/../../../';
$states          = include $plugin_root_dir . '/wp-content/plugins/woocommerce/i18n/states.php';


foreach ( $countries as $country ) {

	/**
	 * The expected JSON format is an object/associative-array with two fields – the country's matchable postcode
	 * length, and a list of postcodes:locations that are valid for that postcode.
	 *
	 * @var $country_data array{postcode_length:int,postcode_locations:array<string,array{postcode:string,state:string,city:string}>}
	 */
	$country_data                       = array();
	$country_data['postcode_locations'] = array();

	unset( $postcode, $state, $woocommerce_state, $data, $dataset_state_name, $file, $line, $states_index, $result_bytes );

	switch ( $country ) {
		case 'jp':
			// It's xxx-yyyy.
			$country_data['postcode_length'] = 7;

			$states_index = array_flip( $states['JP'] );

			$filename = __DIR__ . '/../jp_postal_codes.csv';
			$file     = file( $filename ) ?: array();
			foreach ( $file as $line ) {
				$data = str_getcsv( $line );

				// Strip all non-numeric characters from Japanese postcodes.
				$postcode = preg_replace( '/[^\d]*/', '', $data[0] );

				if ( ! isset( $country_data['postcode_locations'][ $postcode ] ) ) {
					$country_data['postcode_locations'][ $postcode ] = array();
				}
				$dataset_state_name = $data[2];
				$city               = $data[1];
				if ( isset( $states_index[ $dataset_state_name ] ) ) {
					$woocommerce_state = $states_index[ $dataset_state_name ];
				} else {
					error_log( "JP State $dataset_state_name not found in WC states list" );
					continue;
				}

				$country_data['postcode_locations'][ $postcode ][] = array(
					'postcode' => $postcode,
					'state'    => $woocommerce_state,
					'city'     => $city,
				);
			}
			break;

		case 'ie':
			// Eircodes are six characters long, but we only match on the first 3.
			$country_data['postcode_length'] = 3;

			$states_index = array_flip( $states['IE'] );

			$filename = __DIR__ . '/../postcodes-ie.csv';
			if ( ! is_readable( $filename ) ) {
				break;
			}
			$file = file( $filename ) ?: array();
			foreach ( $file as $line ) {
				$data = str_getcsv( $line );

				// Always remove non-alphanumeric characters from Irish postcodes.
				$postcode           = preg_replace( '/[^\d\w]*/', '', $data[0] );
				$city               = $data[1];
				$dataset_state_name = $data[2];
				if ( isset( $states_index[ $dataset_state_name ] ) ) {
					$woocommerce_state = $states_index[ $dataset_state_name ];
				} else {
					error_log( "IE State $dataset_state_name not found in WC states list" );
					continue;
				}

				if ( ! isset( $country_data['postcode_locations'][ $postcode ] ) ) {
					$country_data['postcode_locations'][ $postcode ] = array();
				}
					$country_data['postcode_locations'][ $postcode ][] = array(
						'postcode' => $postcode,
						'state'    => $woocommerce_state,
						'city'     => $city,
					);
			}
			break;
		case 'us':
			$country_data['postcode_length'] = 5; // match on of 9.

			$filename = __DIR__ . "/../postcodes-{$country}.json";
			if ( ! file_exists( $filename ) ) {
				break;
			}

			$json_string           = file_get_contents( $filename ) ?: '';
			$locations_by_postcode = json_decode( $json_string, true );
			// Remove header.
			unset( $locations_by_postcode['zip_code'] );
			foreach ( $locations_by_postcode as $postcode => $locations ) {
				// Remove non-numeric characters from US postcodes.
				$postcode = preg_replace( '/[^\d]*/', '', $postcode );
				foreach ( $locations as $location ) {
					$state = $location['state'];
					$city  = $location['city'];

					if ( ! isset( $country_data['postcode_locations'][ $postcode ] ) ) {
						$country_data['postcode_locations'][ $postcode ] = array();
					}
					$country_data['postcode_locations'][ $postcode ][] = array(
						'postcode' => $postcode,
						'state'    => $state,
						'city'     => $city,
					);
				}
			}
			break;
		case 'in':
			// Indian "PIN code"s are digits characters long
			$country_data['postcode_length'] = 6;

			$states_index = array_flip( $states['IN'] );

			$filename = __DIR__ . '/../in-pincode_30052019.csv';
			if ( ! is_readable( $filename ) ) {
				break;
			}
			$first = true;
			$file  = file( $filename ) ?: array();
			foreach ( $file as $line ) {
				if ( $first ) {
					$first = false;
					continue;
				}
				$data = str_getcsv( $line );

				// Always remove non-alphanumeric characters from Irish postcodes.
				$postcode           = intval( $data[4] );
				$city               = $data[7];
				$dataset_state_name = $data[8];

				if ( 0 === $postcode || empty( $city ) || empty( $dataset_state_name ) ) {
					continue;
				}
				if ( isset( $states_index[ $dataset_state_name ] ) ) {
					$woocommerce_state = $states_index[ $dataset_state_name ];
				} else {
					error_log( "IN State $dataset_state_name not found in WC states list" );
					continue;
				}

				if ( ! isset( $country_data['postcode_locations'][ $postcode ] ) ) {
					$country_data['postcode_locations'][ $postcode ] = array();
				}
				foreach ( $country_data['postcode_locations'][ $postcode ] as $location ) {
					if ( $location['postcode'] == "{$postcode}"
						&& $location['state'] == $woocommerce_state
						&& $location['city'] == $city
					) {
							continue 2;
					}
				}
				$country_data['postcode_locations'][ "{$postcode}" ][] = array(
					'postcode' => "{$postcode}",
					'state'    => $woocommerce_state,
					'city'     => $city,
				);
			}
			break;


		default:
			// Should never reach here.
			break;
	}

	$json         = json_encode( $country_data, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR, 4 );
	$result_bytes = file_put_contents(
		__DIR__ . "/../../{$country}.json",
		$json
	);
	if ( false === $result_bytes ) {
		error_log( "Failed to write {$country}.json" );
	}
	$failed = json_last_error();
	if ( JSON_ERROR_NONE !== $failed ) {
		error_log( "Failed to encode {$country}.json: " . json_last_error_msg() );
	}
}


$countries_php = array_reduce(
	$countries,
	function ( string $carry, string $country ): string {
		return $carry . "\n	'". strtoupper( $country ) . "',";
	},
	''
);

file_put_contents(
	__DIR__ . '/../../available-countries.php',
<<<EOD
<?php
/**
 * Autogenerated by `data-parser.php`.
 *
 * @package brianhenryie/bh-wc-postcode-address-autofill
 */

return array($countries_php
);

EOD,
);
