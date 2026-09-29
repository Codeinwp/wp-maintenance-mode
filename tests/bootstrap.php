<?php
/**
 * PHPUnit bootstrap file
 *
 * @package Sample_Theme
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( class_exists( '\Yoast\PHPUnitPolyfills\Autoload' ) === false ) {
	require_once dirname( __DIR__ ) . '/vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php';
}

if ( ! $_tests_dir ) {
    $_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
    echo "Could not find $_tests_dir/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL;
    exit( 1 );
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Registers theme
 */
function _register_module() {
    require_once dirname( dirname( __FILE__ ) ) . '/wp-maintenance-mode.php';
}

tests_add_filter( 'muplugins_loaded', '_register_module' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';

/**
 * Boot a fresh admin instance, the same way plugins_loaded and init do on admin requests.
 *
 * @return WP_Maintenance_Mode_Admin
 */
function wpmm_test_boot_admin() {
	if ( ! class_exists( 'WP_Maintenance_Mode_Admin' ) ) {
		require_once WPMM_CLASSES_PATH . 'wp-maintenance-mode-admin.php';
	}

	$instance = new ReflectionProperty( 'WP_Maintenance_Mode_Admin', 'instance' );
	$instance->setAccessible( true );
	$instance->setValue( null, null );

	$admin = WP_Maintenance_Mode_Admin::get_instance();
	$admin->load_default_settings();

	return $admin;
}
