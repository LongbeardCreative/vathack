<?php
/*
* Plugin Name: Addendio PLUS
* Plugin URI: https://addendio.com
* Description: Find & test-drive plugins and themes.
* Version: 1.1.0
* Author: Addendio
* Author URI: https://addendio.com
* License: GPL2
* Text Domain: addendio-plus
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );


//=====================================================================================
// Config

// ASSETS
$addedio_assets_url     = 'https://assets.addendio.com/widget/prod/addendio-plus/'; //PRODUCTION

// PLUGINS SPECIFIC
$addendio_version = '1.1.0';
$addendio_env = ''; //PRODUCTION

// FREEMIUS
$freemius_id = '182'; // PRODUCTION
$freemius_pk = 'pk_17d0dcdcf42e839039c2cb3ed4359'; // PRODUCTION
$freemius_enabled = false;


//====================================================================================================
// Plugin Update
require_once dirname(__FILE__) . '/updates/updates.php';

$addendio_current_version =  $addendio_version ; //current version
$addendio_remote_path = 'https://addendio.com/';  //Market website
$addendio_slug = plugin_basename (__FILE__); //plugin slug. In case of themes -> $slug = 'twentyfifteen'; or theme folder name.
$addendio_id = 59037; //plugin or theme post id

new edd_noLicense_auto_update($addendio_current_version, $addendio_remote_path, $addendio_slug, $addendio_id);

//====================================================================================================

// Plugin Version
if ( ! defined( 'ADDENDIO_PLUS_VERSION' ) ) {
	define ( 'ADDENDIO_PLUS_VERSION' , $addendio_version );
}

		// Store URL
if ( ! defined( 'ADDENDIO_STORE_URL' ) ) {
	define ( 'ADDENDIO_STORE_URL' , 'https://addendio.com/' );
}

		// ADDENDIO_DEV environment
if ( ! defined( 'ADDENDIO_DEV' ) ) {
	define ( 'ADDENDIO_ENV' , $addendio_env );
}

// FREEMIUS ENABLED
if ( ! defined( 'ADDENDIO_FREEMIUS_ENABLED' ) ) {
	define ( 'ADDENDIO_FREEMIUS_ENABLED' , $freemius_enabled );
}

// FREEMIUS PK
if ( ! defined( 'ADDENDIO_FREEMIUS_PK' ) ) {
	define ( 'ADDENDIO_FREEMIUS_PK' , $freemius_pk );
}

// FREEMIUS ID
if ( ! defined( 'ADDENDIO_FREEMIUS_ID' ) ) {
	define ( 'ADDENDIO_FREEMIUS_ID' , $freemius_id );
}


//====================================================================================================
// Create a helper function for easy SDK access.
function addplus_fs() {
	global $addplus_fs;

	if ( ADDENDIO_FREEMIUS_ENABLED && ! isset( $addplus_fs ) ) {
		// Include Freemius SDK.
		require_once dirname(__FILE__) . '/freemius/start.php';
		$addplus_fs = fs_dynamic_init( array(
                    'id'                => ADDENDIO_FREEMIUS_ID,
                    'slug'              => 'addendio-plus',
                    'public_key'        => ADDENDIO_FREEMIUS_PK,
                    'is_premium'        => false,
                    'has_addons'        => false,
                    'has_paid_plans'    => false,
                    'is_org_compliant'  => false,
                    'enable_anonymous'  => false,
                    'ignore_pending_mode' => true,   //
                    'menu'              => array(
                            'slug'       => 'addplus_addendio_settings',
                            'account'    => false,
                            'contact'    => true,
                            'support'    => false,
                            'first-path'     => 'plugins.php?page=addendio-search-plugins',
                            'parent'     => array(
                                    'slug' => 'options-general.php',
										),
									),
		) );
	}
	return $addplus_fs;
}

// Init Freemius.
if ( ADDENDIO_FREEMIUS_ENABLED ) {
	addplus_fs();
}



$override = true;
if (ADDENDIO_FREEMIUS_ENABLED && $override) {
	addplus_fs()->override_i18n( array (
										'few-plugin-tweaks' => __( "You need to activate Addendio Plus %s", 'addendio-plus' ) ,
										'activate-x-now' => __( "Register Addendio PLUS Now", 'addendio-plus' ) ,
										'optin-x-now' => __( "Register using Addendio PLUS Now", 'addendio-plus' ) ,
									)
	);
}

function addendio_plus_init() {
	$plugin_dir = basename(dirname(__FILE__)).'/languages/';
	load_plugin_textdomain( 'addendio-plus', false, $plugin_dir );
}

add_action('plugins_loaded', 'addendio_plus_init');

// Make sure that wp_get_current_user function is available
if(!function_exists('wp_get_current_user()')) {
	include(ABSPATH . 'wp-includes/pluggable.php');
}

// Plugin Folder Path
if ( ! defined( 'ADDPLUS_PLUGIN_DIR' ) ) {
	define( 'ADDPLUS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

// Plugin Folder URL
if ( ! defined( 'ADDPLUS_PLUGIN_URL' ) ) {
	define( 'ADDPLUS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

// URL for Assets
if ( ! defined( 'ADDPLUS_ASSETS_URL' ) ) {
	define( 'ADDPLUS_ASSETS_URL', $addedio_assets_url );
}

// Images Folder URL
if ( ! defined( 'ADDPLUS_PLUGIN_IMAGES_URL' ) ) {
	define( 'ADDPLUS_PLUGIN_IMAGES_URL', plugin_dir_url( __FILE__ ).'assets/images/' );
}

// Admin Folder URL
if ( ! defined( 'ADDPLUS_ADMIN_FOLDER' ) ) {
	define( 'ADDPLUS_ADMIN_FOLDER', get_admin_url());
}

//If Addendio LITE is active, we de-activate it...
if ( is_plugin_active( 'addendio/addendio-search-plugins-and-themes.php') ){
	wp_die( 'Please de activate <b>Addendio LITE</b> before activating <b>Addendio PLUS</b>.','Plugin dependency check', array( 'back_link' => true ) );
}

//We include the functions for Addendio PLUS
if ( is_admin() && current_user_can( 'administrator' ) ) {
	require_once ADDPLUS_PLUGIN_DIR . 'includes/addplus-functions.php';
}

