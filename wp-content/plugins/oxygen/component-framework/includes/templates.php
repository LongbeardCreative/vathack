<?php 

/**
 * Get template applied to the post
 *
 * @return string [HTML] or bool false
 * @since 0.2.0
 */

function ct_template_output( $as_shortcodes = false ) {
	
	$shortcodes = ct_template_shortcodes();

	// return shortcodes
	if ( $shortcodes && $as_shortcodes ) {
		return $shortcodes;
	}

	// enqueue view/custom view styles
	global $ct_template_id;
	$ct_template_id = ( isset($template) && isset($template->ID) ) ? $template->ID : get_the_ID();
	
	/*if ( ! defined("SHOW_CT_BUILDER") ) {
		$shortcodes_time 	= get_post_meta( $ct_template_id, "oxygen_shortcodes_css_rendered_timestamp", true );
		$stylesheets_time 	= get_option( "oxygen_stylesheets_css_rendered_timestamp", true );

		//wp_enqueue_style("oxygen-styles", ct_get_current_url( 'xlink=css&tid='.$ct_template_id.'&shortcodes_time='.$shortcodes_time.'&stylesheets_time='.$stylesheets_time ), array('oxygen') );

		wp_enqueue_style("oxygen-styles", ct_get_current_url( 'xlink=css' ), array('oxygen') );
	}*/

	// return rendered HTML
	if ( $shortcodes ) {
		
		$content = do_shortcode( $shortcodes );
		
		if ( !isset( $_REQUEST['xlink'] ) && isset( $template ) ) {
			echo "<!-- Oxygen Template ID: " . $template->post_title . " (" . $template->ID . ") -->";
		}

		return $content;
	} 
	else {
		return false;
	}
}

/**
 * Look for post's template and start buffering content if found on frontend
 * 
 * @since 0.2.0
 */

function ct_templates_buffer_start() { 

	// only for frontend
	if ( defined("SHOW_CT_BUILDER") ) {
		return false;
	}

	global $template_content;

	// generate template output
	$template_content = ct_template_output();

	// support for elementor plugin
	/*if ( isset( $_REQUEST['elementor-preview'] ) ) {
		$template_content = ct_template_output();
	}*/

	if ( $template_content !== false ) {
		// all native post output go to buffer
		ob_start();
		
	} else {

		global $ct_replace_render_template;
		
		if(!isset($ct_replace_render_template) || 
			$ct_replace_render_template == get_single_template() ||
			$ct_replace_render_template == get_page_template() ||
			$ct_replace_render_template == get_index_template()) {
			// default content loop
			// Start the loop.
			while ( have_posts() ) : the_post();
			
			?>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<header>
						<?php the_title( '<h1>', '</h1>' ); ?>
					</header>

					<div>
						<?php
						the_content();

						wp_link_pages( array(
							'before'      => '<div><span>' . __( 'Pages:', 'component-theme' ) . '</span>',
							'after'       => '</div>',
							'link_before' => '<span>',
							'link_after'  => '</span>',
							'pagelink'    => '<span>' . __( 'Page', 'component-theme' ) . ' </span>%',
							'separator'   => '<span>, </span>',
						) );
						?>
					</div>

				</article>
				<?php
				// If comments are open or we have at least one comment, load up the comment template.
				if ( comments_open() || get_comments_number() ) {
					comments_template();
				}

				// End of the loop.
			endwhile;
		}
	}
}


/**
 * Stop buffering native content and output generated template content on frontend
 * 
 * @since 0.2.0
 */

function ct_templates_buffer_end() { 

	// only for frontend
	if ( defined("SHOW_CT_BUILDER") ) {
		return false;
	}

	global $template_content;

	if ( $template_content !== false ) {
		// clear buffer with native content
		ob_clean();

		// output generated template content
		echo $template_content;
	}
}

add_action('ct_builder_start', 	'ct_templates_buffer_start');
add_action('ct_builder_end', 	'ct_templates_buffer_end');


/**
 * Output template settings
 * 
 * @since 0.2.0
 */

function ct_template_builder_settings() { 

	// show only for templates
	if ( !defined("CT_TEMPLATE_EDIT") || CT_TEMPLATE_EDIT !== true ) {
		return false;
	}

	// show only for single posts
	if ( get_post_meta( get_the_ID(), 'ct_template_type', true ) == "single_post" ) : 

	?>
	<div class="ct-toolitem">
		<h3><?php _e("Previewing"); ?></h3>
		<div class="ct-selectbox ct-template-select ct-select-search-enabled">
			<ul class="ct-select">
				<li class="ct-selected">{{template.postData.post_title}}<span class="ct-icon ct-dropdown-icon"></span></li>
				<li class="ct-searchbar">
					<div class="ct-textbox">
						<input ng-model="postsFilter" type="text" value="" placeholder="<?php _e("Search...", "component-theme"); ?>" spellcheck="false"/>
					</div>
				</li>
				<li>
					<ul class="ct-dropdown-list">
						<li ng-repeat="post in template.postsList | filter:postsFilter | limitTo:20"
							ng-click="loadTemplatesTerm(post.id);"
							title="<?php _e("Preview this post", "component-theme"); ?>">
								{{post.title}}
						</li>
					</ul>
				</li>
			</ul>
		</div>
	</div>
	<?php 

	endif;

	// show only for archives
	if ( get_post_meta( get_the_ID(), 'ct_template_type', true ) == "archive" ) : 

	?>
	<div class="ct-toolitem">
		<h3><?php _e("Previewing"); ?></h3>
		<div class="ct-selectbox ct-template-select ct-select-search-enabled">
			<ul class="ct-select">
				<li class="ct-selected">{{template.postData.term_name}}<span class="ct-icon ct-dropdown-icon"></span></li>
				<li class="ct-searchbar">
					<div class="ct-textbox">
						<input ng-model="termsFilter" type="text" value="" placeholder="<?php _e("Search...", "component-theme"); ?>" spellcheck="false"/>
					</div>
				</li>
				<li>
					<ul class="ct-dropdown-list">
						<li ng-repeat="term in template.termsList | filter:termsFilter | limitTo:20"
							ng-click="loadTemplatesTerm(term.id);"
							title="<?php _e("Preview this", "component-theme"); ?>">
								{{term.title}}
						</li>
					</ul>
				</li>
			</ul>
		</div>
	</div>
	<?php 

	endif;
}

add_action("ct_toolbar_component_settings", "ct_template_builder_settings" );


/**
 * Get post's template based on template settings
 *
 * @since  0.2.0
 */

function ct_get_posts_template( $post_id ) {

	if ( ! is_numeric( $post_id ) || $post_id <= 0 ) {
		return false;
	}

	$current_post_type = get_post_type( $post_id );

	$args = array(
		'posts_per_page'	=> -1,
		'orderby' 			=> 'meta_value_num',
		'meta_key'			=> 'ct_template_order',
		'order' 			=> 'DESC',
		'post_type' 		=> 'ct_template',
		'post_status' 		=> 'publish',
		'meta_query' => array(
			array(
				'key'     => 'ct_template_type',
				'value'   => 'single_post',
			),
		),
	);

	$templates = new WP_Query( $args );

	foreach ( $templates->posts as $template ) {
		
		// check if all posts applies
		$all_posts = get_post_meta( $template->ID, 'ct_template_single_all', true );

		if ( $all_posts ) {
			return $template;
		}
		else {

			// get post types
			$post_types = get_post_meta( $template->ID, 'ct_template_post_types', true );

			// check if current post type is added for template
			if ( is_array( $post_types ) && in_array( $current_post_type, $post_types ) ) {

				// taxonomies
				$use_taxonomies = get_post_meta( $template->ID, 'ct_use_template_taxonomies', true );
				if ( $use_taxonomies ) {	

					$template_taxonomies = get_post_meta( $template->ID, 'ct_template_taxonomies', true );

					if ( $template_taxonomies ) {

						$match = true;

						foreach ( $template_taxonomies['names'] as $key => $value ) {

							$post_values = wp_get_post_terms( $post_id, $value, array('fields' => 'names') );

							$template_value = $template_taxonomies['values'][$key];
							
							if ( !in_array( $template_value, $post_values ) ) {
								$match = false;
							}
						}

						if ( $match ) {
							return $template;
						}
					}
				}
				else {
					return $template;
				}
			}
		}
	};

	return false;
}


/**
 * Get archive's template based on templates settings
 *
 * @since  0.2.1
 */

function ct_get_archives_template( $post_id = false ) {

	// Get all archive templates
	$args = array(
		'posts_per_page'	=> -1,
		'order' 			=> 'DESC',
		'orderby'    		=> 'meta_value_num',
		'meta_key' 			=> 'ct_template_order',
		'post_type' 		=> 'ct_template',
		'post_status' 		=> 'publish',
		'meta_query' => array(
			array(
				'key'     => 'ct_template_type',
				'value'   => 'archive',
			),
		),
	);

	$templates = new WP_Query( $args );

	foreach ( $templates->posts as $template ) {
		
		// Check what is the current archive
		// Post types
		if ( is_post_type_archive() ) {

			// check if template for all post types
			$post_types_all = get_post_meta( $template->ID, 'ct_template_archive_post_types_all', true );
			if ( $post_types_all ) {
				return $template;
			}

			// check specific post type
			$post_type 	= get_post_type();
			$post_types = get_post_meta( $template->ID, 'ct_template_archive_post_types', true );

			if ( is_array( $post_types ) && in_array( $post_type, $post_types ) ) {
				return $template;
			}
		}

		// Categories
		if ( is_category() ) {

			// check if template for all categories
			$categories_all  = get_post_meta( $template->ID, 'ct_template_categories_all', true );
			if ( $categories_all ) {
				return $template;
			}

			// check specific categories
			$category_id = get_cat_ID( single_cat_title("", false ) );
			$categories  = get_post_meta( $template->ID, 'ct_template_categories', true );

			if ( is_array( $categories ) && in_array( $category_id, $categories ) ) {
				return $template;
			}
		}
		
		// Tags
		if ( is_tag() ) {

			// check if template for all tags
			$tags_all  = get_post_meta( $template->ID, 'ct_template_tags_all', true );
			if ( $tags_all ) {
				return $template;
			}

			// check specific tags
			$tag_id = get_query_var('tag_id');
			$tags 	= get_post_meta( $template->ID, 'ct_template_tags', true );

			if ( is_array( $tags ) && in_array( $tag_id, $tags ) ) {
				return $template;
			}
		}

		// Author archive
		if ( is_author() ) {

			// check if template for all post types
			$authors_all = get_post_meta( $template->ID, 'ct_template_authors_archives_all', true );
			if ( $authors_all ) {
				return $template;
			}

			// check specific post type
			$author 	= get_the_author_id();
			$authors 	= get_post_meta( $template->ID, 'ct_template_authors_archives', true );

			if ( is_array( $authors ) && in_array( $author, $authors ) ) {
				return $template;
			}
		}

		// Custom Taxonomy
		if ( is_tax() ) {

			// check if template for all taxonomies
			$taxonomies_all = get_post_meta( $template->ID, 'ct_template_custom_taxonomies_all', true );
			if ( $taxonomies_all ) {
				return $template;
			}

			$term_id 	= get_queried_object_id();
			$taxonomies = get_post_meta( $template->ID, 'ct_template_custom_taxonomies', true );

			if ( is_array( $taxonomies ) ) {

				// check if for all terms from current taxonomy
				global $taxonomy;
				if ( in_array( "all_".$taxonomy, $taxonomies )) {
					return $template;
				}

				// loop all taxonomies
				//foreach ( $taxonomies as $taxonomy => $terms ) {

					if ( in_array( $term_id, $taxonomies ) ) {
						return $template;
					}
				//}
			}
		}

		// Front Page
		// backend
		if ( $post_id ) {
			if ( get_option( 'page_on_front' ) == $post_id && get_post_meta( $template->ID, 'ct_template_front_page', true ) ) {
				return $template;
			}	
		}
		// frontend
		else {
			if ( is_front_page() && get_post_meta( $template->ID, 'ct_template_front_page', true ) ) {
				return $template;
			}
		}

		// Blog Posts
		// backend
		if ( $post_id ) {
			if ( get_option( 'page_for_posts' ) == $post_id && get_post_meta( $template->ID, 'ct_template_blog_posts', true ) ) {
				return $template;
			}	
		}
		// frontend
		else {
			if ( is_home() && get_post_meta( $template->ID, 'ct_template_blog_posts', true ) ) {
				return $template;
			}
		}

		// Date
		if ( is_date() && get_post_meta( $template->ID, 'ct_template_date_archive', true ) ) {
			return $template;
		}

		// Search
		if ( is_search() && get_post_meta( $template->ID, 'ct_template_search_page', true ) ) {
			return $template;
		}

		// 404
		if ( is_404() && get_post_meta( $template->ID, 'ct_template_404_page', true ) ) {
			return $template;
		}
	}

	//check for index template
	$args = array(
		'posts_per_page'	=> -1,
		'order' 			=> 'DESC',
		'orderby'    		=> 'meta_value_num',
		'meta_key' 			=> 'ct_template_order',
		'post_type' 		=> 'ct_template',
		'post_status' 		=> 'publish',
		'meta_query' => array(
			array(
				'key'     => 'ct_template_index',
				'value'   => 'true',
			),
		),
	);

	$templates = new WP_Query( $args );

	foreach ( $templates->posts as $template ) {
		if ( get_post_meta( $template->ID, 'ct_template_index', true ) ) {
			return $template;
		}
	}
	// finally return false
	return false;
}


/**
 * Get template's post based on template settings
 *
 * @return Object [Modified WP_Post]
 * @since  0.2.0
 * @author Ilya K.
 */

function ct_get_templates_post( $template_id, $current_post_id = false, $option = false ) {

	if ( ! is_numeric( $template_id ) || $template_id <= 0 ) {
		return array();
	}

	$new_post_key = 0;

	// look in cache
	$posts = wp_cache_get("ct_archive_template_posts_" . $template_id );
	
	if ( ! $posts[$new_post_key] ) {

		/**
		 * Query arguments 
		 */
		
		$args = array(
			'posts_per_page'	=> -1,
			'order'				=> 'DESC'
		);

		/**
		 * Get all template's meta
		 */
		
		$all_posts = get_post_meta( $template_id, 'ct_template_single_all', true );

		if ( $all_posts ) {
			
			$query_posts 	= array();
			$post_types 	= get_post_types( '', 'objects' );
			$exclude_types 	= array( "ct_template", "nav_menu_item", "revision" );
			
			foreach ( $post_types as $post_type ) {
				if ( in_array ( $post_type->name, $exclude_types ) ) {
					continue;
				}
				$query_posts[] = $post_type->name;
			}

			$args['post_type'] = $query_posts;
		}
		else {

			// Post types
			$post_types = get_post_meta( $template_id, 'ct_template_post_types', true );

			// don't query if there is no posts
			if ( !$post_types ) {
				return false;
			}

			if ( in_array("attachment", $post_types ) ) {
				$post_status = 'inherit';
			}
			else {
				$post_status = 'publish';
			};

			// add to args
			$args['post_type'] 		= $post_types;
			$args['post_status'] 	= $post_status;

			// Exclude IDs
			$exclude_ids = get_post_meta( $template_id, 'ct_template_exclude_ids', true );
			$exclude_ids = explode(",", $exclude_ids);
			
			// add to args
			if ( $exclude_ids ) {
				$args['post__not_in'] 	= $exclude_ids;
			}

			// taxonomies
			$use_taxonomies = get_post_meta( $template_id, 'ct_use_template_taxonomies', true );
			if ( $use_taxonomies ) {	

				$template_taxonomies = get_post_meta( $template_id, 'ct_template_taxonomies', true );

				if ( $template_taxonomies ) {
					
					$args['tax_query']['relation'] = 'AND';

					foreach ( $template_taxonomies['names'] as $key => $value ) {
						
						$args['tax_query'][] = array(
												'taxonomy' => $value,
												'field'    => 'slug',
												'terms'    => $template_taxonomies['values'][$key],
											);
					}
				}
			}
		}

		//var_dump($args);

		// Make a query
		$query = new WP_Query( $args );
		$posts = $query->posts;
		
		// append permalinks as well
		foreach($posts as $key => $postitem) {
			$posts[$key]->permalink = get_permalink($postitem->ID);
		}
		
		//var_dump($posts);

		// save in cache
		wp_cache_set("ct_archive_template_posts_" . $template_id, $posts );
	}

	/** 
	 * Check for previous/next post query
	 *
	 * @deprecated 0.3.3
	 */
	
	if ( $current_post_id && $option ) {
		
		foreach ( $posts as $key => $post ) {
			// find current post
			if ( $current_post_id == $post->ID ) {
				
				if ( $option == 'previous' ) {
					$new_post_key = $key - 1;
				}

				if ( $option == 'next' ) {
					$new_post_key = $key + 1;
				}
			}
		}

		// loop posts
		if ( $new_post_key < 0 ) {
			$new_post_key = sizeof( $posts ) - 1;
		}

		if ( $new_post_key > sizeof( $posts ) - 1 ) {
			$new_post_key = 0;
		}
	}

	// if not loading any special post return all posts ids and titles
	if ( !$current_post_id ) {
		
		$posts_ids_titles = array();
		
		foreach ( $posts as $post ) {
			
			// unless it is a woocommerce shop page, which is essentially a redirect to products archive
			if(class_exists('WooCommerce') && get_option( 'woocommerce_shop_page_id' ) == $post->ID)
				continue;

			$posts_list[] = array (
									"id"	=> $post->ID,
									"title" => $post->post_title
								);
		}

		// return list of all posts for preview
		return array (
					"postsList" => $posts_list
				);
	}
	else {
		
		foreach ( $posts as $post ) {
			if ( $post->ID == $current_post_id ) {

				// update some values
				$filtered_post = ct_filter_post_object( $post, true ); // since its a template preivew, we dont want anything to do with the post's custom view
				
				// return post data
				return array (
							"postData" => $filtered_post
						);
			}
		}		
	}

	return array();
}


/**
 * Get template's terms
 *
 * @since  0.2.2
 * @author Ilya K.
 */

function ct_get_templates_term( $template_id, $term_id = false, $option = false ) {

	if ( ! is_numeric( $template_id ) || $template_id <= 0 ) {
		return array();
	}

	$new_term_key = 0;

	// look in cache
	$terms = wp_cache_get("ct_archive_template_terms" . $template_id );
	
	if ( ! $terms[$new_term_key] ) {
	
		// get all saved terms
		// post types
		$post_types_all = get_post_meta( $template_id, 'ct_template_archive_post_types_all', true );
		if ( $post_types_all ) {

			$all_post_types = get_post_types( '', 'objects' );
			$exclude_types 	= array( "ct_template", "nav_menu_item", "revision" );
			
			foreach ( $all_post_types as $post_type ) {
				if ( in_array ( $post_type->name, $exclude_types ) ) {
					continue;
				}
				if ( !$post_type->has_archive ) {
					continue;
				}
				$post_types[] = $post_type->name;
			}
		}
		else {
			$post_types = get_post_meta( $template_id, 'ct_template_archive_post_types', true );
		}
		
		// categories
		$categories_all = get_post_meta( $template_id, 'ct_template_categories_all', true );

		if ( $categories_all ) {
			
			$categories_list = get_categories();

			foreach ( $categories_list as $category ) {
				$categories[] = $category->cat_ID;
			}
		}
		else {
			$categories = get_post_meta( $template_id, 'ct_template_categories', true );
		}
		
		// tags
		$tags_all = get_post_meta( $template_id, 'ct_template_tags_all', true );
		if ( $tags_all ) {
			$tags_list = get_tags();

			foreach ( $tags_list as $tag ) {
				$tags[] = $tag->term_id;
			}
		}
		else {
			$tags = get_post_meta( $template_id, 'ct_template_tags', true );
		}
		
		// authors
		$authors_all = get_post_meta( $template_id, 'ct_template_authors_archives_all', true );
		if ( $authors_all ) {
			$users = get_users( array( 'who' => 'authors' ) );

			foreach ( $users as $user ) {
				$authors[] = $user->ID;
			}
		}
		else { 
			$authors = get_post_meta( $template_id, 'ct_template_authors_archives', true );
		}
		
		// custom taxonomies
		$taxonomies_all = get_post_meta( $template_id, 'ct_template_custom_taxonomies_all', true );
		if ( $taxonomies_all ) {
			
			// only custom
			$args = array(
				"_builtin" => false
			);
			$taxonomies_list = get_taxonomies( $args, 'object' );

			foreach ( $taxonomies_list as $taxonomy ) {

				$args = array(
					'hide_empty' 	=> 0,
					'taxonomy' 		=> $taxonomy->name,
				);

				$categories_list = get_categories( $args );
				foreach ( $categories_list as $category ) {
					$categories[] = $category->term_id;
				}
			}
		}
		else {
			$taxonomies = get_post_meta( $template_id, 'ct_template_custom_taxonomies', true );
		}

		// Other Archives
		 
	 	// index
		$template_index 		= get_post_meta( $template_id, 'ct_template_index', true );

		// front page
		$template_front_page 	= get_post_meta( $template_id, 'ct_template_front_page', true );

		// blog posts
		$template_blog_posts 	= get_post_meta( $template_id, 'ct_template_blog_posts', true );

		// date archive
		$template_date_archive 	= get_post_meta( $template_id, 'ct_template_date_archive', true );

		// search result
		$template_search_page 	= get_post_meta( $template_id, 'ct_template_search_page', true );

		// 404 page
		$template_404_page 		= get_post_meta( $template_id, 'ct_template_404_page', true );


		/**
		 * Collect all terms to $terms array
		 */
		
		$terms = array();

		ct_add_term_posts( $terms, 'post_types', $post_types );
		ct_add_term_posts( $terms, 'category', $categories );
		ct_add_term_posts( $terms, 'post_tag', $tags );
		ct_add_term_posts( $terms, 'authors', $authors );

		// Custom taxonomies
		
		if ( is_array( $taxonomies ) ) {
			
			// check "all_{tax_name}" option
			$args = array(
				"_builtin" => false
			);
			$taxonomies_list = get_taxonomies( $args, 'object' );
			$terms_names = array();
			
			foreach ( $taxonomies_list as $tax ) {
				if ( in_array( "all_".$tax->name, $taxonomies ) ) {

					$args = array(
						'hide_empty' 	=> 0,
						'taxonomy' 		=> $tax->name,
					);

					// add all $tax terms
					$categories_list = get_categories( $args );
					foreach ( $categories_list as $category ) {
						$terms_names[$tax->name][] = $category->term_id;
					}
				}
			}

			// add individual terms 
			foreach ( $taxonomies as $tax_id ) {
				// exclude "all_{tax_name}" options from list
				if ( strpos( $tax_id, "all_") !== 0 ) {
					
					$term = get_term( $tax_id );

					if (!$terms_names[$term->taxonomy]) {
						$terms_names[$term->taxonomy] = array();
					}
					
					if ( !in_array( $tax_id, $terms_names[$term->taxonomy] ) ) {
						$terms_names[$term->taxonomy][] = $tax_id;
					}
				}
			}

			// add terms
			foreach ( $terms_names as $name => $ids ) {
				ct_add_term_posts( $terms, $name, $ids );
			}
		}

		// Other Archives 
		
		if ( $template_index ) {
			ct_add_term_posts( $terms, "index");
		}
		if ( $template_front_page ) {
			ct_add_term_posts( $terms, "front_page");
		}
		if ( $template_date_archive ) {
			ct_add_term_posts( $terms, "date_archive");
		}
		if ( $template_blog_posts ) {
			ct_add_term_posts( $terms, "blog_posts");
		}
		if ( $template_search_page ) {
			ct_add_term_posts( $terms, "search_page");
		}
		if ( $template_404_page ) {
			ct_add_term_posts( $terms, "404_page");
		}

		// Filter all posts data
		
		foreach ( $terms as $term_key => $term ) {
			if ( is_array( $term["term_posts"] ) ) {
				foreach ( $term["term_posts"] as $post_key => $post ) {
					$terms[$term_key]["term_posts"][$post_key] = ct_filter_post_object( $post );
				}
			}
		}

		// Save to cache
		wp_cache_set("ct_archive_template_terms" . $template_id, $terms );
	}

	// if not loading any special term return all terms
	if ( !$term_id ) {
		
		$terms_list = array();
		
		foreach ( $terms as $term ) {
			$terms_list[] = array (
									"id"	=> $term["term_id"],
									"title" => $term["term_name"]
								);
		}

		// return list of all posts for preview
		return array (
					"termsList" => $terms_list
				);
	}
	else {
		
		foreach ( $terms as $term ) {
			if ( $term["term_id"] == $term_id ) {
				
				// return post data
				return array (
							"postData" => $term
						);
			}
		}		
	}

	return array();
}


/**
 * Add all term posts to $posts variable
 *
 * @since  0.2.2
 * @author Ilya K.
 */

function ct_add_term_posts( &$terms, $taxonomy_name, $term_ids = false ) {

	if ( $term_ids !== false ) {

		if ( ! is_array( $term_ids ) ) {
			return;
		}

		// get term posts
		foreach ( $term_ids as $term_id ) {

			// lets get archive links for each term
			$permalink = '';
			
			if ( $taxonomy_name == "post_types" ) {

				/**
				 *	No, we do not need preview for an archive of all pages
				 *	because, there is no such link for the WP frontend.
				 */	 
				if( $term_id == 'page' )
					continue;

				$term 		= get_post_type_object($term_id);
				$term_name 	= $term->label;
				
				$args = array(
					'post_type' 	=> $term_id, // post types here like 'product' or 'post'
					'post_status' 	=> 'publish',
				);

				if($term_id == 'post')
					$permalink = get_site_url(null, '/');
				else
					$permalink = get_post_type_archive_link($term_id);
			}
			elseif ( $taxonomy_name == "authors" ) {
				
				$term = get_user_by( 'id', $term_id );
				$term_name = $term->user_nicename;

				$args = array(
					'post_type' 	=> 'any', // post types here like 'product' or 'post'
					'post_status' 	=> 'publish',
					'post_author' 	=> $term_id
				);

				$permalink = get_author_posts_url($term_id);

				$term_id = "author_".$term_id; // add author identifier
			}
			else {

				// get term data
				$term = get_term_by('id', $term_id, $taxonomy_name);
				$term_name = $term->name;
				
				$args = array(
					'post_type' 	=> 'any',
					'post_status' 	=> 'publish',
					'tax_query' 	=> array (
											array (
												'taxonomy' 	=> $taxonomy_name,
												'terms' 	=> $term_id
											)
										)
					);

				if( $taxonomy_name == "category" ) {
					$permalink = get_category_link($term_id);
				}
				else {
					$permalink = get_term_link(intval($term_id));
				}
			}

			// query posts
			$query = new WP_Query( $args );

			// convert to array
			$term_posts = (array) $query->posts;
			
			$terms[] = array (
					"term_id" 		=> $term_id,
					"term_name" 	=> $term_name,
					"term_posts" 	=> $term_posts,
					"term" 			=> json_encode($term),
					"permalink"		=> $permalink
				);
		}
	}
	// Other archives (Date, Blog posts, Index...)
	else {
		
		$term_id = $taxonomy_name;

		if ( $taxonomy_name == "index" ) {
			
			$term_name 	= __("Index", "component-theme");
			$args 		= array();
			$permalink 	= get_home_url(null, '/'); //????
		}
		
		if ( $taxonomy_name == "date_archive" ) {
			
			$term_name 	= __("Date Archive", "component-theme") . date(" (Y/M)");
			$args 		= array();
			$permalink 	= get_month_link("",""); // current year, current month
		}

		if ( $taxonomy_name == "front_page" ) {

			if ( get_option( 'page_on_front' ) ) {
				
				$term_name 	= __("Front Page", "component-theme");
				$args 		= array();
				$permalink 	= get_permalink( get_option( 'page_on_front' ) );
			}
			else {
				return false;
			}
		}

		if ( $taxonomy_name == "blog_posts" ) {
			
			if ( get_option( 'page_for_posts' ) ) {

				$term_name 	= __("Blog Posts", "component-theme");
				$args 		= array('post_type'=>'post'); // unless the post type is specified, it will also load component templates and any other custom post types
				$permalink 	= get_page_link( get_option( 'page_for_posts' ) );
			}
			else {
				return false;
			}
		}

		if ( $taxonomy_name == "search_page" ) {
			
			$term_name 	= __("Search Page", "component-theme");
			$args 		= array();
			$permalink 	= get_search_link("post");
		}

		if ( $taxonomy_name == "404_page" ) {
			
			$term_name 	= __("404 Page", "component-theme");
			$args 		= array();
			$permalink 	= get_home_url( null, "absoltely_incredible_not_possible_to_exist_in_real_world_url_that_will_always_output_404_error_page" );
		}

		// query posts
		$query = new WP_Query( $args );

		// convert to array
		$term_posts = (array) $query->posts;
		
		$terms[] = array (
				"term_id" 		=> $term_id,
				"term_name" 	=> $term_name,
				"term_posts" 	=> $term_posts,
				"term" 			=> json_encode($term),
				"permalink"		=> $permalink
			);
	}
}


/**
 * Go trough the post object and replace some values
 *
 * @since  0.2.0
 * @author Ilya K.
 * @return Object [Modified WP_Post]
 */

function ct_filter_post_object( $post_object, $no_custom_view = false ) {

	// update post author id to nicename
	$post_object->post_author = get_the_author_meta("user_nicename", $post_object->post_author );

	// get components tree based on shortcodes
	/* New Way */
	$shortcodes = get_post_meta($post_object->ID, "ct_builder_shortcodes", true);
	$tree 		= parse_shortcodes($shortcodes);
	
	$post_object->post_tree = $tree['content'];
	
	// check if content is made in builder or not
	if ( ! $tree['is_shortcode'] ) {
		// add filter for regular text posts
		// add_filter("the_content", "wpautop");
		// $post_object->post_content = apply_filters("the_content", $post_object->post_content );
		$post_object->post_content = do_shortcode($post_object->post_content );
	}
	elseif( !$no_custom_view ) {
		// update post content with shortcodes rendered
		// $post_object->post_content = apply_filters("the_content", $shortcodes );
		$post_object->post_content = do_shortcode($shortcodes );
	}
	
	//remove_filter("the_content", "wpautop");
	
	// fix for oEmbed stuff
	global $wp_embed;

	// Add the fetched posts ID and add it to the global object
	$wp_embed->post_ID = $post_object->ID;

	// Execute the [embed] shortcode
	$wp_embed->run_shortcode( $post_object->post_content );

	// Execute the oEmbed handlers for plain links on the own line
	$wp_embed->autoembed( $post_object->post_content );

	return $post_object;
}


/**
 * Get Footer/Header template to output on get_footer()/get_header() calls
 *
 * @since  0.3.1
 */

function ct_get_template_header_footer( $name ) {

	if ( ! $name ) {
		return;
	}

	// Get archive template with the name passed
	$args = array(
		'posts_per_page'	=> 1,
		'orderby' 			=> 'date',
		'order' 			=> 'DESC',
		'post_type' 		=> 'ct_template',
		'post_status' 		=> 'publish',
		'meta_query' => array(
			array(
				'key'     => 'ct_template_type',
				'value'   => 'header_footer',
			),
			array(
				'key' 	  => 'ct_template_header_footer_name',
				'value'   => $name,
			),
		),
	);

	$query = new WP_Query( $args );

	if ( $query->posts ) {
		$template = $query->posts[0];
	}

	// not templates with the name
	else {

		// get first template with no name
		$args = array(
			'posts_per_page'	=> 1,
			'orderby' 			=> 'date',
			'order' 			=> 'DESC',
			'post_type' 		=> 'ct_template',
			'post_status' 		=> 'publish',
			'meta_query' 		=>  array(
										'relation' => 'AND',
										array(
											'relation' => 'OR',
											array(
												'key' 	=> 'ct_template_header_footer_name',
												'value' => 'NOT EXIST'
											),
											array(
												'key' 	=> 'ct_template_header_footer_name',
												'value' => ''
											),
										),
										array(
											'key' 	=> 'ct_template_type',
											'value' => 'header_footer'
										),
									),
		);
		
		$query = new WP_Query( $args );
		
		if ( $query->posts ) {
			$template = $query->posts[0];
		}
	}

	// template found
	if ( $template ) {
		
		$content = get_post_meta( $template->ID, "ct_builder_shortcodes", true );

		//$content = apply_filters("the_content", $template->post_content );
		$content = do_shortcode($content);

		$header_footer = explode("<div class=\"ct-separator\"></div>", $content );
		
		// save as global to use in ct_builder_start/ct_builder_end
		global $ct_template_header;
		global $ct_template_footer;
		global $ct_template_header_footer_id;

		$ct_template_header = $header_footer[0];
		$ct_template_footer = $header_footer[1];
		$ct_template_header_footer_id = $template->ID;
	}

}
add_action( 'get_header', 'ct_get_template_header_footer' );


/**
 * Output Header/Footer template on ct_builder_start/ct_builder_end
 *
 * @since  0.3.1
 */

function ct_get_template_header() {

	// get footer code from global
	global $ct_template_header;

	if (!$ct_template_header)
		return;
	
	echo "<!-- CT Template Header Start-->\n";
	echo $ct_template_header;
	echo "\n<!-- CT Template Header End-->";
}
function ct_get_template_footer() {

	// get footer code from global
	global $ct_template_footer;

	if (!$ct_template_footer)
		return;
	
	echo "<!-- CT Template Footer Start-->\n";
	echo $ct_template_footer;
	echo "\n<!-- CT Template Footer End-->";
}

add_action( 'ct_builder_start', 'ct_get_template_header' );
add_action( 'ct_builder_end', 'ct_get_template_footer' );