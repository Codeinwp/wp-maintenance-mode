<?php
/**
 * Tests for the public helper functions in includes/functions/helpers.php.
 */

/**
 * Test_Helpers.
 */
class Test_Helpers extends WP_UnitTestCase {

	/*
	 * wpmm_sanitize_ga_code()
	 */

	public function test_sanitize_ga_code_accepts_universal_analytics_ids() {
		$this->assertSame( 'UA-1234567-1', wpmm_sanitize_ga_code( 'UA-1234567-1' ) );
	}

	public function test_sanitize_ga_code_accepts_ga4_ids() {
		$this->assertSame( 'G-AB12CD34EF', wpmm_sanitize_ga_code( 'G-AB12CD34EF' ) );
	}

	public function test_sanitize_ga_code_extracts_the_id_from_markup() {
		$this->assertSame( 'UA-12345', wpmm_sanitize_ga_code( '<script>alert(1)</script>UA-12345' ) );
	}

	public function test_sanitize_ga_code_rejects_input_without_an_id() {
		$this->assertSame( '', wpmm_sanitize_ga_code( 'definitely not a tracking code' ) );
	}

	/*
	 * wpmm_get_capability()
	 */

	public function test_capability_defaults_to_manage_options() {
		$this->assertSame( 'manage_options', wpmm_get_capability( 'settings' ) );
	}

	public function test_capability_honors_the_action_specific_filter() {
		add_filter(
			'wpmm_settings_capability',
			function () {
				return 'edit_pages';
			}
		);

		$this->assertSame( 'edit_pages', wpmm_get_capability( 'settings' ) );
		$this->assertSame( 'manage_options', wpmm_get_capability( 'subscribers' ) );
	}

	public function test_capability_global_filter_overrides_action_specific_one() {
		add_filter(
			'wpmm_settings_capability',
			function () {
				return 'edit_pages';
			}
		);
		add_filter(
			'wpmm_all_actions_capability',
			function () {
				return 'manage_network';
			}
		);

		$this->assertSame( 'manage_network', wpmm_get_capability( 'settings' ) );
	}

	/*
	 * wpmm_form_hidden_fields()
	 */

	public function test_form_hidden_fields_output_a_valid_nonce_and_routing_fields() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );

		ob_start();
		wpmm_form_hidden_fields( 'general' );
		$output = ob_get_clean();

		$this->assertStringContainsString( 'value="general" name="tab"', $output );
		$this->assertStringContainsString( 'value="wpmm_save_settings" name="action"', $output );

		// The rendered nonce must verify against the tab-specific action.
		$this->assertSame( 1, preg_match( '/id="_wpnonce" name="_wpnonce" value="([^"]+)"/', $output, $matches ) );
		$this->assertNotFalse( wp_verify_nonce( $matches[1], 'tab-general' ) );
	}

	/*
	 * wpmm_get_option()
	 */

	public function test_get_option_strips_slashes_from_stored_values() {
		update_option( 'wpmm_settings', array( 'design' => array( 'title' => "It\\'s coming soon" ) ) );

		$value = wpmm_get_option( 'wpmm_settings' );

		$this->assertSame( "It's coming soon", $value['design']['title'] );
	}

	public function test_get_option_returns_the_default_when_the_option_is_missing() {
		delete_option( 'wpmm_settings' );

		$this->assertSame( 'fallback', wpmm_get_option( 'wpmm_settings', 'fallback' ) );
	}
}
