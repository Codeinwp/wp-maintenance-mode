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

	/**
	 * Boot a fresh admin instance and simulate the insert-template AJAX call.
	 *
	 * @param array $settings Value for the wpmm_settings option.
	 * @return void
	 */
	private function apply_template( $settings, $template_slug = 'coming-soon-1', $category = 'coming-soon' ) {
		$this->boot_plugin( $settings );

		if ( ! class_exists( 'WP_Maintenance_Mode_Admin' ) ) {
			require_once WPMM_CLASSES_PATH . 'wp-maintenance-mode-admin.php';
		}

		$instance = new ReflectionProperty( 'WP_Maintenance_Mode_Admin', 'instance' );
		$instance->setAccessible( true );
		$instance->setValue( null, null );

		$admin = WP_Maintenance_Mode_Admin::get_instance();
		$admin->load_default_settings();

		$_POST = array(
			'_wpnonce'      => wp_create_nonce( 'tab-design' ),
			'source'        => 'tab-design',
			'template_slug' => $template_slug,
			'category'      => $category,
		);

		$die_handler = function () {
			return function () {
				throw new WPDieException( 'json response sent' );
			};
		};
		// Outside of admin-ajax.php, wp_send_json_*() ends with a bare `die`;
		// claiming AJAX context routes it through a catchable die handler instead.
		add_filter( 'wp_doing_ajax', '__return_true' );
		add_filter( 'wp_die_ajax_handler', $die_handler );

		ob_start();
		try {
			$admin->insert_template();
		} catch ( WPDieException $e ) {
			// wp_send_json_*() ends the request with wp_die(); expected.
		}
		ob_end_clean();

		remove_filter( 'wp_doing_ajax', '__return_true' );
		remove_filter( 'wp_die_ajax_handler', $die_handler );
		$_POST = array();
	}

	public function test_applying_template_to_user_page_creates_new_page_instead_of_overwriting() {
		$page_id = self::factory()->post->create(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_content' => 'My real homepage content.',
			)
		);
		update_post_meta( $page_id, '_wp_page_template', 'custom-template.php' );

		// What select_page() does when the user picks an existing page.
		wpmm_record_page_state( $page_id );
		update_post_meta( $page_id, '_wp_page_template', 'templates/wpmm-page-template.php' );

		$this->apply_template( $this->make_settings( 0, $page_id ) );

		// The user's page is handed back untouched.
		$this->assertSame( 'publish', get_post_status( $page_id ) );
		$this->assertSame( 'My real homepage content.', get_post( $page_id )->post_content );
		$this->assertSame( 'custom-template.php', get_post_meta( $page_id, '_wp_page_template', true ) );

		// The template landed on a newly created, plugin-owned page instead.
		$settings    = get_option( 'wpmm_settings' );
		$new_page_id = (int) $settings['design']['page_id'];

		$this->assertNotSame( $page_id, $new_page_id );
		$this->assertSame( 'private', get_post_status( $new_page_id ) );
		$this->assertNotEmpty( get_post_meta( $new_page_id, '_wpmm_generated', true ) );
		$this->assertNotEmpty( get_post( $new_page_id )->post_content );
	}

	public function test_applying_template_to_generated_page_updates_it_in_place() {
		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'private',
			)
		);
		update_post_meta( $page_id, '_wpmm_generated', 1 );

		$this->apply_template( $this->make_settings( 0, $page_id ) );

		$settings = get_option( 'wpmm_settings' );

		$this->assertSame( $page_id, (int) $settings['design']['page_id'] );
		$this->assertNotEmpty( get_post( $page_id )->post_content );
	}

	public function test_applying_unknown_template_is_rejected() {
		$pages_before = count( get_posts( array( 'post_type' => 'page', 'post_status' => 'any', 'numberposts' => -1 ) ) );

		$this->apply_template( $this->make_settings( 0, 0 ), '../../nonexistent', 'coming-soon' );

		$settings    = get_option( 'wpmm_settings' );
		$pages_after = count( get_posts( array( 'post_type' => 'page', 'post_status' => 'any', 'numberposts' => -1 ) ) );

		$this->assertSame( 0, (int) $settings['design']['page_id'], 'selection must not change' );
		$this->assertSame( $pages_before, $pages_after, 'no page must be created' );
	}

	public function test_enabled_mode_with_deleted_page_writes_nothing() {
		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
			)
		);
		wp_delete_post( $page_id, true );

		$this->boot_plugin( $this->make_settings( 1, $page_id ) );

		$this->assertFalse( metadata_exists( 'post', $page_id, '_wp_page_template' ) );
		$this->assertFalse( metadata_exists( 'post', $page_id, '_wpmm_original_status' ) );
	}

	public function test_trashed_page_is_not_resurrected_on_disable() {
		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
			)
		);

		wpmm_record_page_state( $page_id );

		// The user deliberately trashes the page while it is selected.
		wp_trash_post( $page_id );

		$this->boot_plugin( $this->make_settings( 0, $page_id ) );
		do_action( 'init' );

		$this->assertSame( 'trash', get_post_status( $page_id ) );
	}

	public function test_enabled_mode_with_trashed_page_records_nothing() {
		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
			)
		);
		wp_trash_post( $page_id );

		$this->boot_plugin( $this->make_settings( 1, $page_id ) );

		$this->assertFalse( metadata_exists( 'post', $page_id, '_wpmm_original_status' ) );
	}

	public function test_recording_twice_does_not_poison_the_original_state() {
		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
			)
		);
		update_post_meta( $page_id, '_wp_page_template', 'custom-template.php' );

		wpmm_record_page_state( $page_id );

		// The plugin takes the page over...
		wp_update_post(
			array(
				'ID'          => $page_id,
				'post_status' => 'private',
			)
		);
		update_post_meta( $page_id, '_wp_page_template', 'templates/wpmm-page-template.php' );

		// ...and the user re-selects the same page while it is in that state.
		wpmm_record_page_state( $page_id );

		wpmm_restore_page_state( $page_id );

		$this->assertSame( 'publish', get_post_status( $page_id ) );
		$this->assertSame( 'custom-template.php', get_post_meta( $page_id, '_wp_page_template', true ) );
	}

	public function test_page_edits_after_restore_become_the_new_baseline() {
		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
			)
		);
		update_post_meta( $page_id, '_wp_page_template', 'custom-template.php' );

		// First take-over / hand-back cycle.
		wpmm_record_page_state( $page_id );
		update_post_meta( $page_id, '_wp_page_template', 'templates/wpmm-page-template.php' );
		wpmm_restore_page_state( $page_id );

		// The user changes the page's template, then the plugin takes it over again.
		update_post_meta( $page_id, '_wp_page_template', 'new-template.php' );
		wpmm_record_page_state( $page_id );
		update_post_meta( $page_id, '_wp_page_template', 'templates/wpmm-page-template.php' );
		wpmm_restore_page_state( $page_id );

		$this->assertSame( 'new-template.php', get_post_meta( $page_id, '_wp_page_template', true ) );
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
