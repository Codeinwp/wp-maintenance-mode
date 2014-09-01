<!DOCTYPE html>
<html>
    <head>
        <title><?php echo $title; ?></title>
        <?php
        if (!empty($styles) && is_array($styles)) {
            foreach ($styles as $src) {
                ?>
                <link rel="stylesheet" href="<?php echo $src; ?>">
                <?php
            }
        }
        ?>
        <meta name="author" content="<?php echo $author; ?>" />
        <meta name="description" content="<?php echo $description; ?>" />
        <meta name="keywords" content="<?php echo $keywords; ?>" />
        <meta name="robots" content="<?php echo $robots; ?>" />
        <?php if (!empty($custom_css) && is_array($custom_css)) { ?>
            <style>
    <?php
    foreach ($custom_css as $css_line) {
        echo $css_line . "\n";
    }
    ?>
            </style>
            <?php
        }
        ?>
    </head>
    <body class="<?php echo $body_classes ? $body_classes : ''; ?>">
        <div class="wrap">
            <?php if (!empty($heading)) { ?><h1><?php echo $heading; ?></h1><?php } ?>
            <?php if (!empty($text)) { ?><h2><?php echo $text; ?></h2><?php } ?>

            <?php
            if (!empty($this->plugin_settings['modules']['countdown_status']) && $this->plugin_settings['modules']['countdown_status'] == 1) {
                ?>
                <div class="countdown" data-start="<?php echo date('F d, Y H:i:s', strtotime($countdown_start)); ?>" data-end="<?php echo date('F d, Y H:i:s', $countdown_end); ?>"></div>
            <?php } ?>

            <?php if (!empty($this->plugin_settings['modules']['subscribe_status']) && $this->plugin_settings['modules']['subscribe_status'] == 1) { ?>
                <?php if (!empty($this->plugin_settings['modules']['subscribe_text'])) { ?><h3><?php echo $this->plugin_settings['modules']['subscribe_text']; ?></h3><?php } ?>
                <div class="subscribe_wrapper" style="min-height: 100px;">
                    <form class="subscribe_form">
                        <input type="text" placeholder="<?php _e('your e-mail...', $this->plugin_slug); ?>" name="email" class="email_input" data-rule-required="true" data-rule-email="true" />
                        <input type="submit" value="<?php _e('Subscribe', $this->plugin_slug); ?>" />
                    </form>
                </div>
            <?php } ?>

            <?php if (!empty($this->plugin_settings['modules']['social_status']) && $this->plugin_settings['modules']['social_status'] == 1) { ?>
                <div class="social">
                    <?php if (!empty($this->plugin_settings['modules']['social_twitter'])) { ?>
                        <a class="tw" href="<?php echo $this->plugin_settings['modules']['social_twitter']; ?>">twitter</a>
                    <?php } ?>

                    <?php if (!empty($this->plugin_settings['modules']['social_facebook'])) { ?>
                        <a class="fb" href="<?php echo $this->plugin_settings['modules']['social_facebook']; ?>">facebook</a>
                    <?php } ?>    

                    <?php if (!empty($this->plugin_settings['modules']['social_pinterest'])) { ?>
                        <a class="pin" href="<?php echo $this->plugin_settings['modules']['social_pinterest']; ?>">pinterest</a>
                    <?php } ?>  

                    <?php if (!empty($this->plugin_settings['modules']['social_github'])) { ?>
                        <a class="git" href="<?php echo $this->plugin_settings['modules']['social_github']; ?>">github</a>
                    <?php } ?>

                    <?php if (!empty($this->plugin_settings['modules']['social_dribbble'])) { ?>
                        <a class="dribbble" href="<?php echo $this->plugin_settings['modules']['social_dribbble']; ?>">dribbble</a>
                    <?php } ?>   

                    <?php if (!empty($this->plugin_settings['modules']['social_google+'])) { ?>
                        <a class="gplus" href="<?php echo $this->plugin_settings['modules']['social_google+']; ?>">google plus</a>
                    <?php } ?>                         
                </div>
            <?php } ?>
            <?php if (!empty($this->plugin_settings['modules']['contact_status']) && $this->plugin_settings['modules']['contact_status'] == 1) { ?>
                <div class="contact">
                    <?php list($open, $close) = explode('|', $this->plugin_settings['modules']['contact_effects']); ?>
                    <div class="form <?php echo $open; ?>">
                        <form class="contact_form">
                            <p class="col"><input type="text" placeholder="<?php _e('Name', $this->plugin_slug); ?>" data-rule-required="true" name="name" class="name_input" /></p>
                            <p class="col last"><input type="text" placeholder="<?php _e('E-mail', $this->plugin_slug); ?>" data-rule-required="true" data-rule-email="true" name="email" class="email_input" /></p>
                            <br clear="all" />
                            <p><textarea placeholder="<?php _e('Your message', $this->plugin_slug); ?>" data-rule-required="true" name="content" class="content_textarea"></textarea></p>
                            <p class="submit"><input type="submit" value="<?php _e('Send', $this->plugin_slug); ?>"></p>
                        </form>
                    </div>
                </div>                

                <a class="contact_us" href="javascript:void(0);" data-open="<?php echo $open; ?>" data-close="<?php echo $close; ?>"><?php _e('Contact us', $this->plugin_slug); ?></a>
            <?php } ?>

            <?php if (!empty($this->plugin_settings['general']['author_link']) && $this->plugin_settings['general']['author_link'] == 1) { ?>
                <div class="author_link">
                    <?php echo sprintf(__('Developed by <a href="%s">Designmodo</a>', $this->plugin_slug), 'http://designmodo.com/' . WPMM_AUTHOR_UTM); ?>
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
        ?>
    </body>
</html>
