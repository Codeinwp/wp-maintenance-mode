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
 * Apply changes to the Modules tab form and save them.
 *
 * @param {Object}   admin The admin fixture.
 * @param {Object}   page  The admin page fixture.
 * @param {Function} apply Callback receiving the Modules tab form locator.
 */
async function saveModulesSettings( admin, page, apply ) {
	await visitPluginSettings( admin );
	await activateSettingsTab( page, 'modules' );

	const form = page.locator( '#tab-modules form' );
	await apply( form );
	await saveSettingsForm( page, form );
}

test.describe( 'maintenance page modules', () => {
	test( 'the countdown module renders for visitors', async ( {
		admin,
		page,
		browser,
	} ) => {
		await saveModulesSettings( admin, page, async ( form ) => {
			await form
				.locator( 'select[name="options[modules][countdown_status]"]' )
				.selectOption( '1', { force: true } );
		} );
		await setMaintenanceMode( admin, page, true );

		const visitor = await openAsVisitor( browser );
		await expect( visitor.page.locator( '.countdown' ) ).toBeAttached();
		await visitor.context.close();

		await setMaintenanceMode( admin, page, false );
		await saveModulesSettings( admin, page, async ( form ) => {
			await form
				.locator( 'select[name="options[modules][countdown_status]"]' )
				.selectOption( '0', { force: true } );
		} );
	} );

	test( 'the social module links to the configured profiles', async ( {
		admin,
		page,
		browser,
	} ) => {
		await saveModulesSettings( admin, page, async ( form ) => {
			await form
				.locator( 'select[name="options[modules][social_status]"]' )
				.selectOption( '1', { force: true } );
			await form
				.locator( 'input[name="options[modules][social_twitter]"]' )
				.fill( 'https://twitter.com/acme', { force: true } );
			await form
				.locator( 'input[name="options[modules][social_github]"]' )
				.fill( 'https://github.com/acme', { force: true } );
		} );
		await setMaintenanceMode( admin, page, true );

		const visitor = await openAsVisitor( browser );
		await expect( visitor.page.locator( '.social a.tw' ) ).toHaveAttribute(
			'href',
			'https://twitter.com/acme'
		);
		await expect(
			visitor.page.locator( '.social a.git' )
		).toHaveAttribute( 'href', 'https://github.com/acme' );
		await visitor.context.close();

		await setMaintenanceMode( admin, page, false );
		await saveModulesSettings( admin, page, async ( form ) => {
			await form
				.locator( 'select[name="options[modules][social_status]"]' )
				.selectOption( '0', { force: true } );
			await form
				.locator( 'input[name="options[modules][social_twitter]"]' )
				.fill( '', { force: true } );
			await form
				.locator( 'input[name="options[modules][social_github]"]' )
				.fill( '', { force: true } );
		} );
	} );

	test( 'the contact module renders its form fields', async ( {
		admin,
		page,
		browser,
	} ) => {
		await saveModulesSettings( admin, page, async ( form ) => {
			await form
				.locator( 'select[name="options[modules][contact_status]"]' )
				.selectOption( '1', { force: true } );
		} );
		await setMaintenanceMode( admin, page, true );

		const visitor = await openAsVisitor( browser );
		const contactForm = visitor.page.locator( '.contact form.contact_form' );
		await expect( contactForm ).toBeAttached();
		await expect(
			contactForm.locator( 'input[name="name"]' )
		).toBeAttached();
		await expect(
			contactForm.locator( 'input[name="email"]' )
		).toBeAttached();
		await expect(
			contactForm.locator( 'textarea[name="content"]' )
		).toBeAttached();
		await visitor.context.close();

		await setMaintenanceMode( admin, page, false );
		await saveModulesSettings( admin, page, async ( form ) => {
			await form
				.locator( 'select[name="options[modules][contact_status]"]' )
				.selectOption( '0', { force: true } );
		} );
	} );

	test( 'the Google Analytics module embeds the tracking code', async ( {
		admin,
		page,
		browser,
	} ) => {
		await saveModulesSettings( admin, page, async ( form ) => {
			await form
				.locator( 'select[name="options[modules][ga_status]"]' )
				.selectOption( '1', { force: true } );
			await form
				.locator( 'input[name="options[modules][ga_code]"]' )
				.fill( 'G-E2ETRACK1', { force: true } );
		} );
		await setMaintenanceMode( admin, page, true );

		const visitor = await openAsVisitor( browser );
		expect( await visitor.page.content() ).toContain( 'G-E2ETRACK1' );
		await visitor.context.close();

		await setMaintenanceMode( admin, page, false );
		await saveModulesSettings( admin, page, async ( form ) => {
			await form
				.locator( 'select[name="options[modules][ga_status]"]' )
				.selectOption( '0', { force: true } );
			await form
				.locator( 'input[name="options[modules][ga_code]"]' )
				.fill( '', { force: true } );
		} );
	} );

	test( 'the GDPR module adds a consent checkbox to the subscribe form', async ( {
		admin,
		page,
		browser,
	} ) => {
		await saveModulesSettings( admin, page, async ( form ) => {
			await form
				.locator( 'select[name="options[modules][subscribe_status]"]' )
				.selectOption( '1', { force: true } );
		} );

		await visitPluginSettings( admin );
		await activateSettingsTab( page, 'gdpr' );
		const gdprForm = page.locator( '#tab-gdpr form' );
		await gdprForm
			.locator( 'input[name="options[gdpr][status]"][value="1"]' )
			.check( { force: true } );
		await saveSettingsForm( page, gdprForm );

		await setMaintenanceMode( admin, page, true );

		const visitor = await openAsVisitor( browser );
		const subscribeForm = visitor.page.locator( 'form.subscribe_form' );
		await expect(
			subscribeForm.locator( 'input[name="acceptance"]' )
		).toBeAttached();
		await expect( subscribeForm.locator( '.privacy_tail' ) ).toContainText(
			'Privacy Policy'
		);
		await visitor.context.close();

		await setMaintenanceMode( admin, page, false );

		await visitPluginSettings( admin );
		await activateSettingsTab( page, 'gdpr' );
		const gdprCleanup = page.locator( '#tab-gdpr form' );
		await gdprCleanup
			.locator( 'input[name="options[gdpr][status]"][value="0"]' )
			.check( { force: true } );
		await saveSettingsForm( page, gdprCleanup );

		await saveModulesSettings( admin, page, async ( form ) => {
			await form
				.locator( 'select[name="options[modules][subscribe_status]"]' )
				.selectOption( '0', { force: true } );
		} );
	} );
} );
