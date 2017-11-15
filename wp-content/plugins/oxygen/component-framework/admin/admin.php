<?php 

/**
 * Add Frontend Builder button or Metabox after post title
 *
 * @since 0.1
 */

function ct_after_post_title() {
	
	// don't show for types other than 'post', 'page' or 'ct_template'
	//$type = get_post_type( $post->ID );
	
	global $post, $wp_meta_boxes;

	$type = get_post_type($post);
	
	if ( $type == "nav_menu_item" || $type == 'revision' ) {
		unset($wp_meta_boxes[get_post_type($post)]['advanced']);
		return;
	}

	// don't show for auto-draft posts
	$status = get_post_status( $post->ID );
	if ( $status == "auto-draft" ) {
		unset($wp_meta_boxes[get_post_type($post)]['advanced']);
		return;
	}
		
	// show builder shortcodes
	echo "<br/>";
	do_meta_boxes(get_current_screen(), 'advanced', $post);
	unset($wp_meta_boxes[get_post_type($post)]['advanced']);

	return;
}
add_action("edit_form_after_title", "ct_after_post_title");


/**
 * Get Frontend Builder post link by post ID
 *
 * @since 0.4.0
 * @author Ilya K.
 */

function ct_get_post_builder_link($post_id) {

	$link = get_permalink( $post_id );
	return add_query_arg( 'ct_builder', 'true', $link );
}


/**
 * Hide admin bar if frontend builder launched
 *
 * @since 0.1
 */

function ct_hide_admin_bar() {

    if ( defined("SHOW_CT_BUILDER") ) {
    	add_filter('show_admin_bar', '__return_false');
    }
}
add_action('init','ct_hide_admin_bar');


/**
 * Load scripts and styles for Component theme elements in WordPress dashboard
 *
 * @since 0.2.0
 */

function ct_enqueue_admin_scripts( $hook ) {

	// load css on all pages
	wp_enqueue_style ( 'ct-admin-style', CT_FW_URI . "/admin/admin.css" );
    
    // load specific scrpits only here 
    if ( 'post.php' != $hook && 'post-new.php' != $hook && 'edit.php' != $hook ) {
        return;
    }

    $screen = get_current_screen();

    // include only on Views screen
    if ( $screen->post_type == "ct_template" ) {
        wp_enqueue_script( 'select2', CT_FW_URI . "/vendor/select2/select2.full.min.js", array( 'jquery' ) );
    	wp_enqueue_style ( 'select2', CT_FW_URI . "/vendor/select2/select2.min.css" );
    }

    wp_enqueue_script( 'ct-admin-script', CT_FW_URI . "/admin/admin.js" );
}
add_action( 'admin_enqueue_scripts', 'ct_enqueue_admin_scripts' );


/**
 * Output shortcodes to meta box content
 * 
 * @since 0.4.0
 * @author Ilya K.
 */

function ct_shortcodes_save_meta_box( $post_id ) {
	
	// Check if our nonce is set
	if ( ! isset( $_POST['ct_shortcode_meta_box_nonce'] ) ) {
		return;
	}

	// Verify that the nonce is valid
	if ( ! wp_verify_nonce( $_POST['ct_shortcode_meta_box_nonce'], 'ct_shortcode_meta_box' ) ) {
		return;
	}

	// If this is an autosave, our form has not been submitted, so we don't want to do anything
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Check the user's permissions
	if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {
		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return;
		}
	} 
	else {
		if ( !current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
	}

	/* OK, it's safe for us to save the data now */

	// get shortcodes
	$shortcodes = trim($_POST['ct_builder_shortcodes']);
	 
	// template type
	update_post_meta( $post_id, 'ct_builder_shortcodes', $shortcodes );
}
add_action( 'save_post', 'ct_shortcodes_save_meta_box' );