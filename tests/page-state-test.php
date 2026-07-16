<?php
/**
 * Tests for the selected-page state lifecycle (issue #523).
 */

/**
 * Test_Page_State.
 */
class Test_Page_State extends WP_UnitTestCase {

	/**
	 * Boot a fresh plugin instance with the given settings, as if WordPress just loaded.
	 *
	 * @param array $settings Value for the wpmm_settings option.
	 * @return WP_Maintenance_Mode
	 */
	private function boot_plugin( $settings ) {
		update_option( 'wpmm_settings', $settings );
		update_option( 'wpmm_new_look', '1' );

		$instance = new ReflectionProperty( 'WP_Maintenance_Mode', 'instance' );
		$instance->setAccessible( true );
		$instance->setValue( null, null );

		return WP_Maintenance_Mode::get_instance();
	}

	/**
	 * Minimal settings array for the code paths under test.
	 *
	 * @param int $status  Maintenance mode status (0/1).
	 * @param int $page_id Selected page ID.
	 * @return array
	 */
	private function make_settings( $status, $page_id ) {
		$settings = WP_Maintenance_Mode::get_instance()->default_settings();

		$settings['general']['status']  = $status;
		$settings['design']['page_id']  = $page_id;

		return $settings;
	}

	public function test_disabled_mode_leaves_selected_published_page_public() {
		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
			)
		);

		$this->boot_plugin( $this->make_settings( 0, $page_id ) );
		do_action( 'init' );

		$this->assertSame( 'publish', get_post_status( $page_id ) );
	}

	public function test_enable_then_disable_returns_private_page_to_private() {
		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'private',
			)
		);

		// Enabling maintenance mode publishes the private page so it can be served.
		$this->boot_plugin( $this->make_settings( 1, $page_id ) );
		$this->assertSame( 'publish', get_post_status( $page_id ) );

		// Disabling it puts the page back to private, not just any page to private.
		$this->boot_plugin( $this->make_settings( 0, $page_id ) );
		do_action( 'init' );
		$this->assertSame( 'private', get_post_status( $page_id ) );
	}

	public function test_user_page_template_is_restored_on_disable_and_reapplied_on_enable() {
		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
			)
		);
		update_post_meta( $page_id, '_wp_page_template', 'custom-template.php' );

		// What select_page() does when the user picks an existing page.
		wpmm_record_page_state( $page_id );
		update_post_meta( $page_id, '_wp_page_template', 'templates/wpmm-page-template.php' );

		// Disabling maintenance mode restores the page's own template and status.
		$this->boot_plugin( $this->make_settings( 0, $page_id ) );
		do_action( 'init' );
		$this->assertSame( 'custom-template.php', get_post_meta( $page_id, '_wp_page_template', true ) );
		$this->assertSame( 'publish', get_post_status( $page_id ) );

		// Re-enabling applies the maintenance template again.
		$this->boot_plugin( $this->make_settings( 1, $page_id ) );
		$this->assertSame( 'templates/wpmm-page-template.php', get_post_meta( $page_id, '_wp_page_template', true ) );
	}

	public function test_plugin_deactivation_restores_selected_page() {
		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
			)
		);

		update_option( 'wpmm_settings', $this->make_settings( 0, $page_id ) );
		wpmm_record_page_state( $page_id );

		// Simulate the state a broken site is in: selected page left private.
		wp_update_post(
			array(
				'ID'          => $page_id,
				'post_status' => 'private',
			)
		);

		WP_Maintenance_Mode::single_deactivate();

		$this->assertSame( 'publish', get_post_status( $page_id ) );
	}
}
