/**
 * External dependencies
 */
import fs from 'fs';

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
	openAsVisitor,
} from '../utils.js';

/**
 * Enable or disable the subscribe module on the Modules tab.
 *
 * @param {Object}  admin   The admin fixture.
 * @param {Object}  page    The admin page fixture.
 * @param {boolean} enabled Whether the subscribe form should show.
 */
async function setSubscribeModule( admin, page, enabled ) {
	await visitPluginSettings( admin );
	await activateSettingsTab( page, 'modules' );

	const modulesForm = page.locator( '#tab-modules form' );
	await modulesForm
		.locator( 'select[name="options[modules][subscribe_status]"]' )
		.selectOption( enabled ? '1' : '0', { force: true } );
	await saveSettingsForm( page, modulesForm );
}

test.describe( 'subscribe form on the maintenance page', () => {
	test( 'a visitor can subscribe and gets a confirmation', async ( {
		admin,
		page,
		browser,
	} ) => {
		await setSubscribeModule( admin, page, true );
		await setMaintenanceMode( admin, page, true );

		const visitor = await openAsVisitor( browser );
		expect( visitor.response.status() ).toBe( 503 );

		const email = `visitor-${ Date.now() }@example.com`;
		await visitor.page.getByPlaceholder( 'your e-mail...' ).fill( email );
		await visitor.page
			.locator( 'input[type="submit"][value="Subscribe"]' )
			.click();

		// The frontend script posts to admin-ajax (wpmm_add_subscriber) and
		// swaps the form for the server's confirmation message.
		await expect(
			visitor.page.locator( '.subscribe_wrapper' )
		).toContainText( 'You successfully subscribed. Thanks!' );
		await visitor.context.close();

		await setMaintenanceMode( admin, page, false );
		await setSubscribeModule( admin, page, false );
	} );

	test( 'client-side validation rejects an invalid email before anything is sent', async ( {
		admin,
		page,
		browser,
	} ) => {
		await setSubscribeModule( admin, page, true );
		await setMaintenanceMode( admin, page, true );

		const visitor = await openAsVisitor( browser );
		await visitor.page
			.getByPlaceholder( 'your e-mail...' )
			.fill( 'not-an-email' );
		await visitor.page
			.locator( 'input[type="submit"][value="Subscribe"]' )
			.click();

		// jquery-validate blocks the submit and renders an inline error (the
		// stylesheet keeps the label itself hidden in this layout); the form
		// stays in place instead of the confirmation message.
		await expect( visitor.page.locator( 'label.error' ) ).toContainText(
			'Please enter a valid email address.'
		);
		await expect(
			visitor.page.locator( 'form.subscribe_form' )
		).toBeAttached();
		await expect( visitor.page.locator( 'body' ) ).not.toContainText(
			'You successfully subscribed'
		);
		await visitor.context.close();

		await setMaintenanceMode( admin, page, false );
		await setSubscribeModule( admin, page, false );
	} );

	test( 'an admin can see, export, and empty the subscribers list', async ( {
		admin,
		page,
		browser,
	} ) => {
		await setSubscribeModule( admin, page, true );
		await setMaintenanceMode( admin, page, true );

		// Start from a clean list; the buttons only render when there are rows.
		await visitPluginSettings( admin );
		await activateSettingsTab( page, 'modules' );
		const emptyButton = page.locator( '#subscribers-empty-list' );
		if ( await emptyButton.count() ) {
			await emptyButton.click( { force: true } );
			await expect( page.locator( '#subscribers_wrap' ) ).toContainText(
				'You have 0 subscribers'
			);
		}

		const email = `exported-${ Date.now() }@example.com`;
		const visitor = await openAsVisitor( browser );
		await visitor.page.getByPlaceholder( 'your e-mail...' ).fill( email );
		await visitor.page
			.locator( 'input[type="submit"][value="Subscribe"]' )
			.click();
		await expect(
			visitor.page.locator( '.subscribe_wrapper' )
		).toContainText( 'You successfully subscribed' );
		await visitor.context.close();

		// The Modules tab reflects the new subscriber.
		await visitPluginSettings( admin );
		await activateSettingsTab( page, 'modules' );
		await expect( page.locator( '#subscribers_wrap' ) ).toContainText(
			'You have 1 subscriber'
		);

		// Export downloads a CSV containing the subscriber (the click loads
		// the admin-ajax export URL in a hidden iframe).
		const downloadPromise = page.waitForEvent( 'download' );
		await page.locator( '#subscribers-export' ).click( { force: true } );
		const download = await downloadPromise;
		const csv = fs.readFileSync( await download.path(), 'utf8' );
		expect( csv ).toContain( 'email' );
		expect( csv ).toContain( email );

		// Emptying the list resets the counter without a reload.
		await page
			.locator( '#subscribers-empty-list' )
			.click( { force: true } );
		await expect( page.locator( '#subscribers_wrap' ) ).toContainText(
			'You have 0 subscribers'
		);

		await setMaintenanceMode( admin, page, false );
		await setSubscribeModule( admin, page, false );
	} );
} );
