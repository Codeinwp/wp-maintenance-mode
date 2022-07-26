<?php
/**
 * Template for the maintenance page
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<?php if ( is_singular() && pings_open( get_queried_object() ) ) : ?>
		<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
	<?php endif; ?>
	<?php do_action( 'wpmm_head' ); ?>
	<?php wp_head(); ?>
</head>
<body  <?php body_class(); ?>>
	<?php
	// todo: otter css is not loaded
	wp_body_open();
	$page_id = WP_Maintenance_Mode::get_instance()->get_plugin_settings()['design']['page_id'];

	$query   = get_post( $page_id );
	$content = apply_filters( 'the_content', $query->post_content );

	echo $content;

	if ( isset( $this->plugin_settings['bot']['status'] ) && $this->plugin_settings['bot']['status'] === 1 ) {
		?>
		<div class="bot-container">
			<div class="bot-chat-wrapper">
				<div class="chat-container cf"></div>
				<div class="input"></div>
				<div class="choices cf"></div>
			</div>
		</div>
		<?php
	}

	wp_footer();
	do_action( 'wpmm_footer' );
	?>
</body>
</html>
