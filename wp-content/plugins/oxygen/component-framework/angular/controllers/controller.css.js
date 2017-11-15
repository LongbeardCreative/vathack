/**
 * All Custom CSS staff here
 * 
 */

CTFrontendBuilder.controller("ControllerCSS", function($scope, $http, $timeout) {

	$scope.idStylesContainer 	= document.getElementById("ct-id-styles");
	$scope.classStylesContainer = document.getElementById("ct-class-styles");

	// Example
	$scope.customSelectorsExample = {
		'#my-custom-id' : {
			'original' : {
				'color' : 'red',
				'font-size' : '24px',
			}
		}
	}

	$scope.selectorDetector = {
		mode: false
	};

	$scope.stylesheetToAdd = {
		name : ""
	}

	$scope.selectorToEdit 		= false;
	$scope.selectorHighlighted 	= true;
	$scope.expandedStyleSets 	= [];

	$scope.excludeProperties = [
				"ct_content",
				"ct_id",
				"ct_parent",
				"tag",
				"url",
				"src",
				"alt",
				"target",
				"icon-id",
				"section-width",
				"custom-width",
				"container-padding-top",
				"container-padding-right",
				"container-padding-bottom",
				"container-padding-left",
				"background-position-top",
				"background-position-left",
				"background-size-width",
				"background-size-height",
				"custom-js",
				"border-all-width",
				"border-all-style",
				"border-all-color",
				"function_name",
				"full_shortcode",
				"gutter",
				"code-css",
				"code-php",
				"code-js",
				"class_name",
				"id_base",
				"pretty_name",
				"friendly_name"];

	$scope.cache.idCSS 			= "";
	$scope.cache.idStyles 		= {};
	
	$scope.cache.classCSS 		= "";
	$scope.cache.classStyles 	= {};
	
	$scope.cache.selectorCSS 	= "";
	$scope.cache.selectorStyles = {};

	$scope.cache.mediaCSS 		= "";
	$scope.cache.mediaStyles 	= {};

	$scope.contentAdded = [];


	/**
	 * Set custom CSS selector to edit
	 *
	 * @since 0.2.0
	 * @author Ilya K.
	 */

	$scope.setCustomSelectorToEdit = function(selector) {

		// if the active component had some active css selector, bring it back to edit mode
		if($scope.component.active.id > 0) {
			$scope.activeSelectors[$scope.component.active.id] = (typeof($scope.activeSelectors[$scope.component.active.id]) !== 'undefined') ? $scope.activeSelectors[$scope.component.active.id] : $scope.component.options[$scope.component.active.id].model.activeselector;
			if($scope.activeSelectors[$scope.component.active.id]) {
				$scope.setCurrentClass($scope.activeSelectors[$scope.component.active.id] ? $scope.activeSelectors[$scope.component.active.id] : false);	
			}
		}

		if ($scope.log) {
			console.log("setCustomSelectorToEdit()", selector)
		}

		$scope.selectorToEdit = selector;

		if (selector) {

			$scope.activateComponent(-1, 'ct_selector');

			// if selector exist in classes
			var possibleClass = selector.substr(1);
    		if ( $scope.classes[possibleClass] ) {
    			$scope.setCurrentClass(possibleClass);
    		}
    		else {
    			$scope.setCurrentClass(false);
    		}

    		if ($scope.customSelectors[selector]) {
    			$scope.expandedStyleSets[$scope.customSelectors[selector]['set_name']] = true;
			}
		}
		
		$scope.highlightSelector(true, selector);
	}


	/**
	 * Set style sheet to edit
	 *
	 * @since 0.3.4
	 * @author Gagan Goraya.
	 */

	$scope.setStyleSheetToEdit = function(stylesheet) {

		$scope.cancelDeleteUndo();
	
		if ($scope.log) {
			console.log("setStyleSheetToEdit()", stylesheet)
		}

		$scope.stylesheetToEdit = stylesheet;
		$scope.switchActionTab('styleSheet');
	}


	/**
	 * Highlight selector elements presented on the page
	 *
	 * @since 0.2.0
	 * @author Ilya K.
	 */

	$scope.highlightSelector = function(forceHighlight, selector, $event) {

		if ($scope.log){
			console.log("highlightSelector()", forceHighlight, selector);
		}

		if (selector===false) {
			$scope.outputCSSStyles("ct-selector-highlight", "");
			return false;
		}

		if ($event){
			$event.stopPropagation();
		}

		var style = "";

		if (undefined === selector) {
			selector = $scope.selectorToEdit;
		}

		if (forceHighlight === undefined) {
			$scope.selectorHighlighted = ! $scope.selectorHighlighted;
		}
		else {
			$scope.selectorHighlighted = forceHighlight;	
		}

		// check if selector has any elements on the page
		if ($scope.selectorHighlighted) {
			
			var elements = []; 

			if (selector.indexOf(".")<0&&selector.indexOf("#")<0&&selector.indexOf("body")<0) {
				elements = angular.element(selector, '.oxygen-body');
			}
			else {
				elements = angular.element(selector);
			}

			if ( elements.length == 0 ) {

				$scope.selectorHighlighted = false;
				
				//alert("There is no elements for '" + selector + "' selector on this page.")
				$scope.outputCSSStyles("ct-selector-highlight", "");
				return false;
			}
		}

		var state = "";

		if ( $scope.currentState == "original" || $scope.currentState == "hover" || $scope.currentState == "active" || $scope.currentState == "focus" ) {
			state = "";
		}
		else {
			state = ":"+$scope.currentState;
		}

		// check if highlighted
        if ($scope.selectorHighlighted) {
        	style = selector + state + "{outline: 2px dashed rgba(26,141,26,1); outline-offset: 3px;}";
        	if (selector.indexOf(".")<0&&selector.indexOf("#")<0&&selector.indexOf("body")<0) {
        		style = ".oxygen-body " + style;
        	}
        }

        // output to <head>
        $scope.outputCSSStyles("ct-selector-highlight", style);
	}


	/**
	 * Add Style Sheet
	 *
	 * @since 0.3.4
	 * @author Gagan Goraya
	 */

	$scope.addStyleSheet = function() {

		$scope.cancelDeleteUndo();

		if ($scope.stylesheetToAdd.name === "") {
			alert("Stylesheet's name can't be empty.");
			return false
		}

		var stylesheet = $scope.stylesheetToAdd.name;

	    // check for validity of the name
    	var re = /^[a-z_-][a-z\d_-]*$/i
	    //var re = /-?[_a-zA-Z]+[_a-zA-Z0-9-]*$/i;
	    if(!re.test(stylesheet)) {
	    	alert("Bad stylesheet name. Special characters and spaces are not allowed");
	    	return;
	    }
		
	    // check for repeat
	    if(typeof($scope.styleSheets[stylesheet]) !== 'undefined' ) {
	    	alert("'" + stylesheet + "' already exist. Please choose another name.");
	    	return;
	    }
		
		$scope.styleSheets[stylesheet] = "";
		$scope.stylesheetToAdd.name = "";	
	}


	/**
	 * Delete Style sheet
	 *
	 * @since 0.3.4
	 * @author Gagan Goraya.
	 */

	$scope.deleteStyleSheet = function(stylesheet) {

		$scope.cancelDeleteUndo();

		var confirmed = confirm("Delete \""+stylesheet+"\" stylesheet from install? (Changes will take effect on Save).");
		
		if (!confirmed) {
			return false;
		}

		if ($scope.log) {
			console.log("deleteStyleSheet()", selector);
		}

		delete $scope.styleSheets[stylesheet];

		// remove the style definitions rendered in the DOM
		var styleSheetContainer = angular.element(document.getElementById("ct-style-sheet-"+stylesheet));

		styleSheetContainer.remove();

		// turn off the editing panel, if this style sheet was being edited
		if($scope.stylesheetToEdit === stylesheet && $scope.actionTabs['styleSheet']) {
			$scope.stylesheetToEdit = false;
		    $scope.actionTabs['styleSheet'] = false;
		}
	}	


	/**
	 * Delete custom CSS selector
	 *
	 * @since 0.2.0
	 * @author Ilya K.
	 */

	$scope.deleteCustomSelector = function(selector) {

		$scope.cancelDeleteUndo();

		var confirmed = confirm("Are you sure to delete \""+selector+"\" selector?");
		
		if (!confirmed) {
			return false;
		}

		if ($scope.log) {
			console.log("deleteCustomSelector()", selector);
		}

		delete $scope.customSelectors[selector];

		$scope.selectorToEdit = false;

		if ( $scope.component.active.id == -1 ) {
			$scope.activateComponent(0,'root')
		}
		
		$scope.classesCached = false;
		$scope.outputCSSOptions();
		$scope.unsavedChanges();
	}


    /**
     * Update custom selector parameter value
     *
     * @since 0.2.0
     * @author Ilya K.
     */
    
    $scope.updateCustomSelectorValue = function(parameter, value) {

    	$scope.cancelDeleteUndo();

    	if ($scope.log) {
    		console.log("updateCustomSelectorValue()", parameter, value);
    	}

    	var state 		= $scope.currentState,
    		selector 	= $scope.selectorToEdit.substr(1);

    	// if selector exist in classes
    	if ( $scope.classes[selector] ) {
    		
    		// check if this state already added
    		if (!$scope.classes[selector][state]) {
                $scope.classes[selector][state] = {};
            }
			if (value==""){
				delete $scope.classes[selector][state][parameter];
			}
			else {
    			$scope.classes[selector][state][parameter] = value;
			}
    	}
    	else {

            if ( !$scope.isEditing('media') ) {
				    
				if (!$scope.customSelectors[$scope.selectorToEdit]) {
				    $scope.customSelectors[$scope.selectorToEdit] = {};
				}

				if (!$scope.customSelectors[$scope.selectorToEdit][state]) {
				    $scope.customSelectors[$scope.selectorToEdit][state] = {};
				}

				if (value==""){
					delete $scope.customSelectors[$scope.selectorToEdit][state][parameter];
				}
				else {
					$scope.customSelectors[$scope.selectorToEdit][state][parameter] = value;
				}

            }
            else {
                // init class media options
                if (!$scope.customSelectors[$scope.selectorToEdit]['media']) {
                    $scope.customSelectors[$scope.selectorToEdit]['media'] = {};
                }
                
                if (!$scope.customSelectors[$scope.selectorToEdit]['media'][$scope.currentMedia]) {
                    $scope.customSelectors[$scope.selectorToEdit]['media'][$scope.currentMedia] = {};
                }

                if (!$scope.customSelectors[$scope.selectorToEdit]['media'][$scope.currentMedia][state]) {
                    $scope.customSelectors[$scope.selectorToEdit]['media'][$scope.currentMedia][state] = {};
                }

                // remove empty options
                if ( value == "" ) {
                    delete $scope.customSelectors[$scope.selectorToEdit]['media'][$scope.currentMedia][state][parameter];
                } 
                else {
                    $scope.customSelectors[$scope.selectorToEdit]['media'][$scope.currentMedia][state][parameter] = value;
                }
            }
	    }
    }


	/**
     * Append <style> element into <head> with all passed styles
     *
     * @since 0.2.0
     * @author Ilya K.
     */
    
    $scope.outputCSSStyles = function(name, style) {

    	var styleElement = document.getElementById(name),
    		output = "";
        
        output = "<style id=\"" + name + "\">",
        output += style;
        output += "</style>";
        
        // add style
        if ( styleElement ) {
            //angular.element(styleElement).replaceWith(output);
            styleElement.innerHTML = style;
        } 
        else {
            angular.element("head").append(output);
        }
    }


    /**
     * Delete <style> element into <head> with all passed styles
     *
     * @since 0.3.1
     * @author Ilya K.
     */
    
    $scope.deleteCSSStyles = function(name) {

    	var styleElement = document.getElementById(name); 
        
        if ( styleElement ) {
            angular.element(styleElement).remove();
        }
    }


    /**
     * Output to the <head> CSS styles for all componets, classes and custom selectors
     *
     * @since 0.2.0
     * @author Ilya K.
     */

    $scope.outputCSSOptions = function(id, force) {
		
		if ($scope.log) {
			console.log("outputCSSOptions()", id);
			$scope.functionStart("outputCSSOptions()");
		}

		var style = "";

		// make columns full width for mobile
		if ($scope.isEditing('media')) {
			style +=	".ct-columns-inner-wrap{"+
							"display:block !important;"+
						"}"+
						".ct-columns-inner-wrap:after{"+
							"display:table;"+
							"clear:both;"+
							"content:\"\";"+
						"}"+
						".ct-column{"+
							"width:100% !important;"+
							"margin:0 !important;"+
						"}"+
						".ct-columns-inner-wrap{"+
							"margin:0 !important;"+
						"}";
		}

		/**
		 * Handle #ID styles
		 */
		
		if (($scope.isEditing("class") || $scope.isEditing("id") || $scope.isEditing("media") || force == true)) {

			if (undefined !== id && id > 0) {
				$scope.updateComponentCacheStyles(id);
			}

			// clear cache
			$scope.cache.idCSS = "";

			Object.keys($scope.cache.idStyles).map(function(key, index) {
				$scope.cache.idCSS += $scope.cache.idStyles[key];
			});	
			
			style += $scope.cache.idCSS;
			$scope.idStylesContainer.innerHTML = style;
			style = "";
		}

		/**
    	 * Handle .class styles
    	 */
    	
    	if ($scope.isEditing("class") || !$scope.classesCached) {
	    
			if (!$scope.classesCached) {
				
				// empty styles cache
				$scope.cache.classStyles = [];

				// loop all classes
				for(var className in $scope.classes) { 
					if ($scope.classes.hasOwnProperty(className)) {
						// get states
						var classStates = $scope.classes[className];
						
						// add styles to cache
						$scope.cache.classStyles[className] = $scope.getSingleClassCSS(className, classStates);
					}
				}

				// set cached flag
				$scope.classesCached = true;
			}
			else {
				$scope.cache.classStyles[$scope.currentClass] = $scope.getSingleClassCSS($scope.currentClass);
			}

			// clear CSS cache
			$scope.cache.classCSS = "";

			Object.keys($scope.cache.classStyles).map(function(key, index) {
				$scope.cache.classCSS += $scope.cache.classStyles[key];
			});
			
			style += $scope.cache.classCSS;
			$scope.classStylesContainer.innerHTML = style;
			style = "";
		}


		/**
    	 * Add all custom selectors' options
    	 * 
    	 */
    	
    	// TODO: implement cache clearing and check for cache
    	$scope.cache.selectorStyles = [];
    	$scope.cache.selectorCSS 	= "";

        for(var selectorName in $scope.customSelectors) { 
			if ($scope.customSelectors.hasOwnProperty(selectorName)) {
				// get states
				var states = $scope.customSelectors[selectorName];
				$scope.cache.selectorStyles[selectorName] = $scope.getSingleSelectorCSS(selectorName, states);
        	}
        }

        // concatenate cache array into single CSS string cache
		for(var selectorName in $scope.cache.selectorStyles) { 
			if ($scope.cache.selectorStyles.hasOwnProperty(selectorName)) {
				
				var css = $scope.cache.selectorStyles[selectorName];
				$scope.cache.selectorCSS += css;
			}
		}

		// add styles from cache
		style += $scope.cache.selectorCSS;
		$scope.outputCSSStyles("ct-custom-selectors", style)

        $scope.functionEnd("outputCSSOptions()");
    }


	/**
	 * Update component styles in cache array
	 *
	 * @since 0.2.5
	 * @author Ilya K.
	 */
	
	$scope.updateComponentCacheStyles = function(id) {

		$scope.contentAdded = [];

		if ($scope.log) {
			console.log("updateComponentCacheStyles()", id, $scope.component.options[id]);
		}

		// update particular component Styles in cache
		$scope.cache.idStyles[id] = $scope.getSingleComponentCSS($scope.component.options[id], id);
	}


	/**
	 * Update all components styles in cache array
	 *
	 * @since 0.3.0
	 * @author Ilya K.
	 */
	
	$scope.updateAllComponentsCacheStyles = function() {

		$scope.functionStart("updateAllComponentsCacheStyles()");

		for(var id in $scope.component.options) { 
			if ($scope.component.options.hasOwnProperty(id)) {
				
				$scope.updateComponentCacheStyles(id);
			}
		}

		$scope.functionEnd("updateAllComponentsCacheStyles()");
	}


	/**
	 * Remove component styles in cache array
	 *
	 * @since 0.2.5
	 * @author Ilya K.
	 */
	
	$scope.removeComponentCacheStyles = function(id) {

		// remove from array
		delete $scope.cache.idStyles[id];
	}


    /**
     * Get one single components CSS
     *
     * @since 0.2.5
     * @author Ilya K.
     * @return {string} CSS styles
     */

    $scope.getSingleComponentCSS = function(componentOptions, componentId, isMedia) {

    	if (componentOptions.name=="ct_reusable" && componentOptions.original) {

    		// holder for reusable CSS
			$scope.reusableCSS 			= {};
			$scope.reusableCSS.styles 	= "";

			var viewId = componentOptions.original.view_id;

    		// add this item CSS
			if ($scope.postsData[viewId]) {
				$scope.generateTreeCSS($scope.postsData[viewId].post_tree, $scope.reusableCSS);
				$scope.outputCSSStyles("ct-re-usable-styles-"+viewId, $scope.reusableCSS.styles);
			}

			return "";
    	}

    	if ($scope.log) {
    		console.log("getSingleComponentCSS()", componentId, componentOptions, isMedia);
    		$scope.functionStart("getSingleComponentCSS()");
		}

    	var style 		= "",
    		important 	= "",
    		paragraph 	= "",
    		currentState = "",
    		componentDefaults = $scope.defaultOptions[componentOptions.name];
		
		// loop components' states
		for(var stateName in componentOptions) { 
			if (componentOptions.hasOwnProperty(stateName)) {
				var stateOption = componentOptions[stateName];
				
				// skip original state for "id"
				if ( stateName=="original" && !$scope.isEditing("media") ) {
					continue;
				}

				// skip "id" original options for media
				if ( stateName=="original" && $scope.isEditing("media") && !isMedia ) {
					continue;
				}

				if (componentId != 0 && typeof(stateOption) === "object" && 
					stateName != "model" && 
					stateName != "classes" &&
					stateName != "media" ) {
					
					// make a copy to not modify original Object
					var mergedOptions = angular.copy(componentOptions[stateName]),
						stateOptions  = $scope.getCSSOptions(componentId, stateName, mergedOptions, componentOptions.name);

					// apply styles to id
					if (
							// if "id" state
							stateName == "id" ||

							// editing media
							( $scope.isEditing('media') && stateName == "original" ) ||
							
							// or if currently editing exactly this id and state
							( stateName 	== $scope.currentState && 
							  componentId 	== $scope.component.active.id && 
							  
							  $scope.isEditing('id') && ( stateName == "hover" || stateName == "active" || stateName == "focus")
							) 
						) 
					{
						currentState = "";
					}
					// apply style to state/pseudo-element
					else {
						currentState = ":" + stateName;
					}

					// check if options is for Paragraph component
					if ( componentOptions.name == "ct_paragraph" ) {
						paragraph = " p";
					}
					else {
						paragraph = "";
					}

					// handle columns gutter
					if ( componentOptions.name == "ct_columns" ) {

						var gutter = $scope.getWidth(stateOptions['gutter']);

						//style += '#' + componentOptions.selector + " > .ct-columns-inner-wrap" + currentState + "{";
						//style += "margin-right" + ": -" + (gutter.value/2) + gutter.unit + ";";
						//style += "margin-left" + ": -" + (gutter.value/2) + gutter.unit + ";";
						//style += '}';

						style += '#' + componentOptions.selector + " .ct-column" + currentState + "{";
						style += "margin-right" + ":" + (gutter.value/2) + gutter.unit + ";";
						style += "margin-left" + ":" + (gutter.value/2) + gutter.unit + ";";
						style += '}';
					}


					// handle section container
					if ( componentOptions.name == "ct_section" ) {

						style += '#' + componentOptions.selector + ">.ct-section-inner-wrap" + currentState + "{";

							if (undefined==stateOptions['section-width']) {
								stateOptions['section-width'] = componentDefaults['section-width'];
							}
						
							if ( stateOptions['section-width'] == "page-width" ) {
								style += "max-width" + ":" + parseInt($scope.pageSettings['max-width']) + "px;";
							}
							if ( stateOptions['section-width'] == "custom" && stateOptions['custom-width']) {
								style += "max-width" + ":" + stateOptions['custom-width'] + ";";
							}
							if ( stateOptions['section-width'] == "full-width" ) {
								style += "max-width:100%;";
							}
						
							// custom-padding
							if ( stateOptions['container-padding-top'] ) {
								style += "padding-top" 		+ ":" + stateOptions['container-padding-top'] + ";";
							}
							if ( stateOptions['container-padding-right'] ) {
								style += "padding-right"	+ ":" + stateOptions['container-padding-right'] + ";";
							}
							if ( stateOptions['container-padding-bottom'] ) {
								style += "padding-bottom" 	+ ":" + stateOptions['container-padding-bottom'] + ";";
							}
							if ( stateOptions['container-padding-left'] ) {
								style += "padding-left" 	+ ":" + stateOptions['container-padding-left'] + ";";
							}
						
						style += '}';
					}

					// TODO: add check for elements with no ID syles to not output empty selectors
					style += '#' + componentOptions.selector + paragraph + currentState + "{";
					
					// make sure its the same selector, and styles are not being applied based on 'just' ID
			    	if ($scope.component.options[componentId] && ($scope.component.options[componentId].selector !== componentOptions.selector && !componentOptions.original)) {
						// do nothing;
			    	}
			    	else if (typeof(stateOptions) === 'object') {
						// loop state's options
						for(var parameter in stateOptions) {
							if (stateOptions.hasOwnProperty(parameter)) {
								var value = stateOptions[parameter];

								if (parameter=="custom-css") {
									continue;
								}

								// load Web Fonts
								if (parameter == "font-family") {
									$scope.loadWebFont(value);
									if ( value.indexOf(',') === -1 && value.toLowerCase() !== "inherit") {
										value = "'"+value+"'";
									}
								}

								if (parameter.trim().toLowerCase() == "content") {
									//value = "\"" + $scope.addSlashes(value) + "\"";
									value = "\"" + value.replace('"','\\"') + "\"";
									$scope.contentAdded['#' + componentOptions.selector + paragraph + currentState] = true;
								}
								
								if (value && $scope.excludeProperties.indexOf(parameter) < 0) {
									//console.log(stateName + " - " + parameter + ": " + value);
									style += parameter + ":" + value + ";";
								}
							}
						}
					}
					
					if ((stateName=="before"||stateName=="after")&&!$scope.contentAdded['#' + componentOptions.selector + paragraph + currentState]) {
						style += "content:\"\";";
						$scope.contentAdded['#' + componentOptions.selector + paragraph + currentState] = true;
					}

					if (stateOptions["custom-css"]) {
						style += stateOptions["custom-css"];					
					}

					style += '}';
				}
			} // end if()
		} // end for()
		
		if (componentOptions['media']) {

			// make a copy to not modify options
			var componentMedia = angular.copy(componentOptions['media']),
				medias = $scope.getAllMediaNames($scope.currentMedia);

			for (var index in medias) {
				var mediaName = medias[index];

				if (componentMedia[mediaName]) {
				
					// add name and selector
					componentMedia[mediaName].name 		= componentOptions.name;
					componentMedia[mediaName].selector 	= componentOptions.selector;
					style += $scope.getSingleComponentCSS(componentMedia[mediaName], componentId, true);
				}
			}
		}

		$scope.functionEnd("getSingleComponentCSS()");
		return style;
    }


    /**
     * Get single class CSS string
     *
     * @since 0.2.5
     * @author Ilya K.
     * @return {sting} CSS code
     */

    $scope.getSingleClassCSS = function(className, classStates) {

    	if ($scope.log) {
    		console.log("getSingleClassCSS()", className);
    		$scope.functionStart("getSingleClassCSS()");
    	}

    	if (undefined === classStates) {
    		classStates = $scope.classes[className];
    	}

    	$scope.contentAdded = [];

    	// add default styles
    	var style = $scope.getSelectorStyles(className, classStates);

    	// add media styles
		if ( $scope.isEditing('media') && $scope.classes[className]['media'] ) {

			var medias = $scope.getAllMediaNames($scope.currentMedia);

			for(var index in medias) { 

				var mediaName = medias[index];

				if ($scope.classes[className]['media'][mediaName]) {
					
					classStates = $scope.classes[className]['media'][mediaName];
    				style += $scope.getSelectorStyles(className, classStates);
				}

				// stop on current media
                if (mediaName == $scope.currentMedia) {
                    break
                }
			}
    	}
		
		$scope.functionEnd("getSingleClassCSS()");
		return style;
    }
    

    /**
     * Get single custom selector CSS string
     *
     * @since 1.3
     * @author Ilya K.
     * @return {sting} CSS code
     */

    $scope.getSingleSelectorCSS = function(selectorName, selectorsStates) {

    	if ($scope.log) {
    		console.log("getSingleSelectorCSS()", selectorName);
    		$scope.functionStart("getSingleSelectorCSS()");
    	}

    	if (undefined === selectorsStates) {
    		selectorsStates = $scope.customSelectors[selectorName];
    	}

    	$scope.contentAdded = [];

    	// add default styles
    	var style = $scope.getSelectorStyles(selectorName, selectorsStates, true);

    	// add media styles
		if ( $scope.isEditing('media') && $scope.customSelectors[selectorName]['media'] ) {

			var medias = $scope.getAllMediaNames($scope.currentMedia);

			for(var index in medias) { 

				var mediaName = medias[index];

				if ($scope.customSelectors[selectorName]['media'][mediaName]) {
					
					selectorsStates = $scope.customSelectors[selectorName]['media'][mediaName];
    				style += $scope.getSelectorStyles(selectorName, selectorsStates, true);
				}

				// stop on current media
                if (mediaName == $scope.currentMedia) {
                    break
                }
			}
    	}
		
		$scope.functionEnd("getSingleSelectorCSS()");
		return style;
    }


    /**
     * Get styles for one single class or custom selector
     *
     * @since 1.3
     * @author Ilya K.
     */

    $scope.getSelectorStyles = function(name, states, isCustomSelectors) {
    		
		var style = "",
			currentState;

		// loop all class states
		for(var state in states) { 
			if (states.hasOwnProperty(state)) {
				var styles = states[state];

				// skip "media" and sets
				if ( state == "media" || state == "set_name" ) {
					continue;
				}

				if ( state == "original" || 
						( name == $scope.selectorToEdit 	&& state == $scope.currentState ) ||
						( name == $scope.currentClass 		&& state == $scope.currentState )
					) 
				{	
					if ( state == "original" || state == "hover" || state == "active" || state == "focus" ) {
						currentState = "";
					}
					else {
						currentState = ":" + state;
					}
				}
				else {
					currentState = ":" + state;
				}

				if (!isCustomSelectors) {
					style += '.' + name + ":not(.ct_paragraph)" + currentState;
					if ( $scope.isPseudoElement(currentState) ) {
						style += ',.' + name + ".ct_paragraph p" + currentState;
					}
					else {
						style += ',.' + name + ".ct_paragraph" + currentState + " p";
					}
				}
				else {
					if (name.indexOf(".")<0&&name.indexOf("#")<0&&name.indexOf("body")<0) {
						style += ".oxygen-body "+ name + currentState;
					}
					else {
						style += name + currentState;	
					}
				}

				style += "{";

				// filter styles
				var options = $scope.getCSSOptions(null, null, styles);
				var contentAdded = false;

				// loop all parameters
				for(var parameter in options) { 
					if (options.hasOwnProperty(parameter)) {

						if (parameter=="custom-css") {
							continue;
						}
						
						var value = options[parameter];
				
						if ( parameter.trim().toLowerCase() == "content" ) {
							//value = "\"" + $scope.addSlashes(value) + "\"";
							value = "\"" + value.replace('"','\\"') + "\"";
							$scope.contentAdded[name+currentState] = true;
						}

						// load Web Fonts
						if ( parameter == "font-family" && value !== undefined) {
							$scope.loadWebFont(value);
							if ( value.indexOf(',') === -1 && value.toLowerCase() !== "inherit") {
								value = "'"+value+"'";
							}
						}

						if ( value && $scope.excludeProperties.indexOf(parameter) < 0 ) {
							style += parameter + ":" + value + ";";
						}
					}
				}
				
				if ((state=="before"||state=="after")&&!$scope.contentAdded[name+currentState]) {
					style += "content:\"\";";
					$scope.contentAdded[name+currentState] = true;
				}

				if (options["custom-css"]) {
					style += options["custom-css"];
				}

				style += "}";

				// handle section container
				if ( options['container-padding-top'] 	 ||
					 options['container-padding-right']  ||
					 options['container-padding-bottom'] ||
					 options['container-padding-left']
				 ) {

					style += '.' + name + currentState + " .ct-section-inner-wrap {";
					
						// custom-padding
						if ( options['container-padding-top'] ) {
							style += "padding-top" 		+ ":" + options['container-padding-top'] + ";";
						}
						if ( options['container-padding-right'] ) {
							style += "padding-right"	+ ":" + options['container-padding-right'] + ";";
						}
						if ( options['container-padding-bottom'] ) {
							style += "padding-bottom" 	+ ":" + options['container-padding-bottom'] + ";";
						}
						if ( options['container-padding-left'] ) {
							style += "padding-left" 	+ ":" + options['container-padding-left'] + ";";
						}
					
					style += '}';
				}
			}
		}
		return style;
	}


    /**
     * Recursive function to generate CSS based on items tree
     *
     * @since 0.2.2
     * @author Ilya K.
     */

    $scope.generateTreeCSS = function(treeNode, css) {

    	// do nothing if post contet is not made in builder
		if( Object.prototype.toString.call(treeNode) !== '[object Array]' ) {
			return false;
		}

		if ($scope.log){
			console.log("generateTreeCSS()", treeNode, css);
		}

    	// loop all componets
    	angular.forEach(treeNode, function(component) {

    		// add name to options
    		component.options.name = component.name;

    		// set original options if not defined
    		if (undefined === component.options.original) {
    			//component.options.original = {};
    		}

    		if (!$scope.isEditing('media')) {
    			component.options.id = component.options.original;
    		}

    		// get CSS styles
    		css.styles += $scope.getSingleComponentCSS(component.options, component.id, $scope.isEditing('media'));

			// loop children if any
			if ( component.children ) {
				$scope.generateTreeCSS(component.children, css);
			}
    	});
	}


	/**
     * Apply component CSS
     *
     * @since 0.3.1
     * @author Ilya K.
     */

	$scope.applyComponentCSS = function(id, name) {

		if (undefined==id) {
            id = $scope.component.active.id;
        }

        if (undefined==name) {
            name = $scope.component.active.name;
        }

		// update Tree
		$scope.setOption(id, name, "custom-css", false, false);
	}
	
	
	/**
     * Apply Style Sheet CSS
     *
     * @since 0.3.4
     * @author Gagan Goraya
     */

	$scope.applyStyleSheet = function(stylesheet) {
 		
 		var styleSheetsOutput = "\n"+$scope.styleSheets[stylesheet]+"\n",
			styleSheetsContainer = document.getElementById("ct-style-sheet-"+stylesheet);

		if(styleSheetsContainer === null) {
			var locations = document.getElementsByClassName("ct-css-location"),
				location = angular.element(locations[locations.length - 1]);

			styleSheetsContainer = angular.element('<style>').attr('id', 'ct-style-sheet-'+stylesheet).addClass('ct-css-location')
			styleSheetsContainer.insertAfter(location);
		}
		else
			styleSheetsContainer = angular.element(styleSheetsContainer);
        
        styleSheetsContainer.empty();
        
        styleSheetsContainer.append(styleSheetsOutput);

        // check for brackets
        if ( bracket = $scope.bracketsFailed(styleSheetsOutput) ) {
        	if ( bracket == "(" || bracket == "{" ) {
        		var position = "closing";
        	}
        	else {
				var position = "opening";
        	}
        	var text = "Warning: there is no " + position + " bracket for \"" + bracket + "\" somewhere in your CSS. This may break page styling."
        	jQuery(".ct-js-error-container", "#ct-toolbar").show().html(text);
        }
        else {
        	jQuery(".ct-js-error-container", "#ct-toolbar").hide().html("");
        }

        $scope.unsavedChanges();
    }

	
	/**
     * Add style sheets if not exist
     *
     * @author Ilya K.
     * @since 0.4.0
     */

    $scope.addDesignSetStyleSheets = function(data) {

    	for(var key in data) { 
			if (data.hasOwnProperty(key)) {
				var stylesheet = data[key];
				
				// check for repeat
				if( typeof(stylesheet) === 'object' && typeof($scope.styleSheets[stylesheet["name"]]) === 'undefined' ) {
					$scope.styleSheets[stylesheet["name"]] = $scope.stripSlashes(stylesheet["content"]);
					$scope.applyStyleSheet(stylesheet["name"]);
				}
			}
		}
    }


	/**
     * Helper function to parse width into value and unit
     *
     * @return {object}
     * @author Ilya K.
     */

	$scope.getWidth = function(width) {

		if (!width) {
			return {
				value : "",
				unit : ""
			}
		}

		var value = parseInt(width, 10);

		if ( width.indexOf("%") > -1 ) {
			unit = "%";
		}

		else if ( width.indexOf("em") > -1 ) {
			unit = "em";
		}

		else if ( width.indexOf("rem") > -1 ) {
			unit = "rem";
		}
		else
			unit = "px";

		return {
			value: value,
			unit: unit
		}
	}


	/**
	 * Output page settings CSS
	 *
	 * @since 0.4.0
	 * @author Ilya K.
	 */
	
	$scope.outputPageSettingsCSS = function() {

		var style = '.ct-section-inner-wrap{';
		style += "max-width" + ":" + parseInt($scope.pageSettings['max-width']) + "px;";
		style += "}";

		// output to head
        $scope.outputCSSStyles("ct-page-settings-styles", style);
	}


	/**
	 * Check balance beetwen brackets
	 *
	 * @since 1.1.0
	 * @author Ilya K.
	 */

	$scope.bracketsFailed = function(str){

		var removeComments = function(str){
	    	var re_comment = /(\/[*][^*]*[*]\/)|(\/\/[^\n]*)/gm;
		    return (""+str).replace( re_comment, "" );
		};
		
		var getOnlyBrackets = function(str){
		    //var re = /[^()\[\]{}]/g;
		    var re = /[^(){}]/g;
		    return (""+str).replace(re, "");
		};
		
		var areBracketsInOrder = function(str){
		    str = ""+str;
		    var bracket = {
		            "}": "{",
		            ")": "("
		        },
		        openBrackets = [], 
		        isClean = true,
		        i = 0,
		        len = str.length;

		    for(; i<len; i++ ){
		        if( bracket[ str[i] ] ){
		            isClean = ( openBrackets.pop() === bracket[ str[i] ] );
		            if (!isClean) {
		            	openBrackets.push( str[i] );
		            }
		        }else{
		            openBrackets.push( str[i] );
		        }
		    }
		    return ( openBrackets.length ) ? openBrackets[0] : false;
		    //return isClean && !openBrackets.length;
		};
	    
	    str = removeComments(str);
	    str = getOnlyBrackets(str);
	    return areBracketsInOrder(str);
	};

	
	/**
	 * Specificity Calculator
	 *
	 * https://github.com/keeganstreet/specificity
	 */

	$scope.SPECIFICITY = (function() {
		var calculate,
			calculateSingle,
			compare;

		// Calculate the specificity for a selector by dividing it into simple selectors and counting them
		calculate = function(input) {
			var selectors,
				selector,
				i,
				len,
				results = [];

			// Separate input by commas
			selectors = input.split(',');

			for (i = 0, len = selectors.length; i < len; i += 1) {
				selector = selectors[i];
				if (selector.length > 0) {
					results.push(calculateSingle(selector));
				}
			}

			return results;
		};

		/**
		 * Calculates the specificity of CSS selectors
		 * http://www.w3.org/TR/css3-selectors/#specificity
		 *
		 * Returns an object with the following properties:
		 *  - selector: the input
		 *  - specificity: e.g. 0,1,0,0
		 *  - parts: array with details about each part of the selector that counts towards the specificity
		 *  - specificityArray: e.g. [0, 1, 0, 0]
		 */
		calculateSingle = function(input) {
			var selector = input,
				findMatch,
				typeCount = {
					'a': 0,
					'b': 0,
					'c': 0
				},
				parts = [],
				// The following regular expressions assume that selectors matching the preceding regular expressions have been removed
				attributeRegex = /(\[[^\]]+\])/g,
				idRegex = /(#[^\s\+>~\.\[:]+)/g,
				classRegex = /(\.[^\s\+>~\.\[:]+)/g,
				pseudoElementRegex = /(::[^\s\+>~\.\[:]+|:first-line|:first-letter|:before|:after)/gi,
				// A regex for pseudo classes with brackets - :nth-child(), :nth-last-child(), :nth-of-type(), :nth-last-type(), :lang()
				pseudoClassWithBracketsRegex = /(:[\w-]+\([^\)]*\))/gi,
				// A regex for other pseudo classes, which don't have brackets
				pseudoClassRegex = /(:[^\s\+>~\.\[:]+)/g,
				elementRegex = /([^\s\+>~\.\[:]+)/g;

			// Find matches for a regular expression in a string and push their details to parts
			// Type is "a" for IDs, "b" for classes, attributes and pseudo-classes and "c" for elements and pseudo-elements
			findMatch = function(regex, type) {
				var matches, i, len, match, index, length;
				if (regex.test(selector)) {
					matches = selector.match(regex);
					for (i = 0, len = matches.length; i < len; i += 1) {
						typeCount[type] += 1;
						match = matches[i];
						index = selector.indexOf(match);
						length = match.length;
						parts.push({
							selector: input.substr(index, length),
							type: type,
							index: index,
							length: length
						});
						// Replace this simple selector with whitespace so it won't be counted in further simple selectors
						selector = selector.replace(match, Array(length + 1).join(' '));
					}
				}
			};

			// Replace escaped characters with plain text, using the "A" character
			// https://www.w3.org/TR/CSS21/syndata.html#characters
			(function() {
				var replaceWithPlainText = function(regex) {
						var matches, i, len, match;
						if (regex.test(selector)) {
							matches = selector.match(regex);
							for (i = 0, len = matches.length; i < len; i += 1) {
								match = matches[i];
								selector = selector.replace(match, Array(match.length + 1).join('A'));
							}
						}
					},
					// Matches a backslash followed by six hexadecimal digits followed by an optional single whitespace character
					escapeHexadecimalRegex = /\\[0-9A-Fa-f]{6}\s?/g,
					// Matches a backslash followed by fewer than six hexadecimal digits followed by a mandatory single whitespace character
					escapeHexadecimalRegex2 = /\\[0-9A-Fa-f]{1,5}\s/g,
					// Matches a backslash followed by any character
					escapeSpecialCharacter = /\\./g;

				replaceWithPlainText(escapeHexadecimalRegex);
				replaceWithPlainText(escapeHexadecimalRegex2);
				replaceWithPlainText(escapeSpecialCharacter);
			}());

			// Remove the negation psuedo-class (:not) but leave its argument because specificity is calculated on its argument
			(function() {
				var regex = /:not\(([^\)]*)\)/g;
				if (regex.test(selector)) {
					selector = selector.replace(regex, '     $1 ');
				}
			}());

			// Remove anything after a left brace in case a user has pasted in a rule, not just a selector
			(function() {
				var regex = /{[^]*/gm,
					matches, i, len, match;
				if (regex.test(selector)) {
					matches = selector.match(regex);
					for (i = 0, len = matches.length; i < len; i += 1) {
						match = matches[i];
						selector = selector.replace(match, Array(match.length + 1).join(' '));
					}
				}
			}());

			// Add attribute selectors to parts collection (type b)
			findMatch(attributeRegex, 'b');

			// Add ID selectors to parts collection (type a)
			findMatch(idRegex, 'a');

			// Add class selectors to parts collection (type b)
			findMatch(classRegex, 'b');

			// Add pseudo-element selectors to parts collection (type c)
			findMatch(pseudoElementRegex, 'c');

			// Add pseudo-class selectors to parts collection (type b)
			findMatch(pseudoClassWithBracketsRegex, 'b');
			findMatch(pseudoClassRegex, 'b');

			// Remove universal selector and separator characters
			selector = selector.replace(/[\*\s\+>~]/g, ' ');

			// Remove any stray dots or hashes which aren't attached to words
			// These may be present if the user is live-editing this selector
			selector = selector.replace(/[#\.]/g, ' ');

			// The only things left should be element selectors (type c)
			findMatch(elementRegex, 'c');

			// Order the parts in the order they appear in the original selector
			// This is neater for external apps to deal with
			parts.sort(function(a, b) {
				return a.index - b.index;
			});

			return {
				selector: input,
				specificity: '0,' + typeCount.a.toString() + ',' + typeCount.b.toString() + ',' + typeCount.c.toString(),
				specificityArray: [0, typeCount.a, typeCount.b, typeCount.c],
				parts: parts
			};
		};

		/**
		 * Compares two CSS selectors for specificity
		 * Alternatively you can replace one of the CSS selectors with a specificity array
		 *
		 *  - it returns -1 if a has a lower specificity than b
		 *  - it returns 1 if a has a higher specificity than b
		 *  - it returns 0 if a has the same specificity than b
		 */
		compare = function(a, b) {
			var aSpecificity,
				bSpecificity,
				i;

			if (typeof a ==='string') {
				if (a.indexOf(',') !== -1) {
					throw 'Invalid CSS selector';
				} else {
					aSpecificity = calculateSingle(a)['specificityArray'];
				}
			} else if (Array.isArray(a)) {
				if (a.filter(function(e) { return (typeof e === 'number'); }).length !== 4) {
					throw 'Invalid specificity array';
				} else {
					aSpecificity = a;
				}
			} else {
				throw 'Invalid CSS selector or specificity array';
			}

			if (typeof b ==='string') {
				if (b.indexOf(',') !== -1) {
					throw 'Invalid CSS selector';
				} else {
					bSpecificity = calculateSingle(b)['specificityArray'];
				}
			} else if (Array.isArray(b)) {
				if (b.filter(function(e) { return (typeof e === 'number'); }).length !== 4) {
					throw 'Invalid specificity array';
				} else {
					bSpecificity = b;
				}
			} else {
				throw 'Invalid CSS selector or specificity array';
			}

			for (i = 0; i < 4; i += 1) {
				if (aSpecificity[i] < bSpecificity[i]) {
					return -1;
				} else if (aSpecificity[i] > bSpecificity[i]) {
					return 1;
				}
			}

			return 0;
		};

		return {
			calculate: calculate,
			compare: compare
		};
	}());

});