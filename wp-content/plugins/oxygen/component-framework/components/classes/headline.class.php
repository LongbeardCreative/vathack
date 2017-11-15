<?php

/**
 * Headline Component Class
 * 
 * @since 0.1.2
 */

Class CT_Headline extends CT_Component {

	function __construct( $options ) {

		// run initialization
		$this->init( $options );
		
		// Add shortcodes
		add_shortcode( $this->options['tag'], array( $this, 'add_shortcode' ) );
	}


	/**
	 * Add a [ct_headline] shortcode to WordPress
	 *
	 * @since 0.1
	 */

	function add_shortcode( $atts, $content ) {

		$options = $this->set_options( $atts );

		ob_start();

		echo "<".esc_attr($options['tag'])." id=\"".esc_attr($options['selector'])."\" class=\"".esc_attr($options['classes'])."\">" . do_shortcode( $content ) . "</".esc_attr($options['tag']).">";

		return ob_get_clean();
	}
}

$headline = new CT_Headline ( 

		array( 
			'name' 		=> 'Heading',
			'tag' 		=> 'ct_headline',
			'params' 	=> array(
					array(
						"type" 			=> "content",
						"param_name" 	=> "ct_content",
						"value" 		=> "Double-click this headline to edit the text.",
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
					array(
						"type" 			=> "tag",
						"heading" 		=> __("Tag"),
						"param_name" 	=> "tag",
						"value" 		=> array (
											"h1" => "H1",
											"h2" => "H2",
											"h3" => "H3",
											"h4" => "H4",
											"h5" => "H5",
											"h6" => "H6",
										),
						"css" 			=> false,
					),
				),
			'advanced' 	=> array(
					'typography' => array(
						'values' 	=> array (
								'font-family' 	=> array ( 'global', 'Display' ),
								'font-size' 	=> "36",
								'font-weight' 	=> "700",
								'text-align' 	=> ""
							)
					),
			),
			'content_editable' => true,
		)
);