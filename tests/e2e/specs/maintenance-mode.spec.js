/**
 * WordPress dependencies
 */
import { test, expect } from '@wordpress/e2e-test-utils-playwright';

/**
 * External dependencies
 */
import { execSync } from 'child_process';

/**
 * Internal dependencies
 */
import { setMaintenanceMode, openAsVisitor } from '../utils.js';

/**
 * Run a WP-CLI command inside the wp-env cli container.
 *
 * @param {string} args WP-CLI arguments, without the leading `wp`.
 */
function wpCli( args ) {
	execSync( `npx wp-env run cli wp ${ args }`, { stdio: 'pipe' } );
}

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

	test( 'admins get a warning notice in wp-admin while maintenance mode is active', async ( {
		admin,
		page,
	} ) => {
		await setMaintenanceMode( admin, page, true );

		await admin.visitAdminPage( 'index.php', '' );
		await expect(
			page.getByText( /The Maintenance Mode is/ ).first()
		).toBeVisible();

		await setMaintenanceMode( admin, page, false );

		await admin.visitAdminPage( 'index.php', '' );
		await expect(
			page.getByText( /The Maintenance Mode is/ )
		).toHaveCount( 0 );
	} );

	test( 'visitors get a 503 on the selected maintenance page (new look)', async ( {
		admin,
		page,
		browser,
		requestUtils,
	} ) => {
		const maintenancePage = await requestUtils.rest( {
			path: '/wp/v2/pages',
			method: 'POST',
			data: {
				title: 'Selected maintenance page',
				status: 'publish',
				content: 'Back in a moment.',
			},
		} );

		await setMaintenanceMode( admin, page, true );
		// The block-based flow has no settings UI without the wizard, so switch it on directly.
		wpCli( 'option update wpmm_new_look 1' );
		wpCli(
			`option patch insert wpmm_settings design page_id ${ maintenancePage.id }`
		);

		try {
			const visitor = await openAsVisitor( browser );
			expect( visitor.response.status() ).toBe( 503 );
			expect( visitor.response.headers()[ 'retry-after' ] ).toBeTruthy();
			await expect( visitor.page.locator( 'body' ) ).toContainText(
				'Back in a moment.'
			);
			await visitor.context.close();
		} finally {
			// Hand the classic flow back for the specs that follow.
			wpCli( 'option update wpmm_new_look 0' );
			wpCli( 'option patch delete wpmm_settings design page_id' );
			wpCli( 'option update show_on_front posts' );
			await setMaintenanceMode( admin, page, false );
			await requestUtils.rest( {
				path: `/wp/v2/pages/${ maintenancePage.id }`,
				method: 'DELETE',
				params: { force: true },
			} );
		}
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
