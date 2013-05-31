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
				<p>
					<select name="wm_config-active" id="wm_config-active">
						<option value="0"<?php selected( 0, $status, TRUE ); ?>><?php _e( 'False', FB_WM_TEXTDOMAIN ); ?> </option>
						<option value="1"<?php selected( 1, $status, TRUE ); ?>><?php _e( 'True', FB_WM_TEXTDOMAIN ); ?> </option>
					</select> 
					<?php submit_button( esc_attr__( 'Save Changes' ), $type = 'primary large', $name = 'submit', $wrap = FALSE ); ?>
				</p>
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
				
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><label for="wm_config-radio"><?php esc_attr_e( 'Countdown', FB_WM_TEXTDOMAIN ); ?></label></th>
						<td>
							<select name="wm_config-radio" id="wm_config-radio">
								<option value="0" <?php selected( $data['radio'], 0 ); ?>><?php esc_attr_e( 'False', FB_WM_TEXTDOMAIN ); ?> </option>
								<option value="1" <?php selected( $data['radio'], 1 ); ?>><?php esc_attr_e( 'True', FB_WM_TEXTDOMAIN ); ?> </option>
							</select>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="wm_config-date"><?php esc_attr_e( 'Date', FB_WM_TEXTDOMAIN ); ?></label></th>
						<td>
							<input size="30" title="<?php 
								esc_attr_e( 'Click for datepicker', FB_WM_TEXTDOMAIN ); ?>" type="text" id="wm_config-date" name="wm_config-date" value="<?php 
								if ( isset($value['date']) ) echo $value['date']; ?>" /><br />
							<small><?php esc_attr_e( 'Activate countdown for using this. Use value and unit or use the countdown and set the date.', FB_WM_TEXTDOMAIN ); ?></small>
						</td>
					</tr>
				</table>
				
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
