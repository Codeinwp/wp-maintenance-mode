<?php

if (!class_exists('WP_Maintenance_Mode_Admin')) {

    class WP_Maintenance_Mode_Admin {

        protected static $instance = null;
        protected $plugin_slug;
        protected $plugin_settings;
        protected $plugin_default_settings;
        protected $plugin_basename;
        protected $plugin_screen_hook_suffix = null;

        private function __construct() {
            $plugin = WP_Maintenance_Mode::get_instance();
            $this->plugin_slug = $plugin->get_plugin_slug();
            $this->plugin_settings = $plugin->get_plugin_settings();
            $this->plugin_default_settings = $plugin->default_settings();
            $this->plugin_basename = plugin_basename(WPMM_PATH . $this->plugin_slug . '.php');

            // Load admin style sheet and JavaScript.
            add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
            add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));

            // Add the options page and menu item.
            add_action('admin_menu', array($this, 'add_plugin_menu'));

            // Add an action link pointing to the options page
            if (is_multisite() && is_plugin_active_for_network($this->plugin_basename)) {
                // settings link will point to admin_url of the main blog, not to network_admin_url
                add_filter('network_admin_plugin_action_links_' . $this->plugin_basename, array($this, 'add_settings_link'));
            } else {
                add_filter('plugin_action_links_' . $this->plugin_basename, array($this, 'add_settings_link'));
            }

            // Add admin notices
            add_action('admin_notices', array($this, 'add_notices'));

            // Add ajax methods
            add_action('wp_ajax_wpmm_subscribers_export', array($this, 'subscribers_export'));
            add_action('wp_ajax_wpmm_reset_settings', array($this, 'reset_settings'));
        }

        public static function get_instance() {
            if (null == self::$instance) {
                self::$instance = new self;
            }

            return self::$instance;
        }

        /**
         * Load CSS files
         * 
         * @since 2.0.0
         * @global object $wp_scripts
         * @return type
         */
        public function enqueue_admin_styles() {
            global $wp_scripts;

            if (!isset($this->plugin_screen_hook_suffix)) {
                return;
            }

            $screen = get_current_screen();
            if ($this->plugin_screen_hook_suffix == $screen->id) {
                $ui = $wp_scripts->query('jquery-ui-core');

                wp_enqueue_style($this->plugin_slug . '-admin-jquery-ui-styles', '//ajax.googleapis.com/ajax/libs/jqueryui/' . (!empty($ui->ver) ? $ui->ver : '1.10.4') . '/themes/smoothness/jquery-ui.min.css', array(), WP_Maintenance_Mode::VERSION);
                wp_enqueue_style($this->plugin_slug . '-admin-styles', WPMM_CSS_URL . 'style-admin.css', array('wp-color-picker'), WP_Maintenance_Mode::VERSION);
            }
        }

        /**
         * Load JS files and their dependencies
         * 
         * @since 2.0.0
         * @return
         */
        public function enqueue_admin_scripts() {
            if (!isset($this->plugin_screen_hook_suffix)) {
                return;
            }

            $screen = get_current_screen();
            if ($this->plugin_screen_hook_suffix == $screen->id) {
                wp_enqueue_media();
                wp_enqueue_script($this->plugin_slug . '-admin-timepicker-addon-script', WPMM_JS_URL . 'jquery-ui-timepicker-addon.js', array('jquery', 'jquery-ui-datepicker'), WP_Maintenance_Mode::VERSION);
                wp_enqueue_script($this->plugin_slug . '-admin-script', WPMM_JS_URL . 'scripts-admin.js', array('jquery', 'wp-color-picker'), WP_Maintenance_Mode::VERSION);
                wp_localize_script($this->plugin_slug . '-admin-script', 'wpmm_vars', array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'plugin_url' => admin_url('options-general.php?page=' . $this->plugin_slug)
                ));
            }
        }

        /**
         * Export subscribers list in CSV format
         * 
         * @since 2.0.0
         * @global object $wpdb
         */
        public function subscribers_export() {
            global $wpdb;

            $results = $wpdb->get_results("SELECT email, insert_date FROM {$wpdb->prefix}wpmm_subscribers ORDER BY id_subscriber DESC", ARRAY_A);
            if (!empty($results)) {
                $filename = 'subscribers-list-' . date('Y-m-d') . '.csv';

                header('Content-Type: text/csv');
                header('Content-Disposition: attachment;filename=' . $filename);

                $fp = fopen('php://output', 'w');

                fputcsv($fp, array('email', 'insert_date'));
                foreach ($results as $item) {
                    fputcsv($fp, $item);
                }

                fclose($fp);
            }
        }

        /**
         * Reset settings
         * 
         * @since 2.0.0
         */
        public function reset_settings() {
            if (empty($_REQUEST['tab'])) {
                return false;
            }
            $tab = $_REQUEST['tab'];

            if (empty($this->plugin_default_settings[$tab])) {
                return false;
            }

            // OPTIONS UPDATE
            $this->plugin_settings[$tab] = $this->plugin_default_settings[$tab];
            update_option('wpmm_settings', $this->plugin_settings);

            wp_send_json(array('success' => 1));
        }

        /**
         * Add plugin in Settings menu
         * 
         * @since 2.0.0
         */
        public function add_plugin_menu() {
            $this->plugin_screen_hook_suffix = add_options_page(
                    __('WP Maintenance Mode', $this->plugin_slug), __('WP Maintenance Mode', $this->plugin_slug), 'manage_options', $this->plugin_slug, array($this, 'display_plugin_settings')
            );
        }

        /**
         * Settings page
         * 
         * @since 2.0.0
         * @global object $wp_roles
         */
        public function display_plugin_settings() {
            global $wp_roles;

            // save settings
            $this->save_plugin_settings();

            // show settings
            include_once(WPMM_VIEWS_PATH . 'settings.php');
        }

        /**
         * Save settings
         * 
         * @since 2.0.0
         */
        public function save_plugin_settings() {
            if (!empty($_POST) && !empty($_POST['tab'])) {
                if (!wp_verify_nonce($_POST['_wpnonce'], 'tab-' . $_POST['tab'])) {
                    die('Security check!');
                }

                // DO SOME SANITIZATIONS
                $tab = $_POST['tab'];
                switch ($tab) {
                    case 'general':
                        $_POST['options']['general']['status'] = (int) $_POST['options']['general']['status'];
                        if (!empty($_POST['options']['general']['status']) && $_POST['options']['general']['status'] == 1) {
                            $_POST['options']['general']['status_date'] = date('Y-m-d H:i:s');
                        }
                        $_POST['options']['general']['bypass_bots'] = (int) $_POST['options']['general']['bypass_bots'];
                        $_POST['options']['general']['backend_role'] = sanitize_text_field($_POST['options']['general']['backend_role']);
                        $_POST['options']['general']['frontend_role'] = sanitize_text_field($_POST['options']['general']['frontend_role']);
                        $_POST['options']['general']['meta_robots'] = (int) $_POST['options']['general']['meta_robots'];
                        $_POST['options']['general']['redirection'] = esc_url($_POST['options']['general']['redirection']);
                        if (!empty($_POST['options']['general']['exclude'])) {
                            $exclude_array = explode("\n", $_POST['options']['general']['exclude']);
                            $_POST['options']['general']['exclude'] = array_map('trim', $exclude_array);
                        } else {
                            $_POST['options']['general']['exclude'] = array();
                        }
                        $_POST['options']['general']['notice'] = (int) $_POST['options']['general']['notice'];
                        $_POST['options']['general']['admin_link'] = (int) $_POST['options']['general']['admin_link'];

                        // delete cache when is already activated, when is activated and when is deactivated
                        if (
                                isset($this->plugin_settings['general']['status']) && isset($_POST['options']['general']['status']) && 
                                (
                                ($this->plugin_settings['general']['status'] == 1 && in_array($_POST['options']['general']['status'], array(0, 1))) ||
                                ($this->plugin_settings['general']['status'] == 0 && $_POST['options']['general']['status'] == 1)
                                )
                        ) {
                            $this->delete_cache();
                        }
                        break;
                    case 'design':
                        $custom_css = array();

                        // CONTENT & CUSTOM CSS
                        $_POST['options']['design']['title'] = sanitize_text_field($_POST['options']['design']['title']);
                        $_POST['options']['design']['heading'] = sanitize_text_field($_POST['options']['design']['heading']);
                        if (!empty($_POST['options']['design']['heading_color'])) {
                            $_POST['options']['design']['heading_color'] = sanitize_text_field($_POST['options']['design']['heading_color']);
                            $custom_css['heading_color'] = '.wrap h1 { color: ' . $_POST['options']['design']['heading_color'] . '; }';
                        }
                        add_filter('safe_style_css', array($this, 'add_safe_style_css')); // add before we save
                        $_POST['options']['design']['text'] = wp_kses_post($_POST['options']['design']['text']);
                        remove_filter('safe_style_css', array($this, 'add_safe_style_css')); // remove after we save

                        if (!empty($_POST['options']['design']['text_color'])) {
                            $_POST['options']['design']['text_color'] = sanitize_text_field($_POST['options']['design']['text_color']);
                            $custom_css['text_color'] = '.wrap h2 { color: ' . $_POST['options']['design']['text_color'] . '; }';
                        }

                        // BACKGROUND & CUSTOM CSS
                        if (!empty($_POST['options']['design']['bg_type'])) {
                            $_POST['options']['design']['bg_type'] = sanitize_text_field($_POST['options']['design']['bg_type']);

                            if ($_POST['options']['design']['bg_type'] == 'color' && !empty($_POST['options']['design']['bg_color'])) {
                                $_POST['options']['design']['bg_color'] = sanitize_text_field($_POST['options']['design']['bg_color']);
                                $custom_css['bg_color'] = 'body { background-color: ' . $_POST['options']['design']['bg_color'] . '; }';
                            }

                            if ($_POST['options']['design']['bg_type'] == 'custom' && !empty($_POST['options']['design']['bg_custom'])) {
                                $_POST['options']['design']['bg_custom'] = esc_url($_POST['options']['design']['bg_custom']);
                                $custom_css['bg_url'] = '.background { background: url(' . $_POST['options']['design']['bg_custom'] . ') no-repeat center top fixed; background-size: cover; }';
                            }

                            if ($_POST['options']['design']['bg_type'] == 'predefined' && !empty($_POST['options']['design']['bg_predefined'])) {
                                $_POST['options']['design']['bg_predefined'] = sanitize_text_field($_POST['options']['design']['bg_predefined']);
                                $custom_css['bg_url'] = '.background { background: url(' . esc_url(WPMM_URL . 'assets/images/backgrounds/' . $_POST['options']['design']['bg_predefined']) . ') no-repeat center top fixed; background-size: cover; }';
                            }
                        }

                        $_POST['options']['design']['custom_css'] = $custom_css;

                        // delete cache when is activated
                        if (!empty($this->plugin_settings['general']['status']) && $this->plugin_settings['general']['status'] == 1) {
                            $this->delete_cache();
                        }
                        break;
                    case 'modules':
                        $custom_css = array();

                        // COUNTDOWN & CUSTOM CSS
                        $_POST['options']['modules']['countdown_status'] = (int) $_POST['options']['modules']['countdown_status'];
                        $_POST['options']['modules']['countdown_start'] = sanitize_text_field($_POST['options']['modules']['countdown_start']);
                        $_POST['options']['modules']['countdown_details'] = array_map('trim', $_POST['options']['modules']['countdown_details']);
                        $_POST['options']['modules']['countdown_details']['days'] = isset($_POST['options']['modules']['countdown_details']['days']) && is_numeric($_POST['options']['modules']['countdown_details']['days']) ? $_POST['options']['modules']['countdown_details']['days'] : 0;
                        $_POST['options']['modules']['countdown_details']['hours'] = isset($_POST['options']['modules']['countdown_details']['hours']) && is_numeric($_POST['options']['modules']['countdown_details']['hours']) ? $_POST['options']['modules']['countdown_details']['hours'] : 1;
                        $_POST['options']['modules']['countdown_details']['minutes'] = isset($_POST['options']['modules']['countdown_details']['minutes']) && is_numeric($_POST['options']['modules']['countdown_details']['minutes']) ? $_POST['options']['modules']['countdown_details']['minutes'] : 0;
                        if (!empty($_POST['options']['modules']['countdown_color'])) {
                            $_POST['options']['modules']['countdown_color'] = sanitize_text_field($_POST['options']['modules']['countdown_color']);
                            $custom_css['countdown_color'] = '.wrap .countdown span { color: ' . $_POST['options']['modules']['countdown_color'] . '; }';
                        }

                        // SUBSCRIBE & CUSTOM CSS
                        $_POST['options']['modules']['subscribe_status'] = (int) $_POST['options']['modules']['subscribe_status'];
                        $_POST['options']['modules']['subscribe_text'] = sanitize_text_field($_POST['options']['modules']['subscribe_text']);
                        if (!empty($_POST['options']['modules']['subscribe_text_color'])) {
                            $_POST['options']['modules']['subscribe_text_color'] = sanitize_text_field($_POST['options']['modules']['subscribe_text_color']);
                            $custom_css['subscribe_text_color'] = '.wrap h3, .wrap .subscribe_wrapper { color: ' . $_POST['options']['modules']['subscribe_text_color'] . '; }';
                        }

                        // SOCIAL NETWORKS
                        $_POST['options']['modules']['social_status'] = (int) $_POST['options']['modules']['social_status'];
                        $_POST['options']['modules']['social_target'] = (int) $_POST['options']['modules']['social_target'];
                        $_POST['options']['modules']['social_github'] = sanitize_text_field($_POST['options']['modules']['social_github']);
                        $_POST['options']['modules']['social_dribbble'] = sanitize_text_field($_POST['options']['modules']['social_dribbble']);
                        $_POST['options']['modules']['social_twitter'] = sanitize_text_field($_POST['options']['modules']['social_twitter']);
                        $_POST['options']['modules']['social_facebook'] = sanitize_text_field($_POST['options']['modules']['social_facebook']);
                        $_POST['options']['modules']['social_pinterest'] = sanitize_text_field($_POST['options']['modules']['social_pinterest']);
                        $_POST['options']['modules']['social_google+'] = sanitize_text_field($_POST['options']['modules']['social_google+']);
                        $_POST['options']['modules']['social_linkedin'] = sanitize_text_field($_POST['options']['modules']['social_linkedin']);

                        // CONTACT
                        $_POST['options']['modules']['contact_status'] = (int) $_POST['options']['modules']['contact_status'];
                        $_POST['options']['modules']['contact_email'] = sanitize_text_field($_POST['options']['modules']['contact_email']);
                        $_POST['options']['modules']['contact_effects'] = sanitize_text_field($_POST['options']['modules']['contact_effects']);

                        // GOOGLE ANALYTICS
                        $_POST['options']['modules']['ga_status'] = (int) $_POST['options']['modules']['ga_status'];
                        $_POST['options']['modules']['ga_code'] = wp_kses(trim($_POST['options']['modules']['ga_code']), array('script' => array()));

                        $_POST['options']['modules']['custom_css'] = $custom_css;

                        // delete cache when is activated
                        if (!empty($this->plugin_settings['general']['status']) && $this->plugin_settings['general']['status'] == 1) {
                            $this->delete_cache();
                        }
                        break;
                }

                $this->plugin_settings[$tab] = $_POST['options'][$tab];
                update_option('wpmm_settings', $this->plugin_settings);
            }
        }

        /**
         * Add new safe inline style css (use by wp_kses_attr in wp_kses_post)
         * - bug discovered by cokemorgan: https://github.com/Designmodocom/WP-Maintenance-Mode/issues/56
         * 
         * @since 2.0.3
         * @param array $properties
         * @return array
         */
        public function add_safe_style_css($properties) {
            $new_properties = array(
                'min-height',
                'max-height',
                'min-width',
                'max-width'
            );

            return array_merge($new_properties, $properties);
        }

        /**
         * Delete cache if any cache plugin (wp_cache or w3tc) is activated
         * 
         * @since 2.0.1
         */
        public function delete_cache() {
            // Super Cache Plugin
            if (function_exists('wp_cache_clear_cache')) {
                wp_cache_clear_cache(is_multisite() && is_plugin_active_for_network($this->plugin_basename) ? get_current_blog_id() : '');
            }

            // W3 Total Cache Plugin
            if (function_exists('w3tc_pgcache_flush')) {
                w3tc_pgcache_flush();
            }
        }

        /**
         * Add settings link
         * 
         * @since 2.0.0
         * @param array $links
         * @return array
         */
        public function add_settings_link($links) {
            return array_merge(
                    array(
                'wpmm_settings' => '<a href="' . admin_url('options-general.php?page=' . $this->plugin_slug) . '">' . __('Settings', $this->plugin_slug) . '</a>'
                    ), $links
            );
        }

        /**
         * Add notices - will be displayed on dashboard
         * 
         * @since 2.0.0
         */
        public function add_notices() {
            $screen = get_current_screen();
            $notices = array();

            if ($this->plugin_screen_hook_suffix != $screen->id) {
                // notice if plugin is activated
                if ($this->plugin_settings['general']['status'] == 1 && $this->plugin_settings['general']['notice'] == 1) {
                    $notices[] = array(
                        'class' => 'error',
                        'msg' => sprintf(__('The Maintenance Mode is <strong>active</strong>. Please don\'t forget to <a href="%s">deactivate</a> as soon as you are done.', $this->plugin_slug), admin_url('options-general.php?page=' . $this->plugin_slug))
                    );
                }

                // show notice if plugin has a notice saved
                $wpmm_notice = get_option('wpmm_notice');
                if (!empty($wpmm_notice) && is_array($wpmm_notice)) {
                    $notices[] = $wpmm_notice;
                }

                // template
                include_once(WPMM_VIEWS_PATH . 'notice.php');
            } else {
                // delete wpmm_notice
                delete_option('wpmm_notice');
            }
        }

    }

}