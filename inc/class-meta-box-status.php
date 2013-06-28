<?php
//avoid direct calls to this file, because now WP core and framework has been used
if ( ! function_exists( 'add_filter' ) ) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
}

class WP_Maintenance_Mode_Status extends WPMaintenanceMode {
	
	protected static $classobj = NULL;
	
	static private   $option_string;
	
	public function __construct() {
		
		if ( ! is_admin() )
			return NULL;
		$this->status  = parent::get_msqld_option();
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
	public function get_metabox_status($data, $status) {
		?>
			STATUS
		<?php
	}
	
}
//$wp_maintenance_mode_status = WP_Maintenance_Mode_Status::get_object();
