/**
 * WordPress dependencies
 */
import { test, expect } from '@wordpress/e2e-test-utils-playwright';

/**
 * Internal dependencies
 */
import {
	visitPluginSettings,
	activateSettingsTab,
	setMaintenanceMode,
	saveSettingsForm,
} from '../utils.js';

// Unique per run so a failed previous run's leftover user can't collide.
const RUN_ID = Date.now();
const SUBSCRIBER = {
	username: `e2e-frontend-role-${ RUN_ID }`,
	email: `e2e-frontend-role-${ RUN_ID }@example.com`,
	password: 'e2e-Str0ng-Pass!',
};

/**
 * Select the given roles in the Frontend Role setting and save.
 *
 * The visible widget is a chosen.js enhancement; the values that submit are
 * the ones on the underlying (hidden) multi-select.
 *
 * @param {Object} admin The admin fixture.
 * @param {Object} page  The admin page fixture.
 * @param {Array}  roles Role slugs to allow on the frontend.
 */
async function setFrontendRoles( admin, page, roles ) {
	await visitPluginSettings( admin );
	await activateSettingsTab( page, 'general' );

	const form = page.locator( '#tab-general form' );
	await form
		.locator( 'select[name="options[general][frontend_role][]"]' )
		.selectOption( roles, { force: true } );
	await saveSettingsForm( page, form );
}

test.describe( 'frontend role access', () => {
	test( 'a logged-in subscriber bypasses maintenance mode only while their role is allowed', async ( {
		admin,
		page,
		browser,
		requestUtils,
	} ) => {
		// Sweep users left behind by previously failed runs.
		const leftovers = await requestUtils.rest( {
			path: '/wp/v2/users',
			params: { search: 'e2e-frontend-role', per_page: 100 },
		} );
		for ( const leftover of leftovers ) {
			await requestUtils.rest( {
				path: `/wp/v2/users/${ leftover.id }`,
				method: 'DELETE',
				params: { force: true, reassign: 1 },
			} );
		}

		const user = await requestUtils.rest( {
			path: '/wp/v2/users',
			method: 'POST',
			data: {
				username: SUBSCRIBER.username,
				email: SUBSCRIBER.email,
				password: SUBSCRIBER.password,
				roles: [ 'subscriber' ],
			},
		} );

		await setMaintenanceMode( admin, page, true );
		await setFrontendRoles( admin, page, [ 'subscriber' ] );

		// Log the subscriber in through the real login form.
		const context = await browser.newContext();
		await context.clearCookies();
		const subscriberPage = await context.newPage();
		await subscriberPage.goto( '/wp-login.php' );
		await subscriberPage
			.locator( 'input[name="log"]' )
			.fill( SUBSCRIBER.username );
		await subscriberPage
			.locator( 'input[name="pwd"]' )
			.fill( SUBSCRIBER.password );
		await Promise.all( [
			subscriberPage.waitForURL( /wp-admin|profile/ ),
			subscriberPage.locator( '#wp-submit' ).click(),
		] );

		// Allowed role: the subscriber sees the real site.
		const allowed = await subscriberPage.goto( '/' );
		expect( allowed.status() ).toBe( 200 );
		await expect( subscriberPage.locator( 'body' ) ).not.toContainText(
			'Sorry for the inconvenience'
		);

		// Revoke the role: the same logged-in session is blocked again.
		await setFrontendRoles( admin, page, [] );
		const blocked = await subscriberPage.goto( '/' );
		expect( blocked.status() ).toBe( 503 );
		await expect( subscriberPage.locator( 'body' ) ).toContainText(
			'Sorry for the inconvenience'
		);
		await context.close();

		await setMaintenanceMode( admin, page, false );
		await requestUtils.rest( {
			path: `/wp/v2/users/${ user.id }`,
			method: 'DELETE',
			params: { force: true, reassign: 1 },
		} );
	} );
} );
