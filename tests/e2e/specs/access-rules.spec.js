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
	saveSettingsForm,
	openAsVisitor,
} from '../utils.js';

const DEFAULT_EXCLUDE_LIST = 'feed\nwp-login\nlogin';
const GOOGLEBOT_UA =
	'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)';

/**
 * Apply changes to the General tab form and save them in one round-trip.
 *
 * @param {Object}   admin The admin fixture.
 * @param {Object}   page  The admin page fixture.
 * @param {Function} apply Callback receiving the General tab form locator.
 */
async function saveGeneralSettings( admin, page, apply ) {
	await visitPluginSettings( admin );
	await activateSettingsTab( page, 'general' );

	const form = page.locator( '#tab-general form' );
	await apply( form );
	await saveSettingsForm( page, form );
}

test.describe( 'maintenance mode access rules', () => {
	test( 'excluded URLs stay reachable for visitors while maintenance mode is active', async ( {
		admin,
		page,
		browser,
		requestUtils,
	} ) => {
		await requestUtils.rest( {
			path: '/wp/v2/pages',
			method: 'POST',
			data: {
				title: 'Status update',
				slug: 'status-update',
				status: 'publish',
				content: 'All systems operational.',
			},
		} );

		await saveGeneralSettings( admin, page, async ( form ) => {
			await form
				.locator( 'input[name="options[general][status]"][value="1"]' )
				.check( { force: true } );
			await form
				.locator( 'textarea[name="options[general][exclude]"]' )
				.fill( `${ DEFAULT_EXCLUDE_LIST }\nstatus-update`, {
					force: true,
				} );
		} );

		// The excluded page is served normally.
		const excluded = await openAsVisitor( browser, '/status-update/' );
		expect( excluded.response.status() ).toBe( 200 );
		await expect( excluded.page.locator( 'body' ) ).toContainText(
			'All systems operational.'
		);
		await excluded.context.close();

		// Everything else is still under maintenance.
		const visitor = await openAsVisitor( browser );
		expect( visitor.response.status() ).toBe( 503 );
		await visitor.context.close();

		await saveGeneralSettings( admin, page, async ( form ) => {
			await form
				.locator( 'input[name="options[general][status]"][value="0"]' )
				.check( { force: true } );
			await form
				.locator( 'textarea[name="options[general][exclude]"]' )
				.fill( DEFAULT_EXCLUDE_LIST, { force: true } );
		} );
	} );

	test( 'search bots see the site while bot bypass is enabled', async ( {
		admin,
		page,
		browser,
	} ) => {
		await saveGeneralSettings( admin, page, async ( form ) => {
			await form
				.locator( 'input[name="options[general][status]"][value="1"]' )
				.check( { force: true } );
			await form
				.locator( 'select[name="options[general][bypass_bots]"]' )
				.selectOption( '1', { force: true } );
		} );

		// A crawler identifying as Googlebot gets the real site.
		const bot = await openAsVisitor( browser, '/', {
			userAgent: GOOGLEBOT_UA,
		} );
		expect( bot.response.status() ).toBe( 200 );
		await expect( bot.page.locator( 'body' ) ).not.toContainText(
			'Sorry for the inconvenience'
		);
		await bot.context.close();

		// A regular visitor still gets the maintenance page.
		const visitor = await openAsVisitor( browser );
		expect( visitor.response.status() ).toBe( 503 );
		await visitor.context.close();

		await saveGeneralSettings( admin, page, async ( form ) => {
			await form
				.locator( 'input[name="options[general][status]"][value="0"]' )
				.check( { force: true } );
			await form
				.locator( 'select[name="options[general][bypass_bots]"]' )
				.selectOption( '0', { force: true } );
		} );
	} );
} );
