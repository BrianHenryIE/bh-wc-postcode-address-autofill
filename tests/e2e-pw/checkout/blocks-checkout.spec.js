// @ts-check
const { test, expect } = require( '@playwright/test' );
const { default: WooCommerceRestApi } = require("@woocommerce/woocommerce-rest-api");

// TODO: Check is this import useful:
// "It also creates a "Checkout page object" util class which contains some new utils, specifically:"
// @see https://github.com/woocommerce/woocommerce-blocks/pull/10532

/**
 * Set a country on the block checkout. The country field is a native `<select>` (rendered with
 * an implicit `combobox` role); select by its option value (the ISO country code).
 *
 * @param {import('@playwright/test').Locator} scope The billing/shipping fields container.
 * @param {string}                             code  ISO country code, e.g. 'US', 'IE'.
 */
async function selectCountry( scope, code ) {
    await scope.getByRole( 'combobox', { name: 'Country/Region' } ).selectOption( code );
}

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

        await page.goto( '/blocks-checkout/?add-to-cart=' + productId,{waitUntil:'domcontentloaded'});

        // Uncheck "Use same address for billing" to reveal the separate billing fields. The
        // block checkbox input is visually hidden behind its mark, so click the label text.
        await page.getByText( 'Use same address for billing' ).click();

        let billingAddress = await page.locator('#billing-fields');
        await expect( billingAddress ).toBeVisible();

        await selectCountry( billingAddress, 'US' );

        await page.waitForLoadState( 'networkidle' );

        let zipCodeField = await billingAddress.locator('#billing-postcode');

        // await billingAddress.getByLabel("ZIP Code").focus();
        await zipCodeField.fill('10001');
        await zipCodeField.press('Tab');

        await page.waitForLoadState( 'networkidle' );

        await expect( await billingAddress.getByLabel(/^State$/) ).toHaveValue('NY');

        // await expect( await billingAddress.getByLabel(/^State$/) ).toHaveValue('NEW YORK');
        await expect( await billingAddress.getByLabel('City') ).toHaveValue('NEW YORK');
	});

    test('Checkout shipping address postcode autofills city and country', async( { page } ) => {

        await page.goto( '/blocks-checkout/?add-to-cart=' + productId,{waitUntil:'domcontentloaded'});

        let shippingAddress = await page.locator('#shipping');

        await selectCountry( shippingAddress, 'IE' );

        await page.waitForLoadState( 'networkidle' );

        await shippingAddress.getByLabel("Eircode").focus();
        await shippingAddress.getByLabel("Eircode").fill('A67 X566');
        await shippingAddress.getByLabel('Eircode').press('Tab');

        await page.waitForLoadState( 'networkidle' );

        // await expect( await shippingAddress.getByLabel('County') ).toHaveValue(/Wicklow/i);
        await expect( await shippingAddress.getByLabel('County') ).toHaveValue('WW');
        await expect( await shippingAddress.getByLabel('City') ).toHaveValue(/rathnew/i);
    });
} );
