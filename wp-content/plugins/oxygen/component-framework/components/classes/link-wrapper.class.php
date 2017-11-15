<?php

/**
 * Link Component Class
 * 
 * @since 0.1.5
 */

Class CT_Link_Wrapper extends CT_Component {

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
	 * Add a toolbar button
	 *
	 * @since 0.1.5
	 */

	function component_button_old() { ?>
		
		<div class="ct-add-component-button ct-action-button" ng-click="addComponents('<?php echo $this->options['tag']; ?>', 'ct_text_block')">
			<?php echo $this->options['name']; ?>
		</div>

	<?php }


	/**
	 * Add a [ct_link] shortcode to WordPress
	 *
	 * @since 0.1
	 */

	function add_shortcode( $atts, $content ) {

		$options = $this->set_options( $atts );

		ob_start(); 

		?><a id="<?php echo esc_attr($options['selector']); ?>" class="<?php echo esc_attr($options['classes']); ?>" href="<?php echo esc_attr($options['url']) ?>" target="<?php echo esc_attr($options['target']) ?>"><?php echo do_shortcode( $content ); ?></a><?php

		return ob_get_clean();
	}

}


// Create toolbar inctances
$link = new CT_Link_Wrapper ( 

		array( 
			'name' 		=> 'Link Wrapper',
			'tag' 		=> 'ct_link',
			'params' 	=> array(
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
					array(
						"type" 			=> "dropdown",
						"heading" 		=> __("Float"),
						"param_name" 	=> "float",
						"value" 		=> array(
											'' => '&nbsp;',
											'none' 	=> "none",
											'left' 	=> "left",
											'right' => "right",
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
										'none' => 'none'
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
				),
			'advanced' 	=> array(
					"positioning" => array(
						"values" 	=> array (
							'display' 	=> 'inline-block',
							)
					)
			)
		)
);

?>