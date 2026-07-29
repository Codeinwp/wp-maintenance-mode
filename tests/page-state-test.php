<?php
/**
 * Tests for the selected-page state lifecycle.
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

	/**
	 * Simulate the select-page AJAX call the Design tab dropdown fires.
	 *
	 * @param array $settings Value for the wpmm_settings option.
	 * @param int   $page_id  Page being selected.
	 * @return void
	 */
	private function select_page( $settings, $page_id ) {
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
			'_wpnonce' => wp_create_nonce( 'tab-design' ),
			'page_id'  => (string) $page_id,
		);

		$die_handler = function () {
			return function () {
				throw new WPDieException( 'json response sent' );
			};
		};
		add_filter( 'wp_doing_ajax', '__return_true' );
		add_filter( 'wp_die_ajax_handler', $die_handler );

		ob_start();
		try {
			$admin->select_page();
		} catch ( WPDieException $e ) {
			// wp_send_json_*() ends the request with wp_die(); expected.
		}
		ob_end_clean();

		remove_filter( 'wp_doing_ajax', '__return_true' );
		remove_filter( 'wp_die_ajax_handler', $die_handler );
		$_POST = array();
	}

	public function test_select_page_workflow_leaves_selected_homepage_public() {
		$page_id = self::factory()->post->create(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_content' => 'Real homepage content.',
			)
		);

		// 1. The user selects their existing homepage in Design > Select page.
		$this->select_page( $this->make_settings( 0, 0 ), $page_id );

		$settings = get_option( 'wpmm_settings' );
		$this->assertSame( $page_id, (int) $settings['design']['page_id'] );

		// 2. Maintenance mode is enabled...
		$this->boot_plugin( $this->make_settings( 1, $page_id ) );

		// 3. ...then disabled, and another request runs the init callback.
		$this->boot_plugin( $this->make_settings( 0, $page_id ) );
		do_action( 'init' );

		// The homepage is publicly available in its original state.
		$this->assertSame( 'publish', get_post_status( $page_id ) );
		$this->assertSame( 'Real homepage content.', get_post( $page_id )->post_content );
		$this->assertSame( '', get_post_meta( $page_id, '_wp_page_template', true ) );
	}

	public function test_changing_selection_restores_the_previously_selected_page() {
		$first_page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
			)
		);
		update_post_meta( $first_page_id, '_wp_page_template', 'custom-template.php' );

		$second_page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
			)
		);

		$this->select_page( $this->make_settings( 0, 0 ), $first_page_id );
		$this->assertSame( 'templates/wpmm-page-template.php', get_post_meta( $first_page_id, '_wp_page_template', true ) );

		$this->select_page( $this->make_settings( 0, $first_page_id ), $second_page_id );

		// The first page is handed back with its own template, and its record is cleared.
		$this->assertSame( 'custom-template.php', get_post_meta( $first_page_id, '_wp_page_template', true ) );
		$this->assertFalse( metadata_exists( 'post', $first_page_id, '_wpmm_original_state' ) );

		$settings = get_option( 'wpmm_settings' );
		$this->assertSame( $second_page_id, (int) $settings['design']['page_id'] );
	}

	public function test_clearing_selection_restores_the_previously_selected_page() {
		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'private',
			)
		);

		$this->select_page( $this->make_settings( 0, 0 ), $page_id );

		// Maintenance mode takes the page over and publishes it.
		$this->boot_plugin( $this->make_settings( 1, $page_id ) );

		$this->select_page( $this->make_settings( 0, $page_id ), 0 );

		$this->assertSame( 'private', get_post_status( $page_id ) );
		$this->assertFalse( metadata_exists( 'post', $page_id, '_wpmm_original_state' ) );
	}

	public function test_private_status_chosen_during_active_mode_is_preserved() {
		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
			)
		);

		$this->select_page( $this->make_settings( 0, 0 ), $page_id );
		$this->boot_plugin( $this->make_settings( 1, $page_id ) );

		// The user makes the page private while maintenance mode is active.
		wp_update_post(
			array(
				'ID'          => $page_id,
				'post_status' => 'private',
			)
		);

		// Any later request while the mode is still active must not republish it.
		$this->boot_plugin( $this->make_settings( 1, $page_id ) );
		$this->assertSame( 'private', get_post_status( $page_id ) );

		// And disabling the mode must not resurrect the pre-private status either.
		$this->boot_plugin( $this->make_settings( 0, $page_id ) );
		do_action( 'init' );
		$this->assertSame( 'private', get_post_status( $page_id ) );
	}

	public function test_replugin_published_page_reprivated_by_user_stays_private() {
		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'private',
			)
		);

		// The plugin legitimately publishes the originally private page once.
		$this->boot_plugin( $this->make_settings( 1, $page_id ) );
		$this->assertSame( 'publish', get_post_status( $page_id ) );

		// The user puts it back to private while the mode is still active.
		wp_update_post(
			array(
				'ID'          => $page_id,
				'post_status' => 'private',
			)
		);

		// The plugin already used its one publish; the user's choice stays.
		$this->boot_plugin( $this->make_settings( 1, $page_id ) );
		$this->assertSame( 'private', get_post_status( $page_id ) );

		$this->boot_plugin( $this->make_settings( 0, $page_id ) );
		do_action( 'init' );
		$this->assertSame( 'private', get_post_status( $page_id ) );
	}

	public function test_status_change_made_by_the_user_survives_disable() {
		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
			)
		);

		// The user selects the page and enables maintenance mode...
		$this->select_page( $this->make_settings( 0, 0 ), $page_id );
		$this->boot_plugin( $this->make_settings( 1, $page_id ) );

		// ...then deliberately unpublishes the page while the mode is active.
		wp_update_post(
			array(
				'ID'          => $page_id,
				'post_status' => 'draft',
			)
		);

		$this->boot_plugin( $this->make_settings( 0, $page_id ) );
		do_action( 'init' );

		// The draft is the user's latest intent; disabling must not republish it.
		$this->assertSame( 'draft', get_post_status( $page_id ) );
	}

	public function test_template_installed_by_an_old_release_is_not_recorded_as_original() {
		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
			)
		);
		// A selection carried over from an old plugin release already wears
		// the maintenance template; it must not be mistaken for user state.
		update_post_meta( $page_id, '_wp_page_template', 'templates/wpmm-page-template.php' );

		$this->boot_plugin( $this->make_settings( 1, $page_id ) );

		$this->boot_plugin( $this->make_settings( 0, $page_id ) );
		do_action( 'init' );

		$this->assertSame( '', get_post_meta( $page_id, '_wp_page_template', true ) );
	}

	public function test_restoring_a_trashed_page_keeps_trash_but_clears_template_and_record() {
		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
			)
		);
		update_post_meta( $page_id, '_wp_page_template', 'custom-template.php' );

		wpmm_record_page_state( $page_id );
		update_post_meta( $page_id, '_wp_page_template', 'templates/wpmm-page-template.php' );

		// The user deliberately trashes the page, then the plugin hands it back.
		wp_trash_post( $page_id );
		wpmm_restore_page_state( $page_id );

		// The status is untouched, but the page no longer carries plugin state.
		$this->assertSame( 'trash', get_post_status( $page_id ) );
		$this->assertSame( 'custom-template.php', get_post_meta( $page_id, '_wp_page_template', true ) );
		$this->assertFalse( metadata_exists( 'post', $page_id, '_wpmm_original_state' ) );
		$this->assertFalse( metadata_exists( 'post', $page_id, '_wpmm_applied_status' ) );
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
		$this->assertFalse( metadata_exists( 'post', $page_id, '_wpmm_original_state' ) );
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

		$this->assertFalse( metadata_exists( 'post', $page_id, '_wpmm_original_state' ) );
	}

	public function test_recording_twice_does_not_poison_the_original_state() {
		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'private',
			)
		);
		update_post_meta( $page_id, '_wp_page_template', 'custom-template.php' );

		// The plugin takes the page over: applies its template and publishes.
		$this->boot_plugin( $this->make_settings( 1, $page_id ) );

		// The user re-selects the same page while it is in that state.
		wpmm_record_page_state( $page_id );

		wpmm_restore_page_state( $page_id );

		$this->assertSame( 'private', get_post_status( $page_id ) );
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
				'post_status' => 'private',
			)
		);

		// Maintenance mode is active: the page is taken over and published.
		$this->boot_plugin( $this->make_settings( 1, $page_id ) );
		$this->assertSame( 'publish', get_post_status( $page_id ) );

		// Deactivating the plugin hands the page back, private again.
		WP_Maintenance_Mode::single_deactivate();

		$this->assertSame( 'private', get_post_status( $page_id ) );
	}
}
