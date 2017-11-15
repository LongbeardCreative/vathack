<?php

/**
 * Receive Components Tree and other options as JSON object
 * and save as post conent and meta
 * 
 * @since 0.1
 */

function ct_save_components_tree_as_post() {
	
	$nonce  	= $_REQUEST['nonce'];
	$post_id 	= $_REQUEST['post_id'];

	// check nonce
	if ( ! wp_verify_nonce( $nonce, 'oxygen-nonce-' . $post_id ) ) {
	    // This nonce is not valid.
	    die( 'Security check' );
	}

	// check if user can edit this post
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		die ( 'Security check' );
	}

	// get all data JSON
	$data = file_get_contents('php://input');

	// encode and separate tree from options
	$data = json_decode($data, true);

	$params = $data['params'];
	$tree 	= $data['tree'];
	
	// settings
	$page_settings	 	= $params['page_settings'];
	$global_settings 	= $params['global_settings'];
	
	// classes and selectors
	$classes		 	= $params['classes'];
	$style_sets 		= $params['style_sets'];
	$custom_selectors 	= isset($params['custom_selectors']) ? $params['custom_selectors'] : array();
	$style_sheets 		= $params['style_sheets'];

	// base64 encode js and css code in the IDs
	$tree['children'] = ct_base64_encode_decode_tree($tree['children']);

	// code tree back to JSON to pass into old function
	$components_tree_json = json_encode($tree);
	
	ob_start();

	// transform JSON to shortcodes
	$shortcodes = components_json_to_shortcodes( $components_tree_json );

	// we don't need anything to be output by custom shortcodes
	ob_clean();

	// if it is page's inner content, then discard all the template related shortcodes here
	$ct_inner = isset($_REQUEST['ct_inner'])? true:false;

	if($ct_inner) {
		// find the id of the inner_content module 
		$matches = array();
		$pattern = '/\[ct_inner_content[^\]]*ct_id\"\:([\d]*)/i';

		// re-index the depths of the components inside the singular_shortcodes on the basis
		// of the inner most nesting of the same type of component in the outer template

		preg_match_all("/(\[ct_inner_content[^\]]*ct_parent[\"|\']:([^,]*),([^\]]*)\]?)(\[\/ct_inner_content]?)/", $shortcodes, $matches);

		$parent_id = intval($matches[2][0]);

		//$outer_shortcodes = preg_replace("/\[ct_inner_content.*\[\/ct_inner_content\]/", '', $shortcodes);

		set_ct_offsetDepths_source($parent_id, $shortcodes);

		preg_match($pattern, $shortcodes, $matches);

		$container_id = $matches[1];

		// extract the contents inside the inner_content block
		$pattern = '/(\[ct_inner_content([^\]]*)\]?)(.*)(\[\/ct_inner_content]?)/i';
		$matches = array();

		preg_match($pattern, $shortcodes, $matches);

		$shortcodes = $matches[3];

		// replace the parent_id of the elements having that equal to the ID of the inner_content module with 0 (assign to root)
		$shortcodes = str_replace('"ct_parent":'.$container_id, '"ct_parent":0', $shortcodes);
		$shortcodes = str_replace('"ct_parent":"'.$container_id.'"', '"ct_parent":0', $shortcodes);
		
		$shortcodes = preg_replace_callback("/([\[|\/])(ct_[^\s\[\]\d]*)[_]?([0-9]?)/", 'ct_undoOffsetDepths', $shortcodes);
	}
	
	// Save as post Meta (NEW WAY)
	update_post_meta( $post_id, 'ct_builder_shortcodes', $shortcodes );
	do_action( 'save_post', $post_id, get_post( $post_id ) );
  	
  	// Process settings
  	// Page
  	$page_settings_saved 	= update_post_meta( $post_id, "ct_page_settings", $page_settings );

  	// Global
  	$global_settings_saved 	= update_option("ct_global_settings", $global_settings );

  	// Process classes
  	//$classes 				= json_decode( stripslashes( $classes ), true );

/*  	// base64 encode js and css code in the classes
	foreach($classes as $key => $class) {

		foreach( $class as $statekey => $state) {
			
			if( $statekey == "media") {
				foreach($state as $bpkey => $bp) {
					foreach($bp as $bpstatekey => $bp) {
						if(isset($class[$statekey][$bpkey][$bpstatekey]['custom-css']))
		  					$classes[$key][$statekey][$bpkey][$bpstatekey]['custom-css'] = base64_encode($classes[$key][$statekey][$bpkey][$bpstatekey]['custom-css']);

		  				if(isset($class[$statekey][$bpkey][$bpstatekey]['custom-js']))
		  					$classes[$key][$statekey][$bpkey][$bpstatekey]['custom-js'] = base64_encode($classes[$key][$statekey][$bpkey][$bpstatekey]['custom-js']);  						
					}
				}
			}
			else {

		  		if(isset($class[$statekey]['custom-css']))
		  			$classes[$key][$statekey]['custom-css'] = base64_encode($class[$statekey]['custom-css']);
		  		if(isset($class[$statekey]['custom-js']))
		  			$classes[$key][$statekey]['custom-js'] = base64_encode($class[$statekey]['custom-js']);
		  	}
	  	}
  	}*/
  	
  	$classes_saved = update_option("ct_components_classes", ct_base64_encode_selectors($classes) );

  	// Process custom CSS selectors
  	$custom_selectors_saved = update_option("ct_custom_selectors", ct_base64_encode_selectors($custom_selectors) );
  	$style_sets_updated 	= update_option("ct_style_sets", $style_sets );

	// base64 encode style sheets
  	foreach($style_sheets as $key => $val) {
  		$style_sheets[$key] = base64_encode($style_sheets[$key]);
  	}

  	$style_sheets_saved = update_option("ct_style_sheets", $style_sheets );

  	$return_object = array(
  		
  		"page_settings_saved" 	 => $page_settings_saved, // true or false
  		"global_settings_saved"  => $global_settings_saved, // true or false
  		
  		"classes_saved" 		 => $classes_saved, // true or false
  		"custom_selectors_saved" => $custom_selectors_saved, // true or false
  		"style_sheets_saved" 	 => $style_sheets_saved, // true or false
  		
  	);

	// echo JSON
  	header('Content-Type: application/json');
  	echo json_encode( $return_object );
	die();
}
add_action('wp_ajax_ct_save_components_tree', 'ct_save_components_tree_as_post');

/**
 * Helper function to base 64 encode/decode custom-css and js recursively through the tree
 * default is encode operation
 * Set second param to be true, for decode operation
 * 
 * @since 0.3.4
 * @author gagan goraya 
 */

function ct_base64_encode_decode_tree($children, $decode = false) {

	if(!is_array($children))
		return array();


	foreach($children as $key => $item) {

		if(isset($item['children']))
			$children[$key]['children'] = ct_base64_encode_decode_tree( $item['children'], $decode );
		
		if(!isset($item['options']))
			continue;

		foreach($item['options'] as $optionkey => $option) {
			// ignore ct_id // ignore ct_parent

			if($optionkey == 'ct_id' || $optionkey == 'ct_parent' || $optionkey == 'selector' || $optionkey == 'ct_content')
				continue;

			// if media then 
			if($optionkey == 'media') {
				foreach($option as $mediakey => $mediaoption) {
					foreach($mediaoption as $mediastatekey => $mediastate) {
						if(isset($mediastate['custom-css'])) {
							if($decode) {
								if(!strpos($mediastate['custom-css'], ' ')) {
									$children[$key]['options'][$optionkey][$mediakey][$mediastatekey]['custom-css'] = base64_decode($mediastate['custom-css']);
								}
							}
							else {
								$children[$key]['options'][$optionkey][$mediakey][$mediastatekey]['custom-css'] = base64_encode($mediastate['custom-css']);
							}
						}
						if(isset($mediastate['custom-js'])) {
							if($decode) {
								if(!strpos($mediastate['custom-js'], ' ')) {
									$children[$key]['options'][$optionkey][$mediakey][$mediastatekey]['custom-js'] = base64_decode($mediastate['custom-js']);
								}
							}
							else {
								$children[$key]['options'][$optionkey][$mediakey][$mediastatekey]['custom-js'] = base64_encode($mediastate['custom-js']);
							}
						}

						// base64 encode the content of the before and after states
						if(is_pseudo_element($mediastatekey)) {
							if(isset($mediastate['content'])) {
								if($decode) {
									$children[$key]['options'][$optionkey][$mediakey][$mediastatekey]['content'] = base64_decode($mediastate['content']);
								}
								else {
									$children[$key]['options'][$optionkey][$mediakey][$mediastatekey]['content'] = base64_encode($mediastate['content']);
								}
							}
						}
					}
				}
				continue;
			}


			// for all others, do the thing
			if(isset($option['custom-css'])) {
				if($decode) {
					if(!strpos($option['custom-css'], ' ')) {
						$children[$key]['options'][$optionkey]['custom-css'] = base64_decode($option['custom-css']);
					}
				}
				else {
					$children[$key]['options'][$optionkey]['custom-css'] = base64_encode($option['custom-css']);
				}
			}
			if(isset($option['custom-js'])) {
				if($decode) {
					if(!strpos($option['custom-js'], ' ')) {
						$children[$key]['options'][$optionkey]['custom-js'] = base64_decode($option['custom-js']);
					}
				}
				else {
					$children[$key]['options'][$optionkey]['custom-js'] = base64_encode($option['custom-js']);
				}
			}
			
			// base64 encode the content of the before and after states
			if(is_pseudo_element($optionkey)) {
				if(isset($option['content'])) {
					if($decode) {
						//if(substr($option['content'], -2) == '==') {
							$children[$key]['options'][$optionkey]['content'] = base64_decode($option['content']);
						//}
					}
					else {
						$children[$key]['options'][$optionkey]['content'] = base64_encode($option['content']);
					}
				}
			}
		}

	}

	return $children;
}


/**
 * base64 encode classes and custom selectors custom ccs/js
 * 
 * @since 1.3
 * @author Ilya/Gagan
 */

function ct_base64_encode_selectors($selectors) {
	
	foreach($selectors as $key => $class) {

		foreach( $class as $statekey => $state) {
			
			if( $statekey == "media") {
				foreach($state as $bpkey => $bp) {
					foreach($bp as $bpstatekey => $bp) {
						if(isset($class[$statekey][$bpkey][$bpstatekey]['custom-css']))
		  					$selectors[$key][$statekey][$bpkey][$bpstatekey]['custom-css'] = base64_encode($selectors[$key][$statekey][$bpkey][$bpstatekey]['custom-css']);

		  				if(isset($class[$statekey][$bpkey][$bpstatekey]['custom-js']))
		  					$selectors[$key][$statekey][$bpkey][$bpstatekey]['custom-js'] = base64_encode($selectors[$key][$statekey][$bpkey][$bpstatekey]['custom-js']);  						
					}
				}
			}
			else {

		  		if(isset($class[$statekey]['custom-css']))
		  			$selectors[$key][$statekey]['custom-css'] = base64_encode($class[$statekey]['custom-css']);
		  		if(isset($class[$statekey]['custom-js']))
		  			$selectors[$key][$statekey]['custom-js'] = base64_encode($class[$statekey]['custom-js']);
		  	}
	  	}
  	}

  	return $selectors;
}


/**
 * Save single component (or array of same level components)
 * as "reusable_part" view (ct_tempalte CPT)
 * 
 * @since 0.2.3 
 */

function ct_save_component_as_view() {

	$name 		= $_REQUEST['name'];
	$nonce  	= $_REQUEST['nonce'];
	$post_id 	= $_REQUEST['post_id'];

	// check nonce
	if ( ! wp_verify_nonce( $nonce, 'oxygen-nonce-' . $post_id ) ) {
	    // This nonce is not valid.
	    die( '0' ); 
	}

	// check if user can publish posts
	if ( ! current_user_can( 'publish_posts' ) ) {
		die ( '0' );
	}

	$component 	= file_get_contents('php://input');
	$tree 		= json_decode($component, true);

	// base64 encode js and css code in the IDs
	$tree["children"] = ct_base64_encode_decode_tree($tree['children']);

	$component = json_encode($tree);

	//var_dump($component);

	$shortcodes = components_json_to_shortcodes( $component, true );

	//var_dump($shortcodes);

	$post = array(
		'post_title'	=> $name,
		'post_type' 	=> "ct_template",
		'post_status'	=> "publish",
		// TODO: check who is a post author
		//'post_author' 	=> ""
	);
	
	// Insert the post into the database
	$post_id = wp_insert_post( $post );
	
	if ( $post_id !== 0 ) {
		$meta = update_post_meta( $post_id, 'ct_template_type', "reusable_part");
		update_post_meta( $post_id, 'ct_builder_shortcodes', $shortcodes );
	}

	// echo JSON
	header('Content-Type: application/json');
	echo $post_id;
	die();
}
add_action('wp_ajax_ct_save_component_as_view', 'ct_save_component_as_view');

/**
 * Post single component (or array of same level components)
 * to the Oxygen server
 * 
 * @since 0.4.0
 * @author Ilya K.
 */

function ct_componentize() {

	$nonce  	= $_REQUEST['nonce'];
	$post_id 	= $_REQUEST['post_id'];

	// check nonce
	if ( ! wp_verify_nonce( $nonce, 'oxygen-nonce-' . $post_id ) ) {
	    // This nonce is not valid.
	    die( '0' ); 
	}

	// check if user can publish posts
	if ( ! current_user_can( 'publish_posts' ) ) {
		die ( '0' );
	}

	$id_to_update 	= $_REQUEST["id_to_update"];
	$name 			= stripslashes($_REQUEST['name']);
	$design_set_id 	= $_REQUEST['design_set_id'];
	$category_id 	= $_REQUEST['category_id'];
	$screenshot	 	= $_REQUEST['screenshot'];
	$status	 		= $_REQUEST['status'];
	$component 		= file_get_contents('php://input');

	global $oxygen_api;

	if ( isset( $id_to_update ) && is_numeric( $id_to_update ) && $id_to_update > 0 ) {
		
		// escape string as PUT method is not auto escaped by WP
		$component 	= addslashes($component);
		$name 		= addslashes($name);

		$response = $oxygen_api->update_component( array(
			"id" 			=> $_REQUEST["id_to_update"],
			"name" 			=> $name,
			"content" 		=> $component,
			"design_set_id" => $design_set_id,
			"category_id" 	=> $category_id,
			"screenshot" 	=> $screenshot,
			"status" 		=> $status
		));
	}
	else {
		$response = $oxygen_api->create_component( array(
			"name" 			=> $name,
			"content" 		=> $component,
			"design_set_id" => $design_set_id,
			"category_id" 	=> $category_id,
			"screenshot" 	=> $screenshot,
			"status" 		=> $status
		));
	}
	
	// echo JSON
	header('Content-Type: application/json');
	echo json_encode($response);
	die();
}
add_action('wp_ajax_ct_componentize', 'ct_componentize');


/**
 * Post asset to the Oxygen server
 * 
 * @since 0.4.0
 * @author Ilya K.
 */

function ct_post_asset() {

	$nonce  	= $_REQUEST['nonce'];
	$post_id 	= $_REQUEST['post_id'];

	// check nonce
	if ( ! wp_verify_nonce( $nonce, 'oxygen-nonce-' . $post_id ) ) {
	    // This nonce is not valid.
	    die( '0' ); 
	}

	// check if user can publish posts
	if ( ! current_user_can( 'publish_posts' ) ) {
		die ( '0' );
	}

	$file = file_get_contents('php://input');
	//var_dump($file);
	global $oxygen_api;
	$response = $oxygen_api->post_asset( array(
			"content" 	=> base64_encode($file),
			"file_name" => $_REQUEST["file_name"],
			"file_type" => $_REQUEST["file_type"]
		));

	// echo JSON
	header('Content-Type: application/json');
	echo json_encode($response);
	die();
}
add_action('wp_ajax_ct_post_asset', 'ct_post_asset');


/**
 * Post whole page to the Oxygen server
 * 
 * @since 0.4.0
 * @author Ilya K.
 */

function ct_componentize_page() {

	$nonce  	= $_REQUEST['nonce'];
	$post_id 	= $_REQUEST['post_id'];

	// check nonce
	if ( ! wp_verify_nonce( $nonce, 'oxygen-nonce-' . $post_id ) ) {
	    // This nonce is not valid.
	    die( '0' ); 
	}

	// check if user can publish posts
	if ( ! current_user_can( 'publish_posts' ) ) {
		die ( '0' );
	}

	$name 			= stripslashes($_REQUEST['name']);
	$design_set_id 	= $_REQUEST['design_set_id'];
	//$category_id 	= $_REQUEST['category_id'];
	$screenshot	 	= $_REQUEST['screenshot'];
	$status	 		= $_REQUEST['status'];
	$component 		= file_get_contents('php://input');

	global $oxygen_api;
	$response = $oxygen_api->create_page( array(
			"name" 			=> $name,
			"content" 		=> $component,
			"design_set_id" => $design_set_id,
			//"category_id" 	=> $category_id
			"screenshot" 	=> $screenshot,
			"status" 		=> $status
		));

	// echo JSON
	header('Content-Type: application/json');
	echo json_encode($response);
	die();
}
add_action('wp_ajax_ct_componentize_page', 'ct_componentize_page');


/**
 * Return post Components Tree as a JSON object 
 * in response to AJAX call
 * 
 * @since 0.1.7
 * @author Ilya K.
 */

function ct_get_components_tree() {

	// possible fix
	//error_reporting(E_ALL & ~(E_STRICT|E_NOTICE|E_WARNINGS|E_DEPRECATED));

	$nonce  	= $_REQUEST['nonce'];
	$post_id 	= $_REQUEST['post_id'];
	$id 		= $_REQUEST['id'];

	// check nonce
	if ( ! wp_verify_nonce( $nonce, 'oxygen-nonce-' . $post_id ) ) {
	    // This nonce is not valid.
	    die( 'Security check' ); 
	}

	// check user role
	if ( ! current_user_can( 'read_post', $id ) ) {
		die ( 'Security check' );
	}
	
	// if the intended target to be edited is the inner content
	$shortcodes = false;
	$ct_inner = isset($_REQUEST['ct_inner'])?true:false;
	
	$singular_shortcodes = get_post_meta($id, "ct_builder_shortcodes", true);

	if ( $ct_inner ) {

		$ct_render_post_using = get_post_meta( $id, "ct_render_post_using", true );
		
		if($ct_render_post_using != 'other_template')
			$shortcodes = get_post_meta( $id, "ct_builder_shortcodes", true );
		else
			$shortcodes = false;

		if ( !$shortcodes ) { // it is not a custom view

			$template_id = get_post_meta( $id, 'ct_other_template', true );

			if($template_id) {
				$template = get_post($template_id);
			}
			else {
				if(intval($id) == intval(get_option('page_on_front')) || intval($id) == intval(get_option('page_for_posts')))
					$template = ct_get_archives_template( $id );
				else
					$template = ct_get_posts_template( $id );
			}

			// get template shortcodes
			$shortcodes = get_post_meta( $template->ID, "ct_builder_shortcodes", true );
		}

		if($shortcodes) {

			if(empty($singular_shortcodes)) {
				/*$content_post = get_post($id);
				$content = $content_post->post_content;
				$content = apply_filters('the_content', $content);
				$content = trim(str_replace(']]>', ']]&gt;', $content));*/

				//if(!empty($content))
				$singular_shortcodes = '[ct_code_block ct_options=\'{"ct_id":2,"ct_parent":0,"selector":"ct_code_block_2_post_7","original":{"code-php":"PD9waHAKCXdoaWxlKGhhdmVfcG9zdHMoKSkgewogICAgCXRoZV9wb3N0KCk7CgkJdGhlX2NvbnRlbnQoKTsKICAgIH0KPz4="},"activeselector":false}\'][/ct_code_block]';
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

	if(!$shortcodes) {
		$shortcodes = $singular_shortcodes;
	}
	
	$json = content_to_components_json( $shortcodes );

	// base 64 decode all the custom-css and custom-js down the tree
	$tree = json_decode($json, true);



	$tree['children'] = ct_base64_encode_decode_tree($tree['children'], true);

	if(!isset($tree['name']) || $tree['name'] != 'root') {
        // data is corrupt, the name property should have been equal to root otherwise.
        // provide a clean slate
        $tree['id'] = 0;
        $tree['name'] = 'root';
        $tree['depth'] = 0;
    }

	$json = json_encode($tree);

	// echo response
  	header('Content-Type: text/html');
  	echo $json;
	die();
}

add_action('wp_ajax_ct_get_components_tree', 'ct_get_components_tree');


/**
 * AJAX callback receive component parameters
 * and return component view
 * 
 * @since 0.1
 * @author Ilya K.
 */

function ct_render_shortcode_by_ajax() {

	header('Content-Type: text/html');

	$nonce  	= $_REQUEST['nonce'];
	$post_id 	= $_REQUEST['post_id'];

	// check nonce
	if ( ! wp_verify_nonce( $nonce, 'oxygen-nonce-' . $post_id ) ) {
	    // This nonce is not valid.
	    die( 'Security check' ); 
	}

	// check user role
	if ( ! current_user_can( 'edit_posts' ) ) {
		die ( 'Security check' );
	}

	// get passed parameters
	$name 	 = $_REQUEST['name'];

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

	die();
}
//add_action('wp_ajax_ct_render_shortcode', 'ct_render_shortcode_by_ajax');


/**
 * Adds a flag to the options that the non-chrome-browser 
 * warning in the builder has been dismissed
 * 
 * @since 0.3.4
 * @author Gagan Goraya.
 */

function ct_remove_chrome_modal() {

	$nonce  	= $_REQUEST['nonce'];
	$post_id 	= $_REQUEST['post_id'];

	// check nonce
	if ( ! wp_verify_nonce( $nonce, 'oxygen-nonce-' . $post_id ) ) {
	    // This nonce is not valid.
	    die( 'Security check' ); 
	}

	// check user role
	if ( ! current_user_can( 'edit_posts' ) ) {
		die ( 'Security check' );
	}

	update_option('ct_chrome_modal', true);
	die();
}
add_action('wp_ajax_ct_remove_chrome_modal', 'ct_remove_chrome_modal');


/**
 * Get widget instance and return rendered widget view
 * 
 * @since 0.2.3
 */

function ct_render_widget_by_ajax() {

	header('Content-Type: text/html');

	$nonce  	= $_REQUEST['nonce'];
	$post_id 	= $_REQUEST['post_id'];

	// check nonce
	if ( ! wp_verify_nonce( $nonce, 'oxygen-nonce-' . $post_id ) ) {
	    // This nonce is not valid.
	    die( 'Security check' ); 
	}

	// check user role
	if ( ! current_user_can( 'edit_posts' ) ) {
		die ( 'Security check' );
	}
	
	$instance = array();
	
	$component_json = file_get_contents('php://input');
	$component 		= json_decode( $component_json, true );
	$options 		= $component['options']['original'];

	//var_dump($options["instance"]);
	
	if ( is_array( $options['instance'] ) ) {
		$instance = $options['instance'];
	}

	if ( $GLOBALS['wp_widget_factory']->widgets[$options['class_name']] ) {
		the_widget( $options['class_name'], $instance );
	}
	else {
		printf( __("<b>Error!</b><br/> No '%s' widget registered in this installation", "component-theme"), $options['class_name'] );
	}

	die();
}
add_action('wp_ajax_ct_render_widget', 'ct_render_widget_by_ajax');


/**
 * Get widget instance and return rendered widget form view
 * 
 * @since 0.2.3
 */

function ct_render_widget_form_by_ajax() {

	header('Content-Type: text/html');

	$nonce  	= $_REQUEST['nonce'];
	$post_id 	= $_REQUEST['post_id'];

	// check nonce
	if ( ! wp_verify_nonce( $nonce, 'oxygen-nonce-' . $post_id ) ) {
	    // This nonce is not valid.
	    die( 'Security check' ); 
	}

	// check user role
	if ( ! current_user_can( 'edit_posts' ) ) {
		die ( 'Security check' );
	}

	$component_json = file_get_contents('php://input');
	$component 		= json_decode( $component_json, true );
	$options 		= $component['options']['original'];

	$instance = array();

	if ( is_array( $options['instance'] ) ) {
		$instance = $options['instance'];
	}

	if ( $GLOBALS['wp_widget_factory']->widgets[$options['class_name']] ) {
		$GLOBALS['wp_widget_factory']->widgets[$options['class_name']]->form($instance);
	}
	else {
		printf( __("<b>Error!</b><br/> No '%s' widget registered in this installation", "component-theme"), $options['class_name'] );
	}

	die();
}
add_action('wp_ajax_ct_render_widget_form', 'ct_render_widget_form_by_ajax');


/**
 * Return SVG Icon Sets
 * 
 * @since 0.2.1
 */

function ct_get_svg_icon_sets() {

	$nonce  	= $_REQUEST['nonce'];
	$post_id 	= $_REQUEST['post_id'];

	// check nonce
	if ( ! wp_verify_nonce( $nonce, 'oxygen-nonce-' . $post_id ) ) {
	    // This nonce is not valid.
	    die( 'Security check' ); 
	}

	// check user role
	if ( ! current_user_can( 'edit_posts' ) ) {
		die ( 'Security check' );
	}

	$svg_sets = get_option("ct_svg_sets", array() );

	// Convert XML sets to Objects
	foreach ( $svg_sets as $name => $set ) {

		$xml = simplexml_load_string($set);

		$hasSymbols = true;

		foreach($xml->children() as $def) {
			
			if($def->getName() == 'defs') {
				
				foreach($def->children() as $symbol) {
					if($symbol->getName() == 'symbol') {
						$symbol['id'] = str_replace(str_replace(' ', '', $name), '', $symbol['id']);
					} else {
						$hasSymbols = false;
					}
				}
			} else {
				
				$hasSymbols = false;
			}
		}
		
		if( $hasSymbols ) {
			
			$set = $xml->asXML();
			$svg_sets[$name] = new SimpleXMLElement( $set );
		}
		else {
			unset($svg_sets[$name]);
		}
	}

	$json = json_encode( $svg_sets );

	// echo JSON
	header('Content-Type: application/json');
	echo $json;
	die();
}
add_action('wp_ajax_ct_get_svg_icon_sets', 'ct_get_svg_icon_sets');


/**
 * Return template/view data with single post or term posts as JSON
 * 
 * @since 0.1.7
 * @author Ilya K.
 */

function ct_get_template_data() {

	$template_id 		= $_REQUEST['template_id'];
	$preview_post_id 	= $_REQUEST['preview_post_id'];
	$nonce  			= $_REQUEST['nonce'];
	$post_id 			= $_REQUEST['post_id'];

	// check nonce
	if ( ! wp_verify_nonce( $nonce, 'oxygen-nonce-' . $post_id ) ) {
	    // This nonce is not valid.
	    die( 'Security check' ); 
	}

	// check user role
	if ( ! current_user_can( 'read_post', $post_id ) ) {
		die ( 'Security check' );
	}

	// single view
	if ( get_post_meta( $template_id, 'ct_template_type', true ) == "single_post") {
		$data = ct_get_templates_post( $template_id, $preview_post_id );
	}

	// archive view
	if ( get_post_meta( $template_id, 'ct_template_type', true ) == "archive") {
		$data = ct_get_templates_term( $template_id, $preview_post_id );
	}
	
	// make GET request to permalink to retrive body class
	$post_data = (array) $data["postData"];
	$response = wp_remote_get( add_query_arg( 'ct_get_body_class', 'true', $post_data["permalink"] ) );
	if ( is_array($response) ) {
		$body = $response['body'];
		$data["bodyClass"] = $body;
	}
	
	// Return JSON
  	header('Content-Type: application/json');
	echo json_encode($data);
	die();
}

add_action('wp_ajax_ct_get_template_data', 'ct_get_template_data');


/**
 * Return single post object as JSON by ID including shortcodes
 * 
 * @since 0.2.3
 * @author Ilya K.
 */

function ct_get_post_data() {
	
	$nonce  	= $_REQUEST['nonce'];
	$post_id 	= $_REQUEST['post_id'];

	// check nonce
	if ( ! wp_verify_nonce( $nonce, 'oxygen-nonce-' . $post_id ) ) {
	    // This nonce is not valid.
	    die( 'Security check' ); 
	}

	$id 	= $_REQUEST['id'];
	$post 	= get_post( $id );

	// check user role
	if ( ! current_user_can( 'read_post', $id ) ) {
		die ( 'Security check' );
	}

	if ( $post ) {
		$data = ct_filter_post_object( $post );
	}

	// base 64 decode all the custom-css and custom-js down the tree
	$data->post_tree = ct_base64_encode_decode_tree($data->post_tree, true);

	// Echo JSON
  	header('Content-Type: application/json');
	echo json_encode($data);
	die();
}
add_action('wp_ajax_ct_get_post_data', 'ct_get_post_data');


/**
 * NON REFACTORED BELOW
 * 
 */
    

/**
 * Exec PHP/HTML code and return output
 * 
 * @since 0.2.4
 * @author Ilya K.
 * @deprecated 0.4.0
 */

function ct_exec_code() {

	$nonce  	= $_REQUEST['nonce'];
	$post_id 	= $_REQUEST['post_id'];

	// check nonce
	if ( ! wp_verify_nonce( $nonce, 'oxygen-nonce-' . $post_id ) ) {
	    // This nonce is not valid.
	    die( 'Security check' ); 
	}

	// check user role
	if ( ! current_user_can( 'edit_posts' ) ) {
		die( 'Security check' );
	}

	// get all data JSON
	$data = file_get_contents('php://input');

	// encode and separate tree from options
	$data = json_decode($data, true);

	$code = $data['code'];
	$term = $data['term'];
	$post = $data['post'];

	$code = base64_decode($code);

	// archive template
	if ( $term ) {
		
		$term = json_decode(stripcslashes($term), true);

		/**
		 * Archives
		 */
		
		if ( isset( $term["term_id"] ) ) {

			// get all the registered taxonomies
			$taxonomies = get_taxonomies( array() , 'objects' );

			$query = array (
				/** query_var of the registered taxonomy will act as a key here, 
				 *	e.g. for category the query_var is category_name
				 */
				$taxonomies[$term['taxonomy']]->query_var => $term['slug']
			);
		}

		/**
		 * Post types
		 */
		
		else {
			$query = array( 'post_type' => $term['name'] );
		}

	}
	// single template
	elseif ( $post ) {

		/**
		 * $post is WP_Post object, need to reproduce WP_Query for this post
		 */

		$query = get_query_vars_from_id($post["ID"]);
	}
	// not template
	else {

		$query = $_REQUEST['query'];
		$query = json_decode(stripcslashes($query), true);
	}

	// simulate WP Query
	global $wp_query;
	$wp_query = new WP_Query($query);

	//var_dump($wp_query); // this seems to be OK

	// check for code
	if ( $code ) {
		eval( ' ?>' . $code . '<?php ' );
	}
	else {
		_e('No code found', 'component-theme');
	}

	/* Restore original Post Data. Do we actually need this? */
	wp_reset_postdata();

	die();
}
add_action('wp_ajax_ct_exec_code', 'ct_exec_code');