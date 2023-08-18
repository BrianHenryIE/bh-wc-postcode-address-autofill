<?php
/**
 * PHPUnit bootstrap file for WP_Mock.
 *
 * @package brianhenryie/bh-wc-postcode-address-autofill
 */

use Alley_Interactive\Autoloader\Autoloader;

WP_Mock::setUsePatchwork( true );
WP_Mock::bootstrap();

global $plugin_root_dir;
Autoloader::generate(
	'BrianHenryIE\\WC_Postcode_Address_Autofill\\',
	$plugin_root_dir . '/src',
)->register();

$class_map = array(
	'Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface' => $plugin_root_dir . '/wp-content/plugins/woocommerce/packages/woocommerce-blocks/src/Integrations/IntegrationInterface.php',
);

spl_autoload_register(
	function ( $classname ) use ( $class_map ) {
		if ( array_key_exists( $classname, $class_map ) && file_exists( $class_map[ $classname ] ) ) {
			require_once $class_map[ $classname ];
		}
	}
);
