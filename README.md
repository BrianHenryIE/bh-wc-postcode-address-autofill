[![WordPress tested 6.2](https://img.shields.io/badge/WordPress-v6.2%20tested-0073aa.svg)](https://wordpress.org/plugins/bh-wc-postcode-address-autofill) [![PHPCS WPCS](https://img.shields.io/badge/PHPCS-WordPress%20Coding%20Standards-8892BF.svg)](https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards) [![PHPUnit ](.github/coverage.svg)](https://brianhenryie.github.io/bh-wc-postcode-address-autofill/) [![PHPStan ](https://img.shields.io/badge/PHPStan-Level%208-2a5ea7.svg)](https://github.com/szepeviktor/phpstan-wordpress)

# Postcode Address Autofill for WooCommerce

Performs a zipcode lookup to autofill the city and state fields. First moves the postcode field above the city and state fields.

Works with WooCommerce Blocks checkout. Postcode data available for the United States (US), Ireland (IE) and Japan (JP). 

![Blocks checkout postcode autofill](./.github/bh-wc-postcode-address-autofill-blocks-checkout.gif "Demo of the city and state autofilling from the postcode entry")

![Shortcode checkout postcode autofill](./.github/bh-wc-postcode-address-autofill.gif "Demo of the city and state autofilling from the postcode entry")

## TODO:

* Send `available_countries` to the frontend to avoid unnecessary lookups
* Shipping addresses (done for shortcode checkout)
* My Account
* JS/TS sourcemap
* Add city suggestions via select2 where postcode is not precise enough
* ~~Serialize data and store in database~~ v1.2.0