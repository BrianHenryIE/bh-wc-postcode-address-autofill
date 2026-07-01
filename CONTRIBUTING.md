
## Testing:

Requires Docker.

### PHPUnit Tests with Codeception/WP-Browser


```bash
composer install
npm install
npx wp-env start
XDEBUG_MODE=coverage composer coverage-tests; 
```

### E2E testing with wp-env and Playwright

Requires Docker

```php
npm install
npx wp-env start
npx playwright test --config ./playwright.config.js
npx wp-env destroy
```

Notes:

```
# Destroy the environment and restart
echo Y | npx wp-env destroy; npx wp-env start

# for development work
open http://localhost:8888

# Start the playwright test runner UI and return to the Terminal (otherwise Terminal is unavailable until the application is exited).
npx playwright test --ui &;

# Start browser and record Playwright steps
npx playwright codegen -o tests/e2e-pw/example.spec.js

# Run WP CLI commands on the tests instance
npx wp-env run tests-cli wp option get rewrite_rules
```

## Dependencies (package.json)

Node/npm dev dependencies and why each is needed:

- `@wordpress/scripts` — the core build/lint/test toolchain (webpack `build`/`start`, `lint:js`, `format:js`, `test:unit`).
- `@wordpress/env` — Docker-based local WordPress environment (`wp-env`); see `.wp-env.json`.
- `@woocommerce/dependency-extraction-webpack-plugin` — at build time, externalizes `@woocommerce/*` imports to the `window.wc.*` globals WooCommerce provides (configured in `webpack.config.js`).
- `@woocommerce/eslint-plugin` — WooCommerce/WordPress ESLint ruleset extended by `.eslintrc`.
- `@playwright/test` — the Playwright test runner used by the E2E specs and `playwright.config.js`.
- `@wordpress/e2e-test-utils-playwright` — WordPress Playwright helpers (`Admin`, `RequestUtils`) used in `tests/e2e-pw`.
- `@woocommerce/woocommerce-rest-api` — REST API client the Playwright E2E tests use to seed store data.

### More Information

See [github.com/BrianHenryIE/WordPress-Plugin-Boilerplate](https://github.com/BrianHenryIE/WordPress-Plugin-Boilerplate) for initial setup rationale. 
