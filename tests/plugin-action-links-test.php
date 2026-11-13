<?php
/**
 * Tests for the plugin row action links on the Plugins screen.
 */

/**
 * Test_Plugin_Action_Links.
 */
class Test_Plugin_Action_Links extends WP_UnitTestCase {

	/**
	 * Assert that the row actions contain the LightStart Settings link.
	 *
	 * @param array<string, string> $actions Row actions returned by the action links filter.
	 * @return void
	 */
	private function assert_settings_link( array $actions ) {
		$this->assertArrayHasKey( 'wpmm_settings', $actions );
		$this->assertStringContainsString( 'admin.php?page=wp-maintenance-mode', $actions['wpmm_settings'] );
		$this->assertStringContainsString( '>Settings</a>', $actions['wpmm_settings'] );
	}

	/**
	 * The Plugins screen shows a Settings link in the plugin row.
	 */
	public function test_plugin_row_has_settings_link(): void {
		wpmm_test_boot_admin();

		// WP_Plugins_List_Table filters the row actions by the plugin file.
		$actions = apply_filters( 'plugin_action_links_' . plugin_basename( WPMM_FILE ), array() );

		$this->assert_settings_link( $actions );
	}

	/**
	 * The Network Admin Plugins screen shows a Settings link when the plugin is network-activated.
	 *
	 * Runs only in multisite mode: WP_MULTISITE=1.
	 *
	 * @group ms-required
	 */
	public function test_network_plugin_row_has_settings_link(): void {
		$this->skipWithoutMultisite();

		$plugin_file = plugin_basename( WPMM_FILE );
		update_site_option( 'active_sitewide_plugins', array( $plugin_file => time() ) );

		wpmm_test_boot_admin();

		$actions = apply_filters( 'network_admin_plugin_action_links_' . $plugin_file, array() );

		$this->assert_settings_link( $actions );
	}
}
