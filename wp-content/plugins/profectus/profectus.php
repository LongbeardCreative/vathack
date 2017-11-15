<?php
/*
Plugin Name:  Profectus
Plugin URI:   https://www.longbeard.com/profectus-report
Description:  Automates various LB specific tasks. Do not delete.
Version:      0.0.2(0028)
Author:       Evan Hennessy
Author URI:   https://www.hennessyevan.com/
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
*/

//Exit if Accessed Directly
if (! defined('ABSPATH')) {
    exit;
}

//Plugin Requirements
// require_once( plugin_dir_path(__FILE__) . 'overrides/override.php');
require_once( plugin_dir_path(__FILE__) . 'admin/profectus_admin.php');

if ( file_exists( plugin_dir_path(__FILE__) . 'assets/functions.php' ) ) {
	require_once( plugin_dir_path(__FILE__) . 'assets/functions.php' );
}

//Oxygen CSS Overrides
// function pf_override_styles() {
//     wp_enqueue_style( 'override_css', plugin_dir_url( __FILE__ ) . 'overrides/override.css', array(), null );
// }
// add_action( 'wp_enqueue_scripts', 'pf_override_styles', 9999 );


/* DEFAULT SCRIPTS */
//Front and Builder Styles
function pf_enqueue_styles() {
    wp_enqueue_style( 'main_css', plugin_dir_url( __FILE__ ) . 'assets/style.css', array(), null );
}
add_action( 'oxygen_enqueue_scripts', 'pf_enqueue_styles' );

//Development Scripts
function pf_cron_script() {
	wp_enqueue_script( 'pf_cron', plugin_dir_url(__FILE__) . 'admin/inc/pf_cron.js', array( 'jquery' ), false, true );
}
if (get_option( 'pf_settings' )['pf_devmode'] == 1) {
	add_action( 'oxygen_enqueue_scripts', 'pf_cron_script' );
}

/* CUSTOM SCRIPTS */
//Externals
function pf_external_scripts() {
	$scripts = explode(',', get_option( 'pf_settings' )['pf_script_url']);

	foreach ($scripts as $script) {
		wp_enqueue_script( 'pf_' . $script, $script );
	}
}
add_action( 'oxygen_enqueue_scripts', 'pf_external_scripts' );