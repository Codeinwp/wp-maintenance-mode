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

	test( 'search bots see the site only while bot bypass is enabled', async ( {
		admin,
		page,
		browser,
	} ) => {
		// Bypass off: a crawler is blocked like any visitor.
		await saveGeneralSettings( admin, page, async ( form ) => {
			await form
				.locator( 'input[name="options[general][status]"][value="1"]' )
				.check( { force: true } );
			await form
				.locator( 'select[name="options[general][bypass_bots]"]' )
				.selectOption( '0', { force: true } );
		} );

		const blockedBot = await openAsVisitor( browser, '/', {
			userAgent: GOOGLEBOT_UA,
		} );
		expect( blockedBot.response.status() ).toBe( 503 );
		await blockedBot.context.close();

		// Bypass on: the crawler gets the real site.
		await saveGeneralSettings( admin, page, async ( form ) => {
			await form
				.locator( 'select[name="options[general][bypass_bots]"]' )
				.selectOption( '1', { force: true } );
		} );

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

	test( 'feeds and the REST API stay reachable during maintenance', async ( {
		admin,
		page,
		browser,
	} ) => {
		await saveGeneralSettings( admin, page, async ( form ) => {
			await form
				.locator( 'input[name="options[general][status]"][value="1"]' )
				.check( { force: true } );
		} );

		// "feed" is on the default exclude list.
		const feed = await openAsVisitor( browser, '/feed/' );
		expect( feed.response.status() ).toBe( 200 );
		expect( feed.response.headers()[ 'content-type' ] ).toContain( 'xml' );
		await feed.context.close();

		// REST requests never reach the template_redirect takeover; this pins
		// the current behavior so a change to it is a conscious decision.
		const rest = await openAsVisitor( browser, '/wp-json/wp/v2/types' );
		expect( rest.response.status() ).toBe( 200 );
		await rest.context.close();

		await saveGeneralSettings( admin, page, async ( form ) => {
			await form
				.locator( 'input[name="options[general][status]"][value="0"]' )
				.check( { force: true } );
		} );
	} );

	test( 'the maintenance response advertises Retry-After and the configured robots policy', async ( {
		admin,
		page,
		browser,
	} ) => {
		await saveGeneralSettings( admin, page, async ( form ) => {
			await form
				.locator( 'input[name="options[general][status]"][value="1"]' )
				.check( { force: true } );
			await form
				.locator( 'select[name="options[general][meta_robots]"]' )
				.selectOption( '1', { force: true } );
		} );

		const visitor = await openAsVisitor( browser );
		expect( visitor.response.status() ).toBe( 503 );
		expect(
			Number( visitor.response.headers()[ 'retry-after' ] )
		).toBeGreaterThan( 0 );
		// WordPress core injects its own robots meta (max-image-preview), so
		// assert the plugin's tag specifically.
		await expect(
			visitor.page.locator(
				'meta[name="robots"][content="noindex, nofollow"]'
			)
		).toBeAttached();
		await visitor.context.close();

		await saveGeneralSettings( admin, page, async ( form ) => {
			await form
				.locator( 'input[name="options[general][status]"][value="0"]' )
				.check( { force: true } );
			await form
				.locator( 'select[name="options[general][meta_robots]"]' )
				.selectOption( '0', { force: true } );
		} );
	} );
} );
