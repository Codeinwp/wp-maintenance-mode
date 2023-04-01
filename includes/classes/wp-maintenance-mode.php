<?php

use ThemeIsle\GutenbergBlocks\CSS\Block_Frontend;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_Maintenance_Mode' ) ) {

	class WP_Maintenance_Mode {

		const VERSION = '2.6.7';

		const MAINTENANCE  = 'maintenance';
		const COMING_SOON  = 'coming-soon';
		const LANDING_PAGE = 'landing-page';

		protected $plugin_slug = 'wp-maintenance-mode';
		protected $plugin_settings;
		protected $plugin_network_settings = array(
			'general' => array(
				'status'       => 0,
				'network_mode' => 0,
			),
		);
		protected $plugin_basename;
		protected static $instance = null;

		private $style_buffer;
		private $current_page_category;

		/**
		 * 3, 2, 1... Start!
		 */
		private function __construct() {
			if ( ! get_option( 'wpmm_settings' ) || get_option( 'wpmm_settings' ) === '' ) {
				update_option( 'wpmm_show_migration', '0' );
				update_option( 'wpmm_new_look', '1' );

				if ( version_compare( $GLOBALS['wp_version'], '5.8', '>=' ) ) {
					update_option( 'wpmm_fresh_install', '1' );
				}
			}

			if ( get_option( 'wpmm_migration_time' ) && ( ( time() - intval( get_option( 'wpmm_migration_time' ) ) > WEEK_IN_SECONDS ) ) ) {
				update_option( 'wpmm_show_migration', '0' );
			}

			$this->plugin_settings = wpmm_get_option( 'wpmm_settings', array() );
			$this->plugin_basename = plugin_basename( WPMM_PATH . $this->plugin_slug . '.php' );

			if ( is_multisite() ) {
				$plugin_network_settings       = get_network_option( get_current_network_id(), 'wpmm_settings_network', $this->plugin_network_settings );
				$plugin_network_settings       = array_filter( $plugin_network_settings );
				$this->plugin_network_settings = wp_parse_args( $plugin_network_settings, $this->plugin_network_settings );
				if ( ! isset( $this->plugin_network_settings['general']['network_mode'] ) ) {
					$this->plugin_network_settings['general']['network_mode'] = 0;
				}
				if ( ! empty( $this->plugin_network_settings ) ) {
					$this->plugin_settings['general']['network_mode'] = ! empty( $this->plugin_network_settings['general']['network_mode'] ) ? 1 : 0;
					if ( $this->plugin_settings['general']['network_mode'] ) {
						$this->plugin_settings['general']['status'] = ! empty( $this->plugin_network_settings['general']['status'] ) ? 1 : 0;
					}
				}
			}

			// Load plugin text domain
			add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

			// Add shortcodes
			add_action( 'init', array( 'WP_Maintenance_Mode_Shortcodes', 'init' ) );

			// Activate plugin when new blog is added
			$new_blog_action = isset( $GLOBALS['wp_version'] ) && version_compare( $GLOBALS['wp_version'], '5.1-RC', '>=' ) ? 'wp_initialize_site' : 'wpmu_new_blog';
			add_action( $new_blog_action, array( $this, 'activate_new_site' ), 11, 1 );

			// Check update
			add_action( 'admin_init', array( $this, 'check_update' ) );

			// Add maintenance page template
			add_filter( 'theme_page_templates', array( $this, 'add_maintenance_template' ) );
			add_filter( 'template_include', array( $this, 'use_maintenance_template' ) );

			// This is a fix for some styles not being loaded on block themes
			if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) {
				add_action( 'wpmm_head', array( $this, 'remember_style_fse' ) );
				add_action( 'wpmm_footer', array( $this, 'add_style_fse' ) );
			}

			if ( ! empty( $this->plugin_settings['general']['status'] ) && $this->plugin_settings['general']['status'] === 1 ) {
				// INIT
				add_action( ( is_admin() ? 'init' : 'template_redirect' ), array( $this, 'init' ) );

				// Add ajax methods
				add_action( 'wp_ajax_nopriv_wpmm_add_subscriber', array( $this, 'add_subscriber' ) );
				add_action( 'wp_ajax_wpmm_add_subscriber', array( $this, 'add_subscriber' ) );
				add_action( 'wp_ajax_nopriv_wpmm_send_contact', array( $this, 'send_contact' ) );
				add_action( 'wp_ajax_wpmm_send_contact', array( $this, 'send_contact' ) );
				add_action( 'otter_form_after_submit', array( $this, 'otter_add_subscriber' ) );

				if ( isset( $this->plugin_settings['design']['page_id'] ) && get_option( 'wpmm_new_look' ) && get_post_status( $this->plugin_settings['design']['page_id'] ) === 'private' ) {
					wp_publish_post( $this->plugin_settings['design']['page_id'] );
				}

				update_option( 'show_on_front', 'page' );
				add_filter(
					'pre_option_page_on_front',
					function ( $value ) {
						if ( ( ! $this->check_user_role() && ! $this->check_exclude() ) && isset( $this->plugin_settings['design']['page_id'] ) && get_option( 'wpmm_new_look' ) ) {
							$page_id = $this->plugin_settings['design']['page_id'];

							if ( ! function_exists( 'is_plugin_active' ) ) {
								include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
							}

							if ( is_plugin_active( 'otter-blocks/otter-blocks.php' ) ) {
								Block_Frontend::instance()->enqueue_google_fonts( $page_id );
							}

							return $page_id;
						}

						return $value;
					}
				);

				// Redirect
				add_action( 'init', array( $this, 'redirect' ), 9 );

				// Enqueue CSS files and add inline css
				add_action( 'wpmm_head', array( $this, 'add_css_files' ) );
				add_action( 'wpmm_head', array( $this, 'add_inline_css_style' ), 11 );

				// Google Analytics tracking script
				add_action( 'wpmm_head', array( $this, 'add_google_analytics_code' ) );

				// Enqueue Javascript files and add inline javascript
				add_action( 'wpmm_before_scripts', array( $this, 'add_bot_extras' ) );
				add_action( 'wpmm_footer', array( $this, 'add_js_files' ) );
			} else {
				// make maintenance page private when maintenance mode is disabled
				add_action(
					'init',
					function() {
						if ( ! isset( $this->plugin_settings['design']['page_id'] ) ) {
							return;
						}
						if ( get_post_status( $this->plugin_settings['design']['page_id'] ) === 'publish' ) {
							wp_update_post(
								array(
									'ID'          => $this->plugin_settings['design']['page_id'],
									'post_status' => 'private',
								)
							);
						}
					}
				);
			}
		}

		/**
		 * Singleton
		 *
		 * @return object
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Return plugin slug
		 *
		 * @since 2.0.0
		 * @return string
		 */
		public function get_plugin_slug() {
			return $this->plugin_slug;
		}

		/**
		 * Return plugin settings
		 *
		 * @since 2.0.0
		 * @return array
		 */
		public function get_plugin_settings() {
			return $this->plugin_settings;
		}

		/**
		 * Return plugin network site settings
		 *
		 * @since 2.6.2
		 * @return array
		 */
		public function get_plugin_network_settings() {
			return $this->plugin_network_settings;
		}

		/**
		 * Return the plugin's page categories
		 *
		 * @return array
		 */
		public static function get_page_categories() {
			return array(
				self::COMING_SOON  => __( 'Coming Soon', 'wp-maintenance-mode' ),
				self::MAINTENANCE  => __( 'Maintenance mode', 'wp-maintenance-mode' ),
				self::LANDING_PAGE => __( 'Landing Page', 'wp-maintenance-mode' ),
			);
		}

		/**
		 * Return the plugin's page categories with labels
		 *
		 * @return string
		 */
		public static function get_page_status_by_category( $category ) {
			switch ( $category ) {
				case self::MAINTENANCE:
					return __( 'Maintenance Page', 'wp-maintenance-mode' );
				case self::COMING_SOON:
					return __( 'Coming Soon Page', 'wp-maintenance-mode' );
				case self::LANDING_PAGE:
					return __( 'Landing Page', 'wp-maintenance-mode' );
			}
		}

		/**
		 * Return plugin default settings
		 *
		 * @since 2.0.0
		 * @return array
		 */
		public function default_settings() {
			return array(
				'general' => array(
					'status'        => 0,
					'status_date'   => '',
					'bypass_bots'   => 0,
					'backend_role'  => array(),
					'frontend_role' => array(),
					'meta_robots'   => 0,
					'redirection'   => '',
					'exclude'       => array(
						0 => 'feed',
						1 => 'wp-login',
						2 => 'login',
					),
					'notice'        => 1,
					'admin_link'    => 0,
				),
				'design'  => array(
					'title'              => _x( 'Maintenance mode', '<title> default', 'wp-maintenance-mode' ),
					'heading'            => _x( 'Maintenance mode', 'heading default', 'wp-maintenance-mode' ),
					'heading_color'      => '',
					'text'               => __( '<p>Sorry for the inconvenience.<br />Our website is currently undergoing scheduled maintenance.<br />Thank you for your understanding.</p>', 'wp-maintenance-mode' ),
					'text_color'         => '',
					'footer_links_color' => '',
					'bg_type'            => 'color',
					'bg_color'           => '',
					'bg_custom'          => '',
					'bg_predefined'      => 'bg1.jpg',
					'other_custom_css'   => '',
					'template_category'  => 'all',
				),
				'modules' => array(
					'countdown_status'     => 0,
					'countdown_start'      => date( 'Y-m-d H:i:s' ),
					'countdown_details'    => array(
						'days'    => 0,
						'hours'   => 1,
						'minutes' => 0,
					),
					'countdown_color'      => '',
					'subscribe_status'     => 0,
					'subscribe_text'       => __( 'Notify me when it\'s ready', 'wp-maintenance-mode' ),
					'subscribe_text_color' => '',
					'social_status'        => 0,
					'social_target'        => 1,
					'social_github'        => '',
					'social_dribbble'      => '',
					'social_twitter'       => '',
					'social_facebook'      => '',
					'social_instagram'     => '',
					'social_pinterest'     => '',
					'social_google+'       => '',
					'social_linkedin'      => '',
					'contact_status'       => 0,
					'contact_email'        => get_option( 'admin_email' ) ? get_option( 'admin_email' ) : '',
					'contact_effects'      => 'move_top|move_bottom',
					'ga_status'            => 0,
					'ga_anonymize_ip'      => 0,
					'ga_code'              => '',
				),
				'bot'     => array(
					'status'    => 0,
					'name'      => 'Admin',
					'avatar'    => '',
					'messages'  => array(
						'01'   => __( 'Hey! My name is {bot_name}, I\'m the owner of this website and I\'d like to be your assistant here.', 'wp-maintenance-mode' ),
						'02'   => __( 'I have just a few questions.', 'wp-maintenance-mode' ),
						'03'   => __( 'What is your name?', 'wp-maintenance-mode' ),
						'04'   => __( 'Nice to meet you here, {visitor_name}!', 'wp-maintenance-mode' ),
						'05'   => __( 'How you can see, our website will be launched very soon.', 'wp-maintenance-mode' ),
						'06'   => __( 'I know, you are very excited to see it, but we need a few days to finish it.', 'wp-maintenance-mode' ),
						'07'   => __( 'Would you like to be first to see it?', 'wp-maintenance-mode' ),
						'08_1' => __( 'Cool! Please leave your email here and I will send you a message when it\'s ready.', 'wp-maintenance-mode' ),
						'08_2' => __( 'Sad to hear that, {visitor_name} :( See you next time…', 'wp-maintenance-mode' ),
						'09'   => __( 'Got it! Thank you and see you soon here!', 'wp-maintenance-mode' ),
						'10'   => __( 'Have a great day!', 'wp-maintenance-mode' ),
					),
					'responses' => array(
						'01'   => __( 'Type your name here…', 'wp-maintenance-mode' ),
						'02_1' => __( 'Tell me more', 'wp-maintenance-mode' ),
						'02_2' => __( 'Boring', 'wp-maintenance-mode' ),
						'03'   => __( 'Type your email here…', 'wp-maintenance-mode' ),
					),
				),
				'gdpr'    => array(
					'status'              => 0,
					'policy_page_label'   => __( 'Privacy Policy', 'wp-maintenance-mode' ),
					'policy_page_link'    => '',
					'policy_page_target'  => 0,
					'contact_form_tail'   => __( 'This form collects your name and email so that we can reach you back. Check out our <a href="#">Privacy Policy</a> page to fully understand how we protect and manage your submitted data.', 'wp-maintenance-mode' ),
					'subscribe_form_tail' => __( 'This form collects your email so that we can add you to our newsletter list. Check out our <a href="#">Privacy Policy</a> page to fully understand how we protect and manage your submitted data.', 'wp-maintenance-mode' ),
				),
			);
		}

		/**
		 * What to do when the plugin is activated
		 *
		 * @since 2.0.0
		 * @param boolean $network_wide
		 */
		public static function activate( $network_wide ) {
			// because we need translated items when activate :)
			load_plugin_textdomain( self::get_instance()->plugin_slug, false, WPMM_LANGUAGES_PATH );

			// do the job
			if ( function_exists( 'is_multisite' ) && is_multisite() ) {
				if ( $network_wide ) {
					// Get all blog ids
					$blog_ids = self::get_blog_ids();
					foreach ( $blog_ids as $blog_id ) {
						switch_to_blog( $blog_id );
						self::single_activate( $network_wide );
						restore_current_blog();
					}
				} else {
					self::single_activate();
				}
			} else {
				self::single_activate();
			}

			update_option( 'wpmm_activated', time() );

			// delete old options
			delete_option( 'wp-maintenance-mode' );
			delete_option( 'wp-maintenance-mode-msqld' );
		}

		/**
		 * Check plugin version for updating process
		 *
		 * @since 2.0.3
		 */
		public function check_update() {
			$version = get_option( 'wpmm_version', '0' );

			if ( ! version_compare( $version, self::VERSION, '=' ) ) {
				self::activate( is_multisite() && is_plugin_active_for_network( $this->plugin_basename ) );
			}
		}

		/**
		 * What to do when the plugin is deactivated
		 *
		 * @since 2.0.0
		 * @param boolean $network_wide
		 */
		public static function deactivate( $network_wide ) {
			if ( function_exists( 'is_multisite' ) && is_multisite() ) {
				if ( $network_wide ) {
					// Get all blog ids
					$blog_ids = self::get_blog_ids();
					foreach ( $blog_ids as $blog_id ) {
						switch_to_blog( $blog_id );
						self::single_deactivate();
						restore_current_blog();
					}
				} else {
					self::single_deactivate();
				}
			} else {
				self::single_deactivate();
			}
		}

		/**
		 * What to do when a new site is activated (multisite env)
		 *
		 * @since 2.0.0
		 * @param int|object $blog
		 */
		public function activate_new_site( $blog ) {
			$current_action = current_action();

			if ( 1 !== did_action( $current_action ) ) {
				return;
			}

			$blog_id = is_object( $blog ) ? $blog->id : $blog;

			switch_to_blog( $blog_id );
			self::single_activate();
			restore_current_blog();
		}

		/**
		 * What to do on single activate
		 *
		 * @since 2.0.0
		 * @global object $wpdb
		 * @param boolean $network_wide
		 */
		public static function single_activate( $network_wide = false ) {
			global $wpdb;

			// create wpmm_subscribers table
			$sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wpmm_subscribers (
                    `id_subscriber` bigint(20) NOT NULL AUTO_INCREMENT,
                    `email` varchar(50) NOT NULL,
                    `insert_date` datetime NOT NULL,
                    PRIMARY KEY (`id_subscriber`)
                  ) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );

			// get all options for different versions of the plugin
			$v2_options      = get_option( 'wpmm_settings' );
			$old_options     = ( is_multisite() && $network_wide ) ? get_site_option( 'wp-maintenance-mode' ) : get_option( 'wp-maintenance-mode' );
			$default_options = self::get_instance()->default_settings();

			/**
			 * Update from v1.8 to v2.x
			 *
			 * -  set notice if the plugin was installed before & set default settings
			 */
			if ( ! empty( $old_options ) && empty( $v2_options ) ) {
				add_option(
					'wpmm_notice',
					array(
						'class' => 'updated notice',
						'msg'   => sprintf(
									/* translators: plugin settings url */
							__( 'WP Maintenance Mode plugin was relaunched and you MUST revise <a href="%s">settings</a>.', 'wp-maintenance-mode' ),
							add_query_arg( array( 'page' => self::get_instance()->plugin_slug ), admin_url( 'options-general.php' ) )
						),
					)
				);

				// import old options
				if ( isset( $old_options['active'] ) ) {
					$default_options['general']['status'] = $old_options['active'];
				}
				if ( isset( $old_options['bypass'] ) ) {
					$default_options['general']['bypass_bots'] = $old_options['bypass'];
				}

				if ( ! empty( $old_options['role'][0] ) ) {
					$default_options['general']['backend_role'] = $old_options['role'][0] === 'administrator' ? array() : $old_options['role'];
				}

				if ( ! empty( $old_options['role_frontend'][0] ) ) {
					$default_options['general']['frontend_role'] = $old_options['role_frontend'][0] === 'administrator' ? array() : $old_options['role_frontend'];
				}

				if ( isset( $old_options['index'] ) ) {
					$default_options['general']['meta_robots'] = $old_options['index'];
				}

				if ( ! empty( $old_options['rewrite'] ) ) {
					$default_options['general']['redirection'] = $old_options['rewrite'];
				}

				if ( ! empty( $old_options['exclude'][0] ) ) {
					$default_options['general']['exclude'] = array_unique( array_merge( $default_options['general']['exclude'], $old_options['exclude'] ) );
				}

				if ( isset( $old_options['notice'] ) ) {
					$default_options['general']['notice'] = $old_options['notice'];
				}

				if ( isset( $old_options['admin_link'] ) ) {
					$default_options['general']['admin_link'] = $old_options['admin_link'];
				}

				if ( ! empty( $old_options['title'] ) ) {
					$default_options['design']['title'] = $old_options['title'];
				}

				if ( ! empty( $old_options['heading'] ) ) {
					$default_options['design']['heading'] = $old_options['heading'];
				}

				if ( ! empty( $old_options['text'] ) ) {
					$default_options['design']['text'] = $old_options['text'];
				}

				if ( isset( $old_options['radio'] ) ) {
					$default_options['modules']['countdown_status'] = $old_options['radio'];
				}

				if ( ! empty( $old_options['date'] ) ) {
					$default_options['modules']['countdown_start'] = $old_options['date'];
				}

				if ( isset( $old_options['time'] ) && isset( $old_options['unit'] ) ) {
					switch ( $old_options['unit'] ) {
						case 0: // seconds
							$default_options['modules']['countdown_details'] = array(
								'days'    => 0,
								'hours'   => 0,
								'minutes' => floor( $old_options['time'] / 60 ),
							);
							break;
						case 1: // minutes
							$default_options['modules']['countdown_details'] = array(
								'days'    => 0,
								'hours'   => 0,
								'minutes' => $old_options['time'],
							);
							break;
						case 2: // hours
							$default_options['modules']['countdown_details'] = array(
								'days'    => 0,
								'hours'   => $old_options['time'],
								'minutes' => 0,
							);
							break;
						case 3: // days
							$default_options['modules']['countdown_details'] = array(
								'days'    => $old_options['time'],
								'hours'   => 0,
								'minutes' => 0,
							);
							break;
						case 4: // weeks
							$default_options['modules']['countdown_details'] = array(
								'days'    => $old_options['time'] * 7,
								'hours'   => 0,
								'minutes' => 0,
							);
							break;
						case 5: // months
							$default_options['modules']['countdown_details'] = array(
								'days'    => $old_options['time'] * 30,
								'hours'   => 0,
								'minutes' => 0,
							);
							break;
						case 6: // years
							$default_options['modules']['countdown_details'] = array(
								'days'    => $old_options['time'] * 365,
								'hours'   => 0,
								'minutes' => 0,
							);
							break;
						default:
							break;
					}
				}
			}

			/**
			 * Set options on first activation
			 */
			if ( empty( $v2_options ) ) {
				$v2_options = $default_options;

				// set options
				add_option( 'wpmm_settings', $v2_options );
			}

			$should_update = false;

			/**
			 * Update from <= v2.0.6 to v2.0.7
			 */
			if ( ! empty( $v2_options['modules']['ga_code'] ) ) {
				$v2_options['modules']['ga_code'] = wpmm_sanitize_ga_code( $v2_options['modules']['ga_code'] );

				// update options
				update_option( 'wpmm_settings', $v2_options );
			}

			/**
			 * Update from <= v2.09 to v^2.1.2
			 */
			if ( empty( $v2_options['bot'] ) ) {
				$v2_options['bot'] = $default_options['bot'];

				// update options
				update_option( 'wpmm_settings', $v2_options );
			}

			/**
			 * Update from <= v2.1.2 to 2.1.5
			 */
			if ( empty( $v2_options['gdpr'] ) ) {
				$v2_options['gdpr'] = $default_options['gdpr'];

				// update options
				update_option( 'wpmm_settings', $v2_options );
			}

			/**
			 * Update from <= v2.2.1 to 2.2.2
			 */
			if ( empty( $v2_options['modules']['ga_anonymize_ip'] ) ) {
				$v2_options['modules']['ga_anonymize_ip'] = $default_options['modules']['ga_anonymize_ip'];

				// update options
				update_option( 'wpmm_settings', $v2_options );
			}

			if ( empty( $v2_options['gdpr']['policy_page_target'] ) ) {
				$v2_options['gdpr']['policy_page_target'] = $default_options['gdpr']['policy_page_target'];

				// update options
				update_option( 'wpmm_settings', $v2_options );
			}

			/**
			 * Update from <= v2.3.0 to 2.4.0
			 */
			if ( empty( $v2_options['design']['other_custom_css'] ) ) {
				$v2_options['design']['other_custom_css'] = $default_options['design']['other_custom_css'];

				// update options
				update_option( 'wpmm_settings', $v2_options );
			}

			if ( empty( $v2_options['design']['footer_links_color'] ) ) {
				$v2_options['design']['footer_links_color'] = $default_options['design']['footer_links_color'];

				// update options
				update_option( 'wpmm_settings', $v2_options );
			}

			// set current version
			update_option( 'wpmm_version', self::VERSION );
		}

		/**
		 * What to do on single deactivate
		 *
		 * @since 2.0.0
		 */
		public static function single_deactivate() {
			wpmm_delete_cache();
		}

		/**
		 * Get all blog ids of blogs in the current network
		 *
		 * @since 2.0.0
		 * @return array / false
		 */
		private static function get_blog_ids() {
			global $wpdb;

			return $wpdb->get_col( $wpdb->prepare( "SELECT blog_id FROM {$wpdb->blogs} WHERE archived = %d AND spam = %d AND deleted = %d", array( 0, 0, 0 ) ) );
		}

		/**
		 * Load languages files
		 *
		 * @since 2.0.0
		 */
		public function load_plugin_textdomain() {
			$domain = $this->plugin_slug;
			$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

			load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
			load_plugin_textdomain( $domain, false, WPMM_LANGUAGES_PATH );
		}

		/**
		 * Initialize when plugin is activated
		 *
		 * @since 2.0.0
		 */
		public function init() {
			/**
			 * CHECKS
			 */
			if (
					( ! $this->check_user_role() ) &&
					! strstr( $_SERVER['PHP_SELF'], 'wp-cron.php' ) &&
					! strstr( $_SERVER['PHP_SELF'], 'wp-login.php' ) &&
					// wp-admin/ is available to everyone only if the user is not loggedin, otherwise.. check_user_role decides
					! ( strstr( $_SERVER['PHP_SELF'], 'wp-admin/' ) && ! is_user_logged_in() ) &&
					! strstr( $_SERVER['PHP_SELF'], 'wp-admin/admin-ajax.php' ) &&
					! strstr( $_SERVER['PHP_SELF'], 'async-upload.php' ) &&
					! ( strstr( $_SERVER['PHP_SELF'], 'upgrade.php' ) && $this->check_user_role() ) &&
					! strstr( $_SERVER['PHP_SELF'], '/plugins/' ) &&
					! strstr( $_SERVER['PHP_SELF'], '/xmlrpc.php' ) &&
					! $this->check_exclude() &&
					! $this->check_search_bots() &&
					! ( defined( 'WP_CLI' ) && WP_CLI )
			) {
				if ( isset( $this->plugin_settings['design']['page_id'] ) && get_option( 'wpmm_new_look' ) ) {
					define( 'IS_MAINTENANCE', true );
					include_once wpmm_get_template_path( 'maintenance.php', true );
					return;
				}

				// HEADER STUFF
				$protocol         = ! empty( $_SERVER['SERVER_PROTOCOL'] ) && in_array( $_SERVER['SERVER_PROTOCOL'], array( 'HTTP/1.1', 'HTTP/1.0' ), true ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0';
				$charset          = get_bloginfo( 'charset' ) ? get_bloginfo( 'charset' ) : 'UTF-8';
				$status_code      = (int) apply_filters( 'wp_maintenance_mode_status_code', 503 ); // this hook will be removed in the next versions
				$status_code      = (int) apply_filters( 'wpmm_status_code', $status_code );
				$backtime_seconds = $this->calculate_backtime();
				$backtime         = (int) apply_filters( 'wpmm_backtime', $backtime_seconds );

				// META STUFF
				$title = ! empty( $this->plugin_settings['design']['title'] ) ? $this->plugin_settings['design']['title'] : get_bloginfo( 'name' ) . ' - ' . __( 'Maintenance Mode', 'wp-maintenance-mode' );
				$title = apply_filters( 'wm_title', $title ); // this hook will be removed in the next versions
				$title = apply_filters( 'wpmm_meta_title', $title );

				$robots = $this->plugin_settings['general']['meta_robots'] === 1 ? 'noindex, nofollow' : 'index, follow';
				$robots = apply_filters( 'wpmm_meta_robots', $robots );

				$author = apply_filters( 'wm_meta_author', get_bloginfo( 'name' ) ); // this hook will be removed in the next versions
				$author = apply_filters( 'wpmm_meta_author', $author );

				$description = get_bloginfo( 'name' ) . ' - ' . get_bloginfo( 'description' );
				$description = apply_filters( 'wm_meta_description', $description ); // this hook will be removed in the next versions
				$description = apply_filters( 'wpmm_meta_description', $description );

				$keywords = _x( 'Maintenance Mode', '<meta> keywords default', 'wp-maintenance-mode' );
				$keywords = apply_filters( 'wm_meta_keywords', $keywords ); // this hook will be removed in the next versions
				$keywords = apply_filters( 'wpmm_meta_keywords', $keywords );

				// CSS STUFF
				$body_classes = ! empty( $this->plugin_settings['design']['bg_type'] ) && $this->plugin_settings['design']['bg_type'] !== 'color' ? 'background' : '';

				if ( ! empty( $this->plugin_settings['bot']['status'] ) && $this->plugin_settings['bot']['status'] === 1 ) {
					$body_classes .= ' bot';
				}

				// CONTENT
				$heading = ! empty( $this->plugin_settings['design']['heading'] ) ? $this->plugin_settings['design']['heading'] : '';
				$heading = apply_filters( 'wm_heading', $heading ); // this hook will be removed in the next versions
				$heading = apply_filters( 'wpmm_heading', $heading );

				$text = ! empty( $this->plugin_settings['design']['text'] ) ? wp_kses_post( $this->plugin_settings['design']['text'] ) : '';
				$text = apply_filters( 'wpmm_text', wpmm_do_shortcode( $text ) );

				// COUNTDOWN
				$countdown_start = ! empty( $this->plugin_settings['modules']['countdown_start'] ) ? $this->plugin_settings['modules']['countdown_start'] : $this->plugin_settings['general']['status_date'];
				$countdown_end   = strtotime( $countdown_start . ' +' . $backtime_seconds . ' seconds' );

				wpmm_set_nocache_constants();
				nocache_headers();

				ob_start();
				header( "Content-type: text/html; charset=$charset" );
				header( "$protocol $status_code Service Unavailable", true, $status_code );
				header( "Retry-After: $backtime" );

				// load maintenance mode template
				include_once wpmm_get_template_path( 'maintenance.php', true );
				ob_flush();
			}
		}

		/**
		 * Extra variables for the bot functionality. Added to the DOM via hooks.
		 * It has to be called before scripts are loaded so the variables are available globally.
		 *
		 * @todo Maybe we can find a better home for this method
		 * @since 2.1.1
		 */
		public function add_bot_extras() {
			if ( empty( $this->plugin_settings['bot']['status'] ) || $this->plugin_settings['bot']['status'] !== 1 ) {
				return;
			}

			$upload_dir = wp_upload_dir();
			$bot_vars   = array(
				'validationName'  => __( 'Please type in your name.', 'wp-maintenance-mode' ),
				'validationEmail' => __( 'Please type in a valid email address.', 'wp-maintenance-mode' ),
				'uploadsBaseUrl'  => trailingslashit( $upload_dir['baseurl'] ),
				'typeName'        => ! empty( $this->plugin_settings['bot']['responses']['01'] ) ? $this->plugin_settings['bot']['responses']['01'] : __( 'Type your name here…', 'wp-maintenance-mode' ),
				'typeEmail'       => ! empty( $this->plugin_settings['bot']['responses']['03'] ) ? $this->plugin_settings['bot']['responses']['03'] : __( 'Type your email here…', 'wp-maintenance-mode' ),
				'send'            => __( 'Send', 'wp-maintenance-mode' ),
				'wpnonce'         => wp_create_nonce( 'wpmts_nonce_subscribe' ),
			);
			echo "<script type='text/javascript'>" .
			'var botVars = ' . wp_json_encode( $bot_vars ) .
			'</script>';
		}

		/**
		 * Check if the current user has access to backend / frontend based on his role compared with role from settings (refactor @ 2.0.4)
		 *
		 * @since 2.0.0
		 * @return boolean
		 */
		public function check_user_role() {
			// check super admin (when multisite is activated) / check admin (when multisite is not activated)
			if ( is_super_admin() ) {
				return true;
			}

			$user          = wp_get_current_user();
			$user_roles    = ! empty( $user->roles ) && is_array( $user->roles ) ? $user->roles : array();
			$allowed_roles = is_admin() ? (array) $this->plugin_settings['general']['backend_role'] : (array) $this->plugin_settings['general']['frontend_role'];

			// add `administrator` role when multisite is activated and the admin of a blog is trying to access his blog
			if ( is_multisite() ) {
				array_push( $allowed_roles, 'administrator' );
			}

			$is_allowed = (bool) array_intersect( $user_roles, $allowed_roles );

			return $is_allowed;
		}

		/**
		 * Calculate backtime based on countdown remaining time if it is activated
		 *
		 * @since 2.0.0
		 * @return int
		 */
		public function calculate_backtime() {
			$backtime = 3600;

			if ( ! empty( $this->plugin_settings['modules']['countdown_status'] ) && $this->plugin_settings['modules']['countdown_status'] === 1 ) {
				$backtime = ( $this->plugin_settings['modules']['countdown_details']['days'] * DAY_IN_SECONDS ) + ( $this->plugin_settings['modules']['countdown_details']['hours'] * HOUR_IN_SECONDS ) + ( $this->plugin_settings['modules']['countdown_details']['minutes'] * MINUTE_IN_SECONDS );
			}

			return $backtime;
		}

		/**
		 * Check if the visitor is a bot (using useragent)
		 *
		 * @since 2.0.0
		 * @return boolean
		 */
		public function check_search_bots() {
			$is_search_bot = false;

			if (
					! empty( $this->plugin_settings['general']['bypass_bots'] ) &&
					$this->plugin_settings['general']['bypass_bots'] === 1 &&
					isset( $_SERVER['HTTP_USER_AGENT'] )
			) {
				$bots = apply_filters(
					'wpmm_search_bots',
					array(
						'Abacho'          => 'AbachoBOT',
						'Accoona'         => 'Acoon',
						'AcoiRobot'       => 'AcoiRobot',
						'Adidxbot'        => 'adidxbot',
						'AltaVista robot' => 'Altavista',
						'Altavista robot' => 'Scooter',
						'ASPSeek'         => 'ASPSeek',
						'Atomz'           => 'Atomz',
						'Bing'            => 'bingbot',
						'BingPreview'     => 'BingPreview',
						'CrocCrawler'     => 'CrocCrawler',
						'Dumbot'          => 'Dumbot',
						'eStyle Bot'      => 'eStyle',
						'FAST-WebCrawler' => 'FAST-WebCrawler',
						'GeonaBot'        => 'GeonaBot',
						'Gigabot'         => 'Gigabot',
						'Google'          => 'Googlebot',
						'ID-Search Bot'   => 'IDBot',
						'Lycos spider'    => 'Lycos',
						'MSN'             => 'msnbot',
						'MSRBOT'          => 'MSRBOT',
						'Rambler'         => 'Rambler',
						'Scrubby robot'   => 'Scrubby',
						'Yahoo'           => 'Yahoo',
					)
				);

				$is_search_bot = (bool) preg_match( '~(' . implode( '|', array_values( $bots ) ) . ')~i', $_SERVER['HTTP_USER_AGENT'] );
			}

			return $is_search_bot;
		}

		/**
		 * Sanitize IP adress.
		 *
		 * @param string $ip Ip string.
		 *
		 * @return array|string|string[]|null
		 */
		public static function sanitize_ip( $ip ) {
			return preg_replace( '/[^0-9a-fA-F:., ]/', '', $ip );
		}
		/**
		 * Check if slug / ip address exists in exclude list
		 *
		 * @since 2.0.0
		 * @return boolean
		 */
		public function check_exclude() {
			$is_excluded   = false;
			$excluded_list = array();

			if ( ! empty( $this->plugin_settings['general']['exclude'] ) && is_array( $this->plugin_settings['general']['exclude'] ) ) {
				$excluded_list  = $this->plugin_settings['general']['exclude'];
				$remote_address = isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '';
				$remote_address = self::sanitize_ip( $remote_address );
				$request_uri    = isset( $_SERVER['REQUEST_URI'] ) ? rawurldecode( $_SERVER['REQUEST_URI'] ) : '';
				$request_uri    = wp_sanitize_redirect( $request_uri );
				foreach ( $excluded_list as $item ) {
					if ( false !== strpos( $item, '#' ) ) {
						$item = trim( substr( $item, 0, strpos( $item, '#' ) ) );
					}

					if ( empty( $item ) ) { // just to be sure :-)
						continue;
					}

					if ( strstr( $remote_address, $item ) || strstr( $request_uri, $item ) ) {
						$is_excluded = true;
						break;
					}
				}
			}

			$is_excluded = apply_filters( 'wpmm_is_excluded', $is_excluded, $excluded_list );

			return $is_excluded;
		}

		/**
		 * Redirect if "Redirection" option is used and users don't have access to WordPress dashboard
		 *
		 * @since 2.0.0
		 * @return null
		 */
		public function redirect() {
			// we do not redirect if there's nothing saved in "redirect" input
			if ( empty( $this->plugin_settings['general']['redirection'] ) ) {
				return null;
			}

			// we do not redirect ajax calls
			if ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
				return null;
			}

			// we do not redirect visitors or logged-in users that are not using /wp-admin/
			if ( ! is_user_logged_in() || ! is_admin() ) {
				return null;
			}

			// we do not redirect users that have access to backend
			if ( $this->check_user_role() ) {
				return null;
			}

			$redirect_to = esc_url_raw( $this->plugin_settings['general']['redirection'] );
			wp_redirect( $redirect_to ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
			exit;
		}

		/**
		 * Google Analytics code
		 *
		 * @since 2.0.7
		 */
		public function add_google_analytics_code() {
			// check if module is activated and code exists
			if (
					empty( $this->plugin_settings['modules']['ga_status'] ) ||
					$this->plugin_settings['modules']['ga_status'] !== 1 ||
					empty( $this->plugin_settings['modules']['ga_code'] )
			) {
				return false;
			}

			// sanitize code
			$ga_code = wpmm_sanitize_ga_code( $this->plugin_settings['modules']['ga_code'] );

			if ( empty( $ga_code ) ) {
				return false;
			}

			// set options
			$ga_options = array();

			if (
					! empty( $this->plugin_settings['modules']['ga_anonymize_ip'] ) &&
					$this->plugin_settings['modules']['ga_anonymize_ip'] === 1
			) {
				$ga_options['anonymize_ip'] = true;
			}

			$ga_options = (object) $ga_options;

			// show google analytics javascript snippet
			include_once wpmm_get_template_path( 'google-analytics.php' );
		}

		/**
		 * Add CSS files
		 *
		 * @since 2.4.0
		 */
		public function add_css_files() {
			$styles = array();
			if ( ! get_option( 'wpmm_new_look' ) || ! ( isset( $this->plugin_settings['design']['page_id'] ) ) ) {
				$styles = array(
					'frontend' => WPMM_CSS_URL . 'style' . WPMM_ASSETS_SUFFIX . '.css?ver=' . self::VERSION,
				);
			}

			if ( ! empty( $this->plugin_settings['bot']['status'] ) && $this->plugin_settings['bot']['status'] === 1 ) {
				$styles['bot'] = WPMM_CSS_URL . 'style.bot' . WPMM_ASSETS_SUFFIX . '.css?ver=' . self::VERSION;
			}

			foreach ( apply_filters( 'wpmm_styles', $styles ) as $handle => $href ) {
				printf( "<link rel=\"stylesheet\" id=\"%s-css\" href=\"%s\" media=\"all\">\n", esc_attr( $handle ), esc_url( $href ) );
			}
		}

		/**
		 * Adds the maintenance page template to the templates dropdown
		 *
		 * @param $templates
		 * @return mixed
		 */
		public function add_maintenance_template( $templates ) {
			return array_merge(
				$templates,
				array(
					'templates/wpmm-page-template.php' => html_entity_decode( '&harr; ' ) . __( 'LightStart template', 'wp-maintenance-mode' ),
				)
			);
		}

		/**
		 * Applies the maintenance page template to the page
		 *
		 * @param $template
		 * @return mixed|string
		 */
		public function use_maintenance_template( $template ) {
			global $post;
			if ( empty( $post ) ) {
				return $template;
			}

			$current_template = get_post_meta( $post->ID, '_wp_page_template', true );

			if ( empty( $current_template ) ) {
				return $template;
			}
			if ( 'templates/wpmm-page-template.php' !== $current_template ) {
				return $template;
			}

			$file = WPMM_VIEWS_PATH . 'wpmm-page-template.php';
			if ( file_exists( $file ) ) {
				return $file;
			}

			return $template;
		}

		/**
		 * Calls `wp_head()` and remembers all the stylesheets rendered in the
		 * `style_buffer` variable.
		 * This is a fix for block themes.
		 *
		 * @return void
		 */
		public function remember_style_fse() {
			ob_start();
			wp_head();
			$output = ob_get_contents();
			ob_end_clean();

			echo $output;

			$doc = new DOMDocument();
			$doc->loadHTML( '<html>' . $output . '</html>' );
			$this->style_buffer = $doc->getElementsByTagName( 'style' );
		}

		/**
		 * Calls `wp_head()` at the end of file so that the missing stylesheets from the header
		 * are added. Checks the `style_buffer` variable to not have duplicated styles.
		 * This is a fix for block themes.
		 *
		 * @return void
		 */
		public function add_style_fse() {
			ob_start();
			wp_head();
			$output = ob_get_contents();
			ob_end_clean();

			$doc = new DOMDocument();
			$doc->loadHTML( '<html>' . $output . '</html>' );
			$elems = $doc->getElementsByTagName( 'style' );
			$css   = '';

			$common_positions = array();

			foreach ( $elems as $i => $elem ) {
				foreach ( $this->style_buffer as $style ) {
					if ( $elems->item( $i )->C14N() == $style->C14N() ) {
						$common_positions[] = $i;
					};
				}
			}

			foreach ( $elems as $i => $elem ) {
				if ( in_array( $i, $common_positions ) ) {
					continue;
				}

				$css .= $elems->item( $i )->C14N();
			}

			echo $css;
		}

		/**
		 * Add inline CSS style
		 *
		 * @since 2.4.0
		 */
		public function add_inline_css_style() {
			$css_rules = array();

			// "Manage Bot > Upload avatar" url
			if ( ! empty( $this->plugin_settings['bot']['avatar'] ) ) {
				$css_rules['bot.avatar'] = sprintf( '.bot-avatar { background-image: url("%s"); }', esc_url( $this->plugin_settings['bot']['avatar'] ) );
			} else {
				$css_rules['bot.avatar'] = sprintf( '.bot-avatar { background-image: url("%s"); }', esc_url( WPMM_IMAGES_URL . 'chatbot.png' ) );
			}

			// style below is not necessary in the new look
			if ( get_option( 'wpmm_new_look' ) && isset( $this->plugin_settings['design']['page_id'] ) ) {
				if ( empty( $css_rules ) ) {
					return;
				}

				printf( "<style type=\"text/css\">\n%s\n</style>\n", wp_strip_all_tags( implode( "\n", $css_rules ) ) );
				return;
			}

			// "Design > Content > Heading" color
			if ( ! empty( $this->plugin_settings['design']['heading_color'] ) ) {
				$css_rules['design.heading_color'] = sprintf( '.wrap h1 { color: %s; }', sanitize_hex_color( $this->plugin_settings['design']['heading_color'] ) );
			}

			// "Design > Content > Text" color
			if ( ! empty( $this->plugin_settings['design']['text_color'] ) ) {
				$css_rules['design.text_color'] = sprintf( '.wrap h2 { color: %s; }', sanitize_hex_color( $this->plugin_settings['design']['text_color'] ) );
			}

			// "Design > Content > Footer links" color
			if ( ! empty( $this->plugin_settings['design']['footer_links_color'] ) ) {
				$css_rules['design.footer_links_color'] = sprintf( '.wrap .footer_links a, .wrap .author_link a { color: %s; }', sanitize_hex_color( $this->plugin_settings['design']['footer_links_color'] ) );
			}

			// "Design > Background" color
			if ( $this->plugin_settings['design']['bg_type'] === 'color' && ! empty( $this->plugin_settings['design']['bg_color'] ) ) {
				$css_rules['design.bg_color'] = sprintf( 'body { background-color: %s; }', sanitize_hex_color( $this->plugin_settings['design']['bg_color'] ) );
			}

			// "Design > Background" custom background url
			if ( $this->plugin_settings['design']['bg_type'] === 'custom' && ! empty( $this->plugin_settings['design']['bg_custom'] ) ) {
				$css_rules['design.bg_custom'] = sprintf( '.background { background: url("%s") no-repeat center top fixed; background-size: cover; }', esc_url( $this->plugin_settings['design']['bg_custom'] ) );
			}

			// "Design > Background" predefined background url
			if (
					$this->plugin_settings['design']['bg_type'] === 'predefined' &&
					! empty( $this->plugin_settings['design']['bg_predefined'] ) &&
					in_array( $this->plugin_settings['design']['bg_predefined'], wp_list_pluck( wpmm_get_backgrounds(), 'big' ), true )
			) {
				$css_rules['design.bg_predefined'] = sprintf( '.background { background: url("%s") no-repeat center top fixed; background-size: cover; }', esc_url( WPMM_URL . 'assets/images/backgrounds/' . $this->plugin_settings['design']['bg_predefined'] ) );
			}

			// "Modules > Countdown" color
			if ( ! empty( $this->plugin_settings['modules']['countdown_color'] ) ) {
				$css_rules['modules.countdown_color'] = sprintf( '.wrap .countdown span { color: %s; }', sanitize_hex_color( $this->plugin_settings['modules']['countdown_color'] ) );
			}

			// "Modules > Subscribe > Text" color
			if ( ! empty( $this->plugin_settings['modules']['subscribe_text_color'] ) ) {
				$css_rules['modules.subscribe_text_color'] = sprintf( '.wrap h3, .wrap .subscribe_wrapper { color: %s; }', sanitize_hex_color( $this->plugin_settings['modules']['subscribe_text_color'] ) );
			}

			// "Design > Other > Custom CSS"
			if ( ! empty( $this->plugin_settings['design']['other_custom_css'] ) ) {
				$css_rules['design.other_custom_css'] = sanitize_textarea_field( $this->plugin_settings['design']['other_custom_css'] );
			}

			if ( empty( $css_rules ) ) {
				return;
			}

			printf( "<style type=\"text/css\">\n%s\n</style>\n", wp_strip_all_tags( implode( "\n", $css_rules ) ) );
		}

		/**
		 * Add Javascript files
		 *
		 * @since 2.4.0
		 */
		public function add_js_files() {
			$scripts = array(
				'jquery'   => site_url( '/wp-includes/js/jquery/jquery' . WPMM_ASSETS_SUFFIX . '.js' ),
				'fitvids'  => WPMM_JS_URL . 'jquery.fitvids' . WPMM_ASSETS_SUFFIX . '.js',
				'frontend' => WPMM_JS_URL . 'scripts' . WPMM_ASSETS_SUFFIX . '.js?ver=' . self::VERSION,
			);

			if ( ! get_option( 'wpmm_new_look' ) || ! ( isset( $this->plugin_settings['design']['page_id'] ) ) ) {
				if ( ! empty( $this->plugin_settings['modules']['countdown_status'] ) && $this->plugin_settings['modules']['countdown_status'] === 1 ) {
					$scripts['countdown-dependency'] = WPMM_JS_URL . 'jquery.plugin' . WPMM_ASSETS_SUFFIX . '.js';
					$scripts['countdown']            = WPMM_JS_URL . 'jquery.countdown' . WPMM_ASSETS_SUFFIX . '.js';
				}

				if (
					( ! empty( $this->plugin_settings['modules']['contact_status'] ) && $this->plugin_settings['modules']['contact_status'] === 1 ) ||
					( ! empty( $this->plugin_settings['modules']['subscribe_status'] ) && $this->plugin_settings['modules']['subscribe_status'] === 1 )
				) {
					$scripts['validate'] = WPMM_JS_URL . 'jquery.validate' . WPMM_ASSETS_SUFFIX . '.js';
				}
			}

			if ( ! empty( $this->plugin_settings['bot']['status'] ) && $this->plugin_settings['bot']['status'] === 1 ) {
				$scripts['validate'] = WPMM_JS_URL . 'jquery.validate' . WPMM_ASSETS_SUFFIX . '.js';
			}

			if ( ! empty( $this->plugin_settings['bot']['status'] ) && $this->plugin_settings['bot']['status'] === 1 ) {
				if ( WPMM_ASSETS_SUFFIX === '' ) {
					$scripts['bot-async'] = WPMM_JS_URL . 'bot.async.js';
				}

				$scripts['bot'] = WPMM_JS_URL . 'bot' . WPMM_ASSETS_SUFFIX . '.js?ver=' . self::VERSION;
			}

			if ( ! did_action( 'wpmm_before_scripts' ) ) {
				do_action( 'wpmm_before_scripts' );
			}

			foreach ( apply_filters( 'wpmm_scripts', $scripts ) as $handle => $src ) {
				printf( "<script type=\"text/javascript\" src=\"%s\" id=\"%s-js\"></script>\n", esc_url( $src ), esc_attr( $handle ) );
			}
		}

		/**
		 * Save subscriber into database (refactor @ 2.0.4)
		 *
		 * @since 2.0.0
		 * @global object $wpdb
		 * @throws Exception
		 */
		public function add_subscriber() {
			global $wpdb;

			try {
				$email = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
				// checks
				if ( empty( $email ) || ! is_email( $email ) ) {
					throw new Exception( __( 'Please enter a valid email address.', 'wp-maintenance-mode' ) );
				}
				if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'wpmts_nonce_subscribe' )
				) {
					throw new Exception( __( 'Security check.', 'wp-maintenance-mode' ) );
				}
				// save.
				$this->insert_subscriber( $email );

				wp_send_json_success( __( 'You successfully subscribed. Thanks!', 'wp-maintenance-mode' ) );
			} catch ( Exception $ex ) {
				wp_send_json_error( $ex->getMessage() );
			}
		}

		/**
		 * Send email via contact form (refactor @ 2.0.4)
		 *
		 * @since 2.0.0
		 * @throws Exception
		 */
		public function send_contact() {
			try {
				$name    = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$email   = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$content = isset( $_POST['content'] ) ? sanitize_textarea_field( $_POST['content'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
				// checks
				if ( empty( $name ) || empty( $email ) || empty( $content ) ) {
					throw new Exception( __( 'All fields required.', 'wp-maintenance-mode' ) );
				}
				if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'wpmts_nonce_contact' )
				) {
					throw new Exception( __( 'Security check.', 'wp-maintenance-mode' ) );
				}
				if ( ! is_email( $email ) ) {
					throw new Exception( __( 'Please enter a valid email address.', 'wp-maintenance-mode' ) );
				}

				// if you add new fields to the contact form... you will definitely need to validate their values
				do_action( 'wpmm_contact_validation', $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
				// vars
				$send_to = ! empty( $this->plugin_settings['modules']['contact_email'] ) ? $this->plugin_settings['modules']['contact_email'] : get_option( 'admin_email' );
				$subject = apply_filters( 'wpmm_contact_subject', __( 'Message via contact', 'wp-maintenance-mode' ) );
				$headers = apply_filters( 'wpmm_contact_headers', array( 'Reply-To: ' . $email ) );

				ob_start();
				include_once wpmm_get_template_path( 'contact.php', true );
				$message = ob_get_clean();

				// add temporary filters
				$from_name = function() use ( $name ) {
					return $name;
				};
				add_filter( 'wp_mail_content_type', 'wpmm_change_mail_content_type', 10, 1 );
				add_filter( 'wp_mail_from_name', $from_name );

				// send email
				$send = wp_mail( $send_to, $subject, $message, $headers );

				// remove temporary filters
				remove_filter( 'wp_mail_content_type', 'wpmm_change_mail_content_type', 10, 1 );
				remove_filter( 'wp_mail_from_name', $from_name );

				if ( ! $send ) {
					throw new Exception( __( 'Something happened! Please try again later.', 'wp-maintenance-mode' ) );
				}

				wp_send_json_success( __( 'Your email was sent to the website administrator. Thanks!', 'wp-maintenance-mode' ) );
			} catch ( Exception $ex ) {
				wp_send_json_error( $ex->getMessage() );
			}
		}

		/**
		 * Save subscriber into database.
		 *
		 * @param Form_Data_Request $form_data The form data.
		 * @return void
		 */
		public function otter_add_subscriber( $form_data ) {
			if ( $form_data ) {
				$input_data = $form_data->get_payload_field( 'formInputsData' );
				$input_data = array_map(
					function( $input_field ) {
						if ( isset( $input_field['type'] ) && 'email' === $input_field['type'] ) {
							return $input_field['value'];
						}
						return false;
					},
					$input_data
				);
				$input_data = array_filter( $input_data );
				if ( ! empty( $input_data ) ) {
					foreach ( $input_data as $email ) {
						$this->insert_subscriber( $email );
					}
				}
			}
		}

		/**
		 * Save subscriber into database.
		 *
		 * @param string $email Email address.
		 * @global object $wpdb
		 *
		 * @return void
		 */
		public function insert_subscriber( $email = '' ) {
			global $wpdb;
			if ( ! empty( $email ) ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$exists = $wpdb->get_row( $wpdb->prepare( "SELECT id_subscriber FROM {$wpdb->prefix}wpmm_subscribers WHERE email = %s", $email ), ARRAY_A );
				if ( empty( $exists ) ) {
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$wpdb->insert(
						$wpdb->prefix . 'wpmm_subscribers',
						array(
							'email'       => sanitize_email( $email ),
							'insert_date' => date( 'Y-m-d H:i:s' ),
						),
						array( '%s', '%s' )
					);
				}
			}
		}

		/**
		 * Set the current_page_category property
		 * @param $category
		 *
		 * @return void
		 */
		public function set_current_page_category( $category ) {
			$this->current_page_category = $category;
		}

		/**
		 * Get the current_page_category property
		 *
		 * @return mixed
		 */
		public function get_current_page_category() {
			return $this->current_page_category;
		}
	}

}
