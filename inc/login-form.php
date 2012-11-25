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
		
		extract( shortcode_atts(
			array(
				'redirect' => ''
			),
		$atts ) );
		
		if ( ! is_user_logged_in() ) {
			if ( $redirect )
				$redirect_url = $redirect;
			else
				$redirect_url = get_permalink();
			
			$form = wp_login_form( array(
				'echo' => FALSE,
				'redirect' => $redirect_url
			) );
		}
		
		return $form;
	}
	
} // end class
new WPMaintenanceMode_Login_Form();

