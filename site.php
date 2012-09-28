<?php
if ( !isset($value) ) {
	if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) )
		$value = get_site_option( FB_WM_TEXTDOMAIN );
	else
		$value = get_option( FB_WM_TEXTDOMAIN );
	$unitvalues = $WPMaintenanceMode->case_unit($value['unit']);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?> id="wp_maintenance_mode" >

<head>
	
	<title><?php if ( isset($value['title']) && ($value['title'] != '') ) echo stripslashes_deep( $value['title'] ); else { bloginfo('name'); echo ' - '; _e( 'Maintenance Mode', FB_WM_TEXTDOMAIN ); } ?></title>
	
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
	<meta name="author" content="WP Maintenance Mode: Frank Bueltge, http://bueltge.de" />
	<meta name="description" content="<?php bloginfo('name'); echo ' - '; bloginfo('description'); ?>" />
	<?php
	if ( isset( $value['index'] ) && 1 === $value['index'] )
		$content = 'noindex, nofollow';
	else {
		$content = 'index, follow';
	} ?>
	<meta name="robots" content="<?php echo $content; ?>" />
	<link rel="Shortcut Icon" type="image/x-icon" href="<?php echo get_option('home'); ?>/favicon.ico" />
	<link rel="stylesheet" type="text/css" href="<?php echo WP_PLUGIN_URL . '/' . FB_WM_BASEDIR ?>/css/jquery.countdown.css" media="all" />
	
	<?php
	if ( ! defined('WP_CONTENT_URL') )
		define('WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
	if ( ! defined('WP_PLUGIN_URL') )
		define( 'WP_PLUGIN_URL', WP_CONTENT_URL . '/plugins' );
	
	if ( ! defined('FB_WM_BASENAME') )
		define( 'FB_WM_BASENAME', plugin_basename(__FILE__) );
	if ( ! defined('FB_WM_BASEDIR') )
		define( 'FB_WM_BASEDIR', dirname( plugin_basename(__FILE__) ) );
	
	global $user_ID;
	
	get_currentuserinfo();
	$locale = get_locale();
	
	wm_head(); ?>
	
</head>

<body>
	
	<div id="header">
		<p><?php if ( isset($value['header']) && ($value['header'] != '') ) echo stripslashes_deep( $value['header'] ); else { bloginfo('name'); echo ' - '; bloginfo('description'); } ?></p>
	</div>

	<div id="content">
		<h1><?php if ( isset($value['heading']) && ($value['heading'] != '') ) echo stripslashes_deep( $value['heading'] ); else _e( 'Maintenance Mode', FB_WM_TEXTDOMAIN ); ?></h1>
		
		<?php wm_content();
		if ( isset( $value['admin_link'] ) && 1 === $value['admin_link'] ) {
			if ( isset($user_ID) && $user_ID ) {
				$adminlogin    = wp_logout_url();
				if ( isset($rolestatus) && 'norights' == $rolestatus )
					$adminloginmsg = '<h3>' . __( 'Access to the admin area blocked', FB_WM_TEXTDOMAIN ) . '</h3>';
				else
					$adminloginmsg = '';
				$adminloginstr = __( 'Admin-Logout', FB_WM_TEXTDOMAIN );
			} else {
				$adminlogin    = site_url('wp-login.php', 'login');
				$adminloginmsg = '';
				$adminloginstr = __( 'Admin-Login', FB_WM_TEXTDOMAIN );
			}
			echo $adminloginmsg;
		?>
		<div class="admin" onclick="location.href='<?php echo $adminlogin; ?>';" onkeypress="location.href='<?php echo $adminlogin; ?>';"><a href="<?php echo $adminlogin; ?>"><?php echo $adminloginstr; ?></a></div>
		<?php } ?>
	</div>
	
	<?php wm_footer(); ?>
	
	<?php
	$td = WPMaintenanceMode::check_datetime();
	if ( isset($td[2]) && 0 !== $td[2] ) {

		$locale = substr($locale, 0, 2);
	?>
		<script type="text/javascript" src="<?php bloginfo('url') ?>/wp-includes/js/jquery/jquery.js"></script>
		<script type="text/javascript" src="<?php echo WPMaintenanceMode::get_plugins_url( 'js/jquery.countdown.pack.js', __FILE__ ); ?>"></script>
		<?php if ( @file_exists( FB_WM_BASE . '/js/jquery.countdown-' . $locale . '.js') ) { ?>
		<script type="text/javascript" src="<?php echo WPMaintenanceMode::get_plugins_url( 'js/jquery.countdown-' . $locale . '.js', __FILE__ ); ?>"></script>
		<?php } ?>
		<script type="text/javascript">
			jQuery(document).ready( function($){
				var austDay = new Date();
				// 'Years', 'Months', 'Weeks', 'Days', 'Hours', 'Minutes', 'Seconds'
				austDay = new Date(<?php echo $td[2]; ?>);
				$('#countdown').countdown({ until: austDay });
			});
		</script>
	<?php } ?>
</body>
</html>
