<?php
/**
 * Plugin Name: WP Maintenance Mode
 * Plugin URI:  http://wordpress.org/extend/plugins/wp-maintenance-mode/
 * Text Domain: wp-maintenance-mode
 * Domain Path: /languages
 * Description: The plugin adds a splash page to your blog that lets visitors know your blog is down for maintenance. Logged in users get full access to the blog including the front-end, depends of the settings.
 * Author:      Frank B&uuml;ltge
 * Author URI:  http://bueltge.de/
 * Donate URI:  http://bueltge.de/wunschliste/
 * Version:     1.8.11
 * Last change: 07/25/2013
 * License:     GPLv3
 * 
 * 
 * License:
 * ==============================================================================
 * Copyright 2009-2013 Frank Bueltge  (email : frank@bueltge.de)
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * 
 * Requirements:
 * ==============================================================================
 * This plugin requires WordPress >= 2.6 and tested with PHP >= 5.3, WP 3.5*
 */

//avoid direct calls to this file, because now WP core and framework has been used
if ( ! function_exists( 'add_filter' ) ) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
}

if ( ! class_exists('WPMaintenanceMode') ) {

	if ( ! defined('WP_CONTENT_URL') )
		define('WP_CONTENT_URL', site_url() . '/wp-content');
	if ( ! defined('WP_PLUGIN_URL') )
		define( 'WP_PLUGIN_URL', WP_CONTENT_URL . '/plugins' );
	
	define( 'FB_WM_BASENAME',   plugin_basename(__FILE__) );
	define( 'FB_WM_BASEDIR',    dirname( plugin_basename(__FILE__) ) );
	define( 'FB_WM_BASE',       rtrim(dirname (__FILE__), '/') );
	define( 'FB_WM_TEXTDOMAIN', 'wp-maintenance-mode' );
	
	add_action( 'plugins_loaded', array ( 'WPMaintenanceMode', 'get_instance' ) );
	
	class WPMaintenanceMode {
		
		/**
		 * Plugin instance.
		 *
		 * @see get_instance()
		 * @type object
		 */
		protected static $instance = NULL;
		
		/**
		 * Var for crawlers list
		 * 
		 * @type Array
		 */
		public $crawlers = array();
		
		function __construct() {
			
			$this->data      = array();
			$this->datamsqld = FALSE;
			
			/**
			 * Crawler List for bypass function
			 * 
			 * Description, Name => Spider, String for check
			 */
			$this->crawlers = array(
				'Abacho'          => 'AbachoBOT',
				'Accoona'         => 'Acoon',
				'AcoiRobot'       => 'AcoiRobot',
				'Adidxbot'        => 'adidxbot',
				'AltaVista robot' => 'Altavista',
				'Altavista robot' => 'Scooter',
				'ASPSeek'         => 'ASPSeek',
				'Atomz'           => 'Atomz',
				'Bing'            => 'bingbot',
				'BingPreview'     => 'BingPreview',
				'CrocCrawler'     => 'CrocCrawler',
				'Dumbot'          => 'Dumbot',
				'eStyle Bot'      => 'eStyle',
				'FAST-WebCrawler' => 'FAST-WebCrawler',
				'GeonaBot'        => 'GeonaBot',
				'Gigabot'         => 'Gigabot',
				'Google'          => 'Googlebot',
				'ID-Search Bot'   => 'IDBot',
				'Lycos spider'    => 'Lycos',
				'MSN'             => 'msnbot',
				'MSRBOT'          => 'MSRBOT',
				'Rambler'         => 'Rambler',
				'Scrubby robot'   => 'Scrubby',
				'Yahoo'           => 'Yahoo',
			);
			
			$this->load_classes();
			
			register_activation_hook( __FILE__, array( $this, 'add_config' ) );
			add_action( 'admin_print_scripts-plugins.php', array( $this, 'add_scripts' ) );
			//add_action( 'load-plugins.php', array(&$this, 'add_scripts') );
			add_action( 'init',       array( $this, 'on_init') );
			//add_action( 'admin_init', array( $this, 'admin_init') );
			add_action( 'admin_menu', array( $this, 'redirect' ) );
			
			add_action( 'admin_init', array( 'WPMaintenanceMode_Settings', 'get_object' ) );
			
			add_action( 'wp_ajax_wm_config-update', array( $this, 'save_config' ) );
			add_action( 'wp_ajax_wm_config-active', array( $this, 'save_active' ) );
		}
		
		
		/**
		 * Access this plugin’s working instance
		 *
		 * @wp-hook plugins_loaded
		 * @since   04/05/2013
		 * @return  object of this class
		 */
		public static function get_instance() {

			NULL === self::$instance and self::$instance = new self;

			return self::$instance;
		}
		
		
		/**
		 * Returns array of features, also
		 * Scans the plugins subfolder "/classes"
		 *
		 * @since   0.1
		 * @return  void
		 */
		protected function load_classes() {
			
			// load all files with the pattern *.php from the directory inc
			foreach( glob( dirname( __FILE__ ) . '/inc/*.php' ) as $class ) {
				require_once $class;
			}
		}
		
		/**
		 * Function to escape strings
		 * Use WP default, if exists
		 * 
		 * @param  String
		 * @return String
		 */
		public function esc_attr( $text ) {
			
			if ( function_exists('esc_attr') )
				$text = esc_attr($text);
			else
				$text = attribute_escape($text);
				
			return $text;
		}
		
		
		// function for WP < 2.8
		public function get_plugins_url( $path = '', $plugin = '' ) {
			
			if ( function_exists('plugins_url') )
				return plugins_url($path, $plugin);
			
			if ( function_exists('is_ssl') )
				$scheme = ( is_ssl() ? 'https' : 'http' );
			else
				$scheme = 'http';
			
			if ( function_exists('plugins_url') )
				$url = plugins_url();
			else 
				$url = WP_PLUGIN_URL;
			
			if ( 0 === strpos($url, 'http') ) {
				if ( function_exists('is_ssl') && is_ssl() )
					$url = str_replace( 'http://', "{$scheme}://", $url );
			}
		
			if ( ! empty($plugin) && is_string($plugin) ) {
				$folder = dirname(plugin_basename($plugin));
				if ('.' != $folder)
					$url .= '/' . ltrim($folder, '/');
			}
		
			if ( ! empty($path) && is_string($path) && ( FALSE === strpos($path, '..') ) )
				$url .= '/' . ltrim($path, '/');
			
			return apply_filters('plugins_url', $url, $path, $plugin);
		}
		
		
		public function on_init() {
			
			load_plugin_textdomain( FB_WM_TEXTDOMAIN, FALSE, FB_WM_BASEDIR . '/languages' );
			
			if ( is_multisite() && ! function_exists( 'is_plugin_active_for_network' ) )
				require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
			
			$value      = self::get_options();
			$valuemsqld = self::get_msqld_option();
			
			if ( $valuemsqld ) {
				$this->on_active();
				if ( ! isset( $value['notice'] ) || 0 !== $value['notice'] )
					add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_alert' ), 9999 );
			}
		}
		
		
		/**
		 * Return the options, check for install and active on WP multisite
		 * 
		 * @return  array $values
		 */
		public static function get_options() {
			
			if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
				$values = get_site_option( FB_WM_TEXTDOMAIN );
			} else {
				$values = get_option( FB_WM_TEXTDOMAIN );
			}
			
			return $values;
		}
		
		/**
		 * Return the msql-dumper-options, check for install and active on WP multisite
		 * 
		 * @return  Boolean $valuemsqld
		 */
		public static function get_msqld_option() {
			
			$msqld = FB_WM_TEXTDOMAIN . '-msqld';
			
			if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
				$valuemsqld = get_site_option( $msqld );
			} else {
				$valuemsqld = get_option( $msqld );
			}
			
			return (bool) $valuemsqld;
		}
		
		/**
		 * Register and enqueue scripts and styles
		 * 
		 * @return  void
		 */
		function add_scripts() {
			
			$locale = get_locale();
			$i18n = substr($locale, 0, 2);
			
			wp_register_script(
				'jquery-ui-timepicker-addon',
				$this->get_plugins_url( 'js/jquery-ui-timepicker-addon.js', __FILE__ ),
				array( 'jquery-ui-datepicker' ),
				'02-22-2013',
				TRUE
			);
			
			wp_register_script(
				'wp-maintenance-mode',
				$this->get_plugins_url( 'js/wp-maintenance-mode.js', __FILE__ ),
				array( 'jquery-ui-datepicker', 'jquery-ui-timepicker-addon' ),
				'',
				TRUE
			);
			wp_enqueue_script( 'jquery-ui-timepicker-addon' );
			wp_enqueue_script( 'wp-maintenance-mode' );
			// for nonce check on JS
			wp_localize_script(
				'wp-maintenance-mode',
				'wp_maintenance_mode_vars', 
				array(
					'ajaxurl'                   => admin_url( 'admin-ajax.php' ),
					'_nonce' => wp_create_nonce( 'wp-maintenance-mode-nonce' )
				)
			);
			
			// translations for datepicker
			if ( ! empty( $i18n ) && 
				 @file_exists( WP_PLUGIN_DIR . '/' . dirname( plugin_basename(__FILE__) ) . '/js/i18n/jquery.ui.datepicker-' . $i18n . '.js' )
				) {
				wp_register_script( 'jquery-ui-datepicker-' . $i18n, $this->get_plugins_url( 'js/i18n/jquery.ui.datepicker-' . $i18n . '.js', __FILE__ ), array('jquery-ui-datepicker') , '', TRUE );
				wp_enqueue_script( 'jquery-ui-datepicker-' . $i18n );
			}
			
			// include styles for datepicker
			wp_enqueue_style( 'jquery-ui-datepicker' );
			wp_enqueue_style( 'jquery-ui-datepicker-overcast', $this->get_plugins_url( 'css/overcast/jquery-ui-1.8.21.custom.css', __FILE__ ) );
			
			// for preview
			add_thickbox();
		}
		
		
		function admin_init() {
			
			if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
				// multisite install
				add_filter( 'network_admin_plugin_action_links', array( $this, 'add_settings_link' ), 10, 2 );
				//add_action( 'after_plugin_row_' . FB_WM_BASENAME, array( 'WPMaintenanceMode_Settings', 'add_config_form'), 10, 3 );
			} else {
				// Single mode install of WP
				if ( version_compare( $GLOBALS['wp_version'], '2.7alpha', '>' ) ) {
					add_action( 'after_plugin_row_' . FB_WM_BASENAME,    array( 'WPMaintenanceMode_Settings', 'add_config_form'), 10, 3 );
					add_filter( 'plugin_action_links_' . FB_WM_BASENAME, array( $this, 'add_settings_link' ), 10, 2 );
				} else {
					add_action( 'after_plugin_row',     array( 'WPMaintenanceMode_Settings', 'add_config_form'), 10, 3 );
					add_filter( 'plugin_action_links',  array( $this, 'add_settings_link' ), 10, 2 );
				}
			}
			
			wp_enqueue_style( 'wp-maintenance-mode-options', $this->get_plugins_url( 'css/style.css', __FILE__ ) );
		}
		
		
		public function add_config() {
			
			$this->data = array( 
				'active'     => 0, 
				'radio'      => 0, 
				'time'       => 60, 
				'link'       => 1, 
				'support'    => 0,
				'admin_link' => 1,
				'theme'      => 1, 
				'index'      => 0,
				'role'       => 'administrator', 
				'unit'       => 1, 
				'title'      => __( 'Maintenance mode', FB_WM_TEXTDOMAIN ), 
				'text'       => __( '<p>Sorry for the inconvenience.<br />Our website is currently undergoing scheduled maintenance.<br /><strong>Please try back in %1$s %2$s</strong><br />Thank you for your understanding.</p>', FB_WM_TEXTDOMAIN ), 
				'exclude'    => array( 
					0 => 'wp-cron',
					1 => 'feed',
					2 => 'wp-login',
					3 => 'login',
					4 => 'wp-admin',
					5 => 'wp-admin/admin-ajax.php'
				),
				'bypass'     => 0,
				'notice'     => 1,
			);
			// if is active in network of multisite
			if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
				add_site_option( FB_WM_TEXTDOMAIN, $this->data );
				add_site_option( FB_WM_TEXTDOMAIN . '-msqld', $this->data['active'] );
			} else {
				add_option( FB_WM_TEXTDOMAIN, $this->data );
				add_option( FB_WM_TEXTDOMAIN . '-msqld', $this->data['active'] );
			}
			
			$old_check = get_option( 'wartungsmodus' );
			if ($old_check)
				delete_option( 'wartungsmodus' );
			
			exit();
		}
		
		
		public function save_active() {
			
			//check_ajax_referer( 'wm_config-update', 'wp-maintenance-mode-nonce' );
			$nonce = $_POST['nonce'];
			if ( ! wp_verify_nonce( $nonce, 'wp-maintenance-mode-nonce' ) )
				wp_die( __( 'You are not authorised to perform this operation.' ) );
			
			if ( ! current_user_can( 'manage_options' ) )
				wp_die( __( 'You are not authorised to perform this operation.' ) );
			
			$this->data      = self::get_options();
			$this->datamsqld = self::get_msqld_option();
			
			if ( isset($_POST['wm_config-active']) )
				$this->data['active'] = (int) $_POST['wm_config-active'];
			
			if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
				update_site_option( FB_WM_TEXTDOMAIN, $this->data );
				update_site_option( FB_WM_TEXTDOMAIN . '-msqld', $this->data['active'] );
			} else {
				update_option( FB_WM_TEXTDOMAIN, $this->data );
				update_option( FB_WM_TEXTDOMAIN . '-msqld', $this->data['active'] );
			}
			
			die( __( 'Updated', FB_WM_TEXTDOMAIN ) );
			
			exit();
		}
		
		
		public function save_config() {
			
			//check_ajax_referer( 'wm_config-update', 'wp-maintenance-mode-nonce' );
			$nonce = $_POST['nonce'];
			if ( ! wp_verify_nonce( $nonce, 'wp-maintenance-mode-nonce' ) )
				wp_die( __( 'You are not authorised to perform this operation.' ) );
			
			if ( ! current_user_can( 'manage_options' ) )
				wp_die( __( 'You are not authorised to perform this operation.' ) );
			
			$this->data = self::get_options();
			
			if ( isset($_POST['wm_config-time']) )
				$this->data['time'] = (int) $_POST['wm_config-time'];
			
			if ( isset($_POST['wm_config-link']) )
				$this->data['link'] = (int) $_POST['wm_config-link'];
			
			if ( isset($_POST['wm_config-support']) )
				$this->data['support'] = (int) $_POST['wm_config-support'];
			
			if ( isset($_POST['wm_config-admin_link']) )
				$this->data['admin_link'] = (int) $_POST['wm_config-admin_link'];
			
			if ( isset($_POST['wm_config-rewrite']) ) {
				if ( function_exists('esc_url') ) {
					$this->data['rewrite'] = esc_url( $_POST['wm_config-rewrite'] );
				} else {
					$this->data['rewrite'] = clean_url( $_POST['wm_config-rewrite'] );
				}
			}
			
			if ( isset( $_POST['wm_config-notice'] ) )
				$this->data['notice'] = (int) $_POST['wm_config-notice'];
			
			if ( isset($_POST['wm_config-unit']) )
				$this->data['unit'] = (int) $_POST['wm_config-unit'];
			
			if ( isset($_POST['wm_config-theme']) )
				$this->data['theme'] = (int) $_POST['wm_config-theme'];
			
			if ( isset($_POST['wm_config-styleurl']) ) {
				if ( function_exists('esc_url') ) {
					$this->data['styleurl'] = esc_url( $_POST['wm_config-styleurl'] );
				} else {
					$this->data['styleurl'] = clean_url( $_POST['wm_config-styleurl'] );
				}
			}

			if ( isset($_POST['wm_config-index']) )
				$this->data['index'] = (int) $_POST['wm_config-index'];
			
			if ( isset($_POST['wm_config-title']) ) 
				$this->data['title'] =  stripslashes_deep( $_POST['wm_config-title'] );
			
			if ( isset($_POST['wm_config-header']) ) 
				$this->data['header'] =  stripslashes_deep( $_POST['wm_config-header'] );
				
			if ( isset($_POST['wm_config-heading']) ) 
				$this->data['heading'] =  stripslashes_deep( $_POST['wm_config-heading'] );
				
			if ( isset($_POST['wm_config-text']) ) 
				$this->data['text'] =  stripslashes_deep( $_POST['wm_config-text'] );
			
			if ( isset($_POST['wm_config-exclude']) )
				$this->data['exclude'] = preg_split("/[\s,]+/", $this->esc_attr( $_POST['wm_config-exclude'] ) );
			
			if ( isset( $_POST['wm_config-bypass'] ) )
				$this->data['bypass'] = (int) $_POST['wm_config-bypass'];
				
			if ( isset($_POST['wm_config-role']) )
				$this->data['role'] = preg_split("/[\s,]+/", $this->esc_attr( $_POST['wm_config-role'] ) );
			
			if ( isset($_POST['wm_config-role_frontend']) )
				$this->data['role_frontend'] = preg_split("/[\s,]+/", $this->esc_attr( $_POST['wm_config-role_frontend'] ) );
			
			if ( isset($_POST['wm_config-radio']) )
				$this->data['radio'] = (int) $_POST['wm_config-radio'];
			
			if ( isset($_POST['wm_config-date']) )
				$this->data['date'] = $this->esc_attr( $_POST['wm_config-date'] );
			
			if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) )
				update_site_option( FB_WM_TEXTDOMAIN, $this->data );
			else
				update_option( FB_WM_TEXTDOMAIN, $this->data );
			
			die( __( 'Updated', FB_WM_TEXTDOMAIN ) );
		}
		
		
		public function del_config() {
			
			if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
				delete_site_option( FB_WM_TEXTDOMAIN );
				delete_site_option( FB_WM_TEXTDOMAIN . '-msqld' );
			} else {
				delete_option( FB_WM_TEXTDOMAIN );
				delete_option( FB_WM_TEXTDOMAIN . '-msqld' );
			}
		}
		
		
		public function check_version() {
			global $wp_version;
		
			if ( version_compare( $wp_version, '2.1-dev', '<' ) ) {
				require (ABSPATH . WPINC . '/pluggable-functions.php'); // < WP 2.1
			} else {
				require (ABSPATH . WPINC . '/pluggable.php'); // >= WP 2.1	
			}
		}
		
		/**
		 * Rewrite for Frontend Login
		 * 
		 * @return  void
		 */
		public function redirect() {
			
			$value = self::get_options();
			
			// if the redirect active
			if ( ! isset($value['rewrite']) )
				return NULL;
			
			// check, is the maintenance mode active
			if ( 0 === $value['active'] )
				return NULL;
			
			// check, Access to backend
			if ( isset( $value['role'][0] ) && current_user_can( $value['role'][0] ) )
				return NULL;
			
			// redirect for wp-admin
			// only Dashboard: #wp-admin/?(index.php)?$#
			if ( preg_match( '#wp-admin/#', $_SERVER['REQUEST_URI'] ) )
				wp_redirect( $value['rewrite'] );
		}
		
		
		public function check_exclude() {
			
			$value = self::get_options();
			
			if ( ! isset($value['exclude']) || empty( $value['exclude'][0] ) )
				return FALSE;
			
			foreach ( (array) $value['exclude'] as $exclude ) {
				// check for IP
				if ( $exclude && ! empty( $_SERVER['REMOTE_ADDR'] ) && strstr( $_SERVER['REMOTE_ADDR'], $exclude ) )
					return TRUE;
				
				if ( $exclude && isset( $_SERVER['REQUEST_URI'] ) && strstr( $_SERVER['REQUEST_URI'], $exclude ) )
					return TRUE;
			}
			
			return FALSE;
		}
		
		
		/**
		 * Check exclude for search bots
		 * 
		 * @since  20/03/2013
		 * @return boolean
		 */
		public function check_bypass() {
			
			$value = self::get_options();
			
			if ( ! isset($value['bypass']) || ( 0 === $value['bypass'] ) )
				return FALSE;
			
			$crawler = $this->crawler_detect( $_SERVER['HTTP_USER_AGENT'] );
			if ( $crawler )
				return TRUE;
			
			return FALSE;
		}
		
		/**
		 * Check for str array value
		 * 
		 * @since   20/03/2013
		 * @see     http://stackoverflow.com/a/5927675/730125
		 * @return  boolean
		 */
		public function str_in_array( $str, $array ) {
			
			$regexp = '~(' . implode( '|', array_values( $array ) ) . ')~i';
			return (bool) preg_match( $regexp, $str );
		}
		
		/**
		 * Check for crawlers
		 * 
		 * @since  20/03/2013
		 * @return boolean TRUE, if is a crawler detect
		 */
		public function crawler_detect( $user_agent ) {
			
			if ( $this->str_in_array( $user_agent, $this->crawlers ) )
				return TRUE;
			
			return FALSE;
		}
		
		public function check_role() {
			
			$value = self::get_options();
			
			if ( is_super_admin() )
				return TRUE;
			
			if ( ! isset( $value['role'][0] ) || ( '' != $value['role'][0] ) )
				$role = 'manage_options';
			
			if ( ! isset( $value['role_frontend'][0] ) )
				$value['role_frontend'][0] = 'manage_options';
			
			if ( is_admin() )
				$current = $value['role'][0];
			else
				$current = $value['role_frontend'][0];
			
			$defaultroles = array('administrator', 'editor', 'author', 'contributor', 'subscriber');
			
			if ( isset( $current ) ) {
				if ( 'administrator' == $current )
					$role = 'manage_options';
					
				elseif ( 'editor' == $current )
					$role = 'manage_categories';
					
				elseif ( 'author' == $current )
					$role = 'publish_posts';
					
				elseif ( 'contributor' == $current )
					$role = 'edit_posts';
					
				elseif ( 'subscriber' == $current )
					$role = 'read';
					
				elseif ( ! in_array( $current, $defaultroles ) )
					$role = 'manage_options';
			} else {
				$role = 'manage_options';
			}
			
			if ( current_user_can( $role ) )
				return TRUE;
			else if ( current_user_can( $value['role_frontend'][0] ) )
				return TRUE;
			
			return FALSE;
		}
		
		
		public function case_unit( $unitvalue ) {
			
			$value['unit'] = $unitvalue;
			$unitvalues = array();
			
			switch( $value['unit'] ) {
				case 0:
					$unitvalues['unit'] = __( 'seconds', FB_WM_TEXTDOMAIN );
					$unitvalues['multiplier'] = 1;
					break;
				case 1:
					$unitvalues['unit'] = __( 'minutes', FB_WM_TEXTDOMAIN );
					$unitvalues['multiplier'] = 60;
					break;
				case 2:
					$unitvalues['unit'] = __( 'hours', FB_WM_TEXTDOMAIN );
					$unitvalues['multiplier'] = 3600;
					break;
				case 3:
					$unitvalues['unit'] = __( 'days', FB_WM_TEXTDOMAIN );
					$unitvalues['multiplier'] = 86400;
					break;
				case 4:
					$unitvalues['unit'] = __( 'weeks', FB_WM_TEXTDOMAIN );
					$unitvalues['multiplier'] = 604800;
					break;
				case 5:
					$unitvalues['unit'] = __( 'months', FB_WM_TEXTDOMAIN );
					$unitvalues['multiplier'] = 2592000; // 30 days
					break;
				case 6:
					$unitvalues['unit'] = __( 'years', FB_WM_TEXTDOMAIN );
					$unitvalues['multiplier'] = 31556952;
					break;
			}
			
			return $unitvalues;
		}
		
		
		public function check_datetime() {
			
			$datetime = NULL;
			$time     = NULL;
			$date     = NULL;
			$value    = self::get_options();
			
			if ( isset($value['radio']) && 1 === $value['radio'] ) {
				$datetime = explode( ' ', $value['date'] );
				$date = explode( '-', $datetime[0] );
				if ( isset($datetime[1]) )
					$time = explode( ':', $datetime[1] );
				else $time = 0;
				if (count($date) < 3) {
					$date = 0; //ausschalten wegen datum is nicht
				} else {
					$date[1] = $date[1] - 1;
					
					if (count($time) < 3)
						$time = 0;
					if ( isset($time) && 0 !== $time ) {
						// 'Years', 'Months', 'Weeks', 'Days', 'Hours', 'Minutes', 'Seconds'
						$date = $date[2].', '.$date[1].', '.$date[0].', '.$time[0].', '.$time[1].', '.$time[2];
					} else {
						$date = $date[2].', '.$date[1].', '.$date[0];
					}
				}
			}
			
			return array( $datetime, $time, $date );
		}
		
		
		public function on_active() {
			global $current_user;
			
			if ( is_multisite() && ! function_exists( 'is_plugin_active_for_network' ) ) {
				require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
			}
			ob_start();
			$value = self::get_options();
			
			if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
				$settings_link = network_admin_url() . 'plugins.php#wm-pluginconflink';
			} else {
				$settings_link = admin_url() . 'plugins.php#wm-pluginconflink';
			}
			
			$scmsg = '';
			// Super Cache Plugin; clear cache on activation of maintance mode
			if ( function_exists( 'wp_cache_clear_cache' ) ) {
				ob_end_clean();
				wp_cache_clear_cache();
				$scmsg .= __( ' &amp; WP Super Cache flushed.', FB_WM_TEXTDOMAIN );
			}
			
			// W3 Total Cache Support
			if ( function_exists( 'w3tc_pgcache_flush' ) ) {
				ob_end_clean();
				w3tc_pgcache_flush();
				$scmsg .= __( ' &amp; W3 Total Cache for pages flushed.', FB_WM_TEXTDOMAIN );
			}
			
			// check options, if the user will see the notices for active maintenance mode
			if ( ! isset($value['notice']) || 0 !== $value['notice'] ) {
				$message = __( 'Caution: Maintenance mode is <strong>active</strong>!', FB_WM_TEXTDOMAIN );
				add_filter( 'login_message', create_function( '', "return '<div id=\"login_error\">$message</div>';" ) );
				$admin_notices = '<div id="message" class="error fade" style="background-color: #FFEBE8 !important;"><p>' . $message . $scmsg . ' <a href="plugins.php#wm-pluginconflink">' . __( 'Deactivate or change Settings', FB_WM_TEXTDOMAIN ) . '</a></p></div>';
				
				if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) )
					add_action( 'network_admin_notices', create_function( '', "echo '$admin_notices';" ) );
				
				add_action( 'admin_notices', create_function( '', "echo '$admin_notices';" ) );
				
				//$in_admin_header = '<a id="mm_in_admin_header" href="' . $settings_link . '" title="' . __( 'Deactivate or change Settings', FB_WM_TEXTDOMAIN ) . '">' . $message . '</a>';
				//add_action( 'in_admin_header', create_function( '', "echo '$in_admin_header';" ) );
				/**
				// actual a ticket in trac #14126
				// @link http://core.trac.wordpress.org/ticket/14126
				$in_admin_header = '<a class="privacy-on-link" href="plugins.php#wm-pluginconflink" title="' . __( 'Deactivate or change Settings', FB_WM_TEXTDOMAIN ) . '">' . $message . '</a>';
				add_action( 'in_admin_site_heading', create_function( '', "echo '$in_admin_header';" ) );
				*/
			}
			
			add_action( 'wm_head', array( $this, 'add_theme' ) );
			add_action( 'wm_content', array( $this, 'add_flash' ) );
			add_action( 'wm_content', array( $this, 'add_content' ) );
			if ( isset($value['link']) && 1 === $value['link'] )
				add_action( 'wm_footer', array( $this, 'add_link' ) );
			
			$locale = get_locale();
			
			if ( isset($value['unit']) )
				$unitvalues = $this->case_unit($value['unit']);
			
			if ( get_bloginfo('charset') )
				$charset = get_bloginfo('charset');
			else
				$charset = 'UTF-8';
			
			// set backtime for header status
			if ( isset($value['time']) )
				$backtime = $value['time'] * $unitvalues['multiplier'];
			else
				$backtime = NULL;
			
			$protocol = $_SERVER["SERVER_PROTOCOL"];
			if ( 'HTTP/1.1' != $protocol && 'HTTP/1.0' != $protocol )
				$protocol = 'HTTP/1.0';
			// Allow to change status code via hook
			$status_code = (int) apply_filters( 'wp_maintenance_mode_status_code', '503' );
			
			if ( ( ! $this->check_role() )
					&& ! strstr($_SERVER['PHP_SELF'], 'wp-login.php' )
					&& ! strstr($_SERVER['PHP_SELF'], 'wp-admin/')
					&& ! strstr($_SERVER['PHP_SELF'], 'async-upload.php')
					&& ! ( strstr($_SERVER['PHP_SELF'], 'upgrade.php') && $this->check_role() )
					&& ! strstr($_SERVER['PHP_SELF'], '/plugins/')
					&& ! strstr($_SERVER['PHP_SELF'], '/xmlrpc.php')
					&& ! $this->check_exclude()
					&& ! $this->check_bypass()
				 ) {
				$rolestatus = 'norights';
				
				// helpful for header problems
				// @see: http://www.arclab.com/products/webformbuilder/php-warning-cannot-modify-header-information-headers-already-sent.html 
				
				nocache_headers();
				ob_start();
header( "Content-type: text/html; charset=$charset" );
header( "$protocol $status_code Service Unavailable", TRUE, $status_code );
header( "Retry-After: $backtime" );
				// Allow alternative splash page
				if ( file_exists( WP_CONTENT_DIR . '/wp-maintenance-mode.php' ) )
					include( WP_CONTENT_DIR . '/wp-maintenance-mode.php' );
				else
					include('site.php');
				ob_flush();
				exit();
			}
			
			/*
			 * @TODO: check this old source
			//$this->check_version();
			if ( ! strstr($_SERVER['PHP_SELF'], 'feed/')
				&& ! strstr($_SERVER['PHP_SELF'], 'wp-admin/')
				&& ! strstr($_SERVER['PHP_SELF'], 'wp-login.php')
				&& ! strstr($_SERVER['PHP_SELF'], 'async-upload.php')
				&& ! ( strstr($_SERVER['PHP_SELF'], 'upgrade.php') && $this->check_role() )
				&& ! strstr($_SERVER['PHP_SELF'], 'trackback/')
				&& ! strstr($_SERVER['PHP_SELF'], '/plugins/')
				&& ! strstr($_SERVER['PHP_SELF'], '/xmlrpc.php')
				&& ! $this->check_exclude()
				&& ! $this->check_bypass()
				&& ! $this->check_role()
				) {
				// Allow alternative splash page
				if ( file_exists( WP_CONTENT_DIR . '/wp-maintenance-mode.php' ) )
					include( WP_CONTENT_DIR . '/wp-maintenance-mode.php' );
				else
					include('site.php');
				exit();
			} else if ( strstr($_SERVER['PHP_SELF'], 'feed/') || strstr($_SERVER['PHP_SELF'], 'trackback/') ) {
				nocache_headers();
				header( "Content-type: text/html; charset=$charset" );
				header( "$protocol $status_code Service Unavailable", TRUE, $status_code );
				header( "Retry-After: $backtime" );
				exit();
			}
			*/
		}
		
		
		public function add_link() {
			
			$value = self::get_options();
			?>
			<div id="footer">
				<p><a href="http://bueltge.de/"><?php _e( 'Plugin by:', FB_WM_TEXTDOMAIN ); ?> <img src="http://bueltge.de/favicon.ico" alt="bueltge.de" width="16" height="16" /></a>
				<?php if ( 2 === $value['theme'] ) { ?>
					&nbsp;<a href="http://davidhellmann.com/"><?php _e( 'Design by:', FB_WM_TEXTDOMAIN ); ?> <img src="http://davidhellmann.com/favicon.ico" alt="davidhellmann.com" width="16" height="16" /></a>
				<?php } elseif ( 3 === $value['theme'] ) { ?>
					&nbsp;<a href="http://www.mynicki.net"><?php _e( 'Design by:', FB_WM_TEXTDOMAIN ); ?> <img src="http://www.mynicki.net/favicon.ico" alt="mynicki.net" width="16" height="16" /></a>
				<?php } elseif ( 4 === $value['theme'] ) { ?>
					&nbsp;<a href="http://www.lokalnetz.com"><?php _e( 'Design by:', FB_WM_TEXTDOMAIN ); ?> <img src="http://www.lokalnetz.com/images/favicon.ico" alt="lokalnetz.com" width="16" height="16" /></a>
				<?php } elseif ( 5 === $value['theme'] ) { ?>
					&nbsp;<a href="http://www.distractedbysquirrels.com"><?php _e( 'Design by:', FB_WM_TEXTDOMAIN ); ?> <img src="http://www.distractedbysquirrels.com/favicon.ico" alt="distractedbysquirrels.com" width="16" height="16" /></a>
				<?php } elseif ( 6 === $value['theme'] ) { ?>
					&nbsp;<a href="http://fv-web.de/"><?php _e( 'Design by:', FB_WM_TEXTDOMAIN ); ?> <img src="http://fv-web.de/favicon.ico" alt="fv-web.de" width="16" height="16" /></a>
				<?php } elseif ( 7 === $value['theme'] ) { ?>
					&nbsp;<a href="http://krautsuppe.de/"><?php _e( 'Design by:', FB_WM_TEXTDOMAIN ); ?> <img src="http://krautsuppe.de/favicon.ico" alt="krautsuppe.de" width="16" height="16" /></a>
				<?php } elseif ( 8 === $value['theme'] ) { ?>
					&nbsp;<a href="http://www.bugeyes.de/"><?php _e( 'Design by:', FB_WM_TEXTDOMAIN ); ?> <img src="http://www.bugeyes.de/favicon.ico" alt="www.bugeyes.de" width="16" height="16" /></a>
				<?php } elseif ( 9 === $value['theme'] ) { ?>
					&nbsp;<a href="http://www.cayou-media.de/"><?php _e( 'Design by:', FB_WM_TEXTDOMAIN ); ?> <img src="http://www.cayou-media.de/favicon.ico" alt="www.cayou-media.de" width="16" height="16" /></a>
				<?php } elseif ( 10 === $value['theme'] ) { ?>
					&nbsp;<a href="http://fabianletscher.de/"><?php _e( 'Design by:', FB_WM_TEXTDOMAIN ); ?> <img src="http://fabianletscher.de/favicon.ico" alt="fabianletscher.de" width="16" height="16" /></a>
				<?php } elseif ( 11 === $value['theme'] ) { ?>
					&nbsp;<a href="http://www.blogdrauf.de/"><?php _e( 'Design by:', FB_WM_TEXTDOMAIN ); ?> <img src="http://www.blogdrauf.de/favicon.ico" alt="www.blogdrauf.de" width="16" height="16" /></a>
				<?php } elseif ( 12 === $value['theme'] ) { ?>
					&nbsp;<a href="http://www.elmastudio.de/"><?php _e( 'Design by:', FB_WM_TEXTDOMAIN ); ?> <img src="http://www.elmastudio.de/favicon.ico" alt="www.elmastudio.de" width="16" height="16" /></a>
				<?php } ?>
				</p>
			</div>
			<?php
		}
		
		
		public function add_theme() {
			
			$locale = get_locale();
			$value  = self::get_options();
			
			$theme  = '';
			$link   = '';
			$style  = '';
			// default theme
			if ( !isset($value['theme']) )
				$value['theme'] = 1;
			
			switch( $value['theme'] ) {
				case 0:
					if ( $value['styleurl'] )
						$style = '<link rel="stylesheet" href="' . $value['styleurl'] . '" type="text/css" media="all" />' ."\n";
					break;
				case 1:
					$theme = 'txt.css';
					break;
				case 2:
					$theme = 'dh.css';
					$style .= '	<style type="text/css">' . "\n" . '<!--';
					$style .= '	#content h1 { text-indent: -99999px; background: url(\'' .  $this->get_plugins_url( '/styles/images/headline-' . $locale . '.jpg', __FILE__) . '\') no-repeat; }' . "\n";
					$style .= '	-->' . "\n";
					$style .= '	</style>';
					break;
				case 3:
					$theme = 'nicki.css';
					break;
				case 4:
					$theme = 'ln.css';
					break;
				case 5:
					$theme = 'monster.css';
					break;
				case 6:
					$theme = 'fv.css';
					break;
				case 7:
					$theme = 'ks.css';
					break;
				case 8:
					$theme = 'be.css';
					break;
				case 9:
					$theme = 'cm.css';
					break;
				case 10:
					$theme = 'fl.css';
					break;
				case 11:
					$theme = 'af.css';
					$style .= '	<style type="text/css">' . "\n" . '<!--';
					$style .= '	#content h1 { text-indent: -99999px; background: url(\'' . $this->get_plugins_url( 'styles/images/headline-af-' . $locale . '.jpg\') no-repeat; }', __FILE__ ) . "\n";
					$style .= '	-->' . "\n";
					$style .= '	</style>';
					break;
				case 12:
					$theme = 'es.css';
					break;
			}
			if ( ! empty($theme) )
				$link  = '<link rel="stylesheet" href="' . $this->get_plugins_url( 'styles/', __FILE__ ) . $theme . '" type="text/css" media="all" />' ."\n";
			echo $link . $style;
		}
		
		/**
		 * Add markup for flash theme
		 * 
		 * @return  String
		 */
		public function add_flash() {
			
			$locale = get_locale();
			$value  = self::get_options();
			
			$flash  = '';
			$object = '';
			// default theme
			if ( !isset($value['theme']) )
				$value['theme'] = 1;
			
			switch( $value['theme'] ) {
				case 9:
					$flash = FB_WM_BASE . '/styles/wartung-' . $locale . '.swf';
					if ( file_exists($flash) ) {
						$flash = $this->get_plugins_url( 'styles/', __FILE__ ) . 'wartung-' . $locale . '.swf';
					} else {
						$flash = $this->get_plugins_url( 'styles/', __FILE__ ) . 'wartung.swf';
					}
					
					$object = '
					<object type="application/x-shockwave-flash" data="' . $flash . '" width="800" height="600" id="galerie" style="outline:none;">
						<param name="wmode" value="transparent" />
						<param name="movie" value="' . $flash . '" />
					</object>';
					break;
			}
			
			echo $object;
		}
		
		
		/**
		 * Add content on splash page
		 * 
		 * @return  String
		 */
		public function add_content() {
			
			$locale = get_locale();
			$value  = self::get_options();
			
			$echo   = '';
			
			// default for unit
			if ( ! isset($value['unit']) )
				$value['unit'] = NULL;
				
			$unitvalues = $this->case_unit($value['unit']);
			$td = $this->check_datetime();
			
			if ( ! empty( $value['text'] ) )
				$value['text'] = wpautop( $value['text'] ); // apply_filters( 'the_content', $value['text'] );
			
			if ( isset($value['radio']) && 1 === $value['radio'] && 0 !== $td[2] ) {
				$echo = wp_sprintf( 
					stripslashes_deep( $value['text'] ),
					'<br /><span id="countdown"></span>',
					date_i18n( get_option('date_format'), strtotime( $td[0][0] ) )
				);
			} elseif ( isset($value['text']) ) {
				if ( ! isset($value['time']) || 0 == $value['time'] )
					$value['time'] = FALSE;
				if ( ! isset($unitvalues['unit']) )
					$unitvalues['unit'] = FALSE;
				$echo = wp_sprintf( stripslashes_deep( $value['text'] ), $value['time'], $unitvalues['unit'] );
			}
			
			echo do_shortcode( $echo );
		}
		
		
		public function check_file($url) {
			
			$url = parse_url($url);
			$fp  = fsockopen($url['host'], 80, $errno, $errstr, 30);
			
			if ( ! $fp) {
				echo $errstr . ' (' . $errno . ')<br />'. "\n";
			} else {
				$httpRequest = 'HEAD ' . $url['path'] . ' HTTP/1.1' . "\r\n"
								. 'Host: ' . $url['host'] ."\r\n"
								. 'Connection: close'. "\r\n\r\n";
				
				fputs($fp, $httpRequest);
				$zeileeins = fgets($fp, 1024);
				fclose($fp);
				
				if ( eregi('200 OK', $zeileeins) ) {
					return TRUE;
				} else {
					return FALSE;
				}
			}
		}
		
		public function add_admin_bar_alert() {
			
			if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) )
				$settings_link = network_admin_url() . 'plugins.php#wm-pluginconflink';
			else
				$settings_link = admin_url() . 'plugins.php#wm-pluginconflink';
			
			$GLOBALS['wp_admin_bar'] -> add_menu( 
				array( 
					'id'    => 'mm_alert', 
					'title' => __( 'Caution: Maintenance mode is <strong>active</strong>!', FB_WM_TEXTDOMAIN ), 
					'href'  => $settings_link
				)
			);
		}
		
		
		public function url_exists( $url ) {
			
			$scheme = ( is_ssl() ? 'https://' : 'http://' );
			
			if ( ( strpos( $url, $scheme ) ) === FALSE )
				$url = $scheme . $url;
			
			if ( is_array( @get_headers( $url ) ) )
				return TRUE;
			else
				return FALSE;
		}
		
	} // end class
	
	/**
	* Template tag to use in site-template
	*/
	function wm_head() {
		
		do_action( 'wm_head', '' );
	}
	
	function wm_content() {
		
		do_action( 'wm_content', '' );
	}
	
	function wm_footer() {
		
		do_action( 'wm_footer', '' );
	}
	
} // end if class exists
