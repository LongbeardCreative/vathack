/**
 * All UI staff here
 * 
 */

CTFrontendBuilder.controller("ControllerUI", function($anchorScroll, $location, $scope, $timeout, $window) {

    $scope.toolbarElement   = jQuery("#ct-toolbar");
    $scope.builderElement   = jQuery("#ct-builder");
    $scope.sidePanelElement = jQuery("#ct-sidepanel");

    // variable to show/hide toolbar elements
    $scope.showClasses          = true; 
    
    $scope.showComponentBar     = false;
    $scope.showPageSettings     = false;
    $scope.showDOMTreeNavigator = false;
    $scope.dialogWindow         = false;
    $scope.viewportRullerShown  = false;

    $scope.currentBorder        = "all";

    $scope.actionTabs = {
        "componentBrowser"  : false,
        "advancedSettings"  : false,
        "linkSettings"      : false,
        "contentEditing"    : false,
        "templateTags"      : false,
        "settings"          : false,
        "styleSheet"        : false,
        "codeEditor"        : false
    };

    $scope.toggledNodes     = [];
    $scope.highlight        = [];
    
    $scope.tabs                         = [];
    $scope.tabs.components              = [];
    $scope.tabs.components.fundamentals = true;

    $scope.tabs.advanced = [];

    // Background tab
    $scope.tabs.advanced.background = {
        color : true,
        image : false,
        gradient : false,
        size : false
    };

    // Position & Size tab
    $scope.tabs.advanced.positionSize               = [];
    $scope.tabs.advanced.positionSize.marginPadding = true;
    $scope.tabs.advanced.positionSize.position      = false;
    $scope.tabs.advanced.positionSize.size          = false;

    $scope.tabs.settings                = [];
    $scope.tabs.settings.page           = true;

    $scope.tabs.sidePanel               = [];
    $scope.tabs.sidePanel.DOMTree       = true;

    $scope.tabs.codeEditor                = [];
    $scope.tabs.codeEditor["code-php"]    = true;
    
    $scope.isSelectableEnabled  = false;
    $scope.isDOMNodesSelected   = false;

    // start with no overlays
    $scope.overlaysCount = 0

    $scope.dialogForms = [];

    $scope.componentSelector = {};


    /**
     * Check if component active by component id
     *
     * @since 0.1.6
     * @return {bool}
     */

    $scope.isActiveId = function(id) {

        return ( id == $scope.component.active.id ) ? true : false;
    }


    /**
     * Check if component active by component name
     * 
     * @since 0.1
     * @return {bool}
     */
    
    $scope.isActiveName = function(name) {

        return (name == $scope.component.active.name) ? true : false;
    }
    

    /**
     * Set advanced settings tab to show
     * 
     * @since 0.1.7
     */
    
    $scope.switchTab = function(tabGroup, tabName) {
        
        $scope.tabs[tabGroup] = [];

        if (tabGroup=="components") {
            $scope.closeAllFolders();
        }
        
        switch (tabName) {
            // all tabs with children
            case "background" : 
                $scope.tabs[tabGroup][tabName] = {color:true};
                break;

            case "position" : 
                $scope.tabs[tabGroup][tabName] = {margin_padding:true};
                break;

            case "background" : 
                $scope.tabs[tabGroup][tabName] = {color:true};
                break;

            case "borders" : 
                $scope.tabs[tabGroup][tabName] = {border:true};
                break;

            case "cssjs" : 
                $scope.tabs[tabGroup][tabName] = {css:true};
                break;

            case "code" : 
                $scope.tabs[tabGroup][tabName] = {'code-php':true};
                break;

            // other regular tabs
            default :
                $scope.tabs[tabGroup][tabName] = true;
        }

        $scope.disableSelectable();
    }


    /**
     * Set advanced settings tab to show
     * 
     * @since 0.3.0
     */
    
    $scope.switchChildTab = function(tabGroup, tabName, childTabName) {
        
        $scope.tabs[tabGroup]                           = []; 
        $scope.tabs[tabGroup][tabName]                  = [];
        $scope.tabs[tabGroup][tabName][childTabName]    = true;

        $scope.disableSelectable();
    }


    /**
     * Check if opened tab is not available for current component and switch to default
     * 
     * @since 0.2.4
     */

    $scope.checkTabs = function() {

        if ($scope.log) {
            console.log("checkTabs()")
        }

        if ( $scope.isActiveName("root") ) {
            $scope.closeAllTabs();
            return;
        }

        // check code block tabs
        if ( $scope.isActiveName("ct_code_block") ) {
            if (    $scope.tabs.advanced['cssjs'] && (
                    $scope.tabs.advanced['cssjs']['js'] || 
                    $scope.tabs.advanced['cssjs']['css'] ) 
                ) {
                $scope.switchActionTab("codeEditor");               
            }
        }
        // check widget
        /*else if ( $scope.isActiveName("ct_widget") ) {
            $scope.closeAllTabs(["componentBrowser"]);
        }
        // check shortcode
        else if ( $scope.isActiveName("ct_shortcode") ) {
            $scope.closeAllTabs(["componentBrowser"]);
        }*/
        // check others
        else if ( $scope.tabs.advanced['code'] && ( $scope.tabs.advanced['code']['code-php'] || 
                    $scope.tabs.advanced['code']['code-js'] ||
                    $scope.tabs.advanced['code']['code-css'] ) && $scope.component.active.name != "ct_code_block" ) {
            $scope.switchChildTab("advanced", "background", "color");
        }

        // check custom JS tab
        /*if ($scope.tabs.advanced['cssjs'] && $scope.tabs.advanced['cssjs']['js'] && ($scope.isEditing('media') || $scope.isEditing('class') || $scope.isEditing('state'))) {
            $scope.switchChildTab('advanced', 'cssjs', 'css');
        }*/
    }


    /**
     * Close all tabs, except the tabs specified in keepTabs array
     * 
     * @since 0.1.7
     */
    
    $scope.closeAllTabs = function(keepTabs) {

        if ($scope.log) {
            console.log("closeAllTabs()", keepTabs);
        }
        
        if (keepTabs==undefined){
            keepTabs = [];
        }
        
        angular.forEach($scope.actionTabs, function(value, tab) {
            if (keepTabs.indexOf(tab) == -1) {
                $scope.actionTabs[tab] = false;
            }
        });

        $scope.adjustViewportContainer();
    }


    /**
     * Switch to code editor if Code Block is active
     * 
     * @since 1.3
     * @author Ilya K.
     */

    $scope.possibleSwitchToCodeEditor = function(tabGroup, tabName) {

        if ( $scope.isActiveName("ct_code_block") ) {
            $scope.switchActionTab("codeEditor");
            $scope.switchTab("codeEditor","code-css");
        }
        else {
            $scope.switchTab(tabGroup, tabName);   
        }
    }


    /**
     * Check is to show tab
     * 
     * @since 0.1.7
     * @return {bool}
     */
    
    $scope.isShowTab = function(tabGroup, tabName) {  

        if ( $scope.tabs[tabGroup] ) {
            return ( $scope.tabs[tabGroup][tabName] ) ? true : false;
        }
        else {
            return false;
        }
    }


    /**
     * Check is to show child tab
     * 
     * @since 0.3.0
     * @return {bool}
     */
    
    $scope.isShowChildTab = function(tabGroup, tabName, childTabName) {  

        if ( $scope.tabs[tabGroup] ) {
            return ( $scope.tabs[tabGroup][tabName] && $scope.tabs[tabGroup][tabName][childTabName] ) ? true : false;
        }
        else {
            return false;
        }
    }


    /**
     * Toggle Side Panel
     *
     * @since 0.1.5
     */

    $scope.toggleSidePanel = function() {

        $scope.showSidePanel = !$scope.showSidePanel;

        $scope.adjustViewportContainer();



    /*    if(jQuery('body').hasClass('ct_inner')) {
            setTimeout(function() {
                $scope.disableOuterDivs();  
            }, 500);
        }

    */

        if (!$scope.showSidePanel) {
            $scope.disableSelectable();
            return false;
        }

        //$scope.sidePanelElement.css("top", $scope.toolbarElement.height());

        /*var timeout = $timeout(function() {
            $scope.updateDOMTreeNavigator();
            // cancel timeout
            $timeout.cancel(timeout);
        }, 0, false);*/
    }


    /**
     * Show editor panel for contenteditable elements
     *
     * @since 0.1.5
     */

    $scope.enableContentEdit = function() {

        if ( $scope.actionTabs["contentEditing"] == true ) {
            return false;
        }

        // switch edit to id
        $scope.switchEditToId();

        var activeComponent = $scope.getActiveComponent();

        if ( activeComponent[0].attributes['contenteditable'] ) {

            // FireFox fix for the invisible cursor issue 
            if ( $scope.isActiveName("ct_link_text") ) {
                jQuery("<input style='position:fixed;top:40%;left:40%' type='text'>").appendTo("body").focus().remove();
            }

            activeComponent[0].setAttribute("contenteditable", "true");
            activeComponent[0].setAttribute("spellcheck", "true");
            activeComponent[0].setAttribute("draggable", "false");
            activeComponent.focus();
            
            $scope.setEndOfContenteditable(activeComponent[0]);

            $scope.actionTabs["contentEditing"] = true;
        }
    }


    /**
     * Hide editor panel for contenteditable elements
     *
     * @since 0.1.5
     */

    $scope.disableContentEdit = function() {

        if ( !$scope.actionTabs["contentEditing"] )
            return false;

        if ($scope.log) {
            console.log('disableContentEdit()');
        }

        // clear selection
        if (window.getSelection) {
            if (window.getSelection().empty) {  // Chrome
                window.getSelection().empty();
            } else if (window.getSelection().removeAllRanges) {  // Firefox
                window.getSelection().removeAllRanges();
                }
        } else if (document.selection) {  // IE?
            document.selection.empty();
        }

        var activeComponent = $scope.getActiveComponent();

        if ( activeComponent[0].attributes['contenteditable'] ) {

            var content = activeComponent.html();
            activeComponent.html("");

           /* var el = activeComponent[0];
            while ((el = el.parentElement) && !el.classList.contains('ct_link'));
            if(el)
                el.setAttribute("href", '');*/

            activeComponent[0].setAttribute("contenteditable", "false");
            activeComponent[0].removeAttribute("spellcheck");
            //activeComponent[0].setAttribute("draggable", "true");
            
            activeComponent.html(content);
            
            if ($scope.component.active.name != 'ct_span') {
                $scope.rebuildDOM($scope.component.active.id);
            }
        }
        
        /*if($scope.component.active.name != 'ct_text_block')
            $scope.rebuildDOM($scope.component.active.id);*/

        $scope.actionTabs["contentEditing"] = false;
    }


    /**
     * Set cursor to the end of contenteditbale. Taken from http://stackoverflow.com/a/3866442/2198798
     *
     * @since 1.1.2
     */

    $scope.setEndOfContenteditable = function(contentEditableElement) {
        var range,selection;
        if(document.createRange)//Firefox, Chrome, Opera, Safari, IE 9+
        {
            range = document.createRange();//Create a range (a range is a like the selection but invisible)
            range.selectNodeContents(contentEditableElement);//Select the entire contents of the element with the range
            range.collapse(false);//collapse the range to the end point. false means collapse to end rather than the start
            selection = window.getSelection();//get the selection object (allows you to change selection)
            selection.removeAllRanges();//remove any selections already made
            selection.addRange(range);//make the range you have just created the visible selection
        }
        else if(document.selection)//IE 8 and lower
        { 
            range = document.body.createTextRange();//Create a range (a range is a like the selection but invisible)
            range.moveToElementText(contentEditableElement);//Select the entire contents of the element with the range
            range.collapse(false);//collapse the range to the end point. false means collapse to end rather than the start
            range.select();//Select the range (make it the visible selection
        }
    }


    /**
     * Wrap active component with link (if not already a link) and show settings
     *
     * @since 0.1.6
     * @author Ilya K.
     */

    $scope.processLink = function() {

        $scope.cancelDeleteUndo();

        if ($scope.log){
            console.log("processLink()");
        }

        var linkComponentId = $scope.getLinkId();

        if (!linkComponentId) {

            // convert to Text Link
            if ($scope.isActiveName("ct_text_block")) {
                $scope.findComponentItem($scope.componentsTree.children, $scope.component.active.id, $scope.updateTreeComponentTag, "ct_link_text");
            }
            else
            // convert to Link Wrapper
            if ($scope.isActiveName("ct_div_block")) {
                $scope.findComponentItem($scope.componentsTree.children, $scope.component.active.id, $scope.updateTreeComponentTag, "ct_link");
                
                // convert all links inside div block
                $scope.findComponentItem($scope.componentsTree.children, $scope.component.active.id, $scope.updateTagsByName, 
                    {from:"ct_link_text",to:"ct_text_block"});
                $scope.findComponentItem($scope.componentsTree.children, $scope.component.active.id, $scope.updateTagsByName, 
                    {from:"ct_link",to:"ct_div_block"});
            }
            else
            if ($scope.component.active.name === 'ct_span') {

                $scope.findComponentItem($scope.componentsTree.children, $scope.component.active.id, $scope.updateTreeComponentTag, "ct_link_text");
                                
                var placeholder = document.getElementById($scope.component.options[$scope.component.active.id]['selector']);

                var parentContent = $scope.component.options[$scope.component.active.parent.id]["id"]['ct_content'];

                $scope.cleanReplace(placeholder, "<span id=\"ct-placeholder-"+$scope.component.active.id+"\"></span>");

            }
            // wrap with Link Wrapper
            else {
                var newComponentId = $scope.wrapComponentWith("ct_link");

                $scope.activateComponent(newComponentId, "ct_link");
            }
        }
        else {
            $scope.activateComponent(linkComponentId, "ct_link");
        }

        //$scope.switchActionTab('linkSettings');

        var button = jQuery('.ct-tab.ct-link-button', '#ct-toolbar');
        var timeout = $timeout(function() {
            var linkelem = jQuery('.ct-active');
            jQuery('<textarea>')
                .attr('id', 'ct-link-dialog-txt')
                .css('display', 'none')
                .attr('data-linkProperty', button.attr('data-linkProperty'))
                .attr('data-linkTarget', button.attr('data-linkTarget'))
                .insertAfter(linkelem);

            wpLink.open('ct-link-dialog-txt'); //open the link popup*/
            
            jQuery( '#wp-link-url' ).val($scope.component.options[$scope.component.active.id]['model']['url']);
            jQuery( '#wp-link-target' ).prop( 'checked', '_blank' === $scope.component.options[$scope.component.active.id]['model']['target'] );
            jQuery('#wp-link-wrap').removeClass('has-text-field');
            $timeout.cancel(timeout);
        }, 0, false);
    }

    
    /**
     * Convert link components from link Div or Text Block
     * 
     * @since 0.3.3
     * @author Ilya K.
     */

    $scope.removeLink = function() {

        // handle Text Link
        if ($scope.isActiveName("ct_link_text")) {

            var componentParent = $scope.getComponentById($scope.component.active.parent.id);

            if ( !componentParent[0] || componentParent[0].attributes['contenteditable'] ) {
                // convert a ct_link_text to ct_span
                $scope.findComponentItem($scope.componentsTree.children, $scope.component.active.id, $scope.updateTreeComponentTag, "ct_span");
                
                var placeholder = document.getElementById($scope.component.options[$scope.component.active.id]['selector']);

                var parentContent = $scope.component.options[$scope.component.active.parent.id]["id"]['ct_content'];

                $scope.cleanReplace(placeholder, "<span id=\"ct-placeholder-"+$scope.component.active.id+"\"></span>");
            }
            else {
                $scope.findComponentItem($scope.componentsTree.children, $scope.component.active.id, $scope.updateTreeComponentTag, "ct_text_block");
            }
        }

        // handle Link Wrapper
        if ($scope.isActiveName("ct_link")) {
            $scope.findComponentItem($scope.componentsTree.children, $scope.component.active.id, $scope.updateTreeComponentTag, "ct_div_block");
        }

        //$scope.switchActionTab('linkSettings');
    }



    /**
     * Show overlay to prevent user action when save the page, etc
     * 
     * @since 0.1.3
     */

    $scope.showLoadingOverlay = function(trigger) {

        var pageOverlay = document.getElementById("ct-page-overlay");
            pageOverlay = angular.element(pageOverlay);

        $scope.overlaysCount++;

        //console.log("showLoadingOverlay()", trigger, $scope.overlaysCount);
        pageOverlay.show();
    }


    /**
     * Remove overlay
     * 
     * @since 0.1.3
     */

    $scope.hideLoadingOverlay = function(trigger) {

        var pageOverlay = document.getElementById("ct-page-overlay");
            pageOverlay = angular.element(pageOverlay);

        $scope.overlaysCount--;

        //console.log("hideLoadingOverlay()", trigger, $scope.overlaysCount);
        // hide spinner only when all overlays closed
        if ($scope.overlaysCount === 0) {
            pageOverlay.hide();
        }
    }

    
    /**
     * Switch action tabs
     * 
     * @since 0.1.7
     */

    $scope.switchActionTab = function(action) {

        if ($scope.log) {
            console.log("switchActionTab()", action);
        }

        // Do not allow to edit the settings while editing inner_content
        if( action === 'settings' && jQuery('body').hasClass('ct_inner')) {
            alert('To edit the settings for this page, load the containing template in the builder.');
            return;
        }

        // on open Add+ section
        if ( action == "componentBrowser" && !$scope.isActiveActionTab("componentBrowser") ) {
            $scope.advancedSettingsActive = $scope.isActiveActionTab("advancedSettings");
        }

        // on close Add+ section
        if ( action == "componentBrowser" && $scope.isActiveActionTab("componentBrowser") ) {
            // open More... section if it was open before
            if ($scope.advancedSettingsActive) {
                $scope.actionTabs['advancedSettings'] = true;
                $scope.advancedSettingsActive = false;
            }
        }
        
        // Check Code Block tabs
        if ( $scope.tabs.advanced['cssjs'] && (
             $scope.tabs.advanced['cssjs']['js'] ||
             $scope.tabs.advanced['cssjs']['css'] ) && 
             $scope.component.active.name == "ct_code_block" ) {
            
            $scope.switchChildTab("advanced", "background", "color");
        }

        // check content editing
        if ( action == "contentEditing" ) {

            if ( !$scope.actionTabs["contentEditing"]) {
                $scope.enableContentEdit();
            } 
            else {
                $scope.disableContentEdit();
            }
        }
        else if ( action === 'styleSheet') {
            if($scope.stylesheetToEdit && $scope.stylesheetToEdit !== $scope.actionTabs[action]) {
                $scope.actionTabs = {};
                $scope.actionTabs[action] = $scope.stylesheetToEdit;
            }
            else {
                $scope.actionTabs[action] = false;
            }
        }
        else {
            
            // disable content editing
            $scope.disableContentEdit();

            // set tab flag
            if ( $scope.actionTabs[action] ) {
                $scope.actionTabs[action] = false;
            } 
            else {
                $scope.actionTabs = {};
                $scope.actionTabs[action] = true;
            }
        }

        $scope.adjustViewportContainer();
        $scope.disableSelectable();
    }


    /**
     * Activate action tabs
     * 
     * @since 0.1.7
     */

    $scope.activateActionTab = function(action) {

        // check content editing
        if ( action == "contentEditing" ) {
                
            // close all tabs before enable
            $scope.actionTabs = {};
            $scope.enableContentEdit();
        }
        else {
            
            // disable content editing
            $scope.disableContentEdit();

            $scope.actionTabs = {};
            $scope.actionTabs[action] = true;
            
            var timeout = $timeout(function() {
                
                // adjust side panel
                $scope.sidePanelElement.stop(true).animate({top:$scope.toolbarElement.height()},150);

                $timeout.cancel(timeout);
            }, 0, false);

        }
    }


    /**
     * Check if action tab is active
     * 
     * @since 0.1.7
     */

    $scope.isActiveActionTab = function(action) {

        return ( $scope.actionTabs[action] ) ? true : false;
    }
    

    /**
     * Uncheck radio button
     * 
     * @since 0.2.3
     */

    $scope.radioButtonClick = function(componentName, paramName, paramValue) {
        
        var modelValue      = $scope.getOption(paramName),
            defaultValue    = $scope.defaultOptions[componentName][paramName];

        if ($scope.isEditing("custom-selector")) {
            var idValue = $scope.component.options[$scope.component.active.id]["model"][paramName];
        }
        else {
            var idValue = $scope.component.options[$scope.component.active.id]["id"][paramName];   
        }

        //console.log(modelValue, defaultValue, paramValue, idValue);
        
        if ($scope.isEditing("id") && !$scope.isEditing("media") && !$scope.isEditing("state")) {
            // set
            if ( modelValue == paramValue && !idValue ) {
                $scope.setOptionModel(paramName, paramValue);
            }
        }
        else {
            idValue = true;
        }

        // unset
        if ( modelValue == paramValue && idValue ) {
            
            $scope.setOptionModel(paramName, "");
        }
    }


    /**
     * Show pop-up dialog with options
     * 
     * @since 0.2.3
     */
    
    $scope.showDialogWindow = function() {
        
        $scope.dialogWindow = true;
    }


    /**
     * Hide pop-up dialog with options
     * 
     * @since 0.2.3
     */
    
    $scope.hideDialogWindow = function() {
        
        $scope.dialogWindow = false;

        // hide forms
        $scope.dialogForms = [];
        
        jQuery(document).off("keydown", $scope.switchComponent);
    }


    /**
     * Enable/disable selectable for DOM Tree
     * 
     * @since 0.2.4
     */

    $scope.switchSelectable = function() {

        if ( $scope.isSelectableEnabled ) {
            $scope.disableSelectable();
        }
        else {
            $scope.enableSelectable();          
        }
    }


    /**
     * Enable selectable for DOM Tree
     * 
     * @since 0.2.4
     */

    $scope.enableSelectable = function() {

        if ( $scope.isSelectableEnabled ) {
            return;
        }

        if ($scope.log) {
            console.log("enableSelectable()");
        }

        // fake component
        $scope.activateComponent(-2); // "-1" is for custom selectors

        $scope.isSelectableEnabled = true;

        // init nuSelecatble plugin
        $scope.selectable = angular.element("#ct-dom-tree").nuSelectable({
            items: '.ct-dom-tree-name',
            selectionClass: 'ct-selection-box',
            selectedClass: 'ct-selected-dom-node',
            autoRefresh: 'true',
            onMove: function(selected) {
                if (selected.length > 0 ) {
                    $scope.isDOMNodesSelected = true;
                }
                else {
                    $scope.isDOMNodesSelected = false;
                };
                $scope.$apply();
            },
            onMouseDown: function() {
                $scope.isDOMNodesSelected = false;
                $scope.$apply();
            }
        });
    }

    /**
     * Disable selectable for DOM Tree
     * 
     * @since 0.2.4
     * @deprecated
     */

    $scope.disableSelectable = function() {

        return false;

        if ( !$scope.isSelectableEnabled ) {
            return;
        }

        $scope.isSelectableEnabled = false;

        // remove data and events
        if ( $scope.selectable ) {
            $scope.selectable.removeData();
            $scope.selectable.unbind('mousedown mouseup');
        }

        // clear selection
        $scope.selectable.find('.ct-selected-dom-node').removeClass('ct-selected-dom-node');

        // activate root
        $scope.activateComponent(0, 'root');
    }


    /**
     * Control toolbar overlap of builder content
     *
     * @since 0.2.?
     */

    angular.element($window).bind("scroll", function() {

        var mainToolbarHeight   = jQuery("#ct-toolbar-main").height(),
            toolbarHeight       = $scope.toolbarElement.height(),
            builderPadding      = $scope.builderElement.css("padding-top").replace("px", ""),
            windowScroll        = jQuery($window).scrollTop(),
            scrollTop           = windowScroll + toolbarHeight - mainToolbarHeight;

        if (this.pageYOffset > 0) {
            
            if ( ! $scope.toolbarElement.hasClass("ct-toolbar-fixed") ) {
                // update toolbar options
                $scope.builderElement.css("padding-top", toolbarHeight );
                $scope.toolbarElement.addClass("ct-toolbar-fixed");
            } 
            else {
                // prevent white space on the top to be scrolled
                if ( builderPadding > toolbarHeight + scrollTop ) {
                    //console.log("fixed");
                    $scope.builderElement.css("padding-top", toolbarHeight + scrollTop);
                }
            }
        } 
        else {
            // stop any animations to prevent jump
            $scope.builderElement.stop(true);
            // update toolbar options
            $scope.toolbarElement.removeClass("ct-toolbar-fixed");
            $scope.builderElement.css("padding-top", 0);
        }
    });
    

    /**
     * Check if componenet is in viewport 
     * 
     * @since 0.3.0
     */

    $scope.isElementInViewport = function(el) {

        //special bonus for those using jQuery
        if (typeof jQuery === "function" && el instanceof jQuery) {
            el = el[0];
        }

        var rect = el.getBoundingClientRect();

        if ( rect.top >= (window.innerHeight || document.documentElement.clientHeight) ) {
            return "below";
        }

        if ( rect.bottom <= 0 ) {
            return "above";
        }

        return "visible";

        //rect.top >= 0 &&
        //rect.left >= 0 &&
        //rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) //&& /*or $(window).height() */
        //rect.right <= (window.innerWidth || document.documentElement.clientWidth) /*or $(window).width() */
    }

    
    /**
     * Smooth scroll window to component by selector
     * 
     * @since 0.3.0
     */
    
    $scope.scrollToComponent = function(selector) {

        if ($scope.log) {
            console.log("scrollToComponent() #"+ selector);
        }
        
        var target = jQuery('#'+selector);
        
        if ( $scope.isElementInViewport(target) == "above" ) {
        
            jQuery('html,body').stop().animate({
                scrollTop: target.offset().top - 100
            }, 500);
        }

        if ( $scope.isElementInViewport(target) == "below" ) {
        
            jQuery('html,body').stop().animate({
                scrollTop: target.offset().top - window.innerHeight + target.outerHeight() + 100
            }, 500);
        }
    };


    /**
     * Programmatically trigger CodeMirror editor blur event
     * to make code apply when clicking in DOM Tree
     *
     * @since 0.4.0
     * @author Ilya K.
     */
    
    $scope.triggerCodeMirrorBlur = function(id) {
        
        if ( !$scope.bubble ) {
            var editor = jQuery('.CodeMirror', '#ct-toolbar')[0];
            
            if (editor) {
                
                /**
                 * Taken from codemirror.js onBlur() function
                 */
                
                var cm = editor.CodeMirror;
                if (cm.state.focused) {
                    var handlers = editor.CodeMirror._handlers && editor.CodeMirror._handlers["blur"];
                    for (var i = 0; i < handlers.length; ++i) handlers[i].apply(null);
                    cm.state.focused = false;
                    jQuery(cm.display.wrapper).removeClass("CodeMirror-focused");
                }
                clearInterval(cm.display.blinker);
                setTimeout(function() {if (!cm.state.focused) cm.display.shift = false;}, 150);
            }
        }

        // prevent propagation
        $scope.bubble = true;
        if (id==0) {
            $scope.bubble = false;            
        }
    };


    /**
     * Add CodeMirror events
     *
     * @since 0.4.0
     * @author Ilya K.
     */
    
    $scope.codemirrorLoaded = function(_editor){
        
        _editor.on("change", function(){

            var timeout = $timeout(function() {
                
                //console.log("codemirrorChangeTimeout()", $scope.component.active.id, $scope.latesetCodeEditorComponentId)
                $scope.latesetCodeEditorComponentId = $scope.component.active.id;

                $timeout.cancel(timeout);
            }, 0, false);
        })
        
        // Update Code Block on Codemirror editor focus out
        _editor.on("blur", function(){

            //console.log("codemirrorBlur()", $scope.latesetCodeEditorComponentId)
            
            switch (_editor.options.type) {

                // Code Block
                
                case "css":
                    //$scope.applyCodeBlockCSS(); // Live edit enabled at the moment
                break;

                case "js":
                    $scope.applyCodeBlockJS($scope.latesetCodeEditorComponentId);
                break;

                case "php":
                    $scope.applyCodeBlockPHP($scope.latesetCodeEditorComponentId);
                break;

                // Component

                case "custom-css":
                    //$scope.applyComponentCSS(); // Live edit enabled at the moment
                break;

                case "custom-js":
                    $scope.applyComponentJS($scope.latesetCodeEditorComponentId);
                break;

                // Stylesheet

                case "stylesheet":
                    //$scope.applyStyleSheet($scope.stylesheetToEdit); // Live edit enabled at the moment
                break;
            }
        });

        _editor.on("focus", function(){

            if (_editor.options.type == "custom-js" ||
                _editor.options.type == "js" ||
                _editor.options.type == "css" ||
                _editor.options.type == "php" ) {
                $scope.setEditingStateToDefault();
            }
        });
    };


    /**
     * All UI/jQuery stuff here
     * 
     * @since 0.3
     */
    
    $scope.setupUI = function() {

        /**
         * Highlight components on hover
         */
        
        // DOM
        $scope.builderElement
        .on("mouseover", ".ct-component", function(e){
            e.stopPropagation();
            jQuery('.ct-highlight').removeClass('ct-highlight');

            // in case we are editing the ct_inner content, then no need to hilight the outer template elements
            if(jQuery('body').hasClass('ct_inner') 
                && (jQuery(this).hasClass('ct-inner-content') || jQuery(this).closest('.ct-component.ct-inner-content').length < 1 )) {
                return;
            }

            jQuery(this).addClass('ct-highlight');
        })
        .on("mouseout", ".ct-component", function(e){
            e.stopPropagation();
            jQuery(this).removeClass('ct-highlight');
        })

        // Up level button
        $scope.toolbarElement
        .on("mouseover", ".ct-up-level-button", function(e){
            var componentId = jQuery(this).attr("ng-attr-up-level-id");
            jQuery('.ct-component[ng-attr-component-id="'+componentId+'"]').addClass('ct-highlight');
        })
        .on("mouseout", ".ct-up-level-button", function(e){
            var componentId = jQuery(this).attr("ng-attr-up-level-id");
            jQuery('.ct-component[ng-attr-component-id="'+componentId+'"]').removeClass('ct-highlight');
        })

        // DOM Tree
        .on("mouseover", ".ct-dom-tree-node-anchor", function(e){
            var componentId = jQuery(this).attr("ng-attr-node-id");
            jQuery('.ct-component[ng-attr-component-id="'+componentId+'"]').addClass('ct-highlight');
        })
        .on("mouseout", ".ct-dom-tree-node-anchor", function(e){
            var componentId = jQuery(this).attr("ng-attr-node-id");
            jQuery('.ct-component[ng-attr-component-id="'+componentId+'"]').removeClass('ct-highlight');
        })

        
        /**
         * Media upload
         */
        
        var media_uploader = null;
        /** 
         * In order to make this functionality available for foreground images as well, 
         * this function relies on data- attributes provided in the .ct-media-button html element
         * this attributes can be as follows 
         * data-mediaTitle for the title of the media dialog
         * data-mediaButton for the text of the 'insert' button on the media dialog
         * data-mediaProperty for specifying the model's param that will be updated with the url
         * data-heightProperty for updating the height 
         * data-widthProperty for updating the width
         *
         */
        $scope.toolbarElement.on('click', '.ct-media-button', function(e) {

            media_uploader = wp.media({
                title : jQuery(e.target).attr('data-mediaTitle') || 'Set Image',
                button : {
                    text : jQuery(e.target).attr('data-mediaButton') || 'Set Image',
                },
                library : { type : 'image' },
                multiple : false,
            });

            media_uploader.on("select", function(){
                var json = media_uploader.state().get("selection").first().toJSON();
                //console.log(json);
                // update scope and model
                $scope.setOptionModel(jQuery(e.target).attr('data-mediaProperty'), json.url);

                if(jQuery(e.target).attr('data-heightProperty'))
                    $scope.setOptionModel(jQuery(e.target).attr('data-heightProperty'), json.height);
                if(jQuery(e.target).attr('data-widthProperty'))
                    $scope.setOptionModel(jQuery(e.target).attr('data-widthProperty'), json.width);
                
                // set image alt attr
                $scope.setOptionModel("alt", json.alt);

                $scope.$apply();
                //console.log(json);
            });

            media_uploader.open();
        });

        if(!window.ajaxurl)
                window.ajaxurl = CtBuilderAjax.ajaxUrl;


        jQuery('body')
            .on('click', '#wp-link-submit', function(e) {
                
                var attrs = wpLink.getAttrs();
                
                $scope.setOptionModel(jQuery('#ct-link-dialog-txt').attr('data-linkProperty'), attrs.href);
                $scope.setOptionModel(jQuery('#ct-link-dialog-txt').attr('data-linkTarget'), attrs.target);

                if( attrs.href.trim() === '') {
                    $scope.removeLink();
                }

                jQuery('body #ct-link-dialog-txt').remove();
                wpLink.close();

            })
            .on('click', '#wp-link-cancel, #wp-link-close, #wp-link-backdrop', function(e) {
                jQuery('body #ct-link-dialog-txt').remove();
                wpLink.close();
            });

        /**
         * Builder handle move
         */
        var dragging = false;

        // handle move start
        jQuery('#ct-viewport-handle').mousedown(function(e){    
            e.preventDefault();
       
            dragging = true;

            var viewport = jQuery('#ct-artificial-viewport');
            var ghostbar = jQuery('<div>',{id: 'ct-ghost-viewport-handle'}).appendTo('#ct-viewport-ruller-wrap');

            // init ghost position
            var position = e.pageX-viewport.offset().left-3;
                ghostbar.css("left", position);
            
            // adjust ghost position on move
            jQuery(document).mousemove(function(e){
                position = e.pageX-viewport.offset().left-3;
                ghostbar.css("left", position);
            });
           
        });

        // handle move end
        jQuery(document).mouseup(function(e){
           if (dragging) {
               
                var viewport    = jQuery('#ct-artificial-viewport'),
                    width       = e.pageX-viewport.offset().left;

                $scope.setMediaByWidth(width);

                jQuery('#ct-ghost-viewport-handle').remove();
                jQuery(document).unbind('mousemove');
                dragging = false;

                $scope.$apply();
            }
        });


        $scope.setMediaByWidth = function(width) {

            if (undefined==width) {
                var viewport = jQuery('#ct-viewport-container');
                width = viewport[0].scrollWidth;
            }
            
            var mediaName = $scope.getMediaNameBySize(width);

            if (mediaName) {
                $scope.setCurrentMedia(mediaName, false);
            }

            // adjust viewport
            $scope.adjustViewport(width + "px");
            $scope.adjustViewportRuller();
        }

        $scope.showViewportRuller = function() {
            jQuery('#ct-viewport-ruller-wrap').css("display", "block");
            $scope.viewportRullerShown = true;
        }

        $scope.hideViewportRuller = function() {
            jQuery('#ct-viewport-ruller-wrap').css("display", "");
            $scope.viewportRullerShown = false;
        }

        $scope.adjustViewportRuller = function() {

            jQuery('#ct-viewport-ruller-wrap').css("width", 0);

            var container   = jQuery('#ct-viewport-container'),
                viewport    = jQuery('#ct-artificial-viewport'),
                offset      = 6;//(viewport.offset().left > 0) ? viewport.offset().left : 0;

            jQuery('#ct-viewport-ruller-wrap').css({
                    left : offset,
                    //width : container.width() - offset
                    width : ($scope.viewportRullerWidth > container.width()) ? $scope.viewportRullerWidth : container.width() - offset - 1
                });

            //console.log("adjustViewportRuller()", offset, container.width() - offset);
            
            jQuery('#ct-viewport-handle').css("left", viewport.width()-3);

            $scope.viewportRullerWidth = viewport.width();
        }


        /**
         * Adjust artificial viewport
         *
         * @since 0.3.2
         */
        
        $scope.adjustViewport = function(size) {

            //console.log("adjustViewport()", size);
        
            jQuery("#ct-artificial-viewport").css("width", size);

            $scope.adjustViewportRuller();
        }


        /**
         * Adjust viewport container
         *
         * @since 0.3.2
         */

        $scope.adjustViewportContainer = function(artificialViewportWidth) {
            
            // DOM Tree opened, Add+ opened
            if ( $scope.showSidePanel && $scope.isActiveActionTab('componentBrowser') ) {

                if (artificialViewportWidth===undefined) {
                    artificialViewportWidth = window.innerWidth - 350 - 300
                }

                if (!$scope.viewportRullerShown){
                    /*jQuery("#ct-artificial-viewport").stop().animate({
                        width: window.innerWidth - 350 - 300 - 12
                    }, 250);*/
                }
                jQuery("#ct-viewport-container").stop().animate({
                    width: window.innerWidth - 350 - 300,
                    marginLeft: 300,
                    paddingTop: 39
                }, 250, function() {
                    $scope.adjustArtificialViewport(artificialViewportWidth);
                    $scope.adjustViewportRuller();
                });
                $scope.sidePanelElement.stop().animate({
                    width: "330px"
                }, 250);
                jQuery(".ct-panel", "#ct-components-browser").stop().animate({
                    left: "0px"
                }, 250);
                jQuery(".ct-panel-tabs-container", "#ct-components-browser").stop().animate({
                    opacity: "1"
                }, 250);
            }
            else

            // DOM Tree opened, Add+ closed
            if ( $scope.showSidePanel && !$scope.isActiveActionTab('componentBrowser') ) {

                if (artificialViewportWidth===undefined) {
                    artificialViewportWidth = window.innerWidth - 350
                }
               
                if (!$scope.viewportRullerShown){
                    jQuery("#ct-artificial-viewport").stop().animate({
                        width: window.innerWidth - 350 - 12
                    }, 250);
                    jQuery("#ct-artificial-viewport").css("min-width", $scope.pageSettings['max-width']+"px");
                }
                jQuery("#ct-viewport-container").stop().animate({
                    width: window.innerWidth - 350,
                    marginLeft: 0,
                    paddingTop: 0
                }, 250, function() {
                    $scope.adjustArtificialViewport(artificialViewportWidth);
                    $scope.adjustViewportRuller();
                });
                $scope.sidePanelElement.stop().animate({
                    width: "330px"
                }, 250);
                jQuery(".ct-panel", "#ct-components-browser").stop().animate({
                    left: "-300px"
                }, 250);
                jQuery(".ct-panel-tabs-container", "#ct-components-browser").stop().animate({
                    opacity: "0"
                }, 250);
            }
            else

            // DOM Tree closed, Add+ opened
            if ( !$scope.showSidePanel && $scope.isActiveActionTab('componentBrowser') ) {

                if (artificialViewportWidth===undefined) {
                    artificialViewportWidth = window.innerWidth - 300
                }
               
                if (!$scope.viewportRullerShown){
                    /*jQuery("#ct-artificial-viewport").stop().animate({
                        width: window.innerWidth - 320 - 12
                    }, 250);*/
                    jQuery("#ct-artificial-viewport").css("min-width", $scope.pageSettings['max-width']+"px");
                }
                jQuery("#ct-viewport-container").stop().animate({
                    width: window.innerWidth - 320,
                    marginLeft: 300,
                    paddingTop: 39
                }, 250, function() {
                    $scope.adjustArtificialViewport(artificialViewportWidth);
                    $scope.adjustViewportRuller();
                });
                $scope.sidePanelElement.stop().animate({
                    width: "0"
                }, 250);
                jQuery(".ct-panel", "#ct-components-browser").stop().animate({
                    left: "0"
                }, 250);
                jQuery(".ct-panel-tabs-container", "#ct-components-browser").stop().animate({
                    opacity: "1"
                }, 250);
            }
            else
            
            // All closed
            {   
                if (!$scope.viewportRullerShown){
                    jQuery("#ct-artificial-viewport").stop().animate({
                        width : jQuery(".oxygen-builder-body").width()-12
                    }, 250);
                }
                jQuery("#ct-viewport-container").stop().animate({
                    width: "100%",
                    marginLeft: 0,
                    paddingTop: 0
                }, 250, function() {
                    $scope.adjustArtificialViewport(artificialViewportWidth);
                    $scope.adjustViewportRuller();
                });
                $scope.sidePanelElement.stop().animate({
                    width: "0"
                }, 250);
                jQuery(".ct-panel", "#ct-components-browser").stop().animate({
                    left: "-300px"
                }, 250);
                jQuery(".ct-panel-tabs-container", "#ct-components-browser").stop().animate({
                    opacity: "0"
                }, 250);
            }
        }


        /**
         * Adjust artificial viewport
         */

        $scope.adjustArtificialViewport = function(artificialViewportWidth) {

            // adjust artificial viewport based on "Page width"
            if ($scope.currentMedia == "default") {

                var viewportContainerWidth = jQuery("#ct-viewport-container").width();
                    pageWidth = $scope.getWidth($scope.pageSettings['max-width']);

                // ruller is active
                if ( $scope.viewportRullerShown ) {
                    
                    // add sidescroll if section width is wider than viewport
                    if ( pageWidth.value > $scope.viewportRullerWidth) {
                        $scope.builderElement.css("width", $scope.pageSettings['max-width']);
                        //console.log("adjustArtificialViewport()", $scope.pageSettings['max-width'])
                    }
                    else {
                        $scope.builderElement.css("width", "");
                        //console.log("adjustArtificialViewport()", "")
                    }
                }
                // no ruller
                else {

                    if (artificialViewportWidth===undefined) {
                        artificialViewportWidth = jQuery("#ct-artificial-viewport").width();
                    }
                    
                    if ( pageWidth.value > artificialViewportWidth ) {
                        jQuery("#ct-artificial-viewport").css("width", $scope.pageSettings['max-width']);
                        jQuery("#ct-artificial-viewport").css("min-width", "");
                        //jQuery("#ct-artificial-viewport").animate({width: $scope.pageSettings['max-width']}, 250);
                        //console.log("adjustArtificialViewport()", $scope.pageSettings['max-width'])
                    }
                    else
                    if ( pageWidth.value < viewportContainerWidth ) {
                        jQuery("#ct-artificial-viewport").css("width", "");
                        jQuery("#ct-artificial-viewport").css("min-width", "");
                        //console.log("adjustArtificialViewport()", "")
                    }

                    // unset builder width
                    $scope.builderElement.css("width", "");
                }
            }
            else {
                // unset builder width
                $scope.builderElement.css("width", "");
                //console.log("adjustArtificialViewport()", "")
            }
        }


        /**
         * Measureboxes
         */
        
        $scope.toolbarElement
        .on("click", ".ct-measure-type", function(e) {
            // hide all boxes
            jQuery(".ct-measurebox-container:not(.ct-border-options)", "#ct-toolbar")
                .removeClass("ct-measure-type-select-active")
                .removeClass("ct-measurebox-selected");
            // show the box
            jQuery(this).closest(".ct-measurebox-container", "#ct-toolbar").addClass("ct-measure-type-select-active");
            measureboxOutsideClick();
        })
        .on("click", ".ct-measure-type-option", function(e) {
            // hide the box
            jQuery(this).closest(".ct-measurebox-container", "#ct-toolbar").removeClass("ct-measure-type-select-active");
        })
        .on("click", ".ct-measurebox-with-options .ct-measure-value", function(e) {
            // hide all boxes
            jQuery(".ct-measurebox-container", "#ct-toolbar")
                .removeClass("ct-measure-type-select-active")
                .removeClass("ct-measurebox-selected");
            
            // show one box
            jQuery(this).closest(".ct-measurebox-container", "#ct-toolbar").addClass("ct-measurebox-selected");
            measureboxOutsideClick();
        })
        .on("focus", ".ct-measure-value", function(e) {
            // select all text
            this.setSelectionRange(0, this.value.length)
        })
        // make box not closed when ('html').click triggered 
        .on("click", ".ct-measurebox-container", function(e){
            e.stopPropagation();
        })
        .on("click", ".ct-measure-type-select", function(e){
            e.stopPropagation();
        });

        function measureboxOutsideClick() {
            // close the box if user click outside it
            jQuery('html').click(function(clickEvent) {
                // close
                jQuery(".ct-measurebox-container", "#ct-toolbar")
                    .removeClass("ct-measure-type-select-active")
                    .removeClass("ct-measurebox-selected");

                // unbid it immideately
                jQuery(this).unbind(clickEvent);
            });
        }

        /**
         * Selects
         */
        
        $scope.toolbarElement
        .on("click", ".ct-select:not(.ct-ui-disabled,.ct-custom-selector)", function(e) {
            
            // if the click was inside a text input for new classname, do not hide the select dropdown
            if(jQuery(e.target).hasClass('ct-new-component-class-input')) {
                e.stopPropagation();
                return;
            }

            var isActive = jQuery(this).hasClass("ct-active");

            // hide all dropdowns
            jQuery(".ct-select").removeClass("ct-active").removeClass("ct-active-media").removeClass("ct-active-states");

            // show this dropdown
            if (!isActive) {
                jQuery(this).addClass("ct-active");
                selectOutsideClick();
            }
            
            // focus on search
            jQuery(".ct-searchbar input",this).focus();

            if ( jQuery(this).parents('.ct-style-set-dropdown') ) {
                jQuery(".ct-new-component-class-input",this).focus();
            }

            e.stopPropagation();
        })

        /**
         * Media
         */

        // media icon click
        .on("click", ".ct-select:not(.ct-ui-disabled) .ct-selected .ct-media-icon", function(e) {

            var select = jQuery(this).closest('.ct-select');
                isActive = select.hasClass("ct-active-media");

            // hide all dropdowns
            jQuery(".ct-select").removeClass("ct-active").removeClass("ct-active-media").removeClass("ct-active-states");

            // show this dropdown
            if (!isActive) {
                select.addClass("ct-active-media");
                selectOutsideClick();
            }

            e.stopPropagation();
        })

        // media item click
        .on("click", ".ct-media-list li", function(e) {

            // hide all dropdowns
            jQuery(".ct-select").removeClass("ct-active-media").removeClass("ct-active-states");
            e.stopPropagation();
        })

        /**
         * State
         */

        // state button click
        .on("click", ".ct-select:not(.ct-ui-disabled) .ct-selected .ct-states-button", function(e) {

            var select = jQuery(this).closest('.ct-select');
                isActive = select.hasClass("ct-active-states");

            // hide all dropdowns
            jQuery(".ct-select").removeClass("ct-active").removeClass("ct-active-states").removeClass("ct-active-media");

            // show this dropdown
            if (!isActive) {
                select.addClass("ct-active-states");
                selectOutsideClick();
            }

            e.stopPropagation();
        })

        // media icon click
        .on("click", ".ct-states-list li", function(e) {

            // hide all dropdowns
            jQuery(".ct-select").removeClass("ct-active-states");
            e.stopPropagation();
        })

        // do not close select when search clicked
        .on("click", ".ct-searchbar", function(e) {
            e.stopPropagation();
        });

        function selectOutsideClick() {
            // close the box if user click outside it
            jQuery('html').click(function(clickEvent) {
                // close
                jQuery(".ct-select", "#ct-toolbar").removeClass("ct-active").removeClass("ct-active-media").removeClass("ct-active-states");

                // unbid it immideately
                jQuery(this).unbind(clickEvent);
            });
        }

        
        /**
         * Border options
         */

        // do not close select when search clicked
        $scope.toolbarElement
        .on("click", ".ct-border-options", function(e) {
            // hide all 
            jQuery(".ct-border-options", "#ct-toolbar").removeClass("ct-measurebox-selected");

            // show one box
            jQuery(this).addClass("ct-measurebox-selected");
            measureboxOutsideClick();
        })


        /**
         * Apply all options
         */
        
        // mark/unmark measurebox as 'apply all'
        .on("click", ".ct-apply-all-trigger", function(e) {

            var _self = jQuery(this);

            if (_self.data('previuosly-checked')) {
                _self.prop('checked', false)
                _self.parents(".ct-measurebox-container").removeClass('ct-apply-all');
            } else {
                jQuery("input",this.closest(".ct-measurebox-options")).data('previuosly-checked', false);
                _self.parents(".ct-measurebox-container").addClass('ct-apply-all').removeClass('ct-apply-opposite');
                applyAll(this);
            }

            var checked = _self.prop('checked');
            _self.data('previuosly-checked', checked);

            // check for border radius
            if (_self.parents(".ct-border-radius-box").length > 0){
                
                // mark/unmark all measureboxes as 'apply all'
                _self.parents(".ct-border-radius-box").find(".ct-apply-all-trigger").each(function(){
                    
                    if (!checked) {
                        _self.prop('checked', false);
                        _self.attr('checked', false);
                        _self.parents(".ct-measurebox-container").removeClass('ct-apply-all');
                    } else {
                        _self.attr('checked', true);
                        jQuery("input",this.closest(".ct-measurebox-options")).data('previuosly-checked', false);
                        _self.parents(".ct-measurebox-container").addClass('ct-apply-all').removeClass('ct-apply-opposite');
                    }
                    _self.data('previuosly-checked', jQuery(this).prop('checked'));
                });
            }

            // reselect value
            jQuery(".ct-measure-value",this.closest(".ct-measurebox-container")).focus();
        })

        // mark/unmark measurebox as 'apply opposite' 
        .on("click", ".ct-apply-opposite-trigger", function(e) {

            var _self = jQuery(this);

            if (_self.data('previuosly-checked')) {
                _self.prop('checked', false)
                _self.parents(".ct-measurebox-container").removeClass('ct-apply-opposite');
            } else {
                jQuery("input",this.closest(".ct-measurebox-options")).data('previuosly-checked', false);
                _self.parents(".ct-measurebox-container").addClass('ct-apply-opposite').removeClass('ct-apply-all');
                applyOpposite(this);
            }

            _self.data('previuosly-checked', jQuery(this).prop('checked'));

            // reselect value
            jQuery(".ct-measure-value", this.closest(".ct-measurebox-container")).focus();
        })

        // update all values on 'apply all' value change
        .on("input", ".ct-apply-all .ct-measure-value", function(e) {

            // find a checkbox to pass into applyAll
            var measureBox  = jQuery(this).parents(".ct-measurebox-container"),
                trigger     = jQuery(".ct-apply-all-trigger", measureBox);
            
            applyAll(trigger[0], jQuery(this).val());
        })

        // update all values on unit select
        .on("click", ".ct-apply-all .ct-measure-type-option", function(e) {

            // find a checkbox to pass into applyAll
            var measureBox  = jQuery(this).parents(".ct-measurebox-container"),
                trigger     = jQuery(".ct-apply-all-trigger", measureBox);
            
            applyAll(trigger[0]);
        });

        function applyAll(element, value, unit) {

            if ($scope.log) {
                console.log("applyAll()");
                $scope.functionStart("applyAll()");
            }

            if ( element.checked ) {

                var elementObj = jQuery(element);
            
                var measureBox  = elementObj.parents(".ct-measurebox-container"),
                    sizeBox     = elementObj.parents(".ct-size-box"),
                    option      = elementObj.data("option");

                // get values from $scope if not defined
                if (undefined === value) {
                    value = $scope.getOption(option);
                }
                if (undefined === unit) {
                    unit = $scope.getOptionUnit(option);
                }
                
                // loop all size box values to apply currently editing value
                jQuery(".ct-apply-all-trigger", sizeBox).each(function(){

                    var option          = jQuery(this).data("option"),
                        currentValue    = $scope.getOption(option),
                        currentUnit     = $scope.getOptionUnit(option);

                    if (currentValue != value) {
                        $scope.setOptionModel(option, value, $scope.component.active.id, $scope.component.active.name, true);
                    }

                    if (currentUnit != unit) {
                        $scope.setOptionUnit(option, unit, true);
                    }
                })

                // safely apply scope
                var timeout = $timeout(function() {
                    $scope.$apply();
                    $timeout.cancel(timeout);
                }, 0, false);

                // update styles
                $scope.outputCSSOptions($scope.component.active.id);
            }

            $scope.functionEnd("applyAll()");
        }

        // update all values on 'apply all' value change
        $scope.toolbarElement
        .on("input", ".ct-apply-opposite .ct-measure-value", function(e) {

            // find a checkbox to pass into applyAll
            var measureBox  = jQuery(this).parents(".ct-measurebox-container"),
                trigger     = jQuery(".ct-apply-opposite-trigger", measureBox);
            
            applyOpposite(trigger[0], jQuery(this).val());
        })

        // update all values on unit select
        .on("click", ".ct-apply-opposite .ct-measure-type-option", function(e) {

            // find a checkbox to pass into applyAll
            var measureBox  = jQuery(this).parents(".ct-measurebox-container"),
                trigger     = jQuery(".ct-apply-opposite-trigger", measureBox);
            
            applyOpposite(trigger[0]);
        });

        function applyOpposite(element, value, unit) {

            $scope.functionStart("applyOpposite");

            if ( element.checked ) {

                var elementObj = jQuery(element);
            
                var measureBox      = elementObj.parents(".ct-measurebox-container"),
                    sizeBox         = elementObj.parents(".ct-size-box"),
                    option          = elementObj.data("option"),
                    oppositeOption  = elementObj.data("opposite-option");

                // get values from $scope if not defined
                if (undefined === value) {
                    value = $scope.getOption(option);
                }
                if (undefined === unit) {
                    unit = $scope.getOptionUnit(option);
                }
                
                // loop all size box values to apply currently editing value
                jQuery('[data-option="'+oppositeOption+'"]', sizeBox).each(function(){

                    var option = jQuery(this).data("option");

                    $scope.setOptionModel(option, value, $scope.component.active.id, $scope.component.active.name, true);
                    $scope.setOptionUnit(option, unit, true);

                    // safely apply scope
                    var timeout = $timeout(function() {
                        $scope.$apply();
                        $timeout.cancel(timeout);
                    }, 0, false);
                })

                // update styles
                $scope.outputCSSOptions($scope.component.active.id);
            }

             $scope.functionEnd("applyOpposite");
        }


        
        /**
         * Increase descrease measure values with top/bottom key press
         */
        
        $scope.toolbarElement.on("keydown", ".ct-measure-value", function(e) {
                
            // increase 
            if (e.keyCode==38) {

                // TODO: add support for float values
                if (this.value == parseInt(this.value, 10)){
                    this.value++;
                    var input = jQuery(this);
                    input.trigger("change").trigger("input");
                }
            };
            
            // decrease
            if (e.keyCode==40) {

                // TODO: add support for float values
                if (this.value == parseInt(this.value, 10)){
                    this.value--;
                    var input = jQuery(this);
                    input.trigger("change").trigger("input");
                }
            }

        });


        // Returns a function, that, as long as it continues to be invoked, will not
        // be triggered. The function will be called after it stops being called for
        // N milliseconds. If `immediate` is passed, trigger the function on the
        // leading edge, instead of the trailing.
        function debounce(func, wait, immediate) {
            var timeout;
            return function() {
                var context = this, args = arguments;
                var later = function() {
                    timeout = null;
                    if (!immediate) func.apply(context, args);
                };
                var callNow = immediate && !timeout;
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
                if (callNow) func.apply(context, args);
            };
        };


        /**
         * Open/close DOM tree node options
         */
        
        // toggle on icon click
        $scope.toolbarElement.on("mousedown", ".ct-more-options-icon", function(e) {

            var isExpanted = jQuery(this).parent().hasClass("ct-more-options-expanded");
                $scope.optionsToOpen = jQuery(this);

            // close all options
            jQuery(".ct-more-options-expanded", "#ct-sidepanel").removeClass("ct-more-options-expanded");
            
            // open this option
            if ( !isExpanted ) {
                var timeout = $timeout(function(_self) {

                    $scope.optionsToOpen.parent().addClass("ct-more-options-expanded");
                    // cancel timeout
                    $timeout.cancel(timeout);
                }, 100, false);
            }
        })

        // This is not working as ng-click triggered first. TODO: find a fix
        // close on item click
        //.on("click", ".ct-more-options-expanded li", function(e) {
        //    jQuery(this).closest('.ct-node-options').removeClass("ct-more-options-expanded");
        //})

        // special properties click
        .on("mousedown", ".oxygen-special-property", function(e){
            $scope.setEditingStateToDefault();
        });

        // window resize
        jQuery(window).resize(function() {
            $scope.adjustViewportContainer();
        });

        jQuery(window).on('click', function(e) {
            if(jQuery(e.target).closest('.ct-active').length < 1 && jQuery(e.target).closest('.ct-editor-panel').length < 1)
                $scope.disableContentEdit();

            /*var clickedComponent = parseInt(jQuery(e.target).closest('.ct-component').attr('ng-attr-component-id'));
           
            $scope.component.active.id = clickedComponent;*/

            
            /*if(clickedComponent === 0) {
                $scope.activateComponent(0, 'root');
            } else if(clickedComponent > 100000) {
                $scope.activateComponent(clickedComponent, 'ct_inner_content');
            }*/
            
           // console.log($scope.component.active.id, parseInt(jQuery(e.target).closest('.ct-component').attr('ng-attr-component-id')));
            
        });
    }
});


/**
 * Animation
 *
 * @since 0.2.2
 */


/**
 * Animate expanded toolbar content like Advanced Settings
 * 
 */

animateToolbar = function($window) {

    var getScope = function(e) {
        return angular.element(e).scope();
    };

    var hasOpenTabs = function(obj) {
        for(var o in obj) {
            if(obj[o]) return true;
        }
        return false;
    };

    var toolbarElement      = jQuery("#ct-toolbar");
    var builderElement      = jQuery("#ct-toolbar");
    var sidePanelElement    = jQuery("#ct-sidepanel");
    
    return {
        // on tab open
        enter: function(element, doneFn) {

            //console.log('enter');

            var mainToolbarHeight   = jQuery("#ct-toolbar-main").height(),
                toolbarHeight       = toolbarElement.height(),
                builderPadding      = builderElement.css("padding-top").replace("px", ""),
                windowScroll        = jQuery($window).scrollTop(),
                scrollTop           = windowScroll + toolbarHeight - mainToolbarHeight;

            /* console.log(mainToolbarHeight)
            console.log(toolbarHeight)
            console.log(element) */
            
            // animate toolbar
            jQuery(element)
                .hide()
                .slideDown(250, function(){
                    toolbarElement.addClass("ct-toolbar-open");
                    sidePanelElement
                        .stop(true)
                        .animate({top:toolbarElement.height()},150);
                    doneFn();
                });

            if (    scrollTop <= toolbarHeight 
                    && builderPadding > mainToolbarHeight 
                    && toolbarElement.hasClass("ct-toolbar-fixed") 
                ) {
                
                // stop any current animation to prevent jump
                //builderElement.stop(true);
                //jQuery("#body").stop(true);
                // add new animation
                builderElement.animate({paddingTop:toolbarHeight},250);
                //jQuery("body").animate({scrollTop:0},3250);
            }
        },

        // on tab close
        leave: function(element, doneFn) {

            //console.log('leave');

            var mainToolbarHeight   = jQuery("#ct-toolbar-main").height(),
                toolbarHeight       = toolbarElement.height(),
                builderPadding      = builderElement.css("padding-top").replace("px", ""),
                windowScroll        = jQuery($window).scrollTop(),
                scrollTop           = windowScroll - toolbarHeight + mainToolbarHeight;
            
            jQuery(element).slideUp(250, function(){
                toolbarElement.removeClass("ct-toolbar-open");
                sidePanelElement
                    .stop(true)
                    .animate({top:toolbarElement.height()},150);
                doneFn();
            }); 
            
            if (    scrollTop <= toolbarHeight 
                    && builderPadding > mainToolbarHeight 
                    && toolbarElement.hasClass("ct-toolbar-fixed") 
                ) {
                
                // stop any current animation to prevent jump
                //builderElement.stop(true);
                //jQuery("#body").stop(true);
                // add new animation
                var $scope = getScope(element);
                if ( !hasOpenTabs($scope.actionTabs) ) {
                    builderElement.animate({paddingTop:mainToolbarHeight},250);
                    jQuery("body").animate({scrollTop:0},250);
                }
            }
        }
    }
}

//CTFrontendBuilder.animation('.ct-panel', animateToolbar);
CTFrontendBuilder.animation('.ct-toolbar-expanded', animateToolbar);


/**
 * Animate DOM Tree Details
 * 
 */
animateDOMTreeNodeDetails = function($window) {

    return {

        addClass: function(element, className, doneFn) {

            if (className!="ct-dom-tree-node-active") {
                doneFn();
                return false;
            }

            var details = jQuery(".ct-dom-tree-node-details", element);

            details.hide();
            details.stop().slideDown({
                duration: 250,
                easing: "linear",
                complete: function(){
                    doneFn();
                }
            });
        },

        removeClass: function(element, className, doneFn) {

            if (className!="ct-dom-tree-node-active") {
                doneFn();
                return false;
            }

            var details = jQuery(".ct-dom-tree-node-details", element);

            details.stop().slideUp({
                duration: 250,
                easing: "linear",
                complete: function(){
                    doneFn();
                }
            });
        },
    }
}

CTFrontendBuilder.animation('.ct-dom-tree-node-anchor', animateDOMTreeNodeDetails);


/**
 * Animate DOM Tree Node
 * 
 */
animateDOMTreeNode = function($window) {

    return {

        addClass: function(element, className, doneFn) {

            if (className!="ct-dom-tree-node-expanded") {
                doneFn();
                return false;
            }

            var details = jQuery(element).children(".ct-dom-tree-node");

            details.hide();
            details.stop().slideDown(250, function(){
                doneFn();
            });
        },
        removeClass: function(element, className, doneFn) {

            if (className!="ct-dom-tree-node-expanded") {
                doneFn();
                return false;
            }

            var details = jQuery(element).children(".ct-dom-tree-node");

            details.stop().slideUp(250, function(){
                doneFn();
            });
        },
    }
}

CTFrontendBuilder.animation('.ct-dom-tree-node', animateDOMTreeNode);


/**
 * Animate DOM Tree Details
 * 
 */

animateStyleSetNode = function($window) {

    return {

        addClass: function(element, className, doneFn) {

            if (className!="ct-style-set-expanded") {
                doneFn();
                return false;
            }

            var details = jQuery(element).nextAll(".ct-style-set-child-selector");

            details.hide();
            details.stop().slideDown(250, function(){
                doneFn();
            });
        },
        removeClass: function(element, className, doneFn) {

            if (className!="ct-style-set-expanded") {
                doneFn();
                return false;
            }

            var details = jQuery(element).nextAll(".ct-style-set-child-selector");

            details.stop().slideUp(250, function(){
                doneFn();
            });
        },
    }
}

CTFrontendBuilder.animation('.ct-style-set-node', animateStyleSetNode);


/**
 * Animate Side Panel
 * 
 */
animateSidePanel = function($window) {

    return {

        addClass: function(element, className, doneFn) {

            /* if (className!="ct-sidepanel-show") {
                doneFn();
                return false;
            }

            jQuery(element).stop().animate({
                    width: "330px"
                }, 250, function() {
                // Animation complete
                doneFn();
            });*/
        },

        removeClass: function(element, className, doneFn) {

            if (className!="ct-sidepanel-show") {
                doneFn();
                return false;
            }

            setTimeout(function() {doneFn();}, 250);
        },
    }
}
CTFrontendBuilder.animation('.ct-panel-elements-managers', animateSidePanel);

/**
 * Animate Add+ section
 * 
 */
animateComponentsBrowser = function($window) {

    return {

        removeClass: function(element, className, doneFn) {

            if (className!="ct-components-browser-open") {
                doneFn();
                return false;
            }

            setTimeout(function() {doneFn();}, 250);
        },
    }
}
CTFrontendBuilder.animation('.ct-components-browser', animateComponentsBrowser);


/**
 * Disable ng-animate for elements with "ct-no-animate" class
 *
 * @since 0.2.2
 */

CTFrontendBuilder.config(['$animateProvider', function($animateProvider){
  // disable animation for elements with the ct-no-animate css class with a regexp.
  // note: "ct-*" is our css namespace
  $animateProvider.classNameFilter(/^((?!(ct-no-animate)).)*$/);
}]);