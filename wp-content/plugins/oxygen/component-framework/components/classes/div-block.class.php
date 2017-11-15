<?php

/**
 * Div Block Component Class
 * 
 * @since 0.1.3
 */

Class CT_DIV_Block extends CT_Component {

	var $options;

	function __construct( $options ) {

		// run initialization
		$this->init( $options );
		
		// Add shortcodes
		add_shortcode( $this->options['tag'], array( $this, 'add_shortcode' ) );

		for ( $i = 2; $i <= 16; $i++ ) {
			add_shortcode( $this->options['tag'] . "_" . $i, array( $this, 'add_shortcode' ) );
		}
	}


	/**
	 * Add a [div_block] shortcode to WordPress
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


// Create toolbar inctances
$button = new CT_DIV_Block ( 

		array( 
			'name' 		=> 'Div Block',
			'tag' 		=> 'ct_div_block',
			'params' 	=> array(
					array(
						"type" 			=> "dropdown",
						"heading" 		=> __("Float"),
						"param_name" 	=> "float",
						"value" 		=> array(
											'' => '&nbsp;',
											'none' 	=> "none",
											'left' 	=> "left",
											'right' => "right"
										),
						"css" 			=> true,
					),
					array(
						"type" 			=> "dropdown",
						"heading" 		=> __("Display"),
						"param_name" 	=> "display",
						"value" 		=> array(
										'' => '&nbsp;',
										'inline' => 'inline',
										'inline-block' => 'inline-block',
										'block' => 'block',
										'none' => 'none',
										),
						"css" 			=> true,
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
						"type" 			=> "align",
						"heading" 		=> __("Align"),
						"param_name" 	=> "text-align",
						'value' 		=> "start"
					),
					array(
						"type" 			=> "typography",
						"heading" 		=> __("Font"),
						"param_name" 	=> "typography",
						"css" 			=> false,
						"hidden" 		=> true
					),
				),
			'advanced' 	=> array(
					'typography' => array(
						'values' 	=> array (
								'font-family' 	=> "",
								'font-size' 	=> "",
								'font-weight' 	=> "",
							)
					),
			),


			
		)
);

?>