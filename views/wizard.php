<?php
$default_templates = array(
	'coming-soon'  => array(
		'slug'      => 'coming-soon-1',
		'thumbnail' => WPMM_TEMPLATES_URL . 'coming-soon/coming-soon-1/screenshot.png',
	),
	'maintenance'  => array(
		'slug'      => 'maintenance-1',
		'thumbnail' => WPMM_TEMPLATES_URL . 'maintenance/maintenance-1/screenshot.png',
	),
	'landing-page' => array(
		'slug'      => 'landing-page-1',
		'thumbnail' => WPMM_TEMPLATES_URL . 'landing-page/landing-page-1/screenshot.png',
	),
);

?>
<div id="wpmm-wizard-wrapper">
	<div class="slider-wrap">
		<div class="step-wrap">
			<div class="step import-step">
				<h4 class="header"><?php esc_html_e( 'Get a boost with our free features', 'wp-maintenance-mode' ); ?></h4>
				<?php if ( ! is_plugin_active( 'otter-blocks/otter-blocks.php' ) ) { ?>
					<div class="optimole-upsell">
						<div class="optimole-upsell-container">
							<span class="components-checkbox-control__input-container">
								<input id="wizard-otter-block-checkbox" type="checkbox" class="components-checkbox-control__input" checked>
								<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" role="presentation" class="components-checkbox-control__checked" aria-hidden="true" focusable="false"><path d="M16.7 7.1l-6.3 8.5-3.3-2.5-.9 1.2 4.5 3.4L17.9 8z"></path></svg>
							</span>
							<label for="wizard-otter-block-checkbox"><?php echo esc_html__( 'Essential Page Templates', 'wp-maintenance-mode' ); ?></label>
						</div>
						<p class="description">
							<?php
							echo wp_kses(
								sprintf(
									// translators: %1$s is a description, %2$s is otter-block link, %3$s is plugin name, %4$s is description text.
									'<strong>%1$s <a href="%2$s" target="_blank">%3$s</a></strong> %4$s',
									__( 'Pick a template to get started.', 'wp-maintenance-mode' ),
									tsdk_utmify( 'https://themeisle.com/plugins/otter-blocks/', $this->plugin_slug, 'wizard' ),
									__( 'Otter Blocks', 'wp-maintenance-mode' ),
									__( 'plugin will be installed and activated to support and customize your layout.', 'wp-maintenance-mode' ),
								),
								wpmm_translated_string_allowed_html()
							);
							?>
							<br>
							<?php
							esc_html_e( 'It also unlocks tools like forms and popups - if you need them later.', 'wp-maintenance-mode' );
							?>
						</p>
					</div>
					<div class="wpmm-templates-radio">
						<form>
							<?php
							$categories = WP_Maintenance_Mode::get_page_categories();
							foreach ( $categories as $category => $label ) {
								$slug          = $default_templates[ $category ]['slug'];
								$thumbnail_url = $default_templates[ $category ]['thumbnail'];
								?>
								<div class="templates-radio__item" >
									<h6 class="tag"><?php echo $label; ?></h6>
									<input id="<?php echo esc_attr( $slug ); ?>" type="radio" name="wizard-template" value="<?php echo esc_attr( $slug ); ?>" data-category="<?php echo esc_attr( $category ); ?>" <?php checked( $category, 'coming-soon' ); ?>>
									<label for="<?php echo esc_attr( $slug ); ?>" class="wpmm-template">
										<img src="<?php echo esc_url( $thumbnail_url ); ?>" alt="<?php echo esc_attr( $slug ); ?>"/>
										<span class="checked-icon">
											<img src="<?php echo esc_url( WPMM_URL . 'assets/images/checked.svg' ); ?>" alt="<?php echo esc_attr( 'checked-icon' ); ?>"/>
										</span>
									</label>
								</div>
								<?php
							}
							?>
						</form>
					</div>
				<?php } ?>
				<?php if ( ! is_plugin_active( 'optimole-wp/optimole-wp.php' ) ) { ?>
					<div class="optimole-upsell">
						<div class="optimole-upsell-container">
							<span class="components-checkbox-control__input-container">
								<input id="wizard-optimole-checkbox" type="checkbox" class="components-checkbox-control__input" checked>
								<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" role="presentation" class="components-checkbox-control__checked" aria-hidden="true" focusable="false"><path d="M16.7 7.1l-6.3 8.5-3.3-2.5-.9 1.2 4.5 3.4L17.9 8z"></path></svg>
							</span>
							<label for="wizard-optimole-checkbox">
								<?php esc_html_e( 'Image and speed optimisation', 'wp-maintenance-mode' ); ?>
							</label>
						</div>
						<p class="description">
							<?php
							echo wp_kses(
								sprintf(
									// translators: %1$s is a description, %2$s is optimole-wp link, %3$s is plugin name, %4$s is description text.
									'%1$s <a href="%2$s" target="_blank">%3$s</a> %4$s',
									__( 'Templates would have pre-optimized images and all of your website’s images would be delivered via Amazon Cloudfront CDN, resulting in an ≈ 80% increase in speed.', 'wp-maintenance-mode' ),
									esc_url( 'https://wordpress.org/plugins/optimole-wp/' ),
									'Optimole',
									__( 'plugin will be installed and activated.', 'wp-maintenance-mode' ),
								),
								wpmm_translated_string_allowed_html(),
							);
							?>
					</div>
				<?php } ?>

				<div id="wizard-buttons" class="import-button">
					<input type="button" class="button button-big button-primary disabled button-import" value="<?php esc_html_e( 'Continue', 'wp-maintenance-mode' ); ?>"/>
					<input type="button" class="button button-big button-secondary button-skip" value="<?php esc_html_e( 'Skip this step', 'wp-maintenance-mode' ); ?>"/>
				</div>
			</div>
		</div>
		<div class="step-wrap">
			<div class="step subscribe-step" aria-hidden="true" style="display: none">
				<img width="250px" src="<?php echo WPMM_IMAGES_URL . 'subscribe.svg'; ?>" alt="subscribe"/>
				<h4><?php esc_html_e( 'Stay in the loop!', 'wp-maintenance-mode' ); ?></h4>
				<p><?php esc_html_e( 'Keep up with feature announcements, promotions, tutorials, and new template releases.', 'wp-maintenance-mode' ); ?></p>
				<div id="email-input-wrap">
					<input type="text" value="<?php echo esc_attr( get_bloginfo( 'admin_email' ) ); ?>" />
					<input type="button" class="button button-primary button-big subscribe-button" value="<?php esc_attr_e( 'Sign me up', 'wp-maintenance-mode' ); ?>" />
				</div>
				<input id="skip-subscribe" type="button" class="button button-link skip-link" value="<?php esc_attr_e( 'I\'ll skip for now, thanks!', 'wp-maintenance-mode' ); ?>" />
			</div>
		</div>
		<div class="step-wrap">
			<div class="step finish-step" aria-hidden="true" style="display: none">
				<img width="250px" src="<?php echo WPMM_IMAGES_URL . 'finish-setup.svg'; ?>" alt="finish-setup"/>
				<h4 class="heading"><?php esc_html_e( 'Your page is ready!', 'wp-maintenance-mode' ); ?></h4>
				<p><?php esc_html_e( 'Head over to the settings page to activate your Coming soon page', 'wp-maintenance-mode' ); ?></p>
				<div class="buttons-wrap">
					<input id="view-page-button" type="button" class="button-big button" value="<?php esc_attr_e( 'View page', 'wp-maintenance-mode' ); ?>"/>
					<input id="refresh-button" type="button" class="button-big button button-primary" value="<?php esc_attr_e( 'Go to settings', 'wp-maintenance-mode' ); ?>"/>
				</div>
			</div>
		</div>
	</div>
</div>
