<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://BrianHenry.ie
 * @since             1.0.0
 * @package brianhenryie/bh-wc-postcode-address-autofill
 *
 * @wordpress-plugin
 * Plugin Name:       Postcode Address Autofill
 * Plugin URI:        http://github.com/BrianHenryIE/bh-wc-postcode-address-autofill/
 * Description:       Autofill city and state based on postcode input.
 * Version:           1.0.1
 * Author:            BrianHenryIE
 * Author URI:        http://BrianHenry.ie
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       bh-wc-postcode-address-autofill
 * Domain Path:       /languages
 *
 * WC requires at least: 7.6
 * WC tested up to:      7.6
 */

namespace BrianHenryIE\WC_Postcode_Address_Autofill;

use BrianHenryIE\WC_Postcode_Address_Autofill\Alley_Interactive\Autoloader\Autoloader;
use BrianHenryIE\WC_Postcode_Address_Autofill\API\API;
use BrianHenryIE\WC_Postcode_Address_Autofill\API\Settings;
use BrianHenryIE\WC_Postcode_Address_Autofill\WP_Includes\Activator;
use BrianHenryIE\WC_Postcode_Address_Autofill\WP_Includes\Deactivator;

if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once __DIR__ . '/vendor-prefixed/autoload.php';

Autoloader::generate( __NAMESPACE__, __DIR__ . '/src', )->register();

/**
 * Current plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'BH_WC_POSTCODE_ADDRESS_AUTOFILL_VERSION', '1.0.1' );
define( 'BH_WC_POSTCODE_ADDRESS_AUTOFILL_BASENAME', plugin_basename( __FILE__ ) );
define( 'BH_WC_POSTCODE_ADDRESS_AUTOFILL_PATH', plugin_dir_path( __FILE__ ) );
define( 'BH_WC_POSTCODE_ADDRESS_AUTOFILL_URL', trailingslashit( plugins_url( plugin_basename( __DIR__ ) ) ) );

register_activation_hook( __FILE__, array( Activator::class, 'activate' ) );
register_deactivation_hook( __FILE__, array( Deactivator::class, 'deactivate' ) );

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function instantiate_bh_wc_postcode_address_autofill(): BH_WC_Postcode_Address_Autofill {

	$settings = new Settings();
	$api      = new API( $settings );

	$plugin = new BH_WC_Postcode_Address_Autofill( $api, $settings );

	return $plugin;
}

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and frontend-facing site hooks.
 */
$GLOBALS['bh_wc_postcode_address_autofill'] = instantiate_bh_wc_postcode_address_autofill();
