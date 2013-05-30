<?php
//avoid direct calls to this file, because now WP core and framework has been used
if ( ! function_exists( 'add_filter' ) ) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
}

class WPMaintenanceMode_About extends WPMaintenanceMode {
	
	protected static $classobj = NULL;
	
	static private   $option_string;
	
	public function __construct() {
		
		if ( ! is_admin() )
			return NULL;
		
		self::$option_string = FB_WM_TEXTDOMAIN;
		
		add_action( self::$option_string . '_settings_page_sidebar', array( $this, 'get_about_plugin' ) );
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
	public function get_about_plugin() {
		?>
		<div class="postbox">
			
			<h3><span><?php _e( 'About this plugin', FB_WM_TEXTDOMAIN ); ?></span></h3>
			<div class="inside">
				<p>
					<strong><?php _e( 'Version:', FB_WM_TEXTDOMAIN ); ?></strong>
					<?php echo parent :: get_plugin_data( 'Version' ); ?>
				</p>
				<p>
					<strong><?php _e( 'Description:', FB_WM_TEXTDOMAIN ); ?></strong>
					<?php echo parent :: get_plugin_data( 'Description' ); ?>
				</p>
			</div>
			
		</div>
		<?php
	}
	
}
$wp_maintenance_mode_about_meta_box = WPMaintenanceMode_About::get_object();
