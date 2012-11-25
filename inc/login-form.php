<?php
/**
 * WordPress Login Form
 * 
 * @since   11/25/2012
 * @uses    [loginform redirect="http://my-redirect-url.com"]
 */

class WPMaintenanceMode_Login_Form {
	
	public function __construct() {
		
		add_shortcode( 'loginform', array( $this, 'login_form_shortcode' ) );
	}
	
	/**
	 * Get a default login form
	 * 
	 * @param  $atts     Array
	 * @param  $content  String
	 */
	public function login_form_shortcode( $atts, $content = NULL ) {
		
		if ( is_multisite() && is_plugin_active_for_network( FB_WM_BASENAME ) )
			$value = get_site_option( FB_WM_TEXTDOMAIN );
		else
			$value = get_option( FB_WM_TEXTDOMAIN );
		
		extract( shortcode_atts(
			array(
				'redirect' => ''
			),
			$atts
		) );
		
		if ( ! is_user_logged_in() ) {
			
			// set default link
			if ( '' == get_permalink() )
				$redirect_default_url = home_url( '/' );
			
			if ( ! isset( $value['rewrite'] ) || empty( $value['rewrite'] ) )
				$redirect_url = $redirect_default_url;
			else
				$redirect_url = $value['rewrite'];
			
			$form = wp_login_form( array(
				'echo' => FALSE,
				'redirect' => $redirect_url
			) );
		}
		
		return $form;
	}
	
} // end class
new WPMaintenanceMode_Login_Form();

