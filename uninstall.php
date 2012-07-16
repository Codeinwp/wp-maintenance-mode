<?php
if( ! defined( 'ABSPATH') && ! defined('WP_UNINSTALL_PLUGIN') )
	exit();

if ( is_multisite() && isset($_GET['networkwide']) && 1 == $_GET['networkwide'] ) {
	add_site_option( 'wp-maintenance-mode' );
	add_site_option( 'wp-maintenance-mode-msqd' );
} else {
	delete_option( 'wp-maintenance-mode' );
	delete_option( 'wp-maintenance-mode-msqd' );
}
