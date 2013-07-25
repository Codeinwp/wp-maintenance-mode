<?php
/**
 * Settings markup
 * 
 * @since   09/20/2012
 */
class WPMaintenanceMode_Settings {
	
	protected static $classobj;
	
	public function __construct() {
		
		if ( ! is_admin() )
			return;
		
		if ( is_multisite() && is_plugin_active_for_network( plugin_basename( FB_WM_BASENAME ) ) ) {
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
		
		wp_enqueue_style( 'wp-maintenance-mode-options', plugin_dir_url( FB_WM_BASENAME ) . 'css/style.css' );
	}
	
	/**
	 * Handler for the action 'init'. Instantiates this class.
	 *
	 * @since   2.0.0
	 * @access  public
	 * @return  $classobj
	 */
	public static function get_object() {
		
		if ( NULL === self :: $classobj ) {
			self :: $classobj = new self;
		}
	
		return self :: $classobj;
	}
	
	function add_settings_link( $links, $file ) {
		
		if ( plugin_basename( FB_WM_BASENAME ) == $file  )
			array_unshift(
				$links,
				sprintf( '<a id="wm-pluginconflink" href="javascript:void(0)" title="Configure this plugin">%s</a>', __('Settings') )
			);
		
		return $links;
	}
	
	
	function network_admin_add_settings_link( $links, $file ) {
		
		if ( plugin_basename( FB_WM_BASENAME ) == $file )
			$links[] = '<a  id="wm-pluginconflink" href="javascript:void(0)" title="Configure this plugin">' . __('Settings') . '</a>';
		
		return $links;
	}
	
	/**
	 * Add settings markup
	 * 
	 * @param   $wm_pluginfile Object
	 * @param   $wm_plugindata Object (array)
	 * @param   $wm_context    Object (all, active, inactive)
	 * @return  void
	 */
	public static function add_config_form( $wm_pluginfile, $wm_plugindata, $wm_context ) {
		global $wp_roles;
		
		//if ( 0 < count($_POST['checked']) )
		//	return;
		
		if ( is_multisite() && is_plugin_active_for_network( FB_WM_BASENAME ) )
			$value = get_site_option( FB_WM_TEXTDOMAIN );
		else
			$value = get_option( FB_WM_TEXTDOMAIN );
		
		// check for non defaults
		if ( ! isset( $value['radio'] ) )
			$value['radio'] = 0;
		if ( ! isset( $value['unit'] ) )
			$value['unit'] = 1;
		if ( ! isset( $value['link'] ) )
			$value['link'] = 1;
		if ( ! isset( $value['admin_link'] ) )
			$value['admin_link'] = 1;
		if ( ! isset( $value['theme'] ) )
			$value['theme'] = 1;
		if ( ! isset( $value['index'] ) )
			$value['index'] = 0;
		// check the additional settings
		if ( ! isset( $value['notice'] ) )
			$value['notice'] = 1;
		if ( ! isset( $value['bypass'] ) )
			$value['bypass'] = 0;
		if ( ! isset( $value['support'] ) )
			$value['support'] = 0;
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
								<option value="0" <?php selected( $value['radio'], 0 ); ?>><?php _e('False', FB_WM_TEXTDOMAIN ); ?> </option>
								<option value="1" <?php selected( $value['radio'], 1 ); ?>><?php _e('True', FB_WM_TEXTDOMAIN ); ?> </option>
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
						<th scope="row">
							<label for="wm_config-time"><?php _e( 'Value:', FB_WM_TEXTDOMAIN ); ?></label>
						</th>
						<td class="alternate">
							<input size="5" type="text" id="wm_config-time" name="wm_config-time" value="<?php if( isset($value['time']) ) echo $value['time']; ?>" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="wm_config-unit"><?php _e( 'Unit:', FB_WM_TEXTDOMAIN ); ?></label>
						</th>
						<td class="alternate">
							<select name="wm_config-unit" id="wm_config-unit">
								<option value="0" <?php selected( $value['unit'], 0 ); ?>><?php _e('second', FB_WM_TEXTDOMAIN ); ?> </option>
								<option value="1" <?php selected( $value['unit'], 1 ); ?>><?php _e('minute', FB_WM_TEXTDOMAIN ); ?> </option>
								<option value="2" <?php selected( $value['unit'], 2 ); ?>><?php _e('hour', FB_WM_TEXTDOMAIN ); ?> </option>
								<option value="3" <?php selected( $value['unit'], 3 ); ?>><?php _e('day', FB_WM_TEXTDOMAIN ); ?> </option>
								<option value="4" <?php selected( $value['unit'], 4 ); ?>><?php _e('week', FB_WM_TEXTDOMAIN ); ?> </option>
								<option value="5" <?php selected( $value['unit'], 5 ); ?>><?php _e('month', FB_WM_TEXTDOMAIN ); ?> </option>
								<option value="6" <?php selected( $value['unit'], 6 ); ?>><?php _e('year', FB_WM_TEXTDOMAIN ); ?> </option>
							</select>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="wm_config-link"><?php _e( 'Link:', FB_WM_TEXTDOMAIN ); ?></label>
						</th>
						<td>
							<select name="wm_config-link" id="wm_config-link">
								<option value="0" <?php selected( $value['link'], 0 ); ?>><?php _e('False', FB_WM_TEXTDOMAIN ); ?> </option>
								<option value="1" <?php selected( $value['link'], 1 ); ?>><?php _e('True', FB_WM_TEXTDOMAIN ); ?> </option>
							</select>
							<br />
							<small><?php _e( 'Please leave a link to the plugin- and design-author on your maintenance mode site.', FB_WM_TEXTDOMAIN ); ?></small>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="wm_config-support"><?php _e( 'Support:', FB_WM_TEXTDOMAIN ); ?></label>
						</th>
						<td>
							<select name="wm_config-support" id="wm_config-support">
								<option value="0" <?php selected( $value['support'], 0 ); ?>><?php _e('False', FB_WM_TEXTDOMAIN ); ?> </option>
								<option value="1" <?php selected( $value['support'], 1 ); ?>><?php _e('True', FB_WM_TEXTDOMAIN ); ?> </option>
							</select>
							<br />
							<small><?php _e( 'By activating this option, you are agreeing to the fact that our code may show a random link to all search robots, this enables us to get a donation for develop on free plugins. Do not worry however, this code will not affect your site in anyway, and nothing displays to the visitors of your website nor will it slow your website down.', FB_WM_TEXTDOMAIN ); ?></small>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="wm_config-admin_link"><?php _e( 'Admin Link:', FB_WM_TEXTDOMAIN ); ?></label>
						</th>
						<td>
							<select name="wm_config-admin_link" id="wm_config-admin_link">
								<option value="0" <?php selected( $value['admin_link'], 0 ); ?>><?php _e('False', FB_WM_TEXTDOMAIN ); ?> </option>
								<option value="1" <?php selected( $value['admin_link'], 1 ); ?>><?php _e('True', FB_WM_TEXTDOMAIN ); ?> </option>
							</select>
							<br />
							<small><?php _e( 'Do you will a link to the admin area of your install?', FB_WM_TEXTDOMAIN ); ?></small>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="wm_config-theme"><?php _e( 'CSS Style:', FB_WM_TEXTDOMAIN ); ?></label>
						</th>
						<td class="alternate">
							<select name="wm_config-theme" id="wm_config-theme">
								<option value="0" <?php selected( $value['theme'], 0 ); ?>><?php _e('Own CSS Stylesheet', FB_WM_TEXTDOMAIN ); ?> </option>
								<option value="1" <?php selected( $value['theme'], 1 ); ?>><?php _e('Simple Text', FB_WM_TEXTDOMAIN ); ?> </option>
								<option value="2" <?php selected( $value['theme'], 2 ); ?>><?php _e('The Truck', FB_WM_TEXTDOMAIN ); ?> </option>
								<option value="3" <?php selected( $value['theme'], 3 ); ?>><?php _e('The Sun', FB_WM_TEXTDOMAIN ); ?> </option>
								<option value="4" <?php selected( $value['theme'], 4 ); ?>><?php _e('The FF Error', FB_WM_TEXTDOMAIN ); ?> </option>
								<option value="5" <?php selected( $value['theme'], 5 ); ?>><?php _e('Monster', FB_WM_TEXTDOMAIN ); ?> </option>
								<option value="6" <?php selected( $value['theme'], 6 ); ?>><?php _e('Chastely', FB_WM_TEXTDOMAIN ); ?> </option>
								<option value="7" <?php selected( $value['theme'], 7 ); ?>><?php _e('Only Typo', FB_WM_TEXTDOMAIN ); ?> </option>
								<option value="8" <?php selected( $value['theme'], 8 ); ?>><?php _e('Paint', FB_WM_TEXTDOMAIN ); ?> </option>
								<option value="9" <?php selected( $value['theme'], 9 ); ?>><?php _e('Animate (Flash)', FB_WM_TEXTDOMAIN ); ?> </option>
								<option value="10" <?php selected( $value['theme'], 10 ); ?>><?php _e('Damask', FB_WM_TEXTDOMAIN ); ?> </option>
								<option value="11" <?php selected( $value['theme'], 11 ); ?>><?php _e('Lego', FB_WM_TEXTDOMAIN ); ?> </option>
								<option value="12" <?php selected( $value['theme'], 12 ); ?>><?php _e('Chemistry', FB_WM_TEXTDOMAIN ); ?> </option>
							</select>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="wm_config-styleurl"><?php _e( 'Own CSS Style URL:', FB_WM_TEXTDOMAIN ); ?></label>
						</th>
						<td class="alternate">
							<input size="30" type="text" id="wm_config-styleurl" name="wm_config-styleurl" value="<?php if ( isset($value['styleurl']) ) echo $value['styleurl']; ?>" /> <small><?php _e( 'URL to the css-file (incl. http://)', FB_WM_TEXTDOMAIN ); ?></small>
							<br />
							<small><?php _e( '<strong>Caution:</strong> Please don&acute;t copy the stylesheet in your plugin folder, it will be deleted on the next automatical update of the plugin!', FB_WM_TEXTDOMAIN ); ?></small>
						</td>
					</tr>
					<?php /** comment, dont work ?>
					<tr valign="top">
						<th scope="row">
							<label for="wm_config-preview"><?php _e( 'Preview', FB_WM_TEXTDOMAIN ); ?></label>
						</th>
						<td class="alternate" style="padding:5px 0 0 0;">
						<script type="text/javascript">
						<!--
							var viewportwidth,
							    viewportheight;
							if (typeof window.innerWidth != 'undefined' ) {
								viewportwidth = window.innerWidth-80,
								viewportheight = window.innerHeight-100
							} else if (typeof document.documentElement != 'undefined'
								&& typeof document.documentElement.clientWidth !=
								'undefined' && document.documentElement.clientWidth != 0)
							{
								viewportwidth = document.documentElement.clientWidth,
								viewportheight = document.documentElement.clientHeight
							} else { // older versions of IE
								viewportwidth = document.getElementsByTagName('body' )[0].clientWidth,
								viewportheight = document.getElementsByTagName('body' )[0].clientHeight
							}
							document.write('<a onclick="return false;" href="<?php echo WP_PLUGIN_URL . '/' 
								. FB_WM_BASEDIR; ?>/index.php?KeepThis=true&amp;TB_iframe=true&amp;height=' 
								+ viewportheight + '&amp;width=' + viewportwidth 
								+ '&amp;modal=false" class="thickbox button"><?php _e( 'Preview', FB_WM_TEXTDOMAIN ); ?></a>' );
							//-->
						</script>
						</td>
					</tr>
					<?php */ ?>
					<tr valign="top">
						<th scope="row">
							<label for="wm_config-index"><?php _e( 'noindex, nofollow:', FB_WM_TEXTDOMAIN ); ?></label>
						</th>
						<td>
							<select name="wm_config-index" id="wm_config-index">
								<option value="0" <?php selected( $value['index'], 0 ); ?>><?php _e('False', FB_WM_TEXTDOMAIN ); ?> </option>
								<option value="1" <?php selected( $value['index'], 1 ); ?>><?php _e('True', FB_WM_TEXTDOMAIN ); ?> </option>
							</select>
							<br />
							<small><?php _e( 'The robots meta tag lets you utilize a granular, page-specific approach to controlling how an individual page should be indexed and served to users in search results. Set TRUE for noindex, nofollow; set FALSE for index, follow.', FB_WM_TEXTDOMAIN ); ?></small>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="wm_config-title"><?php _e( 'Title:', FB_WM_TEXTDOMAIN ); ?></label>
						</th>
						<td>
							<input size="30" type="text" id="wm_config-title" name="wm_config-title" value="<?php if ( isset($value['title']) ) echo $value['title']; ?>" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="wm_config-header"><?php _e( 'Header:', FB_WM_TEXTDOMAIN ); ?></label>
						</th>
						<td class="alternate">
							<input size="30" type="text" id="wm_config-header" name="wm_config-header" value="<?php if ( isset($value['header']) ) echo $value['header']; ?>" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="wm_config-heading"><?php _e( 'Heading:', FB_WM_TEXTDOMAIN ); ?></label>
						</th>
						<td>
							<input size="30" type="text" id="wm_config-heading" name="wm_config-heading" value="<?php if ( isset($value['heading']) ) echo $value['heading']; ?>" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="wm_config-text"><?php _e( 'Text:', FB_WM_TEXTDOMAIN ); ?></label>
						</th>
						<td class="alternate">
							<textarea class="code" style="width: 95%;" cols="40" rows="4" name="wm_config-text" id="wm_config-text"><?php if ( isset($value['text']) ) echo esc_attr($value['text']); ?></textarea>
							<br />
							<small>
								<?php _e( 'Use the first <em>%1$s</em> for the time value or countdown and second <em>%2$s</em> for the unit of the time or countdown-value; HTML and Shortcodes are possible.', FB_WM_TEXTDOMAIN ); ?>
								<?php _e( 'Use <code>[loginform]</code> for add the default login form in the maintenance page.', FB_WM_TEXTDOMAIN ); ?>
							</small>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="wm_config-exclude"><?php _e( 'Exclude:', FB_WM_TEXTDOMAIN ); ?></label>
						</th>
						<td>
							<?php
							if ( isset($value['exclude']) && '' !== $value['exclude'][0] ) {
								if ( is_array( $value['exclude'] ) && 1 <= count($value['exclude']) ) {
									$value_exclude = implode( ', ', $value['exclude'] );
								} else {
									$value_exclude = $value['exclude'];
								}
							} else {
								$value_exclude = NULL;
							}
							?>
							<input class="large-text" size="30" type="text" id="wm_config-exclude" name="wm_config-exclude" value="<?php echo $value_exclude; ?>" />
							<br />
							<small><?php _e( 'Exclude feed, pages, posts, archives or IPs from the maintenance mode. Add the Slug of page or post as a comma-separated list.<br />Example:', FB_WM_TEXTDOMAIN ); ?> <code>wp-cron, feed, wp-admin, ?page_id=12, about, category/test, 127.0.0.1</code></small>
						</td>
					</tr>
					
					<tr valign="top">
						<th scope="row">
							<label for="wm_config-bypass"><?php _e( 'Bypass for Search Bots:', FB_WM_TEXTDOMAIN ); ?></label>
						</th>
						<td>
							<select name="wm_config-bypass" id="wm_config-bypass">
								<option value="0" <?php selected( $value['bypass'], 0 ); ?>><?php _e('False', FB_WM_TEXTDOMAIN ); ?> </option>
								<option value="1" <?php selected( $value['bypass'], 1 ); ?>><?php _e('True', FB_WM_TEXTDOMAIN ); ?> </option>
							</select>
							<small><?php _e( 'Allow Search Bots to bypass maintenance mode?', FB_WM_TEXTDOMAIN ); ?></small>
						</td>
					</tr>
					
					<tr valign="top">
						<th scope="row">
							<label for="wm_config-role"><?php _e( 'Backend Role:', FB_WM_TEXTDOMAIN ); ?></label>
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
							<small><?php _e( 'Allowed userrole to access the backend of this blog.', FB_WM_TEXTDOMAIN ); ?>
							<?php if ( is_multisite() ) { _e( 'Super Admin has always access.', FB_WM_TEXTDOMAIN ); } ?></small>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="wm_config-role_frontend"><?php _e( 'Frontend Role:', FB_WM_TEXTDOMAIN ); ?></label>
						</th>
						<td class="alternate">
							<select name="wm_config-role_frontend" id="wm_config-role_frontend">
							<?php
							// fallback
							if ( ! isset($value['role_frontend'][0]) )
								$value['role_frontend'][0] = NULL;
							
							foreach ( $wp_roles->roles as $role_frontend => $name ) {
								if ( function_exists('translate_user_role') )
									$role_name = translate_user_role( $name['name'] );
								elseif ( function_exists('before_last_bar') )
									$role_name = before_last_bar( $name['name'], 'User role' );
								else
									$role_name = strrpos( $name['name'], '|' );
									
								if ($value['role_frontend'][0] !== $role_frontend)
									$selected = '';
								else
									$selected = ' selected="selected"';
								echo '<option value="' . $role_frontend . '"' . $selected . '>' . $role_name . ' (' . $role_frontend . ')' . ' </option>';
							}
							?>
							</select>
							<small><?php _e( 'Allowed userrole to see the frontend of this blog.', FB_WM_TEXTDOMAIN ); ?>
							<?php if ( is_multisite() ) { _e( 'Super Admin has always access.', FB_WM_TEXTDOMAIN ); } ?></small>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="wm_config-rewrite"><?php _e( 'Redirection:', FB_WM_TEXTDOMAIN ); ?></label>
						</th>
						<td class="alternate">
							<input class="large-text" size="30" type="text" id="wm_config-rewrite" name="wm_config-rewrite" value="<?php if ( isset($value['rewrite']) ) echo $value['rewrite']; ?>" />
							<br />
							<small><?php _e( 'If you want that after the login the destination address is not standard to the dashboard, then defining a URL. (incl. http://)', FB_WM_TEXTDOMAIN ); ?></small>
						</td>
					</tr>
					
					<tr valign="top">
						<th scope="row">
							<label for="wm_config-notice"><?php _e( 'Notice:', FB_WM_TEXTDOMAIN ); ?></label>
						</th>
						<td>
							<select name="wm_config-notice" id="wm_config-notice">
								<option value="0" <?php selected( $value['notice'], 0 ); ?>><?php _e('False', FB_WM_TEXTDOMAIN ); ?> </option>
								<option value="1" <?php selected( $value['notice'], 1 ); ?>><?php _e('True', FB_WM_TEXTDOMAIN ); ?> </option>
							</select>
							<small><?php _e( 'Do you will see all notices, inside backend, the Admin Bar and the login screen?', FB_WM_TEXTDOMAIN ); ?></small>
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
	
} // end class
