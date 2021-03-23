<?php 
/**
 * Settings
 * 
 * @version 2.4.0
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap">
    <h2 class="wpmm-title"><?php echo get_admin_page_title(); ?></h2>

    <div class="wpmm-wrapper">
        <div id="content" class="wrapper-cell">
            <div class="nav-tab-wrapper">
                <a class="nav-tab nav-tab-active" href="#general"><?php _e('General', $this->plugin_slug); ?></a>
                <a class="nav-tab" href="#design"><?php _e('Design', $this->plugin_slug); ?></a>
                <a class="nav-tab" href="#modules"><?php _e('Modules', $this->plugin_slug); ?></a>
                <a class="nav-tab" href="#bot"><?php _e('Manage Bot', $this->plugin_slug); ?></a>
                <a class="nav-tab" href="#gdpr"><?php _e('GDPR', $this->plugin_slug); ?></a>
            </div>

            <div class="tabs-content">
                <div id="tab-general" class="">
                    <form action="<?php echo admin_url('admin-post.php'); ?>" method="post" >
                        <table class="form-table">
                            <tbody>
                                <tr valign="top">
                                    <th scope="row"><label for="options[general][status]"><?php _e('Status', $this->plugin_slug); ?></label></th>
                                    <td>
                                        <label><input type="radio" value="1" name="options[general][status]" <?php checked($this->plugin_settings['general']['status'], 1); ?>> <?php _e('Activated', $this->plugin_slug); ?></label> <br />
                                        <label><input type="radio" value="0" name="options[general][status]" <?php checked($this->plugin_settings['general']['status'], 0); ?>> <?php _e('Deactivated', $this->plugin_slug); ?></label>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><label for="options[general][bypass_bots]"><?php _e('Bypass for Search Bots', $this->plugin_slug); ?></label></th>
                                    <td>
                                        <select name="options[general][bypass_bots]">
                                            <option value="1" <?php selected($this->plugin_settings['general']['bypass_bots'], 1); ?>><?php _e('Yes', $this->plugin_slug); ?></option>
                                            <option value="0" <?php selected($this->plugin_settings['general']['bypass_bots'], 0); ?>><?php _e('No', $this->plugin_slug); ?></option>
                                        </select>
                                        <p class="description"><?php _e('Allow Search Bots to bypass maintenance mode?', $this->plugin_slug); ?></p>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><label for="options[general][backend_role][]"><?php _e('Backend Role', $this->plugin_slug); ?></label></th>
                                    <td>
                                        <select name="options[general][backend_role][]" multiple="multiple" class="chosen-select" data-placeholder="<?php _e('Select role(s)', $this->plugin_slug); ?>">
                                            <?php foreach (wpmm_get_user_roles() as $role => $role_name) { ?>
                                                <option value="<?php echo esc_attr($role); ?>" <?php echo wpmm_multiselect((array) $this->plugin_settings['general']['backend_role'], $role); ?>><?php echo esc_html($role_name); ?></option>
                                            <?php } ?>
                                        </select>
                                        <p class="description"><?php _e('Which user role is allowed to access the backend of this blog? Administrators will always have access.', $this->plugin_slug); ?></p>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><label for="options[general][frontend_role][]"><?php _e('Frontend Role', $this->plugin_slug); ?></label></th>
                                    <td>
                                        <select name="options[general][frontend_role][]" multiple="multiple" class="chosen-select" data-placeholder="<?php _e('Select role(s)', $this->plugin_slug); ?>">
                                            <?php foreach (wpmm_get_user_roles() as $role => $role_name) { ?>
                                                <option value="<?php echo esc_attr($role); ?>" <?php echo wpmm_multiselect((array) $this->plugin_settings['general']['frontend_role'], $role); ?>><?php echo esc_html($role_name); ?></option>
                                            <?php } ?>
                                        </select>
                                        <p class="description"><?php _e('Which user role is allowed to access the frontend of this blog? Administrators will always have access.', $this->plugin_slug); ?></p>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><label for="options[general][meta_robots]"><?php _e('Robots Meta Tag', $this->plugin_slug); ?></label></th>
                                    <td>
                                        <select name="options[general][meta_robots]">
                                            <option value="1" <?php selected($this->plugin_settings['general']['meta_robots'], 1); ?>>noindex, nofollow</option>
                                            <option value="0" <?php selected($this->plugin_settings['general']['meta_robots'], 0); ?>>index, follow</option>
                                        </select>
                                        <p class="description"><?php _e('The robots meta tag lets you use a granular, page-specific approach to control how an individual page should be indexed and served to users in search results.', $this->plugin_slug); ?></p>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><label for="options[general][redirection]"><?php _e('Redirection', $this->plugin_slug); ?></label></th>
                                    <td>
                                        <input type="text" value="<?php echo esc_attr($this->plugin_settings['general']['redirection']); ?>" name="options[general][redirection]" />
                                        <p class="description"><?php _e('If you want to redirect a user (with no access to Dashboard/Backend) to a URL (different from WordPress Dashboard URL) after login, then define a URL (incl. http://)', $this->plugin_slug); ?></p>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><label for="options[general][exclude]"><?php _e('Exclude', $this->plugin_slug); ?></label></th>
                                    <td>
                                        <textarea rows="7" name="options[general][exclude]" style="width: 625px;"><?php
                                            if (!empty($this->plugin_settings['general']['exclude']) && is_array($this->plugin_settings['general']['exclude'])) {
                                                echo implode("\n", $this->plugin_settings['general']['exclude']);
                                            }
                                            ?></textarea>
                                        <p class="description"><?php _e('Exclude feed, pages, archives or IPs from maintenance mode. Add one slug / IP per line!', $this->plugin_slug); ?></p>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><label for="options[general][notice]"><?php _e('Notice', $this->plugin_slug); ?></label></th>
                                    <td>
                                        <select name="options[general][notice]">
                                            <option value="1" <?php selected($this->plugin_settings['general']['notice'], 1); ?>><?php _e('Yes', $this->plugin_slug); ?></option>
                                            <option value="0" <?php selected($this->plugin_settings['general']['notice'], 0); ?>><?php _e('No', $this->plugin_slug); ?></option>
                                        </select>
                                        <p class="description"><?php _e('Do you want to see notices when maintenance mode is activated?', $this->plugin_slug); ?></p>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><label for="options[general][admin_link]"><?php _e('Dashboard link', $this->plugin_slug); ?></label></th>
                                    <td>
                                        <select name="options[general][admin_link]">
                                            <option value="1" <?php selected($this->plugin_settings['general']['admin_link'], 1); ?>><?php _e('Yes', $this->plugin_slug); ?></option>
                                            <option value="0" <?php selected($this->plugin_settings['general']['admin_link'], 0); ?>><?php _e('No', $this->plugin_slug); ?></option>
                                        </select>
                                        <p class="description"><?php _e('Do you want to add a link to the dashboard on your maintenance mode page?', $this->plugin_slug); ?></p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <?php wpmm_form_hidden_fields('general'); ?>
                        <input type="submit" value="<?php _e('Save settings', $this->plugin_slug); ?>" class="button button-primary" name="submit" />
                        <input type="button" value="<?php _e('Reset settings', $this->plugin_slug); ?>" class="button button-secondary reset_settings" data-tab="general" name="submit">
                    </form>
                </div>
                <div id="tab-design" class="hidden">
                    <form action="<?php echo admin_url('admin-post.php'); ?>" method="post">
                        <h3>&raquo; <?php _e('Content', $this->plugin_slug); ?></h3>

                        <table class="form-table">
                            <tbody>
                                <tr valign="top">
                                    <th scope="row"><label for="options[design][title]"><?php _e('Title (HTML tag)', $this->plugin_slug); ?></label></th>
                                    <td>
                                        <input type="text" value="<?php echo esc_attr($this->plugin_settings['design']['title']); ?>" name="options[design][title]" />
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><label for="options[design][heading]"><?php _e('Heading', $this->plugin_slug); ?></label></th>
                                    <td class="has-inline-color-picker">
                                        <input type="text" value="<?php echo esc_attr($this->plugin_settings['design']['heading']); ?>" name="options[design][heading]" />
                                        <input type="text" value="<?php echo esc_attr($this->plugin_settings['design']['heading_color']); ?>" name="options[design][heading_color]" class="color_picker_trigger"/>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><label for="options[design][text]"><?php _e('Text', $this->plugin_slug); ?></label></th>
                                    <td>
                                        <?php
                                        wp_editor(wp_kses_post($this->plugin_settings['design']['text']), 'options_design_text', array(
                                            'textarea_name' => 'options[design][text]',
                                            'textarea_rows' => 8,
                                            'editor_class' => 'large-text',
                                            'media_buttons' => true,
                                            'wpautop' => false,
                                            'default_editor' => 'tinymce',
                                            'teeny' => true
                                        ));
                                        ?>
                                        <div class="shortcodes-list-wrapper">
                                            <?php 
                                                $hide_shortcodes_text = __('Hide available shortcodes', $this->plugin_slug);
                                                $show_shortcodes_text = __('See available shortcodes', $this->plugin_slug);
                                            ?>
                                            <a href="javascript:void(0);" class="button button-small toggle-shortcodes-list" data-hide="<?php echo esc_attr($hide_shortcodes_text); ?>" data-show="<?php echo esc_attr($show_shortcodes_text); ?>" ><?php echo esc_html($show_shortcodes_text); ?></a>
                                            
                                            <ul class="shortcodes-list">
                                                <li><?php _e(sprintf('%s - display a login form', '[loginform]'), $this->plugin_slug); ?></li>
                                                <li><?php _e(sprintf('%s - responsive video embed. Compatible with %s. Example: %s', '[embed]', 'YouTube, Vimeo, DailyMotion', '<span>[embed]https://www.youtube.com/watch?v=dQw4w9WgXcQ[/embed]</span>'), $this->plugin_slug); ?></li>
                                            </ul>
                                        </div>
                                        <p class="description"><?php _e('This text will not be shown when the bot feature is enabled.', $this->plugin_slug); ?></p>
                                        <br />
                                        <input type="text" value="<?php echo esc_attr($this->plugin_settings['design']['text_color']); ?>" name="options[design][text_color]" class="color_picker_trigger" />
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><label for="options[design][footer_links_color]"><?php _e('Footer links', $this->plugin_slug); ?></label></th>
                                    <td>
                                        <input type="text" value="<?php echo esc_attr($this->plugin_settings['design']['footer_links_color']); ?>" name="options[design][footer_links_color]" class="color_picker_trigger"/>
                                        <p class="description"><?php _e('"Dashboard" and "Privacy Policy" links.', $this->plugin_slug); ?></p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <h3>&raquo; <?php _e('Background', $this->plugin_slug); ?></h3>

                        <table class="form-table">
                            <tbody>
                                <tr valign="top">
                                    <th scope="row"><label for="options[design][bg_type]"><?php _e('Choose type', $this->plugin_slug); ?></label></th>
                                    <td>
                                        <select name="options[design][bg_type]" id="design_bg_type">
                                            <option value="color" <?php selected($this->plugin_settings['design']['bg_type'], 'color'); ?>><?php _e('Custom color', $this->plugin_slug); ?></option>
                                            <option value="custom" <?php selected($this->plugin_settings['design']['bg_type'], 'custom'); ?>><?php _e('Uploaded background', $this->plugin_slug); ?></option>
                                            <option value="predefined" <?php selected($this->plugin_settings['design']['bg_type'], 'predefined'); ?>><?php _e('Predefined background', $this->plugin_slug); ?></option>
                                        </select>
                                    </td>
                                </tr>
                                <tr valign="top" class="design_bg_types <?php echo $this->plugin_settings['design']['bg_type'] != 'color' ? 'hidden' : ''; ?>" id="show_color">
                                    <th scope="row"><label for="options[design][bg_color]"><?php _e('Choose color', $this->plugin_slug); ?></label></th>
                                    <td>
                                        <input type="text" value="<?php echo $this->plugin_settings['design']['bg_color']; ?>" name="options[design][bg_color]" class="color_picker_trigger"/>
                                    </td>
                                </tr>
                                <tr valign="top" class="design_bg_types <?php echo $this->plugin_settings['design']['bg_type'] != 'custom' ? 'hidden' : ''; ?>" id="show_custom">
                                    <th scope="row"><label for="options[design][bg_custom]"><?php _e('Upload background', $this->plugin_slug); ?></label></th>
                                    <td>
                                        <input type="text" value="<?php echo esc_attr($this->plugin_settings['design']['bg_custom']); ?>" name="options[design][bg_custom]" class="background_url" />
                                        <input
                                               type="button" 
                                               value="<?php echo esc_attr_x('Upload', 'upload background button', $this->plugin_slug); ?>" 
                                               class="button image_uploader_trigger" 
                                               data-name="background" 
                                               data-title="<?php esc_attr_e('Upload Background', $this->plugin_slug); ?>" 
                                               data-button-text="<?php esc_attr_e('Choose Background', $this->plugin_slug); ?>"
                                               data-to-selector=".background_url"
                                        />
                                        <p class="description"><?php _e('Backgrounds should have 1920x1280 px size.', $this->plugin_slug); ?></p>
                                    </td>
                                </tr>
                                <tr valign="top" class="design_bg_types <?php echo $this->plugin_settings['design']['bg_type'] != 'predefined' ? 'hidden' : ''; ?>" id="show_predefined">
                                    <th scope="row">
                                        <label for="options[design][bg_predefined]"><?php _e('Choose background', $this->plugin_slug); ?></label>
                            <p class="description">
                                * <?php echo sprintf(__('source <a href="%s" target="_blank">Free Photos</a>', $this->plugin_slug), esc_url(wpmm_get_utmized_url('http://designmodo.com/free-photos/', array('source' => 'settings')))); ?>
                            </p>
                            </th>
                            <td>
                                <ul class="bg_list">
                                    <?php foreach (wpmm_get_backgrounds() as $filename) { ?>
                                        <li class="<?php echo $this->plugin_settings['design']['bg_predefined'] == $filename['big'] ? 'active' : ''; ?>">
                                            <label>
                                                <input type="radio" value="<?php echo esc_attr($filename['big']); ?>" name="options[design][bg_predefined]" <?php checked($this->plugin_settings['design']['bg_predefined'], $filename['big']); ?>>
                                                <img src="<?php echo esc_url(WPMM_URL . 'assets/images/backgrounds/' . $filename['small']); ?>" width="200" height="150" />
                                            </label>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </td>
                            </tr>
                            </tbody>
                        </table>

                        <h3>&raquo; <?php _e('Other', $this->plugin_slug); ?></h3>
                        
                        <table class="form-table">
                            <tbody>
                                <tr valign="top">
                                    <th scope="row"><label for="options[design][other_custom_css]"><?php _e('Custom CSS', $this->plugin_slug); ?></label></th>
                                    <td>
                                        <textarea rows="10" name="options[design][other_custom_css]" style="width:625px;" id="other_custom_css"><?php echo wp_strip_all_tags($this->plugin_settings['design']['other_custom_css']); ?></textarea>
                                        <p class="description"><?php esc_html_e('Do not add <style> tags.', $this->plugin_slug); ?></p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <?php wpmm_form_hidden_fields('design'); ?>
                        <input type="submit" value="<?php _e('Save settings', $this->plugin_slug); ?>" class="button button-primary" name="submit">
                        <input type="button" value="<?php _e('Reset settings', $this->plugin_slug); ?>" class="button button-secondary reset_settings" data-tab="design" name="submit">
                    </form>
                </div>
                <div id="tab-modules" class="hidden">
                    <form action="<?php echo admin_url('admin-post.php'); ?>" method="post">
                        <h3>&raquo; <?php _e('Countdown', $this->plugin_slug); ?></h3>

                        <table class="form-table">
                            <tbody>
                                <tr valign="top">
                                    <th scope="row"><label for="options[modules][countdown_status]"><?php _e('Show countdown?', $this->plugin_slug); ?></label></th>
                                    <td>
                                        <select name="options[modules][countdown_status]">
                                            <option value="1" <?php selected($this->plugin_settings['modules']['countdown_status'], 1); ?>><?php _e('Yes', $this->plugin_slug); ?></option>
                                            <option value="0" <?php selected($this->plugin_settings['modules']['countdown_status'], 0); ?>><?php _e('No', $this->plugin_slug); ?></option>
                                        </select>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><label for="options[modules][countdown_start]"><?php _e('Start date', $this->plugin_slug); ?></label></th>
                                    <td>
                                        <input type="text" value="<?php echo esc_attr($this->plugin_settings['modules']['countdown_start']); ?>" name="options[modules][countdown_start]" class="countdown_start" />
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><label for="options[modules][countdown_details]"><?php _e('Countdown (remaining time)', $this->plugin_slug); ?></label></th>
                                    <td class="countdown_details">
                                        <input type="text" value="<?php echo esc_attr($this->plugin_settings['modules']['countdown_details']['days']); ?>" name="options[modules][countdown_details][days]" /> <?php _e('Days', $this->plugin_slug); ?>
                                        <input type="text" value="<?php echo esc_attr($this->plugin_settings['modules']['countdown_details']['hours']); ?>" name="options[modules][countdown_details][hours]" class="margin_left"/> <?php _e('Hours', $this->plugin_slug); ?>
                                        <input type="text" value="<?php echo esc_attr($this->plugin_settings['modules']['countdown_details']['minutes']); ?>" name="options[modules][countdown_details][minutes]" class="margin_left" /> <?php _e('Minutes', $this->plugin_slug); ?>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><label for="options[modules][countdown_color]"><?php _e('Color', $this->plugin_slug); ?></label></th>
                                    <td>
                                        <input type="text" value="<?php echo esc_attr($this->plugin_settings['modules']['countdown_color']); ?>" name="options[modules][countdown_color]" class="color_picker_trigger"/>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <h3>&raquo; <?php _e('Subscribe', $this->plugin_slug); ?></h3>

                        <table class="form-table">
                            <tbody>
                                <tr valign="top">
                                    <th scope="row"><label for="options[modules][subscribe_status]"><?php _e('Show subscribe?', $this->plugin_slug); ?></label></th>
                                    <td>
                                        <select name="options[modules][subscribe_status]">
                                            <option value="1" <?php selected($this->plugin_settings['modules']['subscribe_status'], 1); ?>><?php _e('Yes', $this->plugin_slug); ?></option>
                                            <option value="0" <?php selected($this->plugin_settings['modules']['subscribe_status'], 0); ?>><?php _e('No', $this->plugin_slug); ?></option>
                                        </select>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><label for="options[modules][subscribe_text]"><?php _e('Text', $this->plugin_slug); ?></label></th>
                                    <td class="has-inline-color-picker">
                                        <input type="text" value="<?php echo esc_attr($this->plugin_settings['modules']['subscribe_text']); ?>" name="options[modules][subscribe_text]" />
                                        <input type="text" value="<?php echo esc_attr($this->plugin_settings['modules']['subscribe_text_color']); ?>" name="options[modules][subscribe_text_color]" class="color_picker_trigger"/>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><label for="options[modules][stats]"><?php _e('Stats', $this->plugin_slug); ?></label></th>
                                    <td id="subscribers_wrap">
                                        <?php
                                        $subscribers_no = wpmm_count_where('wpmm_subscribers', 'id_subscriber');
					echo sprintf(_nx('You have %d subscriber', 'You have %s subscribers', $subscribers_no, 'settings page', $this->plugin_slug), $subscribers_no);

                                        if (current_user_can(wpmm_get_capability('subscribers')) && $subscribers_no > 0) {
                                            ?>
                                            <div class="buttons">
                                                <a class="button button-primary" id="subscribers-export" href="javascript:void(0);"><?php _e('Export as CSV', $this->plugin_slug); ?></a>
                                                <a class="button button-secondary" id="subscribers-empty-list" href="javascript:void(0);"><?php _e('Empty subscribers list', $this->plugin_slug); ?></a>
                                            </div>
                                        <?php } ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <h3>&raquo; <?php _e('Social Networks', $this->plugin_slug); ?></h3>

                        <table class="form-table">
                            <tbody>
                                <tr valign="top">
                                    <th scope="row"><label for="options[modules][social_status]"><?php _e('Show social networks?', $this->plugin_slug); ?></label></th>
                                    <td>
                                        <select name="options[modules][social_status]">
                                            <option value="1" <?php selected($this->plugin_settings['modules']['social_status'], 1); ?>><?php _e('Yes', $this->plugin_slug); ?></option>
                                            <option value="0" <?php selected($this->plugin_settings['modules']['social_status'], 0); ?>><?php _e('No', $this->plugin_slug); ?></option>
                                        </select>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><label for="options[modules][social_target]"><?php _e('Links target?', $this->plugin_slug); ?></label></th>
                                    <td>
                                        <select name="options[modules][social_target]">
                                            <option value="1" <?php selected($this->plugin_settings['modules']['social_target'], 1); ?>><?php _e('New page', $this->plugin_slug); ?></option>
                                            <option value="0" <?php selected($this->plugin_settings['modules']['social_target'], 0); ?>><?php _e('Same page', $this->plugin_slug); ?></option>
                                        </select>
                                        <p class="description"><?php _e('Choose how the links will open.', $this->plugin_slug); ?></p>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><label for="options[modules][social_github]">Github</label></th>
                                    <td>
                                        <input type="text" value="<?php echo esc_attr($this->plugin_settings['modules']['social_github']); ?>" name="options[modules][social_github]" />
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><label for="options[modules][social_dribbble]">Dribbble</label></th>
                                    <td>
                                        <input type="text" value="<?php echo esc_attr($this->plugin_settings['modules']['social_dribbble']); ?>" name="options[modules][social_dribbble]" />
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><label for="options[modules][social_twitter]">Twitter</label></th>
                                    <td>
                                        <input type="text" value="<?php echo esc_attr($this->plugin_settings['modules']['social_twitter']); ?>" name="options[modules][social_twitter]" />
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><label for="options[modules][social_facebook]">Facebook</label></th>
                                    <td>
                                        <input type="text" value="<?php echo esc_attr($this->plugin_settings['modules']['social_facebook']); ?>" name="options[modules][social_facebook]" />
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><label for="options[modules][social_instagram]">Instagram</label></th>
                                    <td>    
                                        <input type="text" value="<?php echo esc_attr($this->plugin_settings['modules']['social_instagram']); ?>" name="options[modules][social_instagram]" />
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><label for="options[modules][social_pinterest]">Pinterest</label></th>
                                    <td>
                                        <input type="text" value="<?php echo esc_attr($this->plugin_settings['modules']['social_pinterest']); ?>" name="options[modules][social_pinterest]" />
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><label for="options[modules][social_google+]">Google+</label></th>
                                    <td>
                                        <input type="text" value="<?php echo esc_attr($this->plugin_settings['modules']['social_google+']); ?>" name="options[modules][social_google+]" />
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><label for="options[modules][social_linkedin]">Linkedin</label></th>
                                    <td>
                                        <input type="text" value="<?php echo esc_attr($this->plugin_settings['modules']['social_linkedin']); ?>" name="options[modules][social_linkedin]" />
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <h3>&raquo; <?php _e('Contact', $this->plugin_slug); ?></h3>

                        <table class="form-table">
                            <tbody>
                                <tr valign="top">
                                    <th scope="row"><label for="options[modules][contact_status]"><?php _e('Show contact?', $this->plugin_slug); ?></label></th>
                                    <td>
                                        <select name="options[modules][contact_status]">
                                            <option value="1" <?php selected($this->plugin_settings['modules']['contact_status'], 1); ?>><?php _e('Yes', $this->plugin_slug); ?></option>
                                            <option value="0" <?php selected($this->plugin_settings['modules']['contact_status'], 0); ?>><?php _e('No', $this->plugin_slug); ?></option>
                                        </select>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><label for="options[modules][contact_email]"><?php _e('Email address', $this->plugin_slug); ?></label></th>
                                    <td>
                                        <input type="text" value="<?php echo esc_attr($this->plugin_settings['modules']['contact_email']); ?>" name="options[modules][contact_email]" />
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><label for="options[modules][contact_effects]"><?php _e('Effects', $this->plugin_slug); ?></label></th>
                                    <td>
                                        <select name="options[modules][contact_effects]">
                                            <option value="move_top|move_bottom" <?php selected($this->plugin_settings['modules']['contact_effects'], 'move_top|move_bottom'); ?>><?php _e('Move top - Move bottom', $this->plugin_slug); ?></option>
                                            <option value="zoom|zoomed" <?php selected($this->plugin_settings['modules']['contact_effects'], 'zoom|zoomed'); ?>><?php _e('Zoom - Zoomed', $this->plugin_slug); ?></option>
                                            <option value="fold|unfold" <?php selected($this->plugin_settings['modules']['contact_effects'], 'fold|unfold'); ?>><?php _e('Fold - Unfold', $this->plugin_slug); ?></option>
                                        </select>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <h3>&raquo; <?php _e('Google Analytics', $this->plugin_slug); ?></h3>

                        <table class="form-table">
                            <tbody>
                                <tr valign="top">
                                    <th scope="row"><label for="options[modules][ga_status]"><?php _e('Use Google Analytics?', $this->plugin_slug); ?></label></th>
                                    <td>
                                        <select name="options[modules][ga_status]">
                                            <option value="1" <?php selected($this->plugin_settings['modules']['ga_status'], 1); ?>><?php _e('Yes', $this->plugin_slug); ?></option>
                                            <option value="0" <?php selected($this->plugin_settings['modules']['ga_status'], 0); ?>><?php _e('No', $this->plugin_slug); ?></option>
                                        </select>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><label for="options[modules][ga_anonymize_ip]"><?php _e('Enable IP anonymization?', $this->plugin_slug); ?></label></th>
                                    <td>
                                        <select name="options[modules][ga_anonymize_ip]">
                                            <option value="1" <?php selected($this->plugin_settings['modules']['ga_anonymize_ip'], 1); ?>><?php _e('Yes', $this->plugin_slug); ?></option>
                                            <option value="0" <?php selected($this->plugin_settings['modules']['ga_anonymize_ip'], 0); ?>><?php _e('No', $this->plugin_slug); ?></option>
                                        </select>
					<p class="description"><?php _e(sprintf('Read about IP anonymization on <a href="%s" rel="noreferrer" target="_blank">Google Analytics</a> docs. It is always enabled on Google Analytics 4.', 'https://support.google.com/analytics/answer/2763052'), $this->plugin_slug); ?></p>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><label for="options[modules][ga_code]"><?php _e('Tracking code', $this->plugin_slug); ?></label></th>
                                    <td>
                                        <input type="text" value="<?php echo esc_attr($this->plugin_settings['modules']['ga_code']); ?>" name="options[modules][ga_code]" />
                                        <p class="description"><?php _e('Allowed formats: UA-XXXXXXXX, UA-XXXXXXXX-XXXX, G-XXXXXXXX. Eg: UA-12345678-1 is valid', $this->plugin_slug); ?></p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <?php wpmm_form_hidden_fields('modules'); ?>
                        <input type="submit" value="<?php _e('Save settings', $this->plugin_slug); ?>" class="button button-primary" name="submit">
                        <input type="button" value="<?php _e('Reset settings', $this->plugin_slug); ?>" class="button button-secondary reset_settings" data-tab="modules" name="submit">
                    </form>
                </div>
                <div id="tab-bot" class="hidden">
                    <form action="<?php echo admin_url('admin-post.php'); ?>" method="post">
                        <table class="form-table">
                            <tbody>
                                <tr valign="top">
                                    <td colspan="2">
                                        <h4><?php _e("Setup the conversation steps to capture more subscribers with this friendly way of asking email addresess.", $this->plugin_slug) ?></h4>
                                        <p><?php _e("You may also want to use these wildcards: {bot_name} and {visitor_name} to make the conversation even more realistic.", $this->plugin_slug) ?></p>
                                        <p><?php _e("It is also ok if you don't fill in all the conversation steps if you don't need to.", $this->plugin_slug) ?></p>
					<p><?php _e("If you want to see the list of subscribers, go to Modules &raquo; Subscribe &raquo; Export as CSV.", $this->plugin_slug); ?></p>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row">
                                        <label for="options[bot][status]"><?php _e('Status', $this->plugin_slug); ?></label>
                                    </th>
                                    <td>
                                        <label><input type="radio" value="1" name="options[bot][status]" <?php checked($this->plugin_settings['bot']['status'], 1); ?>> <?php _e('Activated', $this->plugin_slug); ?></label> <br />
                                        <label><input type="radio" value="0" name="options[bot][status]" <?php checked($this->plugin_settings['bot']['status'], 0); ?>> <?php _e('Deactivated', $this->plugin_slug); ?></label>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><label for="options[bot][name]"><?php _e('Bot Name', $this->plugin_slug); ?></label></th>
                                    <td>
                                        <input type="text" name="options[bot][name]" id="options[bot][name]" value="<?php echo esc_attr($this->plugin_settings['bot']['name']); ?>" />
                                        <p class="description"><?php _e("This name will appear when the bot is typing.", $this->plugin_slug); ?></p>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><label for="options[bot][avatar]"><?php _e('Upload avatar', $this->plugin_slug); ?></label></th>
                                    <td>
                                        <input type="text" value="<?php echo esc_attr($this->plugin_settings['bot']['avatar']); ?>" name="options[bot][avatar]" id="options[bot][avatar]" class="avatar_url" />
                                        <input
                                               type="button" 
                                               value="<?php echo esc_attr_x('Upload', 'upload avatar button', $this->plugin_slug); ?>" 
                                               class="button image_uploader_trigger" 
                                               data-name="avatar" 
                                               data-title="<?php esc_attr_e('Upload Avatar', $this->plugin_slug); ?>" 
                                               data-button-text="<?php esc_attr_e('Choose picture', $this->plugin_slug); ?>"
                                               data-to-selector=".avatar_url"
                                        />
                                        <p class="description"><?php _e('A 512 x 512 px will work just fine.', $this->plugin_slug); ?></p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <h3>&raquo; <?php _e('Customize Messages', $this->plugin_slug); ?></h3>

                        <table class="form-table">
                            <tbody>
                                <tr valign="top">
                                    <th scope="row"><label for="options[bot][messages][01]"><?php _e('Message 1', $this->plugin_slug); ?></label></th>
                                    <td>
                                        <textarea name="options[bot][messages][01]" id="options[bot][messages][01]" rows="2" style="width: 625px;"><?php
                                        esc_attr_e($this->plugin_settings['bot']['messages']['01']);
                                        ?></textarea>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><label for="options[bot][messages][02]"><?php _e('Message 2', $this->plugin_slug); ?></label></th>
                                    <td>
                                        <textarea name="options[bot][messages][02]" id="options[bot][messages][02]" rows="2" style="width: 625px;"><?php
                                        echo esc_attr($this->plugin_settings['bot']['messages']['02']);
                                        ?></textarea>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><label for="options[bot][messages][03]"><?php _e('Message 3', $this->plugin_slug); ?></label></th>
                                    <td>
                                        <textarea name="options[bot][messages][03]" id="options[bot][messages][03]" rows="2" style="width: 625px;"><?php
                                        esc_attr_e($this->plugin_settings['bot']['messages']['03']);
                                        ?></textarea>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><label for="options[bot][responses][01]"><?php _e('Response', $this->plugin_slug); ?></label></th>
                                    <td>
                                        <input type="text" name="options[bot][responses][01]" id="options[bot][responses][01]" value="<?php esc_attr_e($this->plugin_settings['bot']['responses']['01']); ?>" />
                                        <span class="bot-hint">Visitor's response will be here.</span>
                                        <p class="description"><?php _e("Edit the placeholder's text", $this->plugin_slug); ?></p>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><label for="options[bot][messages][04]"><?php _e('Message 4', $this->plugin_slug); ?></label></th>
                                    <td>
                                        <textarea name="options[bot][messages][04]" id="options[bot][messages][04]" rows="2" style="width: 625px;"><?php
                                        esc_attr_e($this->plugin_settings['bot']['messages']['04']);
                                        ?></textarea>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><label for="options[bot][messages][05]"><?php _e('Message 5', $this->plugin_slug); ?></label></th>
                                    <td>
                                        <textarea name="options[bot][messages][05]" id="options[bot][messages][05]" rows="2" style="width: 625px;"><?php
                                        esc_attr_e($this->plugin_settings['bot']['messages']['05']);
                                        ?></textarea>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><label for="options[bot][messages][06]"><?php _e('Message 6', $this->plugin_slug); ?></label></th>
                                    <td>
                                        <textarea name="options[bot][messages][06]" id="options[bot][messages][06]" rows="2" style="width: 625px;"><?php
                                        esc_attr_e($this->plugin_settings['bot']['messages']['06']);
                                        ?></textarea>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><label for="options[bot][messages][07]"><?php _e('Message 7', $this->plugin_slug); ?></label></th>
                                    <td>
                                        <textarea name="options[bot][messages][07]" id="options[bot][messages][07]" rows="2" style="width: 625px;"><?php
                                        esc_attr_e($this->plugin_settings['bot']['messages']['07']);
                                        ?></textarea>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><label for="options[bot][responses][02_1]"><?php _e('Response', $this->plugin_slug); ?></label></th>
                                    <td>
                                        <div class="bot-button">
                                            <input type="text" name="options[bot][responses][02_1]" id="options[bot][responses][02_1]" value="<?php esc_attr_e($this->plugin_settings['bot']['responses']['02_1']); ?>" />
                                            <p class="description"><?php _e("Edit button one", $this->plugin_slug); ?></p>
                                        </div>
                                        <div class="bot-button">
                                            <input type="text" name="options[bot][responses][02_2]" id="options[bot][responses][02_2]" value="<?php esc_attr_e($this->plugin_settings['bot']['responses']['02_2']); ?>" />
                                            <p class="description"><?php _e("Edit button two", $this->plugin_slug); ?></p>
                                        </div>
                                        <span class="bot-hint">Visitor's response will be here.</span>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><label for="options[bot][messages][08_1]"><?php _e('Message 8', $this->plugin_slug); ?><br><small><?php _e('(click on button one)', $this->plugin_slug) ?></small></label></th>
                                    <td>
                                        <textarea name="options[bot][messages][08_1]" id="options[bot][messages][08_1]" rows="2" style="width: 625px;"><?php
                                        esc_attr_e($this->plugin_settings['bot']['messages']['08_1']);
                                        ?></textarea>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><label for="options[bot][responses][03]"><?php _e('Response', $this->plugin_slug); ?></label></th>
                                    <td>
                                        <input type="text" name="options[bot][responses][03]" id="options[bot][responses][03]" value="<?php esc_attr_e($this->plugin_settings['bot']['responses']['03']); ?>" />
                                        <span class="bot-hint">Visitor's response will be here.</span>
                                        <p class="description"><?php _e("Edit the placeholder's text", $this->plugin_slug); ?></p>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><label for="options[bot][messages][08_2]"><?php _e('Message 8', $this->plugin_slug); ?><br><small><?php _e('(click on button two)', $this->plugin_slug) ?></small></label></th>
                                    <td>
                                        <textarea name="options[bot][messages][08_2]" id="options[bot][messages][08_2]" rows="2" style="width: 625px;"><?php
                                        esc_attr_e($this->plugin_settings['bot']['messages']['08_2']);
                                        ?></textarea>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><label for="options[bot][messages][09]"><?php _e('Message 9', $this->plugin_slug); ?><br><small><?php _e('(click on button one)', $this->plugin_slug) ?></small></label></th>
                                    <td>
                                        <textarea name="options[bot][messages][09]" id="options[bot][messages][09]" rows="2" style="width: 625px;"><?php
                                        esc_attr_e($this->plugin_settings['bot']['messages']['09']);
                                        ?></textarea>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><label for="options[bot][messages][10]"><?php _e('Message 10', $this->plugin_slug); ?><br><small><?php _e('(click on button one)', $this->plugin_slug) ?></small></label></th>
                                    <td>
                                        <textarea name="options[bot][messages][10]" id="options[bot][messages][10]" rows="2" style="width: 625px;"><?php
                                        esc_attr_e($this->plugin_settings['bot']['messages']['10']);
                                        ?></textarea>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <?php wpmm_form_hidden_fields('bot'); ?>
                        <input type="submit" value="<?php _e('Save settings', $this->plugin_slug); ?>" class="button button-primary" name="submit">
                        <input type="button" value="<?php _e('Reset settings', $this->plugin_slug); ?>" class="button button-secondary reset_settings" data-tab="bot" name="submit">
                    </form>
                </div>
                <div id="tab-gdpr" class="hidden">
                    <form action="<?php echo admin_url('admin-post.php'); ?>" method="post">
                        <table class="form-table">
                            <tbody>
                                <tr valign="top">
                                    <td colspan="2">
                                        <h4><?php _e("To make the plugin GDPR compliant, fill in the details and enable this section.", $this->plugin_slug) ?></h4>
                                        <p><?php _e("Here we added some generic texts that you may want to review, change or remove.", $this->plugin_slug) ?></p>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><label for="options[gdpr][status]"><?php _e('Status', $this->plugin_slug); ?></label></th>
                                    <td>
                                        <label><input type="radio" value="1" name="options[gdpr][status]" <?php checked($this->plugin_settings['gdpr']['status'], 1); ?>> <?php _e('Activated', $this->plugin_slug); ?></label> <br />
                                        <label><input type="radio" value="0" name="options[gdpr][status]" <?php checked($this->plugin_settings['gdpr']['status'], 0); ?>> <?php _e('Deactivated', $this->plugin_slug); ?></label>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row">
                                        <label for="options[gdpr][policy_page_label]"><?php _e('Link name', $this->plugin_slug); ?></label>
                                    </th>
                                    <td>
                                        <input type="text" value="<?php echo esc_attr($this->plugin_settings['gdpr']['policy_page_label']); ?>" name="options[gdpr][policy_page_label]" />
                                        <p class="description"><?php _e('Label the link that will be shown on frontend footer', $this->plugin_slug); ?></p>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row">
                                        <label for="options[gdpr][policy_page_link]"><?php _e('P. Policy page link', $this->plugin_slug); ?></label>
                                    </th>
                                    <td>
                                        <input type="text" value="<?php echo esc_attr($this->plugin_settings['gdpr']['policy_page_link']); ?>" name="options[gdpr][policy_page_link]" />
                                        <p class="description"><?php echo $this->get_policy_link_message(); ?></p>
                                        <p class="description">REMEMBER: In order to make the privacy policy page accessible you need to add it in General -> Exclude.</p>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><label for="options[gdpr][policy_page_target]"><?php _e('P. Policy link target', $this->plugin_slug); ?></label></th>
                                    <td>
                                        <select name="options[gdpr][policy_page_target]">
                                            <option value="1" <?php selected($this->plugin_settings['gdpr']['policy_page_target'], 1); ?>><?php _e('New page', $this->plugin_slug); ?></option>
                                            <option value="0" <?php selected($this->plugin_settings['gdpr']['policy_page_target'], 0); ?>><?php _e('Same page', $this->plugin_slug); ?></option>
                                        </select>
                                        <p class="description"><?php _e('Choose how the link will open.', $this->plugin_slug); ?></p>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row">
                                        <label for="options[gdpr][contact_form_tail]"><?php _e('Contact form \'tail\'', $this->plugin_slug); ?></label>
                                    </th>
                                    <td>
                                        <textarea name="options[gdpr][contact_form_tail]" rows="3" style="width: 600px"><?php echo esc_attr($this->plugin_settings['gdpr']['contact_form_tail']); ?></textarea>
                                        <p class="description"><?php _e('This will be shown together with the acceptance checkbox below the form', $this->plugin_slug); ?></p>
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row">
                                        <label for="options[gdpr][subscribe_form_tail]"><?php _e('Subscribe form \'tail\'', $this->plugin_slug); ?></label>
                                    </th>
                                    <td>
                                        <textarea name="options[gdpr][subscribe_form_tail]" rows="3" style="width: 600px"><?php echo esc_attr($this->plugin_settings['gdpr']['subscribe_form_tail']); ?></textarea>
                                        <p class="description"><?php _e('This will be shown together with the acceptance checkbox below the form', $this->plugin_slug); ?></p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <?php wpmm_form_hidden_fields('gdpr'); ?>
                        <input type="submit" value="<?php _e('Save settings', $this->plugin_slug); ?>" class="button button-primary" name="submit" />
                        <input type="button" value="<?php _e('Reset settings', $this->plugin_slug); ?>" class="button button-secondary reset_settings" data-tab="gdpr" name="submit">
                    </form>
                </div>
            </div>
        </div>

        <?php include_once('sidebar.php'); ?>
    </div>
</div>