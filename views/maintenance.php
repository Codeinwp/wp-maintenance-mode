<!DOCTYPE html>
<html>
    <head>
		<meta charset="UTF-8">
        <title><?php echo stripslashes($title); ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
        <meta name="author" content="<?php echo esc_attr($author); ?>" />
        <meta name="description" content="<?php echo esc_attr($description); ?>" />
        <meta name="keywords" content="<?php echo esc_attr($keywords); ?>" />
        <meta name="robots" content="<?php echo esc_attr($robots); ?>" />
		<?php
		if (!empty($styles) && is_array($styles)) {
			foreach ($styles as $src) {
				?>
				<link rel="stylesheet" href="<?php echo $src; ?>">
				<?php
			}
		}
		if (!empty($custom_css) && is_array($custom_css)) {
			echo '<style>' . implode(array_map('stripslashes', $custom_css)) . '</style>';
		}

		// do some actions
		do_action('wm_head'); // this hook will be removed in the next versions
		do_action('wpmm_head');
		?>
    </head>
    <body class="<?php echo $body_classes ? $body_classes : ''; ?>">
		<?php do_action('wpmm_after_body'); ?>

        <div class="wrap">
			<?php
			// If bot is enabled no text will be shown
			if (!empty($text) && $this->plugin_settings['bot']['status'] === 0) {
				echo "<h2>" . stripslashes($text) . "</h2>";
			}
			?>

			<?php if (!empty($heading)) { ?><h1><?php echo stripslashes($heading); ?></h1><?php } ?>

			<?php if (!empty($this->plugin_settings['bot']['status']) && $this->plugin_settings['bot']['status'] === 1) { ?>
			</div><!-- .wrap -->
			<div class="bot-container">
				<!-- WP Bot -->
				<div class="bot-chat-wrapper">
					<!-- Chats -->
					<div class="chat-container cf"></div>
					<!-- User input -->
					<div class="input"></div>
					<!-- User choices -->
					<div class="choices cf"></div>
				</div>
				<!-- /WP Bot -->
			</div>
			<div class="bot-error"><p></p></div>
			<div class="wrap under-bot">
			<?php } ?>

			<?php
			if (!empty($this->plugin_settings['modules']['countdown_status']) && $this->plugin_settings['modules']['countdown_status'] == 1) {
				?>
				<div class="countdown" data-start="<?php echo date('F d, Y H:i:s', strtotime($countdown_start)); ?>" data-end="<?php echo date('F d, Y H:i:s', $countdown_end); ?>"></div>
			<?php } ?>

			<?php
			if (!empty($this->plugin_settings['modules']['subscribe_status']) && $this->plugin_settings['modules']['subscribe_status'] == 1
					// If the bot is active, legacy subscribe form will be hidden
					// !empty($this->plugin_settings['bot']['status']) && 
					&& $this->plugin_settings['bot']['status'] === 0) {
				?>
				<?php if (!empty($this->plugin_settings['modules']['subscribe_text'])) { ?><h3><?php echo stripslashes($this->plugin_settings['modules']['subscribe_text']); ?></h3><?php } ?>
				<div class="subscribe_wrapper" style="min-height: 100px;">
					<form class="subscribe_form">
						<div class="subscribe_border">
							<input type="text" placeholder="<?php _e('Email', $this->plugin_slug); ?>" name="email" class="email_input" data-rule-required="true" data-rule-email="true" data-rule-required="true" data-rule-email="true" />
							<input type="submit" value="<?php _e('Subscribe', $this->plugin_slug); ?>" />
						</div>
						<?php if (!empty($this->plugin_settings['gdpr']['status']) && $this->plugin_settings['gdpr']['status'] == 1) { ?>
							<div class="privacy_checkbox">
								<label>
									<input type="checkbox" name="acceptance" value="YES" data-rule-required="true" data-msg-required="<?php esc_attr_e('This field is required.', $this->plugin_slug); ?>">

									<span class="checkmark"></span>

									<?php
									if ($this->plugin_settings['gdpr']['policy_page_link']) {
										$policy_link = '<a href="' . $this->plugin_settings['gdpr']['policy_page_link'] . '">' . __("privacy policy") . '</a>';
									} else {
										$policy_link = __("privacy policy");
									}
									_e("I've read and agree with the site's $policy_link", $this->plugin_slug);
									?>
								</label>
							</div>

							<?php if (!empty($this->plugin_settings['gdpr']['subscribe_form_tail'])) { ?>
								<p class="privacy_tail"><?php echo wp_kses($this->plugin_settings['gdpr']['subscribe_form_tail'], wpmm_gdpr_textarea_allowed_html()); ?></p>
								<?php
							}
						}
						?>
					</form>
				</div>
			<?php } ?>

			<?php if (!empty($this->plugin_settings['modules']['contact_status']) && $this->plugin_settings['modules']['contact_status'] == 1) { ?>
				<div class="contact">
					<?php list($open, $close) = !empty($this->plugin_settings['modules']['contact_effects']) && strstr($this->plugin_settings['modules']['contact_effects'], '|') ? explode('|', $this->plugin_settings['modules']['contact_effects']) : explode('|', 'move_top|move_bottom'); ?>
					<div class="form <?php echo esc_attr($open); ?>">
						<span class="close-contact_form">
							<img src="<?php echo WPMM_URL ?>assets/images/close.svg" alt="">
						</span>
						<svg width="365px" height="336px" viewBox="0 0 365 336" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
						<g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
						<g id="Mail" fill="#4361EF" fill-rule="nonzero">
						<path d="M362.561,140.311 C362.232,138.899 361.409,137.65 360.241,136.791 L331.041,114.151 L331.041,65.191 C331.041,57.415 324.737,51.111 316.961,51.111 L249.761,51.111 L185.761,1.431 C183.596,-0.25 180.566,-0.25 178.401,1.431 L114.401,51.111 L47.201,51.111 C39.425,51.111 33.121,57.415 33.121,65.191 L33.121,114.071 L3.521,136.791 C2.361,137.678 1.565,138.958 1.281,140.391 C0.402,145.086 -0.027,149.854 0.001,154.631 L0.001,279.911 C0.001,321.191 28.881,335.911 56.001,335.911 L308.081,335.911 C349.361,335.911 364.081,307.031 364.081,279.911 L364.081,154.631 C364.079,149.818 363.57,145.018 362.561,140.311 Z M331.041,129.431 L351.041,144.791 C351.35,146.912 351.537,149.049 351.601,151.191 L331.041,163.111 L331.041,129.431 Z M182.001,13.831 L230.001,51.111 L134.001,51.111 L182.001,13.831 Z M44.961,65.191 C44.961,64.086 45.856,63.191 46.961,63.191 L317.041,63.191 C318.146,63.191 319.041,64.086 319.041,65.191 L319.041,170.071 L230.001,221.031 L185.521,187.831 L184.481,187.351 L183.361,187.351 L181.121,187.351 L180.081,187.351 L178.961,187.831 L134.001,221.271 L44.961,170.151 L44.961,65.191 Z M12.721,144.871 L32.881,129.271 L32.881,163.191 L12.241,151.271 C12.241,149.111 12.241,146.871 12.721,144.871 Z M12.001,279.911 L12.001,165.031 L123.121,229.031 L19.121,306.551 C14.103,298.598 11.62,289.307 12.001,279.911 Z M308.001,323.111 L55.921,323.511 C45.948,323.704 36.143,320.918 27.761,315.511 L182.001,199.911 L336.321,315.111 C328.025,320.834 318.066,323.648 308.001,323.111 Z M351.921,279.911 C351.954,289.272 349.407,298.462 344.561,306.471 L240.561,228.871 L351.921317,164.871 L351.921,279.911 Z" id="Shape"></path>
						</g>
						</g>
						</svg>
						<h3><?php _e('You have questions?', $this->plugin_slug); ?></h3>
						<form class="contact_form">
							<?php do_action('wpmm_contact_form_start'); ?>

							<p class="col"><input type="text" placeholder="<?php _e('Name', $this->plugin_slug); ?>" data-rule-required="true" data-msg-required="<?php esc_attr_e('This field is required.', $this->plugin_slug); ?>" name="name" class="name_input" /></p>
							<p class="col last"><input type="text" placeholder="<?php _e('E-mail', $this->plugin_slug); ?>" data-rule-required="true" data-rule-email="true" data-msg-required="<?php esc_attr_e('This field is required.', $this->plugin_slug); ?>" data-msg-email="<?php esc_attr_e('Please enter a valid email address.', $this->plugin_slug); ?>" name="email" class="email_input" /></p>
							<br clear="all" />

							<?php do_action('wpmm_contact_form_before_message'); ?>

							<p><textarea placeholder="<?php _e('Your message', $this->plugin_slug); ?>" data-rule-required="true" data-msg-required="<?php esc_attr_e('This field is required.', $this->plugin_slug); ?>" name="content" class="content_textarea"></textarea></p>

							<?php do_action('wpmm_contact_form_after_message'); ?>

							<?php if (!empty($this->plugin_settings['gdpr']['status']) && $this->plugin_settings['gdpr']['status'] == 1) { ?>
								<div class="privacy_checkbox">
									<label>
										<input type="checkbox" name="acceptance" value="YES" data-rule-required="true" data-msg-required="<?php esc_attr_e('This field is required.', $this->plugin_slug); ?>">
										<?php
										if ($this->plugin_settings['gdpr']['policy_page_link']) {
											$policy_link = '<a href="' . $this->plugin_settings['gdpr']['policy_page_link'] . '">' . __("privacy policy") . '</a>';
										} else {
											$policy_link = __("privacy policy");
										}
										_e("I've read and agree with the site's $policy_link", $this->plugin_slug);
										?>
									</label>
								</div>

								<?php if (!empty($this->plugin_settings['gdpr']['contact_form_tail'])) { ?>
									<p class="privacy_tail"><?php echo wp_kses($this->plugin_settings['gdpr']['contact_form_tail'], wpmm_gdpr_textarea_allowed_html()); ?></p>
									<?php
								}
							}
							?>
							<p class="submit"><input type="submit" value="<?php _e('Send Message', $this->plugin_slug); ?>"></p>

							<?php do_action('wpmm_contact_form_end'); ?>
						</form>
					</div>
				</div>

				<a class="contact_us" href="javascript:void(0);" data-open="<?php echo esc_attr($open); ?>" data-close="<?php echo esc_attr($close); ?>"><?php _e('Contact us', $this->plugin_slug); ?></a>
			<?php } ?>
				
<?php if (!empty($this->plugin_settings['modules']['social_status']) && $this->plugin_settings['modules']['social_status'] == 1) { ?>
                <div class="social" data-target="<?php echo !empty($this->plugin_settings['modules']['social_target']) ? 1 : 0; ?>">
                    <?php if (!empty($this->plugin_settings['modules']['social_twitter'])) { ?>
                        <a class="tw" href="<?php echo stripslashes($this->plugin_settings['modules']['social_twitter']); ?>">
                            <svg width="98px" height="80px" viewBox="0 0 98 80" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <g id="Twitter" fill="#979B9E" fill-rule="nonzero">
                                        <path d="M97.523,9.526 C97.383,9.361 97.152,9.305 96.955,9.395 C94.036,10.69 90.965,11.621 87.802,12.171 C91.16,9.645 93.662,6.147 94.945,2.136 C95.007,1.944 94.943,1.734 94.786,1.609 C94.628,1.484 94.409,1.468 94.236,1.571 C90.454,3.814 86.358,5.395 82.056,6.272 C78.244,2.316 72.921,0.053 67.412,0.053 C56.208,0.053 47.094,9.167 47.094,20.37 C47.094,21.725 47.225,23.067 47.485,24.37 C31.967,23.412 17.457,15.962 7.591,3.861 C7.49,3.737 7.337,3.668 7.177,3.684 C7.018,3.696 6.876,3.786 6.796,3.923 C4.996,7.011 4.045,10.544 4.045,14.138 C4.045,20.367 6.875,26.191 11.694,30.034 C9.213,29.736 6.79,28.955 4.605,27.742 C4.458,27.659 4.275,27.66 4.128,27.745 C3.981,27.829 3.888,27.985 3.884,28.155 L3.882,28.415 C3.882,37.361 9.777,45.216 18.164,47.824 C15.955,48.18 13.663,48.156 11.41,47.726 C11.244,47.695 11.07,47.752 10.956,47.88 C10.843,48.008 10.805,48.187 10.857,48.349 C13.372,56.199 20.36,61.704 28.494,62.39 C21.709,67.361 13.689,69.98 5.215,69.98 C3.654,69.98 2.082,69.887 0.542,69.706 C0.322,69.681 0.104,69.812 0.028,70.023 C-0.048,70.236 0.033,70.474 0.223,70.595 C9.393,76.476 19.996,79.583 30.887,79.583 C66.512,79.583 87.8,50.645 87.8,22.669 C87.8,21.89 87.785,21.115 87.754,20.342 C91.597,17.531 94.896,14.09 97.556,10.107 C97.675,9.929 97.662,9.692 97.523,9.526 Z" id="Shape"></path>
                                    </g>
                                </g>
                            </svg>
                        </a>
                    <?php } ?>

                    <?php if (!empty($this->plugin_settings['modules']['social_facebook'])) { ?>
                        <a class="fb" href="<?php echo stripslashes($this->plugin_settings['modules']['social_facebook']); ?>">
                            <svg width="51px" height="97px" viewBox="0 0 51 97" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <g id="Facebook" fill="#979B9E" fill-rule="nonzero">
                                        <path d="M50.099,15.973 L41.041,15.977 C33.939,15.977 32.564,19.352 32.564,24.305 L32.564,35.226 L49.502,35.226 L49.496,52.332 L32.564,52.332 L32.564,96.227 L14.897,96.227 L14.897,52.332 L0.127,52.332 L0.127,35.226 L14.897,35.226 L14.897,22.612 C14.897,7.972 23.84,0 36.9,0 L50.1,0.021 L50.099,15.973 Z" id="Shape"></path>
                                    </g>
                                </g>
                            </svg>
                        </a>
                    <?php } ?>

                    <?php if (!empty($this->plugin_settings['modules']['social_instagram'])) { ?>
                        <a class="instagram" href="<?php echo stripslashes($this->plugin_settings['modules']['social_instagram']); ?>">
                            <svg width="512px" height="512px" viewBox="0 0 512 512" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <g id="Instagram" fill="#979B9E" fill-rule="nonzero">
                                        <path d="M256,174.6 C211.115,174.6 174.6,211.116 174.6,256 C174.6,300.884 211.116,337.4 256,337.4 C300.884,337.4 337.4,300.884 337.4,256 C337.4,211.116 300.885,174.6 256,174.6 Z" id="Shape"></path>
                                        <path d="M416.668,0 L95.334,0 C42.767,0 0,42.766 0,95.333 L0,416.667 C0,469.234 42.767,512 95.334,512 L416.668,512 C469.234,512 512,469.234 512,416.667 L512,95.333 C512,42.766 469.234,0 416.668,0 Z M256,367.4 C194.573,367.4 144.6,317.425 144.6,256 C144.6,194.575 194.573,144.6 256,144.6 C317.427,144.6 367.4,194.574 367.4,256 C367.4,317.426 317.427,367.4 256,367.4 Z M411.203,122.009 C408.414,124.799 404.554,126.399 400.603,126.399 C396.653,126.399 392.783,124.799 389.993,122.009 C387.203,119.219 385.603,115.349 385.603,111.399 C385.603,107.45 387.203,103.588 389.993,100.799 C392.783,97.998 396.653,96.399 400.603,96.399 C404.543,96.399 408.414,97.999 411.203,100.799 C414.004,103.588 415.603,107.449 415.603,111.399 C415.603,115.349 414.004,119.219 411.203,122.009 Z" id="Shape"></path>
                                    </g>
                                </g>
                            </svg>
                        </a>
                    <?php } ?>    

                    <?php if (!empty($this->plugin_settings['modules']['social_pinterest'])) { ?>
                        <a class="pin" href="<?php echo stripslashes($this->plugin_settings['modules']['social_pinterest']); ?>">
                            <svg width="244px" height="311px" viewBox="0 0 244 311" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <g id="Pinterest" fill="#979B9E" fill-rule="nonzero">
                                        <path d="M212.265,31.772 C190.923,11.284 161.388,0 129.101,0 C79.781,0 49.447,20.217 32.685,37.176 C12.027,58.076 0.181,85.827 0.181,113.315 C0.181,147.828 14.617,174.318 38.792,184.173 C40.415,184.838 42.048,185.173 43.649,185.173 C48.749,185.173 52.79,181.836 54.19,176.483 C55.006,173.412 56.897,165.836 57.719,162.547 C59.479,156.052 58.057,152.928 54.219,148.405 C47.227,140.132 43.971,130.349 43.971,117.617 C43.971,79.799 72.131,39.606 124.323,39.606 C165.735,39.606 191.46,63.143 191.46,101.031 C191.46,124.94 186.31,147.082 176.956,163.381 C170.456,174.706 159.026,188.206 141.479,188.206 C133.891,188.206 127.075,185.089 122.774,179.655 C118.711,174.518 117.372,167.882 119.006,160.966 C120.852,153.152 123.369,145.001 125.805,137.121 C130.248,122.729 134.448,109.136 134.448,98.291 C134.448,79.741 123.044,67.277 106.073,67.277 C84.505,67.277 67.608,89.183 67.608,117.148 C67.608,130.863 71.253,141.121 72.903,145.06 C70.186,156.572 54.038,225.013 50.975,237.919 C49.204,245.453 38.535,304.958 56.194,309.703 C76.035,315.034 93.77,257.08 95.575,250.531 C97.038,245.205 102.157,225.066 105.294,212.686 C114.872,221.912 130.294,228.149 145.3,228.149 C173.589,228.149 199.03,215.419 216.937,192.306 C234.304,169.888 243.869,138.642 243.869,104.328 C243.869,77.502 232.349,51.056 212.265,31.772 Z"></path>
                                    </g>
                                </g>
                            </svg>
                        </a>
                    <?php } ?>

                    <?php if (!empty($this->plugin_settings['modules']['social_github'])) { ?>
                        <a class="git" href="<?php echo stripslashes($this->plugin_settings['modules']['social_github']); ?>">
                            <svg width="476px" height="403px" viewBox="0 0 476 403" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <g id="Github" fill="#979B9E" fill-rule="nonzero">
                                        <path d="M436.244,110.752 C441.384,95.33 443.957,79.343 443.957,62.785 C443.957,40.705 439.098,19.957 429.393,0.543 C409.031,0.543 391.044,4.258 375.432,11.679 C359.828,19.102 342.032,30.617 322.053,46.224 C296.931,40.134 270.276,37.089 242.112,37.089 C211.275,37.089 181.867,40.422 153.889,47.083 C133.525,31.093 115.538,19.343 99.93,11.823 C84.322,4.303 66.241,0.544 45.683,0.544 C35.976,19.958 31.123,40.707 31.123,62.786 C31.123,79.537 33.691,95.716 38.831,111.321 C12.942,141.587 0,179.272 0,224.383 C0,263.978 5.898,295.475 17.701,318.89 C23.984,331.257 32.166,342.202 42.255,351.722 C52.34,361.236 63.856,368.95 76.8,374.852 C89.746,380.748 102.781,385.653 115.916,389.551 C129.05,393.451 143.562,396.309 159.456,398.11 C175.349,399.926 189.386,401.114 201.567,401.689 C213.748,402.253 227.26,402.542 242.111,402.542 C259.619,402.542 275.507,402.11 289.789,401.259 C304.066,400.404 320.383,398.306 338.753,394.983 C357.12,391.65 373.3,387.126 387.293,381.418 C401.283,375.71 414.705,367.523 427.552,356.867 C440.399,346.204 450.436,333.549 457.673,318.891 C469.277,295.288 475.085,263.784 475.085,224.384 C475.078,179.082 462.135,141.206 436.244,110.752 Z M401.995,318.455 C395.903,330.926 388.193,340.72 378.868,347.865 C369.539,355.004 357.93,360.712 344.037,365 C330.137,369.281 316.82,372.087 304.066,373.415 C291.308,374.749 277.133,375.413 261.521,375.413 L213.555,375.413 C197.948,375.413 183.765,374.749 171.014,373.415 C158.262,372.087 144.939,369.281 131.043,365 C117.152,360.712 105.543,355.004 96.214,347.865 C86.885,340.719 79.177,330.926 73.086,318.455 C66.996,305.984 63.95,291.379 63.95,274.631 C63.95,251.784 70.517,232.367 83.652,216.386 C96.786,200.396 114.581,192.404 137.039,192.404 C145.227,192.404 163.785,194.401 192.716,198.399 C206.229,200.492 221.172,201.539 237.539,201.539 C253.911,201.539 268.852,200.495 282.363,198.399 C311.68,194.4 330.232,192.404 338.041,192.404 C360.498,192.404 378.293,200.4 391.427,216.386 C404.562,232.374 411.125,251.784 411.125,274.631 C411.125,291.382 408.079,305.995 401.995,318.455 Z" id="Shape"></path>
                                        <path d="M166.875,229.52 C161.069,223.045 154.172,219.808 146.176,219.808 C138.178,219.808 131.28,223.049 125.475,229.52 C119.673,235.988 115.578,243.223 113.2,251.209 C110.817,259.211 109.629,267.013 109.629,274.631 C109.629,282.241 110.82,290.044 113.2,298.045 C115.575,306.036 119.668,313.267 125.475,319.734 C131.283,326.209 138.178,329.447 146.176,329.447 C154.172,329.447 161.072,326.203 166.875,319.734 C172.679,313.266 176.772,306.036 179.15,298.045 C181.53,290.044 182.721,282.241 182.721,274.631 C182.721,267.02 181.533,259.211 179.15,251.209 C176.771,243.226 172.682,235.994 166.875,229.52 Z" id="Shape"></path>
                                        <path d="M349.601,229.52 C343.797,223.045 336.898,219.808 328.904,219.808 C320.913,219.808 314.01,223.049 308.203,229.52 C302.399,235.988 298.307,243.223 295.932,251.209 C293.547,259.211 292.356,267.013 292.356,274.631 C292.356,282.241 293.547,290.044 295.932,298.045 C298.307,306.036 302.4,313.267 308.203,319.734 C314.011,326.209 320.913,329.447 328.904,329.447 C336.898,329.447 343.798,326.203 349.601,319.734 C355.402,313.266 359.497,306.036 361.879,298.045 C364.258,290.044 365.448,282.241 365.448,274.631 C365.448,267.02 364.258,259.211 361.879,251.209 C359.498,243.226 355.402,235.994 349.601,229.52 Z" id="Shape"></path>
                                    </g>
                                </g>
                            </svg>
                        </a>
                    <?php } ?>

                    <?php if (!empty($this->plugin_settings['modules']['social_dribbble'])) { ?>
                        <a class="dribbble" href="<?php echo stripslashes($this->plugin_settings['modules']['social_dribbble']); ?>">
                            <svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                                 viewBox="0 0 92 92" style="enable-background:new 0 0 92 92;" xml:space="preserve">
                            <g fill="#979B9E">
                                <path d="M43.826,33.104C37.033,21.031,29.772,11.19,29.239,10.469C18.283,15.646,10.107,25.737,7.557,37.9
                                    C8.588,37.919,24.96,38.116,43.826,33.104z M48.719,46.301c0.514-0.163,1.029-0.318,1.549-0.467
                                    c-0.986-2.234-2.062-4.47-3.188-6.676c-20.229,6.054-39.643,5.615-40.332,5.598C6.736,45.169,6.717,45.581,6.717,46
                                    c0,10.102,3.816,19.308,10.078,26.267c-0.012-0.013-0.027-0.034-0.027-0.034S27.521,53.157,48.719,46.301z M21.855,76.969
                                    l0.006-0.016c-0.291-0.229-0.604-0.438-0.889-0.676C21.494,76.695,21.855,76.969,21.855,76.969z M36.59,7.859
                                    c-0.035,0.008-0.07,0.017-0.104,0.024c0.061-0.015,0.097-0.021,0.097-0.021L36.59,7.859z M71.945,16.518
                                    C65.027,10.422,55.947,6.716,46,6.716c-3.191,0-6.287,0.391-9.256,1.107c0.598,0.792,7.971,10.589,14.686,22.918
                                    C66.242,25.188,71.842,16.679,71.945,16.518z M46,92C20.596,92,0,71.403,0,46C0,20.596,20.596,0,46,0c25.406,0,46,20.596,46,46
                                    C92,71.403,71.406,92,46,92z M52.684,52.429c-23.057,8.033-30.668,24.193-30.822,24.524c6.664,5.204,15.029,8.331,24.139,8.331
                                    c5.441,0,10.623-1.107,15.335-3.108c-0.582-3.444-2.867-15.476-8.392-29.835C52.857,52.371,52.771,52.396,52.684,52.429z
                                     M54.451,36.594c0.918,1.874,1.795,3.777,2.611,5.698c0.291,0.678,0.572,1.355,0.848,2.029c13.568-1.706,26.925,1.189,27.362,1.284
                                    c-0.093-9.323-3.423-17.869-8.935-24.56C76.258,21.163,69.956,30.263,54.451,36.594z M60.305,50.523
                                    c5.16,14.177,7.25,25.709,7.646,28.059c8.82-5.956,15.088-15.397,16.834-26.351C84.004,51.979,73.006,48.49,60.305,50.523z"/>
                            </g>
                            </svg>

                        </a>
                    <?php } ?>

                    <?php if (!empty($this->plugin_settings['modules']['social_google+'])) { ?>
                        <a class="gplus" href="<?php echo stripslashes($this->plugin_settings['modules']['social_google+']); ?>">
                            <svg width="459px" height="294px" viewBox="0 0 459 294" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                    <g id="Google-plus" fill="#979B9E" fill-rule="nonzero">
                                        <path d="M160.777,177.368 L232.371,177.368 C219.804,212.898 185.768,238.372 145.921,238.078 C97.572,237.721 57.594,199.043 55.717,150.729 C53.705,98.94 95.254,56.166 146.604,56.166 C170.083,56.166 191.509,65.112 207.662,79.771 C211.488,83.244 217.312,83.266 221.075,79.724 L247.371,54.975 C251.483,51.104 251.498,44.567 247.398,40.683 C221.781,16.414 187.417,1.287 149.522,0.547 C68.696,-1.031 0.567,65.238 0.004,146.078 C-0.564,227.525 65.289,293.727 146.604,293.727 C224.803,293.727 288.685,232.498 292.964,155.369 C293.078,154.402 293.153,121.721 293.153,121.721 L160.777,121.721 C155.351,121.721 150.953,126.119 150.953,131.545 L150.953,167.544 C150.953,172.97 155.352,177.368 160.777,177.368 Z" id="Shape"></path>
                                        <path d="M414.464,124.99 L414.464,89.817 C414.464,85.062 410.61,81.208 405.855,81.208 L376.251,81.208 C371.496,81.208 367.642,85.062 367.642,89.817 L367.642,124.99 L332.469,124.99 C327.714,124.99 323.86,128.844 323.86,133.599 L323.86,163.203 C323.86,167.958 327.714,171.812 332.469,171.812 L367.642,171.812 L367.642,206.985 C367.642,211.74 371.496,215.594 376.251,215.594 L405.855,215.594 C410.61,215.594 414.464,211.74 414.464,206.985 L414.464,171.812 L449.637,171.812 C454.392,171.812 458.246,167.958 458.246,163.203 L458.246,133.599 C458.246,128.844 454.392,124.99 449.637,124.99 L414.464,124.99 Z" id="Shape"></path>
                                    </g>
                                </g>
                            </svg>
                        </a>
                    <?php } ?>

                    <?php if (!empty($this->plugin_settings['modules']['social_linkedin'])) { ?>
                        <a class="linkedin" href="<?php echo stripslashes($this->plugin_settings['modules']['social_linkedin']); ?>">
                            <svg width="94px" height="93px" viewBox="0 0 94 93" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                            <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                <g id="Linkedin" fill="#979B9E" fill-rule="nonzero">
                                    <path d="M11.185,0.08 C5.004,0.08 0.001,5.092 0,11.259 C0,17.432 5.003,22.443 11.186,22.443 C17.352,22.443 22.362,17.432 22.362,11.259 C22.362,5.091 17.351,0.08 11.185,0.08 Z" id="Shape"></path>
                                    <rect id="Rectangle-path" x="1.538" y="30.926" width="19.287" height="62.054"></rect>
                                    <path d="M69.925,29.383 C60.543,29.383 54.252,34.527 51.677,39.405 L51.419,39.405 L51.419,30.926 L32.921,30.926 L32.92,30.926 L32.92,92.979 L52.19,92.979 L52.19,62.281 C52.19,54.188 53.731,46.349 63.765,46.349 C73.655,46.349 73.787,55.605 73.787,62.8 L73.787,92.978 L93.06,92.978 L93.06,58.942 C93.06,42.235 89.455,29.383 69.925,29.383 Z" id="Shape"></path>
                                </g>
                            </g>
                        </svg>
                        </a>
                    <?php } ?>
                </div>
            <?php } ?>				

			<?php
			if ((!empty($this->plugin_settings['general']['admin_link']) && $this->plugin_settings['general']['admin_link'] == 1) ||
					(!empty($this->plugin_settings['gdpr']['status']) && $this->plugin_settings['gdpr']['status'] == 1)) {
				?>
				<div class="author_link">
					<?php if ($this->plugin_settings['general']['admin_link'] == 1) { ?>
						<a href="<?php echo admin_url(); ?>"><?php _e('Dashboard', $this->plugin_slug); ?></a> 
					<?php } ?>
					<?php if ($this->plugin_settings['gdpr']['status'] == 1) { ?>
						<a href="<?php echo esc_attr($this->plugin_settings['gdpr']['policy_page_link']); ?>" target="<?php echo!empty($this->plugin_settings['gdpr']['policy_page_target']) && $this->plugin_settings['gdpr']['policy_page_target'] == 1 ? '_blank' : '_self'; ?>"><?php echo esc_html($this->plugin_settings['gdpr']['policy_page_label']); ?></a>
					<?php } ?>
				</div>
			<?php } ?>
        </div>

        <script type='text/javascript'>
			var wpmm_vars = {"ajax_url": "<?php echo admin_url('admin-ajax.php'); ?>"};
		</script>

		<?php
		// Hook before scripts, mostly for internationalization
		do_action('wpmm_before_scripts');

		if (!empty($scripts) && is_array($scripts)) {
			foreach ($scripts as $src) {
				?>
				<script src="<?php echo $src; ?>"></script>
				<?php
			}
		}
		// Do some actions
		do_action('wm_footer'); // this hook will be removed in the next versions
		do_action('wpmm_footer');
		?>
		<?php if (!empty($this->plugin_settings['bot']['status']) && $this->plugin_settings['bot']['status'] === 1) { ?>
			<script type='text/javascript'>
				jQuery(function($) {
					startConversation('homepage', 1);
				});
			</script>
		<?php } ?>
    </body>
</html>