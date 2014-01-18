jQuery(document).ready( function($){
	
	$('#wm-pluginconflink').click(function(s){$('#wm_config_row').slideToggle('fast'); });
	$('#wm_config_active').click(function(){ wm_config_active(); });
	$('#wm_config_submit').click(function(){ wm_config_update(); });
	//$("#wm_config-date").datepicker({ dateFormat: 'dd-mm-yy' });
	$("#wm_config-date").datetimepicker({ timeFormat: 'HH:mm:ss', dateFormat: 'dd-mm-yy' });
	
	function wm_config_active() {
		
		var active_Val = $('#wm_config-active').val();
		$.post( ajaxurl, {
				"action" : "wm_config-active", 
				"wm_config-active" : active_Val,
				"nonce" : wp_maintenance_mode_vars._nonce
			}, 
			
			function(data) {
				$('#wm_message_active, #wm_message_active2').show('fast').animate({opacity: 1.0},
				3000).hide('slow');
			}
		);
		// show admin bar and message note
		if ( active_Val == 1 )
			$('#wp-admin-bar-mm_alert, #message.error').show('fast');
		// hide admin bar and message note
		if ( active_Val == 0 )
			$('#wp-admin-bar-mm_alert, #message.error').hide('fast');
	}
	
	function wm_config_update() {
		
		time_Val          = $('#wm_config-time').val();
		link_Val          = $('#wm_config-link').val();
		support_Val       = $('#wm_config-support').val();
		admin_link_Val    = $('#wm_config-admin_link').val();
		rewrite_Val       = $('#wm_config-rewrite').val();
		notice_Val        = $('#wm_config-notice').val();
		unit_Val          = $('#wm_config-unit').val();
		theme_Val         = $('#wm_config-theme').val();
		styleurl_Val      = $('#wm_config-styleurl').val();
		index_Val         = $('#wm_config-index').val();
		title_Val         = $('#wm_config-title').val();
		header_Val        = $('#wm_config-header').val();
		heading_Val       = $('#wm_config-heading').val();
		text_Val          = $('#wm_config-text').val();
		exclude_Val       = $('#wm_config-exclude').val();
		bypass_Val        = $('#wm_config-bypass').val();
		role_Val          = $('#wm_config-role').val();
		role_frontend_Val = $('#wm_config-role_frontend').val();
		radio_Val         = $('#wm_config-radio').val();
		date_Val          = $('#wm_config-date').val();
		cd_day_Val        = $('#wm_config-cd-day').val();
		cd_month_Val      = $('#wm_config-cd-month').val();
		cd_year_Val       = $('#wm_config-cd-year').val();
		url = '/wp-admin/admin-ajax.php';
		$.post( ajaxurl , {
				"action" : "wm_config-update",
				"nonce" : wp_maintenance_mode_vars._nonce,
				"wm_config-time" : time_Val, 
				"wm_config-unit" : unit_Val, 
				"wm_config-link" : link_Val, 
				"wm_config-support" : support_Val, 
				"wm_config-admin_link" : admin_link_Val,
				"wm_config-rewrite" : rewrite_Val,
				"wm_config-notice" : notice_Val, 
				"wm_config-theme" : theme_Val, 
				"wm_config-styleurl" : styleurl_Val, 
				"wm_config-index" : index_Val,
				"wm_config-title" : title_Val, 
				"wm_config-header" : header_Val, 
				"wm_config-heading" : heading_Val, 
				"wm_config-text" : text_Val, 
				"wm_config-exclude" : exclude_Val,
				"wm_config-bypass" : bypass_Val,
				"wm_config-role" : role_Val, 
				"wm_config-role_frontend" : role_frontend_Val,
				"wm_config-radio" : radio_Val, 
				"wm_config-date" : date_Val, 
				"wm_config-cd-day" : cd_day_Val, 
				"wm_config-cd-month" : cd_month_Val, 
				"wm_config-cd-year" : cd_year_Val
			},
			
			function(data) {
				$('#wm_message_update, #wm_message_update2').show('fast').animate({opacity: 1.0},
				3000).hide('slow');
			}
		);
		
		return false;
	}
	
});