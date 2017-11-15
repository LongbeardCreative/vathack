<?php 

Class CT_Inner_Content extends CT_Component {

	var $shortcode_options;
	var $shortcode_atts;

	function __construct( $options ) {

		// run initialization
		$this->init( $options );
		
		// add shortcodes
		add_shortcode( $this->options['tag'], array( $this, 'add_shortcode' ) );

		for ( $i = 2; $i <= 16; $i++ ) {
			add_shortcode( $this->options['tag'] . "_" . $i, array( $this, 'add_shortcode' ) );
		}
	}


	/**
	 * Add a [ct_inner_content] shortcode to WordPress
	 *
	 * @since 1.2.0
	 */

	function add_shortcode( $atts, $content ) {

		$options = $this->set_options( $atts );

		ob_start();

		?><div id="<?php echo esc_attr($options['selector']); ?>" class="<?php echo esc_attr($options['classes']); ?>"><?php echo do_shortcode( $content ); ?></div><?php

		return ob_get_clean();
	}

	/**
	 * Add a toolbar button
	 *
	 * @since 0.1
	 */
	function component_button() { 

		$post_type = get_post_type();
		
		if ( $post_type != "ct_template") {
			return;
		} ?>

		<div class="ct-add-component-button"
			ng-click="addComponent('<?php echo $this->options['tag']; ?>')">
			<div class="ct-add-component-icon">
				<span class="ct-icon <?php echo $this->options['tag']; ?>-icon"></span>
			</div>
			<?php echo esc_html($this->options['name']); ?>
		</div>


	<?php }
}




// Create instance
$html = new CT_Inner_Content( array( 
			'name' 		=> 'Inner Content',
			'tag' 		=> 'ct_inner_content',			
			'advanced' 	=> false
			)
		);