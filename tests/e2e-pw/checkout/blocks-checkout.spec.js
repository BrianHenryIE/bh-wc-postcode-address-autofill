// @ts-check
const { test, expect } = require( '@playwright/test' );
const { default: WooCommerceRestApi } = require("@woocommerce/woocommerce-rest-api");

// TODO: Check is this import useful:
// "It also creates a "Checkout page object" util class which contains some new utils, specifically:"
// @see https://github.com/woocommerce/woocommerce-blocks/pull/10532

test.describe( 'Checkout page', () => {

    const singleProductPrice = '9.99';
    const simpleProductName = 'Simple Product';

    let productId;

    test.beforeAll( async ( { request , baseURL} ) => {

        // Check have pretty permalinks been set for the REST API.
        let wpApiBaseRoute = await request.get(baseURL + '/wp-json/wp/v2/');
        await expect(wpApiBaseRoute.ok(), "WooCommerce REST API requires pretty permalinks.").toBeTruthy();

        let wcApi = new WooCommerceRestApi( {
            url: baseURL,
            consumerKey: 'admin',
            consumerSecret: 'password',
            version: 'wc/v3',
        } );

        // HTTPS is required to use basic auth on the WooCommerce API. Changing this flag doesn't change the URL or
        // protocol.
        wcApi.isHttps = true;

        // Create a product.
        await wcApi
            .post( 'products', {
                name: simpleProductName,
                type: 'simple',
                regular_price: singleProductPrice,
            } )
            .then( ( response ) => {
                productId = response.data.id;
            } );
    } );

    test('Checkout billing address gets city and country autofilled', async( { page } ) => {

        await page.goto( '/shop/?add-to-cart=' + productId );
        await page.waitForLoadState( 'networkidle' );

        await page.goto( '/blocks-checkout/' );

        let billingAddress = await page.locator('#billing').page();

        await billingAddress.getByLabel('Country/Region').click();
        await billingAddress.getByLabel('Country/Region').fill('united');
        await billingAddress.getByLabel('United States (US)', { exact: true }).click();

        await billingAddress.getByLabel("ZIP Code").focus();
        await billingAddress.getByLabel("ZIP Code").fill('10001');
        await billingAddress.getByLabel('ZIP Code').press('Tab');

        await page.waitForLoadState( 'networkidle' );

        await expect( billingAddress.getByLabel('City') ).toHaveValue('NEW YORK');
        await expect( billingAddress.locator('#billing-state').getByLabel('State') ).toHaveValue(/New York/i );
	});

} );
