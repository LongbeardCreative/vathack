<?php

function lb_front_scripts() {
    wp_enqueue_script( 'lb_custom', plugin_dir_url( __FILE__ ) . 'js/lb_custom.js', array( 'jquery' ), '1.0', true );
}
add_action( 'wp_enqueue_scripts', 'lb_front_scripts' );

// Alter meta tag to prevent all zoom
add_filter( 'wp_head', 'iphonex_meta_viewport');
function iphonex_meta_viewport() {
	return '<meta name="viewport" content="width=device-width, viewport-fit=cover">';
}