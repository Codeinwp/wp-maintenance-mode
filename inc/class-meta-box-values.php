<?php
//avoid direct calls to this file, because now WP core and framework has been used
if ( ! function_exists( 'add_filter' ) ) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
}

class WP_Maintenance_Mode_Values extends WPMaintenanceMode {
	
	protected static $classobj = NULL;
	
	static private   $option_string;
	
	public function __construct() {
		
		if ( ! is_admin() )
			return NULL;
		
		// self::$option_string = FB_WM_TEXTDOMAIN;
		
		// add_action( self::$option_string . '_settings_page_sidebar', array( $this, 'get_about_plugin' ) );
	}
	
	public static function get_object() {
		
		if ( NULL === self :: $classobj )
			self :: $classobj = new self;
		
		return self :: $classobj;
	}
	
	/*
	 * Return informations about the plugin
	 * 
	 * @uses   _e,esc_attr_e
	 * @access public
	 * @return void
	 */
	public function get_metabox_values() {
//		global $wp_roles;
//        
//        if ( is_multisite() && is_plugin_active_for_network( FB_WM_BASENAME ) )
//			$value = get_site_option( FB_WM_TEXTDOMAIN );
//		else
//			$value = get_option( FB_WM_TEXTDOMAIN );
		?>			
		VALUES
		<?php
	}
	
}
//$wp_maintenance_mode_values = WP_Maintenance_Mode_Values::get_object();
