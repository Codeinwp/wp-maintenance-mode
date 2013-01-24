<?php
/**
 * WordPress Login Form
 * 
 * @since   11/25/2012
 * @uses    [loginform redirect="http://my-redirect-url.com"]
 */

class WPMaintenanceMode_Login_Form extends WPMaintenanceMode {
	
	public function __construct() {
		
		add_shortcode( 'loginform', array( $this, 'login_form_shortcode' ) );
	}
	
	public static function get_options() {
		
		return parent::get_options();
	}
	
	/**
	 * Get a default login form
	 * 
	 * @param  $atts     Array
	 * @param  $content  String
	 */
	public function login_form_shortcode( $atts, $content = NULL ) {
		
		$value = $this->get_options();
		
		extract( shortcode_atts(
			array(
				'redirect' => ''
			),
			$atts
		) );
		
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
		
		return $form;
	}
	
} // end class
new WPMaintenanceMode_Login_Form();

