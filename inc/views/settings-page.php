<div class="wrap">
	<?php screen_icon( 'options-general' ); ?>
	<h2><?php echo parent :: get_plugin_data( 'Name' ) . ' ' . __( 'Settings' ); ?>
	</h2>
	
	<form name="my_form" method="post">
        <input type="hidden" name="action" value="some-action">
        <?php 
		wp_nonce_field( FB_WM_TEXTDOMAIN.'-nonce' ); 
		
		/* Used to save closed meta boxes and their order */
		wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false);
		wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false);
		?>
	
		<div id="poststuff">

			<div id="post-body" class="metabox-holder columns-2">

				<!-- main -->
				<div id="post-body-content">

					<div class="meta-box-sortables ui-sortable">
						<?php do_meta_boxes( $this->settings_page, 'normal', null ); ?>
					</div> <!-- .meta-box-sortables .ui-sortable -->

				</div> <!-- post-body-content -->

				<!-- sidebar -->
				<div id="postbox-container-1" class="postbox-container">

					<div class="meta-box-sortables ui-sortable">
						<?php do_meta_boxes( $this->settings_page, 'side', null ); ?>
					</div> <!-- .meta-box-sortables -->

				</div> <!-- #postbox-container-1 .postbox-container -->

			</div> <!-- #post-body .metabox-holder .columns-2 -->

		</div> <!-- #poststuff -->
	</form>
</div>
