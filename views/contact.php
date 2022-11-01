<?php
/**
 * Contact email
 *
 * This template can be overridden by copying it to one of these paths:
 * - /wp-content/themes/{your_child_theme}/wp-maintenance-mode/contact.php
 * - /wp-content/themes/{your_theme}/wp-maintenance-mode/contact.php
 *
 * It can also be overridden by changing the default path. See `wpmm_contact_template` hook:
 * https://github.com/WP-Maintenance-Mode/Snippet-Library/blob/master/change-template-path.php
 *
 * @version 2.4.0
 */

defined( 'ABSPATH' ) || exit;
?>
<!doctype html>
<html>
	<head>
		<style>
			@media screen and (max-width: 500px) {
				#wrap {
					width: 100%;
				}
			}
		</style>
	</head>
	<body style="margin:0;padding:0;background:#efefef;">
		<table align="center" cellpadding="0" cellspacing="0" width="500" id="wrap">
			<tr>
				<td height="60"></td>
			</tr>
			<!-- Content -->
			<tr>
				<td>
					<table style="padding:0 25px;" cellspacing="0" cellspacing="0" style="border:1px solid #e3e5e5" bgcolor="#fff" width="100%">
						<tr>
							<td height="60"></td>
						</tr>
						<tr>
							<td style="text-align:center;">
								<img src="<?php echo esc_url( WPMM_IMAGES_URL . 'icon.svg' ); ?>" />
							</td>
						</tr>
						<tr>
							<td height="40"></td>
						</tr>
						<tr>
							<td style="color:#747e7e;font-family:Lato, Helvetica, Arial, sans-serif;text-align:center;font-size:18px;font-weight:normal;">
								<?php
								echo esc_html(
									sprintf(
												/* translators: name of the blog */
										__( 'You have been contacted via %s.', 'wp-maintenance-mode' ),
										get_bloginfo( 'name' )
									)
								);
								?>
							</td>
						</tr>
						<tr>
							<td height="30"></td>
						</tr>
						<tr>
							<td width="100%">
								<table cellspacing="0" cellpadding="0" width="100%">
									<tbody>
										<?php do_action( 'wpmm_contact_template_start' ); ?>

										<tr>
											<td height="30"></td>
											<td height="30"></td>
										</tr>
										<tr>
											<td width="20%" style="border-bottom:1px solid #e3e5e5;padding:0 0 30px 20px;text-align:left;font-size:14px;font-family:Lato, Helvetica, Arial, sans-serif;color:#747e7e;font-weight:bold;">
												<?php esc_html_e( 'Name:', 'wp-maintenance-mode' ); ?>
											</td>
											<td width="80%" style="border-bottom:1px solid #e3e5e5;padding:0 0 30px 20px;text-align:left;font-size:14px;font-family:Lato, Helvetica, Arial, sans-serif;color:#747e7e;">
												<?php echo esc_html( $name ); ?>
											</td>
										</tr>
										<tr>
											<td height="30"></td>
											<td height="30"></td>
										</tr>
										<tr>
											<td width="20%" style="border-bottom:1px solid #e3e5e5;padding:0 0 30px 20px;text-align:left;font-size:14px;font-family:Lato, Helvetica, Arial, sans-serif;color:#747e7e;font-weight:bold;">
												<?php esc_html_e( 'Email:', 'wp-maintenance-mode' ); ?>
											</td>
											<td width="80%" style="border-bottom:1px solid #e3e5e5;padding:0 0 30px 20px;text-align:left;font-size:14px;font-family:Lato, Helvetica, Arial, sans-serif;color:#747e7e;">
												<?php echo esc_html( $email ); ?>
											</td>
										</tr>

										<?php do_action( 'wpmm_contact_template_before_message' ); ?>

										<tr>
											<td height="30"></td>
											<td height="30"></td>
										</tr>
										<tr>
											<td colspan="2" style="padding:0 0 30px 20px;text-align:left;font-size:14px;font-family:Lato, Helvetica, Arial, sans-serif;color:#747e7e;font-weight:bold;">
												<?php esc_html_e( 'Content:', 'wp-maintenance-mode' ); ?>
											</td>
										</tr>
										<tr>
											<td colspan="2" style="padding:0 0 20px 20px;text-align:left;font-size:14px;font-family:Lato, Helvetica, Arial, sans-serif;color:#747e7e;">
												<?php echo nl2br( esc_html( $content ) ); ?>
											</td>
										</tr>

										<?php do_action( 'wpmm_contact_template_after_message' ); ?>

										<?php do_action( 'wpmm_contact_template_end' ); ?>
									</tbody>
								</table>
							</td>
						</tr>
						<tr>
							<td height="60"></td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td height="60"></td>
			</tr>
			<!-- End Content -->
		</table>
	</body>
</html>
