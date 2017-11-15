<?php
/**
 * @copyright   Copyright (c) 2015, Addendio.com
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


//Requires functions helpers
require_once  dirname(__FILE__) . '/addplus-functions-helpers.php';

//Search Plugins Page
require_once  dirname(__FILE__) . '/addplus-search-plugins-page.php';

//Search Themes Page
require_once  dirname(__FILE__) . '/addplus-search-themes-page.php';

//Tooltips
//require_once( dirname( __FILE__ ).'/addplus-tooltips.php');

//=============================================================================
// Ajax: getPlugins
//=============================================================================
add_action( 'wp_ajax_addplus_getPlugins', 'addplus_getPlugins');
function addplus_getPlugins() {
	if ( ! current_user_can('activate_plugins') )
		wp_die( __( 'Sorry, you are not allowed to manage plugins for this site.' ) );

	$data = addplus_get_plugins_installed();
	echo(json_encode($data));
	wp_die();
}

//=============================================================================
// Ajax: getThemes
//=============================================================================
add_action( 'wp_ajax_addplus_getThemes', 'addplus_getThemes');
function addplus_getThemes() {
	if ( ! current_user_can('switch_themes') )
		wp_die( __( 'Sorry, you are not allowed to manage themes for this site.' ) );

	$data = addplus_get_themes_installed();
	echo(json_encode($data));
	wp_die();
}

//=============================================================================
// Ajax: getNonce token
//=============================================================================
add_action( 'wp_ajax_addplus_noonce', 'addplus_noonce');
function addplus_noonce() {
	$type = $_POST['type'];
	$slug = $_POST['slug'];
	$url = $_POST['url'];
// 	error_log( __METHOD__ . " params " . $type . ' ' . $slug . ' ' . $url);
	if ($type && $slug){
		$nonce = wp_create_nonce( 'plugin' == $type ? 'install-plugin_'.$slug : 'install-theme_'.$slug);
		$admin_url = $url ? admin_url( $url ) : '';
//     error_log( __METHOD__ . " result " . $nonce . ' ' . $admin_url);
		$response = array(
			'nonce' => $nonce,
			'url'   => $admin_url
		);
	} else {
		$response = array(
			'status' => 'error',
			'message' => 'missing url or slug'
		);
	}
	wp_send_json($response);
}

//=============================================================================
// Ajax: installPlugin
//=============================================================================
add_action( 'wp_ajax_addplus_install_plugin', 'addplus_install_plugin');
function addplus_install_plugin() {
	$plugin = isset($_REQUEST['plugin']) ? trim($_REQUEST['plugin']) : '';
	$activate = isset($_REQUEST['activate']) ? trim($_REQUEST['activate']) == '1' : '';
	$response_error = array(
		'status'  	=> 'error',
		'error'			=> '',
		'installed'	=> '0',
		'activated' => '0',
		'installed_url'	=> admin_url('plugins.php'),
		'installed_items'	=> 'plugins'
	);
	$response = array(
		'installed'	=> '0',
		'activated' => '0',
		'installed_url'	=> admin_url('plugins.php'),
		'installed_items'	=> 'plugins'
	);
	if ( ! current_user_can('install_plugins') ){
		$response_error['error'] = 'You are not allowed to install plugins';
		addplus_dieWithJson($response_error);
	}

	include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' ); //for plugins_api.
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
  include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
	include_once( ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php' );

	error_log(__METHOD__ . ' request ' . print_r($_REQUEST, TRUE));
	error_log(__METHOD__ . ' post ' . print_r($_REQUEST, TRUE));

	$check = check_admin_referer( 'install-plugin_' . $plugin );
	error_log(__METHOD__ . ' check ' . $check);

	$api = plugins_api( 'plugin_information', array(
		'slug' => $plugin,
		'fields' => array(
			'short_description' => false,
			'sections' => false,
			'requires' => false,
			'rating' => false,
			'ratings' => false,
			'downloaded' => false,
			'last_updated' => false,
			'added' => false,
			'tags' => false,
			'compatibility' => false,
			'homepage' => false,
			'donate_link' => false,
		),
	) );

	if ( is_wp_error( $api ) ) {
		$response_error['error'] = '' . $api->get_error_message();
		addplus_dieWithJson($response_error);
	}

	$upgrader = new Plugin_Upgrader( );
	$result = $upgrader->install($api->download_link);
	error_log(__METHOD__ . ' installed plugin ' . $result . ' will activate? ' . $activate);
	if (is_wp_error($result)) {
		$response_error['error'] = '' . $result->get_error_message();
		addplus_dieWithJson($response_error);
	}

	$response['installed'] = '1';
	$response_error['installed'] = '1';

	if ( $result && $activate ) {
		$plugin_file = addplus_plugin_file_for_name($plugin);

		if ( ! current_user_can( 'update_plugins' ) ) {
			$response_error['error'] = 'You are not allowed to activate plugins';
			addplus_dieWithJson($response_error);
		}
		if ( ! $plugin_file) {
			$response_error['error'] = 'Plugin cannot be activated';
			addplus_dieWithJson($response_error);
		}

		$activate_result = activate_plugin( $plugin_file, '', ! empty( $_GET['networkwide'] ), false );
		if (is_wp_error($activate_result)) {
			$response_error['error'] = '' . $activate_result->get_error_message();
			error_log(__METHOD__ . ' activate ' . $activate_result->get_error_message());
			addplus_dieWithJson($response_error);
		}
		$response['activated'] = '1';
		error_log(__METHOD__ . ' activate ' . $activate_result);
	}

	addplus_dieWithJson($response);
}


//=============================================================================
// Ajax: getTheme
//=============================================================================
add_action( 'wp_ajax_addplus_install_theme', 'addplus_install_theme');
function addplus_install_theme() {
	$theme = isset($_REQUEST['theme']) ? urldecode($_REQUEST['theme']) : '';
	$activate = isset($_REQUEST['activate']) ? trim($_REQUEST['activate']) == '1' : '';
	$response_error = array(
			'status'  	=> 'error',
			'error'			=> '',
			'installed'	=> '0',
			'activated' => '0',
   		'installed_url'		=> admin_url('themes.php'),
			'installed_items'	=> 'themes'
		);
	$response = array(
		'installed'	=> '0',
		'activated' => '0',
		'installed_url'	=> admin_url('themes.php'),
		'installed_items'	=> 'themes'
	);

	if ( ! current_user_can('install_themes') ){
		$response_error['error'] = 'You are not allowed to install themes';
		addplus_dieWithJson($response_error);
	}

	include_once( ABSPATH . 'wp-admin/includes/theme.php' );
	include_once( ABSPATH . 'wp-admin/includes/theme-install.php' ); //for plugins_api..
  include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
	include_once( ABSPATH . 'wp-admin/includes/class-theme-upgrader.php' );

	check_admin_referer( 'install-theme_' . $theme );
	$api = themes_api('theme_information', array('slug' => $theme, 'fields' => array('sections' => false, 'tags' => false) ) );
	if ( is_wp_error($api) ) {
		$response_error['error'] = '' . $api->get_error_message();
		addplus_dieWithJson($response_error);
	}

	$upgrader = new Theme_Upgrader( );
	$result = $upgrader->install($api->download_link);
	error_log(__METHOD__ . ' result ' . $result);
	if (is_wp_error($result)) {
		$response_error['error'] = '' . $result->get_error_message();
		addplus_dieWithJson($response_error);
	}

	$response['installed'] = '1';
	$response_error['installed'] = '1';

	if ($result && $activate) {
		$theme_object = wp_get_theme( $theme );
		error_log(__METHOD__ . ' result ' . print_r($theme_object, TRUE));

		if ( ! current_user_can( 'switch_themes' ) ) {
			$response_error['error'] = 'You are not allowed to switch themes';
			addplus_dieWithJson($response_error);
		}

		if ( ! $theme_object->exists() || ! $theme_object->is_allowed() ) {
			$response_error['error'] = 'Theme cannot be activated';
			addplus_dieWithJson($response_error);
		}
		switch_theme( $theme_object->get_stylesheet() );
		$response['activated'] = '1';
	}
	addplus_dieWithJson($response);
}

//=============================================================================
// Helper: dieWithJson
//=============================================================================
function addplus_dieWithJson($response, $sep = TRUE) {
	if($sep){
		echo("--**--");
	}
	echo(json_encode($response));
	wp_die();
}


//=============================================================================
// Action: menu
//=============================================================================
//We add the menus for searching plugins and Themes
add_action( 'admin_menu', 'addplus_menu', 9 );
function addplus_menu() {
	global $addplus_plugins_page;
	global $addplus_themes_page;
	global $submenu;

	$addplus_plugins_page = add_plugins_page( 'Search Plugins with Addendio', __('Search Plugins','addendio-plus'), 'manage_options', 'addendio-search-plugins', 'addplus_search_plugins');
	$addplus_themes_page = add_theme_page( 'Search Themes with Addendio', __('Search Themes','addendio-plus'), 'manage_options', 'addendio-search-themes', 'addplus_search_themes');
}

//=================================================================================================================

//=============================================================================
// Action: admin menu
//=============================================================================
add_action( 'admin_menu', 'addplus_add_admin_menu' );
function addplus_add_admin_menu(  ) {
	add_options_page( 'Addendio', 'Addendio', 'manage_options', 'addplus_addendio_settings', 'addplus_options_page' );
}

function addplus_options_page(  ) {
	?>
	<h2><?php echo __( 'Addendio Settings', 'addendio' );?></h2>

	<?php echo __( 'No settings yet, coming in future releases...', 'addendio' );?>

	<?php
}

//=============================================================================
// Filter: Plugin submenu
//=============================================================================
// Reorders the submenu for plugins, sets Search New Plugins in #2 position
add_filter( 'custom_menu_order', 'addplus_custom_plugins_submenu_order' );
function addplus_custom_plugins_submenu_order( $menu_ord ) {
	global $submenu;

	$current_position = array_search ( 'addendio-search-plugins', array_column ( $submenu['plugins.php'] , 2 ) );
	$submenu['plugins.php'] = array_values ( $submenu['plugins.php'] );

	addplus_array_move ( $submenu['plugins.php'] , $current_position, 1 );
}

//=============================================================================
// Filter: Themes submenu
//=============================================================================
// Reorders the submenu for themes, sets Search New Themes in #2 position
add_filter( 'custom_menu_order', 'addplus_custom_themes_submenu_order' );
function addplus_custom_themes_submenu_order( $menu_ord )
{
	global $submenu;

	$current_position = array_search ( 'addendio-search-themes', array_column( $submenu['themes.php'],2 ) );
	$submenu['themes.php'] = array_values( $submenu['themes.php'] );

	addplus_array_move( $submenu['themes.php'] , $current_position, 1);
}

//=============================================================================
// Helper: array move
//=============================================================================
// Move Array element to new position
function addplus_array_move( &$array , $oldpos , $newpos ) {
	if ( $oldpos == $newpos ) { return; }
	array_splice( $array , max ( $newpos , 0 ) , 0 , array_splice ( $array , max ( $oldpos , 0 ) , 1 ) );
}


//=============================================================================
// Action: load addendio page for either plugins or themes
//=============================================================================
// We load the scripts for the search of plugins and themes
add_action( 'admin_enqueue_scripts', 'addplus_load_addendio_pages' );
function addplus_load_addendio_pages($hook) {
	global $addplus_plugins_page;
	global $addplus_themes_page;
	//=======================================================================================================================================
	// PLUGINS SEARCH PAGE
	// only if user is admin and is on the right page, we load what we need
	$fs_registered = false;

	if(is_admin() && current_user_can('manage_options') && ( $hook == $addplus_plugins_page || $hook == $addplus_themes_page )  ) {
		if ( ADDENDIO_FREEMIUS_ENABLED ) {
			if( addplus_fs()->is_registered() ) {
				//JS
				wp_enqueue_script( 'addplus_charts_js', 'https://www.gstatic.com/charts/loader.js', array(), false, true);
				wp_enqueue_script( 'addplus_bootstrap_js', ADDPLUS_ASSETS_URL.'js/addendio.js', array(), false, true);
				$fs_registered = addplus_fs()->is_registered();
			}
		} else {
			//JS
			wp_enqueue_script( 'addplus_charts_js', 'https://www.gstatic.com/charts/loader.js', array(), false, true);
			wp_enqueue_script( 'addplus_bootstrap_js', ADDPLUS_ASSETS_URL.'js/addendio.js', array(), false, true);
		}
	}

	if(is_admin() && current_user_can('manage_options') && $hook == $addplus_plugins_page ) {
		// We pass some variables to the JS app in order to improve results
		$plugins_installed 	= 	addplus_get_plugins_installed ();
		$plugin_section 		= 'plugins';
		$admin_plugins_url 	= plugin_dir_url('plugins.php');
		$plugin_url 				= admin_url("themes.php?page=addendio-search-themes");
		$response 					= array (
																'plugin_section'				=> $plugin_section,
																'plugin_assets'					=> ADDPLUS_ASSETS_URL,
																'admin_plugin_url'			=> $admin_plugins_url,
																'plugin_url'						=> $plugin_url,
																'blog_language' 				=> get_locale() ,
																'freemius_is_registred' => $fs_registered
														);

		wp_localize_script('addplus_bootstrap_js', 'addplus_vars', array_merge($response, $plugins_installed));
	}

	//=======================================================================================================================================
	// THEMES SEARCH PAGE
	// only if user is admin and is on the right page, we load what we need
	if (is_admin() && current_user_can('manage_options') && $hook == $addplus_themes_page ) {
		// We pass some variables to the JS app in order to improve results
		$themes_installed = addplus_get_themes_installed ();
		$plugin_section 	= 'themes';
		$admin_themes_url = plugin_dir_url('themes.php');
		$theme_url 				= admin_url("themes.php?page=addendio-search-themes");

		$response 				= array (
																'plugin_section'				=> $plugin_section,
																'plugin_assets'					=> ADDPLUS_ASSETS_URL,
																'admin_plugin_url'			=> $admin_themes_url,
																'plugin_url'						=> $theme_url,
																'blog_language' 				=> get_locale() ,
																'freemius_is_registred' => $fs_registered,
														);
		wp_localize_script('addplus_bootstrap_js', 'addplus_vars',  array_merge($response, $themes_installed));
	}
}

//=============================================================================
// Helper: getInstalledPlugins
//=============================================================================
function addplus_get_plugins_installed() {
	//We get the list of plugins installed in order to check against the search so the user can see if
	//the plugin is already installed directly in the results...

	$all_active_plugins = get_option('active_plugins', array());
	$active_plugins = array();
	foreach($all_active_plugins as $key => $plugin_name) {
		$arr = explode("/", $plugin_name, 2);
		$active_plugins[$arr[0]] = 1;
	}

	$all_plugins = get_plugins();
	$plugins_slugs = array();
	foreach ($all_plugins as $plugin_root_file => $plugin) {
		// Get our Plugin data variables
		$arr = explode("/", $plugin_root_file, 2);
		$name = $arr[0];
		$plugins_slugs[] .= $name;
		if (!array_key_exists($name, $active_plugins)) {
			$active_plugins[$name] = 0;
		}
// 		error_log(__METHOD__ . ' plugin ' . print_r($plugin, TRUE));
	}
	return array(
			'installed_plugins_slugs' => $plugins_slugs,
			'installed_plugins_status' => $active_plugins
	);
}

//=============================================================================
// Helper: getPluginFileForPluginName
//=============================================================================
function addplus_plugin_file_for_name($plugin_name) {
	$all_plugins = get_plugins();
	foreach ($all_plugins as $plugin_root_file => $plugin) {
		// Get our Plugin data variables
		$arr = explode("/", $plugin_root_file, 2);
		$name = $arr[0];
		if ($name == $plugin_name) {
			return $plugin_root_file;
		}
	}
	return null;
}


//=============================================================================
// Helper: getInstalledThemes
//=============================================================================
function addplus_get_themes_installed() {
	$current_theme_name = get_option('current_theme', array());
	$themes = wp_get_themes();
	$theme_slugs = array_keys($themes);
// 	error_log(__METHOD__ . " themes " . print_r($theme_slugs, TRUE));

	$active_themes = array();
	foreach($themes as $key => $theme) {
		$active = ($current_theme_name == $theme->Name) ? 1 : 0;
		$active_themes[$key] = $active;
	}
// 	error_log(__METHOD__ . " themes " . print_r($active_themes, TRUE));
	$livepreview_link = admin_url("customize.php?theme=");
	return array(
			'installed_themes_slugs' 	=> $theme_slugs,
			'installed_themes_status' => $active_themes,
			'livepreview_url' 				=> $livepreview_link,
	);
}

//================================================================================================
// FREEMIUS RELATED FUNCTIONS
if ( ADDENDIO_FREEMIUS_ENABLED ) {
	addplus_fs()->add_filter('connect_message', 'addplus_fs_custom_connect_message', 10, 6);
}
function addplus_fs_custom_connect_message(
		$message,
		$user_first_name,
		$plugin_title,
		$user_login,
		$site_link,
		$freemius_link
) {
	return sprintf(
			__fs( 'hey-x' ) . '<br>' .
			__fs( 'In order to use <b>'.$plugin_title.'</b> we need to register your site. It takes a few seconds.', 'addendio-plus' ),
			$user_first_name,
			'<b>' . $plugin_title . '</b>',
			'<b>' . $user_login . '</b>',
			$site_link,
			$freemius_link
	);
}


if ( ADDENDIO_FREEMIUS_ENABLED ) {
	addplus_fs()->add_filter('connect_message_on_update ', 'addplus_fs_connect_message_on_update', 10, 6);
}
function addplus_fs_connect_message_on_update (
		$message,
		$user_first_name,
		$plugin_title,
		$user_login,
		$site_link,
		$freemius_link
) {
	return sprintf(
			__fs( 'hey-x' ) . '<br>' .
			__fs( 'You need to register your site, %s needs to connect your user, %s at %s', 'addendio-plus' ),
			$user_first_name,
			'<b>' . $plugin_title . '</b>',
			'<b>' . $user_login . '</b>',
			$site_link,
			$freemius_link
	);
}



/* fs_override_i18n( array(
	'few-plugin-tweaks' => __( "OPTIN NOW!!!", 'addendio-plus' ),
	'skip'           => __( 'Not today', 'addendio-plus' ),
), 'addendio-plus' );
*/	 

	       						