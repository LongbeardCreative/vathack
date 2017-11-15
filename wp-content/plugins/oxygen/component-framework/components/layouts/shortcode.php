<?php
	// TODO review the security aspect

	if (!is_user_logged_in() || !current_user_can( 'manage_options' )) {
	    die();
		}

	$nonce  	= $_REQUEST['nonce'];
	$post_id 	= $_REQUEST['post_id'];

	// check nonce
	if ( ! wp_verify_nonce( $nonce, 'oxygen-nonce-' . $post_id ) ) {
	    // This nonce is not valid.
	    die( 'Security check' );
	}
		
	// get passed parameters
	$name 	 = $_REQUEST['shortcode_name'];

	$options = file_get_contents('php://input');
	$options = json_decode( $options, true );

	if ( ! $options ) {
		_e("Can't get shortcode options", "component-theme");
		die();
	};

	$shortcode = $executed_shortcode = "";

	// Handle WP Embed
	if ( $name == "embed" ) {

		global $wp_embed;
		echo wp_oembed_get( $options['original']['url'] );
		die();
	}

	if ( $options['original']['full_shortcode'] != "" ) {

		$shortcode = $options['original']['full_shortcode'];
		$executed_shortcode = do_shortcode( $shortcode );
	}
	else {

		// handle shortcode component
		if ( $name == "ct_shortcode" ) {

			$name = $options['original']['shortcode_tag'];
			unset( $options['original']['shortcode_tag'] );
		}
		
		$shortcode = "[" . $name . " ";

		foreach ( $options['original'] as $param => $value) {
			$shortcode .= "$param=\"$value\" ";
		}

		$shortcode .= "]";

		$executed_shortcode = do_shortcode( $shortcode );
	}

	if ( $executed_shortcode == $shortcode ) {
		_e("Can't execute the shortcode. Make sure tag and parameters are correct.", "component-theme");
	}
	else {
		// print scripts and styles if added by shortcode
		do_action("wp_print_footer_scripts");
		
		echo $executed_shortcode;
	}