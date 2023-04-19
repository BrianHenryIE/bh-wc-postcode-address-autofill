<?php
/**
 * Loads all required classes
 *
 * @package brianhenryie/bh-wc-checkout-address-suggestions
 */

namespace BrianHenryIE\WC_Checkout_Address_Suggestions;

use BrianHenryIE\WC_Checkout_Address_Suggestions\Alley_Interactive\Autoloader\Autoloader;

require_once __DIR__ . '/vendor-prefixed/autoload.php';

Autoloader::generate(
	__NAMESPACE__,
	__DIR__ . '/src',
)->register();
