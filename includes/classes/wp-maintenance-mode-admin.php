<?php

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_Maintenance_Mode_Admin' ) ) {

	class WP_Maintenance_Mode_Admin {

		protected static $instance = null;
		protected $plugin_slug;
		protected $plugin_settings;
		protected $plugin_default_settings;
		protected $plugin_basename;
		protected $plugin_screen_hook_suffix = null;
		private $dismissed_notices_key       = 'wpmm_dismissed_notices';

		/**
		 * 3, 2, 1... Start!
		 */
		private function __construct() {
			$plugin                        = WP_Maintenance_Mode::get_instance();
			$this->plugin_slug             = $plugin->get_plugin_slug();
			$this->plugin_settings         = $plugin->get_plugin_settings();
			$this->plugin_default_settings = $plugin->default_settings();
			$this->plugin_basename         = plugin_basename( WPMM_PATH . $this->plugin_slug . '.php' );

			// Load admin style sheet and JavaScript.
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

			// Add the options page and menu item.
			add_action( 'admin_menu', array( $this, 'add_plugin_menu' ) );

			// Add an action link pointing to the options page
			if ( is_multisite() && is_plugin_active_for_network( $this->plugin_basename ) ) {
				// settings link will point to admin_url of the main blog, not to network_admin_url
				add_filter( 'network_admin_plugin_action_links_' . $this->plugin_basename, array( $this, 'add_settings_link' ) );
			} else {
				add_filter( 'plugin_action_links_' . $this->plugin_basename, array( $this, 'add_settings_link' ) );
			}

			// Add admin notices
			add_action( 'admin_notices', array( $this, 'add_notices' ) );

			// Add ajax methods
			add_action( 'wp_ajax_wpmm_subscribers_export', array( $this, 'subscribers_export' ) );
			add_action( 'wp_ajax_wpmm_subscribers_empty_list', array( $this, 'subscribers_empty_list' ) );
			add_action( 'wp_ajax_wpmm_dismiss_notices', array( $this, 'dismiss_notices' ) );
			add_action( 'wp_ajax_wpmm_reset_settings', array( $this, 'reset_plugin_settings' ) );

			// Add admin_post_$action
			add_action( 'admin_post_wpmm_save_settings', array( $this, 'save_plugin_settings' ) );

			// Add text to footer
			add_filter( 'admin_footer_text', array( $this, 'admin_footer_text' ), 5 );
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
		 * Load CSS files
		 *
		 * @since 2.0.0
		 * @return type
		 */
		public function enqueue_admin_styles() {
			if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
				return;
			}

			$screen = get_current_screen();
			if ( $this->plugin_screen_hook_suffix === $screen->id ) {
				$wp_scripts       = wp_scripts();
				$ui               = $wp_scripts->query( 'jquery-ui-core' );
				$allowed_versions = array(
					'1.11.4' => true,
					'1.12.1' => true,
					'1.13.0' => true,
					'1.13.1' => true,
				);
				wp_enqueue_style( $this->plugin_slug . '-admin-jquery-ui-styles', WPMM_CSS_URL . 'jquery-ui-styles/' . ( ! empty( $ui->ver ) ? ( isset( $allowed_versions[ $ui->ver ] ) ? $ui->ver : '1.13.1' ) : '1.11.4' ) . '/jquery-ui' . WPMM_ASSETS_SUFFIX . '.css', array(), WP_Maintenance_Mode::VERSION );
				wp_enqueue_style( $this->plugin_slug . '-admin-chosen', WPMM_CSS_URL . 'chosen' . WPMM_ASSETS_SUFFIX . '.css', array(), WP_Maintenance_Mode::VERSION );
				wp_enqueue_style( $this->plugin_slug . '-admin-timepicker-addon-script', WPMM_CSS_URL . 'jquery-ui-timepicker-addon' . WPMM_ASSETS_SUFFIX . '.css', array(), WP_Maintenance_Mode::VERSION );
				wp_enqueue_style( $this->plugin_slug . '-admin-styles', WPMM_CSS_URL . 'style-admin' . WPMM_ASSETS_SUFFIX . '.css', array( 'wp-color-picker' ), WP_Maintenance_Mode::VERSION );
			}
		}

		/**
		 * Load JS files and their dependencies
		 *
		 * @since 2.0.0
		 */
		public function enqueue_admin_scripts() {
			if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
				return;
			}

			$screen = get_current_screen();
			if ( $this->plugin_screen_hook_suffix === $screen->id ) {
				wp_enqueue_media();
				wp_enqueue_script( $this->plugin_slug . '-admin-timepicker-addon-script', WPMM_JS_URL . 'jquery-ui-timepicker-addon' . WPMM_ASSETS_SUFFIX . '.js', array( 'jquery', 'jquery-ui-datepicker' ), WP_Maintenance_Mode::VERSION, true );
				wp_enqueue_script( $this->plugin_slug . '-admin-script', WPMM_JS_URL . 'scripts-admin' . WPMM_ASSETS_SUFFIX . '.js', array( 'jquery', 'wp-color-picker' ), WP_Maintenance_Mode::VERSION, true );
				wp_enqueue_script( $this->plugin_slug . '-admin-chosen', WPMM_JS_URL . 'chosen.jquery' . WPMM_ASSETS_SUFFIX . '.js', array(), WP_Maintenance_Mode::VERSION, true );
				wp_localize_script(
					$this->plugin_slug . '-admin-script',
					'wpmm_vars',
					array(
						'ajax_url'                => admin_url( 'admin-ajax.php' ),
						'plugin_url'              => add_query_arg( array( 'page' => $this->plugin_slug ), admin_url( 'options-general.php' ) ),
						'image_uploader_defaults' => array(
							'title'       => _x( 'Upload Image', 'image_uploader default title', 'wp-maintenance-mode' ),
							'button_text' => _x( 'Choose Image', 'image_uploader default button_text', 'wp-maintenance-mode' ),
						),
					)
				);

				// add code editor (Code Mirror) to the `other_custom_css` textarea
				if ( isset( $GLOBALS['wp_version'] ) && version_compare( $GLOBALS['wp_version'], '4.9.0', '>=' ) && function_exists( 'wp_enqueue_code_editor' ) ) {
					$settings = wp_enqueue_code_editor(
						array(
							'type'       => 'text/css',
							'codemirror' => array(
								'indentUnit'  => 2,
								'tabSize'     => 2,
								'lineNumbers' => true,
							),
						)
					);

					wp_add_inline_script( 'code-editor', sprintf( 'jQuery(function ($) { var custom_css_editor = wp.codeEditor.initialize("other_custom_css", %s); $("body").on("show_design_tab_content", function () { custom_css_editor.codemirror.refresh(); }); });', wp_json_encode( $settings ) ) );
				}
			}

			// For global actions like dismiss notices
			wp_enqueue_script( $this->plugin_slug . '-admin-global', WPMM_JS_URL . 'scripts-admin-global' . WPMM_ASSETS_SUFFIX . '.js', array( 'jquery' ), WP_Maintenance_Mode::VERSION, true );
		}

		/**
		 * Export subscribers list in CSV format (refactor @ 2.0.4)
		 *
		 * @since 2.0.0
		 * @global object $wpdb
		 * @throws Exception
		 */
		public function subscribers_export() {
			global $wpdb;

			try {
				// check capabilities
				if ( ! current_user_can( wpmm_get_capability( 'subscribers' ) ) ) {
					throw new Exception( __( 'You do not have access to this resource.', 'wp-maintenance-mode' ) );
				}
				// check nonce existence
				if ( empty( $_GET['_wpnonce'] ) ) {
					throw new Exception( __( 'The nonce field must not be empty.', 'wp-maintenance-mode' ) );
				}

				// check nonce validation
				if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'tab-modules' ) ) {
					throw new Exception( __( 'Security check.', 'wp-maintenance-mode' ) );
				}
				// get subscribers and export
				$results = $wpdb->get_results( "SELECT email, insert_date FROM {$wpdb->prefix}wpmm_subscribers ORDER BY id_subscriber DESC", ARRAY_A );
				if ( ! empty( $results ) ) {
					$filename = 'subscribers-list-' . date( 'Y-m-d' ) . '.csv';

					header( 'Content-Type: text/csv' );
					header( 'Content-Disposition: attachment;filename=' . $filename );

					$fp = fopen( 'php://output', 'w' );

					fputcsv( $fp, array( 'email', 'insert_date' ) );
					foreach ( $results as $item ) {
						fputcsv( $fp, $item );
					}

					fclose( $fp ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
				}
				die();
			} catch ( Exception $ex ) {
				wp_send_json_error( $ex->getMessage() );
			}
		}

		/**
		 * Empty subscribers list
		 *
		 * @since 2.0.4
		 * @global object $wpdb
		 * @throws Exception
		 */
		public function subscribers_empty_list() {
			global $wpdb;

			try {
				// check capabilities
				if ( ! current_user_can( wpmm_get_capability( 'subscribers' ) ) ) {
					throw new Exception( __( 'You do not have access to this resource.', 'wp-maintenance-mode' ) );
				}
				// check nonce existence
				if ( empty( $_POST['_wpnonce'] ) ) {
					throw new Exception( __( 'The nonce field must not be empty.', 'wp-maintenance-mode' ) );
				}

				// check nonce validation
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'tab-modules' ) ) {
					throw new Exception( __( 'Security check.', 'wp-maintenance-mode' ) );
				}
				// delete all subscribers
				$wpdb->query( "DELETE FROM {$wpdb->prefix}wpmm_subscribers" );

				/* translators: number of subscribers */
				$message = esc_html( sprintf( _nx( 'You have %d subscriber', 'You have %d subscribers', 0, 'ajax response', 'wp-maintenance-mode' ), 0 ) );

				wp_send_json_success( $message );
			} catch ( Exception $ex ) {
				wp_send_json_error( $ex->getMessage() );
			}
		}

		/**
		 * Add plugin in Settings menu
		 *
		 * @since 2.0.0
		 */
		public function add_plugin_menu() {
			$this->plugin_screen_hook_suffix = add_options_page(
				__( 'WP Maintenance Mode', 'wp-maintenance-mode' ),
				__( 'WP Maintenance Mode', 'wp-maintenance-mode' ),
				wpmm_get_capability( 'settings' ),
				$this->plugin_slug,
				array( $this, 'display_plugin_settings' )
			);
		}

		/**
		 * Settings page
		 *
		 * @since 2.0.0
		 */
		public function display_plugin_settings() {
			include_once wpmm_get_template_path( 'settings.php' );
		}

		/**
		 * Save settings
		 *
		 * @since 2.0.0
		 */
		public function save_plugin_settings() {
			// check capabilities
			if ( ! current_user_can( wpmm_get_capability( 'settings' ) ) ) {
				die( esc_html__( 'You do not have access to this resource.', 'wp-maintenance-mode' ) );
			}

			// check nonce existence
			if ( empty( $_POST['_wpnonce'] ) ) {
				die( esc_html__( 'The nonce field must not be empty.', 'wp-maintenance-mode' ) );
			}

			// check tab existence
			if ( empty( $_POST['tab'] ) ) {
				die( esc_html__( 'The tab slug must not be empty.', 'wp-maintenance-mode' ) );
			}

			// check nonce validation
			if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'tab-' . $_POST['tab'] ) ) {
				die( esc_html__( 'Security check.', 'wp-maintenance-mode' ) );
			}

			// check existence in plugin default settings
			$tab = sanitize_key( $_POST['tab'] );
			if ( empty( $this->plugin_default_settings[ $tab ] ) ) {
				die( esc_html__( 'The tab slug must exist.', 'wp-maintenance-mode' ) );
			}

			// Do some sanitizations
			switch ( $tab ) {
				case 'general':
					$_POST['options']['general']['status'] = (int) $_POST['options']['general']['status'];
					if ( ! empty( $_POST['options']['general']['status'] ) && $_POST['options']['general']['status'] === 1 ) {
						$_POST['options']['general']['status_date'] = date( 'Y-m-d H:i:s' );
					}
					$_POST['options']['general']['bypass_bots'] = (int) $_POST['options']['general']['bypass_bots'];

					$_POST['options']['general']['backend_role']  = ! empty( $_POST['options']['general']['backend_role'] ) ? array_map( 'sanitize_text_field', $_POST['options']['general']['backend_role'] ) : array();
					$_POST['options']['general']['frontend_role'] = ! empty( $_POST['options']['general']['frontend_role'] ) ? array_map( 'sanitize_text_field', $_POST['options']['general']['frontend_role'] ) : array();

					$_POST['options']['general']['meta_robots'] = (int) $_POST['options']['general']['meta_robots'];
					$_POST['options']['general']['redirection'] = esc_url_raw( $_POST['options']['general']['redirection'] );
					if ( ! empty( $_POST['options']['general']['exclude'] ) ) {
						$exclude_array = explode( "\n", $_POST['options']['general']['exclude'] );
						// we need to be sure that empty lines will not be saved
						$_POST['options']['general']['exclude'] = array_filter( array_map( 'trim', $exclude_array ) );
						$_POST['options']['general']['exclude'] = array_map( 'sanitize_textarea_field', $_POST['options']['general']['exclude'] );
					} else {
						$_POST['options']['general']['exclude'] = array();
					}
					$_POST['options']['general']['notice']     = (int) $_POST['options']['general']['notice'];
					$_POST['options']['general']['admin_link'] = (int) $_POST['options']['general']['admin_link'];

					// delete cache when is already activated, when is activated and when is deactivated
					if (
							isset( $this->plugin_settings['general']['status'] ) && isset( $_POST['options']['general']['status'] ) &&
							(
							( $this->plugin_settings['general']['status'] === 1 && in_array( $_POST['options']['general']['status'], array( 0, 1 ), true ) ) ||
							( $this->plugin_settings['general']['status'] === 0 && $_POST['options']['general']['status'] === 1 )
							)
					) {
						wpmm_delete_cache();
					}
					break;
				case 'design':
					// Content
					$_POST['options']['design']['title']         = sanitize_text_field( $_POST['options']['design']['title'] );
					$_POST['options']['design']['heading']       = sanitize_text_field( $_POST['options']['design']['heading'] );
					$_POST['options']['design']['heading_color'] = sanitize_hex_color( $_POST['options']['design']['heading_color'] );

					add_filter( 'safe_style_css', array( $this, 'add_safe_style_css' ) ); // add before we save
					$_POST['options']['design']['text']       = wp_kses_post( $_POST['options']['design']['text'] );
					$_POST['options']['design']['text_color'] = sanitize_hex_color( $_POST['options']['design']['text_color'] );
					remove_filter( 'safe_style_css', array( $this, 'add_safe_style_css' ) ); // remove after we save

					$_POST['options']['design']['footer_links_color'] = sanitize_hex_color( $_POST['options']['design']['footer_links_color'] );

					// Background
					$_POST['options']['design']['bg_type']       = sanitize_text_field( $_POST['options']['design']['bg_type'] );
					$_POST['options']['design']['bg_color']      = sanitize_hex_color( $_POST['options']['design']['bg_color'] );
					$_POST['options']['design']['bg_custom']     = esc_url_raw( $_POST['options']['design']['bg_custom'] );
					$_POST['options']['design']['bg_predefined'] = sanitize_text_field( $_POST['options']['design']['bg_predefined'] );

					// Other
					$_POST['options']['design']['other_custom_css'] = sanitize_textarea_field( $_POST['options']['design']['other_custom_css'] );

					// Delete cache when is activated
					if ( ! empty( $this->plugin_settings['general']['status'] ) && $this->plugin_settings['general']['status'] === 1 ) {
						wpmm_delete_cache();
					}
					break;
				case 'modules':
					// Countdown
					$_POST['options']['modules']['countdown_status']  = (int) $_POST['options']['modules']['countdown_status'];
					$_POST['options']['modules']['countdown_start']   = sanitize_text_field( $_POST['options']['modules']['countdown_start'] );
					$_POST['options']['modules']['countdown_details'] = array_map( 'trim', $_POST['options']['modules']['countdown_details'] );
					$_POST['options']['modules']['countdown_details'] = array(
						'days'    => isset( $_POST['options']['modules']['countdown_details']['days'] ) && is_numeric( $_POST['options']['modules']['countdown_details']['days'] ) ? sanitize_text_field( $_POST['options']['modules']['countdown_details']['days'] ) : 0,
						'hours'   => isset( $_POST['options']['modules']['countdown_details']['hours'] ) && is_numeric( $_POST['options']['modules']['countdown_details']['hours'] ) ? sanitize_text_field( $_POST['options']['modules']['countdown_details']['hours'] ) : 1,
						'minutes' => isset( $_POST['options']['modules']['countdown_details']['minutes'] ) && is_numeric( $_POST['options']['modules']['countdown_details']['minutes'] ) ? sanitize_text_field( $_POST['options']['modules']['countdown_details']['minutes'] ) : 0,
					);

					$_POST['options']['modules']['countdown_color'] = sanitize_hex_color( $_POST['options']['modules']['countdown_color'] );

					// Subscribe
					$_POST['options']['modules']['subscribe_status']     = (int) $_POST['options']['modules']['subscribe_status'];
					$_POST['options']['modules']['subscribe_text']       = sanitize_text_field( $_POST['options']['modules']['subscribe_text'] );
					$_POST['options']['modules']['subscribe_text_color'] = sanitize_hex_color( $_POST['options']['modules']['subscribe_text_color'] );

					// Social networks
					$_POST['options']['modules']['social_status']    = (int) $_POST['options']['modules']['social_status'];
					$_POST['options']['modules']['social_target']    = (int) $_POST['options']['modules']['social_target'];
					$_POST['options']['modules']['social_github']    = sanitize_text_field( $_POST['options']['modules']['social_github'] );
					$_POST['options']['modules']['social_dribbble']  = sanitize_text_field( $_POST['options']['modules']['social_dribbble'] );
					$_POST['options']['modules']['social_twitter']   = sanitize_text_field( $_POST['options']['modules']['social_twitter'] );
					$_POST['options']['modules']['social_facebook']  = sanitize_text_field( $_POST['options']['modules']['social_facebook'] );
					$_POST['options']['modules']['social_instagram'] = sanitize_text_field( $_POST['options']['modules']['social_instagram'] );
					$_POST['options']['modules']['social_pinterest'] = sanitize_text_field( $_POST['options']['modules']['social_pinterest'] );
					$_POST['options']['modules']['social_google+']   = sanitize_text_field( $_POST['options']['modules']['social_google+'] );
					$_POST['options']['modules']['social_linkedin']  = sanitize_text_field( $_POST['options']['modules']['social_linkedin'] );

					// Contact
					$_POST['options']['modules']['contact_status']  = (int) $_POST['options']['modules']['contact_status'];
					$_POST['options']['modules']['contact_email']   = sanitize_text_field( $_POST['options']['modules']['contact_email'] );
					$_POST['options']['modules']['contact_effects'] = sanitize_text_field( $_POST['options']['modules']['contact_effects'] );

					// Google analytics
					$_POST['options']['modules']['ga_status']       = (int) $_POST['options']['modules']['ga_status'];
					$_POST['options']['modules']['ga_anonymize_ip'] = (int) $_POST['options']['modules']['ga_anonymize_ip'];
					$_POST['options']['modules']['ga_code']         = wpmm_sanitize_ga_code( $_POST['options']['modules']['ga_code'] );

					// Delete cache when is activated
					if ( ! empty( $this->plugin_settings['general']['status'] ) && $this->plugin_settings['general']['status'] === 1 ) {
						wpmm_delete_cache();
					}
					break;
				case 'bot':
					$_POST['options']['bot']['status'] = (int) $_POST['options']['bot']['status'];
					$_POST['options']['bot']['name']   = sanitize_text_field( $_POST['options']['bot']['name'] );
					$_POST['options']['bot']['avatar'] = esc_url_raw( $_POST['options']['bot']['avatar'] );

					$_POST['options']['bot']['messages']['01']   = sanitize_text_field( $_POST['options']['bot']['messages']['01'] );
					$_POST['options']['bot']['messages']['02']   = sanitize_text_field( $_POST['options']['bot']['messages']['02'] );
					$_POST['options']['bot']['messages']['03']   = sanitize_text_field( $_POST['options']['bot']['messages']['03'] );
					$_POST['options']['bot']['messages']['04']   = sanitize_text_field( $_POST['options']['bot']['messages']['04'] );
					$_POST['options']['bot']['messages']['05']   = sanitize_text_field( $_POST['options']['bot']['messages']['05'] );
					$_POST['options']['bot']['messages']['06']   = sanitize_text_field( $_POST['options']['bot']['messages']['06'] );
					$_POST['options']['bot']['messages']['07']   = sanitize_text_field( $_POST['options']['bot']['messages']['07'] );
					$_POST['options']['bot']['messages']['08_1'] = sanitize_text_field( $_POST['options']['bot']['messages']['08_1'] );
					$_POST['options']['bot']['messages']['08_2'] = sanitize_text_field( $_POST['options']['bot']['messages']['08_2'] );
					$_POST['options']['bot']['messages']['09']   = sanitize_text_field( $_POST['options']['bot']['messages']['09'] );
					$_POST['options']['bot']['messages']['10']   = sanitize_text_field( $_POST['options']['bot']['messages']['10'] );

					$_POST['options']['bot']['responses']['01']   = sanitize_text_field( $_POST['options']['bot']['responses']['01'] );
					$_POST['options']['bot']['responses']['02_1'] = sanitize_text_field( $_POST['options']['bot']['responses']['02_1'] );
					$_POST['options']['bot']['responses']['02_2'] = sanitize_text_field( $_POST['options']['bot']['responses']['02_2'] );
					$_POST['options']['bot']['responses']['03']   = sanitize_text_field( $_POST['options']['bot']['responses']['03'] );

					// Write out JS file on saved
					$this->set_datajs_file( $_POST['options']['bot'] );

					// Delete cache when is activated
					if ( ! empty( $this->plugin_settings['general']['status'] ) && $this->plugin_settings['general']['status'] === 1 ) {
						wpmm_delete_cache();
					}
					break;
				case 'gdpr':
					$_POST['options']['gdpr']['status']              = (int) $_POST['options']['gdpr']['status'];
					$_POST['options']['gdpr']['policy_page_label']   = sanitize_text_field( $_POST['options']['gdpr']['policy_page_label'] );
					$_POST['options']['gdpr']['policy_page_link']    = esc_url_raw( $_POST['options']['gdpr']['policy_page_link'] );
					$_POST['options']['gdpr']['policy_page_target']  = (int) $_POST['options']['gdpr']['policy_page_target'];
					$_POST['options']['gdpr']['contact_form_tail']   = wp_kses( $_POST['options']['gdpr']['contact_form_tail'], wpmm_gdpr_textarea_allowed_html() );
					$_POST['options']['gdpr']['subscribe_form_tail'] = wp_kses( $_POST['options']['gdpr']['subscribe_form_tail'], wpmm_gdpr_textarea_allowed_html() );

					// Delete cache when is activated
					if ( ! empty( $this->plugin_settings['general']['status'] ) && $this->plugin_settings['general']['status'] === 1 ) {
						wpmm_delete_cache();
					}
					break;
			}

			// save settings
			$this->plugin_settings[ $tab ] = $_POST['options'][ $tab ];
			update_option( 'wpmm_settings', $this->plugin_settings );

			// redirect back
			wp_safe_redirect(
				add_query_arg(
					array(
						'page'    => $this->plugin_slug,
						'updated' => true,
					),
					admin_url( 'options-general.php' )
				) . '#' . $tab
			);
			exit;
		}

		/**
		 * Reset settings (refactor @ 2.0.4)
		 *
		 * @since 2.0.0
		 * @throws Exception
		 */
		public function reset_plugin_settings() {
			try {
				// check capabilities
				if ( ! current_user_can( wpmm_get_capability( 'settings' ) ) ) {
					throw new Exception( __( 'You do not have access to this resource.', 'wp-maintenance-mode' ) );
				}

				// check nonce existence
				if ( empty( $_POST['_wpnonce'] ) ) {
					throw new Exception( __( 'The nonce field must not be empty.', 'wp-maintenance-mode' ) );
				}

				// check tab existence
				if ( empty( $_POST['tab'] ) ) {
					throw new Exception( __( 'The tab slug must not be empty.', 'wp-maintenance-mode' ) );
				}

				// check nonce validation
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'tab-' . $_POST['tab'] ) ) {
					throw new Exception( __( 'Security check.', 'wp-maintenance-mode' ) );
				}

				// check existence in plugin default settings
				$tab = sanitize_key( $_POST['tab'] );
				if ( empty( $this->plugin_default_settings[ $tab ] ) ) {
					throw new Exception( __( 'The tab slug must exist.', 'wp-maintenance-mode' ) );
				}

				// update options using the default values
				$this->plugin_settings[ $tab ] = $this->plugin_default_settings[ $tab ];
				update_option( 'wpmm_settings', $this->plugin_settings );

				wp_send_json_success();
			} catch ( Exception $ex ) {
				wp_send_json_error( $ex->getMessage() );
			}
		}

		/**
		 * Add new safe inline style css (use by wp_kses_attr in wp_kses_post)
		 * - bug discovered by cokemorgan: https://github.com/andrianvaleanu/WP-Maintenance-Mode/issues/56
		 *
		 * @since 2.0.3
		 * @param array $properties
		 * @return array
		 */
		public function add_safe_style_css( $properties ) {
			$new_properties = array(
				'min-height',
				'max-height',
				'min-width',
				'max-width',
			);

			return array_merge( $new_properties, $properties );
		}

		/**
		 * Builds the data.js file and writes it into uploads
		 * This file is mandatory for the bot to work correctly.
		 *
		 * @todo rewrite bot functionality. instead of saving the settings to a file, we should add them to the maintenance mode page using `wpmm_before_scripts` action
		 * @param array $messages
		 * @throws Exception
		 */
		public function set_datajs_file( $messages = array() ) {
			$data  = "var botName = \"{$messages['name']}\",\n"
					. "botAvatar = \"{$messages['avatar']}\",\n"
					. "conversationData = {\"homepage\": {1: { \"statement\": [ \n";
			$data .= ( ! empty( $messages['messages']['01'] ) ) ? "\"{$messages['messages']['01']}\", \n" : '';
			$data .= ( ! empty( $messages['messages']['02'] ) ) ? "\"{$messages['messages']['02']}\", \n" : '';
			$data .= ( ! empty( $messages['messages']['03'] ) ) ? "\"{$messages['messages']['03']}\", \n" : '';
			$data .= "], \"input\": {\"name\": \"name\", \"consequence\": 1.2}},1.2:{\"statement\": function(context) {return [ \n";
			$data .= ( ! empty( $messages['messages']['04'] ) ) ? "\"{$messages['messages']['04']}\", \n" : '';
			$data .= ( ! empty( $messages['messages']['05'] ) ) ? "\"{$messages['messages']['05']}\", \n" : '';
			$data .= ( ! empty( $messages['messages']['06'] ) ) ? "\"{$messages['messages']['06']}\", \n" : '';
			$data .= ( ! empty( $messages['messages']['07'] ) ) ? "\"{$messages['messages']['07']}\", \n" : '';
			$data .= "];},\"options\": [{ \"choice\": \"{$messages['responses']['02_1']}\",\"consequence\": 1.4},{ \n"
					. "\"choice\": \"{$messages['responses']['02_2']}\",\"consequence\": 1.5}]},1.4: { \"statement\": function(context) {return [ \n";
			$data .= ( ! empty( $messages['messages']['08_1'] ) ) ? "\"{$messages['messages']['08_1']}\", \n" : '';
			$data .= "];}, \"email\": {\"email\": \"email\", \"consequence\": 1.6}},1.5: {\"statement\": function(context) {return [ \n";
			$data .= ( ! empty( $messages['messages']['08_2'] ) ) ? "\"{$messages['messages']['08_2']}\", \n" : '';
			$data .= "];}},1.6: { \"statement\": function(context) {return [ \n";
			$data .= ( ! empty( $messages['messages']['09'] ) ) ? "\"{$messages['messages']['09']}\", \n" : '';
			$data .= ( ! empty( $messages['messages']['10'] ) ) ? "\"{$messages['messages']['10']}\", \n" : '';
			$data .= '];}}}};';

			// Replace placeholders
			$placeholders = array(
				'{visitor_name}' => '" + ( ( typeof context === \'object\' && context !== null && context.hasOwnProperty(\'name\') ) ? context.name : \'\' )  + "',
				'{bot_name}'     => $messages['name'],
			);

			$data = str_replace( array_keys( $placeholders ), $placeholders, $data );

			// Try to write data.js file
			try {
				$upload_dir = wp_upload_dir();
				if ( file_put_contents( trailingslashit( $upload_dir['basedir'] ) . 'data.js', $data ) === false ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents
					throw new Exception( __( 'WPMM: The file data.js could not be written, the bot will not work correctly.', 'wp-maintenance-mode' ) );
				}
			} catch ( Exception $ex ) {
				// remove error_log when rewrite bot feature
				error_log( $ex->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			}
		}

		/**
		 * Delete object cache & page cache (if the cache plugin is supported)
		 *
		 * ! the method is deprecated, but we keep it for backward compatibility !
		 *
		 * @since 2.0.1
		 */
		public function delete_cache() {
			if ( function_exists( '_deprecated_function' ) ) {
				_deprecated_function( __METHOD__, '2.4.0', 'wpmm_delete_cache()' );
			}

			wpmm_delete_cache();
		}

		/**
		 * Add settings link
		 *
		 * @since 2.0.0
		 * @param array $links
		 * @return array
		 */
		public function add_settings_link( $links ) {
			return array_merge(
				array(
					'wpmm_settings' => sprintf( '<a href="%s">%s</a>', add_query_arg( array( 'page' => $this->plugin_slug ), admin_url( 'options-general.php' ) ), esc_html__( 'Settings', 'wp-maintenance-mode' ) ),
				),
				$links
			);
		}

		/**
		 * Add notices - will be displayed on dashboard
		 *
		 * @since 2.0.0
		 */
		public function add_notices() {
			$screen  = get_current_screen();
			$notices = array();

			if ( $this->plugin_screen_hook_suffix !== $screen->id ) {
				// notice if plugin is activated
				if (
						! empty( $this->plugin_settings['general'] ) && is_array( $this->plugin_settings['general'] ) &&
						$this->plugin_settings['general']['status'] === 1 &&
						$this->plugin_settings['general']['notice'] === 1
				) {
					$notices['is_activated'] = array(
						'class' => 'error',
						'msg'   => sprintf(
								/* translators: plugin settings url */
							__( 'The Maintenance Mode is <strong>active</strong>. Please don\'t forget to <a href="%s">deactivate</a> as soon as you are done.', 'wp-maintenance-mode' ),
							add_query_arg( array( 'page' => $this->plugin_slug ), admin_url( 'options-general.php' ) )
						),
					);
				}

				// show notice if plugin has a notice saved
				$wpmm_notice = get_option( 'wpmm_notice' );
				if ( ! empty( $wpmm_notice ) && is_array( $wpmm_notice ) ) {
					$notices['other'] = $wpmm_notice;
				}
			} else {
				// delete wpmm_notice
				delete_option( 'wpmm_notice' );
			}

			// get dismissed notices
			$dismissed_notices = $this->get_dismissed_notices( get_current_user_id() );

			// template
			include_once wpmm_get_template_path( 'notices.php' );
		}

		/**
		 * Dismiss plugin notices via AJAX
		 *
		 * @throws Exception
		 */
		public function dismiss_notices() {
			try {
				$notice_key = isset( $_POST['notice_key'] ) ? sanitize_key( $_POST['notice_key'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing

				if ( empty( $notice_key ) ) {
					throw new Exception( __( 'Notice key cannot be empty.', 'wp-maintenance-mode' ) );
				}
				if ( empty( $_POST['_wpnonce'] ) ) {
					throw new Exception( __( 'The nonce field must not be empty.', 'wp-maintenance-mode' ) );
				}

				// check nonce validation
				if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'notice_nonce_' . $notice_key ) ) {
					throw new Exception( __( 'Security check.', 'wp-maintenance-mode' ) );
				}

				$this->save_dismissed_notices( get_current_user_id(), $notice_key );

				wp_send_json_success();
			} catch ( Exception $ex ) {
				wp_send_json_error( $ex->getMessage() );
			}
		}

		/**
		 * Get dismissed notices
		 *
		 * @param int $user_id
		 * @return array
		 */
		public function get_dismissed_notices( $user_id ) {
			$dismissed_notices = get_user_meta( $user_id, $this->dismissed_notices_key, true );

			return array_filter( explode( ',', $dismissed_notices ), 'trim' );
		}

		/**
		 * Save dismissed notices
		 * - save as string because of http://wordpress.stackexchange.com/questions/13353/problem-storing-arrays-with-update-user-meta
		 *
		 * @param int    $user_id
		 * @param string $notice_key
		 */
		public function save_dismissed_notices( $user_id, $notice_key ) {
			$dismissed_notices   = $this->get_dismissed_notices( $user_id );
			$dismissed_notices[] = $notice_key;

			update_user_meta( $user_id, $this->dismissed_notices_key, implode( ',', $dismissed_notices ) );
		}

		/**
		 * Display custom text on plugin settings page
		 *
		 * @param string $text
		 */
		public function admin_footer_text( $text ) {
			$screen = get_current_screen();

			if ( $this->plugin_screen_hook_suffix === $screen->id ) {
				$text = sprintf(
						/* translators: link to plugin reviews page on wp.org */
					__( 'If you like <strong>WP Maintenance Mode</strong> please leave us a %s rating. A huge thank you from WP Maintenance Mode makers in advance!', 'wp-maintenance-mode' ),
					'<a href="https://wordpress.org/support/view/plugin-reviews/wp-maintenance-mode?filter=5#new-post" class="wpmm_rating" target="_blank">&#9733;&#9733;&#9733;&#9733;&#9733;</a>'
				);
			}

			return $text;
		}

		/**
		 * Return if policy is available. Useful for older WordPress versions.
		 *
		 * @return boolean
		 */
		public function get_is_policy_available() {
			return function_exists( 'get_privacy_policy_url' );
		}

		/**
		 * Return privacy policy link
		 *
		 * @return string
		 */
		public function get_policy_link() {
			// Check feature is available
			if ( $this->get_is_policy_available() ) {
				return get_privacy_policy_url();
			}
		}

		/**
		 * Return message about privacy policy link
		 *
		 * @return string
		 */
		public function get_policy_link_message() {
			$url = $this->get_policy_link();

			if ( $this->get_is_policy_available() && $this->plugin_settings['gdpr']['policy_page_link'] === '' ) {
				if ( $url === '' ) { // No value and feature available
					return __( 'Your WordPress version supports Privacy settings but you haven\'t set any privacy policy page yet. Go to Settings ➡ Privacy to set one.', 'wp-maintenance-mode' );
				} else { // Value and feature available
					return sprintf(
							/* translators: privacy policy url */
						__( 'The plugin detected this Privacy page: %s – <button>Use this url</button>', 'wp-maintenance-mode' ),
						$url
					);
				}
			} elseif ( $this->get_is_policy_available() && $this->plugin_settings['gdpr']['policy_page_link'] !== '' ) { // Feature available and value set
				if ( $url !== $this->plugin_settings['gdpr']['policy_page_link'] ) { // Current wp privacy page differs from set value
					return __( 'Your Privacy page is pointing to a different URL in WordPress settings. If that\'s correct ignore this message, otherwise UPDATE VALUE TO NEW URL', 'wp-maintenance-mode' );
				}
			} elseif ( ! $this->get_is_policy_available() ) { // No privacy feature available
				return __( 'No privacy features detected for your WordPress version. Update WordPress to get this field automatically filled in or type in the URL that points to your privacy policy page.', 'wp-maintenance-mode' );
			}
		}

	}

}
