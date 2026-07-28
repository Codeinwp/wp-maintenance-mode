/**
 * Shared helpers for the wp-maintenance-mode E2E specs.
 *
 * A note on the force: true interactions below: after any form POST + redirect
 * (every settings save), headless Chromium stops producing animation frames
 * for the page. Playwright's default actionability check waits for the element
 * to be stable across two animation frames, so every non-forced interaction
 * after a save hangs until timeout. The elements are static admin form
 * controls, and each helper verifies the resulting state explicitly, so
 * skipping the actionability wait is safe here.
 */

/**
 * WordPress dependencies
 */
import { expect } from '@wordpress/e2e-test-utils-playwright';

/**
 * Open the plugin settings screen (LightStart top-level menu page).
 *
 * @param {Object} admin The @wordpress/e2e-test-utils-playwright admin fixture.
 */
export async function visitPluginSettings( admin ) {
	await admin.visitAdminPage( 'admin.php', 'page=wp-maintenance-mode' );
}

/**
 * Activate a tab on the plugin settings screen and wait for its panel.
 *
 * Navigating to the hash-less settings URL while another tab's hash is set is
 * a same-document navigation, so the previous tab could still be active —
 * always activate explicitly instead of relying on the default.
 *
 * @param {Object} page The admin page fixture.
 * @param {string} tab  Tab slug: general, design, modules, bot, gdpr.
 */
export async function activateSettingsTab( page, tab ) {
	const link = page.locator( `a.nav-tab[href="#${ tab }"]` );
	await expect( link ).toBeVisible();
	await link.click( { force: true } );
	await expect( page.locator( `#tab-${ tab }` ) ).toBeVisible();
}

/**
 * Submit a settings tab form and wait for the save round-trip.
 *
 * The submit goes through admin-post.php and redirects back to the settings
 * page with ?updated=1, which WordPress strips from the address bar right
 * after load — so wait for the redirect response instead of the URL.
 *
 * @param {Object} page The admin page fixture.
 * @param {Object} form Locator of the tab's settings form.
 */
export async function saveSettingsForm( page, form ) {
	await Promise.all( [
		page.waitForResponse( ( response ) =>
			response.url().includes( 'updated=1' )
		),
		form
			.getByRole( 'button', { name: 'Save settings' } )
			.click( { force: true } ),
	] );
	await page.waitForLoadState();
}

/**
 * Toggle maintenance mode through the General tab of the settings screen.
 *
 * @param {Object}  admin   The admin fixture.
 * @param {Object}  page    The admin page fixture.
 * @param {boolean} enabled Whether maintenance mode should be on.
 */
export async function setMaintenanceMode( admin, page, enabled ) {
	await visitPluginSettings( admin );
	await activateSettingsTab( page, 'general' );

	const generalForm = page.locator( '#tab-general form' );
	const radio = generalForm.locator(
		`input[name="options[general][status]"][value="${ enabled ? 1 : 0 }"]`
	);
	await expect( radio ).toBeVisible();
	await radio.check( { force: true } );

	await saveSettingsForm( page, generalForm );

	// The chosen status still being checked after the reload proves it persisted.
	await expect(
		page.locator(
			`input[name="options[general][status]"][value="${ enabled ? 1 : 0 }"]`
		)
	).toBeChecked();
}

/**
 * Open a page as a logged-out visitor and return { page, response, context }.
 *
 * @param {Object} browser        The Playwright browser fixture.
 * @param {string} url            URL (or path) to open.
 * @param {Object} contextOptions Extra context options (e.g. userAgent).
 */
export async function openAsVisitor( browser, url = '/', contextOptions = {} ) {
	// Contexts created through the runner's browser fixture inherit the
	// config's admin storageState, so drop the login cookies explicitly.
	const context = await browser.newContext( contextOptions );
	await context.clearCookies();
	const page = await context.newPage();
	const response = await page.goto( url );

	return { context, page, response };
}
