<?php
/**
 * Promo Pulsetic
 *
 * @version 2.4.0
 */

defined( 'ABSPATH' ) || exit;
?>
<strong><?php esc_html_e( 'Is Your Website Down? We\'ll Alert You!
', 'wp-maintenance-mode' ); ?></strong>

<br /><br />
<?php esc_html_e( 'Get website downtime alerts by phone call, SMS, email or Slack if your website is down. Create beautiful status pages & incident management reports and keep your visitors updated.', 'wp-maintenance-mode' ); ?>
<br /><br />

<a class="button button-primary" href="<?php echo esc_url( wpmm_get_utmized_url( 'https://pulsetic.com/', array( 'source' => 'noticewpmm' ) ) ); ?>" target="_blank">
	<?php esc_html_e( 'Monitor Your Website and Create Status Pages', 'wp-maintenance-mode' ); ?>
</a>
