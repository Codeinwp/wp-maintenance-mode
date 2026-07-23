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
} );
