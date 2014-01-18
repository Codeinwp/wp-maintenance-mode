<?php

if ( ! function_exists( 'fb_find_wp_config_path' ) ) {
	function fb_find_wp_config_path() {
		
		$dir = dirname(__FILE__);
		
		do {
			if( file_exists( $dir . "/wp-config.php" ) ) {
				return $dir;
				var_dump($dir);
			}
		} while ( $dir = realpath( "$dir/.." ) );
		
		return NULL;
	}
}

if ( ! defined( 'ABSPATH' ) ) {
	define( 'WP_USE_THEMES', FALSE );
	require_once( fb_find_wp_config_path() . '/wp-config.php' );
}

if( ! defined( 'ABSPATH' ) || ! current_user_can('unfiltered_html') ) {
	wp_die( __('Cheatin&#8217; uh?') );
	exit;
}
// Allow alternative splash page
if ( ! file_exists( WP_CONTENT_DIR . '/wp-maintenance-mode.php' ) )
	include 'site.php';
