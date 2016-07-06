<div class="wrap">
    <h2 class="wpmm-title"><?php echo get_admin_page_title(); ?></h2>

    <?php if (!empty($_POST)) { ?>
        <div class="updated settings-error" id="setting-error-settings_updated"> 
            <p><strong><?php _e('Settings saved.', $this->plugin_slug); ?></strong></p>
        </div>
    <?php } ?>

    <div class="wpmm-wrapper">
        <div id="content" class="wrapper-cell">
            <div class="nav-tab-wrapper">
                <a class="nav-tab nav-tab-active" href="#general"><?php _e('General', $this->plugin_slug); ?></a>
                <a class="nav-tab" href="#design"><?php _e('Design', $this->plugin_slug); ?></a>
                <a class="nav-tab" href="#modules"><?php _e('Modules', $this->plugin_slug); ?></a>
            </div>

            <div class="tabs-content">
                <div id="tab-general" class="">
                    <form method="post">
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
                                            <?php
                                            foreach ($wp_roles->roles as $role => $details) {
                                                if ($role == 'administrator') {
                                                    continue;
                                                }
                                                ?>
                                                <option value="<?php echo esc_attr($role); ?>" <?php echo wpmm_multiselect((array) $this->plugin_settings['general']['backend_role'], $role); ?>><?php echo $details['name']; ?></option>
                                            <?php } ?>
                                        </select>
                                        <p class="description"><?php _e('Which user role is allowed to access the backend of this blog? Administrators will always have access.', $this->plugin_slug); ?></p>
                                    </td>
                                </tr>    
                                <tr valign="top">
                                    <th scope="row"><label for="options[general][frontend_role][]"><?php _e('Frontend Role', $this->plugin_slug); ?></label></th>
                                    <td>	
                                        <select name="options[general][frontend_role][]" multiple="multiple" class="chosen-select" data-placeholder="<?php _e('Select role(s)', $this->plugin_slug); ?>">
                                            <?php
                                            foreach ($wp_roles->roles as $role => $details) {
                                                if ($role == 'administrator') {
                                                    continue;
                                                }
                                                ?>
                                                <option value="<?php echo esc_attr($role); ?>" <?php echo wpmm_multiselect((array) $this->plugin_settings['general']['frontend_role'], $role); ?>><?php echo $details['name']; ?></option>
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
                                        <input type="text" value="<?php echo esc_attr(stripslashes($this->plugin_settings['general']['redirection'])); ?>" name="options[general][redirection]" />
                                        <p class="description"><?php _e('If you want to redirect a user (with no access to Dashboard/Backend) to a URL (different from WordPress Dashboard URL) after login, then define a URL (incl. http://)', $this->plugin_slug); ?></p>
                                    </td>
                                </tr>                                
                                <tr valign="top">
                                    <th scope="row"><label for="options[general][exclude]"><?php _e('Exclude', $this->plugin_slug); ?></label></th>
                                    <td>	
                                        <textarea rows="7" name="options[general][exclude]" style="width: 625px;"><?php
                                            if (!empty($this->plugin_settings['general']['exclude']) && is_array($this->plugin_settings['general']['exclude'])) {
                                                echo implode("\n", stripslashes_deep($this->plugin_settings['general']['exclude']));
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

                        <?php wp_nonce_field('tab-general'); ?>
                        <input type="hidden" value="general" name="tab" />
                        <input type="submit" value="<?php _e('Save settings', $this->plugin_slug); ?>" class="button button-primary" name="submit" />
                        <input type="button" value="<?php _e('Reset settings', $this->plugin_slug); ?>" class="button button-secondary reset_settings" data-tab="general" name="submit">
                    </form>
                </div>
                <div id="tab-design" class="hidden">
                    <form method="post">
                        <h3>&raquo; <?php _e('Content', $this->plugin_slug); ?></h3>

                        <table class="form-table">
                            <tbody>
                                <tr valign="top">
                                    <th scope="row"><label for="options[design][title]"><?php _e('Title (HTML tag)', $this->plugin_slug); ?></label></th>
                                    <td>	
                                        <input type="text" value="<?php echo esc_attr(stripslashes($this->plugin_settings['design']['title'])); ?>" name="options[design][title]" />
                                    </td>
                                </tr>     
                                <tr valign="top">
                                    <th scope="row"><label for="options[design][heading]"><?php _e('Heading', $this->plugin_slug); ?></label></th>
                                    <td>	
                                        <input type="text" value="<?php echo esc_attr(stripslashes($this->plugin_settings['design']['heading'])); ?>" name="options[design][heading]" />
                                        <input type="text" value="<?php echo esc_attr(stripslashes($this->plugin_settings['design']['heading_color'])); ?>" name="options[design][heading_color]" data-default-color="<?php echo esc_attr(stripslashes($this->plugin_settings['design']['heading_color'])); ?>" class="color_picker_trigger"/>
                                    </td>
                                </tr>      
                                <tr valign="top">
                                    <th scope="row"><label for="options[design][text]"><?php _e('Text', $this->plugin_slug); ?></label></th>
                                    <td>	
                                        <?php
                                        wp_editor(stripslashes($this->plugin_settings['design']['text']), 'options_design_text', array(
                                            'textarea_name' => 'options[design][text]',
                                            'textarea_rows' => 8,
                                            'editor_class' => 'large-text',
                                            'media_buttons' => false,
                                            'wpautop' => false,
                                            'default_editor' => 'tinymce',
                                            'teeny' => true
                                        ));
                                        ?>
                                        <br />
                                        <input type="text" value="<?php echo esc_attr(stripslashes($this->plugin_settings['design']['text_color'])); ?>" data-default-color="<?php echo esc_attr(stripslashes($this->plugin_settings['design']['text_color'])); ?>" name="options[design][text_color]" class="color_picker_trigger" />
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
                                        <input type="text" value="<?php echo $this->plugin_settings['design']['bg_color']; ?>" data-default-color="<?php echo $this->plugin_settings['design']['bg_color']; ?>" name="options[design][bg_color]" class="color_picker_trigger"/>
                                    </td>
                                </tr>   
                                <tr valign="top" class="design_bg_types <?php echo $this->plugin_settings['design']['bg_type'] != 'custom' ? 'hidden' : ''; ?>" id="show_custom">
                                    <th scope="row"><label for="options[design][bg_custom]"><?php _e('Upload background', $this->plugin_slug); ?></label></th>
                                    <td>	
                                        <input type="text" value="<?php echo esc_attr(stripslashes($this->plugin_settings['design']['bg_custom'])); ?>" name="options[design][bg_custom]" class="upload_image_url" />
                                        <input type="button" value="Upload" class="button" id="upload_image_trigger" />
                                        <p class="description"><?php _e('Backgrounds should have 1920x1280 px size.', $this->plugin_slug); ?></p>
                                    </td>
                                </tr>   
                                <tr valign="top" class="design_bg_types <?php echo $this->plugin_settings['design']['bg_type'] != 'predefined' ? 'hidden' : ''; ?>" id="show_predefined">
                                    <th scope="row">
                                        <label for="options[design][bg_predefined]"><?php _e('Choose background', $this->plugin_slug); ?></label>
                            <p class="description">
                                * <?php echo sprintf(__('source <a href="%s" target="_blank">Free Photos</a>', $this->plugin_slug), 'http://designmodo.com/free-photos/' . WPMM_AUTHOR_UTM); ?>
                            </p>
                            </th>
                            <td>	
                                <ul class="bg_list">
                                    <?php
                                    foreach (glob(WPMM_PATH . 'assets/images/backgrounds/*_thumb.jpg') as $filename) {
                                        $file_thumb = basename($filename);
                                        $file = str_replace('_thumb', '', $file_thumb);
                                        ?>
                                        <li class="<?php echo $this->plugin_settings['design']['bg_predefined'] == $file ? 'active' : ''; ?>">
                                            <label>
                                                <input type="radio" value="<?php echo esc_attr($file); ?>" name="options[design][bg_predefined]" <?php checked($this->plugin_settings['design']['bg_predefined'], $file); ?>>
                                                <img src="<?php echo WPMM_URL . 'assets/images/backgrounds/' . $file_thumb; ?>" width="200" height="150" />
                                            </label>
                                        </li>
                                        <?php
                                    }
                                    ?>
                                </ul>
                            </td>
                            </tr>                                  
                            </tbody>
                        </table>                        

                        <?php wp_nonce_field('tab-design'); ?>
                        <input type="hidden" value="design" name="tab" />
                        <input type="submit" value="<?php _e('Save settings', $this->plugin_slug); ?>" class="button button-primary" name="submit">
                        <input type="button" value="<?php _e('Reset settings', $this->plugin_slug); ?>" class="button button-secondary reset_settings" data-tab="design" name="submit">
                    </form>
                </div>
                <div id="tab-modules" class="hidden">
                    <form method="post">
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
                                        <input type="text" value="<?php echo esc_attr(stripslashes($this->plugin_settings['modules']['countdown_start'])); ?>" name="options[modules][countdown_start]" class="countdown_start" />
                                    </td>
                                </tr>                                  
                                <tr valign="top">
                                    <th scope="row"><label for="options[modules][countdown_details]"><?php _e('Countdown (remaining time)', $this->plugin_slug); ?></label></th>
                                    <td class="countdown_details">	
                                        <input type="text" value="<?php echo esc_attr(stripslashes($this->plugin_settings['modules']['countdown_details']['days'])); ?>" name="options[modules][countdown_details][days]" /> <?php _e('Days', $this->plugin_slug); ?>
                                        <input type="text" value="<?php echo esc_attr(stripslashes($this->plugin_settings['modules']['countdown_details']['hours'])); ?>" name="options[modules][countdown_details][hours]" class="margin_left"/> <?php _e('Hours', $this->plugin_slug); ?>
                                        <input type="text" value="<?php echo esc_attr(stripslashes($this->plugin_settings['modules']['countdown_details']['minutes'])); ?>" name="options[modules][countdown_details][minutes]" class="margin_left" /> <?php _e('Minutes', $this->plugin_slug); ?>
                                    </td>
                                </tr>     
                                <tr valign="top">
                                    <th scope="row"><label for="options[modules][countdown_color]"><?php _e('Color', $this->plugin_slug); ?></label></th>
                                    <td>
                                        <input type="text" value="<?php echo esc_attr(stripslashes($this->plugin_settings['modules']['countdown_color'])); ?>" name="options[modules][countdown_color]" data-default-color="<?php echo esc_attr(stripslashes($this->plugin_settings['modules']['countdown_color'])); ?>" class="color_picker_trigger"/>
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
                                    <td>	
                                        <input type="text" value="<?php echo esc_attr(stripslashes($this->plugin_settings['modules']['subscribe_text'])); ?>" name="options[modules][subscribe_text]" />
                                        <input type="text" value="<?php echo esc_attr(stripslashes($this->plugin_settings['modules']['subscribe_text_color'])); ?>" name="options[modules][subscribe_text_color]" data-default-color="<?php echo esc_attr(stripslashes($this->plugin_settings['modules']['subscribe_text_color'])); ?>" class="color_picker_trigger"/>
                                    </td>
                                </tr> 
                                <tr valign="top">
                                    <th scope="row"><label for="options[modules][stats]"><?php _e('Stats', $this->plugin_slug); ?></label></th>
                                    <td id="subscribers_wrap">	
                                        <?php
                                        $subscribers_no = wpmm_count_where('wpmm_subscribers', 'id_subscriber');
                                        echo sprintf(__('You have %d subscriber(s)', $this->plugin_slug), $subscribers_no);

                                        if ($subscribers_no > 0) {
                                            ?>
                                            <br />
                                            <a class="button button-primary" id="subscribers-export" href="javascript:void(0);"><?php _e('Export as CSV', $this->plugin_slug); ?></a>
                                            <a class="button button-secondary" id="subscribers-empty-list" href="javascript:void(0);"><?php _e('Empty subscribers list', $this->plugin_slug); ?></a>
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
                                        <input type="text" value="<?php echo esc_attr(stripslashes($this->plugin_settings['modules']['social_github'])); ?>" name="options[modules][social_github]" />
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><label for="options[modules][social_dribbble]">Dribbble</label></th>
                                    <td>	
                                        <input type="text" value="<?php echo esc_attr(stripslashes($this->plugin_settings['modules']['social_dribbble'])); ?>" name="options[modules][social_dribbble]" />
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><label for="options[modules][social_twitter]">Twitter</label></th>
                                    <td>	
                                        <input type="text" value="<?php echo esc_attr(stripslashes($this->plugin_settings['modules']['social_twitter'])); ?>" name="options[modules][social_twitter]" />
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><label for="options[modules][social_facebook]">Facebook</label></th>
                                    <td>	
                                        <input type="text" value="<?php echo esc_attr(stripslashes($this->plugin_settings['modules']['social_facebook'])); ?>" name="options[modules][social_facebook]" />
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><label for="options[modules][social_pinterest]">Pinterest</label></th>
                                    <td>	
                                        <input type="text" value="<?php echo esc_attr(stripslashes($this->plugin_settings['modules']['social_pinterest'])); ?>" name="options[modules][social_pinterest]" />
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><label for="options[modules][social_google+]">Google+</label></th>
                                    <td>	
                                        <input type="text" value="<?php echo esc_attr(stripslashes($this->plugin_settings['modules']['social_google+'])); ?>" name="options[modules][social_google+]" />
                                    </td>
                                </tr>
                                <tr valign="top">
                                    <th scope="row"><label for="options[modules][social_linkedin]">Linkedin</label></th>
                                    <td>	
                                        <input type="text" value="<?php echo esc_attr(stripslashes($this->plugin_settings['modules']['social_linkedin'])); ?>" name="options[modules][social_linkedin]" />
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
                                        <input type="text" value="<?php echo esc_attr(stripslashes($this->plugin_settings['modules']['contact_email'])); ?>" name="options[modules][contact_email]" />
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
                                    <th scope="row"><label for="options[modules][ga_code]"><?php _e('Tracking code', $this->plugin_slug); ?></label></th>
                                    <td>	
                                        <input type="text" value="<?php echo esc_attr(stripslashes($this->plugin_settings['modules']['ga_code'])); ?>" name="options[modules][ga_code]" />
                                        <p class="description"><?php _e('Allowed formats: UA-XXXXXXXX, UA-XXXXXXXX-XXXX. Eg: UA-12345678-1 is valid', $this->plugin_slug); ?></p>
                                    </td>
                                </tr>                                
                            </tbody>
                        </table>                         

                        <?php wp_nonce_field('tab-modules'); ?>
                        <input type="hidden" value="modules" name="tab" />
                        <input type="submit" value="<?php _e('Save settings', $this->plugin_slug); ?>" class="button button-primary" name="submit">
                        <input type="button" value="<?php _e('Reset settings', $this->plugin_slug); ?>" class="button button-secondary reset_settings" data-tab="modules" name="submit">
                    </form>
                </div>
            </div>
        </div>

        <?php include_once('sidebar.php'); ?>
    </div>
</div>