<?php
/**
 * Tests for the settings form save handler.
 */

/**
 * Test_Settings_Save.
 */
class Test_Settings_Save extends WP_UnitTestCase {

	/**
	 * Seeded settings, built once: defaults embed the current time.
	 *
	 * @var array<string, array<string, mixed>>
	 */
	private $settings;

	public function set_up(): void {
		parent::set_up();

		$this->settings = WP_Maintenance_Mode::get_instance()->default_settings();
		update_option( 'wpmm_settings', $this->settings );
		update_option( 'wpmm_new_look', '0' );
	}

	public function tear_down(): void {
		$_POST = array();
		wp_set_current_user( 0 );
		parent::tear_down();
	}

	/**
	 * Submit a settings tab form as an administrator.
	 *
	 * @param 'general'|'modules'|'bot'                     $tab    Settings tab slug.
	 * @param array{options?: string|array<string, mixed>} $fields Submitted fields besides nonce and tab.
	 * @return string The message the request ended with.
	 */
	private function save_settings( string $tab, array $fields ): string {
		$admin = wpmm_test_boot_admin();

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );

		$_POST = array_merge(
			array(
				'_wpnonce' => wp_create_nonce( 'tab-' . $tab ),
				'tab'      => $tab,
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
	public function test_malformed_payload_is_rejected_without_saving( array $fields ): void {
		$message = $this->save_settings( 'general', $fields );

		$this->assertSame( 'The submitted settings are invalid.', $message );
		$this->assertSame( $this->settings, get_option( 'wpmm_settings' ) );
	}

	/**
	 * @return array<string, array{0: 'general'|'modules'|'bot', 1: string, 2: string|array<array-key, string|list<string>>}>
	 */
	public function malformed_fields(): array {
		return array(
			'backend_role is a string'         => array( 'general', 'backend_role', 'administrator' ),
			'frontend_role is a string'        => array( 'general', 'frontend_role', 'editor' ),
			'exclude is an array'              => array( 'general', 'exclude', array( 'feed' ) ),
			'redirection is an array'          => array( 'general', 'redirection', array( 'https://example.org' ) ),
			'countdown_details is a string'    => array( 'modules', 'countdown_details', '1' ),
			'countdown day is an array'        => array( 'modules', 'countdown_details', array( 'days' => array( '1' ), 'hours' => '1', 'minutes' => '0' ) ),
			'bot messages is a string'         => array( 'bot', 'messages', 'hello' ),
		);
	}

	/**
	 * Submit an otherwise complete form for the tab with one field of the wrong type.
	 *
	 * @dataProvider malformed_fields
	 *
	 * @param 'general'|'modules'|'bot'                    $tab   Settings tab slug.
	 * @param string                                       $field Field name inside the tab.
	 * @param string|array<array-key, string|list<string>> $value Submitted value.
	 */
	public function test_malformed_field_is_rejected_without_saving( string $tab, string $field, $value ): void {
		// the stored general defaults keep exclude as a list, while the form posts a string
		$form           = 'general' === $tab ? array( 'status' => '0' ) : $this->settings[ $tab ];
		$form[ $field ] = $value;

		$message = $this->save_settings( $tab, array( 'options' => array( $tab => $form ) ) );

		$this->assertSame( 'The submitted settings are invalid.', $message );
		$this->assertSame( $this->settings, get_option( 'wpmm_settings' ) );
	}

	public function test_general_form_save_stores_status(): void {
		$message = $this->save_settings( 'general', array( 'options' => array( 'general' => array( 'status' => '1' ) ) ) );

		$this->assertSame( 'redirected', $message );
		$this->assertSame( 1, get_option( 'wpmm_settings' )['general']['status'] );
	}
}
