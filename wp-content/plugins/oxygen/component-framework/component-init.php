<?php

require_once("admin/cpt-templates.php");
require_once("admin/admin.php");
require_once("admin/pages.php");
require_once("admin/svg-icons.php");
require_once("admin/import-export.php");
require_once("admin/updater/edd-updater.php");

require_once("includes/ajax.php");
require_once("includes/api.php");
require_once("includes/tree-shortcodes.php");
require_once("includes/templates.php");
require_once("includes/wpml-support.php");

// init global API instance
global $oxygen_api;
$oxygen_api = new CT_API();

// init media queries sizes
global $media_queries_list;
$media_queries_list = array (
	"default" 	=> array (
					"maxSize" 	=> "100%",
					"title" 	=> "All devices"
				),
	"tablet" 	=> array (
					"maxSize" 	=> '992px', 
					"title" 	=> "Less than 992px"
				),
	"phone-landscape" 
				=> array (
					"maxSize" 	=> '768px', 
					"title" 	=> "Less than 768px"
				),
	"phone-portrait"
				=> array (
					"maxSize" 	=> '480px', 
					"title" 	=> "Less than 480px"
				),
);

// Include Component Class
require_once("components/component.class.php");

// Add components in certain order
include_once("components/classes/section.class.php");
include_once("components/classes/columns.class.php");
include_once("components/classes/column.class.php");
include_once("components/classes/div-block.class.php");
include_once("components/classes/headline.class.php");
include_once("components/classes/text-block.class.php");
include_once("components/classes/paragraph.class.php");
include_once("components/classes/link-text.class.php");
include_once("components/classes/link-wrapper.class.php");
include_once("components/classes/image.class.php");
include_once("components/classes/svg-icon.class.php");
include_once("components/classes/ul.class.php");
include_once("components/classes/li.class.php");
include_once("components/classes/code-block.class.php");
include_once("components/classes/inner-content.class.php");

// not shown in fundamentals
include_once("components/classes/reusable.class.php");
include_once("components/classes/selector.class.php");
include_once("components/classes/separator.class.php");
include_once("components/classes/shortcode.class.php");
include_once("components/classes/span.class.php");
include_once("components/classes/widget.class.php");


/**
 * Hook for addons to add fundamental components
 *
 * @since 1.4
 */
do_action("oxygen_after_add_components");


/**
 * Run plugin setup
 * 
 * @since 0.3.3
 * @author Ilya K.
 */

function ct_plugin_setup() {

	/**
	 * Setup default SVG Set
	 * 
	 */
	
	//delete_option("ct_svg_sets");
	$svg_sets = get_option("ct_svg_sets", array() );

	if ( empty( $svg_sets ) ) {

		$sets = array(
			"fontawesome" => "Font Awesome",
			"linearicons" => "Linearicons"
		);
		
		foreach ($sets as $key => $name) {
			
			// import default file	
			$file_content = file_get_contents( CT_FW_PATH . "/admin/includes/$key/symbol-defs.svg" );

			$xml = simplexml_load_string($file_content);

			foreach($xml->children() as $def) {
				if($def->getName() == 'defs') {

					foreach($def->children() as $symbol) {
						
						if($symbol->getName() == 'symbol') {
							$symbol['id'] = str_replace(' ', '', $name).$symbol['id'];
							
						}
					}
				}
				
			}
			$file_content = $xml->asXML();

			$svg_sets[$name] = $file_content;
		}

		// save SVG sets to DB
		update_option("ct_svg_sets", $svg_sets );
	}
}
add_action('admin_init', 'ct_plugin_setup');


/**
 * Echo all components styles in one <style>
 * 
 * @since 0.1.6
 */

function ct_footer_styles_hook() {
	
	ob_start();
	do_action("ct_footer_styles");
	$ct_footer_css = ob_get_clean();

	if ( defined("SHOW_CT_BUILDER") ) {
		echo "<style type=\"text/css\" id=\"ct-footer-css\">\r\n";
		echo $ct_footer_css;
		echo "</style>\r\n";
	}
}


function ct_wp_link_dialog() {
    require_once ABSPATH . "wp-includes/class-wp-editor.php";
	_WP_Editors::wp_link_dialog();
}

/**
 * Check if we are in builder mode
 * 
 * @since 0.1
 * @author Ilya K.
 */

function ct_is_show_builder() {

	// check if builder activated
    if ( isset( $_GET['ct_builder'] ) && $_GET['ct_builder'] ) {

		if ( !is_user_logged_in() ) {
		   auth_redirect();
		}
		
		if ( !current_user_can('edit_pages') ) {
			wp_die(__('You do not have sufficient permissions to edit the layout', 'component-theme'));
		}

    	define("SHOW_CT_BUILDER", true);

    	add_action("wp_footer", "ct_wp_link_dialog");
		add_action("wp_head", "ct_footer_styles_hook");
		
		add_filter("document_title_parts", "ct_builder_wp_title", 10, 1);
    }
}
add_action('init','ct_is_show_builder', 1 );


/**
 * Callback for 'document_title_parts' filter
 *
 * @since ?
 * @author ?
 */

function ct_builder_wp_title( $title = array() ) {
 	$title['title'] = __( 'Oxygen Visual Editor', 'component-theme' ).(isset($title['title'])?' - '.$title['title']:'');
    return $title;
}

/**
 * Check if user has rights to open this post/page in builder
 * 
 * @since 1.0.1
 * @author Ilya K.
 */

function ct_check_user_caps() {

	// check if builder activated
    if ( isset( $_GET['ct_builder'] ) && $_GET['ct_builder'] ) {

    	// check if user is logged in
    	if ( !is_user_logged_in() ) {
			auth_redirect();
		}
		
		global $post;

		// if user can edit this post
		if ( $post !== null && ! current_user_can( 'edit_post', $post->ID ) ) {
			auth_redirect();
		}
    }
}
add_action('wp','ct_check_user_caps', 1 );


function ct_oxygen_admin_menu() {

	if(is_admin())
		return;

	global $wp_admin_bar, $post;

	$wp_admin_bar->add_menu( array( 'id' => 'oxygen_admin_bar_menu', 'title' => __( 'Oxygen', 'component-theme' ), 'href' => FALSE ) );

	$post_id = false;
	$template = false;

	// get archive template
	if ( is_archive() || is_search() || is_404() || is_home() || is_front_page() ) {
		
		if ( is_front_page() ) {
			$post_id 	= get_option('page_on_front');

			// NOTE check if other_template or custom_template is applied, if not, then get the default template

			//$shortcodes = get_post_meta( $post_id, "ct_builder_shortcodes", true );
		}
		else if ( is_home() ) {
			$post_id 	= get_option('page_for_posts');

			//$shortcodes = get_post_meta( $post_id, "ct_builder_shortcodes", true );
		}
		else {
			$template 	= ct_get_archives_template();

			//$shortcodes = $template?get_post_meta( $template->ID, "ct_builder_shortcodes", true ):false;
			$wp_admin_bar->add_menu( array( 'id' => 'edit_post_template', 'parent' => 'oxygen_admin_bar_menu', 'title' => __( 'Edit '.$template->post_title.' Template', 'component-theme' ), 'href' => esc_url(ct_get_post_builder_link( $template->ID )) ) );

			$wp_admin_bar->add_menu( array( 'id' => 'edit_template', 'parent' => 'oxygen_admin_bar_menu', 'title' => __( 'Edit Template Settings', 'component-theme' ), 'href' => get_edit_post_link($template->ID) ) );
		}

	} 

	if($post_id || (!$template && is_singular())) {

		if($post_id == false)
			$post_id = $post->ID;

		// look for default template that can apply to the given post
		if(is_front_page() || is_home())
			$generic_view = ct_get_archives_template( $post_id );
		else
			$generic_view = ct_get_posts_template($post_id);

		$custom_view = get_post_meta( $post_id, 'ct_builder_shortcodes', true );

		$ct_render_post_using = get_post_meta( $post_id, 'ct_render_post_using', true );

		$ct_other_template = get_post_meta( $post_id, 'ct_other_template', true );

		if(!$custom_view && !$ct_render_post_using && !$generic_view) {
			$custom_view = ' ';
			$ct_render_post_using = 'custom_template';
		}

		if($ct_render_post_using == 'custom_template' || (!$ct_render_post_using && $custom_view)) {
			$wp_admin_bar->add_menu( array( 'id' => 'edit_in_visual_editor', 'parent' => 'oxygen_admin_bar_menu', 'title' => __( 'Edit in Visual Editor', 'component-theme' ), 'href' => esc_url(ct_get_post_builder_link( $post_id )) ) );
		}
		elseif($ct_render_post_using == 'other_template' || $generic_view) {
			
			global $wpdb;

			$template = $wpdb->get_results(
			    "SELECT id, post_title
			    FROM $wpdb->posts as post
			    WHERE post_type = 'ct_template'
			    AND id = $ct_other_template
			    AND post.post_status IN ('publish')"
			);

			if(is_array($template) && sizeof($template) > 0 ) { // select a default template, if none assigned
				$wp_admin_bar->add_menu( array( 'id' => 'edit_post_template', 'parent' => 'oxygen_admin_bar_menu', 'title' => __( 'Edit '.$template[0]->post_title.' Template', 'component-theme' ), 'href' => esc_url(ct_get_post_builder_link( $ct_other_template )) ) );

			} elseif($generic_view) {
				$ct_other_template = $generic_view->ID;
				$wp_admin_bar->add_menu( array( 'id' => 'edit_generic_template', 'parent' => 'oxygen_admin_bar_menu', 'title' => __( 'Edit '.$generic_view->post_title.' Template', 'component-theme' ), 'href' => esc_url(ct_get_post_builder_link( $ct_other_template )) ) );
			}

			
			// check if the template uses the ct_inner_content module
									
			$shortcodes = get_post_meta( $ct_other_template, 'ct_builder_shortcodes', true );
			
			if(strpos($shortcodes, '[ct_inner_content') !== false) {
				
				$ct_use_inner_content = get_post_meta($post_id, 'ct_use_inner_content', true);

				if($ct_use_inner_content && $ct_use_inner_content == 'layout')
					$wp_admin_bar->add_menu( array( 'id' => 'edit_inner_content', 'parent' => 'oxygen_admin_bar_menu', 'title' => __( 'Edit Inner Content', 'component-theme' ), 'href' => esc_url(ct_get_post_builder_link( $post_id )).'&ct_inner=true' ) );
			}

			//$wp_admin_bar->add_menu( array( 'parent' => 'oxygen_admin_bar_menu', 'title' => __( 'Custom Design, Just for this '. get_post_type(), 'component-theme' ), 'href' => esc_url(ct_get_post_builder_link( $post_id )) ) );
		}
		
	}
}

add_action( 'admin_bar_menu', 'ct_oxygen_admin_menu', 1000 );

/**
 * Set CT parameters to recognize on fronted and builder
 * 
 * @since 0.2.0
 * @author Ilya K.
 */

function ct_editing_template() {

    if ( get_post_type() == "ct_template" ) {

    	$template_type = get_post_meta( get_the_ID(), 'ct_template_type', true );

    	if ( $template_type == "archive" || $template_type == "single_post" ) {
    		define("CT_TEMPLATE_EDIT", true);	
    	}

    	if ( $template_type == "archive" ) {
    		define("CT_TEMPLATE_ARCHIVE_EDIT", true);	
    	}

    	if ( $template_type == "single_post" ) {
    		define("CT_TEMPLATE_SINGLE_EDIT", true);	
    	}
    }
}
add_action('wp','ct_editing_template', 1 );


/**
 * Get current request URL
 * 
 * @since ?
 * @author gagan goraya
 */

function ct_get_current_url($more_query) {

	$request_uri = '';

	$request = explode('?', $_SERVER["REQUEST_URI"]);

	if(isset($request[1])) {
		$request_uri = $_SERVER["REQUEST_URI"].'&'.$more_query;
	}
	else {
		$request_uri = $_SERVER["REQUEST_URI"].'?'.$more_query;	
	}

	$pageURL = 'http';
	if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
	$pageURL .= "://";
	//if ($_SERVER["SERVER_PORT"] != "80") {
	//  $pageURL .= $_SERVER["HTTP_HOST"].":".$_SERVER["SERVER_PORT"].$request_uri;
	//} else {
	  $pageURL .= $_SERVER["HTTP_HOST"].$request_uri;
	//}
	
	return $pageURL;
}


/**
 * Include Scripts and Styles for frontend and builder
 * 
 * @since 0.1
 * @author Ilya K.
 */

function ct_enqueue_scripts() {

	// include normalize.css
	wp_enqueue_style("normalize", CT_FW_URI . "/vendor/normalize.css");

	wp_enqueue_style("oxygen", CT_FW_URI. "/style.css", array(), CT_VERSION );

	wp_enqueue_script("jquery");

	/**
	 * Add-on hook for scripts that should be displayed both frontend and builder
	 *
	 * @since 1.4
	 */
	do_action("oxygen_enqueue_scripts");

	// only for frontend
	if ( ! defined("SHOW_CT_BUILDER") ) {

		/**
		 * Add-on hook
		 *
		 * @since 1.4
		 */
		do_action("oxygen_enqueue_frontend_scripts");

		wp_enqueue_style("oxygen-styles", ct_get_current_url( 'xlink=css' ) );
		// anything beyond this is for builder
		return;
	}

	// Font Loader
	wp_enqueue_script("font-loader", "//ajax.googleapis.com/ajax/libs/webfont/1/webfont.js");

	// jQuery UI
	wp_enqueue_script("jquery-ui", "//code.jquery.com/ui/1.11.3/jquery-ui.js", array(), '1.11.3');
	wp_enqueue_style("jquery-ui-css", "//code.jquery.com/ui/1.11.3/themes/smoothness/jquery-ui.css", array());

	// WordPress Media
	wp_enqueue_media();

	// link manager
	wp_enqueue_script( 'wplink' );
	wp_enqueue_style( 'editor-buttons' );

	// FontAwesome
	wp_enqueue_style("font-awesome", "//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css", array(), '4.3.0');

	// AngularJS
	wp_enqueue_script("angular", 			"//ajax.googleapis.com/ajax/libs/angularjs/1.3.5/angular.js", array(), '1.4.2');
	wp_enqueue_script("angular-animate", 	"//ajax.googleapis.com/ajax/libs/angularjs/1.3.5/angular-animate.js", array(), '1.4.2');

	// Colorpicker
	wp_enqueue_script("bootstrap-colorpicker-module", 	CT_FW_URI . "/vendor/colorpicker/js/bootstrap-colorpicker-module.js");
	wp_enqueue_style ("bootstrap-colorpicker-module", 	CT_FW_URI . "/vendor/colorpicker/css/colorpicker.min.css");

	// Dragula
 	wp_enqueue_script("dragula", 						CT_FW_URI . "/vendor/dragula/angular-dragula.js");
	wp_enqueue_style ("dragula", 						CT_FW_URI . "/vendor/dragula/dragula.min.css");

	// nuSelectable
	//wp_enqueue_script("nu-selectable", 					CT_FW_URI . "/vendor/nuSelectable/jquery.nu-selectable.js");

	// Codemirror
	wp_enqueue_script("ct-codemirror", 					CT_FW_URI . "/vendor/codemirror/codemirror.js");
	wp_enqueue_style ("ct-codemirror", 					CT_FW_URI . "/vendor/codemirror/codemirror.css");

	wp_enqueue_script("ui-codemirror", 					CT_FW_URI . "/vendor/ui-codemirror/ui-codemirror.js");

	wp_enqueue_script("ct-codemirror-html",				CT_FW_URI . "/vendor/codemirror/htmlmixed/htmlmixed.js");
	wp_enqueue_script("ct-codemirror-xml",				CT_FW_URI . "/vendor/codemirror/xml/xml.js");
	wp_enqueue_script("ct-codemirror-js", 				CT_FW_URI . "/vendor/codemirror/javascript/javascript.js");
	wp_enqueue_script("ct-codemirror-css",				CT_FW_URI . "/vendor/codemirror/css/css.js");
	wp_enqueue_script("ct-codemirror-clike",			CT_FW_URI . "/vendor/codemirror/clike/clike.js");
	wp_enqueue_script("ct-codemirror-php",				CT_FW_URI . "/vendor/codemirror/php/php.js");

	// Builder files
	wp_enqueue_script("ct-angular-main", 				CT_FW_URI . "/angular/controllers/controller.main.js", 			array(), CT_VERSION);
	wp_enqueue_script("ct-angular-tree", 				CT_FW_URI . "/angular/controllers/controller.tree.js", 			array(), CT_VERSION);
	wp_enqueue_script("ct-angular-states", 				CT_FW_URI . "/angular/controllers/controller.states.js", 		array(), CT_VERSION);
	wp_enqueue_script("ct-angular-navigation", 			CT_FW_URI . "/angular/controllers/controller.navigation.js", 	array(), CT_VERSION);
	wp_enqueue_script("ct-angular-columns", 			CT_FW_URI . "/angular/controllers/controller.columns.js", 		array(), CT_VERSION);
	wp_enqueue_script("ct-angular-ajax", 				CT_FW_URI . "/angular/controllers/controller.ajax.js", 			array(), CT_VERSION);
	wp_enqueue_script("ct-angular-ui", 					CT_FW_URI . "/angular/controllers/controller.ui.js", 			array(), CT_VERSION);
	wp_enqueue_script("ct-angular-classes", 			CT_FW_URI . "/angular/controllers/controller.classes.js", 		array(), CT_VERSION);
	wp_enqueue_script("ct-angular-options", 			CT_FW_URI . "/angular/controllers/controller.options.js", 		array(), CT_VERSION);
	wp_enqueue_script("ct-angular-fonts", 				CT_FW_URI . "/angular/controllers/controller.fonts.js", 		array(), CT_VERSION);
	wp_enqueue_script("ct-angular-svg", 				CT_FW_URI . "/angular/controllers/controller.svg.js", 			array(), CT_VERSION);
	wp_enqueue_script("ct-angular-css",					CT_FW_URI . "/angular/controllers/controller.css.js", 			array(), CT_VERSION);
	wp_enqueue_script("ct-angular-templates",			CT_FW_URI . "/angular/controllers/controller.templates.js", 	array(), CT_VERSION);
	wp_enqueue_script("ct-angular-drag-n-drop",			CT_FW_URI . "/angular/controllers/controller.drag-n-drop.js", 	array(), CT_VERSION);
	wp_enqueue_script("ct-angular-media-queries",		CT_FW_URI . "/angular/controllers/controller.media-queries.js", array(), CT_VERSION);
	wp_enqueue_script("ct-angular-api",					CT_FW_URI . "/angular/controllers/controller.api.js", 			array(), CT_VERSION);

	/**
	 * Add-on hook
	 *
	 * @since 1.4
	 */
	do_action("oxygen_enqueue_builder_scripts");

	wp_enqueue_script("ct-angular-directives",			CT_FW_URI . "/angular/builder.directives.js", array(), CT_VERSION);

	// Add some variables needed for AJAX requests
	global $post;
	global $wp_query;

	$postid = $post->ID;

	if (is_front_page()) {
		$postid 		= get_option('page_on_front');
	}
	else if(is_home()) {
		$postid 		= get_option('page_for_posts');
	}

	$nonce = wp_create_nonce( 'oxygen-nonce-' . $postid );

	$options = array ( 
		'ajaxUrl' 	=> admin_url( 'admin-ajax.php' ),
		'permalink' => get_permalink(),
		'postId' 	=> $postid,
		'query' 	=> $wp_query->query,
		'nonce' 	=> $nonce
	);

	if ( defined("CT_TEMPLATE_EDIT") ) {
		$options["ctTemplate"] = true;
	}

	if ( defined("CT_TEMPLATE_ARCHIVE_EDIT") ) {
		$options["ctTemplateArchive"] = true;
	}

	if ( defined("CT_TEMPLATE_SINGLE_EDIT") ) {
		$options["ctTemplateSingle"] = true;
	}
	
	wp_localize_script( "ct-angular-main", 'CtBuilderAjax', $options);
}
add_action( 'wp_enqueue_scripts', 'ct_enqueue_scripts' );


/**
 * Init
 * 
 * @since 0.2.5
 */

function ct_init() {

	// check if builder activated
    if ( defined("SHOW_CT_BUILDER") ) {
    	add_action("ct_builder_ng_init", "ct_init_default_options");
    	add_action("ct_builder_ng_init", "ct_init_not_css_options");
    	add_action("ct_builder_ng_init", "ct_init_nice_names");
    	add_action("ct_builder_ng_init", "ct_init_settings");
    	add_action("ct_builder_ng_init", "ct_init_components_classses");
    	add_action("ct_builder_ng_init", "ct_init_custom_selectors");
    	add_action("ct_builder_ng_init", "ct_init_style_sheets");
    	add_action("ct_builder_ng_init", "ct_init_api_token");
    	add_action("ct_builder_ng_init", "ct_init_api_components");
    	
    	add_action("ct_builder_ng_init", "ct_components_tree_init", 100 );

    	// Include Toolbar
		require_once("toolbar/toolbar.class.php");
    }
}
add_action('init','ct_init', 2);


/**
 * Make API call to get the token and output to builder
 *
 * @since 0.4.0
 * @author Ilya K.
 */

function ct_init_api_token() {
		
	global $oxygen_api;
	$user = wp_get_current_user();
	//delete_transient('oxygen-token-check-user-' . $user->ID);
	$token_check = get_transient( 'oxygen-token-check-user-' . $user->ID );
	
	if ( ! $token_check ) {

		$token_check = $oxygen_api->check_api_token();

		if ( isset ( $token_check["ID"] ) ) {
			set_transient( 'oxygen-token-check-user-' . $user->ID, $token_check, 12 * HOUR_IN_SECONDS );
		}
	}
		
	$auth_info = array (
			"token_check" => $token_check
		);

	echo "authInfo=" . htmlspecialchars( json_encode( $auth_info ), ENT_QUOTES ) . ";";
}


/**
 * Get list of all components
 *
 * @since 0.4.0
 * @author Ilya K.
 */

function ct_init_api_components() {

	global $api_components;
	global $api_pages;
	global $api_design_sets;

	//unset( $api_components["status"] );
	$api_components = htmlspecialchars( json_encode( $api_components ) );
	echo "api_components=$api_components;";

	//unset( $api_pages["status"] );
	$api_pages = htmlspecialchars( json_encode( $api_pages ) );
	echo "api_pages=$api_pages;";

	$api_design_sets = htmlspecialchars( json_encode( $api_design_sets ) );
	echo "api_design_sets=$api_design_sets;";
}


/**
 * Get categories, pages, components
 *
 * @since 1.0.1
 * @author Ilya K.
 */

function ct_get_base() {

	if ( ! defined("SHOW_CT_BUILDER") ) {
		return;
	}
	
	global $oxygen_api;
	
	global $oxygen_add_plus;
	global $api_components;
	global $api_pages;
	global $api_design_sets;

	$response = $oxygen_api->get_base();

	if ( $response["status"] == "ok" ) {
		$api_pages 			= $response["pages"];
		$api_components 	= $response["components"];
		$categories 		= $response["categories"];
		$page_categories 	= $response["page_categories"];
		$api_design_sets 	= $response["design_sets"];
	} 
	else {
		$api_pages 			= array();
		$api_components 	= array();
		$categories 		= array();
		$page_categories 	= array();
		$api_design_sets 	= array();
	}

	// build Add+ section
	$components = array();
	$components["id"] 			= "components";
	$components["name"] 		= "Components";
	
	$design_sets = array();
	$design_sets["id"] 	 		= "design_sets";
	$design_sets["name"] 		= "Design Sets";
	$design_sets["children"] 	= array();
	
	// Components
	if(is_array($api_components)) {
		foreach ($api_components as $key => $component) {
			$component = (array) $component;
			
			if ( !isset( $design_sets["children"][$component["design_set_id"]] ) ) {
				$design_sets["children"][$component["design_set_id"]] = array(
					"id" => $component["design_set_id"],
					"name" => $component["design_set_name"],
					"children" => array()
				);
			}

			// create components array first time
			if ( !isset   ( $design_sets["children"][$component["design_set_id"]]["children"]["component"] ) ||
				 !is_array( $design_sets["children"][$component["design_set_id"]]["children"]["component"] ) ) {
				$design_sets["children"][$component["design_set_id"]]["children"]["component"] = array(
					"name" 	=> "Components",
					"type" 	=> "component",
					"id" 	=> $component["design_set_id"],
					"items" => array()
				);
			}

			// push component to certain design set
			$design_sets["children"][$component["design_set_id"]]["children"]["component"]["items"][] = $component;

			// add to categories array
			if(is_array($categories)) {
				foreach ($categories as $key => $category) {
					if ( !isset( $categories[$key]["items"] ) ) {
						$categories[$key]["items"] = array();
					}

					// add component
					if ( $category["id"] == $component["category_id"] ) {
						$categories[$key]["items"][] = $component;
					}
				}
			}
		}
	}
	
	// build categories tree
	$new = array();

	if(is_array($categories)) {
		foreach ($categories as $category){
		    $new[$category['parent_id']][] = $category;
		}
	}

	function ct_build_categories_tree(&$list, $parent){
		$tree = array();
		if(is_array($parent)) {
			foreach ($parent as $k=>$l){
				if(isset($list[$l['id']])){
					$l['children'] = ct_build_categories_tree($list, $list[$l['id']]);
				}
				$tree[] = $l;
			}
		}
		return $tree;
	}

	if(isset($new[0])) {
		$tree = ct_build_categories_tree($new, $new[0]);
		$components["children"] = $tree;
	}

	//if (isset($api_pages["status"]) && $api_pages["status"] != "error") {
		// Pages
		if(is_array($api_pages)) {
			foreach ($api_pages as $key => $page) {

				$page = (array) $page;

				// create pages array first time
				if (!isset   ( $design_sets["children"][$page["design_set_id"]]["children"]["page"] ) || 
					!is_array( $design_sets["children"][$page["design_set_id"]]["children"]["page"] ) ) {
					$design_sets["children"][$page["design_set_id"]]["children"]["page"] = array(
						"name" 	=> "Pages",
						"type" 	=> "page",
						"id" 	=> $page["design_set_id"],
						"items" => array()
					);
				}

				// check pages category
				if ( $page["category_id"] ) {

					// create category folder for the first time
					if (!isset   ($design_sets["children"][$page["design_set_id"]]["children"]["page"]["children"][$page["category_id"]]) || 
						!is_array($design_sets["children"][$page["design_set_id"]]["children"]["page"]["children"][$page["category_id"]]) ) {
						// add to categories array
						if(is_array($page_categories)) {
							foreach ($page_categories as $key => $category) {
								// add component
								if ( $category["id"] == $page["category_id"] ) {
									$name = $category["name"];
								}
							}
						}
						
						$design_sets["children"][$page["design_set_id"]]["children"]["page"]["children"][$page["category_id"]] 
						= array( 	
							"id" 	=> $page["category_id"]."_".$page["design_set_id"],
							"type" 	=> "page",
							"name" 	=> $name );
					}

					$design_sets["children"][$page["design_set_id"]]["children"]["page"]["children"][$page["category_id"]]["items"][] = $page;
				}
				else {
					// push page to certain design set
					$design_sets["children"][$page["design_set_id"]]["children"]["page"]["items"][] = $page;
				}
			}
		}
	//}
	
	$oxygen_add_plus = array(
			"status" 		=> $response["status"],
			"components" 	=> $components,
			"design_sets" 	=> $design_sets,
		);

	if ( $response["status"] == "error" && is_array($response["error"]["errors"])) {
		$oxygen_add_plus["errors"] = reset($response["error"]["errors"]);
	}

	// make design sets ids to be keys
	foreach ( $api_design_sets as $design_set ) {
	    $new_design_sets[$design_set['id']] = $design_set;
	}
	if ( isset($new_design_sets) && is_array( $new_design_sets ) ) {
		$api_design_sets = $new_design_sets;
	}

	if ( $response["status"] == "error" && isset($response["message"]) ) {
		$oxygen_add_plus["message"] = $response["message"];
	}
}
add_action("wp", "ct_get_base");


/**
 * Output all Components (shortcodes) default params to ng-init directive
 *
 * @since 0.1
 */

function ct_init_default_options() {

	$components = apply_filters( "ct_component_default_params", array() );

	$all_defaults = call_user_func_array('array_merge', $components);

	$components["all"] = $all_defaults;

	$output = json_encode($components);
	$output = htmlspecialchars( $output, ENT_QUOTES );

	echo "defaultOptions = $output;";
}


/**
 * Output array of all not CSS options for each component
 *
 * @since 0.3.2
 */

function ct_init_not_css_options() {

	$components = apply_filters( "ct_not_css_options", array() );

	$output = json_encode($components);
	$output = htmlspecialchars( $output, ENT_QUOTES );

	echo "notCSSOptions = $output;";
}


/**
 * Pass Components Tree JSON to ng-init directive
 *
 * @since 0.1
 */

function ct_components_tree_init() {

	echo "init();";
}


/**
 * Output Components nice names
 *
 * @since 0.1.2
 */

function ct_init_nice_names() {

	$names = apply_filters( "ct_components_nice_names", array() );

	$names['root'] = "Root";

	$output = json_encode($names);
	$output = htmlspecialchars( $output, ENT_QUOTES );

	echo "niceNames = $output;";
}


/**
 * Output Page and Global Settings
 *
 * @since 0.1.3
 */

function ct_init_settings() { 

	// Page settings
	$output = json_encode( ct_get_page_settings( get_the_ID() ) );
	$output = htmlspecialchars( $output, ENT_QUOTES );

	echo "pageSettings = $output;";

	// Global settings
	$output = json_encode(ct_get_global_settings());
	$output = htmlspecialchars( $output, ENT_QUOTES );

	echo "globalSettings = $output;";
}


/**
 * Output CSS Classes
 *
 * @since 0.1.7
 */

function ct_init_components_classses() { 
	
	$classes = ct_get_components_classes();

	$output = json_encode( $classes, JSON_FORCE_OBJECT );
	$output = htmlspecialchars( $output, ENT_QUOTES );

	echo "classes = $output;";
}

function ct_get_components_classes($return_js = false) {
	//update_option("ct_components_classes");
	$classes = get_option("ct_components_classes", array());

	if ( ! is_array( $classes ) )
		return array();
	
	// base64_decode the custom-css and custom-js
	$classes = ct_base64_decode_selectors($classes, $return_js);

	return $classes;
}


/**
 * base64 decode classes and custom selectors custom ccs/js
 *
 * @since 1.3
 * @author Ilya/Gagan
 */

function ct_base64_decode_selectors($selectors, $return_js = false) {

	$selecotrs_js = array();

	foreach($selectors as $key => $class) {
		foreach($class as $statekey => $state) {
			if($statekey == 'media') {
				foreach($state as $bpkey => $bp) {
					foreach($bp as $bpstatekey => $bpstate) {
						if(isset($bpstate['custom-css']) && !strpos($bpstate['custom-css'], ' '))
		  					$selectors[$key][$statekey][$bpkey][$bpstatekey]['custom-css'] = base64_decode($bpstate['custom-css']);
		  				if(isset($bpstate['custom-js'])) {
		  					if(!strpos($bpstate['custom-js'], ' '))
		  						$selectors[$key][$statekey][$bpkey][$bpstatekey]['custom-js'] = base64_decode($bpstate['custom-js']);
		  					// output js to the footer
		  					$classes_js[implode("_", array($key, $statekey, $bpkey, $bpstatekey))] = $states[$key][$mediakey][$mediastatekey]['custom-js'];	
		  				}
					}
				}
			}
			else {
		  		if(isset($class[$statekey]['custom-css']) && !strpos($class[$statekey]['custom-css'], ' '))
		  			$selectors[$key][$statekey]['custom-css'] = base64_decode($class[$statekey]['custom-css']);
		  		if(isset($class[$statekey]['custom-js'])) {
		  			if(!strpos($class[$statekey]['custom-js'], ' '))
						$selectors[$key][$statekey]['custom-js'] = base64_decode($class[$statekey]['custom-js']);
		  			
		  			// output js to the footer
		  			$selecotrs_js[implode("_", array($key, $statekey))] = $selectors[$key][$statekey]['custom-js'];
		  		}
		  	}
	  	}
  	}

  	if($return_js)
  		return $selecotrs_js;
  	else
  		return $selectors;
}


/**
 * Init custom selectors styles
 *
 * @since 1.3
 */

function ct_init_custom_selectors() {
	
	//update_option( "ct_custom_selectors", array() );
	$selectors = get_option( "ct_custom_selectors", array() );

	// make sure this is an array if we have empty string saved somehow
	if ($selectors == "") {
		$selectors = array();
	}

	$selectors = ct_base64_decode_selectors($selectors);

	$selectors = json_encode( $selectors, JSON_FORCE_OBJECT );
	$selectors = htmlspecialchars( $selectors, ENT_QUOTES );
	
	echo "customSelectors = $selectors;";

	$style_sets = get_option( "ct_style_sets", array() );

	// make sure this is an array if we have empty string saved somehow
	if ($style_sets == "") {
		$style_sets = array();
	}

	$style_sets = json_encode( $style_sets );
	$style_sets = htmlspecialchars( $style_sets, ENT_QUOTES );

	echo "styleSets=$style_sets;";
}

/**
 * retreive shortcodes
 *
 * @since 1.3
 */

function ct_template_shortcodes() {
	$post_id = false;
	$template = false;
	// get archive template
	if ( is_archive() || is_search() || is_404() || is_home() || is_front_page() ) {

		if ( is_front_page() ) {
			$post_id 	= get_option('page_on_front');
			//$shortcodes = get_post_meta( $post_id, "ct_builder_shortcodes", true );
		}
		else if ( is_home() ) {
			$post_id 	= get_option('page_for_posts');
			//$shortcodes = get_post_meta( $post_id, "ct_builder_shortcodes", true );
		}
		else //if ( !isset($shortcodes) || !$shortcodes ) {
		{
			$template 	= ct_get_archives_template();
			$shortcodes = $template?get_post_meta( $template->ID, "ct_builder_shortcodes", true ):false;
		}
	} 
	//else
	// get single template
	if($post_id || (!$template && is_singular())) {

		// get post type
		//$post_id = get_the_ID();
		if($post_id == false)
			$post_id = get_the_ID();

		$ct_render_post_using = get_post_meta( $post_id, "ct_render_post_using", true );
		
		if($ct_render_post_using != 'other_template')
			$shortcodes = get_post_meta( $post_id, "ct_builder_shortcodes", true );
		else
			$shortcodes = false;

		if ( !$shortcodes ) { // it is not a custom view

			$template_id = get_post_meta( $post_id, 'ct_other_template', true );

			if($template_id) {
				$template = get_post($template_id);
			}
			else {

				if(is_front_page() || is_home())
					$template = ct_get_archives_template( $post_id );
				else
					$template = ct_get_posts_template( $post_id );
			}

			// get template shortcodes
			$shortcodes = get_post_meta( $template->ID, "ct_builder_shortcodes", true );
			
			// if the template uses inner content module, populate it from the shortcodes found in the custom view
			if(strpos($shortcodes, '[ct_inner_content') !== false) {
				
				$ct_use_inner_content = get_post_meta($post_id, 'ct_use_inner_content', true);

				if(!$ct_use_inner_content || $ct_use_inner_content == 'content') {
					$singular_shortcodes = '[ct_code_block ct_options=\'{"ct_id":2,"ct_parent":0,"selector":"ct_code_block_2_post_7","original":{"code-php":"PD9waHAKCXdoaWxlKGhhdmVfcG9zdHMoKSkgewogICAgCXRoZV9wb3N0KCk7CgkJdGhlX2NvbnRlbnQoKTsKICAgIH0KPz4="},"activeselector":false}\'][/ct_code_block]';					
				}
				else {
					$singular_shortcodes = get_post_meta( $post_id, "ct_builder_shortcodes", true );
				}
				// temp replace ID's and parent references of the singular_shortcodes
				$pattern = '/(\"ct_id\"\:)([^,}]*)/i';

				$shortcodes = preg_replace_callback($pattern, 'obfuscate_ids', $shortcodes);
				
				$pattern = '/(\"ct_parent\"\:)([^,}]*)/i';
				$shortcodes = preg_replace_callback($pattern, 'obfuscate_ids', $shortcodes);

				$pattern = '/(\"selector\"\:)(\"ct_[^,"}]*)_post_/i';
				$shortcodes = preg_replace_callback($pattern, 'obfuscate_selectors', $shortcodes);

				// find the id of the inner_content module from the above
				$matches = array();
				$pattern = '/\[ct_inner_content[^\]]*ct_id\"\:([\d]*)/i';
				preg_match($pattern, $shortcodes, $matches);
				$container_id = $matches[1];

				// set the parent_id of all modules having parent_id=0 to the id found above
				$singular_shortcodes = str_replace('"ct_parent":0', '"ct_parent":'.$container_id, $singular_shortcodes);
				$singular_shortcodes = str_replace('"ct_parent":"0"', '"ct_parent":'.$container_id, $singular_shortcodes);

				//convert any would be nested sections to div blocks
				//$singular_shortcodes = str_replace('[ct_section', '[ct_div_block', $singular_shortcodes);
				//$singular_shortcodes = str_replace('[/ct_section', '[/ct_div_block', $singular_shortcodes);

				// re-index the depths of the components inside the singular_shortcodes on the basis
				// of the inner most nesting of the same type of component in the outer template

				preg_match_all("/(\[ct_inner_content[^\]]*ct_parent[\"|\']:([^,]*),([^\]]*)\]?)(\[\/ct_inner_content]?)/", $shortcodes, $matches);

				$parent_id = intval($matches[2][0]);
				
				set_ct_offsetDepths_source($parent_id, $shortcodes);

				$singular_shortcodes = preg_replace_callback("/([\[|\/])(ct_[^\s\[\]\d]*)[_]?([0-9]?)/", 'ct_offsetDepths', $singular_shortcodes);

				// insert the page contents into the inner_content module of the parent template 
				$pattern = '/(\[ct_inner_content([^\]]*)\]?)(\[\/ct_inner_content]?)/i';
				$replacement = '${1}'.$singular_shortcodes.'${3}';
				
				$shortcodes = preg_replace($pattern, $replacement, $shortcodes);
			}
			
		}
	} else {

		$template 	= ct_get_archives_template();
		$shortcodes = $template?get_post_meta( $template->ID, "ct_builder_shortcodes", true ):false;
	}

	if($shortcodes)
		return $shortcodes;
	else
		return false;
}

/**
 * Init style sheets
 *
 * @since 0.3.4
 * @author gagan goraya
 */

function ct_init_style_sheets() {
	
	
	$style_sheets = get_option( "ct_style_sheets", array() ); 

	// it was returning 'string (0) ""' first time, don't know why
	if ( !is_array( $style_sheets ) )
		$style_sheets = array();
	
	//base 64 decode
	foreach($style_sheets as $key => $value) {
		$style_sheets[$key] = base64_decode($style_sheets[$key]);
	}

	$output = json_encode( $style_sheets, JSON_FORCE_OBJECT );
	$output = htmlspecialchars( $output, ENT_QUOTES );
	
	echo "styleSheets = $output;";
}

/**
 * Output all saved CSS styles to frontend
 *
 * @since 0.1.3
 */

function ct_css_styles() {
	// Global settings
	$global_settings = ct_get_global_settings();

	$components_defaults = apply_filters("ct_component_default_params", array() );

	global $fake_properties;

	$fake_properties = array( 
			'overlay-color', 
			'background-position-left', 
			'background-position-top',
			'background-size-width',
			'background-size-height',
			'ct_content',
			'tag',
			'url',
			'src',
			'alt',
			'target',
			'icon-id',
			"section-width",
			"custom-width",
			"container-padding-top",
			"container-padding-right",
			"container-padding-bottom",
			"container-padding-left",
			"custom-css",
			"custom-js",
			"code-css",
			"code-js",
			"code-php",
			"gutter",
			'border-all-color',
			'border-all-style',
			'border-all-width',
			'function_name',
			'friendly_name',
			'shortcode_tag',
			'id'
		);

	// Output all components default styles
	foreach ( $components_defaults as $component_name => $values ) {
		
		$component_name = str_replace( "_", "-", $component_name );
		
		if ( $component_name == "ct-paragraph" ) {
			echo ".$component_name p {\r\n";
		}
		else {
			echo ".$component_name {\r\n";
		}
		if(is_array($values)) {
			foreach ( $values as $name => $value ) {

				// skip uints
				if ( strpos( $name, "-unit") ) {
					continue;
				}

				// skip empty values
				if ( $value === "" ) {
					continue;
				}

				// skip fake properties
				if ( in_array( $name, $fake_properties ) ) {
					continue;
				}

				// handle global fonts
				// if ( $name == "font-family" && is_array( $value ) ) {
				// 	$value = $global_settings['fonts'][$value[1]];

				// 	if ( strpos($value, ",") === false && strtolower($value) != "inherit" ) {
				// 		$value = "'$value'";
				// 	}
				// }

				// handle unit options
				if ( isset($values[$name.'-unit']) && $values[$name.'-unit'] ) {
					// set to auto
					if ( $values[$name.'-unit'] == 'auto' ) {
						$value = 'auto';
					}
					// or add unit
					else {
						$value .= $values[$name.'-unit'];
					}
				}

				if ( $value !== "" ) {
					echo "  $name:$value;\r\n";
				}
			}
		}

		echo "}\r\n";
	}

	// Below is only for frontend
	if ( defined("SHOW_CT_BUILDER") )
		return;
	
	$css = "";

	$page_settings = false;
	$post_id = false;
	$template = false;

	// get archive template
	if ( is_archive() || is_search() || is_404() || is_home() || is_front_page() ) {

		if (is_front_page()) {
			$post_id 		= get_option('page_on_front');
			//$page_settings 	= ct_get_page_settings( $post_id );
		}
		else if(is_home()) {
			$post_id 		= get_option('page_for_posts');
			//$page_settings 	= ct_get_page_settings( $post_id );
		}

		// do not apply any templates if there is a custom view
		else {

			$template = ct_get_archives_template();

			$page_settings = ct_get_page_settings( $template->ID );
		}
	} 
	
	// get single template
	if($post_id || (!$template && is_singular())) {

		if($post_id == false)
			$post_id = get_the_ID();

		$ct_render_post_using = get_post_meta( $post_id, "ct_render_post_using", true );

		if($ct_render_post_using != 'other_template'){
			$custom_view = get_post_meta( $post_id, "ct_builder_shortcodes", true );
		}
		else {
			$custom_view = false;
		}

		// do not apply any templates if there is a custom view
		if ( $custom_view ) {
			$page_settings = ct_get_page_settings( $post_id );
		}
		else {

			$template_id = get_post_meta( $post_id, 'ct_other_template', true );

			if($template_id) {
				$template = get_post($template_id);
			}
			else {

				if(is_front_page() || is_home())
					$template = ct_get_archives_template( $post_id );
				else
					$template = ct_get_posts_template( $post_id );
			}

			// get template shortcodes
			$page_settings = ct_get_page_settings( $template->ID );
		}

	}

	// if no page settings so far, check if we are using a header/footer template
	
	if(!$page_settings) {

		global $ct_template_header_footer_id;

		if(isset($ct_template_header_footer_id)) {

			$page_settings = ct_get_page_settings( $ct_template_header_footer_id );

		}
	}

	$css .= ".ct-section-inner-wrap{\r\n  max-width: ".str_replace('px', '', $page_settings['max-width'])."px;\r\n}\r\n";

	global $media_queries_list;

	// CSS Classes
	$classes = get_option( "ct_components_classes" );

	if ( is_array( $classes ) ) {
		foreach ( $classes as $class => $states ) {
			$style = "";
			foreach ( $states as $state => $options ) {
				
				if ( $state == 'media' ) {

					foreach ( $media_queries_list as $media_name => $media ) {
						$max_width = $media_queries_list[$media_name]['maxSize'];

						if ( isset($options[$media_name]) && $media_name != "default") {

							$style .= "@media (max-width: $max_width) {\n";
								foreach ( $options[$media_name] as $media_state => $media_options ) {
									$style .= ct_generate_class_states_css($class, $media_state, $media_options, true);
								}
							$style .= "}\n\n";
						}
					}
				}
				else {
					$style = ct_generate_class_states_css($class, $state, $options).$style;
				}
			}

			$css .= $style;
		}
	}

	$text_font = $global_settings['fonts']['Text'];

	if ( !is_array($value) && strpos($text_font, ",") === false && strtolower($value) != "inherit" ) {
		$text_font = "'$text_font'";
	}
	
	// output CSS
	echo $css;
}
add_action("ct_footer_styles", "ct_css_styles");

function ct_generate_class_states_css( $class, $state, $options, $is_media = false, $is_selector = false ) {
	
	global $fake_properties;
	//global $font_families_list;
	$css = "";

	$components_defaults = apply_filters("ct_component_default_params", array() );
	$defaults = call_user_func_array('array_merge', $components_defaults);

	if ( !$is_selector ) {
		if ( $state != 'original' ) {
			$css .= ".$class:not(.ct-paragraph):$state,\r\n";
			if ( is_pseudo_element($state) ) {
				$css .= ".$class.ct-paragraph p:$state{\r\n";
			}
			else {
				$css .= ".$class.ct-paragraph:$state p{\r\n";
			}
		}
		else {
			$css .= ".$class:not(.ct-paragraph),\r\n";
			$css .= ".$class.ct-paragraph p{\r\n";
		}
	}
	else {
		if ( $state != 'original' ) {
			$css .= "$class:$state{\r\n";
		}
		else {
			$css .= "$class{\r\n";	
		}
	}

	$content_included = false;
	
	// handle units
	if(is_array($options)) {
		foreach ( $options as $name => $value ) {
			// handle unit options
			if ( isset($defaults[$name.'-unit']) && $defaults[$name.'-unit'] ) {

				if ( isset($options[$name.'-unit']) && $options[$name.'-unit'] ) {
					// set to auto
					if ( $options[$name.'-unit'] == 'auto' ) {
						$options[$name] = 'auto';
					}
					// or add unit
					else {
						$options[$name] .= $options[$name.'-unit'];
					}
				}
				else {
					$options[$name] .= $defaults[$name.'-unit'];
				}
			}
			else {
	            if ( $options[$name] == 'auto' ) {
	            	$name = str_replace("-unit", "", $name);
	                $options[$name] = 'auto';
	            }
			}
		}
	}

	// handle background-position option
	if ( (isset($options['background-position-left']) && $options['background-position-left']) || (isset($options['background-position-top']) && $options['background-position-top']) ) {

		$left = $options['background-position-left'] ? $options['background-position-left'] : "0%";
		$top  = $options['background-position-top'] ? $options['background-position-top'] : "0%";
		$options['background-position'] = $left . " " . $top;
	}

	// handle background-size option
	if ( isset($options['background-size']) && $options['background-size'] == "manual" ) {

		$width = $options['background-size-width'] ? $options['background-size-width'] : "auto";
		$height = $options['background-size-height'] ? $options['background-size-height'] : "auto";
		$options['background-size'] = $width . " " . $height;
	}
	
	// loop all other options
	if(is_array($options)) {
		foreach ( $options as $name => $value ) {

			// skip units
			if ( strpos( $name, "-unit") ) {
				continue;
			}

			// skip empty values
			if ( $value === "" ) {
				continue;
			}

			if ( $name == "font-family") {

				if ( $value[0] == 'global' ) {
						$settings 	= get_option("ct_global_settings");
						$value 		= isset($settings['fonts'][$value[1]]) ? $settings['fonts'][$value[1]]: '';
					}

				//$font_families_list[] = $value;

				if ( strpos($value, ",") === false && strtolower($value) != "inherit") {
					$value = "'$value'";
				}
			}

			// update options array values if there was modifications
			$options[$name] = $value;

			// skip fake properties
			if ( in_array( $name, $fake_properties ) ) {
				continue;
			}

			// handle image urls
			if ( $name == "background-image") {
				
				$value = "url(".$value.")";
				// trick for overlay color
	            if ( isset( $options['overlay-color'] ) ) {
	                $value = "linear-gradient(" . $options['overlay-color'] . "," . $options['overlay-color'] . "), " . $value;
	            }
			}
			
			// add quotes for content for :before and :after
			if ( $name == "content" ) {
				//$value = addslashes( $value );
				$value = str_replace('"', '\"', $value);
				$value = "\"$value\"";
				$content_included = true;
			}

			// finally add to CSS
			$css .= " $name:$value;\r\n";
		}
	}
	
	if ( !$content_included && ( $state == "before" || $state == "after" ) && !$is_media ) {
		$css .= "  content:\"\";\r\n";
	}

	// add custom CSS to the end
	if ( isset($options["custom-css"]) && $options["custom-css"] ) {
		$css .= base64_decode( $options["custom-css"] ) . "\r\n";
	}

	$css .= "}\r\n";

	// handle container padding for classes
	if ( (isset($options['container-padding-top']) && $options['container-padding-top']) 	 ||
		 (isset($options['container-padding-right']) && $options['container-padding-right'])  ||
		 (isset($options['container-padding-bottom']) && $options['container-padding-bottom']) ||
		 (isset($options['container-padding-left']) && $options['container-padding-left']) ) {

		$css .= ".$class .ct-section-inner-wrap {\r\n";
		
		if ( isset($options['container-padding-top']) && $options['container-padding-top'] ) {
			$css .= "padding-top: " . $options['container-padding-top'] . ";\r\n";
		}
		if ( isset($options['container-padding-right']) && $options['container-padding-right'] ) {
			$css .= "padding-right: " . $options['container-padding-right'] . ";\r\n";
		}
		if ( isset($options['container-padding-bottom']) && $options['container-padding-bottom'] ) {
			$css .= "padding-bottom: " . $options['container-padding-bottom'] . ";\r\n";
		}
		if ( isset($options['container-padding-left']) && $options['container-padding-left'] ) {
			$css .= "padding-left: " . $options['container-padding-left'] . ";\r\n";
		}

		$css .= "}\r\n";
	}

	return $css;
}


/**
 * Check if state is pseudo-element by it's name
 *
 * @since 0.4.0
 * @author Ilya K.
 */

function is_pseudo_element( $name ) {
	
	if ( 
            strpos($name, "before")       === false &&
            strpos($name, "after")        === false &&
            strpos($name, "first-letter") === false &&
            strpos($name, "first-line")   === false &&
            strpos($name, "selection")    === false
        ) 
    {
        return false;
    }
    else {
        return true;
    }
}

/**
 * Generate font familes list to load
 *
 * @since  0.2.3
 */

function ct_get_font_families_string( $font_families ){

	if ( ! $font_families ) {
		return "";
	}

	$web_safe_fonts = array(
			'inherit',
			'Inherit',
			'Georgia, serif',
			'Times New Roman, Times, serif',
			'Arial, Helvetica, sans-serif',
			'Arial Black, Gadget, sans-serif',
			'Tahoma, Geneva, sans-serif',
			'Verdana, Geneva, sans-serif',
			'Courier New, Courier, monospace'
		);

	// don't load web safe fonts
	$font_families = array_diff( $font_families, $web_safe_fonts );

	// filter array for empty values
	$font_families = array_filter( $font_families, function( $font ) {
						return $font !== '';
					});

	// filter array for duplicate values
	$font_families = array_unique( $font_families );

	// add font weights
	$font_families = array_map( function( $font ) {
						return $font . ':100,200,300,400,500,600,700,800,900';
					}, $font_families );

	// add "" quotes
	$font_families = array_map( function( $font ) {
						return '"' . $font . '"';
					}, $font_families );		

	// create fonts string to pass into JS
	$font_families = implode(",", $font_families);

	return $font_families;
}


/**
 * Echo all stylesheets
 * 
 * @since 0.3.4
 * @author gagan goraya
 */

function ct_footer_stylesheets_hook() {

	if ( ! defined("SHOW_CT_BUILDER") )
		return;

	$style_sheets = get_option( "ct_style_sheets", array() );

	// it was returning 'string (0) ""' first time, don't know why
	if ( !is_array( $style_sheets ) )
		$style_sheets = array();

	foreach($style_sheets as $key => $value) {
		echo "\n<style type=\"text/css\" id=\"ct-style-sheet-$key\" class=\"ct-css-location\">";
		echo "\n".base64_decode($style_sheets[$key])."\n";
		echo "</style>\n";
	}

}
add_action("wp_footer", "ct_footer_stylesheets_hook");

/**
 * Echo all components JS like web fonts etc
 * 
 * @since 0.1.9
 */

function ct_footer_script_hook() {
	echo "<script type=\"text/javascript\" id=\"ct-footer-js\">";
		do_action("ct_footer_js");
	echo "</script>";


	$footer_js = ct_get_components_classes(true);
	if(is_array($footer_js)) {
		foreach($footer_js as $key => $val) {
			echo "<script type=\"text/javascript\" id=\"$key\">";
				echo $val;
			echo "</script>";		
		}
	}

}
add_action("wp_footer", "ct_footer_script_hook");


/**
 * Displays a warning for non-chrome browsers in the builder
 * 
 * @since 0.3.4
 * @author gagan goraya
 */

function ct_chrome_modal() {

	if ( defined("SHOW_CT_BUILDER") )  {
		$dismissed = get_option("ct_chrome_modal", false );

		$warningMessage = __("<h2><span class='ct-icon-warning'></span> Warning: we recommend Google Chrome when designing pages</h2><p>The designs you create using Oxygen will work properly in all modern browsers including but not limited to Chrome, Firefox, Safari, and Internet Explorer/Edge.</p><p>But for the best, most stable experience when using Oxygen to design pages, we recommend using Google Chrome.</p><p>We've done most of our testing with Chrome and expect that you will encounter minor bugs in the builder when using Firefox or Safari. Please report those to us by e-mailing at support@oxygenapp.com.</p><p>We have no intention of making the builder work well in Internet Explorer.</p><p>Again, this message only applies to the builder itself. The pages you create with Oxygen will render correctly in all modern browsers.</p><p>Best Regards,<br />The Oxygen Team</p>", 'component-theme' );

		$hideMessage = __("hide this notice", 'component-theme' );

		if(!$dismissed) {


			echo "<div ng-click=\"removeChromeModal(\$event)\" class=\"ct-chrome-modal-bg\"><div class=\"ct-chrome-modal\"><a href=\"#\" class=\"ct-chrome-modal-hide\">".$hideMessage."</a>"."</div></div>";

		?>
			<script type="text/javascript">
			
				jQuery(document).ready(function(){
					var warningMessage = "<?php echo $warningMessage; ?>";
					
			        var isChrome = /Chrome/.test(navigator.userAgent) && /Google Inc/.test(navigator.vendor);
			        
			        var chromeModalWrap = jQuery('.ct-chrome-modal-bg');

			        if(isChrome) {
			        	chromeModalWrap.remove();
					}
			        else {
						chromeModalWrap.css('display', 'block');
			           	var chromeModal = jQuery('.ct-chrome-modal');
			            chromeModal.append(warningMessage);
			        }

			    });
			
			</script>

			<?php
		}
	}

}

add_action("wp_footer", "ct_chrome_modal");



/**
 * Fix for <p></p> tags around component shortocdes
 * 
 * @since 0.1.6
 */

//remove_filter("the_content", "wpautop");

/**
 * Turn off wptexturize https://codex.wordpress.org/Function_Reference/wptexturize
 * 
 * @since 0.1.6
 */

add_filter("run_wptexturize", "__return_false");


/**
 * Add support for certain WordPress features
 * 
 * @since 0.2.3
 */

function ct_theme_support() {

	add_theme_support("menus"); 
	add_theme_support("post-thumbnails");
	add_theme_support("title-tag");
	add_theme_support("woocommerce");
}
add_action("init", "ct_theme_support");


/**
 * Uses a dedicated template to render CSS only that can be loaded from external links
 * or Oxygen main template to show builder or builder designed page
 *
 * @author gagan goraya
 * @since 0.3.4
 */

function ct_css_output( $template ) {
	
	$new_template = '';
	
	if ( $template != get_page_template() && $template != get_index_template() ) {
		global $ct_replace_render_template;
		$ct_replace_render_template = $template;
	}

	if ( isset( $_REQUEST['xlink'] ) && stripslashes( $_REQUEST['xlink'] ) == 'css' ) {
		if ( file_exists( dirname( __FILE__) . '/csslink.php' ) ) {
			$new_template = dirname( __FILE__ ) . '/csslink.php';
		}
	}
	else {
		// if there is saved template or if we are in builder mode
		//if ( ct_template_output( true ) || defined( "SHOW_CT_BUILDER" ) ) {
			if ( file_exists(plugin_dir_path( __FILE__ ) . "/oxygen-main-template.php") ) {
				$new_template =  plugin_dir_path( __FILE__ ) . "/oxygen-main-template.php";
			}
		//}
	}
	
	if ( '' != $new_template ) {
		return $new_template;
	}
		
	return $template;
}
add_filter( 'template_include', 'ct_css_output', 99 );

function ct_determine_render_template( $template ) {
	
	$new_template = '';

	if ( defined( "SHOW_CT_BUILDER" ) ) {
		return get_index_template();
	}

	$post_id 	 = get_the_ID();
	$custom_view = false;

	if ( !is_archive() ) {
		$custom_view = get_post_meta( $post_id, "ct_builder_shortcodes", true );
	}
	
	if ( $custom_view || ct_template_output( true ) ) {
		return get_page_template();
	}
	
	return $template;
}
add_filter( 'template_include', 'ct_determine_render_template', 98 );


/**
 * Try to get CSS styles before WP run to speed up page load
 * 
 * @since 1.1.1
 * @author Ilya K.
 */

function ct_css_link( $template ) {

	if ( isset( $_REQUEST['action'] ) && stripslashes( $_REQUEST['action'] ) == 'save-css' ) {
		return;
	}

	if ( ! isset( $_GET['ct_builder'] ) || ! $_GET['ct_builder'] ) {
		if ( isset( $_REQUEST['xlink'] ) && stripslashes( $_REQUEST['xlink'] ) == 'css' ) {
			ob_start();
			include 'csslink.php';
			ob_end_clean();
		}
	}
}
//add_action("after_setup_theme", "ct_css_link");


/**
 * Get template as soon as possible
 * 
 * @since 1.1.1
 * @author Ilya K.
 */

function ct_pre_template_output( $template ) {

	// support for elementor plugin
	if ( isset( $_REQUEST['elementor-preview'] ) ) {
		return;
	}

	global $template_content;
	$template_content = ct_template_output();
}
//add_action("wp", "ct_pre_template_output");


/**
 * Registers all the widgets to be rendered to the WP globals
 *
 * @author gagan goraya
 * @since 0.3.4
 */
	
function ct_register_widgets( ) {
	global $_wp_sidebars_widgets, $shortcode_tags;

	if(!(isset($_wp_sidebars_widgets['ct-virtual-sidebar'])))
		$_wp_sidebars_widgets['ct-virtual-sidebar'] = array();

	$content = ct_template_output(true);

	// Find all registered tag names in $content.
	preg_match_all( '@\[([^<>&/\[\]\x00-\x20=]++)@', $content, $matches );
	$tagnames = array_intersect( array_keys( $shortcode_tags ), $matches[1] );

	if(!array_search('ct_widget', $tagnames))
		return;
	
	$pattern = get_shortcode_regex( array('ct_widget') );
	
	preg_match_all( "/".$pattern."/", $content, $matches );

	foreach($matches[3] as $widgetOptions) {
		preg_match('@\"id_base\":\"([^\"]*)\"@', $widgetOptions, $opMatches);
		array_push($_wp_sidebars_widgets['ct-virtual-sidebar'], $opMatches[1]);
	}
}
add_filter( 'template_redirect', 'ct_register_widgets', 19 );


/**
 * Add Cache-Control headers to force page refresh 
 * on browser's back button click
 *
 * @since 0.4.0
 * @author Ilya K.
 */

function ct_add_headers() {

	if ( defined("SHOW_CT_BUILDER") ) {
		header_remove("Cache-Control");
		header("Cache-Control: no-cache, no-store, must-revalidate, max-age=0"); // HTTP 1.1.
	}
}
add_action( 'send_headers', 'ct_add_headers' );


/**
 * Add 'oxygen-body' class for frontend only
 *
 * @since 0.4.0
 * @author Ilya K.
 */

function ct_body_class($classes) {

	if ( ! defined("SHOW_CT_BUILDER") ) {
		$classes[] = 'oxygen-body';
	}
	else {
		$classes[] = 'oxygen-builder-body';	
	}

	return $classes;
}
add_filter('body_class', 'ct_body_class');


/**
 * Loading webfonts for the front end, in the <head> section
 *
 * @since 0.3.4
 * @author gagan goraya
 */

function add_web_font() {

	if ( defined("SHOW_CT_BUILDER") ) {
		return;
	}

	global $header_font_families;
	$header_font_families = array();

	$global_settings = ct_get_global_settings();
	$shortcodes = false;
	// add default globals
	foreach ( $global_settings['fonts'] as $key => $value ) {
		$header_font_families[] = $value;
	}
	
	$shortcodes = ct_template_shortcodes();

	/*if ( is_archive() || is_search() || is_404() || is_home() || is_front_page() ) {
		
		if (is_front_page()) {
			$post_id 		= get_option('page_on_front');
			$shortcodes 	= get_post_meta( $post_id, "ct_builder_shortcodes", true );
		}
		else if(is_home()) {
			$post_id 		= get_option('page_for_posts');
			$shortcodes 	= get_post_meta( $post_id, "ct_builder_shortcodes", true );
		}

		if ( !$shortcodes ) {
			$template = ct_get_archives_template();
			$shortcodes = get_post_meta( $template->ID, "ct_builder_shortcodes", true );
		}
	
	} 
	else
	// get single template
	if ( is_singular() ) {

		//if it has a custom view
		$post_id 		= get_the_ID();

		$ct_render_post_using = get_post_meta( $post_id, "ct_render_post_using", true );

		if($ct_render_post_using != 'other_template') {
			$shortcodes = get_post_meta( $post_id, "ct_builder_shortcodes", true );
		}
		else {
			$shortcodes = false;
		}

		if(!$shortcodes) {

			$template_id = get_post_meta( $post_id, 'ct_other_template', true );
			
			if(!$template_id) {
				$template = ct_get_posts_template( $post_id );
				$template_id = $template->ID;
			}

			// get template shortcodes
			$shortcodes = get_post_meta( $template_id, "ct_builder_shortcodes", true );
		}
	}
	else {

		$template = ct_get_archives_template();

		$shortcodes = get_post_meta( $template->ID, "ct_builder_shortcodes", true );
	}
	*/
	global $shortcode_tags;

	// Find all registered tag names in $content.
	preg_match_all( '@\[([^<>&/\[\]\x00-\x20=]++)@', $shortcodes, $matches );
	$tagnames = array_intersect( array_keys( $shortcode_tags ), $matches[1] );

	$pattern = get_shortcode_regex( $tagnames );

	$i = 0;
	while(strpos($shortcodes, '[') !== false) {
		$i++;
		$new_shortcodes = preg_replace_callback( "/$pattern/", 'get_shortcode_font', $shortcodes );
		// content will stop to change when all shortcodes parsed
		if ($new_shortcodes!==$shortcodes) {
			// update content and continue parsing
			$shortcodes = $new_shortcodes;
		}
		else {
			// all parsed, stop the loop
			break;
		}
		// bulletproof way to stop the loop, I doubt anyone will have 100000+ shortcodes on one page 
		if ($i > 100000) break;
	}
	
	// class based fonts
	$classes = get_option( "ct_components_classes", array() );

	// and also custom selectors fonts
	$selectors = get_option( "ct_custom_selectors", array() );
	$classes = array_merge($classes,$selectors);

	if(is_array($classes)) {
		foreach($classes as $key => $class) {
			foreach($class as $statekey => $state) {
				if($statekey == 'media') {
					foreach($state as $bpkey => $bp) {
						foreach($bp as $bpstatekey => $bpstate) {
							if(isset($bpstate['font-family'])) {
								$value = $bpstate['font-family'];
								if ( is_array( $value ) ) {
									// handle global fonts
									if ( $value[0] == 'global' ) {
										
										$settings 	= get_option("ct_global_settings"); 
										$value 		= $settings['fonts'][$value[1]];
									}
								}
								else {
									$value = htmlspecialchars_decode($value, ENT_QUOTES);
								}

								// skip empty values
								if ( $value === "" ) {
									continue;
								}

								// make font family accessible for web fonts loader
								
								$header_font_families[] = "$value";
							}
						}
					}
				}
				else {
			  		if(isset($class[$statekey]['font-family'])) {
						$value = $class[$statekey]['font-family'];
						if ( is_array( $value ) ) {
							// handle global fonts
							if ( $value[0] == 'global' ) {
								
								$settings 	= get_option("ct_global_settings"); 
								$value 		= isset($settings['fonts'][$value[1]])?$settings['fonts'][$value[1]]:'';
							}
						}
						else {
							$value = htmlspecialchars_decode($value, ENT_QUOTES);
						}

						// skip empty values
						if ( $value === "" ) {
							continue;
						}

						// make font family accessible for web fonts loader
						$header_font_families[] = "$value";			  			
			  		}
			  	}
		  	}
	  	}
	}

	$font_families = ct_get_font_families_string( $header_font_families );

	if ( $font_families ) {

		echo "
		<script type='text/javascript' src='//ajax.googleapis.com/ajax/libs/webfont/1/webfont.js'></script>
		<script type=\"text/javascript\">
		WebFont.load({
			google: {
				families: [$font_families]
			}
		});
		</script>
		";
	}
	
}

function get_shortcode_font($m) {

	global $header_font_families;

	$parsed_atts= shortcode_parse_atts( $m[3] );

	if (!isset($parsed_atts['ct_options'])) {
		return substr($m[0], 1, -1);
	}
	$decoded_atts = json_decode( $parsed_atts['ct_options'], true );

	if(!is_array($decoded_atts))
		return substr($m[0], 1, -1);
	
	$states = array();

	// get states styles (original, :hover, ...) from shortcode atts
	foreach ( $decoded_atts as $key => $state_params ) {
		if ( is_array( $state_params ) ) {
			$states[$key] = $state_params;
		}
	}

	foreach ( $states as $key => $atts ) {
		
		//echo $key."\n";
		if ( in_array($key, array("classes", "name", "selector") ) ) {
			continue;
		}

		if( $key == 'media') {

			foreach($atts as $bpkey => $bp) {
				foreach($bp as $bpstatekey => $bpstate) {
					if(isset($bpstate['font-family'])) {
						$value = $bpstate['font-family'];
						if ( is_array( $value ) ) {
							// handle global fonts
							if ( $value[0] == 'global' ) {
								
								$settings 	= get_option("ct_global_settings"); 
								$value 		= $settings['fonts'][$value[1]];
							}
						}
						else {
							$value = htmlspecialchars_decode($value, ENT_QUOTES);
						}

						// skip empty values
						if ( $value === "" ) {
							continue;
						}

						// make font family accessible for web fonts loader
						$header_font_families[] = "$value";
	  				}
				}
			}
		}
		else {
			// loop trough properties (background, color, ...)
			foreach ( $atts as $prop => $value ) {					

				if ( is_array( $value ) ) {
					// handle global fonts
					if ( $prop == "font-family" && $value[0] == 'global' ) {
						
						$settings 	= get_option("ct_global_settings"); 
						$value 		= $settings['fonts'][$value[1]];
					}
				}
				else {
					$value = htmlspecialchars_decode($value, ENT_QUOTES);
				}

				// skip empty values
				if ( $value === "" ) {
					continue;
				}

				// make font family accessible for web fonts loader
				if ( $prop == "font-family" ) {
					$header_font_families[] = "$value";
				}

			} // endforeach
		}
		
	}
	
	return substr($m[0], 1, -1);
	
}
add_action( 'wp_head', 'add_web_font', 0 );

/**
 * Set site hash if not exist
 */

function oxygen_update_license_hash() {

	//delete_option("oxygen_license_updated");
	if ( ! get_option("oxygen_license_updated") ) {
		
		$old = get_option( 'oxygen_license_key' );

		if ( $old ) {

			global $oxygen_edd_updater;
			
			update_option( 'oxygen_license_key', '' );
			$oxygen_edd_updater->activate_license();

			update_option( 'oxygen_license_key', $old );
			$oxygen_edd_updater->activate_license();
		}
		else {
			update_option( 'oxygen_license_key', '' );
		}

		update_option("oxygen_license_updated", true);
	}
}
add_action( 'after_setup_theme', 'oxygen_update_license_hash' );


/**
 * Get global settings
 *
 * @since 1.1.1
 * @author Ilya K.
 */

function ct_get_global_settings() {

	// get saved settings
	$settings = get_option("ct_global_settings"); 
	
	// defaults
	$settings = wp_parse_args( 
		$settings,
		array ( "fonts" => array(
						'Text' 		=> 'Open Sans',
						'Display' 	=> 'Source Sans Pro' )
			)
	);

	return $settings;
}


/**
 * Get page settings
 *
 * @since 1.1.1
 * @author Ilya K.
 */

function ct_get_page_settings( $id ) {

	// get saved settings

	// if it is builder mode, and the aim is to edit inner_content layout, let the outer template's page settings apply
	$ct_inner = defined("SHOW_CT_BUILDER") && isset($_REQUEST['ct_inner'])? true:false;
	if($ct_inner) {

		// get the outer template, either it would be the one defined by ct_render_post_using or generic view
		$ct_render_post_using = get_post_meta($id, 'ct_render_post_using', true);
		$ct_other_template = false;

		if($ct_render_post_using && $ct_render_post_using == 'other_template') {
			$ct_other_template = get_post_meta($id, 'ct_other_template', true);
		}

		if(!$ct_other_template || $ct_other_template == 0) { // get the generic template
			
			if(intval($id) == intval(get_option('page_on_front')) || intval($id) == intval(get_option('page_for_posts'))) {
				$template = ct_get_archives_template( $id );
			}
			else {
				$template = ct_get_posts_template( $id );
			}

			if($template) {
				$id = $template->ID;
			}
		}
		else
			$id = $ct_other_template;
	}

	$settings = get_post_meta( $id, "ct_page_settings", true );

	// defaults
	$settings = wp_parse_args( 
		$settings,
		array(
			"max-width" => "1120"
		)
	);

	return $settings;
}


/**
 * Minify CSS
 *
 * @since 1.1.1
 * @author Ilya K.
 */

function oxygen_css_minify( $css ) {
	
	// Remove comments
	$css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);

	// Remove space after colons
	$css = str_replace(': ', ':', $css);

	// Remove new lines and tabs
	$css = str_replace(array("\r\n", "\r", "\n", "\t"), '', $css);

	// Remove excessive spaces
	$css = str_replace(array("     ", "    ", "   ", "  "), ' ', $css);

	// Remove space near commas
	$css = str_replace(', ', ',', $css);
	$css = str_replace(' ,', ',', $css);

	// Remove space before/after brackets
	$css = str_replace('{ ', '{', $css);
	$css = str_replace('} ', '}', $css);
	$css = str_replace(' {', '{', $css);
	$css = str_replace(' }', '}', $css);

	// Remove last semicolon
	$css = str_replace(';}', '}', $css);

	// Remove spaces after semicolon
	$css = str_replace('; ', ';', $css);

	return $css;
}

/**
 * Return body class string in response to GET request
 *
 * @since 1.4
 * @author Ilya K. 
 */

function ct_get_body_class() {

	if ( isset( $_GET['ct_get_body_class'] ) && $_GET['ct_get_body_class'] ) {
		echo join( ' ', get_body_class() );
		die();
	}
}
add_action( 'template_redirect', 'ct_get_body_class' );


/**
 * returns the query_vars for the provided single ID
 * used the logic from $wp->process_request()
 *
 * @since 0.3.4
 * @author gagan goraya
 */

function get_query_vars_from_id($id = false) {

	if(!$id)
		return array();
	
	global $wp_rewrite, $wp;

	$public_query_vars = $wp->public_query_vars;
	$private_query_vars = $wp->private_query_vars;

	$permalink = get_permalink($id);
	$extra_query_vars = '';

	// if permalinks not enabeld
	if(!get_option('permalink_structure')) {
		list($temp, $extra_query_vars) = explode('?', $permalink);
	}

	$query_vars = array();
	$post_type_query_vars = array();

	if ( !is_array( $extra_query_vars ) && !empty( $extra_query_vars )) {
		parse_str( $extra_query_vars, $extra_query_vars );
	}
	// Process PATH_INFO, REQUEST_URI, and 404 for permalinks.

	// Fetch the rewrite rules.
	$rewrite = $wp_rewrite->wp_rewrite_rules();

	if ( ! empty($rewrite) ) {
		// If we match a rewrite rule, this will be cleared.
		$error = '404';
		

		$pathinfo = isset( $_SERVER['PATH_INFO'] ) ? $_SERVER['PATH_INFO'] : '';
		list( $pathinfo ) = explode( '?', $pathinfo );
		$pathinfo = str_replace( "%", "%25", $pathinfo );

		list( $req_uri ) = explode( '?', $_SERVER['REQUEST_URI'] );
		$req_uri = str_replace(get_site_url(), '', $permalink);
		
		$home_path = trim( parse_url( home_url(), PHP_URL_PATH ), '/' );
		$home_path_regex = sprintf( '|^%s|i', preg_quote( $home_path, '|' ) );

		// Trim path info from the end and the leading home path from the
		// front. For path info requests, this leaves us with the requesting
		// filename, if any. For 404 requests, this leaves us with the
		// requested permalink.
		$req_uri = str_replace($pathinfo, '', $req_uri);
		$req_uri = trim($req_uri, '/');
		$req_uri = preg_replace( $home_path_regex, '', $req_uri );
		$req_uri = trim($req_uri, '/');
		$pathinfo = trim($pathinfo, '/');
		$pathinfo = preg_replace( $home_path_regex, '', $pathinfo );
		$pathinfo = trim($pathinfo, '/');
		
		

		// The requested permalink is in $pathinfo for path info requests and
		//  $req_uri for other requests.
		if ( ! empty($pathinfo) && !preg_match('|^.*' . $wp_rewrite->index . '$|', $pathinfo) ) {
			$request = $pathinfo;
		} else {
			// If the request uri is the index, blank it out so that we don't try to match it against a rule.
			if ( $req_uri == $wp_rewrite->index )
				$req_uri = '';
			$request = $req_uri;
		}

		// Look for matches.
		$request_match = $request;
		if ( empty( $request_match ) ) {

			// An empty request could only match against ^$ regex
			if ( isset( $rewrite['$'] ) ) {
				$matched_rule = '$';
				$query = $rewrite['$'];
				$matches = array('');
			}
		} else {

			foreach ( (array) $rewrite as $match => $query ) {
				// If the requesting file is the anchor of the match, prepend it to the path info.
				if ( ! empty($req_uri) && strpos($match, $req_uri) === 0 && $req_uri != $request )
					$request_match = $req_uri . '/' . $request;

				if ( preg_match("#^$match#", $request_match, $matches) ||
					preg_match("#^$match#", urldecode($request_match), $matches) ) {

					if ( $wp_rewrite->use_verbose_page_rules && preg_match( '/pagename=\$matches\[([0-9]+)\]/', $query, $varmatch ) ) {
						// This is a verbose page match, let's check to be sure about it.
						$page = get_page_by_path( $matches[ $varmatch[1] ] );
						if ( ! $page ) {
					 		continue;
						}

						$post_status_obj = get_post_status_object( $page->post_status );
						if ( ! $post_status_obj->public && ! $post_status_obj->protected
							&& ! $post_status_obj->private && $post_status_obj->exclude_from_search ) {
							continue;
						}
					}

					// Got a match.
					$matched_rule = $match;
					break;
				}
			}

		}

		if ( isset( $matched_rule ) ) {
			// Trim the query of everything up to the '?'.
			$query = preg_replace("!^.+\?!", '', $query);

			// Substitute the substring matches into the query.
			$query = addslashes(WP_MatchesMapRegex::apply($query, $matches));

			$matched_query = $query;

			// Parse the query.
			parse_str($query, $perma_query_vars);

			// If we're processing a 404 request, clear the error var since we found something.
			if ( '404' == $error )
				unset( $error, $_GET['error'] );
		}

		// If req_uri is empty or if it is a request for ourself, unset error.
		if ( empty($request) || $req_uri == 'index.php' || strpos($_SERVER['PHP_SELF'], 'wp-admin/') !== false ) {
			unset( $error, $_GET['error'] );
		}
	}

	$public_query_vars = apply_filters( 'query_vars', $public_query_vars );

	foreach ( get_post_types( array(), 'objects' ) as $post_type => $t ) {
		if ( is_post_type_viewable( $t ) && $t->query_var ) {
			$post_type_query_vars[$t->query_var] = $post_type;
		}
	}

	foreach ( $public_query_vars as $wpvar ) {

		if ( isset( $extra_query_vars[$wpvar] ) )
			$query_vars[$wpvar] = $extra_query_vars[$wpvar];
		elseif ( isset( $_POST[$wpvar] ) )
			$query_vars[$wpvar] = $_POST[$wpvar];
		elseif ( isset( $_GET[$wpvar] ) )
			$query_vars[$wpvar] = $_GET[$wpvar];
		elseif ( isset( $perma_query_vars[$wpvar] ) )
			$query_vars[$wpvar] = $perma_query_vars[$wpvar];

		if ( !empty( $query_vars[$wpvar] ) ) {
			if ( ! is_array( $query_vars[$wpvar] ) ) {
				$query_vars[$wpvar] = (string) $query_vars[$wpvar];
			} else {
				foreach ( $query_vars[$wpvar] as $vkey => $v ) {
					if ( !is_object( $v ) ) {
						$query_vars[$wpvar][$vkey] = (string) $v;
					}
				}
			}

			if ( isset($post_type_query_vars[$wpvar] ) ) {
				$query_vars['post_type'] = $post_type_query_vars[$wpvar];
				$query_vars['name'] = $query_vars[$wpvar];
			}
		}
	}

	// Convert urldecoded spaces back into +
	foreach ( get_taxonomies( array() , 'objects' ) as $taxonomy => $t )
		if ( $t->query_var && isset( $query_vars[$t->query_var] ) )
			$query_vars[$t->query_var] = str_replace( ' ', '+', $query_vars[$t->query_var] );

	// Don't allow non-public taxonomies to be queried from the front-end.
	if ( ! is_admin() ) {
		foreach ( get_taxonomies( array( 'public' => false ), 'objects' ) as $taxonomy => $t ) {
			/*
			 * Disallow when set to the 'taxonomy' query var.
			 * Non-public taxonomies cannot register custom query vars. See register_taxonomy().
			 */
			if ( isset( $query_vars['taxonomy'] ) && $taxonomy === $query_vars['taxonomy'] ) {
				unset( $query_vars['taxonomy'], $query_vars['term'] );
			}
		}
	}

	// Limit publicly queried post_types to those that are publicly_queryable
	if ( isset( $query_vars['post_type']) ) {
		$queryable_post_types = get_post_types( array('publicly_queryable' => true) );
		if ( ! is_array( $query_vars['post_type'] ) ) {
			if ( ! in_array( $query_vars['post_type'], $queryable_post_types ) )
				unset( $query_vars['post_type'] );
		} else {
			$query_vars['post_type'] = array_intersect( $query_vars['post_type'], $queryable_post_types );
		}
	}

	// Resolve conflicts between posts with numeric slugs and date archive queries.
	$query_vars = wp_resolve_numeric_slug_conflicts( $query_vars );

	foreach ( (array) $private_query_vars as $var) {
		if ( isset($extra_query_vars[$var]) )
			$query_vars[$var] = $extra_query_vars[$var];
	}

	if ( isset($error) )
		$query_vars['error'] = $error;

	
	$query_vars = apply_filters( 'request', $query_vars );

	return $query_vars;
}


/**
 * This is used to offset the IDs of outer template, when inner_content component is used
 *
 * @since 1.2.0
 * @author Gagan S Goraya.
 */

function obfuscate_ids($matches) {
	return $matches[1].((intval($matches[2]) > 0)?(intval($matches[2])+100000):0);
}

function obfuscate_selectors($matches) {
	$id =  intval(substr($matches[2], strrpos($matches[2], '_')+1 , strlen($matches[2])-strrpos($matches[2], '_')-1));
	$prefix = substr($matches[2] , 0, strrpos($matches[2], '_')+1);
	return $matches[1].$prefix.(($id > 0)?($id+100000):0).'_post_';
}


/**
 * This is used to offset the depths of inner_content shortcodes when it has to be contained within an outer template
 *
 * @since 1.2.0
 * @author Gagan S Goraya.
 */

function ct_offsetDepths($matches) {
	global $ct_offsetDepths_source;
	//print_r($matches);
	$tag = $matches[2];

	$depth = is_numeric($matches[3])?intval($matches[3]):1;
	$newdepth = $depth;
	// if tag has a trailing _, remove it
	if(substr($tag, strlen($tag)-1, 1) == '_')
		$tag = substr($tag, 0, strlen($tag)-1);

	if(isset($ct_offsetDepths_source[$tag])) {
		$newdepth += $ct_offsetDepths_source[$tag];
	}

	return $matches[1].$tag.(($newdepth > 1)?'_'.$newdepth:'');
	
}

function ct_undoOffsetDepths($matches) {
	global $ct_offsetDepths_source;
	//print_r($matches);
	$tag = $matches[2];
	$depth = is_numeric($matches[3])?intval($matches[3]):1;
	$newdepth = $depth;
	// if tag has a trailing _, remove it
	if(substr($tag, strlen($tag)-1, 1) == '_')
		$tag = substr($tag, 0, strlen($tag)-1);

	if(isset($ct_offsetDepths_source[$tag])) {
		$newdepth -= $ct_offsetDepths_source[$tag];
	}
	return $matches[1].$tag.(($newdepth > 1)?'_'.$newdepth:'');
	
}

function set_ct_offsetDepths_source($parent_id, $shortcodes) {

	global $ct_offsetDepths_source;
	$ct_offsetDepths_source = array();
	$last_parent_id = false;
	$matches = array();
	while($parent_id > 0 && $parent_id !== $last_parent_id) {
		
		preg_match_all("/\[(ct_[^\s\[\]\d]*)[_]?([0-9]?)[^\]]*ct_id[\"|\']?:$parent_id\,[\"|\']?ct_parent[\"|\']?:(\d*)\,/", $shortcodes, $matches);
		//print_r($matches);
		$last_parent_id = $parent_id;
		$parent_id = intval($matches[3][0]);
		$depth = is_numeric($matches[2][0])?intval($matches[2][0]):1;
		$tag = $matches[1][0];

		// if tag has a trailing _, remove it
		if(substr($tag, strlen($tag)-1, 1) == '_')
			$tag = substr($tag, 0, strlen($tag)-1);
		//echo $tag."  ".$depth."  ".$parent_id."\n";

		if(isset($ct_offsetDepths_source[$tag]) ) {
			if($ct_offsetDepths_source[$tag] > $depth) {
				$ct_offsetDepths_source[$tag] = $depth;
			}
		}
		else
			$ct_offsetDepths_source[$tag] = $depth;

	}
}


/**
 * If post/page has Oxygen template applied return empty stylesheet URL, so theme functions.php never run   
 *
 * @since 1.4
 * @author Ilya K.
 */

function ct_disable_theme_load( $stylesheet_dir ) {

	// disable theme entirely for now
	return "fake";

	if ( isset( $_GET["has_oxygen_template"] ) && $_GET["has_oxygen_template"] ) {
		return $stylesheet_dir;
	}

	if ( defined("HAS_OXYGEN_TEMPLATE") && HAS_OXYGEN_TEMPLATE ) {
		return "";
	}
	else {
		return $stylesheet_dir;
	}
}
// Need to remove for both parent and child themes
add_filter("template_directory", "ct_disable_theme_load", 1, 1);
add_filter("stylesheet_directory", "ct_disable_theme_load", 1, 1);


/**
 * Filter template name so plugins don't confuse Oxygen with any other theme  
 *
 * @since 1.4.1
 * @author Ilya K.
 */

function ct_oxygen_template_name($template) {
	return "oxygen-is-not-a-theme";
}
add_filter("template", "ct_oxygen_template_name");


/**
 * Disable theme validation
 *
 * @since 1.4.1
 * @author Ilya K.
 */

add_filter("validate_current_theme", "__return_false");


/**
 * Send a GET request to that same URL to check if Oxygen template applies
 *
 * @since 1.4
 * @author Ilya K.
 */

function ct_send_has_oxygen_template() {

	if ( defined( 'DOING_AJAX' ) ) {
		return;
	}

	// no need to check when trying to get styles
	if ( isset( $_GET["xlink"] ) && $_GET["xlink"] == "css" ) {
		return;
	}

	// disable theme for builder
	if ( isset( $_GET["ct_builder"] ) && $_GET["ct_builder"] ) {
		define("HAS_OXYGEN_TEMPLATE", true );
		return;
	}

	// prevent a loop
	if ( isset( $_GET["has_oxygen_template"] ) && $_GET["has_oxygen_template"] ) {
		return;
	}

	$response = wp_remote_get( ct_get_current_url( "has_oxygen_template=true" ) );

	if ( ! is_array( $response ) ) {
		define("HAS_OXYGEN_TEMPLATE", false );
	}
	//var_dump($response);
	if ( $response["body"] == "true") {
		define("HAS_OXYGEN_TEMPLATE", true );
	}
	else {
		define("HAS_OXYGEN_TEMPLATE", false );
	}
}
//add_action("plugins_loaded", "ct_send_has_oxygen_template");


/**
 * Listen for a template check, return proper flag and exit the script
 *
 * @since 1.4
 * @author Ilya K.
 */

function ct_has_oxygen_template() {
	if ( isset( $_GET["has_oxygen_template"] ) && $_GET["has_oxygen_template"] ) {
		echo ( ct_template_output(true) ) ? "true" : "false";
		die;
	}
}
//add_action("wp", "ct_has_oxygen_template");


/**
 * Hook to run on plugin activation for proper CPT init
 *
 * @since 1.4.1
 * @author Ilya K.
 */

function oxygen_activate_plugin() {

	// Register CPT the right way
	ct_add_templates_cpt(); // it also hooked into 'init'
	flush_rewrite_rules();
	// set flag
	update_option("oxygen_rewrite_rules_updated", "1");
}
register_activation_hook( CT_PLUGIN_MAIN_FILE, 'oxygen_activate_plugin' );
// flush rules on deactivation
register_deactivation_hook( CT_PLUGIN_MAIN_FILE, 'flush_rewrite_rules' );