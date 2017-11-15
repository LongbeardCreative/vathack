<?php

/**
 * Span Class
 * 
 * @since 0.1.8
 */


Class CT_Span extends CT_Component {

	var $options;

	function __construct( $options ) {

		// run initialization
		$this->init( $options );

		// remove component button
		remove_action("ct_toolbar_fundamentals_list", array( $this, "component_button" ) );
		
		// Add shortcode
		add_shortcode( $this->options['tag'], array( $this, 'add_shortcode' ) );
	}


	/**
	 * Add a [ct_span] shortcode to WordPress
	 *
	 * @since 0.1.2
	 */

	function add_shortcode( $atts, $content ) {

		$options = $this->set_options( $atts );

		ob_start(); 

		?><span id="<?php echo esc_attr($options['selector']); ?>" class="<?php echo esc_attr($options['classes']); ?>"><?php echo $content; ?></span><?php

		return ob_get_clean();
	}
}

$text_block = new CT_Span ( 

		array( 
			'name' 		=> 'Span',
			'tag' 		=> 'ct_span',
			'params' 	=> array(
					array(
						"type" 			=> "content",
						"param_name" 	=> "ct_content",
						"value" 		=> "Span text",
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
						"css" 			=> false
					),
				),
			'advanced' 	=> array(
					"positioning" => array(
						"values" 	=> array (
							'display' 	=> 'inline-block',
							)
					),
					'typography' => array(
						'values' 	=> array (
								'font-size' 			=> '',
								'font-weight' 			=> '',
								'font-style' 			=> '',
								'text-decoration' 		=> 'inherit',
								'text-transform' 		=> '',
							)
					),
			),
			'content_editable' => true,
		)
);