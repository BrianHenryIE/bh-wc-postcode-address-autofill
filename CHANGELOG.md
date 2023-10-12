# Changelog

## 1.3.0

* Add: autofill for shipping address fields on shortcode checkout
* Fix: Irish counties (states) did not match WooCommerce names
* Fix: Blocks checkout bug where country could be forgotten

## 1.2.1

* Add: comment for `nosemgrep` exclusion reason. 

## 1.2.0 - 2023-10-11

* Add: cache country lookup classes
* Add: on activation, load the store base country data into the cache
* Fix: Japanese datafile states didn't all match WooCommerce states

## 1.1.1 - 2023-09-07

* Dev: Refactor data loading. Data is now stored in a common format and parsed to strongly typed locations
* Dev: Added many tests

## 1.1.0 - 2023-08-18

* Add: WooCommerce Blocks compatibility
* Fix: JS error when blur/focus moved to browser chrome and not a DOM element
* Performance: Don't perform lookup unless postcode has changed
* Dev: Added E2E tests with Playwright and wp-env
* Dev: Added many PHPUnit tests

## 1.0.1

* Fix: focus next element after refresh
* Renamed plugin (from bh-wc-checkout-address-suggestions)
* Removed unused boilerplate files

## 1.0.0 - 2023-04-18

It works.

## 2021-09-24

Initial work.