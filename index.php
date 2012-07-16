<?php
require_once('../../../wp-load.php');

if( !current_user_can('unfiltered_html') )
	wp_die( __('Cheatin&#8217; uh?') );

$WPMaintenanceMode = new WPMaintenanceMode();
$WPMaintenanceMode->on_active();

include 'site.php';
?>