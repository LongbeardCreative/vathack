<?php

/**
 * Link Text Component Class
 * 
 * @since 0.3.1
 */

Class CT_Link_Text extends CT_Component {

	function __construct( $options ) {

		// run initialization
		$this->init( $options );
		
		// Add shortcodes
		add_shortcode( $this->options['tag'], array( $this, 'add_shortcode' ) );
	}


	/**
	 * Add a [ct_link_text] shortcode to WordPress
	 *
	 * @since 0.1
	 */

	function add_shortcode( $atts, $content ) {

		$options = $this->set_options( $atts );

		ob_start(); 

		?><a id="<?php echo esc_attr($options['selector']); ?>" class="<?php echo esc_attr($options['classes']); ?>" href="<?php echo esc_attr($options['url']) ?>" <?php echo ($options['target']) ? "target=\"".esc_attr($options['target'])."\"" : ""; ?>><?php echo do_shortcode( $content ); ?></a><?php

		return ob_get_clean();
	}

}


// Create toolbar inctances
$link = new CT_Link_Text ( 

		array( 
			'name' 		=> 'Text Link',
			'tag' 		=> 'ct_link_text',
			'params' 	=> array(
					array(
						"type" 			=> "content",
						"param_name" 	=> "ct_content",
						"value" 		=> "Double-click to edit link text.",
						"css" 			=> false,
					),
					array(
						"type" 			=> "colorpicker",
						"heading" 		=> __("Color"),
						"param_name" 	=> "color",
						"value" 		=> "",
					),
					array(
						"type" 			=> "typography",
						"heading" 		=> __("Font"),
						"param_name" 	=> "typography",
						"css" 			=> false,
					),
					array(
						"type" 			=> "dropdown",
						"heading" 		=> __("Decoration"),
						"param_name" 	=> "text-decoration",
						"value" 		=> array(
											'' 	=> "&nbsp;",
											'none' 	=> "none",
											'underline' 	=> "underline",
											'overline' => "overline",
											'line-through' => "line-through"
										),
						"css" 			=> true,
					),
					array(
						"type" 			=> "textfield",
						"heading" 		=> __("URL"),
						"param_name" 	=> "url",
						"value" 		=> "http://",
						"hidden"		=> true,
						"css" 			=> false,
					),
					array(
						"type" 			=> "textfield",
						"heading" 		=> __("Target"),
						"param_name" 	=> "target",
						"value" 		=> "_self",
						"hidden"		=> true,
						"css" 			=> false,
					),
				),
			'advanced' 	=> array(
				"positioning" => array(
					"values" => array (
						'display' => 'inline-block',
					)
				)
			),
			'content_editable' => true,
		)
);

?>