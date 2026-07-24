<?php
/**
 * Tests for frontend behaviors of WP_Maintenance_Mode: the page-template
 * integration, the Retry-After backtime, the Google Analytics snippet, and
 * the v1.8 → v2 settings migration on activation.
 */

/**
 * Test_Frontend_Behavior.
 */
class Test_Frontend_Behavior extends WP_UnitTestCase {

	/**
	 * Frontend singleton.
	 *
	 * @var WP_Maintenance_Mode
	 */
	protected static $frontend;

	public static function wpSetUpBeforeClass( $factory ) {
		self::$frontend = WP_Maintenance_Mode::get_instance();
	}

	public function set_up() {
		parent::set_up();
		$this->set_plugin_settings( self::$frontend->default_settings() );
	}

	/**
	 * Point the frontend singleton at the given settings (setup plumbing; the
	 * singleton hydrates its cache once at plugins_loaded).
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
	 * Page template integration
	 */

	public function test_the_plugin_template_is_offered_in_the_page_templates_dropdown() {
		$templates = apply_filters( 'theme_page_templates', array( 'existing.php' => 'Existing' ) );

		$this->assertArrayHasKey( 'templates/wpmm-page-template.php', $templates );
		$this->assertArrayHasKey( 'existing.php', $templates );
	}

	public function test_pages_using_the_plugin_template_load_the_plugin_view() {
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		update_post_meta( $page_id, '_wp_page_template', 'templates/wpmm-page-template.php' );
		$GLOBALS['post'] = get_post( $page_id );

		$template = self::$frontend->use_maintenance_template( 'page.php' );

		$this->assertSame( WPMM_VIEWS_PATH . 'wpmm-page-template.php', $template );
	}

	public function test_pages_without_the_plugin_template_keep_the_theme_template() {
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		$GLOBALS['post'] = get_post( $page_id );

		$this->assertSame( 'page.php', self::$frontend->use_maintenance_template( 'page.php' ) );
	}

	/*
	 * calculate_backtime() — feeds the Retry-After header
	 */

	public function test_backtime_defaults_to_an_hour() {
		$this->assertSame( 3600, self::$frontend->calculate_backtime() );
	}

	public function test_backtime_follows_the_countdown_settings() {
		$settings = self::$frontend->default_settings();
		$settings['modules']['countdown_status']  = 1;
		$settings['modules']['countdown_details'] = array(
			'days'    => 1,
			'hours'   => 2,
			'minutes' => 30,
		);
		$this->set_plugin_settings( $settings );

		// 1 day + 2 hours + 30 minutes, from the worked example.
		$this->assertSame( 95400, self::$frontend->calculate_backtime() );
	}

	/*
	 * add_google_analytics_code()
	 */

	public function test_google_analytics_snippet_is_skipped_while_disabled() {
		ob_start();
		$result = self::$frontend->add_google_analytics_code();
		$output = ob_get_clean();

		$this->assertFalse( $result );
		$this->assertSame( '', $output );
	}

	public function test_google_analytics_snippet_renders_the_sanitized_code() {
		$settings = self::$frontend->default_settings();
		$settings['modules']['ga_status'] = 1;
		$settings['modules']['ga_code']   = '<b>injected</b>G-UNIT99';
		$this->set_plugin_settings( $settings );

		ob_start();
		self::$frontend->add_google_analytics_code();
		$output = ob_get_clean();

		$this->assertStringContainsString( 'G-UNIT99', $output );
		$this->assertStringNotContainsString( '<b>', $output );
	}

	/*
	 * v1.8 → v2 settings migration
	 */

	public function test_activation_migrates_v18_options_into_the_v2_settings() {
		delete_option( 'wpmm_settings' );
		delete_option( 'wpmm_notice' );
		update_option(
			'wp-maintenance-mode',
			array(
				'active'        => 1,
				'bypass'        => 1,
				'index'         => 1,
				'rewrite'       => 'https://example.com/elsewhere',
				'exclude'       => array( 'my-old-slug' ),
				'notice'        => 0,
				'role'          => array( 'editor' ),
				'role_frontend' => array( 'editor' ),
			)
		);

		WP_Maintenance_Mode::single_activate();

		$settings = get_option( 'wpmm_settings' );
		$this->assertEquals( 1, $settings['general']['status'] );
		$this->assertEquals( 1, $settings['general']['bypass_bots'] );
		$this->assertEquals( 1, $settings['general']['meta_robots'] );
		$this->assertSame( 'https://example.com/elsewhere', $settings['general']['redirection'] );
		$this->assertContains( 'my-old-slug', $settings['general']['exclude'] );
		$this->assertContains( 'wp-login', $settings['general']['exclude'] );
		$this->assertEquals( 0, $settings['general']['notice'] );
		$this->assertSame( array( 'editor' ), $settings['general']['backend_role'] );
		$this->assertSame( array( 'editor' ), $settings['general']['frontend_role'] );

		// Upgraded installs are pointed at the relaunched settings screen.
		$notice = get_option( 'wpmm_notice' );
		$this->assertNotEmpty( $notice['msg'] );
	}
}
