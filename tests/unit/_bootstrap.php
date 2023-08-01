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
