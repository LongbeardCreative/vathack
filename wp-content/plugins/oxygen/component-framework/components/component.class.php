<?php 

Class CT_Component {

	var $options;
	var $css = "";
	var $font_families = array ();
	var $advanced_defaults = array (

			'positioning' => array (

				// margin padding
				"margin-top" 			=> "0",
				"margin-right" 			=> "0",
				"margin-bottom" 		=> "0",
				"margin-left" 			=> "0",

				"margin-top-unit" 		=> "px",
				"margin-right-unit" 	=> "px",
				"margin-bottom-unit" 	=> "px",
				"margin-left-unit" 		=> "px",
				
				"padding-top" 			=> "0",
				"padding-right" 		=> "0",
				"padding-bottom" 		=> "0",
				"padding-left" 			=> "0",

				"padding-top-unit" 		=> "px",
				"padding-right-unit" 	=> "px",
				"padding-bottom-unit" 	=> "px",
				"padding-left-unit" 	=> "px",

				// position
				"float"			=> "none",
				"overflow" 		=> "visible",
				"visibility"	=> "visible",
				"display"		=> "block",
				"clear"			=> "none",
				"position"		=> "static",

				"top" 			=> "",
				"left"			=> "",
				"right" 		=> "",
				"bottom" 		=> "",

				"top-unit" 		=> "px",
				"left-unit"		=> "px",
				"right-unit" 	=> "px",
				"bottom-unit" 	=> "px",

				// size
				"width" 			=> "",
				"min-width" 		=> "",
				"max-width" 		=> "",
	
				"height" 			=> "",
				"min-height" 		=> "",
				"max-height" 		=> "",

				"width-unit" 		=> "px",
				"min-width-unit" 	=> "px",
				"max-width-unit" 	=> "px",
	
				"height-unit" 		=> "px",
				"min-height-unit" 	=> "px",
				"max-height-unit" 	=> "px",

				),

			'border' => array (
				"border-top-width" 			=> "0",
				"border-top-width-unit"		=> "px",
				"border-top-style" 			=> "none",
				"border-top-color" 			=> "",
				
				"border-right-width" 		=> "0",
				"border-right-width-unit"	=> "px",
				"border-right-style" 		=> "none",
				"border-right-color" 		=> "",
				
				"border-bottom-width" 		=> "0",
				"border-bottom-width-unit"	=> "px",
				"border-bottom-style" 		=> "none",
				"border-bottom-color" 		=> "",
				
				"border-left-width" 		=> "0",
				"border-left-width-unit"	=> "px",
				"border-left-style" 		=> "none",
				"border-left-color" 		=> "",

				// fake property
				"border-all-width" 			=> "0",
				"border-all-width-unit"		=> "px",
				"border-all-style" 			=> "none",
				"border-all-color" 			=> "",

				// radius
				"border-top-right-radius" 			=> "0",
				"border-top-left-radius" 			=> "0",
				"border-bottom-right-radius" 		=> "0",
				"border-bottom-left-radius" 		=> "0",

				"border-top-right-radius-unit" 		=> "px",
				"border-top-left-radius-unit" 		=> "px",
				"border-bottom-right-radius-unit" 	=> "px",
				"border-bottom-left-radius-unit" 	=> "px",
				),

			'typography' => array (
				'font-family' 			=> 'Inherit',
				
				'font-size' 			=> '',
				'font-size-unit' 		=> 'px',
				
				'font-weight' 			=> '400',
				'font-style' 			=> 'normal',

				'text-align' 			=> '',
				'direction' 			=> 'ltr',
				
				'line-height' 			=> '',
				'line-height-unit' 		=> 'em',

				'letter-spacing' 		=> '',
				'letter-spacing-unit' 	=> 'px',
				
				'list-style-type' 		=> 'disc',
				'text-decoration' 		=> 'none',
				'text-transform' 		=> 'none',
				),

			'background' => array (
				// color
				'background-color' 				=> '',
				
				// image
				'background-image' 				=> '',
				'background-size' 				=> 'auto',
				'background-repeat' 			=> 'repeat',
				'overlay-color' 				=> '',

				'background-size-width'			=> '',
				'background-size-height'		=> '',
				'background-size-width-unit'	=> 'px',
				'background-size-height-unit'	=> 'px',
				
				// position
				'background-position-top' 		=> '',
				'background-position-left' 		=> '',
				'background-position-top-unit' 	=> 'px',
				'background-position-left-unit' => 'px',
				),

			'custom-css' => array (
				'custom-css' 	=> '',
				'custom-js' 	=> "/* %%ELEMENT_ID%% will be replaced with the element's ID (without #). */"
				)
			);

	/**
	 * Constructor
	 * 
	 */

	function __construct( $options ) {

		// run initialization
		$this->init( $options );
	}


	/**
	 * Component init
	 *
	 * @since 0.1.4
	 */
	
	function init( $options ) {
		
		$this->options = $options;

		if ( !( isset($options['advanced']) && is_array( $options['advanced'] ) )) {
			$options['advanced'] = array();
		}

		if ( $options['advanced'] !== false ) {

			$this->options['advanced'] = array_merge_recursive(
												array(
													"background" => array(
														"heading" 	=> __("Background", "component-theme"),
													),
													"positioning" => array(
														"heading" 	=> __("Position", "component-theme"),
													),
													"typography" => array(
														"heading" 	=> __("Typography", "component-theme")
													),
													"border" => array(
														"heading" 	=> __("Border", "component-theme")
													),
													"custom-css" => array(
														"heading" 	=> __("Custom CSS", "component-theme")
													),
												),
												$options['advanced']
											);
		} else {
			$this->options['advanced'] = array();
		}

		// collect all component css styles in footer
		if ( ! isset( $_GET['ct_builder'] ) || ! $_GET['ct_builder'] ) {
			add_action( "ct_footer_styles", array( $this, 'output_css' ) );
		}

		// add custom js
		add_action( "wp_footer", array( $this, 'add_custom_js' ) );

		// output main toolbar elements
		add_action("ct_toolbar_fundamentals_list", 		array( $this, "component_button") );
		add_action("ct_toolbar_component_header", 		array( $this, "component_header") );
		add_action("ct_toolbar_component_settings", 	array( $this, "component_settings") );
		
		add_filter("ct_component_default_params",  		array( $this, "init_default_params") );
		add_filter("ct_not_css_options",  				array( $this, "not_css_options") );
		add_filter("ct_components_nice_names",  		array( $this, "component_nice_name") );
	}


	/**
	 * Add a toolbar button
	 *
	 * @since 0.1 
	 */

	function component_button() {

		?>

		<div class="ct-add-component-button" 
			ng-click="addComponent('<?php echo $this->options['tag']; ?>')">
			<div class="ct-add-component-icon">
				<span class="ct-icon <?php echo $this->options['tag']; ?>-icon"></span>
			</div>
			<?php echo $this->options['name']; ?>
		</div>

		<?php
	}


	/**
	 * Echo ng attributes needed for component settings
	 *
	 * @since 0.1.7
	 */
	
	function ng_attributes( $param_name, $attributes = "class,model,change") { 

		$param_name = sanitize_text_field($param_name);
		
		if ( isset($this->options['shortcode']) && $this->options['shortcode'] ) {
			$shortcode_arg = ", true";
		}

		$attributes = explode(',', $attributes );
		
		if ( in_array('class-fake', $attributes) ) { ?>
			ng-class="checkOptionChanged(component.active.id,'<?php echo $param_name; ?>')"
		<?php }

		if ( in_array('model', $attributes) ) { ?>
			ng-model="component.options[component.active.id]['model']['<?php echo $param_name; ?>']" 
			ng-model-options="{ debounce: 10 }"
		<?php }

		if ( in_array('change', $attributes) ) { ?>
			ng-change="setOption(component.active.id,'<?php echo isset($this->options['tag'])?$this->options['tag']:''; ?>','<?php echo isset($param_name)?$param_name:''; ?>'<?php echo isset($shortcode_arg)?$shortcode_arg:''; ?>)"
		<?php }
				
	}


	/**
	 * Add component header settings
	 *
	 * @since 0.1 
	 */

	function component_header() { 

		if ( isset($this->options['shortcode']) && $this->options['shortcode'] ) {
			$shortcode_arg = ", true";
		}
		
		?>

		<?php if ( isset($this->options['params']) && $this->options['params'] ) : ?>
		<span ng-if="isActiveName('<?php echo $this->options['tag']; ?>')">

			<?php foreach ( $this->options['params'] as $param ) : 
				
				$ng_show = "";
				
				if ( isset($param['hidden']) && $param['hidden'] ) 
					continue;

				if ( isset($param['type']) && $param['type'] == "content" ) 
					continue;

				if ( isset($param['condition']) && $param['condition'] ) { 
					
					$condition = explode("=", $param['condition']);
					$key 	= $condition[0];
					$value 	= $condition[1];
					
					$ng_show = "ng-show=\"component.options[component.active.id]['model']['$key'] == '$value'\"";
				}

				if ( isset($param['type']) && $param['type'] == "columnwidth" ) {
					// hide columns width for media
					//$ng_show = "ng-hide=\"isEditing('media') && isActiveName('ct_column')\"";
				}

				if ( isset($param['param_name']) && $param['param_name'] == "gutter" ) {
					// hide columns spacing for media/state
					//$ng_show = "ng-hide=\"(isEditing('media')||isEditing('state'))&&isActiveName('ct_columns')\"";
				}

				$ct_class = "ct-" . (isset($this->options['tag'])?$this->options['tag']:'') . "-" . (isset($param['param_name'])?$param['param_name']:'');
			?>
			<div class="ct-toolitem <?php echo $ct_class; ?> <?php if($param['type']=='typography') echo 'ct-fontsdropdown'; ?>" <?php echo $ng_show; ?>>
				<h3><?php echo $param['heading']; ?></h3>
				<?php switch ( $param['type'] ) {
					
					case 'mediaurl' : ?>
						
						<div class="ct-textbox ct-textbox-fg ct-textbox-browse <?php if ( $param['css'] === false ) echo "oxygen-special-property"; ?>">
							<span class="ct-textbox-browse-butt ct-media-button" 
							data-mediaTitle="Select Image" 
							data-mediaButton="Select Image" 
							data-mediaProperty="src"
							<?php /*data-heightProperty="height"
							data-widthProperty="width" */?>>Browse</span>
							<input type="text" spellcheck="false"
								ng-class="isInherited(component.active.id, '<?php echo $param['param_name']; ?>')"
								<?php $this->ng_attributes($param['param_name']); ?>/>
						</div>
						<?php break;
					
					case 'textfield' : ?>
						<div class="ct-textbox <?php if ( isset ( $param["class"] ) ) echo $param["class"];?>">
							<input class="<?php if ( $param['css'] === false ) echo "oxygen-special-property"; ?>" type="text" spellcheck="false"
								ng-class="isInherited(component.active.id, '<?php echo $param['param_name']; ?>')"
								<?php $this->ng_attributes($param['param_name']); ?>/>
						</div>
						<?php break;

					case 'columnwidth' : ?>

						<div class="ct-measurebox-container clearfix"
							ng-class="{'ct-word-selected':getOptionUnit('<?php echo $param['param_name']; ?>')=='auto'}">
							<div class="ct-measurebox-wrap">
								<div class="ct-measurebox ct-column-width"
									ng-class="isInherited(component.active.id, '<?php echo $param['param_name']; ?>')">
									<input class="ct-measure-value ct-number-font <?php if ( $param['css'] === false ) echo "oxygen-special-property"; ?>" type="text" spellcheck="false"
										ng-change="setOption(component.active.id,'<?php echo $this->options['tag']; ?>','<?php echo $param['param_name']; ?>'); updateColumnsOnChange(component.active.id,{{component.options[component.active.id]['model']['width']}})"
										<?php $this->ng_attributes($param['param_name'],"class,model"); ?>/>
									<div class="ct-measure-type">{{getOptionUnit('<?php echo $param['param_name']; ?>')}}</div>
								</div>
							</div>
						</div>
						<?php break;

					case 'measurebox' : ?>
						
						<div class="ct-measurebox-container clearfix"
							ng-class="{'ct-word-selected':getOptionUnit('<?php echo $param['param_name']; ?>')=='auto'}">
							<div class="ct-measurebox-wrap <?php if ( isset($param['css']) && $param['css'] === false ) echo "oxygen-special-property"; ?>">
								<div class="ct-measurebox"
									ng-class="isInherited(component.active.id, '<?php echo $param['param_name']; ?>')">
									<input class="ct-measure-value ct-number-font " type="text" spellcheck="false"
										<?php $this->ng_attributes($param['param_name']); ?>
										ng-class="isInherited(component.active.id, '<?php echo $param['param_name']; ?>')" />
									<div class="ct-measure-type">{{getOptionUnit('<?php echo $param['param_name']; ?>')}}</div>
								</div>
								<?php CT_Toolbar::measure_type_select($param['param_name']); ?>
							</div>
						</div>

						<?php break;

					case 'dropdown' : ?>
						<div class="ct-selectbox">
							<ul class="ct-select <?php if ( $param['css'] === false ) echo "oxygen-special-property"; ?>">
								<li class="ct-selected"
									ng-class="isInherited(component.active.id, '<?php echo $param['param_name']; ?>')">
										{{getOption('<?php echo $param['param_name']; ?>')}}<span class="ct-icon ct-dropdown-icon"></span>
								</li>
								<li>
									<ul class="ct-dropdown-list">
										<?php foreach ( $param['value'] as $value => $name ) : ?>
											<li	ng-click="setOptionModel('<?php echo $param['param_name']; ?>','<?php echo $value; ?>')">
												<?php echo $name; ?>
											</li>
										<?php endforeach; ?>
									</ul>
								</li>									
						    </ul>
						</div>
						<?php break;

					case 'radio' : ?>
						<div class="<?php if ( $param['css'] === false ) echo "oxygen-special-property"; ?>"
							ng-class="isInherited(component.active.id, '<?php echo $param['param_name']; ?>')">
							<?php foreach ( $param['value'] as $value => $name ) : ?>
								<input type="radio" value="<?php echo $value; ?>"
									<?php $this->ng_attributes($param['param_name']); ?>
									><?php echo $name; ?>
							<?php endforeach; ?>
						</div>
						<?php break;

					case 'colorpicker' : ?>
						<div class="ct-colorpicker"
							ng-class="isInherited(component.active.id, '<?php echo $param['param_name']; ?>')">
							<input class="ct-color"
								readonly colorpicker="rgba" colorpicker-fixed-position="true" type="text" spellcheck="false"
								<?php $this->ng_attributes($param['param_name']); ?>
								ng-style="{'background-color':getOption('<?php echo $param['param_name']?>')}"/>
						</div>
						<?php break;

					case 'tag' : ?>
						<div class="ct-selectbox ct-heading-type-select">
							<ul class="ct-select oxygen-special-property">
								<li class="ct-selected"
									ng-class="isInherited(component.active.id, '<?php echo $param['param_name']; ?>')">
									{{getOption('<?php echo $param['param_name']; ?>')}}<span class="ct-icon ct-dropdown-icon"></span>
								</li>
								<li>
									<ul class="ct-dropdown-list">
										<?php foreach ( $param['value'] as $value => $name ) : ?>
										<li ng-click="setOptionModel('<?php echo $param['param_name']; ?>','<?php echo $value; ?>');changeTag(<?php if ($this->options['tag']=='ct_shortcode') echo "'shortcode'";?>)">
											<?php echo $name; ?>
										</li>
										<?php endforeach; ?>
									</ul>
								</li>									
						    </ul>
						</div>
						<?php break;
						
					case 'typography' : ?>
						<?php include('views/typography.view.php'); ?>
						<?php break;

					case 'align' : ?>
						<div class="ct-sml-button-list">
							<ul>
								<li ng-class="{'ct-active':component.options[component.active.id]['model']['<?php echo $param['param_name']?>']=='left'}">
									<label title="<?php _e( 'Justify Left', 'component-theme' ); ?>" class="clearfix">
										<input type="radio" <?php $this->ng_attributes($param['param_name']); ?> 
											name="<?php echo $this->options['tag'] ."_". $param['param_name']; ?>"
											value="left"
											ng-click="radioButtonClick('<?php echo $this->options['tag']; ?>', '<?php echo $param['param_name']; ?>', 'left');">
										<span class="ct-icon ct-paragraph-left-icon"></span></label>
								</li>
								<li ng-class="{'ct-active':component.options[component.active.id]['model']['<?php echo $param['param_name']?>']=='center'}">
									<label title="<?php _e( 'Justify Center', 'component-theme' ); ?>" class="clearfix">
										<input type="radio" <?php $this->ng_attributes($param['param_name']); ?> 
											name="<?php echo $this->options['tag'] ."_". $param['param_name']; ?>"
											value="center"
											ng-click="radioButtonClick('<?php echo $this->options['tag']; ?>', '<?php echo $param['param_name']; ?>', 'center');">
										<span class="ct-icon ct-paragraph-center-icon"></span></label>
								<li ng-class="{'ct-active':component.options[component.active.id]['model']['<?php echo $param['param_name']?>']=='right'}">
									<label title="<?php _e( 'Justify Right', 'component-theme' ); ?>" class="clearfix">
										<input type="radio" <?php $this->ng_attributes($param['param_name']); ?> 
											name="<?php echo $this->options['tag'] ."_". $param['param_name']; ?>"
											value="right"
											ng-click="radioButtonClick('<?php echo $this->options['tag']; ?>', '<?php echo $param['param_name']; ?>', 'right');">
										<span class="ct-icon ct-paragraph-right-icon"></span></label>
								</li>
							</ul>
						</div>
						<?php break;

					case 'padding' : ?>
						<table class="ct-toolbar-padding">
							<tr>
								<td>
									<input size="1" type="text" spellcheck="false"
										<?php $this->ng_attributes('padding-left'); ?> />
								</td>
								<td>
									<input size="1" type="text" spellcheck="false"
										<?php $this->ng_attributes('padding-top'); ?> /><br/>
									<input size="1" type="text" spellcheck="false"
										<?php $this->ng_attributes('padding-bottom'); ?> />
								</td>
								<td>
									<input size="1" type="text" spellcheck="false"spellcheck="false"
										<?php $this->ng_attributes('padding-right'); ?> />
								</td>
							</tr>
						</table>
						<?php break;

					case 'container-padding' : ?>
						<table class="ct-toolbar-padding">
							<tr>
								<td>
									<input size="1" type="text" spellcheck="false"
										<?php $this->ng_attributes('container-padding-left'); ?> />
								</td>
								<td>
									<input size="1" type="text" spellcheck="false"
										<?php $this->ng_attributes('container-padding-top'); ?> /><br/>
									<input size="1" type="text" spellcheck="false"
										<?php $this->ng_attributes('container-padding-bottom'); ?> />
								</td>
								<td>
									<input size="1" type="text" spellcheck="false"
										<?php $this->ng_attributes('container-padding-right'); ?> />
								</td>
							</tr>
						</table>
						<?php break;
					
					default : ?>
						<span><?php printf( __( 'Wrong parameter type: %s', 'component-theme' ), $param['type'] ); ?></span>
						<?php break;
				} ?>
			</div><!-- /.ct-toolitem -->
			<?php endforeach; ?>
		
		</span>
		<?php endif; ?>

	<?php }


	/**
	 * Add component header settings
	 *
	 * @since 0.3.0
	 */

	function component_settings() { ?>

		<span ng-if="isActiveName('<?php echo $this->options['tag']; ?>')">

			<?php if ( isset($this->options['content_editable_DISABLED']) && $this->options['content_editable_DISABLED'] ) : ?>
				<div class="ct-toolitem">
					<div class="ct-tab ct-tooltab-closed ct-button-outlined ct-content-edit-button"
						ng-click="switchActionTab('contentEditing');"
						ng-class="{'ct-active' : isActiveActionTab('contentEditing')}">
							{{!isActiveActionTab('contentEditing') ? 'Edit' : 'Done editing'}}
							<span class="ct-icon ct-elipsis-icon"></span>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( $this->options['advanced'] ) : ?>
				<div class="ct-toolitem ct-moretab">
					<div class="ct-tab" title="<?php _e( 'Advanced Settings', 'component-theme' ); ?>"
						ng-click="switchActionTab('advancedSettings');"
						ng-class="{'ct-tooltab-closed ct-button-outlined' : !isActiveActionTab('advancedSettings')}">
							<?php _e( 'More', 'component-theme' ); ?> 
							<span class="ct-icon ct-elipsis-icon"></span>
					</div>
				</div>
			<?php endif; ?>
			
			<?php if ( 	$this->options['tag'] != "ct_selector" &&
						$this->options['tag'] != "ct_widget" &&
						$this->options['tag'] != "ct_shortcode" &&
						$this->options['tag'] != "ct_code_block" ) : ?>
				<div class="ct-toolitem">
					<div class="ct-tab ct-link-button" title="<?php _e( 'Link Settings', 'component-theme' ); ?>"
						ng-click="processLink()"
						data-linkProperty="url"
						data-linkTarget="target"
						ng-class="{'ct-tooltab-closed ct-button-outlined' : !isActiveActionTab('linkSettings'), 'ct-link-button-off' : !getLinkId()}">
							<span class="ct-icon ct-link-icon"></span>
							<?php _e( 'Link', 'component-theme' ); ?><span class="ct-icon ct-asteriks-icon"></span>
					</div>
				</div>
			<?php endif; ?>
			
			<?php if (  isset($this->options['content_editable']) && $this->options['content_editable'] &&
						isset($this->options['tag']) && $this->options['tag'] != "ct_paragraph" && 
						1 == 2 /* Deprecated */ ) : ?>
				
				<?php if ( defined( "CT_TEMPLATE_EDIT" ) ) : ?>
					<span title="<?php _e( 'Data', 'component-theme' ); ?>" 
						ng-show="isActiveName('<?php echo $this->options['tag']; ?>')"
						ng-click="switchActionTab('templateTags');"
						ng-class="{'ct-active-tab' : isActiveActionTab('templateTags'), 'ct-highlight-tab' : getComponentTemplateTag()}"
						class="ct-action-tab">
					<i class="fa fa-database fa-lg"></i></span>
				<?php endif; ?>
			<?php endif; ?>
		</span>

	<?php }



	/**
	 * Get Component name-value pairs from options
	 * 
	 * @since 0.1.2
	 */

	function get_default_params($not_css = false) {

		$params = array();
		$advanced_params = array();

		if ( isset($this->options['params']) && is_array( $this->options['params'] ) ) {
		
			foreach ( $this->options['params'] as $param ) {

				if ( $not_css && $param['css'] !== false ) {
					continue;
				}
				
				// add name:value from each parameter
				if ( isset( $param['param_name'] ) && isset( $param['value'] ) ) {

					if ( is_array($param['value']) ) {
						reset( $param['value'] );
						$params[$param['param_name']] = key($param['value']);
					}
					else {
						$params[$param['param_name']] = $param['value'];
					}
				}
				// if combined option
				elseif ( isset( $param['values'] ) ) {
					
					foreach ( $param['values'] as $name => $value ) {
						$params[$name] 	= $value;
					}
				}
				// add from defaults if this parameter exist
				if ( isset($param['type']) && isset( $this->advanced_defaults[$param['type']] ) ) {

					$params = array_merge( 
									$this->advanced_defaults[$param['type']],
									$params
								);
				}
			}
		}

		foreach ( $this->options['advanced'] as $key => $param ) {

			if ( isset($param['exclude']) && $param['exclude'] ) {
				continue;
			} 

			// add from defaults if this parameter exist
			if ( isset( $this->advanced_defaults[$key] ) ) {
				
				$advanced_params = array_merge( 
									$advanced_params,
									$this->advanced_defaults[$key]
								);
			}
			
			// use values if provided by developer
			if ( isset($param['values']) && is_array ( $param['values'] ) ) {
				
				$advanced_params = array_merge( 
									$advanced_params,
									$param['values']
								);
			}
		}

		return array_merge( $params, $advanced_params );
	}


	/**
	 * Add default Component (shortocode) parameters 
	 * for Angular trough 'ct_component_default_params' filter hook
	 *
	 * @since 0.1 
	 */

	function init_default_params( $params ) {

		$defaults[$this->options['tag']] = $this->get_default_params();

		$combined = array_merge_recursive( $params, $defaults );

		return $combined;
	}


	/**
	 * Add not CSS options for each component to a list
	 * via add_filter("ct_not_css_options")
	 *
	 * @since 0.3.2
	 */
	
	function not_css_options( $params ) {

		if ( isset($this->options['params']) &&  is_array( $this->options['params'] ) ) {
		
			foreach ( $this->options['params'] as $param ) {

				if ( !isset($param['css']) || $param['css'] === false ) {
					$params[$this->options['tag']][] = isset($param['param_name'])?$param['param_name']:'';
				}
			}
		}

		return $params;
	}


	/**
	 * Replace "-" in array keys with "_"
	 * 
	 *
	 * @since 0.1.1
	 */

	function keys_dash_to_underscore( $array ) {

		$new_array = array();

		if ( is_array($array) ) {

			foreach ( $array as $key => $value ) {

				$new_key = str_replace( "-", "_", $key);
				$new_array[$new_key] = $value;
			}
		}

		return $new_array;
	}


	/**
	 * Replace "_" in array keys with "-"
	 * 
	 *
	 * @since 0.1.4
	 */

	function keys_underscore_to_dash( $array ) {

		$new_array = array();

		if ( is_array($array) ) {

			foreach ( $array as $key => $value ) {

				$new_key = str_replace( "_", "-", $key);
				$new_array[$new_key] = $value;
			}
		}

		return $new_array;
	}


	/**
	 * Add component nicename to ng-init
	 *
	 * @since 0.1.2
	 */

	function component_nice_name( $names ) {

		$name[$this->options['tag']] = $this->options['name'];

		$combined = array_merge( $names, $name );

		return $combined;
	}


	/**
	 * Get combined atributes and CSS styles
	 *
	 * @since 0.1.4
	 */
	
	function set_options( $atts ) {

		$atts['ct_options'] = str_replace("\n", "\\n", $atts['ct_options']);
		$atts['ct_options'] = str_replace("\r", "\\r", $atts['ct_options']);
		$atts['ct_options'] = str_replace("\t", "\\t", $atts['ct_options']);

		$atts = json_decode( $atts['ct_options'], true );

		// check if decoded properly
		if ( !$atts ) {
			return false;
		}
		
		$id 		= $atts["ct_id"];
		$selector 	= $atts['selector'];
		$states 	= array();

		// get states styles (original, :hover, ...) from shortcode atts
		foreach ( $atts as $key => $state_params ) {
			if ( is_array( $state_params ) ) {
				$states[$key] = $state_params;
			}
		}

		// lets base64 decode only custom-js and custom-css before rendering out the script and styles
		foreach($states as $key => $state) {

			if($key == 'classes')
				continue;

			if($key == 'media') {

				foreach($state as $mediakey => $mediaoption) {
					foreach($mediaoption as $mediastatekey => $mediastate) {
						if(isset($mediastate['custom-css']) && !strpos($mediastate['custom-css'], ' ')) {
							
							$states[$key][$mediakey][$mediastatekey]['custom-css'] = base64_decode($mediastate['custom-css']);
							
						}
						if(isset($mediastate['custom-js'])) {
							
							if(!strpos($mediastate['custom-js'], ' '))
								$states[$key][$mediakey][$mediastatekey]['custom-js'] = base64_decode($mediastate['custom-js']);

							// also add custom-js to the footer
							// no custom js for media
							/*$this->custom_js[implode("_", array($id, $key, $mediakey, $mediastatekey))] = array(
								"code" => $states[$key][$mediakey][$mediastatekey]['custom-js'],
								"selector" => $selector,
								);*/
						}
						if(is_pseudo_element($mediastatekey)) {
							$states[$key][$mediakey][$mediastatekey]['content'] = isset($mediastate['content'])?base64_decode($mediastate['content']):'';
						}
					}
				}
			}
			elseif(is_pseudo_element($key)) {
				//if(isset($states[$key]['content']) && !strpos($states[$key]['content'], ' '))
					$states[$key]['content'] = isset($states[$key]['content'])?base64_decode($states[$key]['content']):'';

				if(isset($states[$key]['custom-css']) && !strpos($states[$key]['custom-css'], ' '))
					$states[$key]['custom-css'] = base64_decode($states[$key]['custom-css']);
			}
			else {
				
				if(isset($states[$key]['custom-css']) && !strpos($states[$key]['custom-css'], ' '))
					$states[$key]['custom-css'] = base64_decode($states[$key]['custom-css']);
				
				if(isset($states[$key]['custom-js'])) {

					if(!strpos($states[$key]['custom-js'], ' '))
						$states[$key]['custom-js'] = base64_decode($states[$key]['custom-js']);

					// also add custom-js to the footer
					
					//$this->custom_js[implode("_", array($id, $key))] = array(
					// there shoudn't be custom js for states
					$this->custom_js[$id] = array(
						"code" => $states[$key]['custom-js'],
						"selector" => $selector,
						);
				}
			}
		}
		
		// copy states to use to build CSS
		$css_states = $states;

		// get defaults
		$default_atts = $this->get_default_params();
		
		if ( !isset($states['original']) || ! is_array( $states['original'] ) ) {
			$states['original'] = array();
		}
		
		// merge with defaults for shortcodes
		$states['original'] = array_merge( $default_atts, $states['original'] );

		// build regular CSS
		
		$styles = $this->build_css($css_states, $selector);

		// build media queries CSS
		if ( isset($css_states['media']) && is_array($css_states['media']) ) {
			foreach ( $css_states['media'] as $media_name => $css_states) {
				$media_css = $this->build_css($css_states, $selector, true);
				if ( $media_css ) {
					$this->media_queries[$selector][$media_name] = $media_css;
				}
			}
		}

		// add to instance
		$this->css .= $styles;

		$states['original'] = $this->keys_dash_to_underscore( $states['original'] );

		// add classes to return and use in shortcodes
		$states['original']['classes'] = str_replace( "_", "-", $this->options['tag'] );

		if ( isset($states['classes']) && is_array( $states['classes'] ) ) {
			$states['original']['classes'] .= " " . join($states['classes'], " ");
		}

		// add selector and id
		$states['original']['selector'] = $selector;
		$states['original']['id'] 		= $id;

		return $states['original'];
	}

	
	/**
	 * Build CSS string from states array
	 *
	 * @return string
	 * @since 0.3.2
	 */
	
	function build_css($states, $selector, $is_media = false) {

		// get page settings
		$page_settings = ct_get_page_settings( get_the_ID() );

		// get defaults
		$default_atts = $this->get_default_params();

		$paragraph = '';
		// add to css selector if paragraph
		if ( $this->options['tag'] == "ct_paragraph" ) {
			$paragraph = " p";
		}

		$fake_properties = array( 
			'overlay-color',
			'background-position-left', 
			'background-position-top',
			'background-size-width',
			'background-size-height',
			"container-padding-top",
			"container-padding-right",
			"container-padding-bottom",
			"container-padding-left",
			"section-width",
			"custom-width",
			'ct-content',
			"custom-css",
			"custom-js",
			"code-css",
			"code-php",
			"code-js",
			'target',
			'icon-id',
			"gutter",
			'tag',
			'url',
			'src',
			'alt',
			'border-all-color',
			'border-all-style',
			'border-all-width',
			'function-name',
			'friendly-name'
		);

		// init styles variable
		$styles = "";

		// loop trough states (original, :hover, ...) to get all CSS params
		
		foreach ( $states as $key => $atts ) {
			//echo $key."\n";
			if ( in_array($key, array("classes", "media", "name", "selector") ) ) {
				continue;
			}

			// convert "_" back to "-"
			$atts = $this->keys_underscore_to_dash( $atts );
			$key = str_replace("_", "-", $key);

			// start selector CSS
			$full_selector = ( $key != 'original') ? "#$selector$paragraph:$key{\r\n" : "#$selector $paragraph{\r\n";

			$selector_css = "";

			// handle units
			foreach ( $atts as $param => $value ) {

				// handle unit options
				if ( isset($default_atts[$param.'-unit']) && $default_atts[$param.'-unit'] ) {
					
					// check if unit set by user
					if ( isset( $atts[$param.'-unit'] ) ) {
						
						// set to auto
						if ( $atts[$param.'-unit'] == 'auto' ) {
							$atts[$param] = 'auto';
						}
						// set to saved or default
                    	else {
	                        $atts[$param] .= $atts[$param.'-unit'];
	                    }
					}
					// add default unit
					else {
						$atts[$param] .= $default_atts[$param.'-unit'];
					}
				}
				else {
                    if ( $atts[$param] == 'auto' ) {
                    	$param = str_replace("-unit", "", $param);
                        $atts[$param] = 'auto';
                    }
				}
			}

			// handle columns gutter (margin-right)
			if ( $this->options['tag'] == "ct_columns" ) {

				$gutter = $this->get_width($atts['gutter']);

				$styles .= ( $key != 'original') ? "#$selector .ct-column:$key{\r\n" : "#$selector .ct-column{\r\n";
				$styles .= "margin-left:" . ($gutter['value']/2) . $gutter['units'] . ";\r\n";
				$styles .= "margin-right:" . ($gutter['value']/2) . $gutter['units'] . ";\r\n";
				$styles .= "}\r\n";

				/*$styles .= ( $key != 'original') ? "#$selector .ct-columns-inner-wrap:$key{\r\n" : "#$selector .ct-columns-inner-wrap{\r\n";
				$styles .= "margin-left:-" . ($gutter['value']/2) . $gutter['units'] . ";\r\n";
				$styles .= "margin-right:-" . ($gutter['value']/2) . $gutter['units'] . ";\r\n";
				$styles .= "}\r\n";*/
			}

			// handle section container width
			if ( $this->options['tag'] == "ct_section" ) {
					
					$pre_styles = '';
					

					//if ( $atts['section-width'] == "" || $atts['section-width'] == "page-width" ) {
					//	$pre_styles .= "max-width" . ": " . $page_settings['max-width'] . ";\r\n";
					//}
					if ( isset($atts['section-width']) && $atts['section-width'] == "custom" && isset($atts['custom-width']) && $atts['custom-width'] ) {
						$pre_styles .= "max-width" . ": " . $atts['custom-width'] . ";\r\n";
					}
					if ( isset($atts['section-width']) && $atts['section-width'] == "full-width" ) {
						$pre_styles .= "max-width" . ": 100%;\r\n";
					}

					// handle container padding
					if ( isset($atts['container-padding-top']) ) {
						$pre_styles .= "padding-top" . ": " . $atts['container-padding-top'] . ";\r\n";
					}
					if ( isset($atts['container-padding-right']) ) {
						$pre_styles .= "padding-right" . ": " . $atts['container-padding-right'] . ";\r\n";
					}
					if ( isset($atts['container-padding-bottom']) ) {
						$pre_styles .= "padding-bottom" . ": " . $atts['container-padding-bottom'] . ";\r\n";
					}
					if ( isset($atts['container-padding-left']) ) {
						$pre_styles .= "padding-left" . ": " . $atts['container-padding-left'] . ";\r\n";
					}
				
				if($pre_styles != '') {
					$styles .= ( $key != 'original') ? "#$selector > .ct-section-inner-wrap:$key{\r\n" : "#$selector > .ct-section-inner-wrap{\r\n";
					$styles .= $pre_styles;
					$styles .= "}\r\n";
				}
			}

			// handle background-position option
			if ( (isset($atts['background-position-left']) && $atts['background-position-left']) || (isset($atts['background-position-top']) && $atts['background-position-top']) ) {

				$left = $atts['background-position-left'] ? $atts['background-position-left'] : "0%";
				$top  = $atts['background-position-top'] ? $atts['background-position-top'] : "0%";
				$atts['background-position'] = $left . " " . $top;
			}

			// handle background-size option
			if ( isset($atts['background-size']) && $atts['background-size'] == "manual" ) {

				$width = isset($atts['background-size-width']) ? $atts['background-size-width'] : "auto";
				$height = isset($atts['background-size-height']) ? $atts['background-size-height'] : "auto";
				$atts['background-size'] = $width . " " . $height;
			}

			$content_included = false;

			// loop trough properties (background, color, ...)
			foreach ( $atts as $prop => $value ) {					

				// skip units
				if ( strpos( $prop, "-unit") ) {
					continue;
				}

				// skip gutter
				if ( $prop == "gutter" && $this->options['tag'] == "ct_columns" )
					continue;

				if ( is_array( $value ) ) {
					// handle global fonts
					if ( $prop == "font-family" && $value[0] == 'global' ) {
						
						$settings 	= get_option("ct_global_settings"); 
						$value 		= $settings['fonts'][$value[1]];
					}
				}
				else {
					$value = htmlspecialchars_decode($value, ENT_QUOTES);
				}

				// skip empty values
				if ( $value === "" ) {
					continue;
				}

				if ( $prop != "custom-css" ) {

					// handle background image
					if ( $prop == "background-image" ) {
						
						$value = "url($value)";

						// trick for overlay
						if ( isset( $atts['overlay-color'] ) ) {
							$value = "linear-gradient(" . $atts['overlay-color'] . "," . $atts['overlay-color'] . "), " . $value;
						}
					}

					// skip fake properties
					if ( in_array( $prop, $fake_properties ) ) {
						continue;
					}

					
					if ( $prop == "font-family" ) {
						//$this->font_families[] = "$value";
						if ( strpos($value, ",") === false && strtolower($value) !== "inherit") {
							$value = "'$value'";
						}
					}

					// add quotes for content for :before and :after
					if ( $prop == "content" ) {
						//$value = addslashes( $value );
						$value = str_replace('"', '\"', $value);
						$value = "\"$value\"";
						$content_included = true;
					}

					// finally add property:value
					$selector_css .= "  ". $prop . ":" . $value . ";\r\n";
				}
			} // endforeach

			if ( !$content_included && ( $key=="before" || $key=="after" ) && !$is_media ) {
				$selector_css .= "  content:\"\";\r\n";
			}

			// add custom CSS to the end
			//$selector_css .= base64_decode( $atts["custom-css"] );
			$selector_css .= isset($atts["custom-css"])?$atts["custom-css"]:'';

			// add to styles if has any rules
			if ( $selector_css ) {
				$styles .=  $full_selector . $selector_css . "}\r\n";
			}
		}
		
		return $styles;
	}


	/**
	 * Echo all components CSS styles and Media Queries
	 *
	 * @since 0.1.6
	 */

	function output_css() {

		global $media_queries_list;
		
		// output regular CSS
		echo $this->css;
		
		// output Media Queries CSS
		if ( isset($this->media_queries) && $this->media_queries ) {

			echo "\n/* Media Queries Start */\n\n";

			foreach ( $media_queries_list as $media_name => $media ) {
				
				$max_width = $media_queries_list[$media_name]['maxSize'];

				foreach ( $this->media_queries as $selector => $media ) {

					if ( isset($media[$media_name]) ) {

						echo "@media (max-width: $max_width) {\n";
							echo $media[$media_name];
						echo "}\n\n";
					}
				}
			}

			echo "/* Media Queries End */\r\n\n";
		}
	}


	/**
	 * Echo custom JS code added by user
	 *
	 * @since 0.3.1
	 */

	function add_custom_js() {

		if ( isset($this->custom_js) && is_array( $this->custom_js ) ) {

			foreach ( $this->custom_js as $component_id => $custom_js ) {

				$default_atts = $this->get_default_params();
				
				if ( isset($default_atts['custom-js']) && isset($custom_js['code']) && $default_atts['custom-js'] == $custom_js['code'] ) {
					continue;
				}
				
				if ( ! defined("SHOW_CT_BUILDER") ) {
					//$selector 	= $this->options['selector']; ????
					$code 	= $custom_js['code'];
					$code 	= str_replace("%%ELEMENT_ID%%", $custom_js['selector'], $code);
					echo "<script type=\"text/javascript\" id=\"ct_custom_js_".sanitize_text_field($component_id)."\">";
						echo $code;
					echo "</script>\r\n";
				}
			}
		}
	}


	/**
	 * Get CSS width parameter and return value and units
	 *
	 * @since 0.2.3
	 */

	function get_width( $width ) {

		$value = $this->int_val( $width );
							
		if ( strpos( $width, "px") !== false ) {
			$units = "px";
		}

		if ( strpos( $width, "%") !== false ) {
			$units = "%";
		}

		if ( strpos( $width, "em") !== false ) {
			$units = "em";
		}

		if ( strpos( $width, "rem") !== false ) {
			$units = "rem";
		}

		return array(
			"value" => $value,
			"units" => $units
		);
	}

	function int_val( $str ) {
		return (int) preg_replace('/[^\-\d]*(\-?\d*).*/', '$1', $str );
	}

// End CT_Component class	
}