<div id="sidebar" class="wrapper-cell">
    <div class="sidebar_box info_box">
        <h3><?php _e('Plugin Info', $this->plugin_slug); ?></h3>
        <div class="inside">
            <?php $plugin_data = wpmm_plugin_info($this->plugin_slug); ?>
            <ul>
                <li>Name: 
                    <?php
                    echo!empty($plugin_data['Name']) ? $plugin_data['Name'] : '';
                    echo!empty($plugin_data['Version']) ? ' v' . $plugin_data['Version'] : '';
                    ?>
                </li>
                <li><?php _e('Author', $this->plugin_slug); ?>: <?php echo!empty($plugin_data['AuthorName']) ? $plugin_data['AuthorName'] : ''; ?></li>
                <li><?php _e('Website', $this->plugin_slug); ?>: <?php echo!empty($plugin_data['AuthorURI']) ? '<a href="' . $plugin_data['AuthorURI'] . WPMM_AUTHOR_UTM . '" target="_blank">' . $plugin_data['AuthorName'] . '</a>' : ''; ?></li>
                <li><?php _e('Twitter', $this->plugin_slug); ?>: <?php echo!empty($plugin_data['Twitter']) ? '<a href="http://twitter.com/' . $plugin_data['Twitter'] . '" target="_blank">@' . $plugin_data['Twitter'] . '</a>' : ''; ?></li>
                <li><?php _e('GitHub', $this->plugin_slug); ?>: <?php echo!empty($plugin_data['GitHub URI']) ? '<a href="' . $plugin_data['GitHub URI'] . '" target="_blank">' . basename($plugin_data['GitHub URI']) . '</a>' : ''; ?></li>
            </ul>
        </div>
    </div>

    <div class="sidebar_box subscribe_box">
        <h3><?php _e('Subscribe', $this->plugin_slug); ?></h3>
        <div class="inside">
            <?php _e('Subscribe now for early access to new WordPress themes.', $this->plugin_slug); ?>

            <form action="http://designmodo.us4.list-manage.com/subscribe/post?u=d7b5474e1bff23f08409d4ba9&amp;id=a2ae53acc1" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" id="cse-search-box">
                <input class="subscribe-input" type="text" name="EMAIL" id="mce-EMAIL" placeholder="<?php _e('Enter your email address', $this->plugin_slug); ?>" size="47" >
                <button type="submit" class="button button-secondary"><?php _e('Subscribe', $this->plugin_slug); ?></button>
            </form>
        </div>
    </div>

    <div class="sidebar_box news_box">
        <h3><?php _e('News', $this->plugin_slug); ?></h3>
        <div class="inside">
            <?php
            $items = get_designmodo_posts();
            if (!empty($items)) {
                ?>
                <ul>
                    <?php foreach ($items as $item) { ?>
                        <li>&raquo; <a href="<?php echo esc_url($item->get_permalink()); ?>" target="_blank"><?php echo esc_html($item->get_title()); ?></a></li>
                    <?php } ?>
                </ul>
            <?php } ?>
        </div>
    </div>

    <div class="sidebar_box resources_box">
        <h3><?php _e('Resources', $this->plugin_slug); ?></h3>
        <div class="inside">
            <ul>
                <li><a href="<?php echo 'http://designmodo.com/free-wordpress-theme/' . WPMM_AUTHOR_UTM; ?>" target="_blank"><img src="<?php echo WPMM_URL . 'assets/images/resources/ayoshop.jpg'; ?>" /></a></li>
                <li><a href="<?php echo 'http://designmodo.com/linecons-free/' . WPMM_AUTHOR_UTM; ?>" target="_blank"><img src="<?php echo WPMM_URL . 'assets/images/resources/linecons.jpg'; ?>" /></a></li>
                <li><a href="<?php echo 'http://designmodo.com/flat-free/' . WPMM_AUTHOR_UTM; ?>" target="_blank"><img src="<?php echo WPMM_URL . 'assets/images/resources/flatui.jpg'; ?>" /></a></li>               
            </ul>
        </div>
    </div>     
</div>