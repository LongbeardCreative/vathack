var CTFrontendBuilder = angular.module('CTFrontendBuilder', [angularDragula(angular),'ngAnimate','colorpicker.module','ui.codemirror']);

CTFrontendBuilder.controller("MainController", function($scope, $http, $timeout, $window) {

    // log
    $scope.log = false;

    $scope.isInnerContent = false;

    // store component HTML tags in an object
    $scope.htmlTags = {};

    // cache
    $scope.cache = [];

    $scope.iconFilter = {};

    // initial values
    $scope.component = {

        // currently active component
        active : {  
            id : 0,
            name : 'root',
            state : 'original', // element state like 'hover'
            parent: {
                id : null,
                name : ""
            }
        },

        // components counter
        id : 1,

        // all components options
        options: {
            0 : {
                'original' : {},
                'media' : {
                    'original' : {}
                }
            }
        }
    }


    /**
     * Build DOM based on Components Tree JSON
     * 
     * @since 0.1
     */
    
    $scope.init = function() {

        $scope.loadComponentsTree($scope.builderInit);
        
        // fonts
        $scope.getWebFontsList();
        $scope.loadSavedGlobalFonts();
        $scope.updateGlobalFontCSS();
        
        $scope.loadSVGIconSets();

        $scope.setupUI();
        $scope.setupPageLeaveProtection();
        $scope.setupJSErrorNotice();

        $scope.adjustViewportContainer();
        $scope.outputPageSettingsCSS();

        $scope.checkAPIstatus()        


        

        $scope.outputPageSettingsCSS();

        $scope.isInnerContent = jQuery('body').hasClass('ct_inner');
        

    }

    $scope.recursive_disableDivs = function (element) {

        var siblings = element.siblings();

        siblings.each(function() {
            jQuery(this).addClass('ct-disabled-div');
        });

        var parent = element.parent();

        if(parent.attr('id') !== 'ct-builder') {
            $scope.recursive_disableDivs(parent);  
        }
    }

    $scope.disableOuterDivs = function() {

        //var recursive_disableDivs = 

        $scope.recursive_disableDivs(jQuery('.ct_inner_content'));

        jQuery('.ct-disabled-cover').remove();

        jQuery('.ct-disabled-div').each(function() {

            //var t = jQuery(this).offset().top;
            //var l = jQuery(this).offset().left;
            var w = jQuery(this).width();
            var h = jQuery(this).height();

            jQuery(this).append(jQuery('<div>').addClass('ct-disabled-cover').css({ 
                top: 0,
                left: 0,
                width: w+'px',
                height: h+'px',
            }));
        })

    }

    /**
     * Build DOM from Components Tree
     * 
     * @since 0.1
     */

    $scope.buildComponentsFromTree = function(componentsTree, id, reorder) {

        if ($scope.log) {
            //console.log("buildComponentsFromTree()", id);
        }

        // handle root
        if ( 0 == id ) {
            var element = $scope.getComponentById(id);
            element.empty();
            element = null;
            $scope.buildComponentsFromTree($scope.componentsTree.children);

            return false;
        }


        var stopBuilding = false;

        //for(var index in componentsTree) { 
            //if (componentsTree.hasOwnProperty(index)) {
                //var item = componentsTree[index];

        //for (index = 0; index < componentsTree.length; ++index) {
            //var item = componentsTree[index];

        angular.forEach(componentsTree, function(item, index) {

            if ( !stopBuilding ) {

                var key = item.id;

                // if we only need to rebuild specific node
                if ( id ) {
                    
                    // found node
                    if ( key == id ) {
                        if(!reorder)
                            $scope.removeComponentFromDOM(id);
                        stopBuilding = true;
                    } 
                    else {
                        // go deeper to find
                        if ( item.children ) {
                            $scope.buildComponentsFromTree(item.children, id, reorder);
                        }
                        // stop from building
                        return false;
                    }
                }
                
                /**
                 * Apply all options
                 */
                
                // set default options first
                $scope.applyComponentDefaultOptions(key, item.name);

                // set saved 'original' options
                $scope.applyComponentSavedOptions(key, item);

                // add saved classes
                if ( item.options.classes ) {
                    $scope.componentsClasses[key] = angular.copy(item.options.classes);
                }

                // apply model
                $scope.applyModelOptions(key, item.name);

                // save styles to cache
                $scope.updateComponentCacheStyles(key);

                /**
                 * Start building
                 */
                
                var type = "";

                if ( item.options.ct_shortcode ) {
                    type = "shortcode";
                }

                if ( item.options.ct_widget ) {
                    type = "widget";
                }

                var componentParent     = $scope.getComponentById(item.options.ct_parent),
                    componentTemplate   = $scope.getComponentTemplate(item.name, key, type);

                
                if(item.id > 100000) {
                         
                    componentTemplate = componentTemplate.replace(/\s?[^\=\s]*\=\"[^\"]*\"/g, 
                        function(match, contents, offset, s)
                        {
                            if(match.indexOf(' ng-mouse') !== 0) {//(match.indexOf(' class') === 0 || match.indexOf(' id') === 0))
                                /*if(match.indexOf(' class') === 0 && item.name !== "ct_inner_content") {
                                    match = match.substring(0 , match.length-1)+', disabled-div"';
                                }*/
                                return match;
                            }
                            else
                                return '';
                        }
                    );
                }
                
                // set columns number for columns component
                if ( item.name == "ct_columns" && item.children ) {
                    $scope.columns[item.id] = item.children.length;
                }

                // handle HTML component
                if ( item.name == "ct_html" ) {
                    
                    var componentId = item.options.ct_id;
                    
                    // insert to DOM
                    var innerWrap = $scope.getInnerWrap(componentParent);
                    $scope.cleanInsert(componentTemplate, innerWrap, index);
                    
                    // add html
                    var timeout = $timeout(function() {

                        var html = $scope.component.options[componentId]['model']['html'],
                            HTMLComponent = $scope.getComponentById(componentId);

                        HTMLComponent.html(html);
                        
                        // cancel timeout
                        $timeout.cancel(timeout);
                    }, 0, false);
                }
                else

                // handle Re-usable components
                if ( item.name == "ct_reusable" ) {
                    
                    var viewId      = item.options.view_id,
                        componentId = item.options.ct_id;
                    
                    // insert to DOM
                    var innerWrap = $scope.getInnerWrap(componentParent);
                    $scope.cleanInsert(componentTemplate, innerWrap, index);
                    // load post
                    $scope.loadPostData($scope.addReusableContent, viewId, componentId);
                }
                else

                // handle span component
                if ( item.name == "ct_span" || (item.name == "ct_link_text" && (!componentParent[0] || componentParent[0].attributes['contenteditable']))) {

                    if ( componentParent[0] && !componentParent[0].attributes['contenteditable'] ) {
                        $scope.cleanInsert(componentTemplate, componentParent, index);
                    }
                    else {
                        var placeholder = document.getElementById("ct-placeholder-"+item.id);
                        $scope.cleanReplace(placeholder,componentTemplate);
                    }

                }
                else

                // handle ct_link component
                if ( item.name == "ct_link" && componentParent[0] && componentParent[0].attributes['contenteditable'] ) {
                    
                    var timeout = $timeout(function() {

                        var placeholder = document.getElementById("ct-placeholder-"+item.id);

                        $scope.cleanReplace(placeholder,componentTemplate);
                        $scope.rebuildDOM(item.children[0].id);

                        // get highest id number
                        if ( parseInt(item.id) > $scope.component.id && parseInt(item.id) < 100000) {
                            $scope.component.id = parseInt(key);
                        }
                        if ( parseInt(item.id) == $scope.component.id ) {
                            $scope.component.id++;
                        }

                        // cancel timeout
                        $timeout.cancel(timeout);
                    }, 0, false);

                    return;
                } 

                // handle other components
                else {
                    var innerWrap = $scope.getInnerWrap(componentParent);
                    $scope.cleanInsert(componentTemplate, innerWrap, index);
                }

                if ( item.name == "ct_separator" ) {
                    $scope.separatorAdded = true;
                }

                if (item.name == "ct_inner_content") {
                    $scope.innerContentAdded = item;
                }

                // get highest id number
                if ( parseInt(key) > $scope.component.id  && parseInt(key) < 100000) {
                    $scope.component.id = parseInt(key);
                }
                
                // go deeper in Components Tree
                if ( item.children ) {
                    $scope.buildComponentsFromTree(item.children, null, reorder);
                }
            }
        });
        
            //}
        //}
    }


    /**
     * Get currently active component DOM element
     * 
     * @return {jqLite Object}
     * @since 0.1
     */

    $scope.getActiveComponent = function() {

        if ($scope.log) {
            console.log("getActiveComponent()");
        }

        return $scope.getComponentById($scope.component.active.id);
    }


    /**
     * Get component DOM element by ID
     * 
     * @return {jqLite Object}
     * @since 0.1
     */

    $scope.getComponentById = function(id) {

        if ($scope.log) {
            console.log("getComponentById()", id);
        }
        
        // get element by id
        var component = document.querySelector('[ng-attr-component-id="'+id+'"]');

        // return false if no active component found
        if ( !component || component.length == 0 ) {
            return false;
        }
        
        // create jqLite element
        component = angular.element(component);
        
        return component;
    }


    /**
     * Remove currently active component
     * 
     * @since 0.1
     */

    $scope.removeActiveComponent = function() {

        $scope.removeComponentWithUndo($scope.component.active.id, $scope.component.active.name, $scope.component.active.parent.id);
        //$scope.removeComponentById($scope.component.active.id, $scope.component.active.name);
    }
    

    /**
     * Set editable status for component's friendly name in the DOM tree
     * 
     * @since 0.3.3
     * @author gagan goraya
     */    

    $scope.setEditableFriendlyName = function(id) {

        $scope.editableFriendlyName = id;

        // remove &nbsp characters and use default text
        var element = angular.element('[ng-attr-node-id="'+$scope.component.active.id+'"] span.ct-nicename');

        var item = $scope.findComponentItem($scope.componentsTree.children, $scope.component.active.id, $scope.getComponentItem);
        
        var trimmedText = $scope.component.options[$scope.component.active.id]['nicename'].replace(/&nbsp;/g, '');
        
        if(trimmedText === '' && typeof(element.data('defaulttext')) !== 'undefined' && element.data('defaulttext').trim() !== '') {
            trimmedText = element.data('defaulttext');
        }

        item.options['nicename'] = trimmedText;

        
        $scope.component.options[$scope.component.active.id]['nicename'] = trimmedText;
       
        
        // close the menu
        if(id > 0)
            jQuery(".ct-more-options-expanded", "#ct-sidepanel").removeClass("ct-more-options-expanded");
            //jQuery('#ct-dom-tree-node-' + id + ' .ct-more-options-icon').trigger("mousedown");
    }

    /**
     * searches for the node in the component tree and apply function to update nicename
     * 
     * @since 0.3.3
     * @author gagan goraya
     */    

    $scope.updateFriendlyName = function(id) {
        $scope.findComponentItem($scope.componentsTree.children, id, $scope.updateComponentNiceName);
    }

    /**
     * updates the nicename into the provided item out of the component tree
     * 
     * @since 0.3.3
     * @author gagan goraya
     */   

    $scope.updateComponentNiceName = function(id, item) {
        item.options['nicename'] = $scope.component.options[id]['nicename'];
    }


    /**
     * loads the nicename into the current state from the item of the component tree
     * 
     * @since 0.3.3
     * @author gagan goraya
     */   

    $scope.loadComponentNiceName = function(id, item) {
        if(item.options['nicename'])
            $scope.component.options[id]['nicename'] = item.options['nicename'];
    }


    /**
     * Cut the component and display notice to undo this action
     * 
     * @since 1.2
     */

    $scope.removeComponentWithUndo = function(id, name, parentId) {
        
        $scope.removeComponentById(id, name, parentId);

        // create notice        
        var noticeContent = "<div>You deleted " + $scope.component.options[id]['nicename'] + 
                            ". <span class=\"ct-undo-delete\" ng-click=\"undoDelete()\">Undo</span></div>";

        $scope.showNoticeModal(noticeContent);
    }

    
    /**
     * Cancel remove timeout
     * 
     * @since 1.2
     */

    $scope.undoDelete = function() {

        // find component to paste
        $scope.findComponentItem($scope.componentsTree.children, $scope.componentInsertId, $scope.pasteComponentToTree);

        $scope.rebuildDOM($scope.removedComponentId);
        $scope.updateDOMTreeNavigator($scope.removedComponentId);

        $scope.outputCSSOptions($scope.removedComponentId)

        $scope.cancelDeleteUndo();
    }


    /**
     * Cancel remove timeout
     * 
     * @since 1.2
     */

    $scope.cancelDeleteUndo = function(id, name) {
        
        $scope.hideNoticeModal();
    }


    /**
     * Show notice modal
     * 
     * @since 1.2
     */

    $scope.showNoticeModal = function(noticeContent) {

        $scope.noticeModalVisible = true;

        // set content
        var noticeContentContainer = document.getElementById("ct-notice-content");
        noticeContentContainer = angular.element(noticeContentContainer);

        // inseret to DOM
        angular.element(document).injector().invoke(function($compile) {
            noticeContentContainer.html($compile(noticeContent)($scope));
        });
    }


    /**
     * Hide notice modal
     * 
     * @since 1.2
     */

    $scope.hideNoticeModal = function() {

        // clear content
        var noticeContentContainer = document.getElementById("ct-notice-content");
        angular.element(noticeContentContainer).html("");

        $scope.noticeModalVisible = false;
    }

    
    /**
     * Remove component from DOM and Components Tree
     * 
     * @since 0.1.8
     */

    $scope.removeComponentById = function(id, name, parentId) {

        if ($scope.log) {
            console.log("removeComponentById()", id);
        }

        if ( id === 0 ) {
            alert('You can not delete root!');
            return;
        }

        // switch state
        $scope.switchState('original');

        // save IDs in scope
        $scope.componentInsertId = parentId;
        $scope.removedComponentId = id;

        // update active parent
        $scope.findParentComponentItem($scope.componentsTree, id, $scope.updateCurrentActiveParent);

        // handle column remove
        if ( name == "ct_column" ) {
            $scope.removeColumn(id);
        } 
        else {

            // remove from Components Tree
            $scope.findParentComponentItem($scope.componentsTree, id, $scope.cutComponentFromTree);

            // remove from DOM
            var component = $scope.getComponentById(id);

            // save index globally
            $scope.newComponentKey = component.index();
            // not sure why we were need the index
            //$scope.newComponentKey = -1;

            component.hide("slow", function() {
                if ( component.scope() ){
                    component.scope().$destroy();
                }
                component.remove();
                component = null;

                // handle span component
                if ( name == "ct_span" ) {
                    parent = $scope.getActiveComponent();

                    $scope.component.options[$scope.component.active.id]["model"]["ct_content"] = parent[0].innerHTML;
                    $scope.component.options[$scope.component.active.id]["original"]["ct_content"] = parent[0].innerHTML;
                    $scope.setOption($scope.component.active.id, $scope.component.active.name, "ct_content");

                    $scope.rebuildDOM($scope.component.active.id);
                }
            });

            $scope.removeDOMTreeNavigatorNode(id);
        }

        // header/footer separator
        if ( name == "ct_separator" ) {
            $scope.separatorAdded = false;
        }

        if ( name == "ct_inner_content" ) {
            $scope.innerContentAdded = false;
        }

        // activate parent
        $scope.activateComponent($scope.component.active.parent.id, $scope.component.active.parent.name);

        // remove custom-css
        $scope.deleteCSSStyles("css-code-"+id);
        
        // clear styles cache
        $scope.removeComponentCacheStyles(id);

        $scope.contentEditingEnabled = false;
        $scope.linkEditingEnabled = false;

        $scope.unsavedChanges();
    }

    /**
     * Duplicate component by ID
     * 
     * @since 0.3.0
     */

    $scope.duplicateComponent = function(id, parentId) {

        if (undefined === id) {
            id = $scope.component.active.id;
        }

        if (undefined === parentId) {
            parentId = $scope.component.active.parent.id;
        }

        // never duplicate a ct_inner_content, because there can be only one
        if($scope.component.active.name === 'ct_inner_content') {
            alert('You cannot add more than one Inner Content component to a template.');
            return;
        }

        var newComponentId  = $scope.component.id;

        // copy active Selector state if the it is 'ID'
        if(typeof($scope.activeSelectors[id]) !== 'undefined') {
            if($scope.activeSelectors[id] === false) {
                $scope.activeSelectors[newComponentId] = false;
            }
        }

        $scope.applyComponentDefaultOptions(newComponentId, $scope.component.active.name);
            
        // copy component tree node
        $scope.findParentComponentItem($scope.componentsTree, id, $scope.copyComponentTreeNode);

        // find component to paste
        $scope.findComponentItem($scope.componentsTree.children, parentId, $scope.pasteComponentToTree);

        $scope.activateComponent($scope.componentBuffer.id, $scope.componentBuffer.name);
        
        // increase columns number
        if ( $scope.isActiveParent("ct_columns") ) {
            $scope.columns[$scope.component.active.parent.id]++;
        }
    
        $scope.rebuildDOM(newComponentId);
        $scope.updateDOMTreeNavigator(newComponentId);
        $scope.outputCSSOptions(newComponentId);

        // Lets keep the newly added component in memory, this will help to distinguish these from the ones loaded from db
        $scope.justaddedcomponents = $scope.justaddedcomponents || [];
        $scope.justaddedcomponents.push(newComponentId);

        // copy active Selector state. If it is current class
        if(typeof($scope.activeSelectors[id]) !== 'undefined') {
            
            if($scope.activeSelectors[id] !== false)
                $scope.setCurrentClass($scope.activeSelectors[id], true); // the second parameter signifies that we do not want to re-apply model options atm
        }

        // disable undo option
        $scope.cancelDeleteUndo();
    }

    
    /**
     * Activate Component
     * 
     * @since 0.1
     * @author Ilya K.
     */
    
    $scope.activateComponent = function(id, componentName, $event) {

        if ($scope.log) {
            console.log("activateComponent()", id, componentName);
        }

        // do nothing if in selector detector mode
        if ( $scope.selectorDetector.mode && (componentName=="ct_shortcode" || componentName=="ct_code_block" || componentName=="ct_widget") ) { 
            
            if ( $scope.componentSelector.id == id ) {
                $scope.selectorDetector.bubble = true;
                return false;
            }
        }

        // check if we are bubbling up from component with selector mode activated
        if ( id > 0 && $scope.selectorDetector.mode && $scope.selectorDetector.bubble ) {
            return false;
        }

        // disable selector detector mode
        if (componentName!="ct_selector"&&$scope.disableSelectorDetectorMode) {
            $scope.disableSelectorDetectorMode();
        }

        if (undefined===componentName) {
            componentName = $scope.component.options[id].name || "";
        }

        if (componentName=="ct_widget"||componentName=="ct_shortcode"||componentName=="ct_code_block") {
            $scope.selectorDetector.mode = true;
        }

        $scope.stylesheetToEdit = false;
        $scope.actionTabs['styleSheet'] = false;

        // Fix for nested ng-click
        if (typeof $event != 'undefined') {
            // ok, but let it reach the document atleast or some click bindings on the document will not work
            angular.element(document).trigger($event.type);
            $event.stopPropagation();
        }

        if(componentName === 'ct_svg_icon') {
            var currenticonid = $scope.component.options[id]['model']['icon-id'];
            $scope.iconFilter.title = '';
            angular.forEach($scope.SVGSets, function(SVGSet, index) {
                if(currenticonid.indexOf(index.split(' ').join('')) === 0) {
                    $scope.currentSVGSet = index;
                    angular.forEach(SVGSet['defs']['symbol'], function(symbol) {
                        if(symbol['@attributes']['id'] === currenticonid.replace(index.split(' ').join(''), ''))
                            $scope.iconFilter.title = symbol.title;
                    });     
                }
            });
        }

        // No need to actiavte component that already active
        if (id == $scope.component.active.id) {
            return false;
        }

        // save component where selector detector were activate
        if (id !== -1) {
            $scope.componentSelector.id = id;
        }

        // disable selector detector pause mode
        $scope.selectorDetector.modePause = false;
        
        jQuery(".ct-more-options-expanded", "#ct-sidepanel").removeClass("ct-more-options-expanded");
        jQuery(".ct-highlight", "#ct-builder").removeClass('ct-highlight');

        // while editing inner content, there is no point in activating the inner_content container
        /*if(componentName === 'ct_inner_content' && jQuery('body').hasClass('ct_inner')) {
            $scope.disableContentEdit();
            $scope.disableSelectable();
            $scope.closeAllTabs(["advancedSettings","componentBrowser"]); // keep certain sections
            return;
        }*/

        // fire blur on the previous nav component, if its nicename is being edited
        var previousNavElement = angular.element('[ng-attr-node-id="'+$scope.component.active.id+'"]');
        if(previousNavElement.find('span.ct-nicename.ct-nicename-editing').length) {

            $timeout(function() {
                previousNavElement.find('span.ct-nicename.ct-nicename-editing').trigger('blur');
            }, 0);
        }

        $scope.highlightDOMNode(id);

        // disable stuff
        $scope.disableContentEdit();
        $scope.disableSelectable();
        $scope.closeAllTabs(["advancedSettings","componentBrowser"]); // keep certain sections

        // update active component id and name
        $scope.previouslyActiveId       = $scope.component.active.id;
        $scope.component.active.id      = id;
        $scope.component.active.name    = componentName;

        if ( id > 0) {
            // set all edits (class, state...) back to id
            $scope.switchEditToId();

            // update active component parent
            $scope.findParentComponentItem($scope.componentsTree, id, $scope.updateCurrentActiveParent);
        }

        // Hide panels
        $scope.showComponentsList   = false;
        $scope.linkEditingEnabled   = false;

        // apply options
        $scope.applyModelOptions();

        // update CSS for disabled component
        $scope.outputCSSOptions($scope.previouslyActiveId);

        $scope.updateUpButton();
        //$scope.updateBreadcrumbs();
        $scope.checkTabs();
    }


    /**
     * Update current active parent
     *
     * @since 0.1.5
     */

    $scope.updateCurrentActiveParent = function(key, item) {

        $scope.component.active.parent.id   = item.id;
        $scope.component.active.parent.name = item.name;
    }


    /**
     * Update current active parent
     *
     * @since 0.1.5
     */

    $scope.isActiveParent = function(name) {

        return ($scope.component.active.parent.name == name) ? true : false;
    }


    /**
     * Get Component Tempalte based on its name
     * 
     * @since 0.1
     * @author Ilya K.
     */

    $scope.getComponentTemplate = function (componentName, id, type) {

        if ($scope.log) {
            console.log("getComponentTemplate()", componentName, id, type);
        }

        var options   = 'ng-mousedown="activateComponent('+id+ ', \''+componentName+'\', $event);" ' +
                        'ng-attr-component-id="'+id+'" ' + 
                        'ng-class="{\'ct-active\' : isActiveId('+id+')}" ' +
                        'id="{{component.options['+id+'].selector}}" ',

            classes     = 'class="{{getComponentsClasses('+id+', \''+componentName+'\')}}" ',

            template    = "";

        if (type != "shortcode" && type != "widget") {

            switch (componentName) {

                case 'ct_section':

                    classes = 'class="ct-section {{getComponentsClasses('+id+', \''+componentName+'\')}}" ';

                    template = '<div is-nestable="true" ' + options + classes + '>' +
                                    '<div class="ct-section-inner-wrap ct-inner-wrap"></div>' +
                                '</div>';
                    
                    break

                case 'ct_columns':

                    classes = 'class="ct-columns {{getComponentsClasses('+id+', \''+componentName+'\')}}" ';

                    template = '<div ' + options + classes + '>' +
                                    '<div class="ct-columns-inner-wrap ct-inner-wrap" ng-class="checkEmptyColumns('+id+')"></div>'+
                                '</div>';

                    break

                case 'ct_column': 

                    classes = 'class="ct-column {{getComponentsClasses('+id+', \''+componentName+'\')}}" ';
                    
                    template = '<div is-nestable="true" ' + options + classes + '></div>';
                    break

                case 'ct_headline':

                    var tag = $scope.component.options[id]['model'].tag;

                    template = '<'+tag+' contenteditable="false" ng-model="component.options['+id+'][\'model\'].ct_content" ng-model-options="{ debounce: 10 }" ' + options + classes +'></'+tag+'>';
                    break

                case 'ct_image':

                    template = '<img ng-src="{{component.options['+id+'][\'model\'].src}}"' + options + classes + '/>';
                    break

                case 'ct_text_block':

                    template = '<div contenteditable="false" ng-model="component.options['+id+'][\'model\'].ct_content" ng-model-options="{ debounce: 10 }" ' + options + classes + '></div>';
                    break

                case 'ct_paragraph':

                    template = '<div ng-attr-paragraph="true" contenteditable="false" ng-model="component.options['+id+'][\'model\'].ct_content" ng-model-options="{ debounce: 10 }" ' + options + classes + '></div>';
                    break

                case 'ct_div_block':

                    template = '<div is-nestable="true" ' + options + classes + '></div>';
                    break

                case 'ct_ul':

                    template = '<ul is-nestable="true" ' + options + classes + '></ul>';
                    break

                case 'ct_li':

                    template = '<li contenteditable="false" ng-model="component.options['+id+'][\'model\'].ct_content" ng-model-options="{ debounce: 10 }"' + options + classes + '></li>';
                    break

                case 'ct_span':

                    template = '<span ng-attr-span="true" contenteditable="false" ng-model="component.options['+id+'][\'model\'].ct_content" ng-model-options="{ debounce: 10 }" ' + options + classes + '></span>';
                    break

                case 'ct_link_text':

                    template = '<a href="" contenteditable="false" ng-model="component.options['+id+'][\'model\'].ct_content" ng-model-options="{ debounce: 10 }" ' + options + classes + '></a>';
                    break

                case 'ct_link':

                    template = '<a href="" draggable="false" ' + options + classes + 'id="{{component.options['+id+'].selector}}" is-nestable="true" ng-attr-component-id="' + id + '"></a>';
                    break

                case 'ct_svg_icon':
                    template = '<svg ' + options + classes + '><use xlink:href="" ng-href="{{\'#\'+component.options['+id+'][\'model\'][\'icon-id\']}}"></use></svg>';
                    break

                case 'ct_reusable':

                    template = '<div ' + options + classes + '></div>';
                    break

                case 'ct_separator':

                    template = '<div ' + options + classes + '></div>';
                    break

                case 'ct_code_block':

                    var tag = $scope.component.options[id]['model'].tag;

                    template = '<'+tag+' ' + options + classes + '></'+tag+'>';
                    var timeout = $timeout(function() {
                        $scope.applyCodeBlock(id, false);
                        
                        // cancel timeout
                        $timeout.cancel(timeout);
                    }, 0, false);
                    break

                case 'ct_inner_content':
                    classes = 'class="ct-inner-content {{getComponentsClasses('+id+', \''+componentName+'\')}}" ';

                   // var placeholder = '<div class="ct-inner-content-placeholder">Page/Post specific content</div>';
                    template = '<div is-nestable="true" ' + options + classes + '></div>';

                    break

                default: 
                    template = "<span>No Template found</span>"
                    //console.log(componentName);
            }
        }

        // shortcodes 
        else if ( type == "shortcode" ) {

            var tag = $scope.component.options[id]['model'].tag;

            template = '<'+tag+' ' + options + classes + '></'+tag+'>';
            
            var timeout = $timeout(function() {
                $scope.renderShortcode(id, componentName);
                
                // cancel timeout
                $timeout.cancel(timeout);
            }, 0, false);
        }

        // widgets
        else if ( type == "widget" ) {

            template = '<div ' + options + classes + '></div>';

            var timeout = $timeout(function() {
                $scope.renderWidget(id);
                
                // cancel timeout
                $timeout.cancel(timeout);
            }, 0, false);
        }

        if ( componentName != "ct_code_block" ) {
             
            var timeout = $timeout(function() {
                $scope.applyComponentJS(id, componentName, false);

                // cancel timeout
                $timeout.cancel(timeout);
            }, 0, false);
        }

        // store component HTML Tags in an object

        $scope.htmlTags[componentName] = jQuery(template).prop("tagName").toLowerCase();

        return template;
    }


    /**
     * Change element tag name
     * 
     * @since 0.1.7
     */

    $scope.changeTag = function (type, id, name) {

        if (undefined==id) {
            id = $scope.component.active.id;
        }
        if (undefined==name) {
            name = $scope.component.active.name;
        }

        var newComponent = $scope.getComponentTemplate(name, id, type),
            oldComponent = $scope.getComponentById(id);

        $scope.cleanReplace(oldComponent, newComponent);
    }


    /**
     * Get DOM element deepest child to insert
     * 
     * @return jqLite
     * @since 0.1.3
     */
    
    $scope.getInnerWrap = function( element ) {

        if ( !element.hasClass ) {
            return element;
        }

        if ( element.hasClass('ct-columns') || element.hasClass('ct-section') ) {

            var child = element.children();
            if ( child.hasClass('ct-inner-wrap') ) {
                return child;//.children();
            }
            else {
                return element;
            }
        }
        else {
            return element;
        }
    }


    /**
     * Wrap selected text with <span> component
     *
     * @since 0.1.8
     */
    
    $scope.wrapWithSpan = function() {
        
        var parentId    = $scope.component.active.id,
            parentName  = $scope.component.active.name,

            // get selection
            selection   = $scope.getUserSelection(),

            // create span
            node = document.createElement("span"),
            att  = document.createAttribute("id"),

            // get current component DOM node
            parent = $scope.getActiveComponent();
        
        att.value = "ct-placeholder-" + $scope.component.id;
        node.setAttributeNode(att);

        // update selection
        $scope.replaceUserSelection(node);

        var newComponent = {
            id : $scope.component.id, 
            name : "ct_span"
        }

        // set default options first
        $scope.applyComponentDefaultOptions(newComponent.id, "ct_span");

        // insert new component to Components Tree
        $scope.findComponentItem($scope.componentsTree.children, $scope.component.active.id, $scope.insertComponentToTree, newComponent);

        // update span options
        $scope.component.options[newComponent.id]["model"]["ct_content"] = selection;
        $scope.setOption(newComponent.id, "ct_span", "ct_content");

        // update parent options
        $scope.component.options[parentId]["model"]["ct_content"]       = parent.html();
        $scope.component.options[parentId]["original"]["ct_content"]    = parent.html();
        $scope.setOption(parentId, parentName, "ct_content");

        $scope.rebuildDOM(parentId);

        // activate component
        var timeout = $timeout(function() {
            $scope.activateComponent(newComponent.id, "ct_span");
            // cancel timeout
            $timeout.cancel(timeout);
        }, 0, false);
    }


    /**
     * Wrap selected text with <a> tag (not a component)
     *
     * @since 0.1.5
     * @author Ilya K.
     */
    
    $scope.wrapWithLink = function() {

        // get selection
        var sel = $scope.getUserSelection();

        // create link
        var node = document.createElement("a");
        node.appendChild(document.createTextNode(sel));

        // set URL
        var url = prompt("Define link URL", "http://");
        if (url != null) {
            var att = document.createAttribute("href");
            att.value = url;
            node.setAttributeNode(att);
        }
        
        // update selection
        $scope.replaceUserSelection(node);
    }


    /**
     * Get user selection
     *
     * @since 0.1.5
     * @author Ilya K.
     */
    
    $scope.getUserSelection = function() {

        var sel = "";

        if (typeof window.getSelection != "undefined") {
            var sel = window.getSelection();
            if (sel.rangeCount) {
                var container = document.createElement("div");
                for (var i = 0, len = sel.rangeCount; i < len; ++i) {
                    container.appendChild(sel.getRangeAt(i).cloneContents());
                }
                sel = container.innerHTML;
            }
        } else if (typeof document.selection != "undefined") {
            if (document.selection.type == "Text") {
                sel = document.selection.createRange().htmlText;
            }
        }

        return sel;
    }


    /**
     * Replace user selection 
     *
     * @since 0.1.5
     * @author Ilya K.
     */
    
    $scope.replaceUserSelection = function(node) {
        
        var range, html;
        if (window.getSelection && window.getSelection().getRangeAt) {
            range = window.getSelection().getRangeAt(0);
            range.deleteContents();
            range.insertNode(node);
        } else if (document.selection && document.selection.createRange) {
            range = document.selection.createRange();
            html = (node.nodeType == 3) ? node.data : node.outerHTML;
            range.pasteHTML(html);
        }
    }


    /**
     * Helper function to create a list of dom items to be removed from a hierarchy 
     *
     * @since 1.1.0
     * @author Gagan Goraya.
     */

    $scope.listToBeRemoved = function(toBeRemoved, componentsTree, id, collect) {

        if(typeof(collect) === 'undefined')
            collect = false;
    
        var keepGoing = true;

        angular.forEach(componentsTree, function(item, index) {
            
            if(keepGoing) {
               
                if(id && item.id === parseInt(id)) {
                    
                    toBeRemoved.splice(0,toBeRemoved.length);

                    collect = true;
                    keepGoing = false;

                }

                if(collect)
                    toBeRemoved.push(item.id);
                
                if ( item.children ) {
                    $scope.listToBeRemoved(toBeRemoved, item.children, id, collect);
                }
            }
        });
    }

    /**
     * Rebuild DOM node based on current Components Tree
     * 
     * @since 0.1
     * @author Ilya K.
     */
    
    $scope.rebuildDOM = function(id, reorder) {

        if ($scope.log) {
            console.log("rebuildDOM()", id);
        }
        
        $scope.functionStart("rebuildDOM");

        // build children
        if ( $scope.componentsTree.children ) {
            
            if(reorder) {
                
                var toBeRemoved = []
                //recurse and prepare list of items to be removed from the DOM
                $scope.listToBeRemoved(toBeRemoved, $scope.componentsTree.children, id);

                angular.forEach(toBeRemoved.reverse(), function(itemid) {
                    $scope.removeComponentFromDOM(itemid);
                });

            }

            $scope.buildComponentsFromTree($scope.componentsTree.children, id, reorder);
            
            // increment id
            $scope.component.id++;

            $scope.functionEnd("rebuildDOM");
        } 
        else {

            $scope.functionEnd("rebuildDOM");
            return false;
        }
    }


    /**
     * Cut component from DOM
     * 
     * @since 0.1.8
     * @author Ilya K.
     */

    $scope.removeComponentFromDOM = function(id) {

        var component = $scope.getComponentById(id);

        if ( component ) {

            component.scope().$destroy();
            component.remove();
            component = null;
        }
    }


    /**
     * Compile and insert new component
     *
     * @since 0.1.6
     * @author Ilya K.
     */

    $scope.cleanInsert = function(element, parentElement, index) {
        
        angular.element(document).injector().invoke(function($compile) {

            var newScope        = $scope.$new(),
                existingScope   = angular.element(element).scope();
            
            if (existingScope) {
                //existingScope.$destroy();
            }

            var compiledElement = $compile(element)(newScope);
            
            if ( parentElement ) {
                $scope.insertAtIndex(compiledElement, parentElement, index);
            } 
            else {
                angular.element(element).replaceWith(compiledElement);
            }
        });
    }


    /**
     * Compile and replace component 
     *
     * @since 0.1.7
     * @author Ilya K.
     */

    $scope.cleanReplace = function(element, replacement) {

        angular.element(document).injector().invoke(function($compile) {

            var newScope = $scope.$new(),
                compiledReplacement = $compile(replacement)(newScope);
            
            angular.element(element).replaceWith(compiledReplacement);
        });
    }


    /**
     * Insert child DOM element at a specific index in a parent element
     *
     * @since 0.1.7
     * @author Ilya K.
     */

    $scope.insertAtIndex = function(child, parent, index) {

        if ( index === 0 ) {
            parent.prepend(child);
        }
        else if ( index > 0 ) {
            jQuery(">*:nth-child("+index+")", parent).after(child);
        }
        else {
            parent.append(child);
        }
    }


    /**
     * Helper function to escape HTML special chars
     *
     * @since 0.1.7
     * @author Ilya K.
     */
    
    $scope.escapeHtml = function(text) {
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };

        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
    

    /**
     * Helper function to add slashes before quotes
     *
     * @since 0.1.7
     * @author Ilya K.
     */
    
    $scope.addSlashes = function(str) {

        return (str + '')
            .replace(/[\\"']/g, '\\$&')
            .replace(/\u0000/g, '\\0');
    }

    /**
     * Helper function to strip slashes from server response
     *
     * @since 0.4.0
     * @author Ilya K.
     */
    
    $scope.stripSlashes = function(str){
        return str.replace(/\\(.)/mg, "$1");
    }
    


    $scope.toggleExpandEditor = function(e) {
        var editor = jQuery(e.target).siblings('.CodeMirror');
        if(editor && editor.data('collapsed')) {

            editor.animate({'height': editor.data('collapsed')}, 500, function() {editor.css({'height': '', 'zIndex': ''})});
            editor.find('.CodeMirror-gutters').animate({'height': editor.data('collapsed')});
            editor.data('collapsed', false);
            jQuery(e.target).text(jQuery(e.target).attr('data-expand'));
        }
        else {
            editor.data('collapsed', editor.find('.CodeMirror-gutters').css('height'));

            var expandHeight = window.innerHeight-200-jQuery('#ct-toolbar-main').height();
            editor.css('zIndex', '9999999999');
            editor.animate({'height': expandHeight+'px'});
            editor.find('.CodeMirror-gutters').animate({'height': expandHeight+'px'});

            jQuery(e.target).text(jQuery(e.target).attr('data-collapse'));

            
        }
    }

    /**
     * Helper functions to check fucntion execution speed
     *
     * @since 0.2.?
     * @author Ilya K.
     */
    
    $scope.functionStart = function(name) {
        if ( $scope.log === true ) {
            console.time(name);
        }
    }

    $scope.functionEnd = function(name) {
        if ( $scope.log === true ) {
            console.timeEnd(name);
        }
    }

    
    /**
     * Prevent user from leaving a builder if link clicked or form submitted
     * 
     * @since 0.2.5
     */
    
    $scope.setupPageLeaveProtection = function() {

        // bind click and submit events
        jQuery("#ct-builder")
            .on("click", "a", function(e) {    
                e.preventDefault()
            })
            .on("submit", "form", function(e) {    
                e.preventDefault()
            });
    }


    /**
     * Show confirmation dialong on exit
     *
     * @since 0.3.2
     */
    
    $scope.confirmOnPageExit = function(e) {
        
        // If we haven't been passed the event get the window.event
        e = e || window.event;

        var message = 'There is unsaved changes.';

        // For IE6-8 and Firefox prior to version 4
        if (e) 
        {
            e.returnValue = message;
        }

        // For Chrome, Safari, IE8+ and Opera 12+
        return message;
    };


    /**
     * Attach event to show confirm dialog on exit when all saved
     *
     * @since 0.3.2
     */
    
    $scope.unsavedChanges = function() {

        if ($scope.log) {
            console.log("unsavedChanges()");
        }

        if (window.onbeforeunload===null) {
            window.onbeforeunload = $scope.confirmOnPageExit;
        }

        jQuery("#ct-save-button").addClass("ct-unsaved-changes");
    }


    /**
     * Remove event to hide confirm dialog on exit when all saved
     *
     * @since 0.3.2
     */
    
    $scope.allSaved = function() {
        window.onbeforeunload = null;
        jQuery("#ct-save-button").removeClass("ct-unsaved-changes");
    }


    /**
     * Helper to prevent Angular sort Object on ng-repeat
     * 
     * @since 0.3.2
     */
    
    $scope.notSorted = function(obj) {
        if (!obj) {
            return [];
        }
        return Object.keys(obj);
    }


    /**
     * Helper to prevent Angular sort Object on ng-repeat
     * 
     * @since 0.3.3
     */
    $scope.isObjectEmpty = function(obj) {
        if (obj) {
            for (var prop in obj) {
                if (obj.hasOwnProperty(prop)) {
                    return false;
                }
            }
        }
        return true;
    }


    /**
     * Helper to encode string with Unicode characters to base64
     * 
     * @since 0.4.0
     * @author Ilya K.
     */
    
    $scope.b64EncodeUnicode = function(str) {
        return btoa(encodeURIComponent(str).replace(/%([0-9A-F]{2})/g, function(match, p1) {
            return String.fromCharCode('0x' + p1);
        }));
    }


    /**
     * This function intercepts the paste event, removes the formatting off the clipboard data
     * and programatically inserts the plain text. 
     * 
     * As undo/redo history of the editable region gets destroyed on programatically inserting 
     * text into the element. This function contains provision for 1 undo (ctrl-z/cmd-z) to restore
     * the state of element to what it was before the the paste.
     * @author gagan goraya
     *
     * @since 0.3.4
     */

    var stripFormatting = function(e) {
        
        var me = this,
            oldContent = jQuery(me).html(),
            strippedPaste = jQuery('<div>').append(e.originalEvent.clipboardData.getData("Text").replace(/(?:\r\n|\r|\n)/g, '%%linebreak%%')).text(),//.replace(/(?:\r\n|\r|\n)/g, '<br />');
            sel, range;

        if (window.getSelection) {

            sel = window.getSelection();
            
            if (sel.rangeCount) {
                range = sel.getRangeAt(0);
                range.deleteContents();
                var nodes = strippedPaste.split('%%linebreak%%'), 
                    node;

                for (var key in nodes) {
                    if (nodes.hasOwnProperty(key)) {
                    
                        node = document.createTextNode(nodes[key]);
                        
                        range.insertNode(node);
                        range = range.cloneRange();
                        range.selectNodeContents(node);
                        range.collapse(false);
                        
                        sel.removeAllRanges();
                        sel.addRange(range);

                        if (key < nodes.length-1) {
                            node = document.createElement('br')
                                                        
                            range.insertNode(node);
                            range = range.cloneRange();
                            range.selectNode(node);
                            range.collapse(false);
                        
                            sel.removeAllRanges();
                            sel.addRange(range);
                        }
                    }
                }
            }

        } else if (document.selection && document.selection.createRange) {

            range = document.selection.createRange();
            range.text = strippedPaste.split('%%linebreak%%').join("");
        }

        // This lets the user undo the paste action.
        var undofunc = function(e) {
            
            if(e.keyCode === 90 && (e.ctrlKey || e.metaKey)) {
                e.preventDefault();
                jQuery(me).html(oldContent);
                jQuery(me).off('keydown', undofunc);
            }
        }
        jQuery(me).on('keydown', undofunc);
        e.preventDefault();
    }

    jQuery('body')
        .on('paste', '.ct_paragraph', stripFormatting)
        .on('paste', '.ct_headline', stripFormatting)
        .on('paste', '.ct_li', stripFormatting)
        .on('paste', '.ct_span', stripFormatting)
        .on('paste', '.ct_link_text', stripFormatting)
        .on('paste', '.ct_text_block', stripFormatting);

    
    /**
     * Check if user is developer
     */
    
    $scope.isDev = function() {

        var devsArray = [1,2,3,4,5];
        
        return devsArray.indexOf(parseInt($scope.authInfo["token_check"]["ID"],10)) > -1;
    }


    /**
     * Check API response and console.log errors if something wrong
     *
     * @author Ilya K.
     * @since 1.4
     */

    $scope.checkAPIstatus = function() {

        if ($scope.authInfo.token_check.status && 
            $scope.authInfo.token_check.status=="error") {

            // cURL error
            if ($scope.authInfo.token_check.message) { 
                console.log($scope.authInfo.token_check.message)
            }
            else
            // API error
            if ($scope.authInfo.token_check.error &&
                $scope.authInfo.token_check.error.errors ) { 
                console.log($scope.authInfo.token_check.error.errors[Object.keys($scope.authInfo.token_check.error.errors)[0]])
            }
            else {
                console.log($scope.authInfo.token_check)
            }
        }
    }

    
// End MainController
});


/**
 * Collect all controllers into one
 * 
 */

CTFrontendBuilder.controller('BuilderController', function($controller, $scope, $http, $timeout, $window) {

    var locals = {
        $scope: $scope,
        $http: $http,
        $timeout: $timeout
    };

    $controller('MainController',           locals);
    $controller('ComponentsTree',           locals);
    $controller('ComponentsStates',         locals);
    $controller('ControllerNavigation',     locals);
    $controller('ControllerColumns',        locals);
    $controller('ControllerAJAX',           locals);
    $controller('ControllerUI',             locals);
    $controller('ControllerClasses',        locals);
    $controller('ControllerOptions',        locals);
    $controller('ControllerFonts',          locals);
    $controller('ControllerCSS',            locals);
    $controller('ControllerTemplates',      locals);
    $controller('ControllerSVGIcons',       locals);
    $controller('ControllerDragnDrop',      locals);
    $controller('ControllerMediaQueries',   locals);
    $controller('ControllerAPI',            locals);
});