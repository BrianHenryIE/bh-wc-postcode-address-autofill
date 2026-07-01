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

    // Race condition: on a slow connection, a user can edit the city and/or state while the
    // postcode autofill request is still in flight. When the response arrives it must NOT
    // overwrite what the user has since entered. This test currently FAILS (the plugin clobbers
    // both the user's city and state with the server's values) — it exists to prove the bug.
    // The autofill for A67 X566 returns city "Rathnew" / county "WW" (Wicklow); the user
    // instead enters "Kilcoolabbey" / "CW" (Carlow), which must be preserved.
    test('User-entered city and state are not overwritten by a slow autofill response', async( { page } ) => {

        await page.goto( '/blocks-checkout/?add-to-cart=' + productId,{waitUntil:'domcontentloaded'});

        // Simulate a slow network for the plugin's autofill request only: hold the
        // `extensionCartUpdate` batch (identified by our namespace) so we can edit the
        // city and state fields while it is still pending.
        await page.route( '**/wc/store/v1/batch*', async ( route ) => {
            const postData = route.request().postData() || '';
            if ( postData.includes( 'bh-wc-postcode-address-autofill' ) ) {
                await new Promise( ( resolve ) => setTimeout( resolve, 3000 ) );
            }
            await route.continue();
        } );

        let shippingAddress = await page.locator('#shipping');

        await selectCountry( shippingAddress, 'IE' );
        await page.waitForLoadState( 'networkidle' );

        // Entering the postcode fires the async city/state lookup.
        const autofillRequest = page.waitForRequest( ( request ) =>
            request.url().includes( '/wc/store/v1/batch' ) &&
            ( request.postData() || '' ).includes( 'bh-wc-postcode-address-autofill' )
        );
        const autofillResponse = page.waitForResponse( ( response ) =>
            response.url().includes( '/wc/store/v1/batch' ) &&
            ( response.request().postData() || '' ).includes( 'bh-wc-postcode-address-autofill' )
        );
        await shippingAddress.getByLabel('Eircode').fill('A67 X566');
        await shippingAddress.getByLabel('Eircode').press('Tab');

        // Wait until that request is in-flight (held by the route above).
        await autofillRequest;

        // While the request is pending, the user enters a different city and county.
        const cityField = shippingAddress.getByLabel('City');
        const countyField = shippingAddress.getByLabel('County');
        await cityField.fill('Kilcoolabbey');
        await countyField.selectOption('CW');

        // Wait for the slow request to complete and settle so the plugin's `.then` has
        // applied (and clobbered) before asserting — otherwise we'd assert the user's value
        // while it is only briefly still present, giving a false pass.
        await autofillResponse;
        await page.waitForLoadState( 'networkidle' );
        await page.waitForTimeout( 2000 );

        // The user's input must win: their edits happened after the request was fired.
        // Soft assertions so both fields are reported even though both currently clobber.
        await expect.soft( cityField ).toHaveValue('Kilcoolabbey');
        await expect.soft( countyField ).toHaveValue('CW');
    });
} );
