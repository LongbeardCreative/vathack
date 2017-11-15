<?php 

/**
 * oEmbed Class
 *
 * @since 0.1.7
 */

Class CT_Shortcode extends CT_Component {

	function __construct( $options ) {

		// run initialization
		$this->init( $options );
		
		// change button place
		remove_action("ct_toolbar_fundamentals_list", array( $this, "component_button" ) );
		add_action("ct_folder_component_shortcode", array( $this, "component_button" ) );

		add_filter( 'template_include', array( $this, 'ct_shortcode_single_template'), 100 );
		
		// Add shortcodes
		add_shortcode( $this->options['tag'], array( $this, 'add_shortcode' ) );

		// woocommerce specific
		if(isset($_REQUEST['action']) && stripslashes($_REQUEST['action']) == 'ct_render_shortcode') {
			// do not redirect shop page when its a builder preview
			add_action( 'wp', array( $this, 'ct_code_remove_template_redirect'));
		}
	}

	function ct_code_remove_template_redirect() {
		global $wp_filter;
		if(isset($wp_filter['template_redirect']['10']['wc_template_redirect'])) {
			unset($wp_filter['template_redirect']['10']['wc_template_redirect']);
			//echo "WooCommerce Shop page is essentially a redirect to Products Archive.";
		}

	}


	/**
	 * Add a [ct_shortcode] shortcode to WordPress
	 *
	 * @since 0.2.3
	 */

	function add_shortcode( $atts, $content ) {

		$options = $this->set_options( $atts );

		ob_start();	

		?><<?php echo esc_attr($options['tag']) ?> id="<?php echo esc_attr($options['selector']); ?>" class="<?php echo esc_attr($options['classes']); ?>"><?php echo do_shortcode( $content ); ?></<?php echo esc_attr($options['tag']) ?>><?php

		return ob_get_clean();
	}

	
	/**
	 * Add WordPress folder button
	 *
	 * @since 0.4.0
	 * @author Ilya K.
	 */
	
	function component_button() { ?>

		<div class="ct-add-component-button"
			ng-click="addComponent('<?php echo esc_attr($this->options['tag']); ?>','shortcode')">
			<div class="ct-add-component-icon">
				<span class="ct-icon <?php echo esc_attr($this->options['tag']); ?>-icon"></span>
			</div>
			<?php echo esc_html($this->options['name']); ?>
		</div>

	<?php }


	/**
	 * This function hijacks the template to return special template that renders the code results
	 * for the ct_code_block element to load the content into the builder for preview.
	 * 
	 * @since 0.4.0
	 * @author gagan goraya
	 */
	
	function ct_shortcode_single_template( $template ) {

		$new_template = '';

		if(isset($_REQUEST['action']) && stripslashes($_REQUEST['action']) == 'ct_render_shortcode') {
			$nonce  	= $_REQUEST['nonce'];
			$post_id 	= $_REQUEST['post_id'];
			
			// check nonce
			if ( ! wp_verify_nonce( $nonce, 'oxygen-nonce-' . $post_id ) ) {
			    // This nonce is not valid.
			    die( 'Security check' );
			}
			
			if ( file_exists(dirname(dirname( __FILE__)) . '/layouts/' . 'shortcode.php') ) {
				$new_template = dirname(dirname( __FILE__)) . '/layouts/' . 'shortcode.php';
			}
		}

		if ( '' != $new_template ) {
				return $new_template ;
			}

		return $template;
	}

}


$button = new CT_Shortcode ( array( 
		'name' 		=> 'Shortcode',
		'tag' 		=> 'ct_shortcode',
		'shortcode'	=> true,
		'params' 	=> array(
							/*array(
								"param_name" 	=> "shortcode_tag",
								"value" 		=> "shortcode",
								"type" 			=> "textfield",
								"heading" 		=> __("Tag","component-theme"),
								"class" 		=> "ct-textbox-big",
								"css" 			=> false,
							),
							array(
								"param_name" 	=> "id",
								"value" 		=> "",
								"type" 			=> "textfield",
								"heading" 		=> __("ID","component-theme"),
								"class" 		=> "ct-textbox-small",
								"css" 			=> false,
							),*/
							array(
								"param_name" 	=> "full_shortcode",
								"value" 		=> "",
								"type" 			=> "textfield",
								"heading" 		=> __("Full shortcode","component-theme"),
								"class" 		=> "ct-textbox-huge",
								"css" 			=> false,
							),
							array(
								"type" 			=> "tag",
								"heading" 		=> __("Tag"),
								"param_name" 	=> "tag",
								"value" 		=> array (
													"div" => "DIV",
													"p" => "P",
													"h1" => "H1",
													"h2" => "H2",
													"h3" => "H3",
													"h4" => "H4",
													"h5" => "H5",
													"h6" => "H6",
												),
								"css" 			=> false,
							)
						),
		'advanced' => false
		)
	);
