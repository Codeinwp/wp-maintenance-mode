<?php

// STATUS
public function get_metabox_status($data, $status) {
		?>
			<p>
				<select name="wm_config-active" id="wm_config-active">
					<option value="0"<?php selected( 0, $status, TRUE ); ?>><?php _e( 'False', FB_WM_TEXTDOMAIN ); ?> </option>
					<option value="1"<?php selected( 1, $status, TRUE ); ?>><?php _e( 'True', FB_WM_TEXTDOMAIN ); ?> </option>
				</select> 
				<?php submit_button( esc_attr__( 'Save Changes' ), $type = 'primary large', $name = 'submit', $wrap = FALSE ); ?>
			</p>
		<?php
	}
	
// ABOUT
public function get_metabox_about() {
		?>
			<p>
				<strong><?php _e( 'Version:', FB_WM_TEXTDOMAIN ); ?></strong>
				<?php echo parent :: get_plugin_data( 'Version' ); ?>
			</p>
			<p>
				<strong><?php _e( 'Description:', FB_WM_TEXTDOMAIN ); ?></strong>
				<?php echo parent :: get_plugin_data( 'Description' ); ?>
			</p>
		<?php
	}
	
// CONTENT
public function get_metabox_content($data) {
		?>
		<table class="form-table">
			<tr valign="top">
				<th scope="row">
					<label for="wm_config-title"><?php _e( 'Title:', FB_WM_TEXTDOMAIN ); ?></label>
				</th>
				<td>
					<input size="30" type="text" id="wm_config-title" name="wm_config-title" value="<?php if ( isset($value['title']) ) echo $value['title']; ?>" /> <small><?php _e( 'Leave empty for default.', FB_WM_TEXTDOMAIN ); ?></small>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
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
		</table>
		<?php
	}
	
// DESIGN
public function get_metabox_design($data) {
		if ( is_multisite() && is_plugin_active_for_network( FB_WM_BASENAME ) )
			$value = get_site_option( FB_WM_TEXTDOMAIN );
		else
			$value = get_option( FB_WM_TEXTDOMAIN );
		?>               
		<table class="form-table">
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
			<tr valign="top">
				<th scope="row">
					<label for="wm_config-preview"><?php _e( 'Preview', FB_WM_TEXTDOMAIN ); ?></label>
				</th>
				<td style="padding:5px 0 0 0;">
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
		</table>
		<?php
	}
	
// VALUES
public function get_metabox_values($data) {
		global $wp_roles;
        
        if ( is_multisite() && is_plugin_active_for_network( FB_WM_BASENAME ) )
			$value = get_site_option( FB_WM_TEXTDOMAIN );
		else
			$value = get_option( FB_WM_TEXTDOMAIN );
		?>			
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="wm_config-radio"><?php esc_attr_e( 'Countdown', FB_WM_TEXTDOMAIN); ?></label></th>
				<td>
					<select name="wm_config-radio" id="wm_config-radio">
						<option value="0" <?php selected( $data['radio'], 0 ); ?>><?php esc_attr_e( 'False', FB_WM_TEXTDOMAIN); ?> </option>
						<option value="1" <?php selected( $data['radio'], 1 ); ?>><?php esc_attr_e( 'True', FB_WM_TEXTDOMAIN); ?> </option>
					</select>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="wm_config-date"><?php esc_attr_e( 'Date', FB_WM_TEXTDOMAIN); ?></label></th>
				<td>
					<input size="30" title="<?php 
						esc_attr_e( 'Click for datepicker', FB_WM_TEXTDOMAIN); ?>" type="text" id="wm_config-date" name="wm_config-date" value="<?php 
						if ( isset($value['date']) ) echo $value['date']; ?>" /><br />
					<small><?php esc_attr_e( 'Activate countdown for using this. Use value and unit or use the countdown and set the date.', FB_WM_TEXTDOMAIN); ?></small>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label for="wm_config-time"><?php _e( 'Value:', FB_WM_TEXTDOMAIN); ?></label>
				</th>
				<td class="alternate">
					<input size="5" type="text" id="wm_config-time" name="wm_config-time" value="<?php if( isset($value['time']) ) echo $value['time']; ?>" />
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label for="wm_config-unit"><?php _e( 'Unit:', FB_WM_TEXTDOMAIN); ?></label>
				</th>
				<td class="alternate">
					<select name="wm_config-unit" id="wm_config-unit">
						<option value="0" <?php selected( $value['unit'], 0 ); ?>><?php _e('second', FB_WM_TEXTDOMAIN); ?></option>
						<option value="1" <?php selected( $value['unit'], 1 ); ?>><?php _e('minute', FB_WM_TEXTDOMAIN); ?></option>
						<option value="2" <?php selected( $value['unit'], 2 ); ?>><?php _e('hour', FB_WM_TEXTDOMAIN); ?></option>
						<option value="3" <?php selected( $value['unit'], 3 ); ?>><?php _e('day', FB_WM_TEXTDOMAIN); ?></option>
						<option value="4" <?php selected( $value['unit'], 4 ); ?>><?php _e('week', FB_WM_TEXTDOMAIN); ?></option>
						<option value="5" <?php selected( $value['unit'], 5 ); ?>><?php _e('month', FB_WM_TEXTDOMAIN); ?></option>
						<option value="6" <?php selected( $value['unit'], 6 ); ?>><?php _e('year', FB_WM_TEXTDOMAIN); ?></option>
					</select>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label for="wm_config-index"><?php _e( 'noindex, nofollow:', FB_WM_TEXTDOMAIN); ?></label>
				</th>
				<td>
					<select name="wm_config-index" id="wm_config-index">
						<option value="0" <?php selected( $value['index'], 0 ); ?>><?php _e('False', FB_WM_TEXTDOMAIN); ?> </option>
						<option value="1" <?php selected( $value['index'], 1 ); ?>><?php _e('True', FB_WM_TEXTDOMAIN); ?> </option>
					</select>
					<br />
					<small><?php _e( 'The robots meta tag lets you utilize a granular, page-specific approach to controlling how an individual page should be indexed and served to users in search results. Set TRUE for noindex, nofollow; set FALSE for index, follow.', FB_WM_TEXTDOMAIN); ?></small>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row">
					<label for="wm_config-exclude"><?php _e( 'Exclude:', FB_WM_TEXTDOMAIN); ?></label>
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
					<small><?php _e( 'Exclude feed, pages, posts, archives or IPs from the maintenance mode. Add the Slug of page or post as a comma-separated list.<br />Example:', FB_WM_TEXTDOMAIN); ?> <code>wp-cron, feed, wp-admin, ?page_id=12, about, category/test, 127.0.0.1</code></small>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row">
					<label for="wm_config-bypass"><?php _e( 'Bypass for Search Bots:', FB_WM_TEXTDOMAIN); ?></label>
				</th>
				<td>
					<select name="wm_config-bypass" id="wm_config-bypass">
						<option value="0" <?php selected( $value['bypass'], 0 ); ?>><?php _e('False', FB_WM_TEXTDOMAIN); ?> </option>
						<option value="1" <?php selected( $value['bypass'], 1 ); ?>><?php _e('True', FB_WM_TEXTDOMAIN); ?> </option>
					</select>
					<small><?php _e( 'Allow Search Bots to bypass maintenance mode?', FB_WM_TEXTDOMAIN); ?></small>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row">
					<label for="wm_config-role"><?php _e( 'Backend Role:', FB_WM_TEXTDOMAIN); ?></label>
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
					<small><?php _e( 'Allowed userrole to access the backend of this blog.', FB_WM_TEXTDOMAIN); ?>
					<?php if ( is_multisite() ) { _e( 'Super Admin has always access.', FB_WM_TEXTDOMAIN); } ?></small>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label for="wm_config-role_frontend"><?php _e( 'Frontend Role:', FB_WM_TEXTDOMAIN); ?></label>
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
					<small><?php _e( 'Allowed userrole to see the frontend of this blog.', FB_WM_TEXTDOMAIN); ?>
					<?php if ( is_multisite() ) { _e( 'Super Admin has always access.', FB_WM_TEXTDOMAIN); } ?></small>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label for="wm_config-rewrite"><?php _e( 'Redirection:', FB_WM_TEXTDOMAIN); ?></label>
				</th>
				<td class="alternate">
					<input class="large-text" size="30" type="text" id="wm_config-rewrite" name="wm_config-rewrite" value="<?php if ( isset($value['rewrite']) ) echo $value['rewrite']; ?>" />
					<br />
					<small><?php _e( 'If you want that after the login the destination address is not standard to the dashboard, then defining a URL. (incl. http://)', FB_WM_TEXTDOMAIN); ?></small>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row">
					<label for="wm_config-notice"><?php _e( 'Notice:', FB_WM_TEXTDOMAIN); ?></label>
				</th>
				<td>
					<select name="wm_config-notice" id="wm_config-notice">
						<option value="0" <?php selected( $value['notice'], 0 ); ?>><?php _e( 'False', FB_WM_TEXTDOMAIN); ?> </option>
						<option value="1" <?php selected( $value['notice'], 1 ); ?>><?php _e( 'True', FB_WM_TEXTDOMAIN); ?> </option>
					</select>
					<small><?php _e( 'Do you will see all notices, inside backend, the Admin Bar and the login screen?', FB_WM_TEXTDOMAIN); ?></small>
				</td>
			</tr>
		</table>
		<?php
	}