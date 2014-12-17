<?php

/**
 * Get plugin info
 * 
 * @since 2.0.0
 * @param string $plugin_slug
 * @return array
 */
function wpmm_plugin_info($plugin_slug) {
    add_filter('extra_plugin_headers', create_function('', 'return array("GitHub URI","Twitter");'));
    $plugin_data = get_plugin_data(WPMM_PATH . $plugin_slug . '.php');

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
 * @param string $where_string
 * @param array $where_values 
 */
function wpmm_count_where($table, $field = 'ID', $where_string = '', $where_values = array()) {
    global $wpdb;

    $table = $wpdb->prefix . $table;
    $query = empty($where_string) ? "SELECT COUNT($field) FROM $table" : $wpdb->prepare("SELECT COUNT($field) FROM $table WHERE " . $where_string, $where_values);
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