<?php
/**
 * Abilities API integration.
 *
 * @package wp-maintenance-mode
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WP_Maintenance_Mode_Abilities' ) ) {

	/**
	 * Registers the plugin abilities with the WordPress Abilities API.
	 */
	class WP_Maintenance_Mode_Abilities {

		const CATEGORY = 'lightstart';

		const MAX_PER_PAGE = 100;

		const MAX_EXCLUDE_ITEMS = 200;

		/**
		 * Hook the registration callbacks.
		 *
		 * @return void
		 */
		public static function init() {
			if ( ! function_exists( 'wp_register_ability' ) ) {
				return;
			}

			add_action( 'wp_abilities_api_categories_init', array( __CLASS__, 'register_category' ) );
			add_action( 'wp_abilities_api_init', array( __CLASS__, 'register_abilities' ) );
		}

		/**
		 * Register the ability category.
		 *
		 * @return void
		 */
		public static function register_category() {
			if ( ! function_exists( 'wp_register_ability_category' ) ) {
				return;
			}

			wp_register_ability_category(
				self::CATEGORY,
				array(
					'label'       => __( 'LightStart', 'wp-maintenance-mode' ),
					'description' => __( 'Maintenance mode, coming soon and landing page abilities.', 'wp-maintenance-mode' ),
				)
			);
		}

		/**
		 * Register the abilities.
		 *
		 * @return void
		 */
		public static function register_abilities() {
			wp_register_ability(
				'lightstart/get-mode',
				array(
					'label'               => __( 'Get maintenance mode', 'wp-maintenance-mode' ),
					'description'         => __( 'Returns whether maintenance mode is enabled, the selected maintenance page and the bypass / exclusion rules.', 'wp-maintenance-mode' ),
					'category'            => self::CATEGORY,
					'input_schema'        => array(
						'type'                 => 'object',
						'default'              => array(),
						'additionalProperties' => false,
					),
					'output_schema'       => self::get_mode_schema(),
					'execute_callback'    => array( __CLASS__, 'get_mode' ),
					'permission_callback' => array( __CLASS__, 'can_manage_settings' ),
					'meta'                => array(
						'annotations'  => array(
							'readonly'    => true,
							'destructive' => false,
							'idempotent'  => true,
						),
						'show_in_rest' => true,
					),
				)
			);

			wp_register_ability(
				'lightstart/set-mode',
				array(
					'label'               => __( 'Set maintenance mode', 'wp-maintenance-mode' ),
					'description'         => __( 'Enables or disables maintenance mode and optionally updates the selected (existing) maintenance page and the bypass / exclusion rules. Only the provided fields are changed.', 'wp-maintenance-mode' ),
					'category'            => self::CATEGORY,
					'input_schema'        => array(
						'type'                 => 'object',
						'properties'           => array(
							'enabled'        => array(
								'type'        => 'boolean',
								'description' => __( 'Turn maintenance mode on or off.', 'wp-maintenance-mode' ),
							),
							'page_id'        => array(
								'type'        => 'integer',
								'minimum'     => 0,
								'description' => __( 'ID of an existing published or private page to use as the maintenance page. Use 0 to clear the selection.', 'wp-maintenance-mode' ),
							),
							'frontend_roles' => array(
								'type'        => 'array',
								'items'       => array( 'type' => 'string' ),
								'description' => __( 'Role slugs that bypass maintenance mode on the frontend. Administrators always bypass it.', 'wp-maintenance-mode' ),
							),
							'backend_roles'  => array(
								'type'        => 'array',
								'items'       => array( 'type' => 'string' ),
								'description' => __( 'Role slugs that keep access to the dashboard while maintenance mode is on.', 'wp-maintenance-mode' ),
							),
							'exclude'        => array(
								'type'        => 'array',
								'items'       => array( 'type' => 'string' ),
								'description' => __( 'Slugs, URL fragments or IP addresses excluded from maintenance mode. Replaces the current list.', 'wp-maintenance-mode' ),
							),
							'bypass_bots'    => array(
								'type'        => 'boolean',
								'description' => __( 'Let search engine bots bypass maintenance mode.', 'wp-maintenance-mode' ),
							),
							'dry_run'        => array(
								'type'        => 'boolean',
								'default'     => false,
								'description' => __( 'Validate the input and return the resulting state without saving anything.', 'wp-maintenance-mode' ),
							),
						),
						'additionalProperties' => false,
					),
					'output_schema'       => self::get_mode_schema( true ),
					'execute_callback'    => array( __CLASS__, 'set_mode' ),
					'permission_callback' => array( __CLASS__, 'can_manage_settings' ),
					'meta'                => array(
						'annotations'  => array(
							'readonly'    => false,
							'destructive' => true,
							'idempotent'  => true,
						),
						'show_in_rest' => true,
					),
				)
			);

			wp_register_ability(
				'lightstart/list-subscribers',
				array(
					'label'               => __( 'List subscribers', 'wp-maintenance-mode' ),
					'description'         => __( 'Returns the email addresses captured by the maintenance page subscribe form, newest first, with pagination.', 'wp-maintenance-mode' ),
					'category'            => self::CATEGORY,
					'input_schema'        => array(
						'type'                 => 'object',
						'default'              => array(),
						'properties'           => array(
							'page'     => array(
								'type'    => 'integer',
								'minimum' => 1,
								'default' => 1,
							),
							'per_page' => array(
								'type'    => 'integer',
								'minimum' => 1,
								'maximum' => self::MAX_PER_PAGE,
								'default' => 20,
							),
						),
						'additionalProperties' => false,
					),
					'output_schema'       => array(
						'type'       => 'object',
						'properties' => array(
							'subscribers' => array(
								'type'  => 'array',
								'items' => array(
									'type'       => 'object',
									'properties' => array(
										'id'          => array( 'type' => 'integer' ),
										'email'       => array( 'type' => 'string' ),
										'insert_date' => array( 'type' => 'string' ),
									),
								),
							),
							'total'       => array( 'type' => 'integer' ),
							'total_pages' => array( 'type' => 'integer' ),
							'page'        => array( 'type' => 'integer' ),
							'per_page'    => array( 'type' => 'integer' ),
						),
					),
					'execute_callback'    => array( __CLASS__, 'list_subscribers' ),
					'permission_callback' => array( __CLASS__, 'can_manage_subscribers' ),
					'meta'                => array(
						'annotations'  => array(
							'readonly'    => true,
							'destructive' => false,
							'idempotent'  => true,
						),
						'show_in_rest' => true,
					),
				)
			);
		}

		/**
		 * Permission check, same capability as the settings screen.
		 *
		 * @return bool
		 */
		public static function can_manage_settings() {
			return current_user_can( wpmm_get_capability( 'settings' ) );
		}

		/**
		 * Permission check, same capability as the subscribers export.
		 *
		 * @return bool
		 */
		public static function can_manage_subscribers() {
			return current_user_can( wpmm_get_capability( 'subscribers' ) );
		}

		/**
		 * Execute: lightstart/get-mode
		 *
		 * @return array<string, mixed>
		 */
		public static function get_mode() {
			return self::format_mode( self::get_settings() );
		}

		/**
		 * Execute: lightstart/set-mode
		 *
		 * @param mixed $input Ability input.
		 * @return array<string, mixed>|WP_Error
		 */
		public static function set_mode( $input = array() ) {
			$input    = is_array( $input ) ? $input : array();
			$dry_run  = isset( $input['dry_run'] ) && wp_validate_boolean( $input['dry_run'] );
			$settings = self::get_settings();
			$general  = isset( $settings['general'] ) && is_array( $settings['general'] ) ? $settings['general'] : array();
			$changed  = array();

			if ( ! array_intersect( array( 'enabled', 'page_id', 'frontend_roles', 'backend_roles', 'exclude', 'bypass_bots' ), array_keys( $input ) ) ) {
				return new WP_Error( 'lightstart_nothing_to_update', __( 'Provide at least one setting to change.', 'wp-maintenance-mode' ), array( 'status' => 400 ) );
			}

			$old_status = ! empty( $general['status'] ) ? 1 : 0;
			$new_status = $old_status;

			if ( isset( $input['enabled'] ) ) {
				if ( ! empty( $general['network_mode'] ) ) {
					return new WP_Error( 'lightstart_network_mode', __( 'Maintenance mode is controlled from the network settings and cannot be changed for this site.', 'wp-maintenance-mode' ), array( 'status' => 409 ) );
				}

				$new_status = wp_validate_boolean( $input['enabled'] ) ? 1 : 0;
				if ( $new_status !== $old_status ) {
					$changed[] = 'enabled';
				}
			}

			foreach (
				array(
					'frontend_roles' => 'frontend_role',
					'backend_roles'  => 'backend_role',
				) as $field => $setting
			) {
				if ( ! isset( $input[ $field ] ) ) {
					continue;
				}

				$roles = self::sanitize_roles( $input[ $field ] );
				if ( is_wp_error( $roles ) ) {
					return $roles;
				}

				$general[ $setting ] = $roles;
				$changed[]           = $field;
			}

			if ( isset( $input['exclude'] ) ) {
				if ( ! is_array( $input['exclude'] ) || count( $input['exclude'] ) > self::MAX_EXCLUDE_ITEMS ) {
					/* translators: maximum number of items */
					return new WP_Error( 'lightstart_invalid_exclude', sprintf( __( 'The exclude list must be an array of at most %d items.', 'wp-maintenance-mode' ), self::MAX_EXCLUDE_ITEMS ), array( 'status' => 400 ) );
				}

				$exclude = array();
				foreach ( $input['exclude'] as $item ) {
					if ( ! is_scalar( $item ) ) {
						continue;
					}
					$item = sanitize_textarea_field( trim( (string) $item ) );
					if ( '' !== $item ) {
						$exclude[] = $item;
					}
				}

				$general['exclude'] = $exclude;
				$changed[]          = 'exclude';
			}

			if ( isset( $input['bypass_bots'] ) ) {
				$general['bypass_bots'] = wp_validate_boolean( $input['bypass_bots'] ) ? 1 : 0;
				$changed[]              = 'bypass_bots';
			}

			$previous_page_id = isset( $settings['design']['page_id'] ) ? absint( $settings['design']['page_id'] ) : 0;
			$page_id          = $previous_page_id;

			if ( isset( $input['page_id'] ) ) {
				$page_id = absint( $input['page_id'] );

				$valid = self::validate_page( $page_id );
				if ( is_wp_error( $valid ) ) {
					return $valid;
				}

				if ( $page_id !== $previous_page_id ) {
					$changed[] = 'page_id';
				}
			}

			$general['status'] = $new_status;
			if ( 1 === $new_status && isset( $input['enabled'] ) ) {
				$general['status_date'] = date( 'Y-m-d H:i:s' );
			}

			$settings['general'] = $general;
			if ( isset( $input['page_id'] ) ) {
				if ( ! isset( $settings['design'] ) || ! is_array( $settings['design'] ) ) {
					$settings['design'] = array();
				}
				$settings['design']['page_id'] = $page_id;
			}

			if ( ! $dry_run ) {
				if ( isset( $input['page_id'] ) ) {
					wpmm_switch_selected_page( $previous_page_id, $page_id );
				}

				// network_mode is a runtime overlay of the network option, it is never stored per site.
				$to_save = $settings;
				if ( is_multisite() ) {
					unset( $to_save['general']['network_mode'] );

					if ( ! empty( $general['network_mode'] ) ) {
						// keep the site's own status, the active one comes from the network.
						$stored                       = wpmm_get_option( 'wpmm_settings', array() );
						$to_save['general']['status'] = ! empty( $stored['general']['status'] ) ? 1 : 0;
					}
				}
				update_option( 'wpmm_settings', $to_save );

				// same rule as the settings screen: purge when it is or becomes active, or gets deactivated.
				if ( 1 === $old_status || 1 === $new_status ) {
					wpmm_delete_cache();
				}
			}

			$result            = self::format_mode( $settings );
			$result['changed'] = $changed;
			$result['dry_run'] = $dry_run;

			return $result;
		}

		/**
		 * Execute: lightstart/list-subscribers
		 *
		 * @param mixed $input Ability input.
		 * @return array<string, mixed>
		 */
		public static function list_subscribers( $input = array() ) {
			global $wpdb;

			$input    = is_array( $input ) ? $input : array();
			$page     = isset( $input['page'] ) ? max( 1, absint( $input['page'] ) ) : 1;
			$per_page = isset( $input['per_page'] ) ? min( self::MAX_PER_PAGE, max( 1, absint( $input['per_page'] ) ) ) : 20;
			$total    = wpmm_get_subscribers_count();
			$rows     = array();

			if ( $total > 0 ) {
				$rows = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT id_subscriber, email, insert_date FROM {$wpdb->prefix}wpmm_subscribers ORDER BY id_subscriber DESC LIMIT %d OFFSET %d",
						$per_page,
						( $page - 1 ) * $per_page
					),
					ARRAY_A
				);
			}

			$subscribers = array();
			foreach ( (array) $rows as $row ) {
				$subscribers[] = array(
					'id'          => (int) $row['id_subscriber'],
					'email'       => (string) $row['email'],
					'insert_date' => (string) $row['insert_date'],
				);
			}

			return array(
				'subscribers' => $subscribers,
				'total'       => $total,
				'total_pages' => (int) ceil( $total / $per_page ),
				'page'        => $page,
				'per_page'    => $per_page,
			);
		}

		/**
		 * Current settings, read fresh from the database with the network overlay applied.
		 *
		 * @return array<string, mixed>
		 */
		private static function get_settings() {
			$plugin   = WP_Maintenance_Mode::get_instance();
			$settings = wpmm_get_option( 'wpmm_settings', array() );
			$settings = is_array( $settings ) ? $settings : array();

			if ( ! isset( $settings['general'] ) || ! is_array( $settings['general'] ) ) {
				$defaults            = $plugin->default_settings();
				$settings['general'] = $defaults['general'];
			}

			if ( is_multisite() ) {
				$network = $plugin->get_plugin_network_settings();

				$settings['general']['network_mode'] = ! empty( $network['general']['network_mode'] ) ? 1 : 0;
				if ( $settings['general']['network_mode'] ) {
					$settings['general']['status'] = ! empty( $network['general']['status'] ) ? 1 : 0;
				}
			}

			return $settings;
		}

		/**
		 * Shape the settings for output.
		 *
		 * @param array<string, mixed> $settings Plugin settings.
		 * @return array<string, mixed>
		 */
		private static function format_mode( $settings ) {
			$general = isset( $settings['general'] ) && is_array( $settings['general'] ) ? $settings['general'] : array();
			$page_id = isset( $settings['design']['page_id'] ) ? absint( $settings['design']['page_id'] ) : 0;
			$status  = $page_id ? get_post_status( $page_id ) : false;

			return array(
				'enabled'        => ! empty( $general['status'] ),
				'status_date'    => isset( $general['status_date'] ) ? (string) $general['status_date'] : '',
				'network_mode'   => ! empty( $general['network_mode'] ),
				'page_id'        => $page_id,
				'page_title'     => $status ? get_the_title( $page_id ) : '',
				'page_status'    => $status ? (string) $status : '',
				'frontend_roles' => isset( $general['frontend_role'] ) ? array_values( array_map( 'strval', (array) $general['frontend_role'] ) ) : array(),
				'backend_roles'  => isset( $general['backend_role'] ) ? array_values( array_map( 'strval', (array) $general['backend_role'] ) ) : array(),
				'exclude'        => isset( $general['exclude'] ) ? array_values( array_map( 'strval', (array) $general['exclude'] ) ) : array(),
				'bypass_bots'    => ! empty( $general['bypass_bots'] ),
				'redirection'    => isset( $general['redirection'] ) ? (string) $general['redirection'] : '',
			);
		}

		/**
		 * Output schema shared by get-mode and set-mode.
		 *
		 * @param bool $with_changes Add the set-mode specific fields.
		 * @return array<string, mixed>
		 */
		private static function get_mode_schema( $with_changes = false ) {
			$strings = array(
				'type'  => 'array',
				'items' => array( 'type' => 'string' ),
			);

			$properties = array(
				'enabled'        => array( 'type' => 'boolean' ),
				'status_date'    => array( 'type' => 'string' ),
				'network_mode'   => array( 'type' => 'boolean' ),
				'page_id'        => array( 'type' => 'integer' ),
				'page_title'     => array( 'type' => 'string' ),
				'page_status'    => array( 'type' => 'string' ),
				'frontend_roles' => $strings,
				'backend_roles'  => $strings,
				'exclude'        => $strings,
				'bypass_bots'    => array( 'type' => 'boolean' ),
				'redirection'    => array( 'type' => 'string' ),
			);

			if ( $with_changes ) {
				$properties['changed'] = $strings;
				$properties['dry_run'] = array( 'type' => 'boolean' );
			}

			return array(
				'type'       => 'object',
				'properties' => $properties,
			);
		}

		/**
		 * Validate role slugs against the roles offered on the settings screen.
		 *
		 * @param mixed $roles Role slugs.
		 * @return string[]|WP_Error
		 */
		private static function sanitize_roles( $roles ) {
			if ( ! is_array( $roles ) ) {
				return new WP_Error( 'lightstart_invalid_roles', __( 'Roles must be an array of role slugs.', 'wp-maintenance-mode' ), array( 'status' => 400 ) );
			}

			$roles   = array_values( array_unique( array_map( 'sanitize_text_field', array_filter( $roles, 'is_scalar' ) ) ) );
			$unknown = array_diff( $roles, array_keys( wpmm_get_user_roles() ) );

			if ( ! empty( $unknown ) ) {
				/* translators: list of role slugs */
				return new WP_Error( 'lightstart_unknown_role', sprintf( __( 'Unknown or not selectable role(s): %s', 'wp-maintenance-mode' ), implode( ', ', $unknown ) ), array( 'status' => 400 ) );
			}

			return $roles;
		}

		/**
		 * Validate a page the same way the settings screen dropdown limits it.
		 *
		 * @param int $page_id Page ID; 0 clears the selection.
		 * @return true|WP_Error
		 */
		private static function validate_page( $page_id ) {
			if ( ! get_option( 'wpmm_new_look' ) ) {
				return new WP_Error( 'lightstart_page_selection_unavailable', __( 'Selecting a maintenance page is only available with the block based (new look) maintenance page.', 'wp-maintenance-mode' ), array( 'status' => 409 ) );
			}

			if ( 0 === $page_id ) {
				return true;
			}

			$page = get_post( $page_id );
			if ( ! $page || 'page' !== $page->post_type || ! in_array( $page->post_status, array( 'publish', 'private' ), true ) ) {
				return new WP_Error( 'lightstart_invalid_page', __( 'The page must be an existing published or private page.', 'wp-maintenance-mode' ), array( 'status' => 404 ) );
			}

			if ( ! current_user_can( 'edit_post', $page_id ) ) {
				return new WP_Error( 'lightstart_cannot_edit_page', __( 'You are not allowed to edit this page.', 'wp-maintenance-mode' ), array( 'status' => 403 ) );
			}

			return true;
		}
	}
}
