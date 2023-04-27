<?php
/**
 * Loads all required classes
 *
 * @package brianhenryie/bh-wc-postcode-address-autofill
 */

namespace BrianHenryIE\WC_Postcode_Address_Autofill;

use BrianHenryIE\WC_Postcode_Address_Autofill\Alley_Interactive\Autoloader\Autoloader;

require_once __DIR__ . '/vendor-prefixed/autoload.php';

Autoloader::generate(
	__NAMESPACE__,
	__DIR__ . '/src',
)->register();
