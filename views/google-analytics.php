<?php
/**
 * Google Analytics code
 *
 * @version 2.4.0
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr( $ga_code ); ?>"></script>
<script>
	window.dataLayer = window.dataLayer || [];
	function gtag() {dataLayer.push(arguments);}
	gtag('js', new Date());

<?php if ( substr( $ga_code, 0, 2 ) === 'UA' ) { ?>
	gtag('config', '<?php echo esc_attr( $ga_code ); ?>', <?php echo wp_json_encode( $ga_options ); ?>);
<?php } else { ?>
	gtag('config', '<?php echo esc_attr( $ga_code ); ?>');
<?php } ?>
</script>
