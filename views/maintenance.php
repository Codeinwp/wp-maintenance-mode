<?php
/**
 * Maintenance mode page
 *
 * This template can be overridden by copying it to one of these paths:
 * - /wp-content/themes/{your_child_theme}/wp-maintenance-mode/maintenance.php
 * - /wp-content/themes/{your_theme}/wp-maintenance-mode/maintenance.php
 * - /wp-content/themes/{your_child_theme}/wp-maintenance-mode.php [deprecated]
 * - /wp-content/themes/{your_theme}/wp-maintenance-mode.php [deprecated]
 * - /wp-content/wp-maintenance-mode.php
 *
 * It can also be overridden by changing the default path. See `wpmm_maintenance_template` hook:
 * https://github.com/WP-Maintenance-Mode/Snippet-Library/blob/master/change-template-path.php
 *
 * @version 2.4.0
 */

defined( 'ABSPATH' ) || exit;

global $wp;
$current_url = home_url( $wp->request );
$maintenance_url = get_permalink( $this->plugin_settings['design']['page_id'] );

// TODO: idk?
if ( $maintenance_url !== $current_url . '/' ) {
	wp_redirect( $maintenance_url );
}
?>

<style>
	header, footer {
		display: none;
	}
</style>

