// @ts-check
const { defineConfig, devices } = require( '@playwright/test' );

/**
 * Read environment variables from file.
 * https://github.com/motdotla/dotenv
 */
// require('dotenv').config();

/**
 * @see https://playwright.dev/docs/test-configuration
 */
// module.exports = defineConfig(
// 	{
const config = {
	testDir: './tests/e2e-pw',
	/* Run tests in files in parallel */
	fullyParallel: true,
	/* Fail the build on CI if you accidentally left test.only in the source code. */
	forbidOnly: !!process.env.CI,
	/* Retry on CI only */
	retries: process.env.CI ? 2 : 0,
	/* Opt out of parallel tests on CI. */
	workers: process.env.CI ? 1 : undefined,
	/* Reporter to use. See https://playwright.dev/docs/test-reporters */
	// override that location using the PLAYWRIGHT_HTML_REPORT environment variable
	reporter: [['html', { outputFolder: 'tests/_output/playwright-report/' + (new Date()).toISOString() }]],

	/* Shared settings for all the projects below. See https://playwright.dev/docs/api/class-testoptions. */
	use: {
		/* Base URL to use in actions like `await page.goto('/')`. */
		baseURL: 'http://localhost:8889',

		/* Collect trace when retrying the failed test. See https://playwright.dev/docs/trace-viewer */
		trace: 'retain-on-failure',
	},
	// Folder for test artifacts such as screenshots, videos, traces, etc.
	outputDir: './tests/_output/playwright-results',

	// // path to the global setup files.
	// globalSetup: require.resolve('./global-setup'),
	//
	// // path to the global teardown files.
	// globalTeardown: require.resolve('./global-teardown'),
	//
	// // Each test is given 30 seconds.
	// timeout: 30000,

	/* Configure projects for major browsers */
	projects: [
		{
			name: 'chromium',
			use: {...devices['Desktop Chrome']},
		},

		// {
		// 	name: 'firefox',
		// 	use: { ...devices['Desktop Firefox'] },
		// },
		//
		// {
		// 	name: 'webkit',
		// 	use: { ...devices['Desktop Safari'] },
		// },

		/* Test against mobile viewports. */
		// {
		// name: 'Mobile Chrome',
		// use: { ...devices['Pixel 5'] },
		// },
		// {
		// name: 'Mobile Safari',
		// use: { ...devices['iPhone 12'] },
		// },

		/* Test against branded browsers. */
		// {
		// name: 'Microsoft Edge',
		// use: { ...devices['Desktop Edge'], channel: 'msedge' },
		// },
		// {
		// name: 'Google Chrome',
		// use: { ..devices['Desktop Chrome'], channel: 'chrome' },
		// },
	],

	/* Run your local dev server before starting the tests */
	// webServer: {
	// command: 'pnpm run env:start',
	// url: 'http://127.0.0.1:8888',
	// reuseExistingServer: !process.env.CI,
	// },
	// }
// );
};

module.exports = config;