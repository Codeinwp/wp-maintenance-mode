<?php
if( ! defined( 'ABSPATH' ) && ! defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();

delete_site_option( 'wp-maintenance-mode' );
delete_site_option( 'wp-maintenance-mode-msqld' );
delete_option( 'wp-maintenance-mode' );
delete_option( 'wp-maintenance-mode-msqld' );
