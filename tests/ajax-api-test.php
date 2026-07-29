<?php
/**
 * Tests for the plugin's admin-ajax API.
 *
 * Covers the public AJAX seams registered by the plugin:
 * - frontend (nopriv): wpmm_add_subscriber, wpmm_send_contact
 * - admin: wpmm_reset_settings, wpmm_select_page, wpmm_skip_wizard,
 *   wpmm_skip_insert_template, wpmm_change_template_category,
 *   wpmm_toggle_gutenberg, wpmm_subscribe, wpmm_subscribers_empty_list,
 *   wpmm_subscribers_export, wpmm_dismiss_notices
 *
 * Handlers that guard failures with a plain die() (not wp_die) cannot have
 * those guard paths exercised here — a plain die() would terminate the PHPUnit
 * process — so only their JSON-responding paths are covered.
 */

/**
 * Thrown by the test die handler instead of the suite's
 * WPAjaxDieContinueException. It extends Error on purpose: several plugin
 * handlers wrap wp_send_json_*() in a catch-all try/catch ( Exception ),
 * which would swallow an Exception-based die signal and answer a second time.
 */
class WPMM_Ajax_Die_Signal extends Error {
}

/**
 * Test_Ajax_Api.
 */
class Test_Ajax_Api extends WP_Ajax_UnitTestCase {

	/**
	 * Frontend singleton.
	 *
	 * @var WP_Maintenance_Mode
	 */
	protected static $frontend;

	/**
	 * Admin singleton.
	 *
	 * @var WP_Maintenance_Mode_Admin
	 */
	protected static $admin;

	public static function wpSetUpBeforeClass( $factory ) {
		// Creates the wpmm_subscribers table and the default options.
		WP_Maintenance_Mode::single_activate();
		update_option( 'wpmm_settings', WP_Maintenance_Mode::get_instance()->default_settings() );


		self::$frontend = WP_Maintenance_Mode::get_instance();

		// The frontend singleton was built during bootstrap, before wpmm_settings
		// existed; refresh its settings cache so handlers see the defaults.
		$settings = new ReflectionProperty( 'WP_Maintenance_Mode', 'plugin_settings' );
		$settings->setAccessible( true );
		$settings->setValue( self::$frontend, get_option( 'wpmm_settings' ) );

		// The admin class is only loaded on is_admin() requests, which the test
		// bootstrap is not; its constructor registers all wp_ajax_wpmm_* hooks.
		require_once WPMM_CLASSES_PATH . 'wp-maintenance-mode-admin.php';
		self::$admin = WP_Maintenance_Mode_Admin::get_instance();

		// The instance normally hydrates its settings caches on init, which has
		// already fired by the time the class is loaded here.
		self::$admin->load_default_settings();
	}

	public function set_up() {
		parent::set_up();

		global $wpdb;
		$wpdb->query( "DELETE FROM {$wpdb->prefix}wpmm_subscribers" );
		update_option( 'wpmm_settings', WP_Maintenance_Mode::get_instance()->default_settings() );

		// Hook registration must happen per test: the suite snapshots the hook
		// state once per process (first test's set_up) and every tear_down
		// restores that snapshot, so anything registered in a class-level
		// fixture evaporates after the first test of this class.
		//
		// The frontend AJAX hooks are only registered while maintenance mode is
		// enabled at plugins_loaded; register them against the same handlers.
		add_action( 'wp_ajax_nopriv_wpmm_add_subscriber', array( self::$frontend, 'add_subscriber' ) );
		add_action( 'wp_ajax_nopriv_wpmm_send_contact', array( self::$frontend, 'send_contact' ) );

		// The admin constructor registered its wp_ajax_* hooks when the
		// singleton was created in wpSetUpBeforeClass; re-register the ones
		// under test against the same instance.
		add_action( 'wp_ajax_wpmm_subscribers_export', array( self::$admin, 'subscribers_export' ) );
		add_action( 'wp_ajax_wpmm_subscribers_empty_list', array( self::$admin, 'subscribers_empty_list' ) );
		add_action( 'wp_ajax_wpmm_dismiss_notices', array( self::$admin, 'dismiss_notices' ) );
		add_action( 'wp_ajax_wpmm_reset_settings', array( self::$admin, 'reset_plugin_settings' ) );
		add_action( 'wp_ajax_wpmm_select_page', array( self::$admin, 'select_page' ) );
		add_action( 'wp_ajax_wpmm_skip_insert_template', array( self::$admin, 'skip_insert_template' ) );
		add_action( 'wp_ajax_wpmm_skip_wizard', array( self::$admin, 'skip_wizard' ) );
		add_action( 'wp_ajax_wpmm_subscribe', array( self::$admin, 'subscribe_newsletter' ) );
		add_action( 'wp_ajax_wpmm_change_template_category', array( self::$admin, 'change_template_category' ) );
		add_action( 'wp_ajax_wpmm_toggle_gutenberg', array( self::$admin, 'toggle_gutenberg' ) );

		// _handleAjax() fires admin_init without defining DOING_AJAX; whenever a
		// test leaves the install marked fresh, maybe_redirect() would redirect
		// to the wizard and exit, killing the PHPUnit process.
		remove_action( 'admin_init', array( self::$admin, 'maybe_redirect' ) );

		// The ajax testcase removes these in a class-level fixture, which the
		// per-process hook snapshot undoes; without the removal, admin_init
		// reaches out to wordpress.org for core/plugin/theme update checks.
		remove_action( 'admin_init', '_maybe_update_core' );
		remove_action( 'admin_init', '_maybe_update_plugins' );
		remove_action( 'admin_init', '_maybe_update_themes' );

		// Replace the suite's die handler (registered at priority 1) with one
		// that signals via an Error the plugin's catch-all blocks cannot eat.
		add_filter( 'wp_die_ajax_handler', array( $this, 'get_error_safe_die_handler' ), 2 );
	}

	/**
	 * Filter callback returning the die handler used during these tests.
	 *
	 * @return callable
	 */
	public function get_error_safe_die_handler() {
		return array( $this, 'error_safe_die_handler' );
	}

	/**
	 * Collects the response like the suite's die handler, then stops the
	 * handler with an Error-based signal.
	 *
	 * @param string|int $message The wp_die message.
	 * @throws WPMM_Ajax_Die_Signal
	 */
	public function error_safe_die_handler( $message ) {
		$this->_last_response .= ob_get_clean();

		throw new WPMM_Ajax_Die_Signal( is_scalar( $message ) ? (string) $message : '' );
	}

	/**
	 * Run a handler and return the decoded JSON response.
	 *
	 * @param string $action The ajax action, with the nopriv_ prefix when logged out.
	 * @return array
	 */
	protected function handle( $action ) {
		try {
			$this->_handleAjax( $action );
		} catch ( WPMM_Ajax_Die_Signal $e ) {
			// wp_send_json_*() stops here; the payload is in _last_response.
			unset( $e );
		}

		return json_decode( $this->_last_response, true );
	}

	/*
	 * wpmm_add_subscriber (nopriv)
	 */

	public function test_add_subscriber_rejects_invalid_email() {
		wp_set_current_user( 0 );
		$_POST['email']    = 'not-an-email';
		$_POST['_wpnonce'] = wp_create_nonce( 'wpmts_nonce_subscribe' );

		$response = $this->handle( 'nopriv_wpmm_add_subscriber' );

		$this->assertFalse( $response['success'] );
		$this->assertSame( 'Please enter a valid email address.', $response['data'] );
		$this->assertSame( 0, wpmm_get_subscribers_count() );
	}

	public function test_add_subscriber_rejects_missing_nonce() {
		wp_set_current_user( 0 );
		$_POST['email'] = 'visitor@example.com';

		$response = $this->handle( 'nopriv_wpmm_add_subscriber' );

		$this->assertFalse( $response['success'] );
		$this->assertSame( 'Security check.', $response['data'] );
		$this->assertSame( 0, wpmm_get_subscribers_count() );
	}

	public function test_add_subscriber_saves_subscriber_once() {
		wp_set_current_user( 0 );
		$_POST['email']    = 'visitor@example.com';
		$_POST['_wpnonce'] = wp_create_nonce( 'wpmts_nonce_subscribe' );

		$response = $this->handle( 'nopriv_wpmm_add_subscriber' );

		$this->assertTrue( $response['success'] );
		$this->assertSame( 'You successfully subscribed. Thanks!', $response['data'] );
		$this->assertSame( 1, wpmm_get_subscribers_count() );

		// Subscribing the same address twice must not create a duplicate.
		$this->_last_response = '';
		$this->handle( 'nopriv_wpmm_add_subscriber' );
		$this->assertSame( 1, wpmm_get_subscribers_count() );
	}

	/*
	 * wpmm_send_contact (nopriv)
	 */

	public function test_send_contact_requires_all_fields() {
		wp_set_current_user( 0 );
		$_POST['name']  = 'Visitor';
		$_POST['email'] = 'visitor@example.com';
		// no content

		$response = $this->handle( 'nopriv_wpmm_send_contact' );

		$this->assertFalse( $response['success'] );
		$this->assertSame( 'All fields required.', $response['data'] );
	}

	public function test_send_contact_emails_the_site_admin() {
		reset_phpmailer_instance();

		// wp-env sites live on localhost, and PHPMailer rejects the resulting
		// default sender wordpress@localhost as an invalid address.
		add_filter(
			'wp_mail_from',
			function () {
				return 'wordpress@example.com';
			}
		);

		wp_set_current_user( 0 );
		$_POST['name']     = 'Visitor';
		$_POST['email']    = 'visitor@example.com';
		$_POST['content']  = 'Hello, when will the site be back?';
		$_POST['_wpnonce'] = wp_create_nonce( 'wpmts_nonce_contact' );

		$response = $this->handle( 'nopriv_wpmm_send_contact' );

		$this->assertTrue( $response['success'] );

		$mailer = tests_retrieve_phpmailer_instance();
		$this->assertSame( get_option( 'admin_email' ), $mailer->get_recipient( 'to' )->address );
		$this->assertStringContainsString( 'when will the site be back', $mailer->get_sent()->body );

		reset_phpmailer_instance();
	}

	/*
	 * wpmm_reset_settings
	 */

	public function test_reset_settings_requires_capability() {
		$this->_setRole( 'subscriber' );
		$_POST['tab']      = 'design';
		$_POST['_wpnonce'] = wp_create_nonce( 'tab-design' );

		$response = $this->handle( 'wpmm_reset_settings' );

		$this->assertFalse( $response['success'] );
		$this->assertSame( 'You do not have access to this resource.', $response['data'] );
	}

	public function test_reset_settings_restores_tab_defaults() {
		$this->_setRole( 'administrator' );

		$settings                     = get_option( 'wpmm_settings' );
		$settings['design']['title']  = 'Customized title';
		update_option( 'wpmm_settings', $settings );

		$_POST['tab']      = 'design';
		$_POST['_wpnonce'] = wp_create_nonce( 'tab-design' );

		$response = $this->handle( 'wpmm_reset_settings' );

		$this->assertTrue( $response['success'] );

		$defaults = WP_Maintenance_Mode::get_instance()->default_settings();
		$saved    = get_option( 'wpmm_settings' );
		$this->assertSame( $defaults['design']['title'], $saved['design']['title'] );
	}

	/*
	 * wpmm_select_page
	 */

	public function test_select_page_updates_selection_and_template() {
		$this->_setRole( 'administrator' );

		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_title'  => 'Future maintenance page',
			)
		);

		$_POST['page_id']  = (string) $page_id;
		$_POST['_wpnonce'] = wp_create_nonce( 'tab-design' );

		$response = $this->handle( 'wpmm_select_page' );

		$this->assertTrue( $response['success'] );

		$saved = get_option( 'wpmm_settings' );
		$this->assertEquals( $page_id, $saved['design']['page_id'] );
		$this->assertSame( 'templates/wpmm-page-template.php', get_post_meta( $page_id, '_wp_page_template', true ) );
	}

	/*
	 * wpmm_skip_wizard / wpmm_skip_insert_template
	 */

	public function test_skip_wizard_marks_install_as_not_fresh() {
		$this->_setRole( 'administrator' );
		update_option( 'wpmm_fresh_install', '1' );

		$_POST['_wpnonce'] = wp_create_nonce( 'wizard' );

		$response = $this->handle( 'wpmm_skip_wizard' );

		$this->assertTrue( $response['success'] );
		$this->assertEmpty( get_option( 'wpmm_fresh_install' ) );
	}

	public function test_skip_insert_template_marks_install_as_not_fresh() {
		$this->_setRole( 'administrator' );
		update_option( 'wpmm_fresh_install', '1' );

		$_POST['_wpnonce'] = wp_create_nonce( 'wizard' );

		$response = $this->handle( 'wpmm_skip_insert_template' );

		$this->assertTrue( $response['success'] );
		$this->assertEmpty( get_option( 'wpmm_fresh_install' ) );
	}

	/*
	 * wpmm_change_template_category
	 */

	public function test_change_template_category_updates_settings() {
		$this->_setRole( 'administrator' );

		$_POST['category'] = 'coming-soon';
		$_POST['_wpnonce'] = wp_create_nonce( 'tab-design' );

		$response = $this->handle( 'wpmm_change_template_category' );

		$this->assertTrue( $response['success'] );

		$saved = get_option( 'wpmm_settings' );
		$this->assertSame( 'coming-soon', $saved['design']['template_category'] );
	}

	/*
	 * wpmm_toggle_gutenberg
	 */

	public function test_toggle_gutenberg_flips_the_new_look_option() {
		$this->_setRole( 'administrator' );
		delete_option( 'wpmm_new_look' );
		delete_option( 'wpmm_migration_time' );

		$_POST['source']   = 'dashboard';
		$_POST['_wpnonce'] = wp_create_nonce( 'notice_nonce_dashboard' );

		$response = $this->handle( 'wpmm_toggle_gutenberg' );

		$this->assertTrue( $response['success'] );
		$this->assertTrue( (bool) get_option( 'wpmm_new_look' ) );
		$this->assertNotEmpty( get_option( 'wpmm_migration_time' ) );
	}

	/*
	 * wpmm_subscribe (plugin newsletter, external HTTP)
	 */

	public function test_subscribe_newsletter_posts_the_email_to_the_remote_service() {
		$this->_setRole( 'administrator' );

		$captured = null;
		add_filter(
			'pre_http_request',
			function ( $preempt, $args, $url ) use ( &$captured ) {
				if ( WP_Maintenance_Mode_Admin::SUBSCRIBE_ROUTE !== $url ) {
					return $preempt;
				}

				$captured = array(
					'url'  => $url,
					'body' => $args['body'],
				);

				return array(
					'headers'  => array(),
					'body'     => '{}',
					'response' => array(
						'code'    => 200,
						'message' => 'OK',
					),
				);
			},
			10,
			3
		);

		$_POST['email']    = 'owner@example.com';
		$_POST['_wpnonce'] = wp_create_nonce( 'wizard' );

		$response = $this->handle( 'wpmm_subscribe' );

		$this->assertTrue( $response['success'] );
		$this->assertNotNull( $captured );
		$this->assertStringContainsString( 'owner@example.com', $captured['body'] );
	}

	public function test_subscribe_newsletter_reports_remote_failures() {
		$this->_setRole( 'administrator' );

		add_filter(
			'pre_http_request',
			function ( $preempt, $args, $url ) {
				if ( WP_Maintenance_Mode_Admin::SUBSCRIBE_ROUTE !== $url ) {
					return $preempt;
				}

				return new WP_Error( 'http_request_failed', 'Could not resolve host.' );
			},
			10,
			3
		);

		$_POST['email']    = 'owner@example.com';
		$_POST['_wpnonce'] = wp_create_nonce( 'wizard' );

		$response = $this->handle( 'wpmm_subscribe' );

		$this->assertFalse( $response['success'] );
		$this->assertSame( 'Could not resolve host.', $response['data'] );
	}

	/*
	 * wpmm_subscribers_empty_list / wpmm_subscribers_export
	 */

	public function test_subscribers_empty_list_deletes_all_subscribers() {
		$this->_setRole( 'administrator' );

		self::$frontend->insert_subscriber( 'visitor@example.com' );
		$this->assertSame( 1, wpmm_get_subscribers_count() );

		$_POST['_wpnonce'] = wp_create_nonce( 'tab-modules' );

		$response = $this->handle( 'wpmm_subscribers_empty_list' );

		$this->assertTrue( $response['success'] );
		$this->assertSame( 0, wpmm_get_subscribers_count() );
	}

	public function test_subscribers_export_requires_capability() {
		$this->_setRole( 'subscriber' );

		$_GET['_wpnonce'] = wp_create_nonce( 'tab-modules' );

		$response = $this->handle( 'wpmm_subscribers_export' );

		$this->assertFalse( $response['success'] );
		$this->assertSame( 'You do not have access to this resource.', $response['data'] );
	}

	/*
	 * wpmm_dismiss_notices
	 */

	public function test_dismiss_notices_records_the_notice_for_the_user() {
		$this->_setRole( 'administrator' );

		$_POST['notice_key'] = 'rate';
		$_POST['_nonce']     = wp_create_nonce( 'notice_nonce_rate' );

		$response = $this->handle( 'wpmm_dismiss_notices' );

		$this->assertTrue( $response['success'] );
		$this->assertContains( 'rate', self::$admin->get_dismissed_notices( get_current_user_id() ) );
	}

	public function test_dismiss_notices_rejects_a_foreign_nonce() {
		$this->_setRole( 'administrator' );

		$_POST['notice_key'] = 'rate';
		$_POST['_nonce']     = wp_create_nonce( 'notice_nonce_other' );

		$response = $this->handle( 'wpmm_dismiss_notices' );

		$this->assertFalse( $response['success'] );
		$this->assertSame( 'Security check.', $response['data'] );
	}
}
