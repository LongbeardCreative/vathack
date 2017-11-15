<?php

/*
Plugin Name: Oxygen
Author: Soflyy
Author URI: https://oxygenapp.com
Description: Oxygen replaces your WordPress theme and allows you to design your entire website visually, inside WordPress. Construct static pages from fundamental HTML elements and visually style their CSS properties. Create views that will be used to render dynamic content like blog posts, WooCommerce products, or any other custom post type.
Version: 1.4.3
Text Domain: component-theme
*/

define("CT_VERSION", 	"1.4.3");
define("CT_FW_PATH", 	plugin_dir_path( __FILE__ )  . 	"component-framework" );
define("CT_FW_URI", 	plugin_dir_url( __FILE__ )  . 	"component-framework" );
define("CT_PLUGIN_MAIN_FILE", __FILE__ );

require_once("component-framework/component-init.php");