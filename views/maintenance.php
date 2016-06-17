<!DOCTYPE html>
<html>
    <head>
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
        <div class="wrap">
            <?php if (!empty($heading)) { ?><h1><?php echo stripslashes($heading); ?></h1><?php } ?>
            <?php if (!empty($text)) { ?><h2><?php echo stripslashes($text); ?></h2><?php } ?>

            <?php
            if (!empty($this->plugin_settings['modules']['countdown_status']) && $this->plugin_settings['modules']['countdown_status'] == 1) {
                ?>
                <div class="countdown" data-start="<?php echo date('F d, Y H:i:s', strtotime($countdown_start)); ?>" data-end="<?php echo date('F d, Y H:i:s', $countdown_end); ?>"></div>
            <?php } ?>

            <?php if (!empty($this->plugin_settings['modules']['subscribe_status']) && $this->plugin_settings['modules']['subscribe_status'] == 1) { ?>
                <?php if (!empty($this->plugin_settings['modules']['subscribe_text'])) { ?><h3><?php echo stripslashes($this->plugin_settings['modules']['subscribe_text']); ?></h3><?php } ?>
                <div class="subscribe_wrapper" style="min-height: 100px;">
                    <form class="subscribe_form">
                        <input type="text" placeholder="<?php _e('your e-mail...', $this->plugin_slug); ?>" name="email" class="email_input" data-rule-required="true" data-rule-email="true" data-rule-required="true" data-rule-email="true" />
                        <input type="submit" value="<?php _e('Subscribe', $this->plugin_slug); ?>" />
                    </form>
                </div>
            <?php } ?>

            <?php if (!empty($this->plugin_settings['modules']['social_status']) && $this->plugin_settings['modules']['social_status'] == 1) { ?>
                <div class="social" data-target="<?php echo!empty($this->plugin_settings['modules']['social_target']) ? 1 : 0; ?>">
                    <?php if (!empty($this->plugin_settings['modules']['social_twitter'])) { ?>
                        <a class="tw" href="<?php echo stripslashes($this->plugin_settings['modules']['social_twitter']); ?>">twitter</a>
                    <?php } ?>

                    <?php if (!empty($this->plugin_settings['modules']['social_facebook'])) { ?>
                        <a class="fb" href="<?php echo stripslashes($this->plugin_settings['modules']['social_facebook']); ?>">facebook</a>
                    <?php } ?>    

                    <?php if (!empty($this->plugin_settings['modules']['social_pinterest'])) { ?>
                        <a class="pin" href="<?php echo stripslashes($this->plugin_settings['modules']['social_pinterest']); ?>">pinterest</a>
                    <?php } ?>  

                    <?php if (!empty($this->plugin_settings['modules']['social_github'])) { ?>
                        <a class="git" href="<?php echo stripslashes($this->plugin_settings['modules']['social_github']); ?>">github</a>
                    <?php } ?>

                    <?php if (!empty($this->plugin_settings['modules']['social_dribbble'])) { ?>
                        <a class="dribbble" href="<?php echo stripslashes($this->plugin_settings['modules']['social_dribbble']); ?>">dribbble</a>
                    <?php } ?>   

                    <?php if (!empty($this->plugin_settings['modules']['social_google+'])) { ?>
                        <a class="gplus" href="<?php echo stripslashes($this->plugin_settings['modules']['social_google+']); ?>">google plus</a>
                    <?php } ?>         

                    <?php if (!empty($this->plugin_settings['modules']['social_linkedin'])) { ?>
                        <a class="linkedin" href="<?php echo stripslashes($this->plugin_settings['modules']['social_linkedin']); ?>">linkedin</a>
                    <?php } ?>                         
                </div>
            <?php } ?>
            <?php if (!empty($this->plugin_settings['modules']['contact_status']) && $this->plugin_settings['modules']['contact_status'] == 1) { ?>
                <div class="contact">
                    <?php list($open, $close) = !empty($this->plugin_settings['modules']['contact_effects']) && strstr($this->plugin_settings['modules']['contact_effects'], '|') ? explode('|', $this->plugin_settings['modules']['contact_effects']) : explode('|', 'move_top|move_bottom'); ?>
                    <div class="form <?php echo esc_attr($open); ?>">
                        <form class="contact_form">
                            <p class="col"><input type="text" placeholder="<?php _e('Name', $this->plugin_slug); ?>" data-rule-required="true" data-msg-required="<?php esc_attr_e('This field is required.', $this->plugin_slug); ?>" name="name" class="name_input" /></p>
                            <p class="col last"><input type="text" placeholder="<?php _e('E-mail', $this->plugin_slug); ?>" data-rule-required="true" data-rule-email="true" data-msg-required="<?php esc_attr_e('This field is required.', $this->plugin_slug); ?>" data-msg-email="<?php esc_attr_e('Please enter a valid email address.', $this->plugin_slug); ?>" name="email" class="email_input" /></p>
                            <br clear="all" />
                            <p><textarea placeholder="<?php _e('Your message', $this->plugin_slug); ?>" data-rule-required="true" data-msg-required="<?php esc_attr_e('This field is required.', $this->plugin_slug); ?>" name="content" class="content_textarea"></textarea></p>
                            <p class="submit"><input type="submit" value="<?php _e('Send', $this->plugin_slug); ?>"></p>
                        </form>
                    </div>
                </div>                

                <a class="contact_us" href="javascript:void(0);" data-open="<?php echo esc_attr($open); ?>" data-close="<?php echo esc_attr($close); ?>"><?php _e('Contact us', $this->plugin_slug); ?></a>
            <?php } ?>

            <?php if (!empty($this->plugin_settings['general']['admin_link']) && $this->plugin_settings['general']['admin_link'] == 1) { ?>
                <div class="author_link">
                    <a href="<?php echo admin_url(); ?>"><?php _e('Dashboard', $this->plugin_slug); ?></a>
                </div>
            <?php } ?>    
        </div>

        <script type='text/javascript'>
            var wpmm_vars = {"ajax_url": "<?php echo admin_url('admin-ajax.php'); ?>"};
        </script>
        <?php
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
    </body>
</html>
