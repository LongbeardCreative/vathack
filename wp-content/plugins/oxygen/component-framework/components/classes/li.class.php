<?php

/**
 * Ul component
 * 
 * @since 0.3.1
 */

Class CT_LI_Component extends CT_Component {

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
	 * Add a toolbar button
	 *
	 * @since 0.1.5
	 */

	function component_button() { ?>

		<div class="ct-add-component-button"
			ng-click="addComponent('<?php echo isset($this->options['tag'])?$this->options['tag']:''; ?>'<?php echo isset($type)?$type:''; ?>)"
			ng-if="isActiveName('ct_ul')||isActiveName('ct_li')">
			<div class="ct-add-component-icon">
				<span class="ct-icon <?php echo $this->options['tag']; ?>-icon"></span>
			</div>
			<?php echo $this->options['name']; ?>
		</div>

	<?php }


	/**
	 * Add a [ct_li] shortcode to WordPress
	 *
	 * @since 0.1
	 */

	function add_shortcode( $atts, $content ) {

		$options = $this->set_options( $atts );

		ob_start();

		?><li id="<?php echo esc_attr($options['selector']); ?>" class="<?php echo esc_attr($options['classes']); ?>"><?php echo do_shortcode( $content ); ?></li><?php

		return ob_get_clean();
	}
}


// Create toolbar inctances
$button = new CT_LI_Component ( 

		array( 
			'name' 		=> 'Li',
			'tag' 		=> 'ct_li',
			'params' 	=> array(
					array(
						"type" 			=> "content",
						"param_name" 	=> "ct_content",
						"value" 		=> "Double-click to edit list item text.",
						"css" 			=> false,
					),
					array(
						"type" 			=> "typography",
						"heading" 		=> __("Font", "component-theme"),
						"values"		=> array(
											'font-size' => "", 
										)
					),
				),
			'content_editable' => true,
		)
);

?>