<?php 

Class CT_Column extends CT_Component {

	function __construct( $options ) {

		// run initialization
		$this->init( $options );

		// remove component button
		remove_action("ct_toolbar_fundamentals_list", array( $this, "component_button" ) );
		
		// Add shortcodes
		add_shortcode( $this->options['tag'], array( $this, 'add_shortcode' ) );

		for ( $i = 2; $i <= 16; $i++ ) {
			add_shortcode( $this->options['tag'] . "_" . $i, array( $this, 'add_shortcode' ) );
		}
	}


	/**
	 * Add a [ct_column] shortcode to WordPress
	 *
	 * @since 0.1
	 */

	function add_shortcode( $atts, $content ) {

		$options = $this->set_options( $atts );

		ob_start();
		
		?><div id="<?php echo esc_attr($options['selector']); ?>" class="<?php echo esc_attr($options['classes']); ?>"><?php echo do_shortcode( $content ); ?></div><?php

		return ob_get_clean();
	}
}


// Create inctance
$column = new CT_Column( array( 
			'name' 		=> 'Column',
			'tag' 		=> 'ct_column',
			'params' 	=> array(
					array(
						"type" 			=> "colorpicker",
						"heading" 		=> __("Bg"),
						"param_name" 	=> "background-color",
						"value" 		=> "",
					),
					array(
						"type" 			=> "columnwidth",
						"heading" 		=> __("Width"),
						"param_name" 	=> "width",
						"value" 		=> "50.00",
						"css" 			=> false
					),
					array(
						"type" 			=> "align",
						"heading" 		=> __("Align"),
						"param_name" 	=> "text-align",
						'value' 		=> "start"
					),
				),
			'advanced' 	=> array(
					"positioning" => array(
						"values" 	=> array (
							'width-unit' => '%',
							)
					)
				)
		)
	);