<?php 

Class CT_Columns extends CT_Component {

	function __construct( $options ) {

		// run initialization
		$this->init( $options );

		// Add shortcodes
		add_shortcode( $this->options['tag'], array( $this, 'add_shortcode' ) );

		for ( $i = 2; $i <= 16; $i++ ) {
			add_shortcode( $this->options['tag'] . "_" . $i, array( $this, 'add_shortcode' ) );
		}

		// add specific settings
		add_action("ct_toolbar_component_header", array( $this, "columns_settings") );
	}


	/**
	 * Add a toolbar button
	 *
	 * @since 0.1
	 */
	function component_button() { ?>

		<div class="ct-add-component-button"
			ng-click="addComponents('<?php echo esc_attr($this->options['tag']); ?>','ct_column')">
			<div class="ct-add-component-icon">
				<span class="ct-icon <?php echo esc_attr($this->options['tag']); ?>-icon"></span>
			</div>
			<?php echo esc_html($this->options['name']); ?>
		</div>

	<?php }


	/**
	 * Add a [ct_columns] shortcode to WordPress
	 *
	 * @since 0.1
	 */

	function add_shortcode( $atts, $content ) {

		$options = $this->set_options( $atts );

		ob_start();
		
		?><div id="<?php echo esc_attr($options['selector']); ?>" class="<?php echo esc_attr($options['classes']); ?>"><div class="ct-columns-inner-wrap"><?php echo do_shortcode( $content ); ?></div></div><?php

		return ob_get_clean();
	}

	
	/**
	 * Columns settings: columns number
	 */
	
	function columns_settings() { ?>
		
		<div class="ct-toolitem" ng-if="isActiveName('<?php echo esc_attr($this->options['tag']); ?>')">
			<h3><?php _e('Columns', 'component-theme'); ?></h3>
			<div class="ct-textbox ct-columns-number">
				<input type="number" min="1" max="12" class="oxygen-special-property"
					ng-model="columns[component.active.id]"
					ng-change="updateColumns(component.active.id)"/>
			</div>
		</div>
	
	<?php }

// End CT_Columns class
}


// Create section inctance
$section = new CT_Columns( array( 
			'name' 		=> 'Columns',
			'tag' 		=> 'ct_columns',
			'params' 	=> array(
					array(
						"type" 			=> "colorpicker",
						"heading" 		=> __("Bg"),
						"param_name" 	=> "background-color",
						"value" 		=> "#ffffff",
					),
					array(
						"type" 			=> "measurebox",
						"heading" 		=> __("Spacing"),
						"param_name" 	=> "gutter",
						"value" 		=> "0",
						"css" 			=> false
					),
					array(
						"param_name" 	=> "gutter-unit",
						"value" 		=> "px",
						"hidden" 		=> true,
						"css" 			=> false
					),
				),
			'advanced' 	=> array(
					"positioning" => array(
						"values" 	=> array (
							'position' 	=> 'relative',
							)
					)
			)
		)
);