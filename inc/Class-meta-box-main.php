<?php
//avoid direct calls to this file, because now WP core and framework has been used
if ( ! function_exists( 'add_filter' ) ) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
}

class WPMaintenanceMode_Main extends WPMaintenanceMode {
	
	protected static $classobj = NULL;
	
	static private   $option_string;
	
	public function __construct() {
		
		if ( ! is_admin() )
			return NULL;
		
		self::$option_string = FB_WM_TEXTDOMAIN;
		
		add_action( self::$option_string . '_settings_page_sidebar', array( $this, 'get_status_settings' ), 10, 2 );
		add_action( self::$option_string . '_settings_page', array( $this, 'get_value_settings' ) );
		add_action( self::$option_string . '_settings_page', array( $this, 'get_content_settings' ) );
		add_action( self::$option_string . '_settings_page', array( $this, 'get_design_settings' ) );
	}
	
	public static function get_object() {
		
		if ( NULL === self :: $classobj )
			self :: $classobj = new self;
		
		return self :: $classobj;
	}
	
	public function get_status_settings( $data, $status ) {
		?>
		<div class="postbox">
			
			<h3><span><?php _e( 'Status', FB_WM_TEXTDOMAIN ); ?></span></h3>
			<div class="inside">
				<?php var_dump($status); ?>
			</div>
			
		</div>
		<?php
	}
	
	/*
	 * Set the settings for values
	 * 
	 * @return void
	 */
	public function get_value_settings( $data ) {
		?>
		<div class="postbox">
			
			<h3><span><?php _e( 'Values', FB_WM_TEXTDOMAIN ); ?></span></h3>
			<div class="inside">
				<?php var_dump($data); ?>
			</div>
			
		</div>
		<?php
	}
	
	/*
	 * Set the settings for content
	 * 
	 * @return void
	 */
	public function get_content_settings( $data ) {
		?>
		<div class="postbox">
			
			<h3><span><?php _e( 'Content', FB_WM_TEXTDOMAIN ); ?></span></h3>
			<div class="inside">
				<?php var_dump($data); ?>
			</div>
			
		</div>
		<?php
	}
	
	/*
	 * Set design settings
	 * 
	 * @return void
	 */
	public function get_design_settings( $data ) {
		?>
		<div class="postbox">
			
			<h3><span><?php _e( 'Design', FB_WM_TEXTDOMAIN ); ?></span></h3>
			<div class="inside">
				<?php var_dump($data); ?>
			</div>
			
		</div>
		<?php
	}
	
}
$wp_maintenance_mode_main_meta_box = WPMaintenanceMode_Main::get_object();
