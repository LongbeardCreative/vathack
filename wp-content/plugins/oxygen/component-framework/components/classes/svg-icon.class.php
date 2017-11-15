<?php

/**
 * CT_SVG_Icon Component Class
 * 
 * @since 0.2.1
 */

Class CT_SVG_Icon extends CT_Component {

	public $options;
	public $icons_ids = array();

	function __construct( $options ) {

		// run initialization
		$this->init( $options );
		
		// Add shortcodes
		add_shortcode( $this->options['tag'], array( $this, 'add_shortcode' ) );

		// add specific options
		add_action("ct_toolbar_component_settings", array( $this, "icon_settings") );
		add_action("ct_svg_icons_list", 			array( $this, "icons_list") );

		// output svg sets
		add_action("wp_footer", array( $this, "output_all_svg_sets") );
		add_action("wp_footer", array( $this, "output_svg_set") );
	}


	/**
	 * Add a [ct_svg_icon] shortcode to WordPress
	 *
	 * @since 0.2.1
	 */

	function add_shortcode( $atts, $content ) {

		$options = $this->set_options( $atts );

		$this->icons_ids[] = $options['icon_id'];

		ob_start(); 

		?><svg id="<?php echo esc_attr($options['selector']); ?>" class="ct-<?php echo esc_attr($options['icon_id']); ?> <?php echo esc_attr($options['classes']); ?>"><use xlink:href="#<?php echo esc_attr($options['icon_id']); ?>"></use></svg><?php
		
		return ob_get_clean();
	}


	/**
	 * Output settings
	 *
	 * @since 0.2.1 
	 */

	function icon_settings() { ?>
	
		<div class="ct-toolitem" ng-show="isActiveName('<?php echo $this->options['tag']; ?>')">
			<h3><?php _e("Icon Set", "component-theme"); ?></h3>
			<div class="ct-selectbox ct-svg-sets-list">
				<ul class="ct-select oxygen-special-property">
					<li class="ct-selected">
						{{currentSVGSet}}
						<span class="ct-icon ct-dropdown-icon"></span>
					</li>
					<li>
						<ul class="ct-dropdown-list">
							<li ng-repeat="(name,set) in SVGSets" ng-click="setCurrentSVGSet(name);" title="<?php _e("Use this set", "component-theme"); ?>">
								{{name}}
							</li>
						</ul>
					</li>
				</ul>
			</div>
		</div>

		<div class="ct-toolitem" ng-show="isActiveName('<?php echo $this->options['tag']; ?>')">
			<h3><?php _e("Icon", "component-theme"); ?></h3>
			<div class="ct-selectbox">
				<ul class="ct-select ct-svg-icon-select ct-ui-disabled oxygen-special-property">
					<li class="ct-selected"
						ng-class="{'ct-active' : isActiveActionTab('SVGIcons')}">
						<input type="text" ng-model="iconFilter.title" placeholder="<?php _e("search...", "component-theme"); ?>." 
							ng-change="activateActionTab('SVGIcons');"
							ng-click="activateActionTab('SVGIcons');"
							>
						<span class="ct-icon ct-dropdown-icon" ng-click="switchActionTab('SVGIcons');"></span>
					</li>
				</ul>
			</div>
		</div>

	<?php }


	/**
	 * Output Icons list
	 *
	 * @since 0.2.1 
	 */

	function icons_list() { ?>

		<ul class="ct-svg-icons-grid">
			<li ng-repeat="icon in SVGSets[currentSVGSet].defs.symbol | filter:iconFilter" class="ct-svg-icon-item">
				<span title="{{icon.title}}"
					ng-click="setSVGIcon(icon['@attributes']['id'], icon['title'])">
					<svg class="ct-svg-icon">
						<use xlink:href="" ng-href="{{'#'+currentSVGSet.split(' ').join('')+icon['@attributes']['id']}}" ></use>
					</svg>
				</span>
			</li>
		</ul>
		<div class="ct-clearfix"></div>

	<?php }


	/**
	 * Output SVG sets available in install 
	 * only with icons used on the page
	 *
	 * @since 0.2.0
	 */

	function output_svg_set() {

		if ( defined("SHOW_CT_BUILDER") ) 
			return;

		$svg_sets = get_option("ct_svg_sets", array() );

		// loop all sets
		foreach ( $svg_sets as $set ) {

			$icons_to_remove = array();
			
			$svg = new SimpleXMLElement($set);

			if($svg->defs->symbol) {
				// loop all set icons

				foreach ( $svg->defs->symbol as $key => $symbol ) {

					$icon 		= (array)$symbol;
					$attributes = $icon["@attributes"];
					$icon_id 	= $attributes['id'];
					$view_box 	= explode(" ", $attributes['viewBox']);

					if ( in_array( $icon_id, $this->icons_ids ) ) {

						if ( $view_box[2] != $view_box[3] ) {
							echo "<style>";
							echo ".ct-".sanitize_text_field($attributes['id'])."{";
							echo "width:" . ($view_box[2] / $view_box[3]) . "em";	
							echo "}";
							echo "</style>\r\n";
						}
					}
					else {
						// remove not used icons to keep HTML output clean
						$icons_to_remove[] = $symbol;
					}
				};

				foreach ($icons_to_remove as $icon) {
				    unset($icon[0]);
				}


				// remove empty lines
				$output = str_replace("\r", "", $svg->asXML());
				$output = str_replace("\n", "", $output);

				echo $output;
			}
		}
	}


	/**
	 * Output all SVG sets available in install only for builder
	 *
	 * @since 0.2.0
	 */

	function output_all_svg_sets() {

		if ( !defined("SHOW_CT_BUILDER") ) 
			return;

		$svg_sets = get_option("ct_svg_sets", array() );

		

		foreach ( $svg_sets as $set ) {
			
			$svg = new SimpleXMLElement($set);

			// output only if it has valid defs and symbols
			if( isset($svg->defs) && isset($svg->defs->symbol)) {
				echo $set."\n";
			
				// output specific icon widths for some icons based on viewBox parameter
				echo "<style>";

				foreach ( $svg->defs->symbol as $icon ) {

					$icon 		= (array)$icon;
					$attributes = $icon["@attributes"];
					$view_box 	= explode(" ", $attributes['viewBox']);

					if ( $view_box[2] != $view_box[3] ) {
						echo ".ct-".sanitize_text_field($attributes['id'])."{";
						echo "width:" . ($view_box[2] / $view_box[3]) . "em";	
						echo "}";
					}
				}
				echo "</style>";
			}
		}
		
	}

}


// Create toolbar inctances
$button = new CT_SVG_Icon ( 

		array( 
			'name' 		=> 'Icon',
			'tag' 		=> 'ct_svg_icon',
			'params' 	=> array(
					array(
						"type" 			=> "colorpicker",
						"heading" 		=> __("Color"),
						"param_name" 	=> "color",
						"value" 		=> "",
					),
					array(
						"type" 			=> "measurebox",
						"heading" 		=> __("Font size"),
						"param_name" 	=> "font-size",
						"value" 		=> "32",
					),
					array(
						"param_name" 	=> "font-size-unit",
						"value" 		=> "px",
						"hidden"		=> true,
					),
					array(
						"type" 			=> "textfield",
						"param_name" 	=> "icon-id",
						"value" 		=> "FontAwesomeicon-thumbs-up",
						"hidden"		=> true,
						"css" 			=> false,
					),
				),
			/*'advanced' 	=> array(
					"positioning" => array(
						"values" 	=> array (
							'display' 	=> 'inline-block',
							)
					)
			)*/
		)
);

?>