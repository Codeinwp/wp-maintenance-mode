<?php

defined( 'ABSPATH' ) || exit;

require_once WPMM_CLASSES_PATH . 'shortcodes/wp-maintenance-mode-shortcode-loginform.php';

if ( ! class_exists( 'WP_Maintenance_Mode_Shortcodes' ) ) {

	class WP_Maintenance_Mode_Shortcodes {

		/**
		 * Add shortcodes
		 *
		 * @since 2.0.3
		 */
		public static function init() {
			$shortcodes = array(
				'loginform' => __CLASS__ . '::loginform',
			);

			foreach ( $shortcodes as $shortcode => $method ) {
				add_shortcode( $shortcode, $method );
			}
		}

		/**
		 * Shortcode Wrapper
		 *
		 * @since 2.0.3
		 * @param string $function
		 * @param array  $atts
		 * @param array  $wrapper
		 * @return string
		 */
		public static function shortcode_wrapper( $function, $atts = array(), $wrapper = array(
			'before' => null,
			'after'  => null,
		) ) {
			ob_start();

                        // @codingStandardsIgnoreStart
			echo wp_kses_post( $wrapper['before'] );
			call_user_func( $function, $atts );
			echo wp_kses_post( $wrapper['after'] );
                        // @codingStandardsIgnoreEnd

			return ob_get_clean();
		}

		/**
		 * Login form shortcode.
		 *
		 * @since 2.0.3
		 * @param array $atts
		 * @return string
		 */
		public static function loginform( $atts ) {
			return self::shortcode_wrapper( array( 'WP_Maintenance_Mode_Shortcode_Loginform', 'output' ), $atts );
		}

	}

}
