<?php
/**
 * PHPUnit bootstrap file for wpunit tests. Since the plugin will not be otherwise autoloaded.
 *
 * @package brianhenryie/bh-wc-postcode-address-autofill
 */

use Alley_Interactive\Autoloader\Autoloader;

global $plugin_root_dir;
Autoloader::generate(
	'BrianHenryIE\\WC_Postcode_Address_Autofill\\',
	$plugin_root_dir . '/src',
)->register();
