[![WordPress tested 6.2](https://img.shields.io/badge/WordPress-v6.2%20tested-0073aa.svg)](https://wordpress.org/plugins/bh-wc-postcode-address-autofill) [![PHPCS WPCS](https://img.shields.io/badge/PHPCS-WordPress%20Coding%20Standards-8892BF.svg)](https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards) [![PHPUnit ](.github/coverage.svg)](https://brianhenryie.github.io/bh-wc-postcode-address-autofill/) [![PHPStan ](https://img.shields.io/badge/PHPStan-Level%208-2a5ea7.svg)](https://github.com/szepeviktor/phpstan-wordpress)

# Postcode Address Autofill for WooCommerce

Performs a zipcode lookup to autofill the city and state fields. First moves the postcode field above the city and state fields.

Postcode data available for the United States (US), Ireland (IE) and Japan (JP).

![Postcode autofill](./.github/bh-wc-postcode-address-autofill.gif "Demo of the city and state autofilling from the postcode entry")

### PHPUnit Tests with Codeception/WP-Browser

Requires local Apache and MySQL.

```bash
composer install
composer create-databases
composer setup-wordpress
XDEBUG_MODE=coverage composer coverage-tests; 
$ composer delete-databases
```

### E2E testing with wp-env and Playwright

Requires Docker

```php
npm install
npx wp-env start
npx playwright test --config ./tests/e2e-pw/playwright.config.js
npx wp-env destroy
```

Notes:

```
# Destroy the environment and restart
echo Y | npx wp-env destroy; npx wp-env start

# for development work
open http://localhost:8888

# is used for automated tests.
open http://localhost:8889

# Start the playwright test runner UI and return to the Terminal (otherwise Terminal is unavailable until the application is exited).
npx playwright test --config ./tests/e2e-pw/playwright.config.js --ui &;

# Run WP CLI commands on the tests instance
npx wp-env run tests-cli wp option get rewrite_rules
```

### More Information

See [github.com/BrianHenryIE/WordPress-Plugin-Boilerplate](https://github.com/BrianHenryIE/WordPress-Plugin-Boilerplate) for initial setup rationale. 

# Acknowledgements