// @ts-check
const { test, expect } = require( '@playwright/test' );
const { default: WooCommerceRestApi } = require("@woocommerce/woocommerce-rest-api");

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

    test('Customer gets city and country autofilled', async( { page } ) => {

        await page.goto( '/shop/?add-to-cart=' + productId );
		await page.waitForLoadState( 'networkidle' );

        await page.goto( '/blocks-checkout/' );

        await page.getByLabel('Country/Region').click();
        await page.getByLabel('Country/Region').fill('united');
        await page.getByLabel('United States (US)', { exact: true }).click();


        await page.getByLabel("ZIP Code").focus();
        await page.getByLabel("ZIP Code").fill('10001');
        await page.getByLabel('ZIP Code').press('Tab');

        // await page.getByLabel('Phone').focus();
        await page.waitForLoadState( 'networkidle' );

        await expect( page.getByLabel('City') ).toContainText('New York');
        await expect( page.getByLabel('State') ).toHaveValue(/New York/i );
	});

} );
