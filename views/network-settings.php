<?php
/**
 * Settings
 *
 * @version 2.1.6
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap">
	<h2 class="wpmm-title"><?php echo esc_html( get_admin_page_title() ); ?>
		<?php
		if ( get_option( 'wpmm_fresh_install', false ) ) {
			?>
			<span id="wizard-exit"><img src="<?php echo esc_attr( WPMM_IMAGES_URL . 'external.svg' ); ?>" alt="exit"></span><?php } ?>
	</h2>
	<hr>
	<div class="wpmm-wrapper">
		<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
			<input type="hidden" name="options[is_network_site]" value="<?php echo (bool) is_multisite() && is_network_admin(); ?>">
			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row">
							<label for="options[general][status]"><?php esc_html_e( 'Enable network mode', 'wp-maintenance-mode' ); ?></label>
						</th>
						<td>
							<label><input type="checkbox" class="wpmm_network_mode" value="1" name="options[general][network_mode]"<?php checked( $this->plugin_network_settings['general']['network_mode'], 1 ); ?> /></label>
						</td>
					</tr>
					<tr valign="top" class="wpmm_status <?php echo empty( $this->plugin_network_settings['general']['network_mode'] ) ? 'wpmm_status_disable' : ''; ?>">
						<th scope="row">
							<label for="options[general][status]"><?php esc_html_e( 'Status', 'wp-maintenance-mode' ); ?></label>
						</th>
						<td>
							<label><input type="radio" value="1" name="options[general][status]"<?php checked( $this->plugin_network_settings['general']['status'], 1 ); ?> /> <?php esc_html_e( 'Activated on all sites', 'wp-maintenance-mode' ); ?></label> <br />
							<label><input type="radio" value="0" name="options[general][status]"<?php checked( $this->plugin_network_settings['general']['status'], 0 ); ?> /> <?php esc_html_e( 'Deactivated for all sites', 'wp-maintenance-mode' ); ?></label>
						</td>
					</tr>
				</tbody>
			</table>
			<?php wpmm_form_hidden_fields( 'general' ); ?>
			<input type="submit" value="<?php esc_attr_e( 'Save settings', 'wp-maintenance-mode' ); ?>" class="button button-primary" name="submit" />
		</form>
		<?php require_once 'sidebar.php'; ?>
	</div>
</div>
