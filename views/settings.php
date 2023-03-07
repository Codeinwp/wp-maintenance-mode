<?php
/**
 * Settings
 *
 * @version 2.4.0
 */

defined( 'ABSPATH' ) || exit;

$is_old_version = version_compare( $GLOBALS['wp_version'], '5.8', '<' );
if ( ! isset( $this->plugin_settings['design']['page_id'] ) ) {
	$this->plugin_settings['design']['page_id'] = 0;
}

$is_otter_active = is_plugin_active( 'otter-blocks/otter-blocks.php' ) || defined( 'OTTER_BLOCKS_VERSION' );
?>
<div class="wrap">
	<h2 class="wpmm-title"><?php echo esc_html( get_admin_page_title() ); ?>
		<?php
		if ( get_option( 'wpmm_fresh_install', false ) ) {
			?>
			<span id="wizard-exit"><img src="<?php echo esc_attr( WPMM_IMAGES_URL . 'exit.svg' ); ?>" alt="exit"></span><?php } ?>
	</h2>

	<div class="wpmm-wrapper">
		<?php
		if ( get_option( 'wpmm_fresh_install', false ) ) {
			include 'wizard.php';
		} else {
			?>
		<div id="content" class="wrapper-cell">
			<div class="nav-tab-wrapper">
				<a class="nav-tab nav-tab-active" href="#general">
					<svg class="nav-tab-icon h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
					</svg>

					<?php esc_html_e( 'General', 'wp-maintenance-mode' ); ?>
				</a>
				<a class="nav-tab" href="#design">
					<svg class="nav-tab-icon h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
					</svg>

					<?php esc_html_e( 'Design', 'wp-maintenance-mode' ); ?></a>
				<a class="nav-tab" href="#modules">
					<svg class="nav-tab-icon h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14v6m-3-3h6M6 10h2a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v2a2 2 0 002 2zm10 0h2a2 2 0 002-2V6a2 2 0 00-2-2h-2a2 2 0 00-2 2v2a2 2 0 002 2zM6 20h2a2 2 0 002-2v-2a2 2 0 00-2-2H6a2 2 0 00-2 2v2a2 2 0 002 2z" />
					</svg>

					<?php esc_html_e( 'Modules', 'wp-maintenance-mode' ); ?>
				</a>
				<a class="nav-tab" href="#bot">
					<svg class="nav-tab-icon h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
					</svg>

					<?php esc_html_e( 'Manage Bot', 'wp-maintenance-mode' ); ?>
				</a>
				<?php if ( ! get_option( 'wpmm_new_look' ) ) { ?>
				<a class="nav-tab" href="#gdpr">
					<svg class="nav-tab-icon h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
					</svg>

					<?php esc_html_e( 'GDPR', 'wp-maintenance-mode' ); ?>
				</a> <?php } ?>
			</div>

			<div class="tabs-content">
				<div id="tab-general" class="">
					<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
						<table class="form-table">
							<tbody>
								<tr valign="top" class="<?php echo ! empty( $this->plugin_settings['general']['network_mode'] ) ? 'wpmm_status_disable' : ''; ?>">
									<th scope="row">
										<label for="options[general][status]"><?php esc_html_e( 'Status', 'wp-maintenance-mode' ); ?></label>
									</th>
									<td>
										<label><input type="radio" value="1" name="options[general][status]"<?php checked( $this->plugin_settings['general']['status'], 1 ); ?> /> <?php esc_html_e( 'Activated', 'wp-maintenance-mode' ); ?></label> <br />
										<label><input type="radio" value="0" name="options[general][status]"<?php checked( $this->plugin_settings['general']['status'], 0 ); ?> /> <?php esc_html_e( 'Deactivated', 'wp-maintenance-mode' ); ?></label>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="options[general][bypass_bots]"><?php esc_html_e( 'Bypass for Search Bots', 'wp-maintenance-mode' ); ?></label>
									</th>
									<td>
										<select name="options[general][bypass_bots]">
											<option value="1"<?php selected( $this->plugin_settings['general']['bypass_bots'], 1 ); ?>><?php esc_html_e( 'Yes', 'wp-maintenance-mode' ); ?></option>
											<option value="0"<?php selected( $this->plugin_settings['general']['bypass_bots'], 0 ); ?>><?php esc_html_e( 'No', 'wp-maintenance-mode' ); ?></option>
										</select>
										<p class="description"><?php esc_html_e( 'Allow Search Bots to bypass maintenance mode?', 'wp-maintenance-mode' ); ?></p>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="options[general][backend_role][]"><?php esc_html_e( 'Backend Role', 'wp-maintenance-mode' ); ?></label>
									</th>
									<td>
										<select name="options[general][backend_role][]" multiple="multiple" class="chosen-select" data-placeholder="<?php esc_attr_e( 'Select role(s)', 'wp-maintenance-mode' ); ?>">
											<?php foreach ( wpmm_get_user_roles() as $role_key => $role_name ) { ?>
												<option value="<?php echo esc_attr( $role_key ); ?>"<?php echo wpmm_multiselect( (array) $this->plugin_settings['general']['backend_role'], $role_key ); ?>><?php echo esc_html( $role_name ); ?></option>
											<?php } ?>
										</select>
										<p class="description"><?php esc_html_e( 'Which user role is allowed to access the backend of the website? Administrators will always have access.', 'wp-maintenance-mode' ); ?></p>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="options[general][frontend_role][]"><?php esc_html_e( 'Frontend Role', 'wp-maintenance-mode' ); ?></label>
									</th>
									<td>
										<select name="options[general][frontend_role][]" multiple="multiple" class="chosen-select" data-placeholder="<?php esc_attr_e( 'Select role(s)', 'wp-maintenance-mode' ); ?>">
											<?php foreach ( wpmm_get_user_roles() as $role_key => $role_name ) { ?>
												<option value="<?php echo esc_attr( $role_key ); ?>"<?php echo wpmm_multiselect( (array) $this->plugin_settings['general']['frontend_role'], $role_key ); ?>><?php echo esc_html( $role_name ); ?></option>
											<?php } ?>
										</select>
										<p class="description"><?php esc_html_e( 'Which user role is allowed to access the frontend of the website? Administrators will always have access.', 'wp-maintenance-mode' ); ?></p>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="options[general][meta_robots]"><?php esc_html_e( 'Robots Meta Tag', 'wp-maintenance-mode' ); ?></label>
									</th>
									<td>
										<select name="options[general][meta_robots]">
											<option value="1"<?php selected( $this->plugin_settings['general']['meta_robots'], 1 ); ?>>noindex, nofollow</option>
											<option value="0"<?php selected( $this->plugin_settings['general']['meta_robots'], 0 ); ?>>index, follow</option>
										</select>
										<p class="description"><?php esc_html_e( 'The robots meta tag lets you use a granular, page-specific approach to control how an individual page should be indexed and served to users in search results.', 'wp-maintenance-mode' ); ?></p>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="options[general][redirection]"><?php esc_html_e( 'Redirection', 'wp-maintenance-mode' ); ?></label>
									</th>
									<td>
										<input type="text" value="<?php echo esc_url( $this->plugin_settings['general']['redirection'] ); ?>" name="options[general][redirection]" />
										<p class="description"><?php esc_html_e( 'If you want to redirect a user (with no access to Dashboard/Backend) to a URL (different from WordPress Dashboard URL) after login, then define a URL (incl. https://)', 'wp-maintenance-mode' ); ?></p>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="options[general][exclude]"><?php esc_html_e( 'Exclude', 'wp-maintenance-mode' ); ?></label>
									</th>
									<td>
										<?php
										$exclude_list = ! empty( $this->plugin_settings['general']['exclude'] ) && is_array( $this->plugin_settings['general']['exclude'] ) ? $this->plugin_settings['general']['exclude'] : array();
										?>
										<textarea rows="7" name="options[general][exclude]" style="width: 625px;"><?php echo esc_textarea( implode( "\n", $exclude_list ) ); ?></textarea>
										<p class="description"><?php esc_html_e( 'Exclude feed, pages, archives or IPs from maintenance mode. Add one slug / IP per line! Comments start with # and can be appended at the end of a line.', 'wp-maintenance-mode' ); ?></p>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="options[general][notice]"><?php esc_html_e( 'Notice', 'wp-maintenance-mode' ); ?></label>
									</th>
									<td>
										<select name="options[general][notice]">
											<option value="1"<?php selected( $this->plugin_settings['general']['notice'], 1 ); ?>><?php esc_html_e( 'Yes', 'wp-maintenance-mode' ); ?></option>
											<option value="0"<?php selected( $this->plugin_settings['general']['notice'], 0 ); ?>><?php esc_html_e( 'No', 'wp-maintenance-mode' ); ?></option>
										</select>
										<p class="description"><?php esc_html_e( 'Do you want to see notices when maintenance mode is activated?', 'wp-maintenance-mode' ); ?></p>
									</td>
								</tr>
								<?php if ( ! get_option( 'wpmm_new_look' ) ) { ?>
								<tr valign="top">
									<th scope="row">
										<label for="options[general][admin_link]"><?php esc_html_e( 'Dashboard link', 'wp-maintenance-mode' ); ?></label>
									</th>
									<td>
										<select name="options[general][admin_link]">
											<option value="1"<?php selected( $this->plugin_settings['general']['admin_link'], 1 ); ?>><?php esc_html_e( 'Yes', 'wp-maintenance-mode' ); ?></option>
											<option value="0"<?php selected( $this->plugin_settings['general']['admin_link'], 0 ); ?>><?php esc_html_e( 'No', 'wp-maintenance-mode' ); ?></option>
										</select>
										<p class="description"><?php esc_html_e( 'Do you want to add a link to the dashboard on your maintenance mode page?', 'wp-maintenance-mode' ); ?></p>
									</td>
								</tr> <?php } ?>
							</tbody>
						</table>

						<?php wpmm_form_hidden_fields( 'general' ); ?>
						<input type="submit" value="<?php esc_attr_e( 'Save settings', 'wp-maintenance-mode' ); ?>" class="button button-primary" name="submit" />
						<input type="button" value="<?php esc_attr_e( 'Reset settings', 'wp-maintenance-mode' ); ?>" class="button button-secondary reset_settings" data-tab="general" name="submit" />
					</form>
				</div>

				<div id="tab-design" class="hidden">
					<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
						<?php
						if ( get_option( 'wpmm_new_look' ) ) {
							$overrideable_template = wpmm_get_template_path( 'maintenance.php', true );

							if ( WPMM_VIEWS_PATH . 'maintenance.php' !== $overrideable_template ) {
								?>
								<p class="notice notice-info"><?php esc_html_e( 'You are using a custom template from your theme/child theme folder.', 'wp-maintenance-mode' ); ?></p>
								<?php
							} elseif ( ( ! get_post( $this->plugin_settings['design']['page_id'] ) || get_post_status( $this->plugin_settings['design']['page_id'] ) === 'trash' ) && $this->plugin_settings['general']['status'] === 1 ) {
								?>
								<p class="notice notice-error"><?php esc_html_e( 'You don\'t have a maintenance page or your Maintenance Page has been deleted. Please select another one from the dropdown below or import a template and a new one will be created.', 'wp-maintenance-mode' ); ?></p><?php } ?>
							<table class="form-table">
								<tbody>
									<tr valign="top">
										<th scope="row">
											<label for="design_page_id"><?php esc_html_e( 'Select page', 'wp-maintenance-mode' ); ?></label>
										</th>
										<td>
											<?php
											wp_dropdown_pages(
												array(
													'selected' => isset( $this->plugin_settings['design']['page_id'] ) ? $this->plugin_settings['design']['page_id'] : 0,
													'name' => 'options[design][page_id]',
													'id'   => 'design_page_id',
													'option_none_value' => '',
													'show_option_no_change' => __( 'Select page', 'wp-maintenance-mode' ),
													'post_status' => array( 'publish', 'private' ),
												)
											);

											$page_status = get_post_status( isset( $this->plugin_settings['design']['page_id'] ) ? $this->plugin_settings['design']['page_id'] : 0 );
											if ( $page_status && $page_status !== 'trash' ) {
												?>
												<a href="<?php echo get_edit_post_link( $this->plugin_settings['design']['page_id'] ); ?>"><?php esc_html_e( 'Edit page', 'wp-maintenance-mode' ); ?></a> <?php } ?>
										</td>
									</tr>
								</tbody>
							</table>
							<table>
								<tbody><tr valign="top">
									<p class="description"><?php esc_html_e( 'Select the page that will be used as the Maintenance, Coming Soon or Landing page.', 'wp-maintenance-mode' ); ?></p>
								</tr></tbody>
							</table>
							<table class="form-table">
								<tbody>
									<tr valign="top">
										<th scope="row">
											<label for="dashboard-template" class="wpmm-templates-gallery__label"><?php esc_html_e( 'Pick a template', 'wp-maintenance-mode' ); ?></label>
										</th>
										<td class="category-select-wrap">
											<select name="options[design][template_category]" id="template-category">
												<option value="all"<?php selected( $this->plugin_settings['design']['template_category'], 'all' ); ?>><?php esc_html_e( 'All Templates', 'wp-maintenance-mode' ); ?></option>
												<?php
												$categories = WP_Maintenance_Mode::get_page_categories();
												foreach ( $categories as $category => $label ) {
													?>
														<option value="<?php echo esc_attr( $category ); ?>"<?php selected( $this->plugin_settings['design']['template_category'], $category ); ?>><?php echo esc_html( $label ); ?></option>
													<?php
												}
												?>
											</select>
										</td>
									</tr>
								</tbody>
							</table>
							<?php if ( $is_old_version ) { ?>
								<p class="description"><i><?php echo __( '<b>Note</b>: You need at least WP 5.8 to use new generation maintenance pages.', 'wp-maintenance-mode' ); ?></i></p>
							<?php } else { ?>
								<p class="wpmm-templates-gallery__description">
									<?php esc_html_e( 'Pick one of our starter templates for your maintenance or coming soon page. You can always customize them based on your needs.', 'wp-maintenance-mode' ); ?>
									<br/>
									<?php esc_html_e( 'Stay in the loop for more templates!', 'wp-maintenance-mode' ); ?>
								</p>
								<?php
								if ( ! $is_otter_active ) {
									echo $this->get_otter_notice( 'settings' );
								}
								?>
							<?php } ?>
							<div class="wpmm-templates">
								<?php
								if ( ! isset( $this->plugin_settings['design']['template_category'] ) ) {
									$this->plugin_settings['design']['template_category'] = 'all';
									update_option( 'wpmm_settings', $this->plugin_settings );
								}

								$selected_category = $this->plugin_settings['design']['template_category'];
								$categories        = WP_Maintenance_Mode::get_page_categories();

								if ( $selected_category !== 'all' ) {
									$categories = array( $selected_category => array( $selected_category ) );
								}

								$will_replace = isset( $this->plugin_settings['design']['page_id'] ) &&
												! ( ! get_post( $this->plugin_settings['design']['page_id'] ) ||
													empty( trim( get_post( $this->plugin_settings['design']['page_id'] )->post_content ) ) ||
													get_post( $this->plugin_settings['design']['page_id'] )->post_status === 'trash' );

								foreach ( $categories as $category => $label ) {
									$templates = list_files( WPMM_TEMPLATES_PATH . $category . '/', 1 );

									natsort( $templates );

									foreach ( $templates as $template ) {
										$name      = basename( $template );
										$thumbnail = WPMM_TEMPLATES_URL . $category . '/' . $name . '/screenshot.png';
										$content   = WPMM_TEMPLATES_PATH . $category . '/' . $name . '/blocks-export.json';

										$template_label = json_decode( file_get_contents( $content ) )->label;
										?>
										<div class="wpmm-template-wrap">
											<div class="wpmm-template-image-wrap <?php echo $is_old_version ? '' : 'can-import'; ?>">
												<img src="<?php echo $thumbnail; ?>" alt="<?php echo $name; ?>"/>
												<?php if ( ! $is_old_version ) { ?>
													<button type="button" class="button button-primary button-import" data-tab="design" data-slug="<?php echo esc_attr( $name ); ?>" data-category="<?php echo esc_attr( $category ); ?>" data-replace="<?php echo (int) $will_replace; ?>"><?php esc_html_e( 'Import template', 'wp-maintenance-mode' ); ?></button>
												<?php } ?>
											</div>
											<p class="description"><?php echo $template_label; ?></p>
										</div>
										<?php
									}
								}
								?>
							</div>
						<?php } else { /* legacy code */ ?>
						<table class="form-table">
							<tbody>
								<tr valign="top">
									<th scope="row">
										<label for="options[design][title]"><?php esc_html_e( 'Title (HTML tag)', 'wp-maintenance-mode' ); ?></label>
									</th>
									<td>
										<input type="text" value="<?php echo esc_attr( $this->plugin_settings['design']['title'] ); ?>" name="options[design][title]" />
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="options[design][heading]"><?php esc_html_e( 'Heading', 'wp-maintenance-mode' ); ?></label>
									</th>
									<td class="has-inline-color-picker">
										<input type="text" value="<?php echo esc_attr( $this->plugin_settings['design']['heading'] ); ?>" name="options[design][heading]" />
										<input type="text" value="<?php echo esc_attr( $this->plugin_settings['design']['heading_color'] ); ?>" name="options[design][heading_color]" class="color_picker_trigger" />
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="options[design][text]"><?php esc_html_e( 'Text', 'wp-maintenance-mode' ); ?></label>
									</th>
									<td>
										<?php
										wp_editor(
											wp_kses_post( $this->plugin_settings['design']['text'] ),
											'options_design_text',
											array(
												'textarea_name' => 'options[design][text]',
												'textarea_rows' => 8,
												'editor_class' => 'large-text',
												'media_buttons' => true,
												'wpautop' => false,
												'default_editor' => 'tinymce',
												'teeny'   => true,
											)
										);
										?>
										<div class="shortcodes-list-wrapper">
											<?php
											$hide_shortcodes_text = __( 'Hide available shortcodes', 'wp-maintenance-mode' );
											$show_shortcodes_text = __( 'See available shortcodes', 'wp-maintenance-mode' );
											?>
											<a href="javascript:void(0);" class="button button-small toggle-shortcodes-list" data-hide="<?php echo esc_attr( $hide_shortcodes_text ); ?>" data-show="<?php echo esc_attr( $show_shortcodes_text ); ?>" ><?php echo esc_html( $show_shortcodes_text ); ?></a>

											<ul class="shortcodes-list">
												<li>
													<?php
													/* translators: shortcode tag */
													printf( esc_html__( '%s - display a login form', 'wp-maintenance-mode' ), '[loginform]' );
													?>
												</li>
												<li>
													<?php
													/* translators: 1: shortcode tag, 2: list of compatible services, 3: shortcode example */
													printf( esc_html__( '%1$s - responsive video embed. Compatible with %2$s. Example: %3$s', 'wp-maintenance-mode' ), '[embed]', 'YouTube, Vimeo, DailyMotion', '<span>[embed]https://www.youtube.com/watch?v=HCfPhZQz2CE[/embed]</span>' );
													?>
												</li>
											</ul>
										</div>
										<p class="description"><?php esc_html_e( 'This text will not be shown when the bot feature is enabled.', 'wp-maintenance-mode' ); ?></p>
										<br />
										<input type="text" value="<?php echo esc_attr( $this->plugin_settings['design']['text_color'] ); ?>" name="options[design][text_color]" class="color_picker_trigger" />
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="options[design][footer_links_color]"><?php esc_html_e( 'Footer links', 'wp-maintenance-mode' ); ?></label>
									</th>
									<td>
										<input type="text" value="<?php echo esc_attr( $this->plugin_settings['design']['footer_links_color'] ); ?>" name="options[design][footer_links_color]" class="color_picker_trigger" />
										<p class="description"><?php esc_html_e( '"Dashboard" and "Privacy Policy" links.', 'wp-maintenance-mode' ); ?></p>
									</td>
								</tr>
							</tbody>
						</table>

						<h3>&raquo; <?php esc_html_e( 'Background', 'wp-maintenance-mode' ); ?></h3>

						<table class="form-table">
							<tbody>
								<tr valign="top">
									<th scope="row">
										<label for="options[design][bg_type]"><?php esc_html_e( 'Choose type', 'wp-maintenance-mode' ); ?></label>
									</th>
									<td>
										<select name="options[design][bg_type]" id="design_bg_type">
											<option value="color"<?php selected( $this->plugin_settings['design']['bg_type'], 'color' ); ?>><?php esc_html_e( 'Custom color', 'wp-maintenance-mode' ); ?></option>
											<option value="custom"<?php selected( $this->plugin_settings['design']['bg_type'], 'custom' ); ?>><?php esc_html_e( 'Uploaded background', 'wp-maintenance-mode' ); ?></option>
											<option value="predefined"<?php selected( $this->plugin_settings['design']['bg_type'], 'predefined' ); ?>><?php esc_html_e( 'Predefined background', 'wp-maintenance-mode' ); ?></option>
										</select>
									</td>
								</tr>
								<tr valign="top" class="design_bg_types <?php echo $this->plugin_settings['design']['bg_type'] !== 'color' ? 'hidden' : ''; ?>" id="show_color">
									<th scope="row">
										<label for="options[design][bg_color]"><?php esc_html_e( 'Choose color', 'wp-maintenance-mode' ); ?></label>
									</th>
									<td>
										<input type="text" value="<?php echo esc_attr( $this->plugin_settings['design']['bg_color'] ); ?>" name="options[design][bg_color]" class="color_picker_trigger" />
									</td>
								</tr>
								<tr valign="top" class="design_bg_types <?php echo $this->plugin_settings['design']['bg_type'] !== 'custom' ? 'hidden' : ''; ?>" id="show_custom">
									<th scope="row">
										<label for="options[design][bg_custom]"><?php esc_html_e( 'Upload background', 'wp-maintenance-mode' ); ?></label>
									</th>
									<td>
										<input type="text" value="<?php echo esc_url( $this->plugin_settings['design']['bg_custom'] ); ?>" name="options[design][bg_custom]" class="background_url" />
										<input
												type="button"
												value="<?php echo esc_attr_x( 'Upload', 'upload background button', 'wp-maintenance-mode' ); ?>"
												class="button image_uploader_trigger"
												data-name="background"
												data-title="<?php esc_attr_e( 'Upload Background', 'wp-maintenance-mode' ); ?>"
												data-button-text="<?php esc_attr_e( 'Choose Background', 'wp-maintenance-mode' ); ?>"
												data-to-selector=".background_url"
										/>
										<p class="description"><?php esc_html_e( 'Backgrounds should have 1920x1280 px size.', 'wp-maintenance-mode' ); ?></p>
									</td>
								</tr>
								<tr valign="top" class="design_bg_types <?php echo $this->plugin_settings['design']['bg_type'] !== 'predefined' ? 'hidden' : ''; ?>" id="show_predefined">
									<th scope="row">
										<label for="options[design][bg_predefined]"><?php esc_html_e( 'Choose background', 'wp-maintenance-mode' ); ?></label>

										<p class="description">
											<?php
											printf(
												wp_kses(
												/* translators: free photos url */
													__( '* source <a href="%s" target="_blank">Free Photos</a>', 'wp-maintenance-mode' ),
													wpmm_translated_string_allowed_html()
												),
												esc_url( wpmm_get_utmized_url( 'https://themeisle.com/blog/wordpress-stock-photos/', array( 'campaign' => 'settings' ) ) )
											);
											?>
										</p>
									</th>
									<td>
										<ul class="bg_list">
											<?php foreach ( wpmm_get_backgrounds() as $filename ) { ?>
												<li class="<?php echo $this->plugin_settings['design']['bg_predefined'] === $filename['big'] ? 'active' : ''; ?>">
													<label>
														<input type="radio" value="<?php echo esc_attr( $filename['big'] ); ?>" name="options[design][bg_predefined]"<?php checked( $this->plugin_settings['design']['bg_predefined'], $filename['big'] ); ?> />
														<img src="<?php echo esc_url( WPMM_URL . 'assets/images/backgrounds/' . $filename['small'] ); ?>" width="200" height="150" />
													</label>
												</li>
											<?php } ?>
										</ul>
									</td>
								</tr>
							</tbody>
						</table>

						<h3>&raquo; <?php esc_html_e( 'Other', 'wp-maintenance-mode' ); ?></h3>

						<table class="form-table">
							<tbody>
								<tr valign="top">
									<th scope="row">
										<label for="options[design][other_custom_css]"><?php esc_html_e( 'Custom CSS', 'wp-maintenance-mode' ); ?></label>
									</th>
									<td>
										<textarea rows="10" name="options[design][other_custom_css]" style="width:625px;" id="other_custom_css"><?php echo esc_textarea( wp_strip_all_tags( $this->plugin_settings['design']['other_custom_css'] ) ); ?></textarea>
										<p class="description"><?php esc_html_e( 'Do not add <style> tags.', 'wp-maintenance-mode' ); ?></p>
									</td>
								</tr>
							</tbody>
						</table>

						<input type="submit" value="<?php esc_attr_e( 'Save settings', 'wp-maintenance-mode' ); ?>" class="button button-primary" name="submit" />
						<input type="button" value="<?php esc_attr_e( 'Reset settings', 'wp-maintenance-mode' ); ?>" class="button button-secondary reset_settings" data-tab="design" name="submit" />

							<?php
						} /* end of legacy code */
						wpmm_form_hidden_fields( 'design' );
						?>
					</form>
				</div>

				<div id="tab-modules" class="hidden">
					<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
						<?php if ( ! get_option( 'wpmm_new_look' ) ) { ?>
						<h3>&raquo; <?php esc_html_e( 'Countdown', 'wp-maintenance-mode' ); ?></h3>

						<table class="form-table">
							<tbody>
								<tr valign="top">
									<th scope="row">
										<label for="options[modules][countdown_status]"><?php esc_html_e( 'Show countdown?', 'wp-maintenance-mode' ); ?></label>
									</th>
									<td>
										<select name="options[modules][countdown_status]">
											<option value="1"<?php selected( $this->plugin_settings['modules']['countdown_status'], 1 ); ?>><?php esc_html_e( 'Yes', 'wp-maintenance-mode' ); ?></option>
											<option value="0"<?php selected( $this->plugin_settings['modules']['countdown_status'], 0 ); ?>><?php esc_html_e( 'No', 'wp-maintenance-mode' ); ?></option>
										</select>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="options[modules][countdown_start]"><?php esc_html_e( 'Start date', 'wp-maintenance-mode' ); ?></label>
									</th>
									<td>
										<input type="text" value="<?php echo esc_attr( $this->plugin_settings['modules']['countdown_start'] ); ?>" name="options[modules][countdown_start]" class="countdown_start" />
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="options[modules][countdown_details]"><?php esc_html_e( 'Countdown (remaining time)', 'wp-maintenance-mode' ); ?></label>
									</th>
									<td class="countdown_details">
										<input type="text" value="<?php echo esc_attr( $this->plugin_settings['modules']['countdown_details']['days'] ); ?>" name="options[modules][countdown_details][days]" /> <?php esc_html_e( 'Days', 'wp-maintenance-mode' ); ?>
										<input type="text" value="<?php echo esc_attr( $this->plugin_settings['modules']['countdown_details']['hours'] ); ?>" name="options[modules][countdown_details][hours]" class="margin_left" /> <?php esc_html_e( 'Hours', 'wp-maintenance-mode' ); ?>
										<input type="text" value="<?php echo esc_attr( $this->plugin_settings['modules']['countdown_details']['minutes'] ); ?>" name="options[modules][countdown_details][minutes]" class="margin_left" /> <?php esc_html_e( 'Minutes', 'wp-maintenance-mode' ); ?>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="options[modules][countdown_color]"><?php esc_html_e( 'Color', 'wp-maintenance-mode' ); ?></label>
									</th>
									<td>
										<input type="text" value="<?php echo esc_attr( $this->plugin_settings['modules']['countdown_color'] ); ?>" name="options[modules][countdown_color]" class="color_picker_trigger" />
									</td>
								</tr>
							</tbody>
						</table>

						<h3>&raquo; <?php esc_html_e( 'Subscribe', 'wp-maintenance-mode' ); ?></h3>

						<table class="form-table">
							<tbody>
								<tr valign="top">
									<th scope="row">
										<label for="options[modules][subscribe_status]"><?php esc_html_e( 'Show subscribe?', 'wp-maintenance-mode' ); ?></label>
									</th>
									<td>
										<select name="options[modules][subscribe_status]">
											<option value="1"<?php selected( $this->plugin_settings['modules']['subscribe_status'], 1 ); ?>><?php esc_html_e( 'Yes', 'wp-maintenance-mode' ); ?></option>
											<option value="0"<?php selected( $this->plugin_settings['modules']['subscribe_status'], 0 ); ?>><?php esc_html_e( 'No', 'wp-maintenance-mode' ); ?></option>
										</select>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="options[modules][subscribe_text]"><?php esc_html_e( 'Text', 'wp-maintenance-mode' ); ?></label>
									</th>
									<td class="has-inline-color-picker">
										<input type="text" value="<?php echo esc_attr( $this->plugin_settings['modules']['subscribe_text'] ); ?>" name="options[modules][subscribe_text]" />
										<input type="text" value="<?php echo esc_attr( $this->plugin_settings['modules']['subscribe_text_color'] ); ?>" name="options[modules][subscribe_text_color]" class="color_picker_trigger" />
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="options[modules][stats]"><?php esc_html_e( 'Stats', 'wp-maintenance-mode' ); ?></label>
									</th>
									<td id="subscribers_wrap">
										<?php
										$subscribers_no = wpmm_get_subscribers_count();

										/* translators: number of subscribers */
										echo esc_html( sprintf( _nx( 'You have %d subscriber', 'You have %d subscribers', $subscribers_no, 'settings page', 'wp-maintenance-mode' ), $subscribers_no ) );

										if ( current_user_can( wpmm_get_capability( 'subscribers' ) ) && $subscribers_no > 0 ) {
											?>
											<div class="buttons">
												<a class="button button-primary" id="subscribers-export" href="javascript:void(0);"><?php esc_html_e( 'Export as CSV', 'wp-maintenance-mode' ); ?></a>
												<a class="button button-secondary" id="subscribers-empty-list" href="javascript:void(0);"><?php esc_html_e( 'Empty subscribers list', 'wp-maintenance-mode' ); ?></a>
											</div>
										<?php } ?>
									</td>
								</tr>
							</tbody>
						</table>

						<h3>&raquo; <?php esc_html_e( 'Social Networks', 'wp-maintenance-mode' ); ?></h3>

						<table class="form-table">
							<tbody>
								<tr valign="top">
									<th scope="row">
										<label for="options[modules][social_status]"><?php esc_html_e( 'Show social networks?', 'wp-maintenance-mode' ); ?></label>
									</th>
									<td>
										<select name="options[modules][social_status]">
											<option value="1"<?php selected( $this->plugin_settings['modules']['social_status'], 1 ); ?>><?php esc_html_e( 'Yes', 'wp-maintenance-mode' ); ?></option>
											<option value="0"<?php selected( $this->plugin_settings['modules']['social_status'], 0 ); ?>><?php esc_html_e( 'No', 'wp-maintenance-mode' ); ?></option>
										</select>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="options[modules][social_target]"><?php esc_html_e( 'Links target?', 'wp-maintenance-mode' ); ?></label>
									</th>
									<td>
										<select name="options[modules][social_target]">
											<option value="1"<?php selected( $this->plugin_settings['modules']['social_target'], 1 ); ?>><?php esc_html_e( 'New page', 'wp-maintenance-mode' ); ?></option>
											<option value="0"<?php selected( $this->plugin_settings['modules']['social_target'], 0 ); ?>><?php esc_html_e( 'Same page', 'wp-maintenance-mode' ); ?></option>
										</select>
										<p class="description"><?php esc_html_e( 'Choose how the links will open.', 'wp-maintenance-mode' ); ?></p>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row" colspan="2" style="font-weight: normal;">
										<?php
										echo wp_kses(
											__( 'You have to use full URLs. For example: if your Twitter username is <code>WordPress</code>, the URL should be <code>https://twitter.com/WordPress</code>.', 'wp-maintenance-mode' ),
											wpmm_translated_string_allowed_html()
										);
										?>
									</th>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="options[modules][social_github]">Github</label>
									</th>
									<td>
										<input type="text" value="<?php echo esc_attr( $this->plugin_settings['modules']['social_github'] ); ?>" name="options[modules][social_github]" />
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="options[modules][social_dribbble]">Dribbble</label>
									</th>
									<td>
										<input type="text" value="<?php echo esc_attr( $this->plugin_settings['modules']['social_dribbble'] ); ?>" name="options[modules][social_dribbble]" />
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="options[modules][social_twitter]">Twitter</label>
									</th>
									<td>
										<input type="text" value="<?php echo esc_attr( $this->plugin_settings['modules']['social_twitter'] ); ?>" name="options[modules][social_twitter]" />
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="options[modules][social_facebook]">Facebook</label>
									</th>
									<td>
										<input type="text" value="<?php echo esc_attr( $this->plugin_settings['modules']['social_facebook'] ); ?>" name="options[modules][social_facebook]" />
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="options[modules][social_instagram]">Instagram</label>
									</th>
									<td>
										<input type="text" value="<?php echo esc_attr( $this->plugin_settings['modules']['social_instagram'] ); ?>" name="options[modules][social_instagram]" />
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="options[modules][social_pinterest]">Pinterest</label>
									</th>
									<td>
										<input type="text" value="<?php echo esc_attr( $this->plugin_settings['modules']['social_pinterest'] ); ?>" name="options[modules][social_pinterest]" />
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="options[modules][social_google+]">Google+</label>
									</th>
									<td>
										<input type="text" value="<?php echo esc_attr( $this->plugin_settings['modules']['social_google+'] ); ?>" name="options[modules][social_google+]" />
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="options[modules][social_linkedin]">Linkedin</label>
									</th>
									<td>
										<input type="text" value="<?php echo esc_attr( $this->plugin_settings['modules']['social_linkedin'] ); ?>" name="options[modules][social_linkedin]" />
									</td>
								</tr>
							</tbody>
						</table>

						<h3>&raquo; <?php esc_html_e( 'Contact', 'wp-maintenance-mode' ); ?></h3>

						<table class="form-table">
							<tbody>
								<tr valign="top">
									<th scope="row">
										<label for="options[modules][contact_status]"><?php esc_html_e( 'Show contact?', 'wp-maintenance-mode' ); ?></label>
									</th>
									<td>
										<select name="options[modules][contact_status]">
											<option value="1"<?php selected( $this->plugin_settings['modules']['contact_status'], 1 ); ?>><?php esc_html_e( 'Yes', 'wp-maintenance-mode' ); ?></option>
											<option value="0"<?php selected( $this->plugin_settings['modules']['contact_status'], 0 ); ?>><?php esc_html_e( 'No', 'wp-maintenance-mode' ); ?></option>
										</select>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="options[modules][contact_email]"><?php esc_html_e( 'Email address', 'wp-maintenance-mode' ); ?></label>
									</th>
									<td>
										<input type="text" value="<?php echo esc_attr( $this->plugin_settings['modules']['contact_email'] ); ?>" name="options[modules][contact_email]" />
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="options[modules][contact_effects]"><?php esc_html_e( 'Effects', 'wp-maintenance-mode' ); ?></label>
									</th>
									<td>
										<select name="options[modules][contact_effects]">
											<option value="move_top|move_bottom"<?php selected( $this->plugin_settings['modules']['contact_effects'], 'move_top|move_bottom' ); ?>><?php esc_html_e( 'Move top - Move bottom', 'wp-maintenance-mode' ); ?></option>
											<option value="zoom|zoomed"<?php selected( $this->plugin_settings['modules']['contact_effects'], 'zoom|zoomed' ); ?>><?php esc_html_e( 'Zoom - Zoomed', 'wp-maintenance-mode' ); ?></option>
											<option value="fold|unfold"<?php selected( $this->plugin_settings['modules']['contact_effects'], 'fold|unfold' ); ?>><?php esc_html_e( 'Fold - Unfold', 'wp-maintenance-mode' ); ?></option>
										</select>
									</td>
								</tr>
							</tbody>
						</table>
						<?php } ?>
						<?php if ( get_option( 'wpmm_new_look' ) ) { ?>
							<h3>&raquo; <?php esc_html_e( 'Subscribe', 'wp-maintenance-mode' ); ?></h3>

							<table class="form-table">
								<tbody>
								<tr valign="top">
									<th scope="row">
										<label for="options[modules][stats]"><?php esc_html_e( 'Stats', 'wp-maintenance-mode' ); ?></label>
									</th>
									<td id="subscribers_wrap">
										<?php
										$subscribers_no = wpmm_get_subscribers_count();

										/* translators: number of subscribers */
										echo esc_html( sprintf( _nx( 'You have %d subscriber', 'You have %d subscribers', $subscribers_no, 'settings page', 'wp-maintenance-mode' ), $subscribers_no ) );

										if ( current_user_can( wpmm_get_capability( 'subscribers' ) ) && $subscribers_no > 0 ) {
											?>
											<div class="buttons">
												<a class="button button-primary" id="subscribers-export" href="javascript:void(0);"><?php esc_html_e( 'Export as CSV', 'wp-maintenance-mode' ); ?></a>
												<a class="button button-secondary" id="subscribers-empty-list" href="javascript:void(0);"><?php esc_html_e( 'Empty subscribers list', 'wp-maintenance-mode' ); ?></a>
											</div>
										<?php } ?>
									</td>
								</tr>
								</tbody>
							</table>
						<?php } ?>
						<h3>&raquo; <?php esc_html_e( 'Google Analytics', 'wp-maintenance-mode' ); ?></h3>

						<table class="form-table">
							<tbody>
								<tr valign="top">
									<th scope="row">
										<label for="options[modules][ga_status]"><?php esc_html_e( 'Use Google Analytics?', 'wp-maintenance-mode' ); ?></label>
									</th>
									<td>
										<select name="options[modules][ga_status]">
											<option value="1"<?php selected( $this->plugin_settings['modules']['ga_status'], 1 ); ?>><?php esc_html_e( 'Yes', 'wp-maintenance-mode' ); ?></option>
											<option value="0"<?php selected( $this->plugin_settings['modules']['ga_status'], 0 ); ?>><?php esc_html_e( 'No', 'wp-maintenance-mode' ); ?></option>
										</select>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="options[modules][ga_anonymize_ip]"><?php esc_html_e( 'Enable IP anonymization?', 'wp-maintenance-mode' ); ?></label>
									</th>
									<td>
										<select name="options[modules][ga_anonymize_ip]">
											<option value="1"<?php selected( $this->plugin_settings['modules']['ga_anonymize_ip'], 1 ); ?>><?php esc_html_e( 'Yes', 'wp-maintenance-mode' ); ?></option>
											<option value="0"<?php selected( $this->plugin_settings['modules']['ga_anonymize_ip'], 0 ); ?>><?php esc_html_e( 'No', 'wp-maintenance-mode' ); ?></option>
										</select>
										<p class="description">
											<?php
											printf(
												wp_kses(
												/* translators: Google Analytics documentation url */
													__( 'Read about IP anonymization on <a href="%s" rel="noreferrer" target="_blank">Google Analytics</a> docs. It is always enabled on Google Analytics 4.', 'wp-maintenance-mode' ),
													wpmm_translated_string_allowed_html()
												),
												esc_url( 'https://support.google.com/analytics/answer/2763052' )
											);
											?>
										</p>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="options[modules][ga_code]"><?php esc_html_e( 'Tracking code', 'wp-maintenance-mode' ); ?></label>
									</th>
									<td>
										<input type="text" value="<?php echo esc_attr( $this->plugin_settings['modules']['ga_code'] ); ?>" name="options[modules][ga_code]" />
										<p class="description"><?php esc_html_e( 'Allowed formats: UA-XXXXXXXX, UA-XXXXXXXX-XXXX, G-XXXXXXXX. Eg: UA-12345678-1 is valid', 'wp-maintenance-mode' ); ?></p>
									</td>
								</tr>
							</tbody>
						</table>

						<?php wpmm_form_hidden_fields( 'modules' ); ?>
						<input type="submit" value="<?php esc_attr_e( 'Save settings', 'wp-maintenance-mode' ); ?>" class="button button-primary" name="submit" />
						<input type="button" value="<?php esc_attr_e( 'Reset settings', 'wp-maintenance-mode' ); ?>" class="button button-secondary reset_settings" data-tab="modules" name="submit" />
					</form>
				</div>

				<div id="tab-bot" class="hidden">
					<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
						<table class="form-table">
							<tbody>
								<tr valign="top">
									<td colspan="2">
										<h4><?php esc_html_e( 'Setup the conversation steps to capture more subscribers with this friendly way of asking email addresses.', 'wp-maintenance-mode' ); ?></h4>
										<p><?php esc_html_e( 'You may also want to use these wildcards: {bot_name} and {visitor_name} to make the conversation even more realistic.', 'wp-maintenance-mode' ); ?></p>
										<p><?php esc_html_e( 'It is also ok if you don\'t fill in all the conversation steps if you don\'t need to.', 'wp-maintenance-mode' ); ?></p>
										<p><?php esc_html_e( 'If you want to see the list of subscribers, go to Modules &raquo; Subscribe &raquo; Export as CSV.', 'wp-maintenance-mode' ); ?></p>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="options[bot][status]"><?php esc_html_e( 'Status', 'wp-maintenance-mode' ); ?></label>
									</th>
									<td>
										<label><input type="radio" value="1" name="options[bot][status]"<?php checked( $this->plugin_settings['bot']['status'], 1 ); ?> /> <?php esc_html_e( 'Activated', 'wp-maintenance-mode' ); ?></label> <br />
										<label><input type="radio" value="0" name="options[bot][status]"<?php checked( $this->plugin_settings['bot']['status'], 0 ); ?> /> <?php esc_html_e( 'Deactivated', 'wp-maintenance-mode' ); ?></label>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="options[bot][name]"><?php esc_html_e( 'Bot Name', 'wp-maintenance-mode' ); ?></label>
									</th>
									<td>
										<input type="text" name="options[bot][name]" id="options[bot][name]" value="<?php echo esc_attr( $this->plugin_settings['bot']['name'] ); ?>" />
										<p class="description"><?php esc_html_e( 'This name will appear when the bot is typing.', 'wp-maintenance-mode' ); ?></p>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="options[bot][avatar]"><?php esc_html_e( 'Upload avatar', 'wp-maintenance-mode' ); ?></label>
									</th>
									<td>
										<input type="text" value="<?php echo esc_url( $this->plugin_settings['bot']['avatar'] ); ?>" name="options[bot][avatar]" id="options[bot][avatar]" class="avatar_url" />
										<input
												type="button"
												value="<?php echo esc_attr_x( 'Upload', 'upload avatar button', 'wp-maintenance-mode' ); ?>"
												class="button image_uploader_trigger"
												data-name="avatar"
												data-title="<?php esc_attr_e( 'Upload Avatar', 'wp-maintenance-mode' ); ?>"
												data-button-text="<?php esc_attr_e( 'Choose picture', 'wp-maintenance-mode' ); ?>"
												data-to-selector=".avatar_url"
										/>
										<p class="description"><?php esc_html_e( 'A 512 x 512 px will work just fine.', 'wp-maintenance-mode' ); ?></p>
									</td>
								</tr>
							</tbody>
						</table>

						<h3>&raquo; <?php esc_html_e( 'Customize Messages', 'wp-maintenance-mode' ); ?></h3>

						<table class="form-table">
							<tbody>
								<tr valign="top">
									<th scope="row">
										<label for="options[bot][messages][01]"><?php esc_html_e( 'Message 1', 'wp-maintenance-mode' ); ?></label>
									</th>
									<td>
										<textarea name="options[bot][messages][01]" id="options[bot][messages][01]" rows="2" style="width: 625px;"><?php echo esc_textarea( $this->plugin_settings['bot']['messages']['01'] ); ?></textarea>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="options[bot][messages][02]"><?php esc_html_e( 'Message 2', 'wp-maintenance-mode' ); ?></label>
									</th>
									<td>
										<textarea name="options[bot][messages][02]" id="options[bot][messages][02]" rows="2" style="width: 625px;"><?php echo esc_textarea( $this->plugin_settings['bot']['messages']['02'] ); ?></textarea>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="options[bot][messages][03]"><?php esc_html_e( 'Message 3', 'wp-maintenance-mode' ); ?></label>
									</th>
									<td>
										<textarea name="options[bot][messages][03]" id="options[bot][messages][03]" rows="2" style="width: 625px;"><?php echo esc_textarea( $this->plugin_settings['bot']['messages']['03'] ); ?></textarea>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="options[bot][responses][01]"><?php esc_html_e( 'Response', 'wp-maintenance-mode' ); ?></label>
									</th>
									<td>
										<input type="text" name="options[bot][responses][01]" id="options[bot][responses][01]" value="<?php echo esc_attr( $this->plugin_settings['bot']['responses']['01'] ); ?>" />
										<span class="bot-hint"><?php echo esc_html_x( 'Visitor\'s response will be here.', 'response for message 3', 'wp-maintenance-mode' ); ?></span>
										<p class="description"><?php esc_html_e( 'Edit the placeholder\'s text', 'wp-maintenance-mode' ); ?></p>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="options[bot][messages][04]"><?php esc_html_e( 'Message 4', 'wp-maintenance-mode' ); ?></label>
									</th>
									<td>
										<textarea name="options[bot][messages][04]" id="options[bot][messages][04]" rows="2" style="width: 625px;"><?php echo esc_textarea( $this->plugin_settings['bot']['messages']['04'] ); ?></textarea>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="options[bot][messages][05]"><?php esc_html_e( 'Message 5', 'wp-maintenance-mode' ); ?></label>
									</th>
									<td>
										<textarea name="options[bot][messages][05]" id="options[bot][messages][05]" rows="2" style="width: 625px;"><?php echo esc_textarea( $this->plugin_settings['bot']['messages']['05'] ); ?></textarea>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="options[bot][messages][06]"><?php esc_html_e( 'Message 6', 'wp-maintenance-mode' ); ?></label>
									</th>
									<td>
										<textarea name="options[bot][messages][06]" id="options[bot][messages][06]" rows="2" style="width: 625px;"><?php echo esc_textarea( $this->plugin_settings['bot']['messages']['06'] ); ?></textarea>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="options[bot][messages][07]"><?php esc_html_e( 'Message 7', 'wp-maintenance-mode' ); ?></label>
									</th>
									<td>
										<textarea name="options[bot][messages][07]" id="options[bot][messages][07]" rows="2" style="width: 625px;"><?php echo esc_textarea( $this->plugin_settings['bot']['messages']['07'] ); ?></textarea>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="options[bot][responses][02_1]"><?php esc_html_e( 'Response', 'wp-maintenance-mode' ); ?></label>
									</th>
									<td>
										<div class="bot-button">
											<input type="text" name="options[bot][responses][02_1]" id="options[bot][responses][02_1]" value="<?php echo esc_attr( $this->plugin_settings['bot']['responses']['02_1'] ); ?>" />
											<p class="description"><?php esc_html_e( 'Edit button one', 'wp-maintenance-mode' ); ?></p>
										</div>
										<div class="bot-button">
											<input type="text" name="options[bot][responses][02_2]" id="options[bot][responses][02_2]" value="<?php echo esc_attr( $this->plugin_settings['bot']['responses']['02_2'] ); ?>" />
											<p class="description"><?php esc_html_e( 'Edit button two', 'wp-maintenance-mode' ); ?></p>
										</div>
										<span class="bot-hint"><?php echo esc_html_x( 'Visitor\'s response will be here.', 'response for message 7', 'wp-maintenance-mode' ); ?></span>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="options[bot][messages][08_1]"><?php esc_html_e( 'Message 8', 'wp-maintenance-mode' ); ?><br><small><?php esc_html_e( '(click on button one)', 'wp-maintenance-mode' ); ?></small></label>
									</th>
									<td>
										<textarea name="options[bot][messages][08_1]" id="options[bot][messages][08_1]" rows="2" style="width: 625px;"><?php echo esc_textarea( $this->plugin_settings['bot']['messages']['08_1'] ); ?></textarea>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="options[bot][responses][03]"><?php esc_html_e( 'Response', 'wp-maintenance-mode' ); ?></label>
									</th>
									<td>
										<input type="text" name="options[bot][responses][03]" id="options[bot][responses][03]" value="<?php echo esc_attr( $this->plugin_settings['bot']['responses']['03'] ); ?>" />
										<span class="bot-hint"><?php echo esc_html_x( 'Visitor\'s response will be here.', 'response for message 8 (click on button one)', 'wp-maintenance-mode' ); ?></span>
										<p class="description"><?php esc_html_e( 'Edit the placeholder\'s text', 'wp-maintenance-mode' ); ?></p>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="options[bot][messages][08_2]"><?php esc_html_e( 'Message 8', 'wp-maintenance-mode' ); ?><br><small><?php esc_html_e( '(click on button two)', 'wp-maintenance-mode' ); ?></small></label>
									</th>
									<td>
										<textarea name="options[bot][messages][08_2]" id="options[bot][messages][08_2]" rows="2" style="width: 625px;"><?php echo esc_textarea( $this->plugin_settings['bot']['messages']['08_2'] ); ?></textarea>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="options[bot][messages][09]"><?php esc_html_e( 'Message 9', 'wp-maintenance-mode' ); ?><br><small><?php esc_html_e( '(click on button one)', 'wp-maintenance-mode' ); ?></small></label>
									</th>
									<td>
										<textarea name="options[bot][messages][09]" id="options[bot][messages][09]" rows="2" style="width: 625px;"><?php echo esc_textarea( $this->plugin_settings['bot']['messages']['09'] ); ?></textarea>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="options[bot][messages][10]"><?php esc_html_e( 'Message 10', 'wp-maintenance-mode' ); ?><br><small><?php esc_html_e( '(click on button one)', 'wp-maintenance-mode' ); ?></small></label>
									</th>
									<td>
										<textarea name="options[bot][messages][10]" id="options[bot][messages][10]" rows="2" style="width: 625px;"><?php echo esc_textarea( $this->plugin_settings['bot']['messages']['10'] ); ?></textarea>
									</td>
								</tr>
							</tbody>
						</table>

						<?php wpmm_form_hidden_fields( 'bot' ); ?>
						<input type="submit" value="<?php esc_attr_e( 'Save settings', 'wp-maintenance-mode' ); ?>" class="button button-primary" name="submit" />
						<input type="button" value="<?php esc_attr_e( 'Reset settings', 'wp-maintenance-mode' ); ?>" class="button button-secondary reset_settings" data-tab="bot" name="submit" />
					</form>
				</div>

				<?php if ( ! get_option( 'wpmm_new_look' ) ) { ?>
				<div id="tab-gdpr" class="hidden">
					<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
						<table class="form-table">
							<tbody>
								<tr valign="top">
									<td colspan="2">
										<h4><?php esc_html_e( 'To make the plugin GDPR compliant, fill in the details and enable this section.', 'wp-maintenance-mode' ); ?></h4>
										<p><?php esc_html_e( 'Here we added some generic texts that you may want to review, change or remove.', 'wp-maintenance-mode' ); ?></p>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="options[gdpr][status]"><?php esc_html_e( 'Status', 'wp-maintenance-mode' ); ?></label>
									</th>
									<td>
										<label><input type="radio" value="1" name="options[gdpr][status]"<?php checked( $this->plugin_settings['gdpr']['status'], 1 ); ?> /> <?php esc_html_e( 'Activated', 'wp-maintenance-mode' ); ?></label> <br />
										<label><input type="radio" value="0" name="options[gdpr][status]"<?php checked( $this->plugin_settings['gdpr']['status'], 0 ); ?> /> <?php esc_html_e( 'Deactivated', 'wp-maintenance-mode' ); ?></label>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="options[gdpr][policy_page_label]"><?php esc_html_e( 'Link name', 'wp-maintenance-mode' ); ?></label>
									</th>
									<td>
										<input type="text" value="<?php echo esc_attr( $this->plugin_settings['gdpr']['policy_page_label'] ); ?>" name="options[gdpr][policy_page_label]" />
										<p class="description"><?php esc_html_e( 'Label the link that will be shown on frontend footer', 'wp-maintenance-mode' ); ?></p>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="options[gdpr][policy_page_link]"><?php esc_html_e( 'P. Policy page link', 'wp-maintenance-mode' ); ?></label>
									</th>
									<td>
										<input type="text" value="<?php echo esc_url( $this->plugin_settings['gdpr']['policy_page_link'] ); ?>" name="options[gdpr][policy_page_link]" />
										<p class="description"><?php echo wp_kses( $this->get_policy_link_message(), wpmm_translated_string_allowed_html() ); ?></p>
										<p class="description"><?php esc_html_e( 'REMEMBER: In order to make the privacy policy page accessible you need to add it in General -> Exclude.', 'wp-maintenance-mode' ); ?></p>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="options[gdpr][policy_page_target]"><?php esc_html_e( 'P. Policy link target', 'wp-maintenance-mode' ); ?></label>
									</th>
									<td>
										<select name="options[gdpr][policy_page_target]">
											<option value="1"<?php selected( $this->plugin_settings['gdpr']['policy_page_target'], 1 ); ?>><?php esc_html_e( 'New page', 'wp-maintenance-mode' ); ?></option>
											<option value="0"<?php selected( $this->plugin_settings['gdpr']['policy_page_target'], 0 ); ?>><?php esc_html_e( 'Same page', 'wp-maintenance-mode' ); ?></option>
										</select>
										<p class="description"><?php esc_html_e( 'Choose how the link will open.', 'wp-maintenance-mode' ); ?></p>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="options[gdpr][contact_form_tail]"><?php esc_html_e( 'Contact form \'tail\'', 'wp-maintenance-mode' ); ?></label>
									</th>
									<td>
										<textarea name="options[gdpr][contact_form_tail]" rows="3" style="width: 600px"><?php echo esc_textarea( wp_kses( $this->plugin_settings['gdpr']['contact_form_tail'], wpmm_gdpr_textarea_allowed_html() ) ); ?></textarea>
										<p class="description"><?php esc_html_e( 'This will be shown together with the acceptance checkbox below the form', 'wp-maintenance-mode' ); ?></p>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="options[gdpr][subscribe_form_tail]"><?php esc_html_e( 'Subscribe form \'tail\'', 'wp-maintenance-mode' ); ?></label>
									</th>
									<td>
										<textarea name="options[gdpr][subscribe_form_tail]" rows="3" style="width: 600px"><?php echo esc_textarea( wp_kses( $this->plugin_settings['gdpr']['subscribe_form_tail'], wpmm_gdpr_textarea_allowed_html() ) ); ?></textarea>
										<p class="description"><?php esc_html_e( 'This will be shown together with the acceptance checkbox below the form', 'wp-maintenance-mode' ); ?></p>
									</td>
								</tr>
							</tbody>
						</table>

						<?php wpmm_form_hidden_fields( 'gdpr' ); ?>
						<input type="submit" value="<?php esc_attr_e( 'Save settings', 'wp-maintenance-mode' ); ?>" class="button button-primary" name="submit" />
						<input type="button" value="<?php esc_attr_e( 'Reset settings', 'wp-maintenance-mode' ); ?>" class="button button-secondary reset_settings" data-tab="gdpr" name="submit" />
					</form>
				</div> <?php } ?>
			</div>
		</div>

		<?php require_once 'sidebar.php'; } ?>
	</div>
</div>
