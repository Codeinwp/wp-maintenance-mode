<?php
if( ! defined( 'ABSPATH' ) && ! defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();

add_site_option( 'wp-maintenance-mode' );
add_site_option( 'wp-maintenance-mode-msqd' );
delete_option( 'wp-maintenance-mode' );
delete_option( 'wp-maintenance-mode-msqd' );
