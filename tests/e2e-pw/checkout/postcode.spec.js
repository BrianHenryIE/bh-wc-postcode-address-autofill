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

        // Required.
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

        await page.goto( '/shortcode-checkout/' );

        await page.selectOption( '#billing_country', 'US' );

        await page.getByLabel("ZIP Code").focus();
        await page.getByLabel("ZIP Code").fill('10001');

        await page.locator('#billing_email').focus();
        await page.waitForLoadState( 'networkidle' );

        await expect( page.locator( '#billing_state' ) ).toContainText('New York');
        await expect( page.locator( '#billing_city' ) ).toHaveValue(/New York/i );
	});

    test('Customer gets city and country autofilled only once', async( { page } ) => {

        await page.goto( '/shop/?add-to-cart=' + productId );
        await page.waitForLoadState( 'networkidle' );

        await page.goto( '/shortcode-checkout/' );

        await page.selectOption( '#billing_country', 'IE' );

        await page.getByLabel("Eircode").focus();
        await page.getByLabel("Eircode").fill('A67 X566');

        await page.locator('#billing_email').focus();
        await page.waitForLoadState( 'networkidle' );

        await expect( page.locator( '#billing_state' ) ).toContainText('Wicklow');
        await expect( page.locator( '#billing_city' ) ).toHaveValue(/Wicklow/i );

        await page.getByLabel("Town / City").fill('Rathnew');

        await page.getByLabel("Eircode").focus();
        await page.getByLabel("Eircode").fill('A67 X566');

        await page.locator('#billing_email').focus();
        await page.waitForLoadState( 'networkidle' );

        await expect( page.locator( '#billing_city' ) ).toHaveValue(/Rathnew/i );
    });
} );
