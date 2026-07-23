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

const DEFAULT_HEADING = 'Maintenance mode';

test.describe( 'maintenance page design', () => {
	test( 'custom title and heading show up on the maintenance page', async ( {
		admin,
		page,
		browser,
	} ) => {
		await visitPluginSettings( admin );
		await activateSettingsTab( page, 'design' );
		const designForm = page.locator( '#tab-design form' );
		await designForm
			.locator( 'input[name="options[design][title]"]' )
			.fill( 'Back soon — Acme Co', { force: true } );
		await designForm
			.locator( 'input[name="options[design][heading]"]' )
			.fill( 'We are polishing things up', { force: true } );
		await saveSettingsForm( page, designForm );

		await setMaintenanceMode( admin, page, true );

		const visitor = await openAsVisitor( browser );
		expect( visitor.response.status() ).toBe( 503 );
		await expect( visitor.page ).toHaveTitle( 'Back soon — Acme Co' );
		await expect(
			visitor.page.getByRole( 'heading', {
				name: 'We are polishing things up',
			} )
		).toBeVisible();
		await visitor.context.close();

		await setMaintenanceMode( admin, page, false );

		// Hand the design defaults back for the specs that follow.
		await visitPluginSettings( admin );
		await activateSettingsTab( page, 'design' );
		const cleanupForm = page.locator( '#tab-design form' );
		await cleanupForm
			.locator( 'input[name="options[design][title]"]' )
			.fill( DEFAULT_HEADING, { force: true } );
		await cleanupForm
			.locator( 'input[name="options[design][heading]"]' )
			.fill( DEFAULT_HEADING, { force: true } );
		await saveSettingsForm( page, cleanupForm );
	} );

	test( 'the reset button restores the design tab defaults', async ( {
		admin,
		page,
	} ) => {
		await visitPluginSettings( admin );
		await activateSettingsTab( page, 'design' );
		const designForm = page.locator( '#tab-design form' );
		const headingField = designForm.locator(
			'input[name="options[design][heading]"]'
		);
		await headingField.fill( 'A heading to be discarded', {
			force: true,
		} );
		await saveSettingsForm( page, designForm );

		// The reset control posts wpmm_reset_settings and reloads the page.
		await activateSettingsTab( page, 'design' );
		await page
			.locator( 'input.reset_settings[data-tab="design"]' )
			.click( { force: true } );

		await expect(
			page.locator( 'input[name="options[design][heading]"]' )
		).toHaveValue( DEFAULT_HEADING );
	} );
} );
