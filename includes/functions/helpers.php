<?php

defined( 'ABSPATH' ) || exit;

/**
 * Get plugin info
 *
 * @since 2.0.0
 * @param string $plugin_slug
 * @return array
 */
function wpmm_plugin_info($plugin_slug) {
	add_filter('extra_plugin_headers', 'wpmm_add_extra_plugin_headers', 99, 1);

	$plugin_data = get_plugin_data(WPMM_PATH . $plugin_slug . '.php');
        
        remove_filter('extra_plugin_headers', 'wpmm_add_extra_plugin_headers', 99, 1);

	return $plugin_data;
}

/**
 * Count db records using where
 *
 * EDIT: PHP Notice:  wpdb::prepare was called <strong>incorrectly</strong>. The query argument of wpdb::prepare() must have a placeholder.
 *
 * @since 2.0.0
 * @global object $wpdb
 * @param string $table
 * @param string $field
 * @param array $where eg: array('id_subscriber = %d' => 12)
 */
function wpmm_count_where($table, $field = 'ID', $where = array()) {
	global $wpdb;

	$table = $wpdb->prefix . $table;
	$where_keys = array_keys($where);
	$where_values = array_values($where);

	if (!empty($where)) {
		$query = $wpdb->prepare("SELECT COUNT({$field}) FROM {$table} WHERE " . implode(' AND ', $where_keys), $where_values);
	} else {
		$query = "SELECT COUNT({$field}) FROM {$table}";
	}

	$count = $wpdb->get_var($query);

	return intval($count);
}

/**
 * Outputs the html selected attribute
 *
 * @since 2.0.4
 * @param array $values
 * @param string $current
 * @param bool $echo
 * @return string html attribute or empty string
 */
function wpmm_multiselect($values, $current) {
	foreach ($values as $k => $role) {
		$is_selected = __checked_selected_helper($role, $current, false, 'selected');
		if (!empty($is_selected)) {
			return $is_selected;
			break;
		}
	}
}

/**
 * Return the UTM'ized url
 * 
 * @since 2.3.0
 * @param string $url
 * @param array $utms
 * @return string
 */
function wpmm_get_utmized_url($url, $utms = array()) {
        $utms = wp_parse_args($utms, array(
                'source' => null,
                'medium' => 'wpmaintenance',
                'campaign' => null,
                'term' => null,
                'content' => null,
        ));

        foreach ($utms as $key => $value) {
                if (empty($value)) {
                    unset($utms[$key]);
                    continue;
                }

                $utms[$key] = sprintf('utm_%s=%s', $key, $value);
        }

        if (empty($utms)) {
                return $url;
        }

        return sprintf('%s/?%s', untrailingslashit($url), implode('&', $utms));
}

/**
 * Return banner url
 * 
 * @param string $filename
 * @return string
 */
function wpmm_get_banner_url($filename) {
	return sprintf('%s/assets/images/recommended/%s', untrailingslashit(WPMM_URL), $filename);
}

/**
 * Return list of banners
 *
 * @since 2.0.4
 * @return array
 */
function wpmm_get_banners() {
	return array(
		array(
			'title' => 'Blocksy',
			'link' => 'https://creativethemes.com/blocksy/',
			'image' => 'blocksy.jpg',
			'utm' => true
		),
		array(
			'title' => 'StrictThemes – WordPress Themes',
			'link' => 'https://themeforest.net/user/strictthemes/portfolio?utf8=%E2%9C%93&order_by=sales&ref=StrictThemes',
			'image' => 'strictthemes.png',
			'utm' => false
		),
		array(
			'title' => 'Postcards',
			'link' => 'https://designmodo.com/postcards/',
			'image' => 'postcards.jpg',
			'utm' => true
		),
		array(
			'title' => 'Static Pages',
			'link' => 'https://designmodo.com/static-pages/',
			'image' => 'static-pages.png',
			'utm' => true
		)
	);
}

/**
 * Get list of available backgrounds
 * 
 * @since 2.3.0
 * @return array
 */
function wpmm_get_backgrounds() {
        $backgrounds = array();

        foreach(glob(WPMM_PATH . 'assets/images/backgrounds/*_thumb.jpg') as $file) {
            $backgrounds[] = array(
                'big' => str_replace('_thumb', '', basename($file)),
                'small' => basename($file)
            );
        }

        return $backgrounds;
}

/**
 * Get list of user roles
 * 
 * @since 2.3.0
 * @global object $wp_roles
 * @return array
 */
function wpmm_get_user_roles() {
        global $wp_roles;

        $roles = array();

        foreach($wp_roles->roles as $role => $details) {
            if ($role === 'administrator') {
                continue;
            }
            
            $roles[$role] = $details['name'];
        }

        return $roles;
}

/**
 * Sanitize Google Analytics SiteID code
 *
 * Valid examples:
 * UA-..........
 * UA-..........-....
 * G-..........
 *
 * @since 2.0.7
 * @param string $string
 * @return string
 */
function wpmm_sanitize_ga_code($string) {
	preg_match('/(UA-\d{4,10}(-\d{1,4})?|G-\w+)/', $string, $matches);

	return isset($matches[0]) ? $matches[0] : '';
}

/**
 * Return allowed HTML tags for GDPR module textareas
 * 
 * @since 2.2.2
 * @return array
 */
function wpmm_gdpr_textarea_allowed_html() {
	$allowed_html = array(
		'a' => array(
			'href' => array(),
			'title' => array(),
			'class' => array(),
			'rel' => array(),
			'target' => array()
		),
		'strong' => array(),
		'em' => array(),
		'p' => array(),
	);

	return apply_filters('wpmm_gdpr_textarea_allowed_html', $allowed_html);
}

/**
 * Return capability
 * 
 * @since 2.3.0
 * @param string $action
 * @return string
 */
function wpmm_get_capability($action) {
        if(has_filter('wpmm_all_actions_capability')) {
            return apply_filters('wpmm_all_actions_capability', 'manage_options');
        }
    
        return apply_filters(sprintf('wpmm_%s_capability', $action), 'manage_options');
}

/**
 * Get template path
 * 
 * @since 2.4.0
 * @param string $template_name
 * @return string
 */
function wpmm_get_template_path($template_name) {
    $file_path = WPMM_VIEWS_PATH . $template_name;
    $files_list = array(
        get_stylesheet_directory() . '/wp-maintenance-mode/' . $template_name, // check child theme
        get_template_directory() . '/wp-maintenance-mode/' . $template_name, // check theme
    );

    // maintain backward compatibility
    if ($template_name === 'maintenance.php') {
        $files_list = array_merge($files_list, array(
            get_stylesheet_directory() . '/wp-maintenance-mode.php', // check child theme
            get_template_directory() . '/wp-maintenance-mode.php', // check theme
            WP_CONTENT_DIR . '/wp-maintenance-mode.php', // check `wp-content`
        ));
    }

    // we need just unique values because get_stylesheet_directory() === get_template_directory() if you don't use a child theme
    foreach (array_unique($files_list) as $file) {
        if (file_exists($file)) {
            $file_path = $file;
            break;
        }
    }

    /**
     * Possible filters:
     * - wpmm_maintenance_template
     * - wpmm_contact_template
     */
    return apply_filters(sprintf('wpmm_%s_template', basename($template_name, '.php')), $file_path);
}

/**
 * Run shortcodes
 * 
 * @since 2.4.0
 * @global object $post
 * @param string $content
 * @return string
 */
function wpmm_do_shortcode($content) {
    global $post;

    // register and run [embed] shortcode
    if (isset($GLOBALS['wp_embed']) && is_callable(array($GLOBALS['wp_embed'], 'run_shortcode'))) {
        // $post should be null. this way, the cache will be saved separately, not as a post_meta of the current post
        $post = null;
        
        $content = $GLOBALS['wp_embed']->run_shortcode($content);
    }
    
    return do_shortcode($content);
}

/**
 * Returns the value of the option after `stripslashes_deep` is applied
 * 
 * @since 2.4.0
 * @param string $option
 * @param mixed $default
 * @return mixed
 */
function wpmm_get_option($option, $default = false) {
    return stripslashes_deep(get_option($option, $default));
}

/**
 * Generate form hidden fields
 * 
 * @param string $name
 */
function wpmm_form_hidden_fields($name) {
    $hidden_fields = array(
        array(
            'name' => sprintf('tab-%s', $name),
            'type' => 'nonce',
        ),
        array(
            'name' => 'tab',
            'value' => $name,
            'type' => 'custom',
        ),
        array(
            'name' => 'action',
            'value' => 'wpmm_save_settings',
            'type' => 'custom',
        ),
    );

    foreach ($hidden_fields as $field) {
        switch ($field['type']) {
            case 'custom':
                printf('<input type="hidden" value="%s" name="%s" />', esc_attr($field['value']), esc_attr($field['name']));
                break;
            case 'nonce':
                wp_nonce_field($field['name']);
                break;
        }
    }
}

if (!function_exists('wp_scripts')) {

	/**
	 * Initialize $wp_scripts if it has not been set.
	 *
	 * (to maintain backward compatibility for those with WP < 4.2.0)
	 *
	 * @since 2.0.8
	 * @global WP_Scripts $wp_scripts
	 * @return WP_Scripts instance
	 */
	function wp_scripts() {
		global $wp_scripts;

		if (!( $wp_scripts instanceof WP_Scripts )) {
			$wp_scripts = new WP_Scripts();
		}

		return $wp_scripts;
	}

}

if (!function_exists('sanitize_hex_color')) {
    
        /**
         * Sanitizes a hex color.
         *
         * (to maintain backward compatibility for those with WP < 4.6.0)
         *
         * @param string $color
         * @return string|void
         */
        function sanitize_hex_color( $color ) {
                if ( '' === $color ) {
                        return '';
                }

                // 3 or 6 hex digits, or the empty string.
                if ( preg_match( '|^#([A-Fa-f0-9]{3}){1,2}$|', $color ) ) {
                        return $color;
                }
        }

}