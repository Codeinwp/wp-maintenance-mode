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
 * Version:     1.8.1
 * Last change: 09/28/2012
 * Licence:     GPLv3
 * 
 * 
 * License:
 * ==============================================================================
 * Copyright 2009-2012 Frank Bueltge  (email : frank@bueltge.de)
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
 * This plugin requires WordPress >= 2.6 and tested with PHP Interpreter >= 5.3
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
	
	class WPMaintenanceMode {
		
		function WPMaintenanceMode() {
			
			$this->load_classes();
			
			register_activation_hook( __FILE__, array( $this, 'add_config' ) );
			add_action( 'admin_print_scripts-plugins.php', array( $this, 'add_scripts' ) );
			//add_action( 'load-plugins.php', array(&$this, 'add_scripts') );
			add_action( 'init',       array( $this, 'on_init'), 1 );
			add_action( 'admin_init', array( $this, 'admin_init') );
			
			add_action( 'wp_ajax_wm_config-update', array( $this, 'save_config' ) );
			add_action( 'wp_ajax_wm_config-active', array( $this, 'save_active' ) );
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
			foreach( glob( dirname( __FILE__ ) . '/inc/*.php' ) as $class )
				require_once $class;
		}
		
		/**
		 * Function to escape strings
		 * Use WP default, if exists
		 * 
		 * @param  String
		 * @return String
		 */
		function esc_attr( $text ) {
			
			if ( function_exists('esc_attr') )
				$text = esc_attr($text);
			else
				$text = attribute_escape($text);
				
			return $text;
		}
		
		
		// function for WP < 2.8
		function get_plugins_url( $path = '', $plugin = '' ) {
			
			if ( function_exists('plugin_url') )
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
		
			if ( !empty($plugin) && is_string($plugin) ) {
				$folder = dirname(plugin_basename($plugin));
				if ('.' != $folder)
					$url .= '/' . ltrim($folder, '/');
			}
		
			if ( !empty($path) && is_string($path) && ( FALSE === strpos($path, '..') ) )
				$url .= '/' . ltrim($path, '/');
		
			return apply_filters('plugins_url', $url, $path, $plugin);
		}
		
		
		function on_init() {
			
			load_plugin_textdomain( FB_WM_TEXTDOMAIN, FALSE, FB_WM_BASEDIR . '/languages' );
			
			if ( is_multisite() && ! function_exists( 'is_plugin_active_for_network' ) )
				require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
			
			if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) )
				$valuemsqld = get_site_option( FB_WM_TEXTDOMAIN . '-msqld' );
			else
				$valuemsqld = (int) get_option( FB_WM_TEXTDOMAIN . '-msqld' );
			
			if ( 1 === $valuemsqld || '1' === $valuemsqld ) {
				$this->on_active();
				add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_alert' ), 9999 );
			}
		}
		
		
		function add_scripts() {
			
			$locale = get_locale();
			$i18n = substr($locale, 0, 2);
			
			wp_register_script( 'wp-maintenance-mode', $this->get_plugins_url( 'js/wp-maintenance-mode.js', __FILE__ ), array('jquery-ui-datepicker') , '', TRUE );
			wp_enqueue_script( 'wp-maintenance-mode' );
			
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
				add_action( 'after_plugin_row_' . FB_WM_BASENAME, array( 'WPMaintenanceMode_Settings', 'add_config_form'), 10, 3 );
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
		
		
		function add_settings_link( $links, $file ) {
			if ( plugin_basename( __FILE__ ) == $file  )
				array_unshift(
					$links,
					sprintf( '<a id="wm-pluginconflink" href="javascript:void(0)" title="Configure this plugin">%s</a>', __('Settings') )
				);
			
			return $links;
		}
		
		
		function network_admin_add_settings_link( $links, $file ) {
			
			if ( plugin_basename( __FILE__ ) == $file )
				$links[] = '<a  id="wm-pluginconflink" href="javascript:void(0)" title="Configure this plugin">' . __('Settings') . '</a>';
			
			return $links;
		}
		
		
		function add_config() {
			
			$this->data = array( 
				'active'     => 0, 
				'radio'      => 0, 
				'time'       => 60, 
				'link'       => 1, 
				'admin_link' => 1,
				'theme'      => 1, 
				'role'       => 'administrator', 
				'unit'       => 1, 
				'title'      => __( 'Maintenance mode', FB_WM_TEXTDOMAIN ), 
				'text'       => __( '<p>Sorry for the inconvenience.<br />Our website is currently undergoing scheduled maintenance.<br /><strong>Please try back in %1$s %2$s</strong><br />Thank you for your understanding.</p>', FB_WM_TEXTDOMAIN ), 
				'exclude'    => 'wp-cron, feed, wp-admin'
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
		}
		
		
		function save_active() {
			
			$this->data = array();
			if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
				$this->data = get_site_option( FB_WM_TEXTDOMAIN );
				$this->datamsqld = get_site_option( FB_WM_TEXTDOMAIN . '-msqld' );
			} else {
				$this->data = get_option( FB_WM_TEXTDOMAIN );
				$this->datamsqld = get_option( FB_WM_TEXTDOMAIN . '-msqld' );
			}

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
		}
		
		
		function save_config() {
			
			$this->data = array();
			if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) )
				$this->data = get_site_option( FB_WM_TEXTDOMAIN );
			else
				$this->data = get_option( FB_WM_TEXTDOMAIN );
			
			if ( isset($_POST['wm_config-time']) )
				$this->data['time'] = (int) $_POST['wm_config-time'];
			if ( isset($_POST['wm_config-unit']) )
				$this->data['unit'] = (int) $_POST['wm_config-unit'];
			if ( isset($_POST['wm_config-link']) )
				$this->data['link'] = (int) $_POST['wm_config-link'];
			if ( isset($_POST['wm_config-admin_link']) )
				$this->data['admin_link'] = (int) $_POST['wm_config-admin_link'];
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
			if ( isset($_POST['wm_config-role']) )
				$this->data['role'] = preg_split("/[\s,]+/", $this->esc_attr( $_POST['wm_config-role'] ) );
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
		
		
		function del_config() {
			
			if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
				delete_site_option( FB_WM_TEXTDOMAIN );
				delete_site_option( FB_WM_TEXTDOMAIN . '-msqld' );
			} else {
				delete_option( FB_WM_TEXTDOMAIN );
				delete_option( FB_WM_TEXTDOMAIN . '-msqld' );
			}
		}
		
		
		function check_version() {
			global $wp_version;
		
			if ( version_compare($wp_version, '2.1-dev', '<') ) {
				require (ABSPATH . WPINC . '/pluggable-functions.php'); // < WP 2.1
			} else {
				require (ABSPATH . WPINC . '/pluggable.php'); // >= WP 2.1	
			}
		}
		
		
		function check_exclude() {
			
			if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) )
				$value = get_site_option( FB_WM_TEXTDOMAIN );
			else
				$value = get_option( FB_WM_TEXTDOMAIN );
			
			if ( ! isset($value['exclude']) )
				return FALSE;
			
			foreach ( (array) $value['exclude'] as $exclude ) {
				// check for IP
				if ( strstr( $_SERVER['REMOTE_ADDR'], $exclude ) )
					return TRUE;
				
				if ( $exclude && strstr( $_SERVER['REQUEST_URI'], $exclude ) )
					return TRUE;
			}
			
			return FALSE;
		}
		
		
		function check_role() {
			if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) )
				$value = get_site_option( FB_WM_TEXTDOMAIN );
			else
				$value = get_option( FB_WM_TEXTDOMAIN );
			
			if ( is_super_admin() )
				return TRUE;
			
			if ( !isset( $value['role'][0] ) || ( '' != $value['role'][0] ) )
				$role = 'manage_options';
			
			$defaultroles = array('administrator', 'editor', 'author', 'contributor', 'subscriber');
			
			if ( isset($value['role'][0]) ) {
				if ( 'administrator' == $value['role'][0] )
					$role = 'manage_options';
					
				elseif ( 'editor' == $value['role'][0] )
					$role = 'manage_categories';
					
				elseif ( 'author' == $value['role'][0] )
					$role = 'publish_posts';
					
				elseif ( 'contributor' == $value['role'][0] )
					$role = 'edit_posts';
					
				elseif ( 'subscriber' == $value['role'][0] )
					$role = 'read';
					
				elseif ( !in_array( $value['role'][0], $defaultroles ) )
					$role = 'manage_options';
			} else {
				$role = 'manage_options';
			}
			
			if ( current_user_can( $role ) )
				return TRUE;
			
			return FALSE;
		}
		
		
		function case_unit($unitvalue) {
			
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
		
		
		function check_datetime() {
			
			$datetime = NULL;
			$time = NULL;
			$date = NULL;
			if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) )
				$value = get_site_option( FB_WM_TEXTDOMAIN );
			else
				$value = get_option( FB_WM_TEXTDOMAIN );
			
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
		
		
		function on_active() {
			global $current_user;
			
			if ( is_multisite() && ! function_exists( 'is_plugin_active_for_network' ) ) {
				require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
			}
			
			if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) )
				$value = get_site_option( FB_WM_TEXTDOMAIN );
			else
				$value = get_option( FB_WM_TEXTDOMAIN );
			
			if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) )
				$settings_link = network_admin_url() . 'plugins.php#wm-pluginconflink';
			else
				$settings_link = admin_url() . 'plugins.php#wm-pluginconflink';
			
			$scmsg = '';
			// Super Cache Plugin; clear cache on activation of maintance mode
			if ( function_exists( 'wp_cache_clear_cache' ) ) {
				wp_cache_clear_cache();
				$scmsg .= __( ' &amp; WP Super Cache flushed.', FB_WM_TEXTDOMAIN );
			}
			
			// W3 Total Cache Support
			if ( function_exists( 'w3tc_pgcache_flush' ) ) {
				w3tc_pgcache_flush();
				$scmsg .= __( ' &amp; W3 Total Cache for pages flushed.', FB_WM_TEXTDOMAIN );
			}
			
			$message = __( 'Caution: Maintenance mode is <strong>active</strong>!', FB_WM_TEXTDOMAIN );
			add_filter( 'login_message', create_function( '', "return '<div id=\"login_error\">$message</div>';" ) );
			$admin_notices = '<div id="message" class="error fade" style="background-color: #FFEBE8 !important;"><p>' . $message . $scmsg . ' <a href="plugins.php#wm-pluginconflink">' . __( 'Deactivate or change Settings', FB_WM_TEXTDOMAIN ) . '</a></p></div>';
			if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) )
				add_action( 'network_admin_notices', create_function( '', "echo '$admin_notices';" ) );
			add_action( 'admin_notices', create_function( '', "echo '$admin_notices';" ) );
			$in_admin_header = '<a id="mm_in_admin_header" href="' . $settings_link . '" title="' . __( 'Deactivate or change Settings', FB_WM_TEXTDOMAIN ) . '">' . $message . '</a>';
			//add_action( 'in_admin_header', create_function( '', "echo '$in_admin_header';" ) );
			/**
			// actual a ticket in trac #14126
			// @link http://core.trac.wordpress.org/ticket/14126
			$in_admin_header = '<a class="privacy-on-link" href="plugins.php#wm-pluginconflink" title="' . __( 'Deactivate or change Settings', FB_WM_TEXTDOMAIN ) . '">' . $message . '</a>';
			add_action( 'in_admin_site_heading', create_function( '', "echo '$in_admin_header';" ) );
			*/
			
			add_action( 'wm_head', array(&$this, 'add_theme') );
			add_action( 'wm_content', array(&$this, 'add_flash') );
			add_action( 'wm_content', array( &$this, 'add_content' ) );
			if ( isset($value['link']) && 1 === $value['link'] )
				add_action( 'wm_footer', array(&$this, 'add_link') );
			
			$locale = get_locale();
			
			if ( isset($value['unit']) )
				$unitvalues = $this->case_unit($value['unit']);
			
			// set backtime for header status
			if ( isset($value['time']) )
				$backtime = $value['time'] * $unitvalues['multiplier'];
			else
				$backtime = NULL;
			
			if ( ( ! $this->check_role() )
					&& ! strstr($_SERVER['PHP_SELF'], 'wp-login.php' )
					&& ! strstr($_SERVER['PHP_SELF'], 'async-upload.php')
					&& ! strstr($_SERVER['PHP_SELF'], '/plugins/')
					&& ! $this->check_exclude()
				 ) {
				$rolestatus = 'norights';
				nocache_headers();
				header("HTTP/1.0 503 Service Unavailable");
				header("Retry-After: $backtime");
				include('site.php');
				exit();
			}
			
			//$this->check_version();
			if ( ! strstr($_SERVER['PHP_SELF'], 'feed/')
				&& ! strstr($_SERVER['PHP_SELF'], 'wp-admin/')
				&& ! strstr($_SERVER['PHP_SELF'], 'wp-login.php')
				&& ! strstr($_SERVER['PHP_SELF'], 'async-upload.php')
				&& ! ( strstr($_SERVER['PHP_SELF'], 'upgrade.php') && $this->check_role() )
				&& ! strstr($_SERVER['PHP_SELF'], 'trackback/')
				&& ! strstr($_SERVER['PHP_SELF'], '/plugins/')
				&& ! $this->check_exclude()
				&& ! $this->check_role()
				) {
				include('site.php');
				exit();
			} else if ( strstr($_SERVER['PHP_SELF'], 'feed/') || strstr($_SERVER['PHP_SELF'], 'trackback/') ) {
				nocache_headers();
				header("HTTP/1.0 503 Service Unavailable");
				header("Retry-After: $backtime");
				exit();
			}
			
		}
		
		
		function add_link() {
			
			if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) )
				$value = get_site_option( FB_WM_TEXTDOMAIN );
			else
				$value = get_option( FB_WM_TEXTDOMAIN );
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
		
		
		function add_theme() {
			
			$locale = get_locale();
			if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) )
				$value = get_site_option( FB_WM_TEXTDOMAIN );
			else
				$value = get_option( FB_WM_TEXTDOMAIN );
			
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
		
		
		function add_flash() {
			
			$locale = get_locale();
			if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) )
				$value = get_site_option( FB_WM_TEXTDOMAIN );
			else
				$value = get_option( FB_WM_TEXTDOMAIN );
			
			$flash  = '';
			$object = '';
			// default theme
			if ( !isset($value['theme']) )
				$value['theme'] = 1;
			
			switch( $value['theme'] ) {
				case 9:
					$flash = FB_WM_BASE . '/styles/wartung-' . $locale . '.swf';
					if ( file_exists($flash) ) {
						$flash = $flash;
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
		
		
		function add_content() {
			
			$locale = get_locale();
			if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) )
				$value = get_site_option( FB_WM_TEXTDOMAIN );
			else
				$value = get_option( FB_WM_TEXTDOMAIN );
			$echo = NULL;
			// default for unit
			if ( !isset($value['unit']) )
				$value['unit'] = NULL;
				
			$unitvalues = $this->case_unit($value['unit']);
			$td = $this->check_datetime();
			
			if ( isset($value['radio']) && 1 === $value['radio'] && 0 !== $td[2] ) {
				$echodate = $td[0][0];
				if ('de_DE' == $locale)
					$echodate = str_replace('-', '.', $td[0][0]);
				if ( 0 !== $td[1] )
					$echodate .= ' ' . $td[0][1];
				$echo = wp_sprintf( stripslashes_deep( $value['text']), '<br /><span id="countdown"></span>', $echodate );
			} elseif ( isset($value['text']) ) {
				if (!isset($value['time']) || 0 == $value['time'] )
					$value['time'] = FALSE;
				if (!isset($unitvalues['unit']) )
					$unitvalues['unit'] = FALSE;
				$echo = wp_sprintf( stripslashes_deep( $value['text'] ), $value['time'], $unitvalues['unit'] );
			}
			
			echo do_shortcode($echo);
		}
		
		
		function check_file($url) {
			
			$url = parse_url($url);
			$fp  = fsockopen($url['host'], 80, $errno, $errstr, 30);
			
			if (!$fp) {
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
		
		function add_admin_bar_alert() {
			
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
		
		
		function url_exists($url) {
			if ( (strpos($url, "http")) === FALSE ) $url = "http://" . $url;
			if ( is_array(@get_headers($url)) )
				return TRUE;
			else
				return FALSE;
		}
		
	} // end class
	
	/**
	* Template tag to use in site-template
	*/
	function wm_head() {
		
		do_action('wm_head');
	}
	
	function wm_content() {
		
		do_action('wm_content');
	}
	
	function wm_footer() {
		
		do_action('wm_footer');
	}
	
	$GLOBALS['WPMaintenanceMode'] = new WPMaintenanceMode();
}

?>
