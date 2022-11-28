<?php
/**
 * Template Name: Full Width for Maintenance Page
 */

defined( 'ABSPATH' ) || exit;

$overrideable_template = wpmm_get_template_path( 'maintenance.php', true );
if ( ( defined( 'IS_MAINTENANCE' ) && IS_MAINTENANCE ) && WPMM_VIEWS_PATH . 'maintenance.php' !== $overrideable_template ) {
	exit();
} else {
	$settings = WP_Maintenance_Mode::get_instance()->get_plugin_settings();
	?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<?php do_action( 'wpmm_head' ); ?>
	<?php wp_head(); ?>
</head>
<body  <?php body_class(); ?>>
	<?php
	wp_body_open();

	the_post();
	the_content();

	if ( isset( $settings['bot']['status'] ) && $settings['bot']['status'] === 1 ) {
		?>
		<div class="bot-container">
			<div class="bot-avatar"><div class="avatar-notice"></div></div>
			<div class="bot-chat-wrapper" style="display: none">
				<div class="chat-container cf"></div>
				<div class="input"></div>
				<div class="choices cf"></div>
			</div>
		</div>
		<div class="bot-error"><p></p></div>
		<div class="wrap under-bot">
		<?php
	}
	?>

	<script type='text/javascript'>
		var wpmmVars = {"ajaxURL": "<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>"};
	</script>

	<?php
	wp_footer();
	do_action( 'wpmm_footer' );
	?>
</body>
</html>
	<?php
	exit();
}
