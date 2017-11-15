/**
 * All Classes staff here
 * 
 */

CTFrontendBuilder.controller("ControllerOptions", function($scope, $timeout) {

    $scope.changesToApply = [];

    $scope.optionsHierarchy = {
        "background" : {
            
            "color"         : ["background-color"],
            "image"         : ["background-image", "overlay-color"],
            "size"          : ["background-size","background-repeat","background-position-left","background-position-top",
                               "background-size-width","background-size-height"],
        },

        "position" : {
            
            "margin_padding" : ["container-padding-top","container-padding-right","container-padding-bottom","container-padding-left",
                                "padding-top","padding-right","padding-bottom","padding-left",
                                "margin-top","margin-right","margin-bottom","margin-left",
                                // units
                                "container-padding-top-unit","container-padding-right-unit","container-padding-bottom-unit","container-padding-left-unit",
                                "padding-top-unit","padding-right-unit","padding-bottom-unit","padding-left-unit",
                                "margin-top-unit","margin-right-unit","margin-bottom-unit","margin-left-unit"],
            "position"       : ["display","float","visibility","clear","overflow","position","top","right","bottom","left"],            
            "size"           : ["width","min-width","max-width","height","min-height","max-height",
                                // units
                                "width-unit","min-width-unit","max-width-unit","height-unit","min-height-unit","max-height-unit"],
        },

        "typography" : {
            
            "typography"    : ["font-family","font-size","font-weight","color","text-align","line-height","text-decoration","font-style","text-transform"],
        },

        "borders" : {
            
            "border"        : ["border-top-color","border-top-style","border-top-width",
                               "border-right-color","border-right-style","border-right-width",
                               "border-bottom-color","border-bottom-style","border-bottom-width",
                               "border-left-color","border-left-style","border-left-width"],
            "radius"        : ["border-top-left-radius","border-top-right-radius",
                               "border-bottom-left-radius","border-bottom-right-radius"]
        },

        "cssjs" : {
            
            "css"   : ["custom-css"],
            "js"    : ["custom-js"]
        },

        "code-php"  : ["code-php"],
        "code-css"  : ["code-css"],
        "code-js"   : ["code-js"]
    }


	/**
     * Update all component's options inside a Components Tree
     * 
     * @since 0.1
     * @author Ilya K.
     */

    $scope.updateTreeComponentOptions = function(key, item, component) {
        
        if ($scope.log) {
            console.log('updateTreeComponentOptions()', key, item, component);
        }

        var componentDefaults   = $scope.defaultOptions[item.name],
            state               = $scope.currentState;

        if (componentDefaults["ct_content"]) {
            item.options["ct_content"] = componentDefaults["ct_content"];
        }

        return;

        // loop all component options
        angular.forEach($scope.component.options[component.id][state], function(value, parameter) {

            // include only options different from defaults and content
            if ( componentDefaults[parameter] != value 
            	|| parameter == 'ct_content' 
            	|| parameter == 'shortcode_tag' 
            	|| $scope.isEditing("media") 
            	|| $scope.isEditing("state") ) {
                
                if ( parameter == 'selector' || 
                     parameter == 'ct_id' || 
                     parameter == 'ct_parent' ||
                     parameter == 'ct_content' ||
                     parameter == 'classes' || 
                     ( parameter == "url" && component.isShortcode ) ) 
                {
                    item.options[parameter] = value;
                }
                else 
                    // add state option
                    if ( state ) {

                        if ( !item.options[state] ) {
                            item.options[state] = {};
                        }

                        item.options[state][parameter] = value;
                    }
            }
            else {
                if ( !item.options[state] ) {
                    // nothing here
                } else {
                    delete item.options[state][parameter];
                }
            }
        });
    }


    /**
     * Update one single component's option inside a Components Tree
     * 
     * @since 0.1.7
     * @author Ilya K.
     */

    $scope.updateTreeComponentOption = function(key, item, component) {
        
        if ($scope.log) {
            console.log('updateTreeComponentOption()', key, item, component);
        }

        var componentDefaults   = $scope.defaultOptions[item.name],
            state               = $scope.currentState,
            parameter           = component.optionName,
            value               = "";

        if ( parameter == 'selector') {
            value = $scope.component.options[component.id][parameter];
        }
        else {
            if ( $scope.isEditing("id") && !$scope.isEditing("state") && !$scope.isEditing("media") ) {
                value = $scope.component.options[component.id]['id'][parameter];
            }
            else {
                value = $scope.component.options[component.id]["model"][parameter];   
            }
        }

        // handle column width option
        if ( component.tag == "ct_column" && parameter == "width" ) {
            
            if ( !item.options[state] ) {
                item.options[state] = {};
            }
            // update tree
            item.options[state][parameter] = value;

            // update options object
            $scope.component.options[component.id][state][parameter] = value;
            
            return true;
        }

        /**
         * Update current Class
         */
        
        if ( $scope.isEditing('class')
             && parameter !== 'ct_content'      // in case, it is the content it should be updated to the ID options
             && !( component.tag === 'ct_image' // in case, it is an image component, its src, height, width should be updated to the ID options
                && ( parameter === 'src'
                    //||  parameter === 'width'
                    //||  parameter === 'height'
                   )
                )
             && parameter !== 'url'
             && parameter !== 'target'
            )
        {
            if ( $scope.classes[$scope.currentClass] ) {
                
                // clear class cache
                delete $scope.cache.classStyles[$scope.currentClass];
                
                // don't include not CSS options
                if ( parameter == 'selector'    || 
                     parameter == 'ct_id'       || 
                     parameter == 'ct_parent'   || 
                     parameter == 'ct_content'  || 
                     parameter == 'classes'     || 
                     ( parameter == "url" && component.isShortcode ) ) 
                {
                   // nothing here
                }
                else {
                    // add option to class
                    if ( !$scope.isEditing('media') ) {
                        
                        // init class state options
                        if (!$scope.classes[$scope.currentClass][state]) {
                            $scope.classes[$scope.currentClass][state] = {};
                        }

                        // remove empty options
                        if (value == "") {
                            delete $scope.classes[$scope.currentClass][state][parameter];
                        }
                        else {
                            $scope.classes[$scope.currentClass][state][parameter] = value;
                        }

                    }
                    else {
                        // init class media options
                        if (!$scope.classes[$scope.currentClass]['media']) {
                            $scope.classes[$scope.currentClass]['media'] = {};
                        }
                        
                        if (!$scope.classes[$scope.currentClass]['media'][$scope.currentMedia]) {
                            $scope.classes[$scope.currentClass]['media'][$scope.currentMedia] = {};
                        }

                        if (!$scope.classes[$scope.currentClass]['media'][$scope.currentMedia][state]) {
                            $scope.classes[$scope.currentClass]['media'][$scope.currentMedia][state] = {};
                        }

                        // remove empty options
                        if ( value == "" ) {
                            delete $scope.classes[$scope.currentClass]['media'][$scope.currentMedia][state][parameter];
                        } 
                        else {
                            $scope.classes[$scope.currentClass]['media'][$scope.currentMedia][state][parameter] = value;
                        }
                    }
                }
            }
        }
        else

        /**
         * Update Media in Components Tree
         */

        if ( $scope.isEditing('media') &&
                // skip not CSS options
                parameter != 'selector'   && 
                parameter != 'ct_id'      && 
                parameter != 'ct_parent'  &&
                parameter != 'ct_content' &&
                parameter != 'tag'        && 
                parameter != "url" ) {

            // update media parameter in $scope.component.options
            $scope.setMediaParameter(component.id, parameter, value);

            // init media state if not exist
            if ( !item.options['media'] ) {
                item.options['media'] = {};
            }
            if ( !item.options['media'][$scope.currentMedia] ) {
                item.options['media'][$scope.currentMedia] = {};
            }
            if ( !item.options['media'][$scope.currentMedia][$scope.currentState] ) {
                item.options['media'][$scope.currentMedia][$scope.currentState] = {};
            }

            // remove from Tree if empty
            if ( value == "" || value == undefined ) {

                // remove property
                delete item.options['media'][$scope.currentMedia][$scope.currentState][parameter];
                delete $scope.component.options[component.id]['media'][$scope.currentMedia][$scope.currentState][parameter];
                
                // remove state if empty
                if ($scope.isObjectEmpty(item.options['media'][$scope.currentMedia][$scope.currentState])){
                    delete item.options['media'][$scope.currentMedia][$scope.currentState];
                }
                // remove current media if empty
                if ($scope.isObjectEmpty(item.options['media'][$scope.currentMedia])){
                    delete item.options['media'][$scope.currentMedia];
                }
                // remove media if empty
                if ($scope.isObjectEmpty(item.options['media'])){
                    delete item.options['media'];
                }
            } 
            // add in Tree
            else {
                item.options['media'][$scope.currentMedia][$scope.currentState][parameter] = value;
            }
        }

        /**
         * Update Component Tree
         */
        
        else {

            // add state for options if not exist
            if (!$scope.component.options[component.id][state]){
                $scope.component.options[component.id][state] = {};
            }

            // add state for component if not exist
            if (!item.options[state]){
                item.options[state] = {};
            }

            // set option to default for original state if value is empty
            if (!value && state == "original") {
                $scope.component.options[component.id][state][parameter] = componentDefaults[parameter]
            }
            // set to current value
            else {
                if (value == "") {
                    delete $scope.component.options[component.id][state][parameter];
                }
                else {
                    $scope.component.options[component.id][state][parameter] = value;
                }
            }
            
            // check units (px, em, etc)
            if ( parameter.indexOf("-unit") > 0 ) {

                var unitOption = parameter.replace("-unit", "");

                // delete both
                if ( $scope.component.options[component.id][state][parameter] == componentDefaults[parameter] &&
                     $scope.component.options[component.id][state][unitOption] == componentDefaults[unitOption] &&
                     !$scope.component.options[component.id]["id"][unitOption])
                {
                    delete item.options[state][parameter];
                    delete item.options[state][unitOption];
                }
                else
                // delete only unit
                if ( $scope.component.options[component.id][state][parameter] == componentDefaults[parameter] ) {
                    delete item.options[state][parameter];
                }
                // add both
                else {
                    item.options[state][parameter] = value;
                    item.options[state][unitOption] = $scope.component.options[component.id][state][unitOption];
                }  
            }
            else
            // check options with units (font-size, etc)
            if ( componentDefaults[parameter+"-unit"] !== undefined &&
                 componentDefaults[parameter+"-unit"] !== $scope.component.options[component.id][state][parameter+"-unit"] ) {
                    
                    if ( value != "" && value != undefined ) {
                        item.options[state][parameter] = value;
                    }
                    else {
                        delete item.options[state][parameter];
                        delete $scope.component.options[component.id][state][parameter];

                        // set back to default value
                        if (state == "original") {
                            $scope.component.options[component.id][state][parameter] = componentDefaults[parameter];
                        }
                    }
            }
            else
                // handle content to replace span HTML with placeholders
                if ( parameter == 'ct_content' ) {

                    var parentComponent     = $scope.getComponentById(component.id),
                        isContentEditable   = false;
                    
                    if ( parentComponent ) {
                        isContentEditable = parentComponent.attr('contenteditable');
                    }

                    // check if component is contenteditable
                    if ( isContentEditable ) {
                    
                        var element         = angular.element("<span>"+value+"</span>"),
                            haveComponents  = false;

                        // loop all child nodes
                        angular.forEach(element.find("*"), function(child) {

                            var childNode   = angular.element(child),
                                componentId = childNode.attr('ng-attr-component-id');

                            // if child is a component
                            if ( componentId ) {

                                childNode.replaceWith("<span id=\"ct-placeholder-"+componentId+"\"></span>");
                                haveComponents = true;
                            }
                        });

                        // update tree value
                        if ( haveComponents ) {
                            item.options[parameter] = element.prop('innerHTML');
                        } 
                        else {
                            item.options[parameter] = value;
                        }
                    }
                    else {
                        item.options[parameter] = value;
                    }
                } 
                // handle options other than "ct_content"
                else {
                    // not CSS options
                    if ( parameter == 'selector'    || 
                         parameter == 'ct_id'       || 
                         parameter == 'ct_parent'   ||
                         parameter == 'classes'     || 
                         ( parameter == "url" && component.isShortcode ) ) 
                    {
                        item.options[parameter] = value;
                    }
                    else 
                        // handle state's option
                        if (state) {

                            if (value === "" || value === undefined) {
                                delete item.options[state][parameter];
                            }
                            else {
                                item.options[state][parameter] = value;
                            }
                        }
                    }
        }
    }


    /**
     * Change component tag
     * 
     * @since 0.3.1
     * @author Ilya K.
     */

    $scope.updateTreeComponentTag = function(id, item, newTag) {
        
        // change tag
        item.name = newTag;
        
        // update active name
        if ( $scope.component.active.id == id ) {
            $scope.component.active.name = newTag;
        }

        // lets also update nicename if exists
        
        if($scope.component.options[id]['nicename'] && $scope.component.options[id]['nicename'].trim() !== '')
            $scope.component.options[item.id]['nicename'] = $scope.calcDefaultComponentTitle(item);

        // rebuild DOM and Tree Navigator
        var timeout = $timeout(function() {
            
            $scope.rebuildDOM(id);
            $scope.updateDOMTreeNavigator(id);
            $timeout.cancel(timeout);
        }, 0, false);
    }

    
    /**
     * Apply component's default options
     * 
     * @since 0.1.7
     */
    
    $scope.applyComponentDefaultOptions = function(id, componentName) {

        if ($scope.log) {
            console.log('applyComponentDefaultOptions()', id, componentName);
        }

        // init component options
        if ( !$scope.component.options[id] ) {
            $scope.component.options[id] = {};
        }
        if ( !$scope.component.options[id]['original'] ) {
            $scope.component.options[id]['original'] = {};
        }
        if ( !$scope.component.options[id]['id'] ) {
            $scope.component.options[id]['id'] = {};
        }
            
        // set default options
        for(var name in $scope.defaultOptions[componentName]) { 
            if ($scope.defaultOptions[componentName].hasOwnProperty(name)) {
                
                var value = $scope.defaultOptions[componentName][name];

                // update 'original'
                $scope.component.options[id]['original'][name] = value;

                // load web fonts
                if ( name == "font-family" ) {
                    $scope.loadWebFont(value);
                }
            }
        }

        // set component selector
        if (!$scope.component.options[id]['selector']) {
            $scope.component.options[id]['selector'] = componentName + "_" + id + "_post_" + CtBuilderAjax.postId;
        }

        // set component name
        if (!$scope.component.options[id]['name']) {
            $scope.component.options[id]['name'] = componentName;
        }
        
        // update model
        $scope.component.options[id]['model'] = angular.copy($scope.component.options[id]['original']);
    }

 
    /**
     * Apply components options saved in Components Tree
     * 
     * @since 0.1.8
     * @author Ilya K.
     */
    
    $scope.applyComponentSavedOptions = function(id, componentTreeItem) {

        if ($scope.log) {
            console.log("applyComponentSavedOptions()", id, componentTreeItem);
        }

        // loop component's states
        for(var stateName in componentTreeItem.options) { 
            if (componentTreeItem.options.hasOwnProperty(stateName)) {
                
                var stateOptions = componentTreeItem.options[stateName];

                // check global fonts and delete if not exist
                $scope.checkGlobalFont(id, componentTreeItem);

                // use original options by default
                if (typeof stateOptions === 'object' && stateName != "classes") {

                    if ( !$scope.component.options[id][stateName] ) {
                        $scope.component.options[id][stateName] = {};
                    }

                    // loop state's options
                    for(var optionName in stateOptions) { 
                        if (stateOptions.hasOwnProperty(optionName)) {
                            
                            var optionValue = stateOptions[optionName];

                            // save 'id' options to check later against defaults
                            if ( stateName == "original" ) {
                                $scope.component.options[id]["id"][optionName] = optionValue;
                            }

                            $scope.component.options[id][stateName][optionName] = optionValue;

                            if ($scope.log) {
                                //console.log(stateName, optionName, optionValue);
                            }

                            if ( optionName == "font-family" ) {
                                $scope.loadWebFont(optionValue);
                            }   
                        }
                    }
                }
                else if (typeof stateOptions !== 'object') {

                    /*if ( stateName == "ct_content" ) {
                        // replace template tags if found
                        stateOptions = $scope.filterTemplateTags(stateOptions, id);
                    }*/

                    if ( stateName == "selector" ) {
                        $scope.component.options[id][stateName] = stateOptions;
                    }
                    else {
                        $scope.component.options[id]['original'][stateName] = stateOptions;
                    }
                }
            }
        }
        
        // update model
        $scope.component.options[id]['model'] = angular.copy($scope.component.options[id]['original']);
    }


    /**
     * Apply all options to model based on current class, state and media
     * 
     * @since 0.3.2
     * @author Ilya K.
     */
    
    $scope.applyModelOptions = function(id, tag) {

        if (undefined===id) {
            id = $scope.component.active.id;
        }
        
        if (undefined===tag) {
            tag = $scope.component.active.name;
        }

        if ($scope.log) {
            console.log("applyModelOptions()", id, tag);
        }
        
        // init options
        if (!$scope.component.options[id]) {
            $scope.component.options[id] = [];
        }

        // clear model
        $scope.component.options[id]['model'] = {}

        // apply id's 'original'
        angular.extend( $scope.component.options[id]['model'],
                        $scope.component.options[id]['original'])

        /**
         * ID
         */
        
        if ($scope.isEditing("id")) {
            
            if ($scope.isEditing("media")) {

                $scope.component.options[id]['model'] = {};

                if ($scope.component.options[id]['media'] &&
                    $scope.component.options[id]['media'][$scope.currentMedia]) {
                    $scope.component.options[id]['model'] = angular.copy($scope.component.options[id]['media'][$scope.currentMedia][$scope.currentState] || {})
                }
            }
            else {
               
                if ($scope.isEditing("state")) {
                    $scope.component.options[id]['model'] = angular.copy($scope.component.options[id][$scope.currentState] || {})
                }
            }
        }

        /**
         * Class
         */


        if ($scope.isEditing("class") && typeof($scope.classes[$scope.currentClass]) == "object") {

            $scope.component.options[id]['model'] = angular.copy($scope.classes[$scope.currentClass]['original'] || {});

            if ($scope.isEditing("media")) {

                $scope.component.options[id]['model'] = {};

                if ($scope.classes[$scope.currentClass]['media'] &&
                    $scope.classes[$scope.currentClass]['media'][$scope.currentMedia]) {
                    $scope.component.options[id]['model'] = angular.copy($scope.classes[$scope.currentClass]['media'][$scope.currentMedia][$scope.currentState] || {})
                }
            }
            else {
                $scope.component.options[id]['model'] = {};

                if ($scope.classes[$scope.currentClass] &&
                    $scope.classes[$scope.currentClass][$scope.currentState]) {

                    $scope.component.options[id]['model'] = angular.copy($scope.classes[$scope.currentClass][$scope.currentState] || {})
                }
            }
        }

        /**
         * Custom selector
         */

        if ($scope.isEditing("custom-selector") && !$scope.isEditing("class")) {
            
            if ($scope.customSelectors[$scope.selectorToEdit]){
                //$scope.component.options[id]['model'] = angular.copy($scope.customSelectors[$scope.selectorToEdit][$scope.currentState] || {});

                if ($scope.isEditing("media")) {

                    $scope.component.options[id]['model'] = {};

                    if ($scope.customSelectors[$scope.selectorToEdit]['media'] &&
                        $scope.customSelectors[$scope.selectorToEdit]['media'][$scope.currentMedia]) {
                        $scope.component.options[id]['model'] = angular.copy($scope.customSelectors[$scope.selectorToEdit]['media'][$scope.currentMedia][$scope.currentState] || {})
                    }
                }
                else {
                    $scope.component.options[id]['model'] = {};

                    if ($scope.customSelectors[$scope.selectorToEdit] &&
                        $scope.customSelectors[$scope.selectorToEdit][$scope.currentState]) {

                        $scope.component.options[id]['model'] = angular.copy($scope.customSelectors[$scope.selectorToEdit][$scope.currentState] || {})
                    }
                }
            }
        }

        // load fonts
        for(var name in $scope.component.options[id]['model']) {
            if ($scope.component.options[id]['model'].hasOwnProperty(name) && name=="font-family") {
                $scope.loadWebFont($scope.component.options[id]['model'][name]);
            }
        }

        // check units
        for(var name in $scope.component.options[id]['model']) {
            if ($scope.component.options[id]['model'].hasOwnProperty(name) && $scope.component.options[id]['model'][name+"-unit"]) {
                
                // only if editing "id"
                if ($scope.isEditing("id") && !$scope.isEditing("media") && !$scope.isEditing("state")) {
            
                    if ($scope.component.options[id]['model'][name+"-unit"] != $scope.defaultOptions[tag][name+"-unit"] &&
                        !$scope.component.options[id]['id'][name] ){
                        
                        delete $scope.component.options[id]['model'][name];
                    }
                }
            }
        }

        if (!$scope.isEditing("custom-selector")) {

            // keep content
            $scope.component.options[id]['model']['ct_content'] = 
            $scope.component.options[id]['original']['ct_content'];

            $scope.component.options[id]['model']['src'] = 
            $scope.component.options[id]['original']['src'];

            $scope.component.options[id]['model']['url'] = 
            $scope.component.options[id]['original']['url'];

            $scope.component.options[id]['model']['target'] = 
            $scope.component.options[id]['original']['target'];

            // keep icon id
            $scope.component.options[id]['model']['icon-id'] = 
            $scope.component.options[id]['original']['icon-id'];

            // keep 
            var optionsToKeep = ['code-php','code-js','code-css', 'class_name', 'id_base', 'pretty_name'];

            for(var key in optionsToKeep) {
                if (optionsToKeep.hasOwnProperty(key) ) {
                    var optionName = optionsToKeep[key];
                    $scope.component.options[id]['model'][optionName] = 
                    $scope.component.options[id]['original'][optionName];
                }
            }
        }
    }


    /**
     * Set Component Options for Components Tree, 
     * update CSS and render shortcode if needed
     * 
     * @since 0.1
     * @author Ilya K.
     */

    $scope.setOption = function(id, tag, optionName, isShortcode, notUpdateCSS) {

        $scope.cancelDeleteUndo();

        if ($scope.log) {
            console.log("setOption() '" + optionName + "' for '" + tag + "' tag to '" + $scope.component.options[id]['model'][optionName] + "'");
            $scope.functionStart("setOption()");
        }

        if ($scope.component.options[id]['model'][optionName]==null) {
            $scope.component.options[id]['model'][optionName] = "";
        }

        // update 'id' options
        if ( $scope.isEditing("id") && !$scope.isEditing("media") && !$scope.isEditing("state")) {

            var allowedEmptyOptions = ["code-css","code-js","code-php","custom-css","custom-js"];
            
            if ($scope.component.options[id]['model'][optionName]) {

                // init 'id' options if not defined
                if (!$scope.component.options[id]["id"]) {
                    $scope.component.options[id]["id"] = {};
                }
                $scope.component.options[id]["id"][optionName] = $scope.component.options[id]['model'][optionName];
            }
            else {
                if($scope.component.options[id]['model'][optionName]=="") {

                    // delete empty values and set model to default    
                    if (allowedEmptyOptions.indexOf(optionName) === -1) {
                        
                        delete $scope.component.options[id]["id"][optionName];

                        // check unit options
                        if ( $scope.component.options[id]["model"][optionName+'-unit'] ) {
                            if ( $scope.component.options[id]["model"][optionName+'-unit'] == $scope.defaultOptions[tag][optionName+'-unit'] ){
                                 
                                 $scope.component.options[id]["model"][optionName]       = $scope.defaultOptions[tag][optionName];
                                 $scope.component.options[id]["original"][optionName]    = $scope.defaultOptions[tag][optionName];
                            }
                        }
                        else 
                            if (optionName!="ct_content") {
                                $scope.component.options[id]["model"][optionName]       = $scope.defaultOptions[tag][optionName];
                                $scope.component.options[id]["original"][optionName]    = $scope.defaultOptions[tag][optionName];
                            }
                    }
                    else {
                        if (!$scope.component.options[id]["id"]) {
                            $scope.component.options[id]["id"] = {};
                        }
                        
                        $scope.component.options[id]["id"][optionName] = $scope.component.options[id]['model'][optionName];
                    }
                }
            }
        }

        // check for empty class name
        if ( $scope.isEditing('class') && $scope.currentClass == "" ) {
            alert("Please choose the class from the list or set a new one.");
            $scope.functionEnd("setOption()");
            return false;
        }

        // make sure column width is no more than 100 and no less than 0
        if ( tag == "ct_column" && optionName == "width" ) {
            if ( $scope.component.options[id]['model'][optionName] > 100 ) {
                $scope.component.options[id]['model'][optionName] = 100;
            }
            if ( $scope.component.options[id]['model'][optionName] < 0 ) {
                $scope.component.options[id]['model'][optionName] = 0;
            }
        }

        // handle fake "border-all-option"
        if ( optionName && optionName.indexOf("border-all") > -1 ) {
            $scope.setOptionModel(optionName.replace("all","top"),      $scope.component.options[id]['model'][optionName]);
            $scope.setOptionModel(optionName.replace("all","right"),    $scope.component.options[id]['model'][optionName]);
            $scope.setOptionModel(optionName.replace("all","bottom"),   $scope.component.options[id]['model'][optionName]);
            $scope.setOptionModel(optionName.replace("all","left"),     $scope.component.options[id]['model'][optionName]);
            $scope.functionEnd("setOption()");
            return;
        }

        var component = {
            id: id,
            tag: tag,
            optionName: optionName,
            isShortcode: isShortcode
        }

        // update custom-selector options
        if ( $scope.isEditing('custom-selector') ) {

            //$scope.selectorDetector.mode = false;
            
            var parameter   = component.optionName,
                value       = $scope.component.options[component.id]['model'][parameter];
            
            // don't include not CSS options
            if ( parameter == 'selector'    || 
                 parameter == 'ct_id'       || 
                 parameter == 'ct_parent'   ||
                 parameter == 'ct_content'  || 
                 parameter == 'classes'     || 
                 ( parameter == "url" && component.isShortcode ) ) 
            {
               // nothing here
            }
            else {
                $scope.updateCustomSelectorValue(parameter, value);
                delete $scope.cache.classStyles[$scope.currentClass];
                $scope.outputCSSOptions();
                $scope.unsavedChanges();

                $scope.functionEnd("setOption()");
                return;
            }
        }
        // update Components Tree
        else if ( optionName !== undefined ) {
            $scope.findComponentItem($scope.componentsTree.children, id, $scope.updateTreeComponentOption, component);
        } else {
            // update all options if option name is not set
            $scope.findComponentItem($scope.componentsTree.children, id, $scope.updateTreeComponentOptions, component);
        }

        $scope.unsavedChanges();

        // render shortcode
        if ( isShortcode ) {
            $scope.renderShortcode(id, tag);
        }

        // don't update CSS options when editing content
        if ( optionName == "ct_content" || notUpdateCSS ) {
            $scope.functionEnd("setOption()");
            return;
        }

        // update styles
        $scope.outputCSSOptions(id);

        // if there is no active selector on the current component, have it display ID as current selector
        if($scope.isNotSelectedYet(id) && typeof(optionName) !== 'undefined') // && optionName !== 'ct_content')
            $scope.switchEditToId(true);

        $scope.functionEnd("setOption()");
    }


    /**
     * Set option model value and update Tree
     *
     * @since 0.3.0
     * @author Ilya K.
     * @return {string}
     */
    
    $scope.setOptionModel = function(optionName, optionValue, id, name, notUpdateCSS) {

        if ($scope.log) {
            console.log("setOptionModel()", id, optionName, optionValue);
            $scope.functionStart("setOptionModel()");
        }

        if (undefined === id) {
            id = $scope.component.active.id;
        }
        if (undefined === name) {
            name = $scope.component.active.name;
        }
        
        // update model
        $scope.component.options[id]['model'][optionName] = optionValue;

        // update Tree
        $scope.setOption(id, name, optionName, false, notUpdateCSS);

        $scope.functionEnd("setOptionModel()");
    }


    /**
     * Get option from model
     *
     * @since 0.3.0
     * @author Ilya K.
     * @return {string}
     */
    
    $scope.getOption = function(optionName, id) {

        if (undefined === id) {
            id = $scope.component.active.id;
        }

        if ( $scope.component.options[id]['model'] && $scope.component.options[id]['model'][optionName] !== undefined ) {
            return $scope.component.options[id]['model'][optionName];
        }
        else {
            return "";
        }
    }


    /**
     * Get Component's CSS options by id
     * 
     * @since 0.1
     * @author Ilya K.
     * @return {Array} [key values pairs of CSS properties]
     */

    $scope.getCSSOptions = function(id, stateName, customOptions, componentName) {

        var customCss = [],
            options = {};

        // use passed options (for classes and custom selectors)
        if ( customOptions ) {
            options = angular.copy(customOptions);
        }
        // or get component options
        else {
            if (undefined === id) {
                return {};
            }
            if (undefined === stateName) {
                stateName = $scope.currentState;
            }
            if ($scope.component.options[id]) {
                options = angular.copy($scope.component.options[id][stateName]);
            }
        }

        options = options || {};
        
        if ($scope.log) {
            console.log("getCSSOptions()", id, stateName, options);
        }

        // handle background-image option
        if ( options['background-image'] ) {
            
            options['background-image'] = "url("+options['background-image']+")";

            // trick for overlay color
            if ( options['overlay-color'] ) {
                options['background-image'] = 
                    "linear-gradient(" + options['overlay-color'] + "," + options['overlay-color'] + "), " + options['background-image'];
            }
        }

        // handle options with units 
        for(var name in options) { 
            if (options.hasOwnProperty(name)) {

                if (name.indexOf('-unit')<0) {

                    if (!componentName) {
                        // #ID
                        if (id && id > 0) {
                            componentName = $scope.component.options[id].name;    
                        }
                        // .class
                        else {
                            componentName = "all";
                        }
                    }

                    // skip options with no units
                    if ( undefined == $scope.defaultOptions[componentName][name+'-unit'] ) {
                        continue;
                    }

                    if (options[name+'-unit'] == 'auto') {
                        options[name] = 'auto';
                    }
                    else {
                        var unit = ( options[name+'-unit'] ) ? options[name+'-unit'] : $scope.defaultOptions[componentName][name+'-unit'];
                        if ( options[name] ) {
                            options[name] += unit;
                        }
                    }

                    delete options[name+'-unit'];
                }
                else {
                    if (options[name] == 'auto') {
                        options[name.replace("-unit", "")] = 'auto';
                    }
                }
            }
        }

        // delete all -unit options
        for(var name in options) { 
            if (options.hasOwnProperty(name) && name.indexOf("-unit") > 0) {
                delete options[name];
            }
        }

        // handle background-position option
        if ( options['background-position-top'] || options['background-position-left'] ) {

            var top   = options['background-position-top'] || "0%",
                left  = options['background-position-left'] || "0%";
            
            options['background-position'] = left;
            options['background-position'] += " " + top;
        }

        // remove fake properties
        options['background-position-top'] = null;
        options['background-position-left'] = null;

        // handle background-size option
        if ( options['background-size'] == "manual" ) {

            var width   = options['background-size-width'] || "auto",
                height  = options['background-size-height'] || "auto";
            
            options['background-size'] = width;
            options['background-size'] += " " + height;
        }

        // remove fake properties
        options['background-size-width'] = null;
        options['background-size-height'] = null;

        // handle font
        if ( options['font-family'] && options['font-family'][0] == 'global' ) {
            if ( customOptions ) {
                options['font-family'] = $scope.getGlobalFont( options['font-family'][1] );
            }
            else {
                options['font-family'] = $scope.getComponentFont(id, false, stateName); // TODO: add support for custom selectors
            }
        }
        
        return options;
    }


    /**
     * Check if current component option is different from original
     *
     * @since 0.1.4
     */
    
    $scope.checkOptionChanged = function(id, name) {

        if ($scope.log) {
            //console.log("checkOptionChanged", id, name);
        }

        // TODO: make it work with custom selectors
        if ( $scope.isEditing('custom-selector') ) {
            return false;
        }

        var original = false,
            current  = false;

        // check if changed in current id's state
        if ( $scope.isEditing('state') && $scope.isEditing('id') ) {
        
            // get current state option value
            if ( $scope.component.options[id][$scope.currentState] ) {
                current = $scope.component.options[id][$scope.currentState][name];
            }
            
            // no state option
            if ( ! current ) {
                return false;
            }

            original = $scope.component.options[id]['original'][name];

            // check global fonts
            if ( name == "font-family" ) {
                if (    current && 
                        current[0]  == 'global' && 
                        original[0] == 'global' && 
                        current[1] == original[1] 
                    ) {
                    
                    return false;
                }
            }

            // check and return
            if ( original && current != original ) {
                return "ct-option-different";
            }
        }
        // check if changed in current class
        else {

            if ( $scope.classes[$scope.currentClass] && 
                 $scope.classes[$scope.currentClass][$scope.currentState] &&
                 $scope.classes[$scope.currentClass][$scope.currentState][name] ) 
            {
                return "ct-option-different";
            }
        }
    }


    /**
     * Check what user is currently editing
     *
     * @since 0.1.7
     * @return {bool}
     * @author Ilya K.
     */

    $scope.isEditing = function(query) {

        switch (query) {
            
            case "id" :
                return ( $scope.currentClass === false && $scope.component.active.id >= 0 ) ? true : false;
                break;

            case "class" :
                return ( $scope.currentClass !== false ) ? true : false;
                break;

            case "state" :
                return ( $scope.currentState != "original" ) ? true : false;
                break;

            case "pseudo-element" :
                return ( $scope.isPseudoElement($scope.currentState) ) ? true : false;
                break;

            case "custom-selector" :
                return ( $scope.selectorToEdit !== false ) ? true : false;
                break;

            case "style-sheet" :
                return ( $scope.stylesheetToEdit !== false && typeof($scope.stylesheetToEdit) !== 'undefined' ) ? true : false;
                break;

            case "media" :
                return ( $scope.currentMedia !== "default" ) ? true : false;
                break;

            default:
                return false;
        }
    }


    /**
     * Switch editing state to 'id'
     *
     * @since 0.1.7
     * @author Ilya K.
     */
    
    $scope.switchEditToId = function(explicitly) {
        
        if ($scope.log) {
            console.log('switchEditToId()', $scope.component.active.id)
        }

        var isEditingId = $scope.isEditing('id');

        $scope.setCustomSelectorToEdit(false);
        
        if(!$scope.activeSelectors[$scope.component.active.id])
            $scope.setCurrentClass(false);

        // if done explicitly via selecting from the selectors dropdown
        if(typeof(explicitly) !== 'undefined' && explicitly === true) {
            $scope.activeSelectors = $scope.activeSelectors || {};
            $scope.activeSelectors[$scope.component.active.id] = false;
            $scope.setCurrentClass(false);
        }

        $scope.switchState("original");
        $scope.showClasses = false;

        if ( isEditingId ) {
            return false;
        }
    }


    /** 
     * Switch editing state to 'id' and media to 'default'
     *
     * @since 1.0.1
     * @author Ilya K.
     */
    
    $scope.setEditingStateToDefault = function() {

        $scope.switchEditToId(true);
        $scope.setCurrentMedia("default");

        // safely apply scope
        var timeout = $timeout(function() {
            $scope.$apply();
            $timeout.cancel(timeout);
        }, 0, false);
    }


    /**
     * Called when any of the page settings changed
     *
     * @since 0.2.3
     */
    
    $scope.pageSettingsUpdate = function() {
        
        // update cache
        $scope.updateAllComponentsCacheStyles();
        // output CSS
        $scope.outputCSSOptions();
        $scope.outputPageSettingsCSS();

        $scope.adjustViewportContainer();
        
        $scope.unsavedChanges();
    }


    /**
     * Set option unit like 'px', 'em' for options like margin, padding, etc
     *
     * @since 0.3.0
     */
    
    $scope.setOptionUnit = function(option, unit, notUpdateCSS) {

        var optionName = option+"-unit",
            id = $scope.component.active.id,
            tag = $scope.component.active.name;

        // udpate model
        $scope.component.options[id]['model'][optionName] = unit;

        $scope.setOption(id, tag, optionName, false, notUpdateCSS);

        $scope.applyModelOptions();
    }


    /**
     * Get option unit like 'px', 'em' for options like margin, padding, etc
     *
     * @since 0.3.0
     * @return {string}
     */
    
    $scope.getOptionUnit = function(option) {

        var optionName = option+"-unit",
            id = $scope.component.active.id;

        if ( $scope.component.options[id]['model'] && $scope.component.options[id]['model'][optionName] ) {
        	return $scope.component.options[id]['model'][optionName];
        }
        else {
            return $scope.defaultOptions["all"][optionName];
        }
    }


    /**
     * Get component selector
     *
     * @since 0.3.0
     * @return {string}
     */

    $scope.getComponentSelector = function(id) {

        if (undefined == id) {
            id = $scope.component.active.id;
        }
        
        return $scope.component.options[id]['selector'];
    }


    /**
     * Apply component JS
     *
     * @since 0.3.1
     */

    $scope.applyComponentJS = function(id, name, updateTree) {

        $scope.applyingComponentJS = true;
        jQuery(".ct-js-error-container", "#ct-toolbar").hide().html("");

        if ($scope.log) {
            console.log("applyComponentJS()", id, name);
        }

        if (undefined==id) {
            id = $scope.component.active.id;
        }

        if (undefined==name) {
            name = $scope.component.active.name;
        }

        if (undefined==updateTree) {
            updateTree = true;
        }

        if (updateTree) {
            $scope.setOption(id, name, 'custom-js', false, false);
        }

        var customJS = $scope.getOption('custom-js', id);

        // output if not equal to default value
        if ($scope.defaultOptions[name]['custom-js'] !== customJS ) {
            $scope.outputJSScript("ct_custom_js_", id, customJS);       
        }

        // We don't have Custom JS for any other states
        // output to DOM
        /*angular.forEach($scope.component.options[id], function(option, key) {
            if(key === 'media') {
                // media styles shouldn't have custom js
                angular.forEach(option, function(breakpoint, bpkey) {
                    angular.forEach(breakpoint, function(bpstate, statekey) {
                        if( bpstate['custom-js']){
                            //console.log(bpkey+" "+statekey);
                            $scope.outputJSScript("ct_custom_js_"+bpkey+"-"+statekey+"-", id, bpstate['custom-js']);
                        }
                    });
                });
            }
            else if( key !== 'model' && key !== 'id' && option['custom-js']) {
                $scope.outputJSScript("ct_custom_js_"+key+"-", id, option['custom-js']);
            }
        });*/

        $scope.applyingComponentJS = false;
    }


    /**
     * Check options to see if this current id option 
     * or inherited from defaults
     *
     * @since 0.3.2
     * @return {string} CSS class to grey out values
     */

    $scope.isInherited = function(id, optionName, optionValue) {

        if (undefined===optionValue) {
            optionValue = $scope.getOption(optionName, id);
        }

        // skip not active option
        if ($scope.getOption(optionName, id) != optionValue) {
            return false;
        }

        // editing id 'original'
        if ($scope.isEditing("id") && !$scope.isEditing("media") && !$scope.isEditing("state")) {
            if ($scope.component.options[id]["id"][optionName] == optionValue) {
                return false;
            }
        }

        // editing id state
        if ($scope.isEditing("id") && !$scope.isEditing("media") && $scope.isEditing("state")) {
            if ($scope.component.options[id][$scope.currentState] &&
                $scope.component.options[id][$scope.currentState][optionName] == optionValue) {
                return false;
            }
        }

        // editing id media
        if ($scope.isEditing("id") && $scope.isEditing("media") ) {
            if ( $scope.component.options[id]['media'] &&
                 $scope.component.options[id]['media'][$scope.currentMedia] &&
                 $scope.component.options[id]['media'][$scope.currentMedia][$scope.currentState] &&
                 $scope.component.options[id]['media'][$scope.currentMedia][$scope.currentState][optionName] == optionValue) {
                return false;
            }
        }

        // editing class
        if ($scope.isEditing("class") && !$scope.isEditing("media") ) {
            if ($scope.classes[$scope.currentClass] &&
                $scope.classes[$scope.currentClass][$scope.currentState] &&
                $scope.classes[$scope.currentClass][$scope.currentState][optionName] == optionValue) {
                return false;
            }
        }

        // editing class media
        if ($scope.isEditing("class") && $scope.isEditing("media") ) {
            if ( $scope.classes[$scope.currentClass] &&
                 $scope.classes[$scope.currentClass]['media'] &&
                 $scope.classes[$scope.currentClass]['media'][$scope.currentMedia] &&
                 $scope.classes[$scope.currentClass]['media'][$scope.currentMedia][$scope.currentState] &&
                 $scope.classes[$scope.currentClass]['media'][$scope.currentMedia][$scope.currentState][optionName] == optionValue) {
                return false;
            }
        }

        // editing custom selector
        if ($scope.isEditing("custom-selector") && !$scope.isEditing("class") ) {

            // media
            if ( $scope.isEditing("media") ) {
                if ( $scope.customSelectors[$scope.selectorToEdit] &&
                     $scope.customSelectors[$scope.selectorToEdit]['media'] &&
                     $scope.customSelectors[$scope.selectorToEdit]['media'][$scope.currentMedia] &&
                     $scope.customSelectors[$scope.selectorToEdit]['media'][$scope.currentMedia][$scope.currentState] &&
                     $scope.customSelectors[$scope.selectorToEdit]['media'][$scope.currentMedia][$scope.currentState][optionName] == optionValue) {
                    return false;
                }
            }

            // desktop
            if (
                $scope.customSelectors[$scope.selectorToEdit] &&
                $scope.customSelectors[$scope.selectorToEdit][$scope.currentState] &&
                $scope.customSelectors[$scope.selectorToEdit][$scope.currentState][optionName] == optionValue) {
                return false;
            }
        }

        return "ct-option-inherited";
    }


    /**
     * Check options if any of border values has an option
     * or inherited from defaults
     *
     * @since 0.3.2
     * @return {bool}
     */
    
    $scope.isBorderHasStyles = function(side) {

        var width = $scope.isInherited($scope.component.active.id, "border-"+side+"-width");
            style = $scope.isInherited($scope.component.active.id, "border-"+side+"-style");
            color = $scope.isInherited($scope.component.active.id, "border-"+side+"-color");

        return (!width||!style||!color) ? true : false;
    }


    /**
     * Check if tab has at least one option defined
     *
     * @since 0.3.2
     * @author Ilya K.
     * @return {bool}
     */

    $scope.isTabHasOptions = function(key, childKey) {

        if (!$scope.optionsHierarchy[key]) 
            return false;

        if (childKey===undefined) {
            
            for (var tab in $scope.optionsHierarchy[key]) { 
                if ($scope.optionsHierarchy[key].hasOwnProperty(tab)) {
                    
                    var subtub = $scope.optionsHierarchy[key][tab];
                    for (var index in subtub) { 
                        var optionName = subtub[index];
                        if ($scope.isInherited($scope.component.active.id, optionName)===false) {

                            return true;
                        }
                    }
                }
            }
        }
        else {

            if (!$scope.optionsHierarchy[key][childKey])
                return false;
                    
            var subtub = $scope.optionsHierarchy[key][childKey];
            for (var index in subtub) { 
                var optionName = subtub[index];
                if ($scope.isInherited($scope.component.active.id, optionName)===false) {

                    return true;
                }
            }
        }
    }


    /**
     * Check if global settings are different and propose user to change
     *
     * @since 1.1.1
     * @author Ilya K.
     */

    $scope.checkGlobalOptions = function(options) {

        // parse options
        try {
            options = JSON.parse(options);
        }
        catch (e) {
            console.log(options);
            return;
        }

        // check global options (fonts)
        if ( options.display !== undefined && options.text !== undefined ) {
            
            if ( $scope.globalSettings.fonts.Display != options.display ||
                 $scope.globalSettings.fonts.Text != options.text ) {

                var confirmed = confirm("This Design Set's recommended fonts are:\r"+
                                        "Display: "+options.display+"\r"+
                                        "Text: "+options.text+"\r"+
                                        "Would you like to change your fonts to the recommended fonts?");
            
                if (confirmed) {
                    // update global settings
                    $scope.setGlobalFont("Display", options.display);
                    $scope.setGlobalFont("Text", options.text);
                }
            }
        }

        // check page settings (page width)
        if ( options["page-width"] !== undefined ) {
            
            if ( $scope.pageSettings['max-width'] != options["page-width"] ) {

                var confirmed = confirm("This Design Set's recommended page width is "+options["page-width"]+
                                        "px, but the width of this page is "+$scope.pageSettings['max-width']+
                                        "px. Would you like to change your page width to "+options["page-width"]+"px?");
            
                if (confirmed) {
                    // update page settings
                    $scope.pageSettings['max-width'] = options["page-width"];
                    $scope.pageSettingsUpdate();
                }
            }
        }
    }

});