<?php

/**
 * Ul component
 * 
 * @since 0.3.1
 */

Class CT_UL_Component extends CT_Component {

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
	 * Add a [ct_ul] shortcode to WordPress
	 *
	 * @since 0.1
	 */

	function add_shortcode( $atts, $content ) {

		$options = $this->set_options( $atts );

		ob_start();

		?><ul id="<?php echo esc_attr($options['selector']); ?>" class="<?php echo esc_attr($options['classes']); ?>"><?php echo do_shortcode( $content ); ?></ul><?php

		return ob_get_clean();
	}
}


// Create toolbar inctances
$button = new CT_UL_Component ( 

		array( 
			'name' 		=> 'UL',
			'tag' 		=> 'ct_ul',
			'params' 	=> array(
					array(
						"type" 			=> "typography",
						"heading" 		=> __("Font", "component-theme"),
						"values"		=> array(
											'font-size' => "", 
										)
					)
				)
		)
);

?>