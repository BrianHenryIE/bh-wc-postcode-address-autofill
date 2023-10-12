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

    test('Customer gets billing city and state autofilled', async( { page } ) => {

        await page.goto( '/shortcode-checkout/?add-to-cart=' + productId,{waitUntil:'domcontentloaded'});

        let billingFields = await page.locator( '.woocommerce-billing-fields' );

        await page.selectOption( '#billing_country', 'US' );

        await billingFields.getByLabel("ZIP Code").focus();
        await billingFields.getByLabel("ZIP Code").fill('10001');

        await billingFields.locator('#billing_email').focus();
        await page.waitForLoadState( 'networkidle' );

        await expect( billingFields.locator( '#billing_state' ) ).toContainText('New York');
        await expect( billingFields.locator( '#billing_city' ) ).toHaveValue(/New York/i );
	});

    // TODO: Right now this just confirms that the refresh request is not sent unless the postcode is changed
    // TODO: Add a test with multiple shipping options, so when the chosen one changes and refreshes the checkout, confirm the postcode is not altered.
    test('Customer gets city and state autofilled only once', async( { page } ) => {

        await page.goto( '/shortcode-checkout/?add-to-cart=' + productId,{waitUntil:'domcontentloaded'});

        let billingFields = await page.locator( '.woocommerce-billing-fields' );

        await page.selectOption( '#billing_country', 'IE' );

        await billingFields.getByLabel("Eircode").focus();
        await billingFields.getByLabel("Eircode").fill('A67 X566');

        await billingFields.locator('#billing_email').focus();
        await page.waitForLoadState( 'networkidle' );

        await expect( billingFields.locator( '#billing_state' ) ).toContainText('Wicklow');
        await expect( billingFields.locator( '#billing_city' ) ).toHaveValue(/Rathnew/i );

        await billingFields.getByLabel("Town / City").fill('Wicklow');

        await billingFields.getByLabel("Eircode").focus();
        await billingFields.getByLabel("Eircode").fill('A67 X566');

        await billingFields.locator('#billing_email').focus();
        await page.waitForLoadState( 'networkidle' );

        await expect( billingFields.locator( '#billing_city' ) ).toHaveValue(/Wicklow/i );
    });

    test('Customer gets shipping city and state autofilled', async( { page } ) => {

        await page.goto( '/shortcode-checkout/?add-to-cart=' + productId,{waitUntil:'domcontentloaded'});

        let shippingFields = await page.locator( '.woocommerce-shipping-fields' );

        await page.locator('#ship-to-different-address').click();
        await page.waitForTimeout(250);

        await page.selectOption( '#shipping_country', 'IE' );

        await shippingFields.getByLabel("Eircode").focus();
        await shippingFields.getByLabel("Eircode").fill('D02 WY18');

        await shippingFields.locator('#shipping_address_1').focus();
        await page.waitForLoadState( 'networkidle' );

        await expect( shippingFields.locator( '#shipping_city' ) ).toHaveValue(/Dublin 2/i );
        await expect( shippingFields.locator( '#shipping_state' ) ).toContainText('Dublin');
    });
} );
