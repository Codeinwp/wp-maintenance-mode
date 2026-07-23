/**
 * WordPress dependencies
 */
import { test, expect } from '@wordpress/e2e-test-utils-playwright';

/**
 * Internal dependencies
 */
import { setMaintenanceMode, openAsVisitor } from '../utils.js';

test.describe( 'maintenance mode lifecycle', () => {
	test( 'the site is public while maintenance mode is off', async ( {
		admin,
		page,
		browser,
	} ) => {
		await setMaintenanceMode( admin, page, false );

		const visitor = await openAsVisitor( browser );
		expect( visitor.response.status() ).toBe( 200 );
		await expect( visitor.page.locator( 'body' ) ).not.toContainText(
			'Sorry for the inconvenience'
		);
		await visitor.context.close();
	} );

	test( 'visitors get the 503 maintenance page while active, admins and the login page stay reachable', async ( {
		admin,
		page,
		browser,
	} ) => {
		await setMaintenanceMode( admin, page, true );

		// A logged-out visitor is served the maintenance page with a 503.
		const visitor = await openAsVisitor( browser );
		expect( visitor.response.status() ).toBe( 503 );
		await expect( visitor.page.locator( 'body' ) ).toContainText(
			'Sorry for the inconvenience'
		);
		await visitor.context.close();

		// The login page stays excluded from the takeover.
		const login = await openAsVisitor( browser, '/wp-login.php' );
		expect( login.response.status() ).toBe( 200 );
		await expect(
			login.page.locator( 'input[name="log"]' )
		).toBeVisible();
		await login.context.close();

		// A logged-in administrator still sees the real site.
		const adminResponse = await page.goto( '/' );
		expect( adminResponse.status() ).toBe( 200 );
		await expect( page.locator( 'body' ) ).not.toContainText(
			'Sorry for the inconvenience'
		);
	} );

	test( 'disabling maintenance mode makes the site public again', async ( {
		admin,
		page,
		browser,
	} ) => {
		await setMaintenanceMode( admin, page, false );

		const visitor = await openAsVisitor( browser );
		expect( visitor.response.status() ).toBe( 200 );
		await expect( visitor.page.locator( 'body' ) ).not.toContainText(
			'Sorry for the inconvenience'
		);
		await visitor.context.close();
	} );
} );
