<?php

/**
 * Text Block Class
 * 
 * @since 0.1.2
 */


Class CT_Text_Block extends CT_Component {

	var $options;

	function __construct( $options ) {

		// run initialization
		$this->init( $options );
		
		// Add shortcode
		add_shortcode( $this->options['tag'], array( $this, 'add_shortcode' ) );
	}


	/**
	 * Add a [ct_text_block] shortcode to WordPress
	 *
	 * @since 0.1.2
	 */

	function add_shortcode( $atts, $content ) {

		$options = $this->set_options( $atts );

		ob_start();
		
		?><div id="<?php echo esc_attr($options['selector']); ?>" class="<?php echo esc_attr($options['classes']); ?>"><?php echo do_shortcode( $content ); ?></div><?php

		return ob_get_clean();
	}
}

$text_block = new CT_Text_Block( 

		array( 
			'name' 		=> 'Text Block',
			'tag' 		=> 'ct_text_block',
			'params' 	=> array(
					array(
						"type" 			=> "content",
						"param_name" 	=> "ct_content",
						"value" 		=> "This is a block of text. Double-click this text to edit it.",
					),
					array(
						"type" 			=> "colorpicker",
						"heading" 		=> __("Color", "component-theme"),
						"param_name" 	=> "color",
						"value" 		=> "",
					),
					array(
						"type" 			=> "typography",
						"heading" 		=> __("Font", "component-theme"),
						"css" 			=> false,
					),
					array(
						"type" 			=> "textfield",
						"param_name" 	=> "template_tag",
						"value" 		=> "",
						"hidden" 		=> true,
						"css" 			=> false,
					),
					array(
						"type" 			=> "textfield",
						"param_name" 	=> "comment_meta_name",
						"value" 		=> "",
						"hidden" 		=> true,
						"css" 			=> false,
					),
					array(
						"type" 			=> "textfield",
						"param_name" 	=> "function_name",
						"value" 		=> "get_comment_rating",
						"hidden" 		=> true,
						"css" 			=> false,
					),
				),
			'advanced' 	=> array(
					'typography' => array(
						'values' 	=> array (
								'text-align' 	=> ""
							)
					),
			),
			'content_editable' => true,
		)
);