<?php 

Class CT_Widget extends CT_Component {

	function __construct( $options ) {

		// run initialization
		$this->init( $options );

		// remove component button
		remove_action("ct_toolbar_fundamentals_list", array( $this, "component_button" ) );
		
		// Add shortcodes
		add_shortcode( $this->options['tag'], array( $this, 'add_shortcode' ) );

		// add toolbar
		add_action("ct_toolbar_widgets_folder", 	array( $this, "widgets_list") );

		// add specific options
		add_action("ct_toolbar_component_settings", array( $this, "widget_settings") );
	}


	/**
	 * Add a [ct_widget] shortcode to WordPress
	 *
	 * @since 0.2.3
	 */

	function add_shortcode( $atts, $content ) {

		$options = $this->set_options( $atts );

		$atts = json_decode($atts['ct_options'], true);

		ob_start();

		if ( ! $GLOBALS['wp_widget_factory']->widgets[$atts['original']['class_name']] ) {
			echo "<div><b>Error!</b><br/> No '".$atts['original']['class_name']."' widget registered in this installation.<br/><br/></div>";
			return ob_get_clean();
		}
		
		?><div id="<?php echo esc_attr($options['selector']); ?>" class="<?php echo esc_attr($options['classes']); ?>"><?php the_widget( $atts['original']['class_name'], isset($atts['original']['paramsBase64'])?array_map(array($this,"ct_decode_widget_shortcode_params"), $atts['original']['instance']):$atts['original']['instance'] ); ?></div><?php

		return ob_get_clean();
	}

	
	/**
	 * Decode widget options
	 *
	 * @since 1.2
	 */

	function ct_decode_widget_shortcode_params($value) {
		return base64_decode($value);
	}


	/**
	 * Output settings
	 *
	 * @since 0.2.3
	 */

	function widget_settings() { ?>

		<div class="ct-toolitem" ng-show="isActiveName('<?php echo $this->options['tag']; ?>')" >
			<div class="ct-tab ct-button-outlined ct-tooltab-closed" title="<?php _e("Set widget options","component-theme");?>" 
				ng-click="renderWidget(component.active.id,true)">
				<span class="ct-icon"></span><?php _e("Edit Widget Settings", "component-theme"); ?></div>
		</div>

	<?php }


	/**
	 * Display all widgets
	 *
	 * @since  0.2.3
	 */

	function widgets_list() {
		
		foreach ( $GLOBALS['wp_widget_factory']->widgets as $class => $widget ) {

			?>

			<div class="ct-add-component-button" title="<?php echo $widget->widget_options['description']; ?>"
				ng-click="addWidget('<?php echo $class; ?>','<?php echo $widget->id_base; ?>', '<?php echo $widget->name; ?>')">
				<div class="ct-add-component-icon">
					<span class="ct-icon <?php echo $this->options['tag']; ?>-icon"></span>
				</div>
				<?php echo $widget->name; ?>
			</div>

			<?php 
		}
	}
}


// Create inctance
$widget = new CT_Widget( array( 
			'name' 		=> 'Widget',
			'tag' 		=> 'ct_widget',
			'params' 	=> array(
					array(
						"type" 			=> "textfield",
						"param_name" 	=> "class_name",
						"hidden" 		=> true,
						"css" 			=> false,
					),
					array(
						"type" 			=> "textfield",
						"param_name" 	=> "id_base",
						"hidden" 		=> true,
						"css" 			=> false,
					),
					array(
						"type" 			=> "textfield",
						"param_name" 	=> "instance",
						"hidden" 		=> true,
						"css" 			=> false,
					),
				),
			'advanced' => false
			)
		); 