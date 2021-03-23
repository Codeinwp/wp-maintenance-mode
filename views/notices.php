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

	printf( '<div id="message" class="%s" data-key="%s"><p>%s</p></div>', esc_attr( $notice['class'] ), esc_attr( $key ), wp_kses_post( $notice['msg'] ) );
}
