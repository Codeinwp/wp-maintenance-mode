<?php
/**
 * LightStart
 *
 * Plugin Name: LightStart - Maintenance Mode, Coming Soon and Landing Page Builder
 * Description: Adds a splash page to your site that lets visitors know your site is down for maintenance. It's perfect for a coming soon or landing page.
 * Version: 2.6.7
 * Author: Themeisle
 * Author URI: https://themeisle.com/
 * Twitter: themeisle
 * GitHub Plugin URI: https://github.com/codeinwp/wp-maintenance-mode
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: wp-maintenance-mode
 * Domain Path: /languages
 * WordPress Available:  yes
 * Requires License:    no
 */

defined( 'ABSPATH' ) || exit;

/**
 * DEFINE PATHS
 */
define( 'WPMM_PATH', plugin_dir_path( __FILE__ ) );
define( 'WPMM_FILE', __FILE__ );
define( 'WPMM_CLASSES_PATH', WPMM_PATH . 'includes/classes/' );
define( 'WPMM_FUNCTIONS_PATH', WPMM_PATH . 'includes/functions/' );
define( 'WPMM_LANGUAGES_PATH', basename( WPMM_PATH ) . '/languages/' );
define( 'WPMM_VIEWS_PATH', WPMM_PATH . 'views/' );
define( 'WPMM_CSS_PATH', WPMM_PATH . 'assets/css/' );
define( 'WPMM_TEMPLATES_PATH', WPMM_PATH . 'assets/templates/' );

/**
 * DEFINE URLS
 */
define( 'WPMM_URL', plugin_dir_url( __FILE__ ) );
define( 'WPMM_JS_URL', WPMM_URL . 'assets/js/' );
define( 'WPMM_CSS_URL', WPMM_URL . 'assets/css/' );
define( 'WPMM_IMAGES_URL', WPMM_URL . 'assets/images/' );
define( 'WPMM_TEMPLATES_URL', WPMM_URL . 'assets/templates/' );

/**
 * OTHER DEFINES
 */
define( 'WPMM_ASSETS_SUFFIX', ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min' );

/**
 * FUNCTIONS
 */
require_once WPMM_FUNCTIONS_PATH . 'hooks.php';
require_once WPMM_FUNCTIONS_PATH . 'helpers.php';
if ( is_multisite() && ! function_exists( 'is_plugin_active_for_network' ) ) {
	require_once ABSPATH . '/wp-admin/includes/plugin.php';
}

/**
 * FRONTEND
 */
require_once WPMM_CLASSES_PATH . 'wp-maintenance-mode-shortcodes.php';
require_once WPMM_CLASSES_PATH . 'wp-maintenance-mode.php';
register_activation_hook( __FILE__, array( 'WP_Maintenance_Mode', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'WP_Maintenance_Mode', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'WP_Maintenance_Mode', 'get_instance' ) );

/**
 * DASHBOARD
 */
if ( is_admin() ) {
	require_once WPMM_CLASSES_PATH . 'wp-maintenance-mode-admin.php';
	add_action( 'plugins_loaded', array( 'WP_Maintenance_Mode_Admin', 'get_instance' ) );
}

add_filter( 'themeisle_sdk_products', 'wpmm_load_sdk' );


/**
 * Filter products array.
 *
 * @param array $products products array.
 *
 * @return array
 */
function wpmm_load_sdk( $products ) {
	$products[] = __FILE__;
	return $products;
}

$autoload_path = __DIR__ . '/vendor/autoload.php';
if ( is_file( $autoload_path ) ) {
	require_once $autoload_path;
}

add_filter(
	'wp_maintenance_mode_load_promotions',
	function() {
		return array( 'otter' );
	}
);
