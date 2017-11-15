<?php 

Class CT_Separator extends CT_Component {

	function __construct( $options ) {

		// run initialization
		$this->init( $options );
		
		// add shortcodes
		add_shortcode( $this->options['tag'], array( $this, 'add_shortcode' ) );
	}


	/**
	 * Add a [ct_separator] shortcode to WordPress
	 *
	 * @since 0.3.1
	 */

	function add_shortcode( $atts, $content ) {

		$options = $this->set_options( $atts );

		ob_start();
		
		?><div class="ct-separator"></div><?php

		return ob_get_clean();
	}


	/**
	 * Add a toolbar button
	 *
	 * @since 0.1
	 */
	function component_button() { 

		$template_type = get_post_meta( get_the_ID(), 'ct_template_type', true ); 

		if ( $template_type != "header_footer") {
			return;
		} ?>

		<div class="ct-add-component-button" 
			ng-click="addComponent('<?php echo esc_attr($this->options['tag']); ?>'); addSeparator();"
			ng-show="!separatorAdded">
			<div class="ct-add-component-icon">
				<span class="ct-icon <?php echo esc_attr($this->options['tag']); ?>-icon"></span>
			</div>
			<?php echo esc_html($this->options['name']); ?>
		</div>

	<?php }

}


// Create instance
$separator = new CT_Separator( array( 
				'name' 		=> 'Separator',
				'tag' 		=> 'ct_separator',
				'advanced' 	=> false
			)
		);