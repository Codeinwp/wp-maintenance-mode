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

if ( isset( $this->plugin_settings['design']['page_id'] ) && get_option( 'wpmm_new_look' ) ) {
	if ( ! is_front_page() ) {
		wp_redirect( home_url() );
	}
} else {
	?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> >
	<head>
		<meta charset="UTF-8">
		<title><?php echo esc_html( $title ); ?></title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta name="author" content="<?php echo esc_attr( $author ); ?>" />
		<meta name="description" content="<?php echo esc_attr( $description ); ?>" />
		<meta name="keywords" content="<?php echo esc_attr( $keywords ); ?>" />
		<meta name="robots" content="<?php echo esc_attr( $robots ); ?>" />
		<?php
		do_action( 'wm_head' ); // this hook will be removed in the next versions
		do_action( 'wpmm_head' );
		?>
	</head>
	<body class="<?php echo $body_classes ? esc_attr( $body_classes ) : ''; ?>">
		<?php do_action( 'wpmm_after_body' ); ?>

		<div class="wrap" role="main">
			<?php if ( ! empty( $heading ) ) { ?>
				<!-- Heading -->
				<h1><?php echo esc_html( $heading ); ?></h1>
			<?php } ?>

			<?php
			/**
			 * We don't escape the $text, because wp_kses_post was applied before do_shortcode. So it's safe to output it.
			 */
			if ( ! empty( $text ) ) {
				$allowed_html = wp_kses_allowed_html( 'post' );

				$allowed_html['form']   = array(
					'id'     => array(),
					'class'  => array(),
					'action' => array(),
					'method' => array(),
				);
				$allowed_html['input']  = array(
					'type'        => array(),
					'id'          => array(),
					'name'        => array(),
					'value'       => array(),
					'class'       => array(),
					'placeholder' => array(),
				);
				$allowed_html['iframe'] = array(
					'src'             => array(),
					'height'          => array(),
					'width'           => array(),
					'frameborder'     => array(),
					'allowfullscreen' => array(),
					'data-*'          => true,
				);
				?>
				<!-- Text -->
				<h2><?php echo wp_kses( $text, $allowed_html ); ?></h2>
				<?php
			}
			?>

			<?php if ( ! empty( $this->plugin_settings['bot']['status'] ) && $this->plugin_settings['bot']['status'] === 1 ) { ?>
			</div><!-- .wrap -->

			<!-- Bot -->
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
			<?php } ?>

			<?php if ( ! empty( $this->plugin_settings['modules']['countdown_status'] ) && $this->plugin_settings['modules']['countdown_status'] === 1 ) { ?>
				<!-- Countdown -->
				<div class="countdown" data-start="<?php echo esc_attr( date( 'F d, Y H:i:s', strtotime( $countdown_start ) ) ); ?>" data-end="<?php echo esc_attr( date( 'F d, Y H:i:s', $countdown_end ) ); ?>"></div>
			<?php } ?>

			<?php
			if ( ( ! empty( $this->plugin_settings['modules']['subscribe_status'] ) && $this->plugin_settings['modules']['subscribe_status'] === 1 ) ) {
				?>
				<!-- Subscribe -->
				<?php if ( ! empty( $this->plugin_settings['modules']['subscribe_text'] ) ) { ?>
					<h3><?php echo esc_html( $this->plugin_settings['modules']['subscribe_text'] ); ?></h3>
				<?php } ?>

				<div class="subscribe_wrapper" style="min-height: 100px;">
					<form class="subscribe_form">
						<div class="subscribe_border">
							<input type="text" placeholder="<?php esc_attr_e( 'your e-mail...', 'wp-maintenance-mode' ); ?>" name="email" class="email_input" data-rule-required="true" data-rule-email="true" data-rule-required="true" data-rule-email="true" />
							<?php wp_nonce_field( 'wpmts_nonce_subscribe' ); ?>
							<input type="submit" value="<?php esc_attr_e( 'Subscribe', 'wp-maintenance-mode' ); ?>" />
						</div>
						<?php if ( ! empty( $this->plugin_settings['gdpr']['status'] ) && $this->plugin_settings['gdpr']['status'] === 1 ) { ?>
							<div class="privacy_checkbox">
								<label>
									<input type="checkbox" name="acceptance" value="YES" data-rule-required="true" data-msg-required="<?php esc_attr_e( 'This field is required.', 'wp-maintenance-mode' ); ?>" />

									<?php echo esc_html_x( 'I\'ve read and agree with the site\'s privacy policy', 'subscribe form', 'wp-maintenance-mode' ); ?>
								</label>
							</div>

							<?php if ( ! empty( $this->plugin_settings['gdpr']['subscribe_form_tail'] ) ) { ?>
								<p class="privacy_tail"><?php echo wp_kses( $this->plugin_settings['gdpr']['subscribe_form_tail'], wpmm_gdpr_textarea_allowed_html() ); ?></p>
							<?php } ?>
						<?php } ?>
					</form>
				</div>
			<?php } ?>

			<?php if ( ! empty( $this->plugin_settings['modules']['social_status'] ) && $this->plugin_settings['modules']['social_status'] === 1 ) { ?>
				<!-- Social networks -->
				<div class="social" data-target="<?php echo ! empty( $this->plugin_settings['modules']['social_target'] ) ? 1 : 0; ?>">
					<?php if ( ! empty( $this->plugin_settings['modules']['social_twitter'] ) ) { ?>
						<a class="tw" href="<?php echo esc_url( $this->plugin_settings['modules']['social_twitter'] ); ?>">twitter</a>
					<?php } ?>

					<?php if ( ! empty( $this->plugin_settings['modules']['social_facebook'] ) ) { ?>
						<a class="fb" href="<?php echo esc_url( $this->plugin_settings['modules']['social_facebook'] ); ?>">facebook</a>
					<?php } ?>

					<?php if ( ! empty( $this->plugin_settings['modules']['social_instagram'] ) ) { ?>
						<a class="instagram" href="<?php echo esc_url( $this->plugin_settings['modules']['social_instagram'] ); ?>">instagram</a>
					<?php } ?>

					<?php if ( ! empty( $this->plugin_settings['modules']['social_pinterest'] ) ) { ?>
						<a class="pin" href="<?php echo esc_url( $this->plugin_settings['modules']['social_pinterest'] ); ?>">pinterest</a>
					<?php } ?>

					<?php if ( ! empty( $this->plugin_settings['modules']['social_github'] ) ) { ?>
						<a class="git" href="<?php echo esc_url( $this->plugin_settings['modules']['social_github'] ); ?>">github</a>
					<?php } ?>

					<?php if ( ! empty( $this->plugin_settings['modules']['social_dribbble'] ) ) { ?>
						<a class="dribbble" href="<?php echo esc_url( $this->plugin_settings['modules']['social_dribbble'] ); ?>">dribbble</a>
					<?php } ?>

					<?php if ( ! empty( $this->plugin_settings['modules']['social_google+'] ) ) { ?>
						<a class="gplus" href="<?php echo esc_url( $this->plugin_settings['modules']['social_google+'] ); ?>">google plus</a>
					<?php } ?>

					<?php if ( ! empty( $this->plugin_settings['modules']['social_linkedin'] ) ) { ?>
						<a class="linkedin" href="<?php echo esc_url( $this->plugin_settings['modules']['social_linkedin'] ); ?>">linkedin</a>
					<?php } ?>
				</div>
			<?php } ?>

			<?php if ( ! empty( $this->plugin_settings['modules']['contact_status'] ) && $this->plugin_settings['modules']['contact_status'] === 1 ) { ?>
				<!-- Contact -->
				<div class="contact">
					<?php list($open, $close) = ! empty( $this->plugin_settings['modules']['contact_effects'] ) && strstr( $this->plugin_settings['modules']['contact_effects'], '|' ) ? explode( '|', $this->plugin_settings['modules']['contact_effects'] ) : explode( '|', 'move_top|move_bottom' ); ?>
					<div class="form <?php echo esc_attr( $open ); ?>">
						<span class="close-contact_form">
							<img src="<?php echo esc_url( WPMM_URL . 'assets/images/close.svg' ); ?>" alt="">
						</span>

						<form class="contact_form">
							<?php do_action( 'wpmm_contact_form_start' ); ?>

							<p class="col">
								<input type="text" placeholder="<?php esc_attr_e( 'Name', 'wp-maintenance-mode' ); ?>" data-rule-required="true" data-msg-required="<?php esc_attr_e( 'This field is required.', 'wp-maintenance-mode' ); ?>" name="name" class="name_input" />
							</p>
							<p class="col last">
								<input type="text" placeholder="<?php esc_attr_e( 'E-mail', 'wp-maintenance-mode' ); ?>" data-rule-required="true" data-rule-email="true" data-msg-required="<?php esc_attr_e( 'This field is required.', 'wp-maintenance-mode' ); ?>" data-msg-email="<?php esc_attr_e( 'Please enter a valid email address.', 'wp-maintenance-mode' ); ?>" name="email" class="email_input" />
							</p>
							<?php wp_nonce_field( 'wpmts_nonce_contact' ); ?>
							<br clear="all" />

							<?php do_action( 'wpmm_contact_form_before_message' ); ?>

							<p>
								<textarea placeholder="<?php esc_attr_e( 'Your message', 'wp-maintenance-mode' ); ?>" data-rule-required="true" data-msg-required="<?php esc_attr_e( 'This field is required.', 'wp-maintenance-mode' ); ?>" name="content" class="content_textarea"></textarea>
							</p>

							<?php do_action( 'wpmm_contact_form_after_message' ); ?>

							<?php if ( ! empty( $this->plugin_settings['gdpr']['status'] ) && $this->plugin_settings['gdpr']['status'] === 1 ) { ?>
								<div class="privacy_checkbox">
									<label>
										<input type="checkbox" name="acceptance" value="YES" data-rule-required="true" data-msg-required="<?php esc_attr_e( 'This field is required.', 'wp-maintenance-mode' ); ?>" />

										<?php echo esc_html_x( 'I\'ve read and agree with the site\'s privacy policy', 'contact form', 'wp-maintenance-mode' ); ?>
									</label>
								</div>

								<?php if ( ! empty( $this->plugin_settings['gdpr']['contact_form_tail'] ) ) { ?>
									<p class="privacy_tail"><?php echo wp_kses( $this->plugin_settings['gdpr']['contact_form_tail'], wpmm_gdpr_textarea_allowed_html() ); ?></p>
								<?php } ?>
							<?php } ?>

							<p class="submit"><input type="submit" value="<?php esc_attr_e( 'Send', 'wp-maintenance-mode' ); ?>" /></p>

							<?php do_action( 'wpmm_contact_form_end' ); ?>
						</form>
					</div>
				</div>

				<a class="contact_us" href="javascript:void(0);" data-open="<?php echo esc_attr( $open ); ?>" data-close="<?php echo esc_attr( $close ); ?>"><?php esc_html_e( 'Contact us', 'wp-maintenance-mode' ); ?></a>
			<?php } ?>

			<?php
			if (
				( ! empty( $this->plugin_settings['general']['admin_link'] ) && $this->plugin_settings['general']['admin_link'] === 1 ) ||
				( ! empty( $this->plugin_settings['gdpr']['status'] ) && $this->plugin_settings['gdpr']['status'] === 1 )
			) {
				?>
				<!-- Footer links -->
				<div class="footer_links">
					<?php if ( $this->plugin_settings['general']['admin_link'] === 1 ) { ?>
						<a href="<?php echo esc_url( admin_url() ); ?>"><?php esc_html_e( 'Dashboard', 'wp-maintenance-mode' ); ?></a>
					<?php } ?>

					<?php if ( $this->plugin_settings['gdpr']['status'] === 1 ) { ?>
						<a href="<?php echo esc_url( $this->plugin_settings['gdpr']['policy_page_link'] ); ?>" target="<?php echo ! empty( $this->plugin_settings['gdpr']['policy_page_target'] ) && $this->plugin_settings['gdpr']['policy_page_target'] === 1 ? '_blank' : '_self'; ?>"><?php echo esc_html( $this->plugin_settings['gdpr']['policy_page_label'] ); ?></a>
					<?php } ?>
				</div>
			<?php } ?>
		</div>

		<script type='text/javascript'>
			const wpmmVars = {"ajaxURL": "<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>"};
		</script>

		<?php
		do_action( 'wm_footer' ); // this hook will be removed in the next versions
		do_action( 'wpmm_footer' );
		?>
	</body>
</html>
	<?php
	exit();
}
?>
