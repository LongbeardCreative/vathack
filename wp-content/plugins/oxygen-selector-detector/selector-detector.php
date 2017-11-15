<?php 

/* 
Plugin Name: Oxygen Selector Detector
Author: Soflyy
Author URI: https://oxygenapp.com
Description: Adds an option to create Custom Selectors using Selector Detector tool
Version: 1.0
Text Domain: oxygen
*/

define("OSD_VERSION", "1.0");
define("OSD_OXYGEN_REQUIRED_VERSION", "1.4");
define("OSD_PATH", 	plugin_dir_path( __FILE__ ) );
define("OSD_URI", 	plugin_dir_url( __FILE__ ) );

Class OxygenSelectorDetector {

	function __construct() {

		if ( $this->versions_is_ok() ) {
			
			// add scripts and styles
			add_action( 'oxygen_enqueue_builder_scripts', 	array( $this, 'enqueue_script' ) );
			
			// add views
			add_action( 'oxygen_after_toolbar', 			array( $this, 'choose_selector_view' ) );
			add_action( 'oxygen_sidepanel_before_classes', 	array( $this, 'list_style_sets_view' ) );
			add_action( 'oxygen_sidepanel_after_classes', 	array( $this, 'list_selectors_view' ) );

			include_once 'includes/edd-updater/edd-updater.php';
		}
	}

	
	/**
	 * Check if Oxygen main plugin installed and version is supported
	 *
	 * @since 1.0
	 * @author Ilya K.
	 */

	function versions_is_ok() {

		if ( ! defined("CT_VERSION") ) {
			add_action( 'admin_notices', array( $this, 'oxygen_not_found' ) );
			return false;
		}

		if ( version_compare( CT_VERSION, OSD_OXYGEN_REQUIRED_VERSION ) >= 0) {
	    	return true;
		}
		else {
			add_action( 'admin_notices', array( $this, 'oxygen_wrong_version' ) );
			return false;
		}
	}


	/**
	 * Admin notice if Oxygen main plugin not found active
	 *
	 * @since 1.0
	 * @author Ilya K.
	 */

	function oxygen_not_found() {
		
		$classes = 'notice notice-error';
		$message = __( 'Can\'t start Selector Detector add-on. Oxygen main plugin not found active in your install.', 'oxygen' );

		printf( '<div class="%1$s"><p>%2$s</p></div>', $classes, $message ); 
	}


	/**
	 * Admin notice if Oxygen main plugin version is not compatible
	 *
	 * @since 1.0
	 * @author Ilya K.
	 */

	function oxygen_wrong_version() {
		
		$classes = 'notice notice-error';
		$message = __( 'Your Oxygen version is not supported by Selector Detector add-on. Minimal required Oxygen version is:', 'oxygen' );

		printf( '<div class="%1$s"><p>%2$s <b>%3$s</b></p></div>', $classes, $message, OSD_OXYGEN_REQUIRED_VERSION ); 
	}

	
	/**
	 * Add scripts and styles
	 *
	 * @since 1.0
	 * @author Ilya K.
	 */

	function enqueue_script() {
		
		wp_enqueue_script( 'selector-detector-controller', 	OSD_URI . 'includes/osd.controller.js', array(), OSD_VERSION );
		wp_enqueue_style ( 'selector-detector',  		 	OSD_URI . 'includes/osd.styles.css' );
	}
	
	
	/**
	 * Include Choose Selector box HTML view
	 *
	 * @since 1.0
	 * @author Ilya K.
	 */

	function choose_selector_view() {
		require_once 'includes/views/choose-selector.view.php';
	}
	
	
	/**
	 * Include Style Sets list HTML view
	 *
	 * @since 1.0
	 * @author Ilya K.
	 */

	function list_style_sets_view() {
		require_once 'includes/views/stylesets-list.view.php';
	}

	
	/**
	 * Include Selectors list HTML view
	 *
	 * @since 1.0
	 * @author Ilya K.
	 */

	function list_selectors_view() {
		require_once 'includes/views/selectors-list.view.php';
	}

}

/**
 * Init Selector Detector add-on after Oxygen main plugin loaded
 */

function oxygen_selector_detector_init() {
	// Instantiate the plugin
	$oxygenSelectorDetectorInstance = new OxygenSelectorDetector();
}
add_action( 'plugins_loaded', 'oxygen_selector_detector_init' );