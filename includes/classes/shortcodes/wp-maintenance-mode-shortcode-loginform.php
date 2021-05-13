<?php

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_Maintenance_Mode_Shortcode_Loginform' ) ) {

	class WP_Maintenance_Mode_Shortcode_Loginform {

		/**
		 * Output
		 *
		 * @since 2.0.3
		 * @param array $atts
		 */
		public static function output( $atts ) {
			$atts = shortcode_atts(
				array(
					'redirect' => '',
				),
				$atts
			);

			include_once wpmm_get_template_path( 'loginform.php' );
		}

	}

}
