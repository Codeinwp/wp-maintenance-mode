<?php

/**
 * Get Designmodo posts
 * 
 * @param array $args
 * @return array
 */
function get_designmodo_posts($args = array()) {
    $args = wp_parse_args($args, array(
        'feed' => 'http://feeds.feedburner.com/designmodo',
        'posts_no' => 5
    ));

    // GET POSTS
    $feed = fetch_feed($args['feed']);
    if (!is_wp_error($feed)) {
        $max_items = $feed->get_item_quantity($args['posts_no']);
        $items = $feed->get_items(0, $max_items);

        return $items;
    }

    return array();
}

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
    $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT($field) FROM $table " . (!empty($where) ? "WHERE " . implode(" AND ", array_keys($where)) : ""), !empty($where) ? array_values($where) : array()));

    return intval($count);
}