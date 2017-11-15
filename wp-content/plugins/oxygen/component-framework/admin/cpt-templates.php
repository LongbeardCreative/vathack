<?php

/**
 * Register "Templates" Custom Post Type
 * 
 * @since 0.2.0
 */

add_action( 'init', 'ct_add_templates_cpt' );

function ct_add_templates_cpt() {
	$labels = array(
		'name'               => _x( 'Templates', 'post type general name', 'component-theme' ),
		'singular_name'      => _x( 'Template', 'post type singular name', 'component-theme' ),
		'menu_name'          => _x( 'Templates', 'admin menu', 'component-theme' ),
		'name_admin_bar'     => _x( 'Template', 'add new on admin bar', 'component-theme' ),
		'add_new'            => _x( 'Add New', 'template', 'component-theme' ),
		'add_new_item'       => __( 'Add New Template', 'component-theme' ),
		'new_item'           => __( 'New Template', 'component-theme' ),
		'edit_item'          => __( 'Edit Template', 'component-theme' ),
		'view_item'          => __( 'View Template', 'component-theme' ),
		'all_items'          => __( 'Templates', 'component-theme' ),
		'search_items'       => __( 'Search Templates', 'component-theme' ),
		'parent_item_colon'  => __( 'Parent Templates:', 'component-theme' ),
		'not_found'          => __( 'No templates found.', 'component-theme' ),
		'not_found_in_trash' => __( 'No templates found in Trash.', 'component-theme' )
	);

	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => true,
		'has_archive'		 => true,
		'show_ui'            => true,
		'show_in_menu'       => 'ct_dashboard_page',
		'query_var'          => true,
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => null,
		'supports'           => array( 'title' )
	);

	register_post_type( 'ct_template', $args );

	// flush rewrite rules if needed
	$flag = get_option("oxygen_rewrite_rules_updated");
	if ($flag !== "1") {
		flush_rewrite_rules();
		update_option("oxygen_rewrite_rules_updated", "1");
	}
}


/**
 * Remove all unnecessary UI elements on Template edit page
 * 
 * @since 0.2.0
 */

add_filter( 'get_sample_permalink_html', 	'ct_template_remove_permalink' );
//add_filter( 'pre_get_shortlink', 			'ct_template_remove_shortlink', 10, 2 );

function ct_template_remove_permalink( $return ) {
	global $post;
    return 'ct_template' === get_post_type( $post->ID ) ? '' : $return;
}

function ct_template_remove_shortlink( $false, $post_id ) {
	global $post;
    return 'ct_template' === get_post_type( $post_id ) ? '' : $false;
}


/**
 * Hide 'ct_template' from being viewed on frontend
 * 
 * @since 0.2.0
 */

add_action( 'template_redirect', 'ct_check_templates_post');

function ct_check_templates_post() {
	global $post;

	if(!isset($post) || !isset($post->ID))
		return;

	$post_type = get_post_type( $post->ID );

	if ( $post_type == 'ct_template' && !defined("SHOW_CT_BUILDER") 
		&& stripslashes($_REQUEST['action']) != 'ct_exec_code' 
		&& stripslashes($_REQUEST['action']) != 'ct_render_shortcode' ) {
		wp_redirect( get_edit_post_link( $post->ID, "" ) );
	}
}


/**
 * Adds a box to the main column on Templates screen
 * 
 * @since 0.2.0
 */

add_action( 'add_meta_boxes', 'ct_template_meta_box' );
add_action( 'save_post', 'ct_template_save_meta_box' );

function ct_template_meta_box() {

	add_meta_box(
		'ct_template_options',
		__( 'Template Options', 'component-theme' ),
		'ct_template_meta_box_callback',
		'ct_template',
		'normal',
		'high'
	);
}


/**
 * Output the meta box content
 * 
 * @since 0.2.0
 */

function ct_template_meta_box_callback( $post ) {

	// Add a nonce field so we can check for it later.
	wp_nonce_field( 'ct_template_meta_box', 'ct_template_meta_box_nonce' );

	// template type
	$template_type = get_post_meta( $post->ID, 'ct_template_type', true );


	/**
	 * Archive View
	 * 
	 */
	
	// post types
	$template_archive_post_types_all = get_post_meta( $post->ID, 'ct_template_archive_post_types_all', true );
	$template_archive_post_types 	 = get_post_meta( $post->ID, 'ct_template_archive_post_types', true );
	if ( !is_array( $template_archive_post_types ) ) {
		$template_archive_post_types = array();
	}
	
	// categories
	$template_categories_all = get_post_meta( $post->ID, 'ct_template_categories_all', true );
	$template_categories 	 = get_post_meta( $post->ID, 'ct_template_categories', true );
	if ( !$template_categories ) {
		$template_categories = array();
	}
	
	// tags
	$template_tags_all = get_post_meta( $post->ID, 'ct_template_tags_all', true );
	$template_tags 	   = get_post_meta( $post->ID, 'ct_template_tags', true );
	if ( !$template_tags ) {
		$template_tags = array();
	}

	// custom taxonomies
	$template_custom_taxonomies_all = get_post_meta( $post->ID, 'ct_template_custom_taxonomies_all', true );
	$template_custom_taxonomies 	= get_post_meta( $post->ID, 'ct_template_custom_taxonomies', true );
	if ( !$template_custom_taxonomies ) {
		$template_custom_taxonomies = array();
	}

	// authors archives
	$template_authors_archives_all = get_post_meta( $post->ID, 'ct_template_authors_archives_all', true );
	$template_authors_archives 	   = get_post_meta( $post->ID, 'ct_template_authors_archives', true );
	if ( !$template_authors_archives ) {
		$template_authors_archives = array();
	}
	
	// index
	$template_index 		= get_post_meta( $post->ID, 'ct_template_index', true );

	// front page
	$template_front_page 	= get_post_meta( $post->ID, 'ct_template_front_page', true );

	// blog posts
	$template_blog_posts 	= get_post_meta( $post->ID, 'ct_template_blog_posts', true );

	// date archive
	$template_date_archive 	= get_post_meta( $post->ID, 'ct_template_date_archive', true );

	// search result
	$template_search_page 	= get_post_meta( $post->ID, 'ct_template_search_page', true );

	// 404 page
	$template_404_page 		= get_post_meta( $post->ID, 'ct_template_404_page', true );
	
	
	/**
	 * Single View
	 * 
	 */
	
	$template_single_all 	= get_post_meta( $post->ID, 'ct_template_single_all', true );

	// post types
	$template_post_types 	= get_post_meta( $post->ID, 'ct_template_post_types', true );

	if ( !is_array( $template_post_types ) ) {
		$template_post_types = array();
	}
	
	$template_exclude_ids 	= get_post_meta( $post->ID, 'ct_template_exclude_ids', true );

	// ids
	$template_include_ids 	= get_post_meta( $post->ID, 'ct_template_include_ids', true );

	// taxonomies
	$use_taxonomies 		= get_post_meta( $post->ID, 'ct_use_template_taxonomies', true );
	$template_taxonomies 	= get_post_meta( $post->ID, 'ct_template_taxonomies', true );

	if ( !$template_taxonomies ) {
		$template_taxonomies = array(
									'names' 	=> array(""),
									'values' 	=> array("")
								);
	}

	// header/footer view
	$template_header_footer_name = get_post_meta( $post->ID, 'ct_template_header_footer_name', true );

	require('views/cpt-templates-metabox.php');
}


/**
 * Save Template meta
 * 
 * @since 0.2.0
 * @author Ilya K.
 */

function ct_template_save_meta_box( $post_id ) {

	// Check if our nonce is set
	if ( ! isset( $_POST['ct_template_meta_box_nonce'] ) ) {
		return;
	}

	// Verify that the nonce is valid
	if ( ! wp_verify_nonce( $_POST['ct_template_meta_box_nonce'], 'ct_template_meta_box' ) ) {
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
	
	// template type
	$template_type = sanitize_text_field($_POST['ct_template_type']);

	/**
	 * Archive View
	 */
	
	// post types
	$template_archive_post_types 		= is_array($_POST['ct_template_archive_post_types']) ? array_map('sanitize_text_field', $_POST['ct_template_archive_post_types']): array();
	$template_archive_post_types_all 	= sanitize_text_field($_POST['ct_template_archive_post_types_all']);
//$sanitizedValues = array_filter($_POST['ct_template_archive_post_types'], 'ctype_digit');
	// categories
	$template_categories_all 			= sanitize_text_field($_POST['ct_template_categories_all']);
	$template_categories 				= is_array($_POST['ct_template_categories']) ? array_map('sanitize_text_field', $_POST['ct_template_categories']): array();
	
	// tags
	$template_tags_all 					= sanitize_text_field($_POST['ct_template_tags_all']);
	$template_tags 						= is_array($_POST['ct_template_tags']) ? array_map('sanitize_text_field', $_POST['ct_template_tags']): array();
	
	// custom taxonomies
	$template_custom_taxonomies_all 	= sanitize_text_field($_POST['ct_template_custom_taxonomies_all']);
	$template_custom_taxonomies 		= is_array($_POST['ct_template_custom_taxonomies']) ? array_map('sanitize_text_field', $_POST['ct_template_custom_taxonomies']): array();

	// authors archives
	$template_authors_archives_all 		= sanitize_text_field($_POST['ct_template_authors_archives_all']);
	$template_authors_archives 			= is_array($_POST['ct_template_authors_archives']) ? array_map('sanitize_text_field', $_POST['ct_template_authors_archives']): array();

	// index
	$template_index 					= sanitize_text_field($_POST['ct_template_index']);

	// front page
	$template_front_page 				= sanitize_text_field($_POST['ct_template_front_page']);

	// blog posts
	$template_blog_posts 				= sanitize_text_field($_POST['ct_template_blog_posts']);

	// date archive
	$template_date_archive 				= sanitize_text_field($_POST['ct_template_date_archive']);

	// search result
	$template_search_page 				= sanitize_text_field($_POST['ct_template_search_page']);

	// 404 page
	$template_404_page 					= sanitize_text_field($_POST['ct_template_404_page']);


	/**
	 * Single View
	 */
	
	$template_single_all 	= $_POST['ct_template_single_all'];
	
	// post types
	$template_post_types 	= $_POST['ct_template_post_types'];
	$template_exclude_ids 	= sanitize_text_field( $_POST['ct_template_exclude_ids'] );

	// ids
	$template_include_ids 	= sanitize_text_field( $_POST['ct_template_include_ids'] );

	// taxonomies
	$use_taxonomies 		= $_POST['ct_use_template_taxonomies'];
	$taxonomy_names 		= $_POST['ct_taxonomy_names'];
	$taxonomy_values 		= $_POST['ct_taxonomy_values'];

	unset($taxonomy_names[0]);
	unset($taxonomy_values[0]);
	
	$template_taxonomies 	= array(
									'names' 	=> $taxonomy_names,
									'values' 	=> $taxonomy_values );

	// header/footer
	$template_header_footer_name = sanitize_text_field( $_POST['ct_template_header_footer_name'] );
	
	/**
	 * Update Post Meta
	 */
	 
	// template type
	update_post_meta( $post_id, 'ct_template_type', $template_type );

	/**
	 * Archive View
	 */
	
	// post types
	update_post_meta( $post_id, 'ct_template_archive_post_types', $template_archive_post_types );
	if ( $template_archive_post_types_all ) {
		update_post_meta( $post_id, 'ct_template_archive_post_types_all', $template_archive_post_types_all );
	}
	else {
		update_post_meta( $post_id, 'ct_template_archive_post_types_all', "");
	}

	// categories
	update_post_meta( $post_id, 'ct_template_categories', $template_categories );
	if ( $template_categories_all ) {
		update_post_meta( $post_id, 'ct_template_categories_all', $template_categories_all );
	}
	else {
		update_post_meta( $post_id, 'ct_template_categories_all', "");
	}
	
	// tags
	update_post_meta( $post_id, 'ct_template_tags', $template_tags );
	if ( $template_tags_all ) {
		update_post_meta( $post_id, 'ct_template_tags_all', $template_tags_all );
	}
	else {
		update_post_meta( $post_id, 'ct_template_tags_all', "");
	}
	
	// custom taxonomy
	update_post_meta( $post_id, 'ct_template_custom_taxonomies', $template_custom_taxonomies );
	if ( $template_custom_taxonomies_all ) {
		update_post_meta( $post_id, 'ct_template_custom_taxonomies_all', $template_custom_taxonomies_all );
	}
	else {
		update_post_meta( $post_id, 'ct_template_custom_taxonomies_all', "" );
	}

	// authors archives
	update_post_meta( $post_id, 'ct_template_authors_archives', $template_authors_archives );
	if ( $template_authors_archives_all ) {
		update_post_meta( $post_id, 'ct_template_authors_archives_all', $template_authors_archives_all );
	}
	else {
		update_post_meta( $post_id, 'ct_template_authors_archives_all', "" );
	}

	// index
	update_post_meta( $post_id, 'ct_template_index', $template_index );

	// front page
	update_post_meta( $post_id, 'ct_template_front_page', $template_front_page );

	// blog posts
	update_post_meta( $post_id, 'ct_template_blog_posts', $template_blog_posts );

	// date archive
	update_post_meta( $post_id, 'ct_template_date_archive', $template_date_archive );

	// search result
	update_post_meta( $post_id, 'ct_template_search_page', $template_search_page);

	// 404 page
	update_post_meta( $post_id, 'ct_template_404_page', $template_404_page);

	 
	/**
	 * Single View
	 */
	
	update_post_meta( $post_id, 'ct_template_single_all', 			$template_single_all );
	
	// post types
	update_post_meta( $post_id, 'ct_template_post_types', 			$template_post_types );
	update_post_meta( $post_id, 'ct_template_exclude_ids', 			$template_exclude_ids );
	update_post_meta( $post_id, 'ct_template_include_ids', 			$template_include_ids );
	update_post_meta( $post_id, 'ct_use_template_taxonomies', 		$use_taxonomies );
	update_post_meta( $post_id, 'ct_template_taxonomies', 			$template_taxonomies );

	// header/footer
	update_post_meta( $post_id, 'ct_template_header_footer_name', 	$template_header_footer_name );	
}


/**
 * Add custom columns to Views CPT table
 * 
 * @since 0.2.3
 */

function ct_custom_views_columns($columns) {

	// save date and uset value to use later
	$date = $columns['date'];
	unset($columns['date']);
    
    // add type
    $columns['ct_template_type'] = __( 'Template Type', 'component-theme' );

    // add date back
    $columns['date'] = $date;

    return $columns;
}

function ct_custom_view_column( $column, $post_id ) {
    switch ( $column ) {

        case 'ct_template_type' :
            
            $type = get_post_meta( $post_id , 'ct_template_type' , true );

           	if ( $type == "reusable_part") {
           		_e( 'Re-usable part', 'component-theme' );
           	}

           	if ( $type == "archive") {
           		_e( 'Archive', 'component-theme' );
           	}

           	if ( $type == "single_post") {
           		_e( 'Single post', 'component-theme' );
           	}

           	if ( $type == "header_footer") {
           		_e( 'Header/Footer', 'component-theme' );
           	}

           	if ( $type == "other_template") {
           		_e( 'Other Template', 'component-theme' );
           	}

            break;
    }
}

add_filter( 'manage_ct_template_posts_columns', 'ct_custom_views_columns' );
add_action( 'manage_ct_template_posts_custom_column' , 'ct_custom_view_column', 10, 2 );


/**
 * Add view meta box for all CPTs
 * 
 * @since 0.4.0
 * @author Ilya K.
 */

function ct_oxygen_meta_box() {

	$post_types 	= get_post_types( '', 'objects' ); 
	$exclude_types 	= array( "nav_menu_item", "revision" );

	foreach ( $post_types as $post_type ) {

		if (in_array($post_type->name, $exclude_types)){
			continue;
		}

		add_meta_box(
			'ct_views_cpt',
			__( 'Oxygen', 'component-theme' ),
			'ct_view_meta_box_callback',
			$post_type->name,
			'advanced',
			'high'
		);
	}
}
add_action( 'add_meta_boxes', 'ct_oxygen_meta_box' );


/**
 * Output views to meta box content
 * 
 * @since 0.4.0
 * @author Ilya K.
 */

function ct_view_meta_box_callback( $post ) {
	global $wpdb;
	// Add a nonce field so we can check for it later
	wp_nonce_field( 'ct_view_meta_box', 'ct_view_meta_box_nonce' );

	$screen = get_current_screen();

	if ($screen->post_type != "ct_template") {

		// generic view
		if ( get_option( 'page_for_posts' ) == $post->ID || get_option( 'page_on_front' ) == $post->ID ) {
			$generic_view = ct_get_archives_template( $post->ID );
		}
		else {
			$generic_view = ct_get_posts_template( $post->ID );
		}

		// custom view
		$custom_view = get_post_meta( $post->ID, 'ct_builder_shortcodes', true );


		$ct_render_post_using = get_post_meta( $post->ID, 'ct_render_post_using', true );

		$ct_other_template = get_post_meta( $post->ID, 'ct_other_template', true );
		
		if(!$custom_view && (!$ct_render_post_using || $ct_render_post_using == 'custom_template') && !$generic_view) {
			$custom_view = ' ';	
			$ct_render_post_using = 'custom_template';
			update_post_meta( $post->ID, 'ct_render_post_using', 'custom_template' );
		}

		/*if(!$custom_view && !$ct_render_post_using) { // if it is a new post/page
			// if no default template applies
			update_post_meta( $post->ID, 'ct_render_post_using', 'other_template' );
		}*/
		
		?>
			<div class="ct-admin-tooltip-top">
				<div>
					<?php echo __("This page will be rendered with...", "component-theme"); ?>
				</div>
			</div>
		
		<ul>
			<li>
				<label>

					<input type="radio" value="custom_template" <?php echo ($custom_view && $ct_render_post_using != 'other_template')?'checked':'';?> name="ct_render_post_using" class="ct_render_post_using" /> <?php _e("Custom Design, Just For This Page", "component-theme")?>
				</label>
				<div class="ct_template_option_panel" <?php echo (!$custom_view || $ct_render_post_using == 'other_template')?'style="display:none;"':'';?>>

					<?php
					if ( $custom_view ) { ?>
						
							<a href="<?php echo esc_url(ct_get_post_builder_link( $post->ID )); ?>" class="button button-primary" style="vertical-align:middle">
								<?php _e("Edit in Visual Editor", "component-theme")?>
							</a> 


							<a href='#' id="ct_delete_custom_view" style="vertical-align:middle; margin-left: 8px;"><?php _e("Delete", "component-theme");?></a>

						
					<?php }
					else {
					?>
						
						<input name="ct_create_custom_view" type="submit" class="button button-primary" id="ct_create_custom_view" value="<?php _e("Create Custom Template", "component-theme")?>">
						
					<?php }
					?>
				</div>
			</li>
			<li>
				<label>

					<input type="radio" value="other_template" <?php echo ($ct_render_post_using == 'other_template' || !$custom_view)?'checked':'';?> name="ct_render_post_using" class="ct_render_post_using" /> <?php _e("Template", "component-theme");?>
				</label>
				<div class="ct_template_option_panel" <?php echo (($ct_render_post_using == false && $custom_view) || ($ct_render_post_using == 'custom_template' && $custom_view))?'style="display:none;"':'';?> >

					<?php
					// wp_query to get all the post type 

						$templates = $wpdb->get_results(
						    "SELECT id, post_title
						    FROM $wpdb->posts as post
						    WHERE post_type = 'ct_template'
						    AND post.post_status IN ('publish')"


						);
					
						if($generic_view || sizeof($templates) > 0) {
					?>
					
						<label style="margin-top: 10px;"><?php _e("Choose Template", "component-theme");?>
							
							<select name="ct_other_template">
								<?php
									$selected_template = false;

									if($generic_view) {
									?>
									<option value="0" <?php echo intval($ct_other_template) == $generic_view->id?'selected':'';?>>Default (<?php echo $generic_view->post_title; ?>)</option>
									<?php
									}
									else {
										?>
									<option value="0"></option>
										<?php
									}
									foreach($templates as $template) {
										if(intval($ct_other_template) == $template->id) {
											$selected_template = $template;
										}

										// do not display re-usables
										$ct_template_type = get_post_meta($template->id, 'ct_template_type', true);

										if(!($ct_template_type && $ct_template_type =='reusable_part')) {
											?>
											<option value="<?php echo $template->id; ?>" <?php echo intval($ct_other_template) == $template->id?'selected':'';?>><?php echo $template->post_title; ?></option>
											<?php
										}
									}
									
								?>
							</select>
							<?php
								if(!$selected_template && !empty($generic_view)) {
							?>
							<div class="ct-admin-tooltip">
								<span class="dashicons dashicons-info"></span>
								<div>
									<?php printf(__("The \"%s\" template will be applied to this page unless you override it by choosing another template, or ", "component-theme"), sanitize_text_field( $generic_view->post_title ) ); ?>
									 
									<a href='<?php echo get_edit_post_link( $generic_view->ID );?>'>
										<?php printf(__("edit the settings for the \"%s\" template", "component-theme"), sanitize_text_field( $generic_view->post_title ) ); ?>
									</a>
								</div>
							</div>
							<?php 
								}
							?>
						</label>
					
					<?php 
						}

					if(!empty($generic_view)) { 
						$def_temp_id = $generic_view->ID;
						$def_temp_title = $generic_view->post_title;
					}
					else {
						$def_temp_id = $selected_template->id;
						$def_temp_title = $selected_template->post_title;
					}

					if($generic_view || $selected_template) {
					?>
					<br />
					<a href="<?php echo esc_url(ct_get_post_builder_link( $def_temp_id )); ?>" class="button button-primary" style="margin-top: 8px;">
						<?php printf(__("Edit \"%s\" in Visual Editor", "component-theme"), sanitize_text_field( $def_temp_title ) ); ?>
					</a>
					<?php } 
						$existing_id = false;
						$existing_title = false;

						if(($generic_view && !$ct_other_template) || $selected_template) {
								if($generic_view && !$ct_other_template) {
									$existing_id = $generic_view->ID;
									$existing_title = $generic_view->post_title;
								} 
								elseif($selected_template) {
									$existing_id = $selected_template->id;
									$existing_title = $selected_template->post_title;
								}
									// check if the template uses the ct_inner_content module
									
									$shortcodes = get_post_meta( $existing_id, 'ct_builder_shortcodes', true );
									
									if(strpos($shortcodes, '[ct_inner_content') !== false) {
										echo "<p style='margin-top:20px; margin-bottom:4px; font-size: 16px;'>".__("Inner Content", "component-theme")."</p>";
										//printf(__("The \"%s\" template provides the scope to edit inner content of the page ", "component-theme"), sanitize_text_field( $existing_title ) ); 
									
									$ct_use_inner_content = get_post_meta($post->ID, 'ct_use_inner_content', true);
								?>
								
								<ul>
									<li>
										<label>
											<input type="radio" value="content" name="ct_use_inner_content" class="ct_use_inner_content" <?php echo (!$ct_use_inner_content || $ct_use_inner_content == 'content') ? 'checked':'';?> /> <?php _e("Use standard WordPress post content field", "component-theme")?>
										</label>
									</li>
									<li>
										<label>
											<input type="radio" value="layout" name="ct_use_inner_content" class="ct_use_inner_content" <?php echo ( $ct_use_inner_content && $ct_use_inner_content == 'layout') ? 'checked':'';?> /> <?php _e("Design inner content with Oxygen", "component-theme")?>
										</label>

										<a id="ct_edit_inner_content" style="margin-top:10px; margin-bottom:10px; <?php echo ( !$ct_use_inner_content || $ct_use_inner_content == 'content') ? 'display:none':'';?>" href='#' class="button button-primary ct-user-inner-content-layout">
											<?php _e("Edit the inner content for this page", "component-theme"); ?>
										</a>
									</li>
								</ul>
								
								<?php 
									}
								/*
								?>
								<a href="#" id="ct_create_custom_view_from"> <?php _e("Create Custom Design From ", "component-theme"); echo "'".sanitize_text_field( $existing_title )."'";?></a>
							<?php
							*/
						}
						else {
							?>
								<p><span class="dashicons dashicons-info"></span>
									You can create templates and mass apply them to content matching any criteria from Oxygen-><a href='<?php echo admin_url('edit.php?post_type=ct_template');?>'>Templates</a>
								</p>

							<?php
						}
					?>
					
				</div>
			</li>
			<li>
				<hr style="margin-top: 30px;" />
				<?php
					wp_nonce_field( 'ct_shortcode_meta_box', 'ct_shortcode_meta_box_nonce' );
				?>
				<p>
					<span id="ct-toggle-shortcodes"><?php _e( "Page Shortcodes", "component-theme" ); ?></span>
				</p>
				<div id="ct-builder-shortcodes" style="display:none">
					<textarea class="widefat" rows="8" name="ct_builder_shortcodes" id ="ct_builder_shortcodes"><?php echo $custom_view; ?></textarea>
				</div>
			</li>
		</ul>

		<?php
	}
	// Button only for "Views"
	else { ?>

		<p><a href="<?php echo esc_url(ct_get_post_builder_link( $post->ID )); ?>" class="button button-primary">
			<?php printf( __("Edit '%s' in Visual Editor", "component-theme"), get_the_title() ); ?>
		</a></p>


		<?php
			/**
			 * Builder shortcodes
			 */
		
			// Add a nonce field so we can check for it later.
			wp_nonce_field( 'ct_shortcode_meta_box', 'ct_shortcode_meta_box_nonce' );
			$shortcodes = get_post_meta( $post->ID, 'ct_builder_shortcodes', true );
		?>
		<p>
			<span id="ct-toggle-shortcodes"><?php _e( "Page Shortcodes", "component-theme" ); ?></span>
		</p>
		<div id="ct-builder-shortcodes" style="display:none">
			<textarea class="widefat" rows="8" name="ct_builder_shortcodes" id="ct_builder_shortcodes"><?php echo htmlentities( $shortcodes ); ?></textarea>
		</div>

	<?php }

}


/**
 * Output views to meta box content
 * 
 * @since 0.4.0
 * @author Ilya K.
 */

function ct_view_save_meta_box( $post_id ) {
	
	// Check if our nonce is set
	if ( ! isset( $_POST['ct_view_meta_box_nonce'] ) ) {
		return;
	}

	// Verify that the nonce is valid
	if ( ! wp_verify_nonce( $_POST['ct_view_meta_box_nonce'], 'ct_view_meta_box' ) ) {
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

	if (isset($_POST['ct_use_inner_content'])) {
		$ct_use_inner_content = sanitize_text_field($_POST['ct_use_inner_content']);

		update_post_meta( $post_id, 'ct_use_inner_content', $ct_use_inner_content);
	}

	if (isset($_POST['ct_render_post_using'])) {
		$ct_render_post_using = sanitize_text_field($_POST['ct_render_post_using']);

		update_post_meta( $post_id, 'ct_render_post_using', $ct_render_post_using);
		
		if($ct_render_post_using == 'other_template') {

			if(isset($_POST['ct_other_template'])) {
				
				$ct_other_template = sanitize_text_field($_POST['ct_other_template']);
				if(is_numeric($ct_other_template)) {
					if(is_numeric($ct_other_template) > 0)
						update_post_meta( $post_id, 'ct_other_template', $ct_other_template);
					else
						delete_post_meta(  $post_id, 'ct_other_template' );
				}
			}
		} else {
			delete_post_meta(  $post_id, 'ct_other_template' );
		}
	}

	/**
	 * Delete custom view (clear the shortocdes meta)
	 */
	
	/*if ( isset( $_POST["ct_delete_custom_view"] ) ) {

		unset( $_POST['ct_builder_shortcodes'] );
		update_post_meta( $post_id, 'ct_builder_shortcodes', "");
	};*/

	/**
	 * Redirect to builder to edit inner content
	 */
	if (isset($_POST["ct_redirect_inner_content"]) && sanitize_text_field($_POST["ct_redirect_inner_content"]) == "true") {
		
		/*$other_template = intval(sanitize_text_field($_POST["ct_other_template"]));

		if($other_template > 0) {
			$shortcodes = get_post_meta( $other_template, "ct_builder_shortcodes", true );
		}
		else {
			$template 	= ct_get_posts_template( $post_id );
			$shortcodes = get_post_meta( $template->ID, "ct_builder_shortcodes", true );
		}

		// if the shortcodes contain a ct_inner_content element, remove it
		$shortcodes = preg_replace("/\[ct_inner_content[^\]]*\]\[\/ct_inner_content\]/i", '', $shortcodes);

		// set post shortcodes to view shortcodes
		update_post_meta( $post_id, 'ct_builder_shortcodes', esc_sql($shortcodes));

		// reset the page settings to use custom view
		update_post_meta( $post_id, 'ct_render_post_using', 'custom_template' );
		delete_post_meta(  $post_id, 'ct_other_template' );
		*/
		// redirect to builder
		wp_redirect( ct_get_post_builder_link( $post_id ).'&ct_inner=true' );
		exit;
	}

	/**
	 * Redirect to builder to create a view
	 */
	if (isset($_POST["ct_custom_view_on_create_copy"]) && sanitize_text_field($_POST["ct_custom_view_on_create_copy"]) == "true") {
		
		$other_template = intval(sanitize_text_field($_POST["ct_other_template"]));

		if($other_template > 0) {
			$shortcodes = get_post_meta( $other_template, "ct_builder_shortcodes", true );
		}
		else {
			$template 	= ct_get_posts_template( $post_id );
			$shortcodes = get_post_meta( $template->ID, "ct_builder_shortcodes", true );
		}

		// if the shortcodes contain a ct_inner_content element, remove it
		$shortcodes = preg_replace("/\[ct_inner_content[^\]]*\]\[\/ct_inner_content\]/i", '', $shortcodes);

		// set post shortcodes to view shortcodes
		update_post_meta( $post_id, 'ct_builder_shortcodes', esc_sql($shortcodes));

		// reset the page settings to use custom view
		update_post_meta( $post_id, 'ct_render_post_using', 'custom_template' );
		delete_post_meta(  $post_id, 'ct_other_template' );

		// redirect to builder
		wp_redirect( ct_get_post_builder_link( $post_id ) );
		exit;
	}

	if ( isset( $_POST["ct_create_custom_view"] ) ) {

		// redirect to builder
		wp_redirect( ct_get_post_builder_link( $post_id ) );
		exit;
	};
}
add_action( 'save_post', 'ct_view_save_meta_box' );


/**
 * Add metabox for ct_template post type where user can set view order
 * 
 * @since 0.4.0
 * @author Ilya K.
 */

function ct_view_order_meta_box() {

	add_meta_box(
		'ct_view_order',
		__( 'Template Order', 'component-theme' ),
		'ct_view_order_meta_box_callback',
		'ct_template',
		'side'
	);
}
add_action( 'add_meta_boxes', 'ct_view_order_meta_box' );
add_action( 'save_post', 'ct_view_order_save_meta_box' );


/**
 * Output views to meta box content
 * 
 * @since 0.4.0
 * @author Ilya K.
 */

function ct_view_order_meta_box_callback( $post ) {

	// Add a nonce field so we can check for it later
	wp_nonce_field( 'ct_view_order_meta_box', 'ct_view_order_meta_box_nonce' );

	$order = get_post_meta( $post->ID, 'ct_template_order', true );

	_e("Order ", "component-theme");

	?>

	<input type="text" name="ct_template_order" value="<?php echo esc_html($order); ?>">
	<p class="description"><?php _e("Templates with highest order has a priority when multiple templates applies."); ?></p>
	<?php
}


/**
 * Output views to meta box content
 * 
 * @since 0.4.0
 * @author Ilya K.
 */

function ct_view_order_save_meta_box( $post_id ) {

	// Check if our nonce is set
	if ( ! isset( $_POST['ct_view_order_meta_box_nonce'] ) ) {
		return;
	}

	// Verify that the nonce is valid
	if ( ! wp_verify_nonce( $_POST['ct_view_order_meta_box_nonce'], 'ct_view_order_meta_box' ) ) {
		return;
	}

	// If this is an autosave, our form has not been submitted, so we don't want to do anything
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Check the user's permissions
	if ( !current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( isset( $_POST["ct_template_order"] ) ) {
		update_post_meta( $post_id, 'ct_template_order', intval($_POST["ct_template_order"]) );
	};
}


/**
 * Add a select with all view types to filter
 * 
 * @since 0.4.0
 * @author Ilya K.
 */

add_action( 'restrict_manage_posts', 'ct_views_filter_dropdown' );
function ct_views_filter_dropdown() {
	
	global $typenow;
	
	$taxonomy = $typenow.'_type';
	
	if( $typenow == "ct_template" ) {
	
		$types = array(

           	"archive" =>
           		__( 'Archive', 'component-theme' ),

           	"single_post" =>
           		__( 'Single post', 'component-theme' ),
           	
           	"reusable_part" =>
           		__( 'Re-usable part', 'component-theme' ),
           	
           	"header_footer" =>
           		__( 'Header/Footer', 'component-theme' ),

           	"other_template" =>
           		__( 'Other Template', 'component-theme' ),

		);
        
        echo "<select name=\"ct_template_type\">";
        echo "<option value=\"\">All templates types</option>";
        
        foreach( $types as $name => $title ) {

            $selected = $name == isset($_GET['ct_template_type']) ? ' selected ' : '';
            echo "<option $selected value=\"$name\">" . $title .  "</option>";
        }

        echo "</select>";
	}
}


/**
 * Filter views based on type selected by user
 * 
 * @since 0.4.0
 * @author Ilya K.
 */

function ct_views_filter( $query )
{
    global $typenow;
    global $pagenow;

    if( $pagenow == 'edit.php' && $typenow == 'ct_template' && isset($_GET['ct_template_type']) && $_GET['ct_template_type'] )
    {
        $query->query_vars[ 'meta_key' ] = 'ct_template_type';
        $query->query_vars[ 'meta_value' ] = sanitize_text_field($_GET['ct_template_type']);
    }
}
add_filter( 'parse_query', 'ct_views_filter' );


/**
 * Add order column to views list table
 * 
 * @since 0.4.0
 * @author Ilya K.
 */

function ct_views_order_column( $columns ) {

	// save date and uset value to use later
	$date = $columns['date'];
	unset($columns['date']);
    
    // add type
    $columns['ct_view_order'] = __( 'Order', 'component-theme' );

    // add date back
    $columns['date'] = $date;

    return $columns;
}
add_filter( 'manage_ct_template_posts_columns', 'ct_views_order_column' );


/**
 * Add order value to views order column
 * 
 * @since 0.4.0
 * @author Ilya K.
 */

function ct_views_order_value( $column, $post_id ) {
    switch ( $column ) {

        case 'ct_view_order' :
        	$template_order = get_post_meta( $post_id, 'ct_template_order', true );
            echo is_int( $template_order ) ? $template_order : 0;
        break;
    }
}
add_action( 'manage_ct_template_posts_custom_column' , 'ct_views_order_value', 10, 2 );


/**
 * Make view order column sortable
 * 
 * @since 0.4.0
 * @author Ilya K.
 */

function ct_views_order_sortable( $columns ) {
   
    $columns['ct_view_order'] = 'ct_view_order';

    return $columns;
}
add_filter( 'manage_edit-ct_template_sortable_columns', 'ct_views_order_sortable' );


/**
 * Sort views by order column
 * 
 * @since 0.4.0
 * @author Ilya K.
 */

function ct_views_order_sort( $query ) {
    
    if( ! is_admin() )
        return;
 
    $orderby = $query->get( 'orderby');
 
    if( 'ct_view_order' == $orderby ) {
        $query->set('meta_key','ct_template_order');
        $query->set('orderby','meta_value_num');
    }
}
add_action( 'pre_get_posts', 'ct_views_order_sort' );


/**
 * Add view post type column to views list table
 * 
 * @since 0.4.0
 * @author Ilya K.
 */

function ct_views_type_column( $columns ) {

	// save date and uset value to use later
	$date = $columns['date'];
	unset($columns['date']);
    
    // add type
    $columns['ct_post_type'] = __( 'Post Types', 'component-theme' );

    // add date back
    $columns['date'] = $date;

    return $columns;
}
add_filter( 'manage_ct_template_posts_columns', 'ct_views_type_column' );


/**
 * Output post types in post type column of the views list table
 * 
 * @since 0.4.0
 * @author Ilya K.
 */

function ct_views_type_value( $column, $post_id ) {
	$post_types = '';
    switch ( $column ) {

        case 'ct_post_type' :

        	$template_type = get_post_meta( $post_id, 'ct_template_type', true );
            
            if ( $template_type == "single_post" ) {
            	$post_types = get_post_meta( $post_id, 'ct_template_post_types', true );
            }

            if ( $template_type == "archive" ) {
            	$post_types = get_post_meta( $post_id, 'ct_template_archive_post_types', true );
            }

            if ( isset( $post_types ) && is_array( $post_types ) ) {
            	$post_types = implode(", ", $post_types);
            }

            echo $post_types;
        
        break;
    }
}
add_action( 'manage_ct_template_posts_custom_column' , 'ct_views_type_value', 10, 2 );


/**
 * Add view taxonomies to the list table
 * 
 * @since 0.4.0
 * @author Ilya K.
 */

function ct_views_taxonomies_column( $columns ) {

	// save date and uset value to use later
	$date = $columns['date'];
	unset($columns['date']);
    
    // add type
    $columns['ct_post_taxonomies'] = __( 'Taxonomies', 'component-theme' );

    // add date back
    $columns['date'] = $date;

    return $columns;
}
add_filter( 'manage_ct_template_posts_columns', 'ct_views_taxonomies_column' );


function ct_views_taxonomies_value( $column, $post_id ) {
    switch ( $column ) {

        case 'ct_post_taxonomies' :

        	$template_type = sanitize_text_field(get_post_meta( $post_id, 'ct_template_type', true ));

            if ( $template_type == "archive" ) {
            	
            	$categories 		= get_post_meta( $post_id, 'ct_template_categories', true );
            	$categories 		= is_array($categories) ? array_map('sanitize_text_field', $categories): array();


				$tags 				= get_post_meta( $post_id, 'ct_template_tags', true );
				$tags = is_array($tags) ? array_map('sanitize_text_field', $tags): array();

				$custom_taxonomies 	= get_post_meta( $post_id, 'ct_template_custom_taxonomies', true );
				$custom_taxonomies 	= is_array($custom_taxonomies) ? array_map('sanitize_text_field', $custom_taxonomies): array();

				$authors_archives 	= get_post_meta( $post_id, 'ct_template_authors_archives', true );
				$authors_archives 	= is_array($authors_archives) ? array_map('sanitize_text_field', $authors_archives): array();

				$categories_all 		= sanitize_text_field(get_post_meta( $post_id, 'ct_template_categories_all', true ));
				$tags_all 				= sanitize_text_field(get_post_meta( $post_id, 'ct_template_tags_all', true ));
				$custom_taxonomies_all 	= sanitize_text_field(get_post_meta( $post_id, 'ct_template_custom_taxonomies_all', true ));
				$authors_archives_all 	= sanitize_text_field(get_post_meta( $post_id, 'ct_template_authors_archives_all', true ));
            }

			if ( isset($categories_all) && $categories_all ) {
				_e("All Categories", "component-theme");
				echo "<br/>";
			}
			else
			if ( isset( $categories ) && is_array( $categories ) ) {

				foreach ( $categories as $id ) {
					$category = get_term_by( "id", $id, "category" ); 
					$category_names[] = $category->name;
				}

				if(is_array($category_names)) {
					_e("Categories: ", "component-theme");
					echo implode(", ", $category_names);
					echo "<br/>";
				}
				
			}

			if ( isset($tags_all) && $tags_all ) {
				_e("All Tags", "component-theme");
				echo "<br/>";
			}
			else
			if ( isset( $tags ) && is_array( $tags ) ) {
				
				foreach ( $tags as $id ) {
					$tag = get_term_by( "id", $id, "post_tag" );
					$tag_names[] = $tag->name;
				}
				if(is_array($tag_names)) {
					_e("Tags: ", "component-theme");
					echo implode(", ", $tag_names);
					echo "<br/>";
				}
			}

            if ( isset($custom_taxonomies_all) && $custom_taxonomies_all ) {
            	_e("All Custom Taxonomies", "component-theme");
            	echo "<br/>";
            }
			else
			if ( isset( $custom_taxonomies ) && is_array( $custom_taxonomies ) ) {
				
				$taxonomy_names = array();
				$all_terms = array();     	
				foreach ( $custom_taxonomies as $id ) {

					//var_dump(strpos( $id, "all_"));

					// all certain taxonomy terms
					if ( strpos( $id, "all_") === 0 ) {
						_e("All ", "component-theme");
						echo str_replace("all_", "", $id)."<br/>";

						// save to exclude later
						$all_terms[] = str_replace("all_", "", $id);
					}
					// single term
					else {
						$term = get_term($id);
						$taxonomy_names[$term->taxonomy][] = $term->name;
					}
				}

				foreach ( $taxonomy_names as $name => $temrs ) {
					
					if (in_array($name, $all_terms))
						continue;

					echo $name .": ". implode(", ", $temrs);
					echo "<br/>";
				}
			}

			if ( isset($authors_archives_all) && $authors_archives_all ) {
				_e("All Authors", "component-theme");
				echo "<br/>";
			}
			else
			if ( isset($authors_archives) && is_array( $authors_archives ) ) {
				
				foreach ( $authors_archives as $id ) {
					$author = get_user_by("id", $id);
					$author_names[] = $author->user_nicename;
				}

				if(is_array($author_names)) {
					_e("Authors: ", "component-theme");
					echo implode(", ", $author_names);
					echo "<br/>";
				}
			}
        
        break;
    }
}
add_action( 'manage_ct_template_posts_custom_column' , 'ct_views_taxonomies_value', 10, 2 );