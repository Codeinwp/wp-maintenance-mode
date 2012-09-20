<?php
if( ! defined( 'ABSPATH' ) && ! defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();

if ( is_multisite() && is_plugin_active_for_network( 'wp-maintenance-mode/wp-maintenance-mode.php' ) ) {
	add_site_option( 'wp-maintenance-mode' );
	add_site_option( 'wp-maintenance-mode-msqd' );
} else {
	delete_option( 'wp-maintenance-mode' );
	delete_option( 'wp-maintenance-mode-msqd' );
}
