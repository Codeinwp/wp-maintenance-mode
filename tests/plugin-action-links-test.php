<?php
/**
 * Tests for the plugin row action links on the Plugins screen.
 */

/**
 * Test_Plugin_Action_Links.
 */
class Test_Plugin_Action_Links extends WP_UnitTestCase {

	/**
	 * Boot a fresh admin instance, the same way plugins_loaded does on admin requests.
	 *
	 * @return WP_Maintenance_Mode_Admin
	 */
	private function boot_admin() {
		if ( ! class_exists( 'WP_Maintenance_Mode_Admin' ) ) {
			require_once WPMM_CLASSES_PATH . 'wp-maintenance-mode-admin.php';
		}

		$instance = new ReflectionProperty( 'WP_Maintenance_Mode_Admin', 'instance' );
		$instance->setAccessible( true );
		$instance->setValue( null, null );

		$admin = WP_Maintenance_Mode_Admin::get_instance();

		// init fires after plugins_loaded, before the Plugins screen renders.
		$admin->load_default_settings();

		return $admin;
	}

	/**
	 * The Plugins screen shows a Settings link in the plugin row.
	 */
	public function test_plugin_row_has_settings_link() {
		$this->boot_admin();

		// WP_Plugins_List_Table filters the row actions by the plugin file.
		$actions = apply_filters( 'plugin_action_links_' . plugin_basename( WPMM_FILE ), array() );

		$this->assertArrayHasKey( 'wpmm_settings', $actions );
		$this->assertStringContainsString( 'admin.php?page=wp-maintenance-mode', $actions['wpmm_settings'] );
		$this->assertStringContainsString( '>Settings</a>', $actions['wpmm_settings'] );
	}
}
