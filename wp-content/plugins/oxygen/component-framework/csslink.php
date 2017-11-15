<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

// Need to output CSS for 404 page
if ( is_404() ) {
	status_header( 200 );
}

if ( !isset( $_REQUEST['xlink'] ) || stripslashes( $_REQUEST['xlink'] ) != 'css' ) {
	exit;
}

// Enable caching
//header('Cache-Control: public');
// Expire in one day
//header('Expires: ' . gmdate('D, d M Y H:i:s', time() + ( 86400 * 365 ) ) . ' GMT');
// Set the correct MIME type
header("Content-type: text/css");

global $ct_template_id;

/**
 * Shortcodes and Classes
 */

$styles = false;
$id = isset($_REQUEST['tid'])?intval($_REQUEST['tid']):false;

// Check styles cache
/*if ( !isset( $_REQUEST['action'] ) || stripslashes( $_REQUEST['action'] ) != 'save-css' ) {
	
	$styles = get_post_meta( $id, "oxygen_page_styles", true );

	if ( $styles ) {
		echo $styles;
		$die = true;
	}
}*/

if ( ! $styles ) {
	
	// start buffer again
	ob_start();

	ct_template_output();
	
	// output shortcode styles
	do_action('ct_footer_styles');
	
	// get shortcodes styles
	$styles = ob_get_clean();
	$styles = oxygen_css_minify( $styles );
	
	if ( !isset( $_REQUEST['action'] ) || stripslashes( $_REQUEST['action'] ) != 'save-css' ) {
		echo $styles;
	}

	// save styles cache
	//update_post_meta( $ct_template_id, "oxygen_page_styles", $styles );
	//update_post_meta( $ct_template_id, "oxygen_shortcodes_css_rendered_timestamp", time() );
}


/**
 * Stylesheets
 */

$styles = false;

// Check Stylesheets cache
/*if ( !isset( $_REQUEST['action'] ) || stripslashes( $_REQUEST['action'] ) != 'save-css' ) {

	$styles = get_option( "oxygen_stylesheets_styles" );

	if ( $styles ) {
		echo $styles;
		if ( isset($die) && $die ) {
			die();
		}
	}
}*/

if ( ! $styles ) {

	// output all Stylesheets
	$style_sheets = get_option( "ct_style_sheets", array() );

	if ( is_array( $style_sheets ) ) {
		
		$styles = "";

		foreach( $style_sheets as $key => $value ) {

			$style_sheets[$key] = base64_decode( $style_sheets[$key] );
			$styles .= $style_sheets[$key];
		}
	}

	$styles = oxygen_css_minify( $styles );
	
	if ( !isset( $_REQUEST['action'] ) || stripslashes( $_REQUEST['action'] ) != 'save-css' ) {
		echo $styles;
	}

	// save styles cache
	//if ( update_option( "oxygen_stylesheets_styles", $styles ) ) {
	//	update_option( "oxygen_stylesheets_css_rendered_timestamp", time() );
	//}
}


/**
 * Custom selectors
 */

global $media_queries_list;

$selectors = get_option( "ct_custom_selectors" );

$css = "";

if ( is_array( $selectors ) ) {
	foreach ( $selectors as $selector => $states ) {
		foreach ( $states as $state => $options ) {

			if ( $state == 'set_name' || $state == 'friendly_name' ) {
				continue;
			}	

			if ( $state == 'media' ) {
				
				foreach ( $media_queries_list as $media_name => $media ) {
					$max_width = $media_queries_list[$media_name]['maxSize'];
					
					if ( $options[$media_name] && $media_name != "default") {
						$css .= "@media (max-width: $max_width) {\n";
						foreach ( $options[$media_name] as $media_state => $media_options ) {
							$css .= ct_generate_class_states_css($selector, $media_state, $media_options, true, true);
						}
						$css .= "}\n\n";
					}
				}
			}
			else {
				$css = ct_generate_class_states_css($selector, $state, $options, false, true).$css;
			}
		}
	}
}
echo oxygen_css_minify($css);