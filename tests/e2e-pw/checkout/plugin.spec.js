// Enable the plugin
const { test, expect, RequestUtils, Admin } = require ('@wordpress/e2e-test-utils-playwright');
const {paramCase: kebabCase} = require("param-case");

// TODO: Failing due to "Database Update Required".
test.describe( 'Plugin activates without issue', () => {

    test( 'can activate the plugin', async ( {
                                                                        page,
                                                                        playwright,
                                                                        baseURL,
                                                                    } ) => {

        const requestUtils = await RequestUtils.setup(
            {
                user: {username: "admin", password: "password"},
                baseURL:baseURL
            });

        // basic-auth/basic-auth.php
        await requestUtils.activatePlugin(kebabCase("JSON Basic Authentication"));
        // await requestUtils.activatePlugin('basic-auth/basic-auth.php');

        // await requestUtils.activatePlugin(kebabCase( "WooCommerce" ));
        await requestUtils.activatePlugin("woocommerce");
        // await requestUtils.activatePlugin(kebabCase( "Gutenberg"));
        // await requestUtils.activatePlugin("gutenberg");
        // await requestUtils.activatePlugin(kebabCase("WooCommerce Blocks")); // 'woo-gutenberg-products-block'
        // woo-gutenberg-products-block/woocommerce-gutenberg-products-block.php
        // await requestUtils.activatePlugin('woo-gutenberg-products-block'); // 'woo-gutenberg-products-block'
        // await requestUtils.activatePlugin('woocommerce-gutenberg-products-block');
        // await requestUtils.activatePlugin(kebabCase("WooCommerce Dummy Payments Gateway")); // woocommerce-gateway-dummy
        await requestUtils.activatePlugin("woocommerce-dummy-payments-gateway"); // woocommerce-gateway-dummy
        // await requestUtils.activatePlugin('woocommerce-gateway-dummy'); // woocommerce-gateway-dummy

        await requestUtils.activatePlugin(kebabCase("Postcode Address Autofill")); // bh-wc-postcode-address-autofill

        await page.goto( 'wp-login.php', {
            waitUntil: 'networkidle',
        } );

        await page.fill( 'input[name="log"]', "admin" );
        await page.fill( 'input[name="pwd"]', "password" );
        // await page.click( 'text=Log In' );
        await page.locator('#wp-submit').click();

        await page.waitForLoadState( 'networkidle' );

        await page.goto( 'wp-admin/plugins.php', {
            waitUntil: 'networkidle',
        } );

        await expect(
            page.locator( '.plugin-title strong', {
                hasText: /^Postcode Address Autofill$/,
            } )
        ).toBeVisible();

        await expect(
            page.locator( '#deactivate-postcode-address-autofill' )
        ).toBeVisible();
    } );
});