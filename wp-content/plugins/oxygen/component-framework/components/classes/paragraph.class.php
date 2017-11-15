<?php

/**
 * Paragraph Class
 * 
 * @since 0.1.6
 */


Class CT_Paragraph extends CT_Component {

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

$text_block = new CT_Paragraph ( 

		array( 
			'name' 		=> 'Paragraphs',
			'tag' 		=> 'ct_paragraph',
			'params' 	=> array(
					array(
						"type" 			=> "content",
						"param_name" 	=> "ct_content",
						"value" 		=> "<p>This is a paragraph - p tags. Double click this text to edit it. Make two newlines to make a new paragraph.</p><p>This is a another paragraph.</p>",
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