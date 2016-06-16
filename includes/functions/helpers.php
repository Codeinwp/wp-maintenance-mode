<?php

/**
 * Get plugin info
 * 
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
 * @global object $wpdb
 * @param string $table
 * @param string $field
 * @param array $where
 */
function wpmm_count_where($table, $field = 'ID', $where = array()) {
    global $wpdb;

	$table = $wpdb->prefix . $table;
    if(!empty($where) && is_array($where)) $where = "WHERE " . implode(" AND ", array_keys($where)); else $where = "";
    $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(%s) FROM %s %s",$field,$table,$where));

    return intval($count);
}
