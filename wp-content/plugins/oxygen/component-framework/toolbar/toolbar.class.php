<?php 

/**
 * Toolbar Class
 *
 * @since 0.1
 */

Class CT_Toolbar {

	function CT_Toolbar() {

		add_action("wp", array( $this, "toolbar_init" ) );
	}

	function toolbar_init() {

		// TODO: check if user can edit this exact post?
		if ( current_user_can("edit_posts") && defined("SHOW_CT_BUILDER") ) {
			add_action("ct_before_builder", array( $this, "toolbar_view") );
		}

		global $oxygen_api;
		global $oxygen_add_plus;

		$this->folders = $oxygen_add_plus;

		$this->options['advanced'] = array(
											"background" => array (
												"heading" 	=> __("Background", "component-theme"),
												"children" 	=> 
													array (
														"color" 			=> __("Color", "component-theme"),
														"image" 			=> __("Image", "component-theme"),
														/*"gradient" 			=> __("Gradient", "component-theme"),*/
														"size" 				=> __("Tiling & Position", "component-theme"),
													)
											),

											"position" => array (
												"heading" 	=> __("Position & Size", "component-theme"),
												"children" 	=> 
													array (
														"margin_padding" 	=> __("Margin & Padding", "component-theme"),
														"position" 			=> __("Position", "component-theme"),
														"size" 				=> __("Size", "component-theme"),
													)
											),

											"typography" => array (
												"heading" 	=> __("Typography", "component-theme"),
											),

											"borders" => array (
												"heading" 	=> __("Borders", "component-theme"),
												"children" 	=> 
													array (
														"border" 		=> __("Border", "component-theme"),
														"radius" 		=> __("Radius", "component-theme"),
													)
											),
											
											"cssjs" => array (
												"heading" 	=> __("CSS & JavaScript", "component-theme"),
												"children" 	=> 
													array (
														"css" 			=> __("CSS", "component-theme"),
														"js" 			=> __("JavaScript", "component-theme"),
													)
											),			
										);

		//$this->options['advanced'] = apply_filters("ct_component_advanced_options", $this->options['advanced']);

		// include styles
		add_action("wp_enqueue_scripts", array( $this, "enqueue_scripts" ) );

		add_action("ct_builder_ng_init", array( $this, "init_folders" ) );

		// output main toolbar elements
		add_action("ct_toolbar_component_header",			array( $this, "component_header"), 1 );
		add_action("ct_toolbar_advanced_settings", 			array( $this, "advanced_settings") );
		add_action("ct_toolbar_advanced_anchors", 			array( $this, "advanced_settings_anchors") );
		add_action("ct_toolbar_advanced_child_anchors", 	array( $this, "advanced_settings_child_anchors") );

		add_action("ct_toolbar_code_editor_settings", 		array( $this, "code_editor_settings") );
		add_action("ct_toolbar_code_editor_anchors", 		array( $this, "code_editor_settings_anchors") );

		add_action("ct_toolbar_components_list",			array( $this, "components_list") );
		add_action("ct_toolbar_components_anchors", 		array( $this, "components_anchors") );

		add_action("ct_toolbar_reusable_parts", 			array( $this, "ct_reusable_parts") );
		
		add_action("ct_toolbar_page_settings", 				array( $this, "ct_show_page_settings" ) );
		add_action("ct_toolbar_global_settings", 			array( $this, "ct_show_global_settings") );
		add_action("ct_dialog_window", 						array( $this, "dialog_window") );
	}


	/**
	 * Enqueue scripts and styles
	 *
	 * @since 0.1.4
	 */

	function enqueue_scripts() {

		wp_enqueue_style ("ct-ui", 			CT_FW_URI . "/toolbar/UI/css/default.css");
		wp_enqueue_style ("ct-dom-treee", 	CT_FW_URI . "/toolbar/UI/css/domtree.css");
		//wp_enqueue_style ("ct-old-ui", 		CT_FW_URI . "/toolbar/toolbar.css");
	}


	/**
	 * Make folders structure availbale on frontend
	 *
	 * @since 0.4.0
	 */

	function init_folders() {

		$output = json_encode( $this->folders );
		$output = htmlspecialchars( $output, ENT_QUOTES );

		echo "folders=$output;";
	}


	/**
	 * Include toolbar view file
	 *
	 * @since 0.1.4
	 */

	function toolbar_view() {

		global $post;

		$shortlink = wp_get_shortlink( $post->ID, 'post' );

		require_once("toolbar.view.php");
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
			ng-change="setOption(component.active.id, component.active.name,'<?php echo $param_name; ?>'<?php echo isset($shortcode_arg)?$shortcode_arg:''; ?>)"
		<?php }
				
	}


	/**
	 * Selector box
	 * 
	 * @since 0.1.4
	 */
	
	function component_header() { ?>

		<span ng-show="component.active.name && component.active.name!='root' && component.active.name!='ct_inner_content' && !isEditing('style-sheet')">

			<span ng-if="isEditing('custom-selector')">
				
				<div class="ct-toolitem">
					<div class="ct-noheader">
						<div class="ct-button ct-control-button ct-icon-only" title="<?php _e( "Highlight elements on the page", "component-theme" ); ?>"
							ng-click="highlightSelector()">
							<span class="ct-icon ct-eye-icon"
								ng-class="{'ct-icon-striked':!selectorHighlighted}"></span>
						</div>
					</div>
				</div>
				
				<?php
				/*
				<div class="ct-toolitem">
					<div class="ct-noheader">
						<div class="ct-button ct-control-button ct-icon-only" title="<?php _e( "Choose Selector Box", "component-theme" ); ?>"
							ng-click="$parent.showChooseSelectorBox=!$parent.showChooseSelectorBox">
							<span class="ct-icon ct-eyedrop-icon"
								ng-class="{'ct-icon-striked':!$parent.showChooseSelectorBox}"></span>
						</div>
					</div>
				</div>
				*/
				?>

				<div class="ct-toolitem">
					<h3><?php _e( "Editing Selector", "component-theme" ); ?></h3>
					<div class="ct-selectbox ct-css-select ct-tags-select">
						<ul class="ct-select ct-custom-selector">
							<li class="ct-selected"> 
								<span class="ct-icon ct-media-icon"
									ng-class="'ct-media-'+getCurrentMedia()"></span>
								<input type="text" spellcheck="false"
									ng-class="{'ct-no-state':$parent.selectorToEdit.indexOf(':')>=0}"
									ng-model="$parent.selectorToEdit" ng-change="selectorChange('{{$parent.selectorToEdit}}')">
								<div class="ct-button ct-control-button ct-states-button"
									ng-if="$parent.selectorToEdit.indexOf(':')<0"
									ng-class="{'ct-state-hightlight':isStatesHasOptions()||currentState!='original'}">
									{{(currentState=="original") ? "state" : ":"+currentState}}
								</div>
							</li>
							<li class="ct-media-list">
								<ul class="ct-dropdown-list">
									<li ng-repeat="name in notSorted(mediaList)"
										ng-click="setCurrentMedia(name);">
											<span class="ct-icon ct-media-icon"
												ng-class="'ct-media-'+name"></span>
											<span
												ng-class="{'ct-glow-text':isHasMedia(name),'ct-bold-text':getCurrentMedia()==name}">
												{{getMediaTitle(name)}}
											</span>
											<span class="ct-icon ct-remove-icon" title="<?php _e("Remove media styles from component", "component-theme"); ?>" 
												ng-click="removeComponentMedia(name); event.stopPropagation()"
												ng-show="isHasMedia(name)"></span>
									</li>	
								</ul>
							</li>
							<li class="ct-states-list ct-states-select">
								<ul>
									<li>
										<ul class="ct-dropdown-list">
											<li title="<?php _e("Edit original state", "component-theme"); ?>"
												ng-click="switchState('original');">
													<?php _e("original", "component-theme"); ?>
											</li>
											<li title="<?php _e("Edit this state", "component-theme"); ?>"
												ng-repeat="state in getComponentStatesList()"
												ng-click="switchState('original'); switchState(state);"
												ng-class="{'ct-glow-text':isStateHasOptions(state)}">
													:{{state}}
													<span class="ct-icon ct-remove-icon"
														title="<?php _e("Remove state from selector", "component-theme"); ?>"
														ng-click="tryDeleteComponentState(state,$event)"></span>
													</span>
											</li>
										</ul>
									</li>
									<li ng-click="addState()" class="ct-selectbox-add-item">
										<span class="ct-selectbox-add-item-inner">
											<?php _e("add state...", "component-theme"); ?>
										</span>
									</li>
								</ul>
							</li>
						</ul>
					</div>
				</div>

				<div class="ct-toolitem" ng-if="isEditing('pseudo-element')&&isEditing('custom-selector')">
					<div class="ct-textbox ct-noheader">
						<input type="text" class="ct-expand ct-no-animate" placeholder="<?php _e("content...", "component-theme"); ?>" spellcheck="false"
							ng-model="component.options[component.active.id]['model']['content']"
							ng-change="setOption(component.active.id,component.active.name,'content')"/>
					</div>
				</div>

				<div class="ct-toolitem" ng-show="!isEditing('class')">
					<h3><?php _e( "Friendly Name", "component-theme" ); ?></h3>
					<div class="ct-textbox ct-textbox-big">
						<input type="text" ng-model="$parent.customSelectors[$parent.selectorToEdit]['friendly_name']">
					</div>
				</div>

				<div class="ct-toolitem" ng-show="!isEditing('class')">
					<h3><?php _e( "Style Set", "component-theme" ); ?></h3>
					<div class="ct-selectbox ct-css-select ct-style-set-dropdown">
						<ul class="ct-select">
							<li class="ct-selected">{{$parent.customSelectors[$parent.selectorToEdit]['set_name']}}<span class="ct-icon ct-dropdown-icon"></span></li>
							<li class="ct-selectbox-add-item-selector">
					        	<input type="text" class="ct-new-component-class-input" placeholder="Enter style set name..." 
					        	ng-model="$parent.newStyleSetName"
					        	ng-keypress="processStyleSetNameInput($event)">
					        	<span class="ct-selectbox-add-item-inner" ng-click="addNewStyleSet()">
									add style set</span>
							</li>
							<li>
								<ul class="ct-dropdown-list">
									<li ng-repeat="set in styleSets"
										ng-click="setSelectorStyleSet(set);">
										<span class="ct-class-name" title="<?php _e("Use this style set", "component-theme"); ?>">
											{{set}}
										</span>
									</li>
								</ul>
							</li>
					    </ul>
					</div>
				</div>
			</span>
			<span ng-if="isActiveName('ct_widget')">		
				<div class="ct-toolitem">
					<h3><?php _e( "Editing Widget", "component-theme" ); ?></h3>
					<div class="ct-textbox ct-textbox-big">
						<input type="text" ng-value="getOption('pretty_name')" readonly>
					</div>
				</div>
			</span>

			<span class="ct-no-animate" ng-hide="isEditing('custom-selector')">

				<div class="ct-toolitem">
					<h3 class='ct-currentlyeditingtext'><?php _e("Currently Editing", "component-theme"); ?> <small><strong>{{ niceNames[component.active.name] }}</strong><span class='ct-htmltagofcmp'> &lt; {{ htmlTags[component.active.name] }} &gt;</span></small> <span id="ct-up-level-button"></span></h3>
					<div class="ct-selectbox ct-css-select ct-tags-select">
						<ul class="ct-select">
							<li class="ct-selected ct-choose-selector" 
								ng-if="isNotSelectedYet(component.active.id)"
								ng-click="onSelectorDropdown()">
								<span class="ct-selected-none"><?php _e( "Choose selector to edit...", "component-theme" ); ?></span><span class="ct-icon ct-dropdown-icon"></span>
							</li>
							<li class="ct-selected" 
								ng-if="isEditing('id') && !isNotSelectedYet(component.active.id)">
								<span class="ct-icon ct-media-icon"
									ng-class="'ct-media-'+getCurrentMedia()"></span>
								<span class="ct-tag ct-id-tag">id</span>
								<input type="text" spellcheck="false" 
									ng-model="component.options[component.active.id]['selector']"
									ng-change="setOption(component.active.id, component.active.name, 'selector')">
								<div class="ct-button ct-control-button ct-states-button"
									ng-class="{'ct-state-hightlight':isStatesHasOptions()||currentState!='original'}">
									{{(currentState=="original") ? "state" : ":"+currentState}}
								</div>
							</li>
							<li class="ct-selected" 
								ng-if="isEditing('class') && !isNotSelectedYet(component.active.id)"
								ng-click="showClasses=!showClasses">
								<span class="ct-icon ct-media-icon"
									ng-class="'ct-media-'+getCurrentMedia()"></span>
								<span class="ct-tag ct-class-tag">class</span>
								<input type="text" spellcheck="false"
									ng-model="currentClass">
								<div class="ct-button ct-control-button ct-states-button"
									ng-class="{'ct-state-hightlight':isStatesHasOptions()||currentState!='original'}">
									{{(currentState=="original") ? "state" : ":"+currentState}}
								</div>
							</li>
							<li class="ct-selectbox-add-item-selector">
					        	<input	type="text" 
					        			name="ct-new-component-class"
					        			class="ct-new-component-class-input"
					        			placeholder="<?php _e( "Enter class name...", "component-theme" ); ?>"
					        			ng-model="newcomponentclass.name"
					        			ng-keypress="processClassNameInput($event, component.active.id)" 
					        			focus-me="ctSelectBoxFocus" />

					        	<span 	class="ct-selectbox-add-item-inner" 
					        			ng-click="tryAddClassToComponent(component.active.id)">
									<?php _e("add class...", "component-theme"); ?>
								</span>
							</li>
							<li class="ct-classes-list">
								<ul class="ct-dropdown-list">
									<li ng-click="switchEditToId(true)">
										<span class="ct-tag ct-id-tag">id</span>
										<span class="ct-tag-name">{{getComponentSelector()}}</span>
									</li>
									<li ng-repeat="(key,className) in componentsClasses[component.active.id]"
										title="<?php _e("Edit this class", "component-theme"); ?>"
										ng-click="setCurrentClass(className)">
											<span class="ct-tag ct-class-tag">class</span>
											<span class="ct-tag-name">{{className}}</span>
											<span class="ct-icon ct-remove-icon"
												title="<?php _e("Remove class from component", "component-theme"); ?>"
												ng-click="removeComponentClass(className)"></span>
									</li>
							    </ul>
							</li>
							<li class="ct-media-list">
								<ul class="ct-dropdown-list">
									<li ng-repeat="name in notSorted(mediaList)"
										ng-click="setCurrentMedia(name);">
											<span class="ct-icon ct-media-icon"
												ng-class="'ct-media-'+name"></span>
											<span
												ng-class="{'ct-glow-text':isHasMedia(name),'ct-bold-text':getCurrentMedia()==name}">
												{{getMediaTitle(name)}}
											</span>
											<span class="ct-icon ct-remove-icon" title="<?php _e("Remove media styles from component", "component-theme"); ?>" 
												ng-click="removeComponentMedia(name); event.stopPropagation()"
												ng-show="isHasMedia(name)"></span>
									</li>	
								</ul>
							</li>
							<li class="ct-states-list ct-states-select">
								<ul>
									<li>
										<ul class="ct-dropdown-list">
											<li title="<?php _e("Edit original state", "component-theme"); ?>"
												ng-click="switchState('original');">
													<?php _e("original", "component-theme"); ?>
											</li>
											<li title="<?php _e("Edit this state", "component-theme"); ?>"
												ng-repeat="state in getComponentStatesList()"
												ng-click="switchState('original'); switchState(state);"
												ng-class="{'ct-glow-text':isStateHasOptions(state)}">
													:{{state}}
													<span class="ct-icon ct-remove-icon"
														title="<?php _e("Remove state from component", "component-theme"); ?>"
														ng-click="tryDeleteComponentState(state,$event)"></span>
													</span>
											</li>
										</ul>
									</li>
									<li ng-click="addState()" class="ct-selectbox-add-item">
										<span class="ct-selectbox-add-item-inner">
											<?php _e("add state...", "component-theme"); ?>
										</span>
									</li>
								</ul>
							</li>
						</ul>
					</div>
				</div>
			</span>
			
			<div class="ct-toolitem" ng-if="isEditing('pseudo-element')&&!isEditing('custom-selector')">
				<div class="ct-textbox ct-noheader">
					<input type="text" class="ct-expand ct-no-animate" placeholder="<?php _e("content...", "component-theme"); ?>" spellcheck="false"
						ng-model="component.options[component.active.id]['model']['content']"
						ng-change="setOption(component.active.id,component.active.name,'content')"/>
				</div>
			</div>

		</span>

	<?php }


	/**
	 * Add component advanced settings anchors
	 *
	 * @since 0.1.1
	 */

	function advanced_settings_anchors() { 

		foreach ( $this->options['advanced'] as $key => $tab ) : 

			if ( $key == "cssjs" ) {
				$ng_click = "possibleSwitchToCodeEditor('advanced', '$key')";
			}
			else {
				$ng_click = "switchTab('advanced', '$key');";
			}

			$no_children = ( !isset($tab['children']) || !$tab['children'] ) ? "ct-panel-tab-no-subtabs" : "";

			?>
			<div class="ct-panel-tab ct-button <?php echo $no_children; ?>"
				ng-click="<?php echo $ng_click; ?>"
				ng-class="{'ct-active' : isShowTab('advanced','<?php echo $key; ?>')}">
					<span class="ct-icon ct-<?php echo $key; ?>-icon"></span> 
					<?php echo $tab['heading']; ?>
				<span class="ct-tab-indicator"
					ng-show="isTabHasOptions('<?php echo $key; ?>')"></span>
			</div>
			<?php 

		endforeach;
	}


	/**
	 * Add component advanced settings anchors
	 *
	 * @since 0.1.1
	 */

	function advanced_settings_child_anchors() { 

		foreach ( $this->options['advanced'] as $key => $tab ) : 

			if ( isset($tab['children']) && $tab['children'] ) : ?>

			<div class="ct-child-panel-tabs"
				ng-show="isShowTab('advanced','<?php echo $key; ?>')">
			
				<?php foreach ( $tab['children'] as $child_key => $child_tab ) : 
					
					$ng_hide = "";
					
					if ( $child_key == "js" && $key == "cssjs" ) {
						//$ng_hide = 'ng-hide="isEditing(\'media\')||isEditing(\'class\')||isEditing(\'state\')"';
						$ng_click = 'setEditingStateToDefault()';
					}

				?>

					<div class="ct-child-panel-tab ct-button ct-child-panel-tab-<?php echo $child_key; ?>"
						ng-click="switchChildTab('advanced', '<?php echo $key; ?>', '<?php echo $child_key; ?>'); <?php echo isset($ng_click)?$ng_click:''; ?>"
						ng-class="{'ct-active' : isShowChildTab('advanced', '<?php echo $key; ?>', '<?php echo $child_key; ?>')}">
							<span class="ct-bullet"></span>
							<?php echo $child_tab; ?>
						<span class="ct-tab-indicator"
							ng-show="isTabHasOptions('<?php echo $key; ?>', '<?php echo $child_key; ?>')"></span>
					</div>

				<?php endforeach; ?>
			</div>

			<?php  endif;
		endforeach;
	}


	/**
	 * Add component advanced settings tabs
	 *
	 * @since 0.1.1
	 */

	function advanced_settings() { 

		foreach ( $this->options['advanced'] as $key => $tab ) : 

			if ( strpos( $key, "code" ) !== false || strpos( $key, "cssjs" ) !== false ) {
				$classes = "ct-code-mirror-panel";
			}

			if ( isset($tab['children']) && $tab['children'] ) :

				foreach ( $tab['children'] as $child_key => $child_tab ) : ?>
					
					<div class="ct-panel clearfix <?php echo isset($classes)?$classes:''; ?> <?php echo $key.'-'.$child_key; ?>" ng-if="isShowChildTab('advanced', '<?php echo $key; ?>', '<?php echo $child_key; ?>')">
						<?php if ( file_exists( CT_FW_PATH . "/toolbar/views/$key/$key.$child_key.view.php" ) ) :
							include( "views/$key/$key.$child_key.view.php");
						else : ?>			
							<span><?php printf( __( 'Wrong parameter type: %s', 'component-theme' ), "$key.$child_key" ); ?></span>
						<?php endif; ?>
					</div>

				<?php endforeach; 

			else : ?>

				<div class="ct-panel clearfix <?php echo isset($classes)?$classes:''; ?> <?php echo $key ;?>" ng-if="isShowTab('advanced', '<?php echo $key; ?>')">
					<?php if ( file_exists( CT_FW_PATH . "/toolbar/views/$key.view.php" ) ) :
						include( "views/$key.view.php");
					else : ?>			
						<span><?php printf( __( 'Wrong parameter type: %s', 'component-theme' ), "$key" ); ?></span>
					<?php endif; ?>
				</div>

			<?php endif;
		
		endforeach;
	}


	/**
	 * Add component code editor settings anchors
	 *
	 * @since 1.3
	 */

	function code_editor_settings_anchors() {

		$code_editor_tabs =
			array (	"code-php"		=> __("PHP & HTML", "component-theme"),
					"code-css" 		=> __("CSS", "component-theme"),
					"code-js" 		=> __("JavaScript", "component-theme") );

		foreach ( $code_editor_tabs as $key => $tab ) : ?>
			
			<div class="ct-panel-tab ct-button ct-panel-tab-no-subtabs"
				ng-click="switchTab('codeEditor', '<?php echo $key; ?>');"
				ng-class="{'ct-active' : isShowTab('codeEditor','<?php echo $key; ?>')}">
					<span class="ct-icon ct-<?php echo $key; ?>-icon"></span> 
					<?php echo $tab; ?>
				<span class="ct-tab-indicator"
					ng-show="isTabHasOptions('<?php echo $key; ?>')"></span>
			</div>
		
		<?php endforeach;
	}


	/**
	 * Add code block PHP, CSS, JS ediotrs
	 *
	 * @since 1.3
	 */

	function code_editor_settings() {

		$code_editor_tabs =
			array (	"code-php"		=> __("PHP & HTML", "component-theme"),
					"code-css" 		=> __("CSS", "component-theme"),
					"code-js" 		=> __("JavaScript", "component-theme") );

		foreach ( $code_editor_tabs as $key => $tab ) : ?>

			<div class="ct-panel clearfix ct-code-mirror-panel" ng-if="isShowTab('codeEditor', '<?php echo $key; ?>')">
				<?php if ( file_exists( CT_FW_PATH . "/toolbar/views/$key.view.php" ) ) :
					include( "views/$key.view.php");
				endif; ?>
			</div>
		
		<?php endforeach;
	}


	/**
	 * Output Global Settings
	 *
	 * @since 0.1.9
	 */

	function ct_show_global_settings() { 

		$global_settings = ct_get_global_settings(); 

		$settings = array(
				"fonts" => array(
						'title' 	=> __("Default Fonts", "component-theme"),
						'options' 	=> $global_settings["fonts"]
					)
				);
		?>

		<?php foreach ( $settings as $name => $section ) : ?>
			<?php echo sanitize_text_field($section['title']); ?><br/>
			<table class="ct-global-fonts ct-settings">
				<tr ng-repeat="(name,font) in globalSettings.fonts">
					<td>
						<span>{{name}}</span>
					</td>
					<td>
						<div class="ct-selectbox ct-font-family-select ct-select-search-enabled">
							<ul class="ct-select">
								<li class="ct-selected">{{globalSettings.fonts[name]}}<span class="ct-icon ct-dropdown-icon"></span></li>
								<li class="ct-searchbar">
									<div class="ct-textbox">
										<input ng-model="fontsFilter" type="text" value="" placeholder="<?php _e("Search...", "component-theme"); ?>" spellcheck="false"/>
									</div>
								</li>
								<li>
									<ul class="ct-dropdown-list">
										<li ng-repeat="font in typeKitFonts | filter:fontsFilter | limitTo: 20"
											ng-click="setGlobalFont(name, font.slug);"
											title="<?php _e("Apply this font family", "component-theme"); ?>">
												{{font.name}}
										</li>
										<li ng-repeat="font in fontsList | filter:fontsFilter | limitTo: 20"
											ng-click="setGlobalFont(name,font);">
											<span class="ct-class-name" title="<?php _e("Apply this font family", "component-theme"); ?>">
												{{font}}
											</span>
										</li>
									</ul>
								</li>
						    </ul>
						</div>
					</td>
					<td>
						<div class="ct-button ct-control-button ct-block-left-button"
							ng-show="name!='Display'&&name!='Text'"
							ng-click="deleteGlobalFont(name)">
								<i title="<?php _e("Delete font", "component-theme"); ?>" class="fa fa-trash fa-lg"></i>
						</div>
					</td>
				</tr>
				<tr>
					<td>
						<div class="ct-button ct-control-button ct-block-left-button" ng-click="addGlobalFont()">
							<?php _e("Add font", "component-theme"); ?>
						</div>
					</td>
					<td></td>
					<td></td>
				</tr>
			</table>
		<?php endforeach; ?>

	<?php }


	/**
	 * Components Browser tabs anchors
	 *
	 * @since 0.2.3
	 */

	function components_anchors() {  

		?>

		<div class="ct-panel-tab ct-button ct-panel-tab-no-subtabs"
			ng-click="switchTab('components', 'fundamentals');"
			ng-class="{'ct-active' : isShowTab('components','fundamentals')}">
				<span class="ct-icon ct-no-icon"></span> 
				<?php _e("Fundamentals", "component-theme") ?>
			<span class="ct-tab-indicator"
				ng-show="isTabHasOptions('fundamentals')"></span>
		</div>

		<?php $this->output_top_folders_anchors( array(
												"wordpress" => array(
																"name" 	=> "WordPress",
																"children" => array(
																		array(
																			"name" 	=> "Widgets",
																			"id" 	=> "widgets"
																			)
																	)
																) 
												) ); ?>

		<div class="ct-panel-tab ct-button ct-panel-tab-no-subtabs"
			ng-click="switchTab('components', 'reusable_parts');"
			ng-class="{'ct-active' : isShowTab('components','reusable_parts')}">
				<span class="ct-icon ct-no-icon"></span> 
				<?php _e("Re-usable", "component-theme") ?>
			<span class="ct-tab-indicator"
				ng-show="isTabHasOptions('reusable_parts')"></span>
		</div>

		<?php 
			if ( $this->folders["status"] == "ok" ) {
				$this->output_top_folders_anchors( $this->folders ); 
			}
			/*elseif (!get_option('oxygen_license_key')) {
				// do nothing
			}
			elseif ( $this->folders["status"] == "error" && isset($this->folders["message"])) {
				echo "<span class=\"ct-folders-anchors-error\">".sanitize_text_field($this->folders["message"])."</span>";
			}
			elseif ( $this->folders["status"] == "error" && is_array($this->folders["errors"])) {
				echo "<span class=\"ct-folders-anchors-error\">".sanitize_text_field($this->folders["errors"][0])."</span>";
			}
			else {
				var_dump( $this->folders );
			}*/
		?>

		<?php
	}


	/**
	 * Output all top folders as tab anchors to Add+ section
	 *
	 * @since 0.4.0
	 * @author Ilya K.
	 */
	
	function output_top_folders_anchors( $folders ) {

		if ( !is_array( $folders ) )
			return;

		unset($folders["status"]);

		foreach ( $folders as $key => $folder ) : 

			$slug = (isset($folder["name"]) ? sanitize_title($folder["name"]):'') . "-" . (isset($folder["id"])?$folder["id"]:''); ?>
			
			<div class="ct-panel-tab ct-button ct-panel-tab-no-subtabs"
				ng-click="switchTab('components', '<?php echo sanitize_text_field($key); ?>');openFolder('<?php echo $slug; ?>')"
				ng-class="{'ct-active' : isShowTab('components','<?php echo sanitize_text_field($key); ?>')}">
					<span class="ct-icon ct-no-icon"></span>
					<?php echo sanitize_text_field($folder["name"]); ?>
			</div>

		<?php endforeach;
	}


	/**
	 * Recursively output all folders' content
	 *
	 * @since 0.4.0
	 * @author Ilya K.
	 */
	
	function output_folders_content( $folders, $main_key, $title, $path="" ) {

		if ( !is_array( $folders ) )
			return;

		unset($folders["status"]);

		if ( $main_key && $path ) {
			$path .= " > " . "<span class=\"ct-folders-breadcrumb\" ng-click=\"openFolder('$main_key')\">$title</span>";
		}
		elseif ( $main_key ) {
			$path .= "<span class=\"ct-folders-breadcrumb\" ng-click=\"openFolder('$main_key')\">$title</span>";
		}

		global $folder_type;
		global $folder_class;

		foreach ( $folders as $key => $folder ) : 

			if ( !is_array( $folder ) )
				continue;

			$slug = (isset($folder["name"]) ? sanitize_title($folder["name"]):'') . "-" . (isset($folder["id"])?$folder["id"]:'');
			if ( isset($folder["id"]) && ($folder["id"] === "design_sets" || $folder["id"] === "components" || $folder["id"] === "pages") ) {
				$folder_type = isset($folder["id"])?$folder["id"]:false;
				$folder_class = "ct-api-items";
			}

			if ( $folder["name"] === "WordPress" ) {
				$folder_class = "";
			}

			?>

			<div class="ct-panel ct-folder-<?php echo $slug; ?> <?php echo $folder_class; ?>" ng-if="isShowFolder('<?php echo $slug; ?>')">
				
				<div class="ct-folders-breadcrumbs">
					<?php echo ( $path ) ? $path . " > " : ""; ?>
					<?php echo sanitize_text_field($folder["name"]); ?>
				</div>

				<?php if ( isset($folder["id"]) && $folder["id"] === "widgets" ) : ?>
					<?php do_action("ct_toolbar_widgets_folder"); ?>
				<?php else : ?>

					<?php if ( isset($folder["name"]) && $folder["name"] === "WordPress" ) : ?>
						<?php do_action("ct_folder_component_shortcode"); ?>
					<?php endif; ?>

					<?php if ( isset($folder["children"]) && $folder["children"] ) : ?>
						<?php foreach ( $folder["children"] as $subkey => $subfolder ) : 
							$subslug = sanitize_title($subfolder["name"]) . "-" . $subfolder["id"];
						?>

							<?php if ( isset($subfolder["component"]) && $subfolder["component"] ) : ?>
								<?php do_action("ct_folder_component_" . $subslug); ?>
							<?php else : ?>
								<div class="ct-open-folder-button" ng-click="openFolder('<?php echo $subslug; ?>')">
									<div class="ct-open-folder-icon">
										<span class="ct-icon"></span>
									</div>
									<?php echo sanitize_text_field( $subfolder["name"]) ; ?>
								</div>
							<?php endif; ?>

						<?php endforeach; ?>
					<?php endif; ?>

					<?php if ( isset($folder["items"]) && $folder["items"] ) : ?>
						<?php foreach ( $folder["items"] as $subkey => $subfolder ) : 
							if ( empty( $subfolder ) || ! is_array( $subfolder ) ) {
								continue;
							}
							$subslug = sanitize_title( $subfolder["name"] ) . "-" . $subfolder["id"];

							// update screenshot to use imgix
							if ( $subfolder["screenshot_url"] && strpos( $subfolder["screenshot_url"], "s3.amazonaws.com") !== false ) {
								$subfolder["screenshot_url"] = str_replace(
																	"https://s3.amazonaws.com/asset-dev-testing/", 
																	"https://oxygen.imgix.net/", $subfolder["screenshot_url"]);
								$subfolder["screenshot_url"] .= "?w=520";
							}
						?>

							<?php if ( isset ( $subfolder["component"] ) ) : ?>
								<?php do_action("ct_folder_component_".$subslug ); ?>
							<?php else : ?>
								<div class="ct-add-item-button"
									ng-click="showAddItemDialog(<?php echo sanitize_text_field($subfolder["id"]); ?>, '<?php echo sanitize_text_field($folder["type"]); ?>', '<?php echo sanitize_text_field($folder["id"]); ?>', '<?php echo sanitize_text_field($folder_type); ?>')">
									<div class="ct-add-item-name">
										<span class="ct-add-item-title"><?php echo sanitize_text_field( $subfolder["name"] ); ?></span>
										<span class="ct-add-item-design-label"><?php echo sanitize_text_field( $subfolder["design_set_name"] ); ?></span>
										<span class="ct-add-item-icon" title="<?php _e("Add now","component-theme")?>"
											ng-click="addItem(<?php echo sanitize_text_field($subfolder["id"]); ?>, '<?php echo sanitize_text_field($folder["type"]); ?>', $event)"></span>
									</div>
									<img class="ct-add-item-button-image" data-src="<?php echo esc_url($subfolder["screenshot_url"]); ?>">
								</div>
							<?php endif; ?>

						<?php endforeach; ?>
					<?php endif; ?>
					
					<?php if ( isset($folder["id"]) && $folder["id"] === "design_sets" ) : ?>
						
						<div class="ct-add-component-button" ng-if="isDev()" ng-click="showCreateDesignSet()">
							<div class="ct-add-component-icon">
								<span class="ct-icon"></span>
							</div>
							<?php echo "Add Design Set..."; ?>
						</div>

					<?php endif; ?>

				<?php endif; ?>
			</div>

			<?php $this->output_folders_content( isset($folder["children"])?$folder["children"]:null, $slug, isset($folder["name"])?$folder["name"]:null, $path ); ?>

		<?php endforeach;
	}


	/**
	 * Components Browser tabs 
	 *
	 * @since 0.2.3
	 */

	function components_list() { 
		
		?>
		
		<div class="ct-panel" ng-if="isShowTab('components','fundamentals')">
			<?php do_action("ct_toolbar_fundamentals_list"); ?>
		</div>

		<?php $this->output_folders_content( array(
												"wordpress" => array(
																"name" 	=> "WordPress",
																"children" => array(
																		array(
																			"name" 	=> "Widgets",
																			"id" 	=> "widgets"
																			)
																	)
												)
										) , "", "" ); ?>

		<div class="ct-panel ct-reusable-parts" ng-if="isShowTab('components','reusable_parts')">
			<?php do_action("ct_toolbar_reusable_parts"); ?>
		</div>

		<?php $this->output_folders_content( $this->folders, "", "" ); ?>

		<?php 
	}


	/**
	 * Add all "Re-usable parts" to Components browser
	 *
	 * @since  0.2.3
	 */

	function ct_reusable_parts() {

		// Get all archive templates
		$args = array(
			'posts_per_page'	=> -1,
			'orderby' 			=> 'date',
			'order' 			=> 'DESC',
			'post_type' 		=> 'ct_template',
			'post_status' 		=> 'publish',
			'meta_key'   		=> 'ct_template_type',
			'meta_value' 		=> 'reusable_part'
		);

		$templates = new WP_Query( $args );

		foreach ( $templates->posts as $template ) : ?>

			<div class="ct-add-component-button">
				<div class="ct-reusable-title"><?php echo $template->post_title; ?></div>
				<div class="ct-add-component-icon" title="<?php _e("Add Re-usable part as single component", "component-theme")?>"
					ng-click="loadReusablePart(<?php echo $template->ID; ?>)">
					<?php _e("Single", "component-theme"); ?>
				</div>
				<div class="ct-add-component-icon" title="<?php _e("Add Re-usable part as editable fundamentals", "component-theme")?>"
					ng-click="loadReusablePart(<?php echo $template->ID; ?>, component.active.id)">
					<?php _e("Editable", "component-theme"); ?>
				</div>
			</div>
		
		<?php endforeach;

	}


	/**
	 * Output Page Settings in Builders Toolbar
	 *
	 * @since 0.1.3
	 */

	function ct_show_page_settings() { ?>
		
		<br />
		<table class="ct-settings">
			<tr>
				<td><?php _e( "Page Width", "component-theme" ); ?></td>
				<td>
					<div class="ct-measurebox-container clearfix">
						<div class="ct-measurebox-wrap">
							<div class="ct-measurebox ct-column-width">
								<input class="ct-measure-value ct-number-font" type="text" spellcheck="false"
									ng-model="pageSettings['max-width']"
									ng-change="pageSettingsUpdate()">
									<div class="ct-measure-type">px</div>
							</div>
						</div>
					</div>
				</td>
			</tr>
		</table>

	<?php }


	/**
	 * Output .measure-type-select element
	 *
	 * @since 0.3.0
	 */

	static public function measure_type_select($option, $types = "px,%,em,auto") { 

		$types = explode(",", $types);

		?>
		
		<div class="ct-measure-type-select">
			<?php if (in_array("px", $types)) : ?>
			<div class="ct-button ct-measure-type-option"
				ng-click="setOptionUnit('<?php echo $option; ?>', 'px')"
				ng-class="{'ct-active':getOptionUnit('<?php echo $option; ?>')=='px'}">
				<span class="ct-bullet"></span> PX
			</div>
			<?php endif; ?>
			<?php if (in_array("%", $types)) : ?>
			<div class="ct-button ct-measure-type-option"
				ng-click="setOptionUnit('<?php echo $option; ?>', '%')"
				ng-class="{'ct-active':getOptionUnit('<?php echo $option; ?>')=='%'}">
				<span class="ct-bullet"></span> &#37;
			</div>
			<?php endif; ?>
			<?php if (in_array("em", $types)) : ?>
			<div class="ct-button ct-measure-type-option"
				ng-click="setOptionUnit('<?php echo $option; ?>', 'em')"
				ng-class="{'ct-active':getOptionUnit('<?php echo $option; ?>')=='em'}">
				<span class="ct-bullet"></span> EM
			</div>
			<?php endif; ?>
			<?php if (in_array("auto", $types)) : ?>
			<div class="ct-button ct-measure-type-option"
				ng-click="setOptionUnit('<?php echo $option; ?>', 'auto')"
				ng-class="{'ct-active':getOptionUnit('<?php echo $option; ?>')=='auto'}">
				<span class="ct-bullet"></span> <?php _e("Auto", "component-theme"); ?>
			</div>
			<?php endif; ?>
		</div>

	<?php }


	/**
	 * Output .ct-measurebox-options element
	 *
	 * @since 0.3.0
	 */

	function measure_box_options( $option, $opposite_option, $text = "" ) { ?>

		<div class="ct-measurebox-options">
			<div class="ct-checkbox">
				<label><input class="ct-apply-opposite-trigger" type="radio" name="<?php echo $option; ?>_measure" 
					data-option="<?php echo $option; ?>" 
					data-opposite-option="<?php echo $opposite_option; ?>"/>
					<span class="ct-checkbox-box"></span><span><?php echo $text ?></span></label>
			</div>
			<div class="ct-checkbox">
				<label><input class="ct-apply-all-trigger" type="radio" name="<?php echo $option; ?>_measure" data-option="<?php echo $option; ?>"/>
					<span class="ct-checkbox-box"></span><span><?php _e("Apply All", "component-theme"); ?></span></label>
			</div>
		</div>
	<?php }


	/**
	 * Output dialog window settings
	 *
	 * @since 0.2.4
	 */

	function dialog_window() { ?>

		<?php 
			// TODO: avoid additional API call here
			global $oxygen_api;
			$categories = $oxygen_api->get_categories();
			unset($categories["status"]);
			array_walk($categories, function(&$value, &$key) {
				if(isset($value["name"]))
			    	$value["name"] 	= sanitize_text_field($value["name"]);
			    if(isset($value["id"]))
			    	$value["id"] 	= sanitize_text_field($value["id"]);
			});		
		?>
		
		<div ng-if="isActiveName('ct_widget')">
			<div id="ct-dialog-widget-content" class="ct-dialog-widget-content">
				<!-- AJAX loaded content here -->
			</div>
			<div class="ct-action-button" ng-click="applyWidgetInstance()">Apply</div>
		</div>

		<div ng-if="dialogForms['showComponentizeForm']">
			<div id="ct-dialog-componentize-form" class="ct-dialog-componentize-form">
				ID (keep empty for new component) <br/>
				<input type="text" class="ct-textbox ct-textbox-huge" ng-model="componentizeOptions.idToUpdate"><br/><br/>	
				Name <br/>
				<input type="text" class="ct-textbox ct-textbox-huge" ng-model="componentizeOptions.name"><br/><br/>
				Category <br/>
				<select class="ct-select" ng-model="componentizeOptions.categoryId">
					<?php foreach ($categories as $key => $category) : ?>
						<option value="<?php echo isset($category["id"])?$category["id"]:''; ?>"><?php echo isset($category["name"])?$category["name"]:''; ?></option>
					<?php endforeach; ?>
				</select><br/>
				Design Set ID <br/>
				<input type="text" class="ct-textbox" ng-model="componentizeOptions.designSetId"><br/>
				Screenshot <br/>
				<input type="file" file-model="componentizeOptions.screenshot"><br/>
			</div>
			<br/>
			<button class="ct-action-button" ng-click="componentize()"><?php echo "Submit"; ?></button>
		</div>

		<div ng-if="dialogForms['showPageComponentizeForm']">
			<div id="ct-dialog-page-componentize-form" class="ct-dialog-page-componentize-form">
				Name <br/>
				<input type="text" class="ct-textbox ct-textbox-huge" ng-model="componentizeOptions.pageName"><br/><br/>
				Design Set ID <br/>
				<input type="text" class="ct-textbox" ng-model="componentizeOptions.designSetId"><br/>
				Screenshot <br/>
				<input type="file" file-model="componentizeOptions.screenshot"><br/>
			</div>
			<br/>
			<button class="ct-action-button" ng-click="tryPageComponentize()"><?php echo "Submit"; ?></button>
		</div>

		<div ng-if="dialogForms['showAddItemDialogForm']" id='ct-add-component-page-dialog'>
			<div id="ct-dialog-add-item-form" class="ct-dialog-add-item-form">
				<div class="clearfix">
					<span class="ct-component-title">{{stripSlashes(itemOptions.currentItem['name'])}}</span>
					<span class="ct-dialog-item-design-label">{{stripSlashes(itemOptions.currentItem['design_set_name'])}}</span>
					<button class="ct-action-button ct-add-form-button" ng-click="addItem()"><?php echo "Add Component to Page"; ?></button>
				</div>
				<div class='ct-component-img-container'>
					<img ng-src="{{itemOptions.currentItem['screenshot_url']}}" alt="<?php _e("Item screenshot", "component-theme"); ?>" />
				</div>
				<div class="ct-component-nav-arrows clearfix">
					<span class="ct-component-nav-arrow ct-component-nav-left"
						ng-click="switchComponent(null,'left')"><?php _e("&laquo; Previous", "component-theme"); ?></span>
					<span class="ct-component-nav-arrow ct-component-nav-right"
						ng-click="switchComponent(null,'right')"><?php _e("Next &raquo;", "component-theme"); ?></span>
				</div>
				<button ng-if="isDev()" class="ct-action-button ct-upload-screenshot-button" ng-click="showUpdateScreenshot()"><?php echo "Update Screenshot"; ?></button>
			</div>
		</div>

		<div ng-if="dialogForms['showUploadAsset']">
			<div id="ct-dialog-upload-asset-form" class="ct-dialog-upload-asset-form">
				Screenshot <br/>
				<input type="file" file-model="componentizeOptions.screenshot"><br/>
			</div>
			<br/>
			<button class="ct-action-button" ng-click="updateScreenshot()"><?php echo "Submit"; ?></button>
		</div>

		<div ng-if="dialogForms['showAddDesignSet']">
			<div id="ct-dialog-design-set-form" class="ct-dialog-design-set-form">
				Name <br/>
				<input type="text" class="ct-textbox ct-textbox-huge" ng-model="componentizeOptions.setName"><br/><br/>
				Status <br/>
				<select class="ct-select" ng-model="componentizeOptions.status">
					<option value="public">public</option>
					<option value="dev">dev</option>
				</select><br/>
			</div>
			<br/>
			<button class="ct-action-button" ng-click="createDesignSet()"><?php echo "Submit"; ?></button>
		</div>

		<div ng-if="dialogForms['stylesheet']">
			<div id="ct-dialog-stylesheet-form" class="ct-dialog-stylesheet-form">
				Design Set ID <br/>
				<input type="text" class="ct-textbox" ng-model="componentizeOptions.designSetId"><br/>
			</div>
			<br/>
			<button class="ct-action-button" ng-click="postStyleSheet()"><?php echo "Submit"; ?></button>
		</div>

	<?php }

}

// Create toolbar inctance
if ( defined("SHOW_CT_BUILDER") ) {
	$toolbar = new CT_Toolbar();
}
