<?php 

/**
 * WPML support
 *  
 */

 
add_filter( 'wpml_pb_shortcode_content_for_translation', 'ct_wpml_filter_content_for_translation', 10 , 2 );
 
function ct_wpml_filter_content_for_translation( $content, $post_id ) {
	$shortcodes = get_post_meta( $post_id, "ct_builder_shortcodes", true );
	if ( $shortcodes ) {
		$content = $shortcodes;
	}
	return $content;
}

add_filter( 'wpml_pb_shortcodes_save_translation', 'ct_wpml_filter_save_translation', 10, 3 );

function ct_wpml_filter_save_translation( $saved, $translated_post_id, $new_content ) {
	update_post_meta( $translated_post_id, "ct_builder_shortcodes", $new_content );
	return true;
}
