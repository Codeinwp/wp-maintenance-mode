<?php

defined( 'ABSPATH' ) || exit;

use ThemeIsle\GutenbergBlocks\CSS\CSS_Handler;

if ( ! class_exists( 'WP_Maintenance_Mode_Admin' ) ) {

	class WP_Maintenance_Mode_Admin {

		const SUBSCRIBE_ROUTE = 'https://api.themeisle.com/tracking/subscribe';

		protected static $instance = null;
		protected $plugin_slug;
		protected $plugin_settings;
		protected $plugin_network_settings;
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
			$this->plugin_network_settings = $plugin->get_plugin_network_settings();
			$this->plugin_default_settings = $plugin->default_settings();
			$this->plugin_basename         = plugin_basename( WPMM_PATH . $this->plugin_slug . '.php' );

			// Load admin style sheet and JavaScript.
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

			// Add the options page and menu item.
			add_action( 'admin_menu', array( $this, 'add_plugin_menu' ) );
			add_action( 'network_admin_menu', array( $this, 'add_plugin_menu' ) );

			add_action( 'admin_init', array( $this, 'maybe_redirect' ) );

			// Add an action link pointing to the options page
			if ( is_multisite() && is_plugin_active_for_network( $this->plugin_basename ) ) {
				// settings link will point to admin_url of the main blog, not to network_admin_url
				add_filter( 'network_admin_plugin_action_links_' . $this->plugin_basename, array( $this, 'add_settings_link' ) );
			} else {
				add_filter( 'plugin_action_links_' . $this->plugin_basename, array( $this, 'add_settings_link' ) );
			}

			// Add admin notices
			add_action( 'admin_notices', array( $this, 'add_notices' ) );

			// Add network admin notices.
			add_action( 'network_admin_notices', array( $this, 'add_notices' ) );
			add_action( 'network_admin_notices', array( $this, 'save_plugin_settings_notice' ) );

			// Add ajax methods
			add_action( 'wp_ajax_wpmm_subscribers_export', array( $this, 'subscribers_export' ) );
			add_action( 'wp_ajax_wpmm_subscribers_empty_list', array( $this, 'subscribers_empty_list' ) );
			add_action( 'wp_ajax_wpmm_dismiss_notices', array( $this, 'dismiss_notices' ) );
			add_action( 'wp_ajax_wpmm_reset_settings', array( $this, 'reset_plugin_settings' ) );
			add_action( 'wp_ajax_wpmm_select_page', array( $this, 'select_page' ) );
			add_action( 'wp_ajax_wpmm_insert_template', array( $this, 'insert_template' ) );
			add_action( 'wp_ajax_wpmm_skip_wizard', array( $this, 'skip_wizard' ) );
			add_action( 'wp_ajax_wpmm_subscribe', array( $this, 'subscribe_newsletter' ) );
			add_action( 'wp_ajax_wpmm_change_template_category', array( $this, 'change_template_category' ) );
			add_action( 'wp_ajax_wpmm_toggle_gutenberg', array( $this, 'toggle_gutenberg' ) );
			add_action( 'wp_ajax_wpmm_update_sdk_options', array( $this, 'wpmm_update_sdk_options' ) );

			// Add admin_post_$action
			add_action( 'admin_post_wpmm_save_settings', array( $this, 'save_plugin_settings' ) );

			// Add text to footer
			add_filter( 'admin_footer_text', array( $this, 'admin_footer_text' ), 5 );

			// Wizard screen setup
			add_filter( 'admin_body_class', array( $this, 'add_wizard_classes' ) );

			// Display custom page state
			add_filter( 'display_post_states', array( $this, 'add_display_post_states' ), 10, 2 );
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
		 * @return void
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

				// wizard stylesheet
				if ( get_option( 'wpmm_fresh_install', false ) ) {
					wp_enqueue_style( $this->plugin_slug . '-wizard-styles', WPMM_CSS_URL . 'style-wizard' . WPMM_ASSETS_SUFFIX . '.css', array( 'wp-components' ), WP_Maintenance_Mode::VERSION );
				}
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
					'wpmmVars',
					array(
						'ajaxURL'                => admin_url( 'admin-ajax.php' ),
						'pluginURL'              => add_query_arg( array( 'page' => $this->plugin_slug ), admin_url( 'options-general.php' ) ),
						'ajaxNonce'              => wp_create_nonce( 'ajax' ),
						'wizardNonce'            => wp_create_nonce( 'wizard' ),
						'pluginInstallNonce'     => wp_create_nonce( 'updates' ),
						'isOtterInstalled'       => file_exists( ABSPATH . 'wp-content/plugins/otter-blocks/otter-blocks.php' ),
						'isOtterActive'          => is_plugin_active( 'otter-blocks/otter-blocks.php' ),
						'isOptimoleInstalled'    => file_exists( ABSPATH . 'wp-content/plugins/optimole-wp/optimole-wp.php' ),
						'isOptimoleActive'       => is_plugin_active( 'optimole-wp/optimole-wp.php' ),
						'errorString'            => __( 'Something went wrong, please try again.', 'wp-maintenance-mode' ),
						'loadingString'          => __( 'Doing some magic...', 'wp-maintenance-mode' ),
						'importingText'          => __( 'Importing', 'wp-maintenance-mode' ),
						'importDone'             => __( 'Done', 'wp-maintenance-mode' ),
						'invalidEmailString'     => __( 'Invalid email, please try again.', 'wp-maintenance-mode' ),
						'finishWizardStrings'    => array(
							'maintenance' => __( 'Your maintenance page is ready!', 'wp-maintenance-mode' ),
							'coming-soon' => __( 'Your coming soon page is ready!', 'wp-maintenance-mode' ),
						),
						'adminURL'               => get_admin_url(),
						'otterActivationLink'    => add_query_arg(
							array(
								'action'        => 'activate',
								'plugin'        => rawurlencode( 'otter-blocks/otter-blocks.php' ),
								'plugin_status' => 'all',
								'paged'         => '1',
								'_wpnonce'      => wp_create_nonce( 'activate-plugin_otter-blocks/otter-blocks.php' ),
							),
							esc_url( network_admin_url( 'plugins.php' ) )
						),
						'optimoleActivationLink' => add_query_arg(
							array(
								'action'        => 'activate',
								'plugin'        => rawurlencode( 'optimole-wp/optimole-wp.php' ),
								'plugin_status' => 'all',
								'paged'         => '1',
								'_wpnonce'      => wp_create_nonce( 'activate-plugin_optimole-wp/optimole-wp.php' ),
							),
							esc_url( network_admin_url( 'plugins.php' ) )
						),
						'modalTexts'             => array(
							'title'          => __( 'The template has been imported!', 'wp-maintenance-mode' ),
							'description'    => __( 'The template has been imported to a new draft page. You can take a look and enable it from plugin settings.', 'wp-maintenance-mode' ),
							'buttonPage'     => __( 'Go to page', 'wp-maintenance-mode' ),
							'buttonSettings' => __( 'Go to Settings', 'wp-maintenance-mode' ),
						),
						'confirmModalTexts'      => array(
							'title'          => __( 'Import this template?', 'wp-maintenance-mode' ),
							'description'    => __( 'By importing this template, the existing content on your Maintenance Page will be replaced. Do you wish to continue?', 'wp-maintenance-mode' ),
							'buttonContinue' => __( 'Continue', 'wp-maintenance-mode' ),
							'buttonGoBack'   => __( 'Go back', 'wp-maintenance-mode' ),
						),
						'imageUploaderDefaults'  => array(
							'title'      => _x( 'Upload Image', 'image_uploader default title', 'wp-maintenance-mode' ),
							'buttonText' => _x( 'Choose Image', 'image_uploader default button_text', 'wp-maintenance-mode' ),
						),
						'skipImportStrings'      => array(
							'maintenance'  => __( 'I don’t want to use a Maintenance Template', 'wp-maintenance-mode' ),
							'coming-soon'  => __( 'I don’t want to use a Coming Soon Template', 'wp-maintenance-mode' ),
							'landing-page' => __( 'I don’t want to use a Landing Page Template', 'wp-maintenance-mode' ),
						),
						'skipImportDefault'      => __( 'I don’t want to use a template', 'wp-maintenance-mode' ),
					)
				);

				// add code editor (Code Mirror) to the `other_custom_css` textarea
				if ( ! get_option( 'wpmm_new_look' ) && isset( $GLOBALS['wp_version'] ) && version_compare( $GLOBALS['wp_version'], '4.9.0', '>=' ) && function_exists( 'wp_enqueue_code_editor' ) ) {
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
			$parent_menu              = 'options-general.php';
			$network_menu_hook_suffix = '';
			if ( is_multisite() && is_network_admin() ) {
				$parent_menu              = 'settings.php';
				$network_menu_hook_suffix = '-network';
			}
			$this->plugin_screen_hook_suffix = add_submenu_page(
				$parent_menu,
				__( 'LightStart', 'wp-maintenance-mode' ),
				__( 'LightStart', 'wp-maintenance-mode' ),
				wpmm_get_capability( 'settings' ),
				$this->plugin_slug,
				array( $this, 'display_plugin_settings' )
			);
			$this->plugin_screen_hook_suffix = $this->plugin_screen_hook_suffix . $network_menu_hook_suffix;
		}

		public function maybe_redirect() {
			if ( ! get_option( 'wpmm_fresh_install', false ) || ! get_option( 'wpmm_settings_redirect', '1' ) ) {
				return;
			}

			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				return;
			}

			if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
				return;
			}

			update_option( 'wpmm_settings_redirect', '0' );
			wp_safe_redirect( admin_url( 'options-general.php?page=wp-maintenance-mode' ) );
			exit;
		}

		/**
		 * Settings page
		 *
		 * @since 2.0.0
		 */
		public function display_plugin_settings() {
			if ( is_multisite() && is_network_admin() ) {
				include_once wpmm_get_template_path( 'network-settings.php' );
			} else {
				include_once wpmm_get_template_path( 'settings.php' );
			}
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
					if ( isset( $_POST['options']['general']['bypass_bots'] ) ) {
						$_POST['options']['general']['bypass_bots'] = (int) $_POST['options']['general']['bypass_bots'];
					}

					$_POST['options']['general']['backend_role']  = ! empty( $_POST['options']['general']['backend_role'] ) ? array_map( 'sanitize_text_field', $_POST['options']['general']['backend_role'] ) : array();
					$_POST['options']['general']['frontend_role'] = ! empty( $_POST['options']['general']['frontend_role'] ) ? array_map( 'sanitize_text_field', $_POST['options']['general']['frontend_role'] ) : array();
					if ( isset( $_POST['options']['general']['meta_robots'] ) ) {
						$_POST['options']['general']['meta_robots'] = (int) $_POST['options']['general']['meta_robots'];
					}
					if ( isset( $_POST['options']['general']['redirection'] ) ) {
						$_POST['options']['general']['redirection'] = esc_url_raw( $_POST['options']['general']['redirection'] );
					}
					if ( ! empty( $_POST['options']['general']['exclude'] ) ) {
						$exclude_array = explode( "\n", $_POST['options']['general']['exclude'] );
						// we need to be sure that empty lines will not be saved
						$_POST['options']['general']['exclude'] = array_filter( array_map( 'trim', $exclude_array ) );
						$_POST['options']['general']['exclude'] = array_map( 'sanitize_textarea_field', $_POST['options']['general']['exclude'] );
					} else {
						$_POST['options']['general']['exclude'] = array();
					}
					if ( isset( $_POST['options']['general']['notice'] ) ) {
						$_POST['options']['general']['notice'] = (int) $_POST['options']['general']['notice'];
					}

					if ( ! empty( $_POST['options']['general']['admin_link'] ) ) {
						$_POST['options']['general']['admin_link'] = (int) $_POST['options']['general']['admin_link'];
					}

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
					if ( isset( $_POST['options']['general']['network_mode'] ) ) {
						$_POST['options']['general']['network_mode'] = (int) $_POST['options']['general']['network_mode'];
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

					// Google Analytics
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

			$redirect_to = wpmm_option_page_url();
			if ( ! empty( $_POST['options']['is_network_site'] ) ) {
				$redirect_to           = network_admin_url( 'settings.php' );
				$option_name           = 'wpmm_settings_network';
				$this->plugin_settings = array(
					'general' => array(
						'status'       => $this->plugin_settings['general']['status'],
						'network_mode' => $this->plugin_settings['general']['network_mode'],
					),
				);
				update_network_option( get_current_network_id(), 'wpmm_settings_network', $this->plugin_settings );
			} else {
				update_option( 'wpmm_settings', $this->plugin_settings );
			}

			// redirect back
			wp_safe_redirect(
				add_query_arg(
					array(
						'page'    => $this->plugin_slug,
						'updated' => true,
					),
					$redirect_to
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
		 * Select a page as Maintenance Page
		 *
		 * @return void
		 */
		public function select_page() {
			// check nonce existence
			if ( empty( $_POST['_wpnonce'] ) ) {
				die( esc_html__( 'The nonce field must not be empty.', 'wp-maintenance-mode' ) );
			}

			// check nonce validation
			if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'tab-design' ) ) {
				die( esc_html__( 'Security check.', 'wp-maintenance-mode' ) );
			}

			$this->plugin_settings['design']['page_id'] = $_POST['page_id'];
			wp_update_post(
				array(
					'ID'            => $this->plugin_settings['design']['page_id'],
					'page_template' => 'templates/wpmm-page-template.php',
				)
			);

			update_option( 'wpmm_settings', $this->plugin_settings );

			wp_send_json_success();
		}

		/**
		 * Insert the content from the template to the Maintenance Page
		 * If no Maintenance Page exists, create one.
		 *
		 * @return void
		 */
		public function insert_template() {
			if ( ! is_plugin_active( 'otter-blocks/otter-blocks.php' ) ) {
				wp_send_json_error( array( 'error' => 'Otter Blocks is not activated' ) );
			}

			// check nonce existence
			if ( empty( $_POST['_wpnonce'] ) ) {
				die( esc_html__( 'The nonce field must not be empty.', 'wp-maintenance-mode' ) );
			}

			// check nonce validation
			if ( ! wp_verify_nonce( $_POST['_wpnonce'], $_POST['source'] ) ) {
				die( esc_html__( 'Security check.', 'wp-maintenance-mode' ) );
			}

			$template_slug = $_POST['template_slug'];
			$category      = $_POST['category'];
			$template      = json_decode( file_get_contents( WPMM_TEMPLATES_PATH . $category . '/' . $template_slug . '/blocks-export.json' ) );

			$blocks = str_replace( '\n', '', $template->content );

			$post_arr = array(
				'post_type'     => 'page',
				'post_status'   => 'private',
				'post_content'  => $blocks,
				'page_template' => 'templates/wpmm-page-template.php',
			);

			if ( isset( $this->plugin_settings['design']['page_id'] ) && get_post_status( $this->plugin_settings['design']['page_id'] ) && get_post_status( $this->plugin_settings['design']['page_id'] ) !== 'trash' ) {
				$post_arr['ID'] = $this->plugin_settings['design']['page_id'];
				$page_id        = wp_update_post( $post_arr );
			} else {
				$post_arr['post_title'] = __( 'Maintenance Page', 'wp-maintenance-mode' );
				$page_id                = wp_insert_post( $post_arr );
			}

			if ( $page_id === 0 || $page_id instanceof WP_Error ) {
				wp_send_json_error( array( 'error' => 'Could not get the page' ) );
			}

			$this->plugin_settings['design']['page_id'] = $page_id;
			CSS_Handler::generate_css_file( $page_id );

			if ( 'wizard' === $_POST['source'] ) {
				$this->plugin_settings['general']['status'] = 1;
				update_option( 'wpmm_fresh_install', false );
			}

			update_option( 'wpmm_page_category', $category );
			update_option( 'wpmm_settings', $this->plugin_settings );
			wp_send_json_success( array( 'pageEditURL' => get_edit_post_link( $page_id ) ) );
		}

		/**
		 * Skip importing a template (and installing Otter) from the wizard
		 *
		 * @return void
		 */
		public function skip_wizard() {
			// check nonce existence
			if ( empty( $_POST['_wpnonce'] ) ) {
				die( esc_html__( 'The nonce field must not be empty.', 'wp-maintenance-mode' ) );
			}

			// check nonce validation
			if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'wizard' ) ) {
				die( esc_html__( 'Security check.', 'wp-maintenance-mode' ) );
			}

			update_option( 'wpmm_fresh_install', false );
			wp_send_json_success();
		}

		/**
		 * Subscribe user to plugin newsletter
		 *
		 * @return void
		 */
		public function subscribe_newsletter() {
			// check nonce existence
			if ( empty( $_POST['_wpnonce'] ) ) {
				die( esc_html__( 'The nonce field must not be empty.', 'wp-maintenance-mode' ) );
			}

			// check nonce validation
			if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'wizard' ) ) {
				die( esc_html__( 'Security check.', 'wp-maintenance-mode' ) );
			}

			if ( ! isset( $_POST['email'] ) ) {
				die( esc_html__( 'Empty field: email', 'wp-maintenance-mode' ) );
			}

			$response = wp_remote_post(
				self::SUBSCRIBE_ROUTE,
				array(
					'headers' => array(
						'Content-Type' => 'application/json',
					),
					'body'    => wp_json_encode(
						array(
							'slug'  => 'wp-maintenance-mode',
							'site'  => get_site_url(),
							'email' => $_POST['email'],
							'data'  => array(
								'category' => get_option( 'wpmm_page_category' ),
							),
						)
					),
				)
			);

			if ( is_wp_error( $response ) ) {
				wp_send_json_error( $response->get_error_message() );
			}

			wp_send_json_success( $response );
		}

		/**
		 * Change the category of templates to display
		 *
		 * @return void
		 */
		public function change_template_category() {
			// check nonce existence
			if ( empty( $_POST['_wpnonce'] ) ) {
				die( esc_html__( 'The nonce field must not be empty.', 'wp-maintenance-mode' ) );
			}

			// check nonce validation
			if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'tab-design' ) ) {
				die( esc_html__( 'Security check.', 'wp-maintenance-mode' ) );
			}

			if ( empty( $_POST['category'] ) ) {
				die( esc_html__( 'Empty field: category.', 'wp-maintenance-mode' ) );
			}

			$this->plugin_settings['design']['template_category'] = $_POST['category'];
			update_option( 'wpmm_settings', $this->plugin_settings );

			wp_send_json_success();
		}

		/**
		 * Migrate to the new method of building the maintenance page or downgrade to the old one.
		 * Used from the migration notice.
		 *
		 * @return void
		 */
		public function toggle_gutenberg() {
			if ( empty( $_POST['source'] ) ) {
				die( esc_html__( 'The source filed must not be empty.', 'wp-maintenance-mode' ) );
			}

			// check nonce existence
			if ( empty( $_POST['_wpnonce'] ) ) {
				die( esc_html__( 'The nonce field must not be empty.', 'wp-maintenance-mode' ) );
			}

			// check nonce validation
			if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'notice_nonce_' . $_POST['source'] ) ) {
				die( esc_html__( 'Security check.', 'wp-maintenance-mode' ) );
			}

			$current_option = get_option( 'wpmm_new_look', false );
			update_option( 'wpmm_new_look', ! $current_option );

			if ( ! $current_option && ! get_option( 'wpmm_migration_time' ) ) {
				update_option( 'wpmm_migration_time', time() );
			}

			wp_send_json_success();
		}

		/**
		 * Updates options to track Otter traffic
		 *
		 * @return void
		 */
		function wpmm_update_sdk_options() {
			// check nonce existence
			if ( empty( $_POST['_wpnonce'] ) ) {
				die( esc_html__( 'The nonce field must not be empty.', 'wp-maintenance-mode' ) );
			}

			// check nonce validation
			if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'ajax' ) ) {
				die( esc_html__( 'Security check.', 'wp-maintenance-mode' ) );
			}

			update_option( 'themeisle_sdk_promotions_otter_installed', true );
			update_option( 'otter_reference_key', 'wp-maintenance-mode' );

			wp_send_json_success();
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

			// show this notice if user had the plugin installed on the moment of rebranding
			if ( ThemeisleSDK\Product::get( WPMM_FILE )->get_install_time() < strtotime( '2022-11-02' ) ) {
				$notices['rebrand'] = array(
					'class' => 'notice wpmm_notices notice-success is-dismissible',
					'msg'   => __( 'WP Maintenance Mode is now LightStart. Enjoy the same features, more templates and new landing pages building compatibility.', 'wp-maintenance-mode' ),
				);
			}

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

				if ( ! get_option( 'wpmm_fresh_install' ) && get_option( 'wpmm_new_look' ) && $this->plugin_settings['general']['status'] === 1 ) {
					if ( isset( $this->plugin_settings['design']['page_id'] ) ) {
						$maintenance_page = get_post( $this->plugin_settings['design']['page_id'] );

						if ( ( $maintenance_page instanceof WP_Post ) && $maintenance_page->post_status !== 'publish' && $maintenance_page->post_status !== 'private' ) {
							$notices['maintenance_page_deleted'] = array(
								'class' => 'error',
								'msg'   => $maintenance_page->post_status === 'draft' ?
									sprintf( __( '<strong>Action required</strong>: your Maintenance page is drafted. Visit the page to <a href="%s">publish</a> it.', 'wp-maintenance-mode' ), get_edit_post_link( $maintenance_page ) ) :
									sprintf( __( '<strong>Action required</strong>: your Maintenance page has been deleted. Visit <a href="%s">settings page</a> to address this issue.', 'wp-maintenance-mode' ), get_admin_url() . 'options-general.php?page=wp-maintenance-mode#design' ),
							);
						}
					}

					// check if the maintenance.php template is overridden
					$overrideable_template = wpmm_get_template_path( 'maintenance.php', true );
					if ( WPMM_VIEWS_PATH . 'maintenance.php' === $overrideable_template ) {
						if ( ! isset( $this->plugin_settings['design']['page_id'] ) || ! get_post( $this->plugin_settings['design']['page_id'] ) ) {
							$notices['maintenance_page_not_found'] = array(
								'class' => 'error',
								'msg'   => sprintf( __( '<strong>Action required</strong>: you don\'t have a page as Maintenance page. Visit <a href="%s">settings page</a> to select one.', 'wp-maintenance-mode' ), get_admin_url() . 'options-general.php?page=wp-maintenance-mode#design' ),
							);
						}
					}
				}

				// show notice if plugin has a notice saved
				$wpmm_notice = get_option( 'wpmm_notice' );
				if ( ! empty( $wpmm_notice ) && is_array( $wpmm_notice ) ) {
					$notices['other'] = $wpmm_notice;
				}
			} else {
				if ( get_option( 'wpmm_show_migration', true ) ) {
					if ( ! get_option( 'wpmm_new_look' ) ) {
						$notices['migration'] = array(
							'class' => 'notice notice-success',
							'msg'   => __( 'We upgraded the way maintenance pages are build. Migrate to use Gutenberg for your page!&emsp;<button id="wpmm-migrate" class="button button-primary">Migrate</button>', 'wp-maintenance-mode' ),
						);
					} else {
						$notices['rollback'] = array(
							'class' => 'notice wpmm_notices notice-info is-dismissible',
							'msg'   => __( 'You migrated to use Gutenberg for building the Maintenance page.&emsp;<button id="wpmm-rollback" class="button button-link button-link-delete">Rollback</button>', 'wp-maintenance-mode' ),
						);
					}
				}

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
				if ( empty( $_POST['_nonce'] ) ) {
					throw new Exception( __( 'The nonce field must not be empty.', 'wp-maintenance-mode' ) );
				}

				// check nonce validation
				if ( ! wp_verify_nonce( $_POST['_nonce'], 'notice_nonce_' . $notice_key ) ) {
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
					__( 'If you like <strong>Lightstart</strong> please leave us a %s rating. A huge thank you from WP Maintenance Mode makers in advance!', 'wp-maintenance-mode' ),
					'<a href="https://wordpress.org/support/view/plugin-reviews/wp-maintenance-mode?filter=5#new-post" class="wpmm_rating" target="_blank">&#9733;&#9733;&#9733;&#9733;&#9733;</a>'
				);
			}

			return $text;
		}

		/**
		 * Add custom state to the maintenance page
		 *
		 * @param array $post_states Post states.
		 * @param WP_Post $post Current post.
		 * @return array
		 */
		public function add_display_post_states( $post_states, $post ) {
			if ( isset( $this->plugin_settings['design']['page_id'] ) && $this->plugin_settings['design']['page_id'] == $post->ID ) {
				$post_states['wpmm_for_maintenance'] = WP_Maintenance_Mode::get_page_status_by_category( get_option( 'wpmm_page_category', 'maintenance' ) );
			}

			return $post_states;
		}

		/**
		 * Add classes to make the wizard full screen
		 *
		 * @param string $classes Body classes.
		 * @return string
		 */
		public function add_wizard_classes( $classes ) {
			if ( get_option( 'wpmm_fresh_install', false ) ) {
				$classes .= 'wpmm-wizard-fullscreen';
			}

			return $classes;
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

			return '';
		}

		/**
		 * Return external link icon
		 *
		 * @return string
		 */
		public function get_external_link_icon() {
			return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" class="wpmm-external_link_icon" aria-hidden="true" focusable="false"><path d="M18.2 17c0 .7-.6 1.2-1.2 1.2H7c-.7 0-1.2-.6-1.2-1.2V7c0-.7.6-1.2 1.2-1.2h3.2V4.2H7C5.5 4.2 4.2 5.5 4.2 7v10c0 1.5 1.2 2.8 2.8 2.8h10c1.5 0 2.8-1.2 2.8-2.8v-3.6h-1.5V17zM14.9 3v1.5h3.7l-6.4 6.4 1.1 1.1 6.4-6.4v3.7h1.5V3h-6.3z"></path></svg>';
		}

		/**
		 * Returns the HTML of the Otter notice
		 *
		 * @return string
		 */
		public function get_otter_notice( $location = null ) {
			return sprintf(
				'<div class="wpmm_otter-notice">
					<div class="wpmm_otter-notice__logo">
						<img src="%s"/>
					</div>
					<div class="wpmm_otter-notice__text">%s&nbsp;<a href="%s" target="_blank">%s</a></div>
				</div>',
				esc_url( WPMM_URL . 'assets/images/otter-logo.svg' ),
				/* translators: %1$s %2$s bold text tags */
				sprintf( __( 'These templates make use of %1$s Otter Blocks %2$s powerful features, which will be installed and activated.', 'wp-maintenance-mode' ), '<b>', '</b>' ),
				tsdk_utmify( 'https://themeisle.com/plugins/otter-blocks/', $this->plugin_slug, $location ),
				__( 'Learn more about Otter.', 'wp-maintenance-mode' ) . $this->get_external_link_icon()
			);
		}

		/**
		 * Display save plugin settings notice.
		 */
		public function save_plugin_settings_notice() {
			$screen  = get_current_screen();
			$notices = array();

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( ! empty( $_GET['updated'] ) && $this->plugin_screen_hook_suffix === $screen->id ) { ?>
				<div id="message" class="updated notice is-dismissible"><p><strong><?php esc_html_e( 'Settings saved.', 'wp-maintenance-mode' ); ?></strong></p></div>
				<?php
			}
		}
	}
}
