<?php


Class CT_Image extends CT_Component {

	function __construct( $options ) {

		// run initialization
		$this->init( $options );
		
		// Add shortcode
		add_shortcode( $this->options['tag'], array( $this, 'add_shortcode' ) );
	}


	/**
	 * Add a [ct_headline] shortcode to WordPress
	 *
	 * @since 0.1
	 */

	function add_shortcode( $atts, $content ) {

		$options = $this->set_options( $atts );

		return '<img id="' . esc_attr($options['selector']) . '" alt="' . esc_attr(base64_decode($options['alt'])) . '" src="' . esc_attr($options['src']) . '" class="' . esc_attr($options['classes']) . '"/>';
	}
}

/**
 * Create Iamge Component Instance
 * 
 * @since 0.1.2
 */

$button = new CT_Image ( 

		array( 
			'name' 		=> 'Image',
			'tag' 		=> 'ct_image',
			'params' 	=> array(
					array(
						"type" 			=> "mediaurl",
						"heading" 		=> __("URL"),
						"param_name" 	=> "src",
						"value" 		=> "http://placehold.it/200x150",
						"css"			=> false
					),
					array(
						"type" 			=> "measurebox",
						"heading" 		=> __("Width"),
						"param_name" 	=> "width",
						"value" 		=> "",
					),
					array(
						"type" 			=> "measurebox",
						"heading" 		=> __("Height"),
						"param_name" 	=> "height",
						"value" 		=> "",
					),
					array(
						"param_name" 	=> "width-unit",
						"value" 		=> "auto",
						"hidden" 		=> true,
					),
					array(
						"param_name" 	=> "height-unit",
						"value" 		=> "auto",
						"hidden" 		=> true,
					),
					array(
						"type" 			=> "textfield",
						"heading" 		=> __("Alt"),
						"param_name" 	=> "alt",
						"value" 		=> "",
						"css" 			=> false,
					),
				)
		)
);

?>