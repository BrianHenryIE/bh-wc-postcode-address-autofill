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
 * @package brianhenryie/bh-wc-checkout-address-suggestions
 *
 * @wordpress-plugin
 * Plugin Name:       Checkout Address Suggestions
 * Plugin URI:        http://github.com/BrianHenryIE/bh-wc-checkout-address-suggestions/
 * Description:       Autofill city and state based on postcode input.
 * Version:           1.0.0
 * Author:            BrianHenryIE
 * Author URI:        http://BrianHenry.ie
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       bh-wc-checkout-address-suggestions
 * Domain Path:       /languages
 */

namespace BrianHenryIE\WC_Checkout_Address_Suggestions;

// If this file is called directly, abort.
use BrianHenryIE\WC_Checkout_Address_Suggestions\API\API;
use BrianHenryIE\WC_Checkout_Address_Suggestions\API\Settings;
use BrianHenryIE\WC_Checkout_Address_Suggestions\WP_Includes\Activator;
use BrianHenryIE\WC_Checkout_Address_Suggestions\WP_Includes\Deactivator;

if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once plugin_dir_path( __FILE__ ) . 'autoload.php';

/**
 * Current plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'BH_WC_CHECKOUT_ADDRESS_SUGGESTIONS_VERSION', '1.0.0' );
define( 'BH_WC_CHECKOUT_ADDRESS_SUGGESTIONS_BASENAME', plugin_basename( __FILE__ ) );
define( 'BH_WC_CHECKOUT_ADDRESS_SUGGESTIONS_PATH', plugin_dir_path( __FILE__ ) );
define( 'BH_WC_CHECKOUT_ADDRESS_SUGGESTIONS_URL', trailingslashit( plugins_url( plugin_basename( __DIR__ ) ) ) );

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
function instantiate_bh_wc_checkout_address_suggestions(): BH_WC_Checkout_Address_Suggestions {

	$settings = new Settings();
	$api      = new API( $settings );

	$plugin = new BH_WC_Checkout_Address_Suggestions( $api, $settings );

	return $plugin;
}

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and frontend-facing site hooks.
 */
$GLOBALS['bh_wc_checkout_address_suggestions'] = instantiate_bh_wc_checkout_address_suggestions();
