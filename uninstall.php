<?php

// If uninstall not called from WordPress, then exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

/**
 * Uninstall operations
 */
function single_uninstall() {
	// delete subscribers table
	$GLOBALS['wpdb']->query( "DROP TABLE IF EXISTS {$GLOBALS['wpdb']->prefix}wpmm_subscribers" );

	// delete options
	$options_to_delete = array(
		'wpmm_settings',
		'wpmm_notice',
		'wpmm_version',
	);

	foreach ( $options_to_delete as $option ) {
		delete_option( $option );
	}

	// delete dismissed notices meta key
	$users_with_dismissed_notices = (array) get_users(
		array(
			'fields'   => 'ids',
			'meta_key' => 'wpmm_dismissed_notices',
		)
	);

	foreach ( $users_with_dismissed_notices as $user_id ) {
		delete_user_meta( $user_id, 'wpmm_dismissed_notices' );
	}

	// delete bot settings file (data.js)
	$upload_dir        = wp_upload_dir();
	$bot_settings_file = ! empty( $upload_dir['basedir'] ) ? trailingslashit( $upload_dir['basedir'] ) . 'data.js' : false;

	if ( $bot_settings_file !== false && file_exists( $bot_settings_file ) ) {
		wp_delete_file( $bot_settings_file );
	}
}

// Let's do it!
if ( is_multisite() ) {
	single_uninstall();

	// delete data foreach blog
	$blogs_list = $GLOBALS['wpdb']->get_results( "SELECT blog_id FROM {$GLOBALS['wpdb']->blogs}", ARRAY_A );
	if ( ! empty( $blogs_list ) ) {
		foreach ( $blogs_list as $blog ) {
			switch_to_blog( $blog['blog_id'] );
			single_uninstall();
			restore_current_blog();
		}
	}
} else {
	single_uninstall();
}
