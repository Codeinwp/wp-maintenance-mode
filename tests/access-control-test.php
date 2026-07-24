<?php
/**
 * Tests for the frontend access-control decisions — who gets past an active
 * maintenance mode: user roles, excluded slugs/IPs, and search bots.
 */

/**
 * Test_Access_Control.
 */
class Test_Access_Control extends WP_UnitTestCase {

	/**
	 * Frontend singleton.
	 *
	 * @var WP_Maintenance_Mode
	 */
	protected static $frontend;

	/**
	 * Snapshot of $_SERVER, restored after each test.
	 *
	 * @var array
	 */
	protected $server_backup = array();

	public static function wpSetUpBeforeClass( $factory ) {
		self::$frontend = WP_Maintenance_Mode::get_instance();
	}

	public function set_up() {
		parent::set_up();

		$this->server_backup = $_SERVER;
		$this->set_plugin_settings( self::$frontend->default_settings() );
	}

	public function tear_down() {
		$_SERVER = $this->server_backup;
		parent::tear_down();
	}

	/**
	 * Point the frontend singleton at the given settings.
	 *
	 * The singleton hydrates its settings cache once at plugins_loaded, so the
	 * cache has to be refreshed for per-test settings (setup plumbing, not an
	 * assertion target).
	 *
	 * @param array $settings A full wpmm_settings array.
	 */
	protected function set_plugin_settings( array $settings ) {
		update_option( 'wpmm_settings', $settings );

		$prop = new ReflectionProperty( 'WP_Maintenance_Mode', 'plugin_settings' );
		$prop->setAccessible( true );
		$prop->setValue( self::$frontend, $settings );
	}

	/*
	 * check_user_role()
	 */

	public function test_administrators_bypass_maintenance_mode() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );

		$this->assertTrue( self::$frontend->check_user_role() );
	}

	public function test_subscribers_are_blocked_by_default() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );

		$this->assertFalse( self::$frontend->check_user_role() );
	}

	public function test_logged_out_visitors_are_blocked() {
		wp_set_current_user( 0 );

		$this->assertFalse( self::$frontend->check_user_role() );
	}

	public function test_frontend_role_setting_grants_access() {
		$settings = self::$frontend->default_settings();
		$settings['general']['frontend_role'] = array( 'subscriber' );
		$this->set_plugin_settings( $settings );

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );

		$this->assertTrue( self::$frontend->check_user_role() );
	}

	/*
	 * check_exclude()
	 */

	public function test_feed_urls_are_excluded_by_default() {
		$_SERVER['REQUEST_URI'] = '/feed/';

		$this->assertTrue( self::$frontend->check_exclude() );
	}

	public function test_regular_urls_are_not_excluded() {
		$_SERVER['REQUEST_URI'] = '/some-page/';

		$this->assertFalse( self::$frontend->check_exclude() );
	}

	public function test_custom_slug_in_exclude_list_is_excluded() {
		$settings = self::$frontend->default_settings();
		$settings['general']['exclude'][] = 'status-update';
		$this->set_plugin_settings( $settings );

		$_SERVER['REQUEST_URI'] = '/status-update/';

		$this->assertTrue( self::$frontend->check_exclude() );
	}

	public function test_visitor_ip_in_exclude_list_is_excluded() {
		$settings = self::$frontend->default_settings();
		$settings['general']['exclude'][] = '10.11.12.13';
		$this->set_plugin_settings( $settings );

		$_SERVER['REQUEST_URI'] = '/some-page/';
		$_SERVER['REMOTE_ADDR'] = '10.11.12.13';

		$this->assertTrue( self::$frontend->check_exclude() );
	}

	public function test_exclude_entries_support_trailing_comments() {
		$settings = self::$frontend->default_settings();
		$settings['general']['exclude'][] = 'status-update # public status page';
		$this->set_plugin_settings( $settings );

		$_SERVER['REQUEST_URI'] = '/status-update/';

		$this->assertTrue( self::$frontend->check_exclude() );
	}

	/*
	 * check_search_bots()
	 */

	public function test_search_bots_bypass_when_the_option_is_enabled() {
		$settings = self::$frontend->default_settings();
		$settings['general']['bypass_bots'] = 1;
		$this->set_plugin_settings( $settings );

		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)';

		$this->assertTrue( self::$frontend->check_search_bots() );
	}

	public function test_search_bots_are_blocked_while_the_option_is_disabled() {
		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)';

		$this->assertFalse( self::$frontend->check_search_bots() );
	}

	public function test_regular_browsers_are_not_treated_as_bots() {
		$settings = self::$frontend->default_settings();
		$settings['general']['bypass_bots'] = 1;
		$this->set_plugin_settings( $settings );

		$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0 Safari/537.36';

		$this->assertFalse( self::$frontend->check_search_bots() );
	}
}
