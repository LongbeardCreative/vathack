<?php 

Class CT_Section extends CT_Component {

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
	 * Add a [ct_section] shortcode to WordPress
	 *
	 * @since 0.1
	 */

	function add_shortcode( $atts, $content ) {

		$options = $this->set_options( $atts );

		ob_start();
		
		?><div id="<?php echo esc_attr($options['selector']) ?>" class="<?php echo esc_attr($options['classes']); ?>"><div class="ct-section-inner-wrap"><?php echo do_shortcode( $content ); ?></div></div><?php

		return ob_get_clean();
	}

	/**
	 * Add a toolbar button
	 *
	 * @since 1.2.0
	 */
	function component_button() { 

		?>
		<div class="ct-add-component-button" ng-click="addComponent('<?php echo $this->options['tag']; ?>')">
			<div class="ct-add-component-icon">
				<span class="ct-icon <?php echo $this->options['tag']; ?>-icon"></span>
			</div>
			<?php echo esc_html($this->options['name']); ?>
		</div>


	<?php }

// End CT_Section class
}


// Create section inctance
$section = new CT_Section( array( 
			'name' 		=> 'Section',
			'tag' 		=> 'ct_section',
			'params' 	=> array(
					array(
						"type" 			=> "colorpicker",
						"heading" 		=> __("Bg"),
						"param_name" 	=> "background-color",
						"value" 		=> "",
					),
					array(
						"type" 			=> "dropdown",
						"heading" 		=> __("Container"),
						"param_name" 	=> "section-width",
						"value" 		=> array(
											'page-width' 	=> "page width",
											'full-width' 	=> "full width",
											'custom'		=> "custom", 
										),
						"css" 			=> false
					),
					array(
						"type" 			=> "measurebox",
						"heading" 		=> __(""),
						"param_name" 	=> "custom-width",
						"value" 		=> "",
						"condition" 	=> "section-width=custom",
					),
					array(
						"param_name" 	=> "custom-width-unit",
						"value" 		=> "auto",
						"hidden" 		=> true
					),
					array(
						"type" 			=> "container-padding",
						"param_name"	=> "container-padding-top",
						"value" 		=> "0",
						"hidden" 		=> true
					),
					array(
						"type" 			=> "container-padding",
						"param_name"	=> "container-padding-right",
						"value" 		=> "0",
						"hidden" 		=> true
					),
					array(
						"type" 			=> "container-padding",
						"param_name"	=> "container-padding-bottom",
						"value" 		=> "0",
						"hidden" 		=> true
					),
					array(
						"type" 			=> "container-padding",
						"param_name"	=> "container-padding-left",
						"value" 		=> "0",
						"hidden" 		=> true
					),
					array(
						"type" 			=> "container-padding",
						"param_name"	=> "container-padding-top-unit",
						"value" 		=> "px",
						"hidden" 		=> true
					),
					array(
						"type" 			=> "container-padding",
						"param_name"	=> "container-padding-right-unit",
						"value" 		=> "px",
						"hidden" 		=> true
					),
					array(
						"type" 			=> "container-padding",
						"param_name"	=> "container-padding-bottom-unit",
						"value" 		=> "px",
						"hidden" 		=> true
					),
					array(
						"type" 			=> "container-padding",
						"param_name"	=> "container-padding-left-unit",
						"value" 		=> "px",
						"hidden" 		=> true
					),
					array(
						"type" 			=> "align",
						"heading" 		=> __("Align"),
						"param_name" 	=> "text-align",
						'value' 		=> "start"
					),
				)
		)
);