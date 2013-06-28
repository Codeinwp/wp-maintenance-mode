<?php
//avoid direct calls to this file
if ( ! function_exists( 'add_filter' ) ) {
        header('Status: 403 Forbidden');
        header('HTTP/1.1 403 Forbidden');
        exit();
}

class WP_Maintenance_Mode_Admin extends WPMaintenanceMode {
        
	protected static $classobj = NULL;
	
	// string for plugin file
	// static private   $plugin;
	
	static private   $option_string;

	public function __construct() {
		
		if ( ! is_admin() )
			return NULL;
		
		if ( ! function_exists( 'is_plugin_active_for_network' ) )
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		
		add_action( 'admin_init', array( $this, 'add_scripts' ) );
		
		if ( is_multisite() && is_plugin_active_for_network( FB_WM_BASENAME ) ) {
			add_action( 'network_admin_menu',    array( $this, 'add_settings_page' ) );
			// add settings link
//			add_filter( 'network_admin_plugin_action_links', array( $this, 'network_admin_plugin_action_links' ), 10, 2 );
			// save settings on network
//			add_action( 'network_admin_edit_' . self::$option_string, array( $this, 'save_network_settings_page' ) );
			// return message for update settings
//			add_action( 'network_admin_notices', array( $this, 'get_network_admin_notices' ) );
			// add script on settings page
		} else {
			add_action( 'admin_menu',            array( $this, 'add_settings_page' ) );
			// add settings link
//			add_filter( 'plugin_action_links',   array( $this, 'plugin_action_links' ), 10, 2 );
			// use settings API
//			add_action( 'admin_init',            array( $this, 'register_settings' ) );
		}
		
	}

	public static function get_object() {
		
		if ( NULL === self::$classobj )
			self::$classobj = new self;
		
		return self::$classobj;
	}

	// generate menu
	function add_settings_page() {
		
		if ( ! function_exists( 'is_plugin_active_for_network' ) )
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
			
		if ( is_multisite() && is_plugin_active_for_network( FB_WM_BASENAME ) ) {
			$this->settings_page = add_submenu_page(
				'settings.php',
				parent :: get_plugin_data( 'Name' ) . ' ' . __( 'Settings' ),
				parent :: get_plugin_data( 'Name' ),
				'manage_network',
				FB_WM_TEXTDOMAIN,
				array( $this, 'get_settings_page' )
			);
		} else {
			$this->settings_page = add_options_page(
				parent :: get_plugin_data( 'Name' ) . ' ' . __( 'Settings' ),
				parent :: get_plugin_data( 'Name' ),
				'manage_options',
				FB_WM_TEXTDOMAIN,
				array( $this, 'get_settings_page' )
			);
		// add_action( 'contextual_help', array( $this, 'contextual_help' ), 10, 3 ); --- deprecated 
		}
		
		// Create hooks for adding meta boxes
		add_action( 'load-'.$this->settings_page, array( &$this, 'register_metaboxes'));
		add_action( 'admin_footer-'.$this->settings_page, array( &$this, 'footer_script'));
	}
	
	function footer_script(){
		?>
			<script type="text/javascript">
				//<![CDATA[
					jQuery(document).ready( function($) {
						// close postboxes that should be closed
						$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
						// postboxes setup
						postboxes.add_postbox_toggles(pagenow);
					});
				//]]>
			</script>
		<?php
	}
	
	function register_metaboxes() {
		global 
			$wp_maintenance_mode_values,
			$wp_maintenance_mode_content,
			$wp_maintenance_mode_design,
			$wp_maintenance_mode_status,
			$wp_maintenance_mode_about;
				
		
		$wp_maintenance_mode_values = WP_Maintenance_Mode_Values::get_object();
		$wp_maintenance_mode_content = WP_Maintenance_Mode_Content::get_object();
		$wp_maintenance_mode_design = WP_Maintenance_Mode_Design::get_object();
		$wp_maintenance_mode_status = WP_Maintenance_Mode_Status::get_object();
		$wp_maintenance_mode_about = WP_Maintenance_Mode_About::get_object();
		
		// main > Values
		add_meta_box( 
			'maintenance-values', // $id
			__( 'Values', FB_WM_TEXTDOMAIN), // $title
			array( $wp_maintenance_mode_values, 'get_metabox_values'), // $callback
			$this->settings_page, // $post_type
			'normal', // $context
			'high' // $priority
			// $callback_args
		);
		
		// main > Content
		add_meta_box( 
			'maintenance-content', // $id
			__( 'Content', FB_WM_TEXTDOMAIN), // $title
			array( $wp_maintenance_mode_content, 'get_metabox_content'), // $callback
			$this->settings_page, // $post_type
			'normal', // $context
			'core' // $priority
			// $callback_args
		);
		
		// main > Design
		add_meta_box( 
			'maintenance-design', // $id
			__( 'Design', FB_WM_TEXTDOMAIN), // $title
			array( $wp_maintenance_mode_design, 'get_metabox_design'), // $callback
			$this->settings_page, // $post_type
			'normal', // $context
			'default' // $priority
			// $callback_args
		);

		// sidebar > Status
		add_meta_box( 
			'maintenance-status',
			__( 'Status', FB_WM_TEXTDOMAIN),
			array( $wp_maintenance_mode_status, 'get_metabox_status'),
			$this->settings_page,
			'side',
			'high'
		);
		
		// sidebar > About
		add_meta_box( 
			'maintenance-about',
			__( 'About this plugin', FB_WM_TEXTDOMAIN),
			array( $wp_maintenance_mode_about, 'get_metabox_about'),
			$this->settings_page,
			'side',
			'default'
		);
		
	}
	
	function clean() {
		?>
			<p>soemthing</p>
		<?php
	}
	
	/**
	 * Register and enqueue scripts and styles
	 * 
	 * @return  void
	 */
	function add_scripts( $screen ) {
		if ( 'settings_page_wp-maintenance-mode-network' == $screen->id || 'settings_page_wp-maintenance-mode' == $screen->id ) { // 
			$locale = get_locale();
			$i18n = substr($locale, 0, 2);

			wp_register_script(
				'jquery-ui-timepicker-addon',
				$this->get_plugins_url( 'js/jquery-ui-timepicker/jquery-ui-timepicker-addon.min.js', __FILE__ ),
				array( 'jquery-ui-datepicker' ),
				'1.3',
				TRUE
			);

			wp_register_script(
				'wp-maintenance-mode',
				$this->get_plugins_url( 'js/wp-maintenance-mode.js', __FILE__ ),
				array( 'jquery-ui-datepicker', 'jquery-ui-timepicker-addon' ),
				'1.8.8',
				TRUE
			);
			wp_enqueue_script( 'jquery-ui-timepicker-addon' );
			wp_enqueue_script( 'wp-maintenance-mode' );

			// translations for datepicker
			if ( ! empty( $i18n ) && 
				 @file_exists( WP_PLUGIN_DIR . '/' . dirname( plugin_basename(__FILE__) ) . '/js/i18n/jquery.ui.datepicker-' . $i18n . '.js' )
				) {
				wp_register_script( 'jquery-ui-datepicker-' . $i18n, $this->get_plugins_url( 'js/i18n/jquery.ui.datepicker-' . $i18n . '.js', __FILE__ ), array('jquery-ui-datepicker') , '', TRUE );
				wp_enqueue_script( 'jquery-ui-datepicker-' . $i18n );
			}

			// translations for timepicker
			if ( ! empty( $i18n ) && 
				 @file_exists( WP_PLUGIN_DIR . '/' . dirname( plugin_basename(__FILE__) ) . '/js/jquery-ui-timepicker/i18n/jquery-ui-timepicker-' . $i18n . '.js' )
				) {
				wp_register_script( 'jquery-ui-timepicker-addon-' . $i18n, $this->get_plugins_url( '/js/jquery-ui-timepicker/i18n/jquery-ui-timepicker-' . $i18n . '.js', __FILE__ ), array('jquery-ui-datepicker', 'jquery-ui-timepicker-addon') , '1.3', TRUE );
				wp_enqueue_script( 'jquery-ui-timepicker-addon-' . $i18n );
			}

			// include styles for datepicker
			wp_enqueue_style( 'jquery-ui-datepicker' );
			wp_enqueue_style( 'jquery-ui-datepicker-overcast', $this->get_plugins_url( 'css/overcast/jquery-ui-1.8.21.custom.css', __FILE__ ) );

			// for preview
			add_thickbox();
		}
	}
	
	public function get_settings_page() {
		wp_enqueue_script('common');
		wp_enqueue_script('wp-lists');
		wp_enqueue_script('postbox');

		require( dirname( __FILE__ ) . '/views/settings-page.php' ); // CHECK for better solution
		
	}
		
} // END WP_Maintenance_Mode_Admin

$wp_maintenance_mode_admin = WP_Maintenance_Mode_Admin::get_object();
