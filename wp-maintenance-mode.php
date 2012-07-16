<?php
/**
 * Plugin Name: WP Maintenance Mode
 * Plugin URI: http://bueltge.de/wp-wartungsmodus-plugin/101/
 * Text Domain: wp-maintenance-mode
 * Domain Path: /languages
 * Description: The plugin adds a splash page to your blog that lets visitors know your blog is down for maintenance. Logged in users get full access to the blog including the front-end, depends of the settings.
 * Author: Frank B&uuml;ltge
 * Author URI: http://bueltge.de/
 * Donate URI: http://bueltge.de/wunschliste/
 * Version: 1.7.1
 * Last change: 5.12.2011
 * Licence: GPLv3
 */

/**
License:
==============================================================================
Copyright 2009-2011 Frank Bueltge  (email : frank@bueltge.de)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

Requirements:
==============================================================================
This plugin requires WordPress >= 2.6 and tested with PHP Interpreter >= 5.3.1
*/

//avoid direct calls to this file, because now WP core and framework has been used
if ( ! function_exists('add_action') ) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
}

if ( ! class_exists('WPMaintenanceMode') ) {

	if ( ! defined('WP_CONTENT_URL') )
		define('WP_CONTENT_URL', site_url() . '/wp-content');
	if ( ! defined('WP_PLUGIN_URL') )
		define( 'WP_PLUGIN_URL', WP_CONTENT_URL . '/plugins' );
	
	define( 'FB_WM_BASENAME', plugin_basename(__FILE__) );
	define( 'FB_WM_BASEDIR', dirname( plugin_basename(__FILE__) ) );
	define( 'FB_WM_BASE', rtrim (dirname (__FILE__), '/') );
	define( 'FB_WM_TEXTDOMAIN', 'wp-maintenance-mode' );

	class WPMaintenanceMode {
		
		function WPMaintenanceMode() {
			
			register_activation_hook( __FILE__, array(&$this, 'add_config') );
			add_action( 'load-plugins.php', array(&$this, 'add_scripts') );
			add_action( 'init',             array(&$this, 'on_init'), 1 );
			add_action( 'admin_init',       array(&$this, 'admin_init') );
			
			add_action( 'wp_ajax_wm_config-update', array(&$this, 'save_config' ) );
			add_action( 'wp_ajax_wm_config-active', array(&$this, 'save_active' ) );
		}
		
		
		function esc_attr($text) {
			if ( function_exists('esc_attr') )
				$text = esc_attr($text);
			else
				$text = attribute_escape($text);
				
			return $text;
		}
		
		
		// function for WP < 2.8
		function get_plugins_url($path = '', $plugin = '') {
			
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
		
			if ( !empty($plugin) && is_string($plugin) )
			{
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
			
			if ( is_multisite() && ! function_exists( 'is_plugin_active_for_network' ) ) {
				require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
			}
			
			if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) )
				$valuemsqld = get_site_option( FB_WM_TEXTDOMAIN . '-msqld' );
			else
				$valuemsqld = (int) get_option( FB_WM_TEXTDOMAIN . '-msqld' );
			
			if ( 1 === $valuemsqld || '1' === $valuemsqld ) {
				$this -> on_active();
				add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_alert' ), 9999 );
			}
		}
		
		
		function add_scripts() {
			global $current_user;
			
			$locale = get_locale();
			
			wp_enqueue_script( 'jquery-ui-datetimepicker', $this->get_plugins_url( 'js/ui.datetimepicker.js', __FILE__ ), array('jquery-ui-core') , 0.1, TRUE );
			//wp_register_script( 'jquery-ui-datetimepicker-de', $this->get_plugins_url( 'js/de_DE.datetimepicker.js', __FILE__ ), array( 'jquery-ui-core', 'jquery-ui-datetimepicker' ) , 0.1, TRUE );
			//if ( 'de_DE' === $locale )
			//	wp_enqueue_script( 'jquery-ui-core', 'jquery-ui-datetimepicker', 'jquery-ui-datetimepicker-de' );
			add_action( 'admin_footer', array(&$this, 'add_script2admin_footer') );
			
			wp_enqueue_style( 'jquery-ui-datepicker', $this->get_plugins_url( 'css/overcast/jquery-ui-1.7.2.custom.css', __FILE__ ) );
			
			add_thickbox();
		}
		
		
		function admin_init() {
			
			if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
				// multisite install
				add_filter( 'network_admin_plugin_action_links', array( $this, 'add_settings_link' ), 10, 2 );
				add_action( 'after_plugin_row_' . FB_WM_BASENAME,    array(&$this, 'add_config_form'), 10, 3 );
			} else {
				// Single mode install of WP
				if ( version_compare( $GLOBALS['wp_version'], '2.7alpha', '>' ) ) {
					add_action( 'after_plugin_row_' . FB_WM_BASENAME,    array(&$this, 'add_config_form'), 10, 3 );
					add_filter( 'plugin_action_links_' . FB_WM_BASENAME, array(&$this, 'add_settings_link' ), 10, 2 );
				} else {
					add_action( 'after_plugin_row',     array(&$this, 'add_config_form'), 10, 3 );
					add_filter( 'plugin_action_links',  array(&$this, 'add_settings_link' ), 10, 2 );
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
			if ( plugin_basename( __FILE__ ) == $file  )
				$links[] = '<a  id="wm-pluginconflink" href="javascript:void(0)" title="Configure this plugin">' . __('Settings') . '</a>';
		
			return $links;
		}
		
		
		function add_script2admin_footer() {
			?>
			<script type="text/javascript">
				jQuery(document).ready( function($){
					
					$('#wm-pluginconflink').click(function(s){$('#wm_config_row').slideToggle('fast'); });
					$('#wm_config_active').click(function(){ wm_config_active(); });
					$('#wm_config_submit').click(function(){ wm_config_update(); });
					$("#wm_config-date").datetimepicker({ dateFormat: 'dd-mm-yy', timeFormat: ' hh:ii:ss' });
					
					function wm_config_active(){
						
						active_Val = $('#wm_config-active').val();
						url = '<?php echo get_bloginfo('wpurl'); ?>/wp-admin/admin-ajax.php';
						$.post( url , { 
								"action" : "wm_config-active", 
								"wm_config-active" : active_Val 
							}, 
							function(data) {
								$('#wm_message_active, #wm_message_active2').show('fast').animate({opacity: 1.0}, 3000).hide('slow');
							}
						);
					}
					
					function wm_config_update(){
						
						time_Val         = $('#wm_config-time').val();
						link_Val         = $('#wm_config-link').val();
						unit_Val         = $('#wm_config-unit').val();
						theme_Val        = $('#wm_config-theme').val();
						styleurl_Val     = $('#wm_config-styleurl').val();
						title_Val        = $('#wm_config-title').val();
						header_Val       = $('#wm_config-header').val();
						heading_Val      = $('#wm_config-heading').val();
						text_Val         = $('#wm_config-text').val();
						exclude_Val      = $('#wm_config-exclude').val();
						role_Val         = $('#wm_config-role').val();
						radio_Val        = $('#wm_config-radio').val();
						date_Val         = $('#wm_config-date').val();
						cd_day_Val       = $('#wm_config-cd-day').val();
						cd_month_Val     = $('#wm_config-cd-month').val();
						cd_year_Val      = $('#wm_config-cd-year').val();
						url = '<?php echo get_bloginfo('wpurl'); ?>/wp-admin/admin-ajax.php';
						$.post( url , {
								"action" : "wm_config-update", 
								"wm_config-time" : time_Val, 
								"wm_config-unit" : unit_Val, 
								"wm_config-link" : link_Val, 
								"wm_config-theme" : theme_Val, 
								"wm_config-styleurl" : styleurl_Val, 
								"wm_config-title" : title_Val, 
								"wm_config-header" : header_Val, 
								"wm_config-heading" : heading_Val, 
								"wm_config-text" : text_Val, 
								"wm_config-exclude" : exclude_Val, 
								"wm_config-role" : role_Val, 
								"wm_config-radio" : radio_Val, 
								"wm_config-date" : date_Val, 
								"wm_config-cd-day" : cd_day_Val, 
								"wm_config-cd-month" : cd_month_Val, 
								"wm_config-cd-year" : cd_year_Val
							}, 
							function(data) {
								$('#wm_message_update, #wm_message_update2').show('fast').animate({opacity: 1.0}, 3000).hide('slow');
							}
						);
						return false;
					}
				});
			</script>
		<?php
		}
		
		/**
		 * 
		 * @return 
		 * @param $wm_pluginfile Object
		 * @param $wm_plugindata Object (array)
		 * @param $wm_context    Object (all, active, inactive)
		 */
		function add_config_form($wm_pluginfile, $wm_plugindata, $wm_context) {
			global $wp_roles;
			
			//if ( 0 < count($_POST['checked']) )
			//	return;
			if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) )
				$value = get_site_option( FB_WM_TEXTDOMAIN );
			else
				$value = get_option( FB_WM_TEXTDOMAIN );
			?>
			<tr id="wm_config_tr" >
				<td colspan="3">
				
				<div id="wm_config_row" class="<?php echo ( isset($_GET['show']) && 'wmconfig' == $_GET['show'] ) ? '' : 'config_hidden' ;?>">
					<div class="updated fade" id="wm_message_update" style="background-color: #FFFBCC;">
						<p><?php echo sprintf( __( 'Plugin %s settings <strong>updated</strong>.', FB_WM_TEXTDOMAIN ), $wm_plugindata['Name'] ); ?></p>
					</div>
					<div class="error fade" id="wm_message_active" >
						<p><?php echo sprintf( __( 'Plugin %s active status <strong>updated</strong>.', FB_WM_TEXTDOMAIN ), $wm_plugindata['Name'] ); ?></p>
					</div>
					
					<h4><?php _e( 'Plugin Activate', FB_WM_TEXTDOMAIN ); ?></h4>
					<input type="hidden" name="wm_action" value="wm_config-active" />
					<p>
						<select name="wm_config-active" id="wm_config-active">
							<option value="0"<?php if ( isset($value['active']) && 0 === $value['active'] ) { echo ' selected="selected"'; } ?>><?php _e('False', FB_WM_TEXTDOMAIN ); ?> </option>
							<option value="1"<?php if ( isset($value['active']) && 1 === $value['active'] ) { echo ' selected="selected"'; } ?>><?php _e('True', FB_WM_TEXTDOMAIN ); ?> </option>
						</select> 
						<input id="wm_config_active" type="button" value="<?php _e( 'Update', FB_WM_TEXTDOMAIN ); ?>" class="button-primary" />
					</p>
					<div class="plugin-update-tr">
						<p id="wm_message_active2" class="update-message"><?php echo sprintf( __( 'Plugin %s active status <strong>updated</strong>.', FB_WM_TEXTDOMAIN ), $wm_plugindata['Name'] ); ?></p>
					</div>
					
					<h4><?php _e( 'Plugin Settings', FB_WM_TEXTDOMAIN ); ?></h4>
					<input type="hidden" name="wm_action" value="wm_config-update" />
					
					<table class="form-table">
						<tr valign="top">
							<th scope="row">
								<label for="wm_config-radio"><?php _e( 'Countdown:', FB_WM_TEXTDOMAIN ); ?></label>
							</th>
							<td>
								<select name="wm_config-radio" id="wm_config-radio">
									<option value="0"<?php if ( isset($value['radio']) && 0 === $value['radio'] ) { echo ' selected="selected"'; } ?>><?php _e('False', FB_WM_TEXTDOMAIN ); ?> </option>
									<option value="1"<?php if ( isset($value['radio']) && 1 === $value['radio'] ) { echo ' selected="selected"'; } ?>><?php _e('True', FB_WM_TEXTDOMAIN ); ?> </option>
								</select>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label for="wm_config-date" title="<?php _e( 'Click for datepicker', FB_WM_TEXTDOMAIN ); ?>"><?php _e( 'Date:', FB_WM_TEXTDOMAIN ); ?></label>
							</th>
							<td>
								<input size="30" title="<?php _e( 'Click for datepicker', FB_WM_TEXTDOMAIN ); ?>" type="text" id="wm_config-date" name="wm_config-date" value="<?php if ( isset($value['date']) ) echo $value['date']; ?>" /><br />
								<small><?php _e( 'Activate countdown for using this. Use value and unit or use the countdown and set the date.', FB_WM_TEXTDOMAIN ); ?></small>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row" class="alternate">
								<label for="wm_config-time"><?php _e( 'Value:', FB_WM_TEXTDOMAIN ); ?></label>
							</th>
							<td class="alternate">
								<input size="5" type="text" id="wm_config-time" name="wm_config-time" value="<?php if( isset($value['time']) ) echo $value['time']; ?>" />
							</td>
						</tr>
						<tr valign="top">
							<th scope="row" class="alternate">
								<label for="wm_config-unit"><?php _e( 'Unit:', FB_WM_TEXTDOMAIN ); ?></label>
							</th>
							<td class="alternate">
								<select name="wm_config-unit" id="wm_config-unit">
									<option value="0"<?php if ( isset($value['unit']) && 0 === $value['unit'] ) { echo ' selected="selected"'; } ?>><?php _e('second', FB_WM_TEXTDOMAIN ); ?> </option>
									<option value="1"<?php if ( isset($value['unit']) && 1 === $value['unit'] ) { echo ' selected="selected"'; } ?>><?php _e('minute', FB_WM_TEXTDOMAIN ); ?> </option>
									<option value="2"<?php if ( isset($value['unit']) && 2 === $value['unit'] ) { echo ' selected="selected"'; } ?>><?php _e('hour', FB_WM_TEXTDOMAIN ); ?> </option>
									<option value="3"<?php if ( isset($value['unit']) && 3 === $value['unit'] ) { echo ' selected="selected"'; } ?>><?php _e('day', FB_WM_TEXTDOMAIN ); ?> </option>
									<option value="4"<?php if ( isset($value['unit']) && 4 === $value['unit'] ) { echo ' selected="selected"'; } ?>><?php _e('week', FB_WM_TEXTDOMAIN ); ?> </option>
									<option value="5"<?php if ( isset($value['unit']) && 5 === $value['unit'] ) { echo ' selected="selected"'; } ?>><?php _e('month', FB_WM_TEXTDOMAIN ); ?> </option>
									<option value="6"<?php if ( isset($value['unit']) && 6 === $value['unit'] ) { echo ' selected="selected"'; } ?>><?php _e('year', FB_WM_TEXTDOMAIN ); ?> </option>
								</select>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label for="wm_config-link"><?php _e( 'Link:', FB_WM_TEXTDOMAIN ); ?></label>
							</th>
							<td>
								<select name="wm_config-link" id="wm_config-link">
									<option value="0"<?php if ( isset($value['link']) && 0 === $value['link'] ) { echo ' selected="selected"'; } ?>><?php _e('False', FB_WM_TEXTDOMAIN ); ?> </option>
									<option value="1"<?php if ( isset($value['link']) && 1 === $value['link'] ) { echo ' selected="selected"'; } ?>><?php _e('True', FB_WM_TEXTDOMAIN ); ?> </option>
								</select>
								<br />
								<small><?php _e( 'Please leave a link to the plugin- and design-author on your maintenance mode site.', FB_WM_TEXTDOMAIN ); ?></small>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row" class="alternate">
								<label for="wm_config-theme"><?php _e( 'Theme:', FB_WM_TEXTDOMAIN ); ?></label>
							</th>
							<td class="alternate">
								<select name="wm_config-theme" id="wm_config-theme">
									<option value="0"<?php if ( isset($value['theme']) && 0 === $value['theme'] ) { echo ' selected="selected"'; } ?>><?php _e('Own Style', FB_WM_TEXTDOMAIN ); ?> </option>
									<option value="1"<?php if ( isset($value['theme']) && 1 === $value['theme'] ) { echo ' selected="selected"'; } ?>><?php _e('Simple Text', FB_WM_TEXTDOMAIN ); ?> </option>
									<option value="2"<?php if ( isset($value['theme']) && 2 === $value['theme'] ) { echo ' selected="selected"'; } ?>><?php _e('The Truck', FB_WM_TEXTDOMAIN ); ?> </option>
									<option value="3"<?php if ( isset($value['theme']) && 3 === $value['theme'] ) { echo ' selected="selected"'; } ?>><?php _e('The Sun', FB_WM_TEXTDOMAIN ); ?> </option>
									<option value="4"<?php if ( isset($value['theme']) && 4 === $value['theme'] ) { echo ' selected="selected"'; } ?>><?php _e('The FF Error', FB_WM_TEXTDOMAIN ); ?> </option>
									<option value="5"<?php if ( isset($value['theme']) && 5 === $value['theme'] ) { echo ' selected="selected"'; } ?>><?php _e('Monster', FB_WM_TEXTDOMAIN ); ?> </option>
									<option value="6"<?php if ( isset($value['theme']) && 6 === $value['theme'] ) { echo ' selected="selected"'; } ?>><?php _e('Chastely', FB_WM_TEXTDOMAIN ); ?> </option>
									<option value="7"<?php if ( isset($value['theme']) && 7 === $value['theme'] ) { echo ' selected="selected"'; } ?>><?php _e('Only Typo', FB_WM_TEXTDOMAIN ); ?> </option>
									<option value="8"<?php if ( isset($value['theme']) && 8 === $value['theme'] ) { echo ' selected="selected"'; } ?>><?php _e('Paint', FB_WM_TEXTDOMAIN ); ?> </option>
									<option value="9"<?php if ( isset($value['theme']) && 9 === $value['theme'] ) { echo ' selected="selected"'; } ?>><?php _e('Animate (Flash)', FB_WM_TEXTDOMAIN ); ?> </option>
									<option value="10"<?php if ( isset($value['theme']) && 10 === $value['theme'] ) { echo ' selected="selected"'; } ?>><?php _e('Damask', FB_WM_TEXTDOMAIN ); ?> </option>
									<option value="11"<?php if ( isset($value['theme']) && 11 === $value['theme'] ) { echo ' selected="selected"'; } ?>><?php _e('Lego', FB_WM_TEXTDOMAIN ); ?> </option>
									<option value="12"<?php if ( isset($value['theme']) && 12 === $value['theme'] ) { echo ' selected="selected"'; } ?>><?php _e('Chemistry', FB_WM_TEXTDOMAIN ); ?> </option>
								</select>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row" class="alternate">
								<label for="wm_config-styleurl"><?php _e( 'Own Style URL (incl. http://):', FB_WM_TEXTDOMAIN ); ?></label>
							</th>
							<td class="alternate">
								<input size="30" type="text" id="wm_config-styleurl" name="wm_config-styleurl" value="<?php if ( isset($value['styleurl']) ) echo $value['styleurl']; ?>" /> <small><?php _e( 'URL to the css-file', FB_WM_TEXTDOMAIN ); ?></small>
								<br />
								<small><?php _e( '<strong>Coution:</strong> Please don&acute;t copy the stylesheet in your plugin folder, it will be deleted on the next automatical update of the plugin!', FB_WM_TEXTDOMAIN ); ?></small>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row" class="alternate">
								<label for="wm_config-preview"><?php _e( 'Preview', FB_WM_TEXTDOMAIN ); ?></label>
							</th>
							<td class="alternate" style="padding:5px 0 0 0;">
							<a onclick="return false;" href="<?php echo WP_PLUGIN_URL . '/' . FB_WM_BASEDIR; ?>/index.php?TB_iframe=true" class="thickbox button"><?php _e( 'Preview', FB_WM_TEXTDOMAIN ); ?></a>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label for="wm_config-title"><?php _e( 'Title:', FB_WM_TEXTDOMAIN ); ?></label>
							</th>
							<td>
								<input size="30" type="text" id="wm_config-title" name="wm_config-title" value="<?php if ( isset($value['title']) ) echo $value['title']; ?>" /> <small><?php _e( 'Leave empty for default.', FB_WM_TEXTDOMAIN ); ?></small>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row" class="alternate">
								<label for="wm_config-header"><?php _e( 'Header:', FB_WM_TEXTDOMAIN ); ?></label>
							</th>
							<td class="alternate">
								<input size="30" type="text" id="wm_config-header" name="wm_config-header" value="<?php if ( isset($value['header']) ) echo $value['header']; ?>" /> <small><?php _e( 'Leave empty for default.', FB_WM_TEXTDOMAIN ); ?></small>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label for="wm_config-heading"><?php _e( 'Heading:', FB_WM_TEXTDOMAIN ); ?></label>
							</th>
							<td>
								<input size="30" type="text" id="wm_config-heading" name="wm_config-heading" value="<?php if ( isset($value['heading']) ) echo $value['heading']; ?>" /> <small><?php _e( 'Leave empty for default.', FB_WM_TEXTDOMAIN ); ?></small>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row" class="alternate">
								<label for="wm_config-text"><?php _e( 'Text:', FB_WM_TEXTDOMAIN ); ?></label>
							</th>
							<td class="alternate">
								<textarea class="code" style="width: 95%;" cols="40" rows="4" name="wm_config-text" id="wm_config-text"><?php if ( isset($value['text']) ) echo esc_attr($value['text']); ?></textarea>
								<br />
								<small><?php _e( 'Use the first <em>%1$s</em> for the time value or countdown and second <em>%2$s</em> for the unit of the time or countdown-value; HTML and Shortcodes are possible', FB_WM_TEXTDOMAIN ); ?></small>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label for="wm_config-exclude"><?php _e( 'Excludes:', FB_WM_TEXTDOMAIN ); ?></label>
							</th>
							<td>
								<?php 
								if ( isset($value['exclude']) ) {
									if ( 1 < count($value['exclude']) ) {
										$value_exclude = join( ', ', $value['exclude'] );
									} else {
										$value_exclude = $value['exclude'];
									} 
								} else {
									$value_exclude = NULL;
								}
								?>
								<input size="30" type="text" id="wm_config-exclude" name="wm_config-exclude" value="<?php echo $value_exclude; ?>" />
								<br />
								<small><?php _e( 'Exclude feed, pages, posts or archives from the maintenance mode. Add the Slug of page or post as a comma-separated list.<br />Example:', FB_WM_TEXTDOMAIN ); ?> <code>wp-cron, feed, wp-admin, ?page_id=12, about, my-first-page, how-is-this-possible, category/test</code></small>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row" class="alternate">
								<label for="wm_config-role"><?php _e( 'Role:', FB_WM_TEXTDOMAIN ); ?></label>
							</th>
							<td class="alternate">
								<select name="wm_config-role" id="wm_config-role">
									<?php
									// fallback
									if ( ! isset($value['role'][0]) )
										$value['role'][0] = NULL;
									
									foreach ( $wp_roles->roles as $role => $name ) {
										if ( function_exists('translate_user_role') )
											$role_name = translate_user_role( $name['name'] );
										elseif ( function_exists('before_last_bar') )
											$role_name = before_last_bar( $name['name'], 'User role' );
										else
											$role_name = strrpos( $name['name'], '|' );
											
										if ($value['role'][0] !== $role)
											$selected = '';
										else
											$selected = ' selected="selected"';
										echo '<option value="' . $role . '"' . $selected . '>' . $role_name . ' (' . $role . ')' . ' </option>';
									}
									?>
								</select>
								<small><?php _e( 'Allowed userrole to see the frontend of this blog.', FB_WM_TEXTDOMAIN ); ?>
								<?php if ( is_multisite() ) { _e( 'Super Admin has always access.', FB_WM_TEXTDOMAIN ); } ?></small>
							</td>
						</tr>
						</table>
						<br />
						<div class="plugin-update-tr">
							<p id="wm_message_update2" class="update-message"><?php echo sprintf( __( 'Plugin %s settings <strong>updated</strong>.', FB_WM_TEXTDOMAIN ), $wm_plugindata['Name'] ); ?></p>
						</div>
						<p id="submitbutton">
							<input id="wm_config_submit" type="button" value="<?php _e( 'Save', FB_WM_TEXTDOMAIN ); ?>" class="button-secondary" />
						</p>
					</div>
					
				</td>
			</tr>
			<?php
		}
		
		
		function add_config() {
			
			$this->data = array( 
				'active' => 0, 
				'radio' => 0, 
				'time' => 60, 
				'link' => 1, 
				'theme' => 1, 
				'role' => 'administrator', 
				'unit' => 1, 
				'title' => 'Maintenance mode', 
				'text' => '<p>Sorry for the inconvenience.<br />Our website is currently undergoing scheduled maintenance.<br /><strong>Please try back in %1$s %2$s</strong><br />Thank you for your understanding.</p>', 
				'exclude' => 'wp-cron, feed, wp-admin'
			);
			// if is active in network of multisite
			if ( is_multisite() && isset($_GET['networkwide']) && 1 == $_GET['networkwide'] ) {
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
			if ( isset($_POST['wm_config-theme']) )
				$this->data['theme'] = (int) $_POST['wm_config-theme'];
			if ( isset($_POST['wm_config-styleurl']) ) {
				if ( function_exists('esc_url') ) {
					$this->data['styleurl'] = esc_url( $_POST['wm_config-styleurl'] );
				} else {
					$this->data['styleurl'] = clean_url( $_POST['wm_config-styleurl'] );
				}
			}
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
			
			if ( !isset($value['exclude']) )
				return FALSE;
			
			foreach ( (array) $value['exclude'] as $exclude ) {
				if ( $exclude && strstr($_SERVER['REQUEST_URI'], $exclude) )
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
			
			$message = __( 'Caution: Maintenance mode is <strong>active</strong>!', FB_WM_TEXTDOMAIN );
			add_filter( 'login_message', create_function( '', "return '<div id=\"login_error\">$message</div>';" ) );
			$admin_notices = '<div id="message" class="error fade" style="background-color: #FFEBE8 !important;"><p>' . $message . ' <a href="plugins.php#wm-pluginconflink">' . __( 'Deactivate or change Settings', FB_WM_TEXTDOMAIN ) . '</a></p></div>';
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
			
			if ( ( !$this->check_role() )
					&& !strstr($_SERVER['PHP_SELF'], 'wp-login.php' )
					&& !strstr($_SERVER['PHP_SELF'], 'async-upload.php')
					&& !strstr($_SERVER['PHP_SELF'], '/plugins/')
					&& !$this->check_exclude()
				 ) {
				$rolestatus = 'norights';
				nocache_headers();
				header("HTTP/1.0 503 Service Unavailable");
				header("Retry-After: $backtime");
				include('site.php');
				exit();
			}
			
			//$this->check_version();
			if ( !strstr($_SERVER['PHP_SELF'], 'feed/')
				&& !strstr($_SERVER['PHP_SELF'], 'wp-admin/')
				&& !strstr($_SERVER['PHP_SELF'], 'wp-login.php')
				&& !strstr($_SERVER['PHP_SELF'], 'async-upload.php')
				&& !( strstr($_SERVER['PHP_SELF'], 'upgrade.php') && $this->check_role() )
				&& !strstr($_SERVER['PHP_SELF'], 'trackback/')
				&& !strstr($_SERVER['PHP_SELF'], '/plugins/')
				&& !$this->check_exclude()
				&& !$this->check_role()
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
