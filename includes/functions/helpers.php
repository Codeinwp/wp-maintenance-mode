<?php

/**
 * Get plugin info
 * 
 * @since 2.0.0
 * @param string $plugin_slug
 * @return array
 */
function wpmm_plugin_info($plugin_slug) {
    add_filter('extra_plugin_headers', create_function('', 'return array("GitHub Plugin URI","Twitter");'));
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
 * Get banners list from Maintenance Mode API
 * 
 * @since 2.0.4
 * @return array
 */
function wpmm_get_banners() {
    if (false === ($banners = get_transient('wpmm_banners_list'))) {
        $response = wp_remote_get('http://maintenancemode.co/wp-json/wpmm/v1/banners', array(
            'timeout' => 10
        ));

        $banners = array();
        $items = json_decode(wp_remote_retrieve_body($response), true);

        if (!empty($items) && is_array($items)) {
            foreach ($items as $item) {
                $banners[$item['type']][] = $item;
            }
        }

        set_transient('wpmm_banners_list', $banners, 3 * HOUR_IN_SECONDS);
    }

    return $banners;
}