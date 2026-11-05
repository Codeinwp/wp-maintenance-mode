<?php
/**
 * Tests for the settings form save handler.
 */

/**
 * Test_Settings_Save.
 */
class Test_Settings_Save extends WP_UnitTestCase {

	public function tear_down(): void {
		$_POST = array();
		wp_set_current_user( 0 );
		parent::tear_down();
	}

	/**
	 * Submit the General tab form as an administrator.
	 *
	 * @param array{options?: string|array{general: string|array{status: string}}} $fields Submitted fields besides nonce and tab.
	 * @return string The message the request ended with.
	 */
	private function save_general_settings( array $fields ): string {
		$settings                      = WP_Maintenance_Mode::get_instance()->default_settings();
		$settings['general']['status'] = 0;
		update_option( 'wpmm_settings', $settings );

		$admin = wpmm_test_boot_admin();

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );

		$_POST = array_merge(
			array(
				'_wpnonce' => wp_create_nonce( 'tab-general' ),
				'tab'      => 'general',
			),
			$fields
		);

		$redirect_catcher = function (): void {
			// the handler ends with wp_safe_redirect() + exit
			throw new WPDieException( 'redirected' );
		};
		add_filter( 'wp_redirect', $redirect_catcher );

		try {
			$admin->save_plugin_settings();
			$message = 'no response';
		} catch ( WPDieException $e ) {
			$message = $e->getMessage();
		} finally {
			remove_filter( 'wp_redirect', $redirect_catcher );
		}

		return $message;
	}

	/**
	 * @return array<string, array{0: array{options?: string|array{general: string}}}>
	 */
	public function malformed_payloads(): array {
		return array(
			'options is a string'          => array( array( 'options' => 'status' ) ),
			'options is an empty string'   => array( array( 'options' => '' ) ),
			'options[general] is a string' => array( array( 'options' => array( 'general' => 'status' ) ) ),
			'options is missing'           => array( array() ),
		);
	}

	/**
	 * @dataProvider malformed_payloads
	 *
	 * @param array{options?: string|array{general: string}} $fields Submitted fields besides nonce and tab.
	 */
	public function test_malformed_general_payload_is_rejected_without_saving( array $fields ): void {
		$message = $this->save_general_settings( $fields );

		$this->assertSame( 'The submitted settings are invalid.', $message );
		$this->assertSame( 0, get_option( 'wpmm_settings' )['general']['status'] );
	}

	public function test_general_form_save_stores_status(): void {
		$message = $this->save_general_settings( array( 'options' => array( 'general' => array( 'status' => '1' ) ) ) );

		$this->assertSame( 'redirected', $message );
		$this->assertSame( 1, get_option( 'wpmm_settings' )['general']['status'] );
	}
}
