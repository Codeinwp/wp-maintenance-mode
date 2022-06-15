<?php
/**
 * Notices
 *
 * @version 2.4.0
 */

defined( 'ABSPATH' ) || exit;

foreach ( $notices as $key => $notice ) {
	if ( in_array( $key, $dismissed_notices, true ) ) {
		continue;
	}

	printf( '<div id="message" class="%s" data-key="%s" data-nonce="%s"><p>%s</p></div>', esc_attr( $notice['class'] ), esc_attr( $key ), esc_attr( wp_create_nonce( 'notice_nonce_' . $key ) ), wp_kses_post( $notice['msg'] ) );
}
