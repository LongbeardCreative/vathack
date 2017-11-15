/**
 * UI to navigate Componenets Tree: DOM Tree, breadcrumbs, up level buttons, ...
 * 
 */

CTFrontendBuilder.controller("ControllerNavigation", function($scope, $http, $timeout) {

    $scope.domTreeFilter = {
        domTreeSearchKeyword:''
    }

    $scope.openFolders = {};

    /**
     * Generate breadcrumbs
     * 
     * @since 0.1.2
     */

    $scope.generateBreadcrumbs = function(key, item) {

        // temporary disabled
        return false;

        if ( item.name == "ct_link" ) {
            $scope.findComponentItem($scope.componentsTree.children, item.options.ct_parent, $scope.generateBreadcrumbs);
            return false;
        }

        var activate    = ' ng-click="activateComponent(' + key + ', \'' + item.name + '\', $event)"',
            name        = $scope.niceNames[item.name],
            activeClass = "";

        if ( item.name == "ct_woocommerce" ) {
                hookName = $scope.getWooCommerceHookNiceName(item.options.original['hook_name']);
                name += hookName;
            }

        if ( key == $scope.component.active.id ) {
            activeClass     = "ct-breadcrumb-current";
            activate        = "";
        }

        var breadcrumb =    '<span class="ct-breadcrumb ' + activeClass + '"' + activate + '>' +
                                name +
                            '</span>';
        
        // if not root and not root's child
        if ( item.options && item.id != 0 ) {

            if ( $scope.componentsBreadcrumbs == "" ) {
                // add parent element and current element
                $scope.componentsBreadcrumbs = breadcrumb;
            }
            else {
                // prepend parents
                $scope.componentsBreadcrumbs = breadcrumb + ' &gt; ' + $scope.componentsBreadcrumbs;
            }

            $scope.findComponentItem($scope.componentsTree.children, item.options.ct_parent, $scope.generateBreadcrumbs);
        }
        else {
            $scope.componentsBreadcrumbs = '<span class="ct-breadcrumb" ng-click="activateComponent(0, \'root\', $event)">Root</span> &gt; ' + $scope.componentsBreadcrumbs;
        }
    }


    /**
     * Update breadcrumbs based on currently active element
     *
     * @since 0.1.2
     */
    
    $scope.updateBreadcrumbs = function() {

        // temporary disabled
        return false;

        // empty breadcrumbs
        $scope.componentsBreadcrumbs = "";

        // get container to insert
        var breadcrumbsContainer = document.getElementsByClassName("ct-breadcrumbs");

        // create jqLite element and clear HTML
        breadcrumbsContainer = angular.element(breadcrumbsContainer);
        breadcrumbsContainer.empty();

        if ( $scope.component.active.id != 0 ) {
            // recursivley build breadcrumbs
            $scope.findComponentItem($scope.componentsTree.children, $scope.component.active.id, $scope.generateBreadcrumbs);
            breadcrumbs = $scope.componentsBreadcrumbs;
        }
        else {
            breadcrumbs = "<span>Root</span>";
        }

        // inseret to DOM
        angular.element(document).injector().invoke(function($compile) {
            breadcrumbsContainer.append($compile(breadcrumbs)($scope));
        });
    }


    /**
     * Generate one level up button
     * 
     * @since 0.1.2
     */

    $scope.generateUpButton = function(id, item) {

        // root has no parent
        if ( id != 0 ) {
            var parentId = item.options.ct_parent;
            name = $scope.niceNames[item.name];
        }
        else {
            name = "Root";
        }

        var activate    = ' ng-click="activateComponent(' + id + ', \'' + item.name + '\', $event);"',
            title       = 'Level up to &quot;' + name + '&quot;',
            parent      = '<span class="ct-up-level-button" title="' + title + '" ng-attr-up-level-id="'+id+'"' + activate + '></span>';


        if ( $scope.upButton == "" ) {

            $scope.upButton = "1";
            $scope.findComponentItem($scope.componentsTree.children, parentId, $scope.generateUpButton);
        } 
        else {
            $scope.upButton = parent;
        }
    }


    /**
     * Button to go one level up in Components Tree
     *
     * @since 0.1.2
     */
    
    $scope.updateUpButton = function() {

        // empty button
        $scope.upButton = "";

        // get container to insert
        var upButtonContainer = document.getElementById("ct-up-level-button");

        // create jqLite element and clear HTML
        upButtonContainer = angular.element(upButtonContainer);
        upButtonContainer.empty();

        if ( $scope.component.active.id != 0 ) {
            
            $scope.findComponentItem($scope.componentsTree.children, $scope.component.active.id, $scope.generateUpButton);   

            upButton = $scope.upButton;

            if ( ! upButton )
                return;

            // insert to DOM
            angular.element(document).injector().invoke(function($compile) {
                upButtonContainer.append($compile(upButton)($scope));
            });
        }
    }

    /**
     * returns true if any of the properties (component type, HTML tag, class(s), selector ID, ID, nice name) 
     * contains the string from the domTree search box
     *
     * @since 0.3.3
     * @author gagan goraya
     */

    $scope.filterComponentForAMatch = function(id, item) {

        var checkAgainst = [
            $scope.niceNames[item.name], //component type
            '<'+$scope.htmlTags[item.name]+'>', //html tag
            item.options.selector,
            id.toString()
        ]
        
        // classes
        if(item.options.classes && item.options.classes.length > 0)
            checkAgainst = checkAgainst.concat(item.options.classes);

        // if nicename is defined
        if(item.options.nicename) {
            checkAgainst.push(item.options.nicename);
        }
        else {
            checkAgainst.push($scope.calcDefaultComponentTitle(item));
        }

        var matched = false;

        checkAgainst.forEach(function(element) {
            matched = matched || element.toLowerCase().indexOf($scope.domTreeFilter.domTreeSearchKeyword.toLowerCase()) > -1;
        })
        
        // if anyone of this bloodline matches, then matched should return true. hence we go recursive
        if(item.children) {
            item.children.forEach(function(child) {
                matched = matched || $scope.filterComponentForAMatch(child.id, child);
            })
        }

        return matched;
    }

    /**
     * applies the filterComponentForAMatch as a callback to the component with the given id
     *
     * @since 0.3.3
     * @author gagan goraya
     */

    $scope.isAMatch = function(id) {
        
        return $scope.findComponentItem($scope.componentsTree.children, id, $scope.filterComponentForAMatch);
    }

    /**
     * returns the default Title for a component to display in the DOMTree entry
     *
     * @since 0.3.3
     * @author gagan goraya
     */

    $scope.calcDefaultComponentTitle = function(item) {
        
        var niceName = $scope.niceNames[item.name];

        if ( item.name == "ct_reusable" ) {
            niceName += " (post: " + item.options.view_id + ")";
        }
        else if ( item.name == "ct_woocommerce" ) {
            hookName = $scope.getWooCommerceHookNiceName(item.options.original['hook_name']);
            niceName += hookName;
        }
        
        niceName += " (#" + item.id + ")";

        return niceName;
    }

    /**
     * Generate DOM Tree Navigator
     * 
     * @since 0.1.5
     */

    $scope.generateDOMTreeNavigator = function(node, searchId, DOMTree, isChild, reorder) {

        if(typeof(isChild) === 'undefined')
            isChild = false;

        var isBrake = false;

        angular.forEach(node.children, function(item, index) {
            
            if ( !isBrake ) {

                if (undefined === searchId || item.id == searchId) {

                    var id              = item.id,
                        name            = item.name,

                        ngClassNode     = ' ng-class="{\'ct-dom-tree-node-expanded\' : toggledNodes['+id+'],\'ct-dom-tree-child\' : hasParent('+id+')}"}',
                        ngClassAnchor   = ' ng-class="{\'ct-dom-tree-node-selected\' : isActiveId('+id+')}"';

                    var niceName = $scope.calcDefaultComponentTitle(item),
                        activate    = ' ng-mousedown="triggerCodeMirrorBlur('+id+');" ng-click="activateComponent('+id+', \'' + name + '\',$event); scrollToComponent(\''+item.options.selector+'\');"',
                        classes     = "",
                        title       = "";

                    if ( !item.children ) {
                        classes += " ct-dom-tree-no-children";
                    }

                    if (    item.name == "ct_columns"    || 
                            item.name == "ct_column"    || 
                            item.name == "ct_section"   || 
                            item.name == "ct_ul"        || 
                            item.name == "ct_link"      || 
                            (item.name == 'ct_inner_content' && (!CtBuilderAjax['query'] || !CtBuilderAjax['query']['post_type'] || CtBuilderAjax['query']['post_type'] !== 'ct_template') ) ||
                            item.name == "ct_div_block" ) {
                        classes += " ct-accept-drops";
                    }
                    

                    if ( item.name == "ct_columns" ) {
                        classes += " ct-sortable-columns";
                    }

                    if ( item.name == "ct_column" ) {
                        classes += " ct-sortable-column";
                    }

                    if ( item.name == "ct_ul" ) {
                        classes += " ct-sortable-ul";
                    }

                    if ( item.name == "ct_li" ) {
                        classes += " ct-sortable-li";
                    }

                    if ( item.name == "ct_section" ) {
                        classes += " ct-sortable-section";
                    }

                    if ( item.name == "ct_link" ) {
                        classes += " ct-sortable-link";
                    }

                    if ( item.name == "ct_link_text" ) {
                        classes += " ct-sortable-link-text";
                    }

                    if ( item.name != "ct_span" ) {
                        classes += " ct-draggable";
                    }

                    // check if user can componentize
                    if ($scope.isDev()) {
                        var componentize = '<li ng-show="isCanComponentize('+id+',\''+name+'\');"'+
                                                'ng-click="showComponentize('+id+')">'+
                                                    'Componentize'+
                                            '</li>';
                    }
                    else {
                        var componentize = "";
                    }


                    if ($scope.component.options[item.id]==undefined) {
                        $scope.component.options[item.id] = {};
                    }
                    $scope.component.options[item.id]['nicename'] = ($scope.component.options[item.id]['nicename'] && $scope.component.options[item.id]['nicename'].trim() !== '') ? $scope.component.options[item.id]['nicename'] : niceName;
                    
                    if(parseInt(item.id) < 100000)
                        DOMTree.HTML += 
                                '<div ng-if="domTreeFilter.domTreeSearchKeyword===\'\' || isAMatch('+id+')" ng-attr-tree-id="' + item.id + '"' + ngClassNode + ' id="ct-dom-tree-node-' + item.id + '" class="ct-dom-tree-node ' + classes + '" ng-Style="isActiveId('+id+')?{zIndex: 9999}:{}">' +
                                    
                                    '<div class="ct-dom-tree-bottom-dash-cover"></div>'+
                                    '<div class="ct-dom-tree-left-dash-cover"></div>'+

                                    '<div ng-attr-node-id="' + id + 
                                        '" class="ct-dom-tree-node-anchor ct-dom-tree-node-type-general ct-dom-tree-name"' + 
                                        ngClassAnchor + activate + '>'+                                 
                                        '<div class="ct-dom-tree-node-header" '+
                                            'ng-class="{\'ct-handle\': editableFriendlyName!='+id+'}">'+
                                            '<div class="ct-expand-butt" ng-click="toggleNode('+id+')">'+
                                                '<span class="ct-icon"></span>'+
                                            '</div>'+
                                            '<span class="ct-icon ct-node-type-icon"></span>' +
                                            '<span class="ct-nicename" ng-if="editableFriendlyName!='+id+'" ng-bind="component.options[\''+item.id+'\'][\'nicename\']" />' + 
                                            '<span class="ct-nicename ct-nicename-editing" ng-blur="setEditableFriendlyName(0)" ng-change="updateFriendlyName('+id+')" ng-if="editableFriendlyName=='+id+'" contenteditable="true" data-plaintext="true" data-defaulttext="'+ niceName +'" ng-Model="component.options[\''+item.id+'\'][\'nicename\']" focus-me="true" />' + 
                                            '<span class="ct-node-details">'+
                                                '&lt;{{htmlTags["'+item.name+'"]}}&gt;'+
                                            '</span>'+
                                            '<div class="ct-node-options">'+
                                                '<span class="ct-icon ct-more-options-icon"></span>'+
                                                '<span class="ct-icon ct-delete-icon" title="Remove Component" ng-click="removeComponentWithUndo('+id+',\''+name+'\''+','+item.options['ct_parent']+')"></span>'+
                                                '<div class="ct-more-options-container">'+
                                                    '<div class="ct-more-options">'+
                                                        '<ul>'+
                                                            componentize+
                                                            '<li ng-show="isCanComponentize('+id+',\''+name+'\');"'+
                                                                'ng-click="saveReusable('+id+')">'+
                                                                    'Make Re-Usable'+
                                                            '</li>'+
                                                            '<li ng-click="duplicateComponent('+id+','+item.options['ct_parent']+')">'+
                                                                'Duplicate'+
                                                            '</li>'+
                                                            '<li ng-click="wrapComponentWith(\'ct_div_block\','+id+','+item.options['ct_parent']+')">'+
                                                                'Wrap with &#60;div&#62;'+
                                                            '</li>'+
                                                            '<li ng-click="setEditableFriendlyName('+id+')">'+
                                                                'Rename'+
                                                            '</li>'+
                                                        '</ul>'+
                                                    '</div>'+
                                                '</div>'+
                                            '</div>'+
                                        '</div>'+
                                        '<div class="ct-dom-tree-horizontal-dash"></div>'+
                                    '</div>';

                    // go deeper in Components Tree
                    if ( item.children ) {
                        $scope.generateDOMTreeNavigator(item, undefined, DOMTree, true); //this last true signifies, that its a child tree
                    }
                    if(parseInt(item.id) < 100000)
                        DOMTree.HTML += "</div>";

                    if ( undefined === searchId ) {
                        $scope.componentsTreeNavigator = DOMTree.HTML;
                    }

                    //  lets use the component options to store friendly editable names
                   
                    //  if the nicename exists in component tree for this item, load it from there
                    $scope.findComponentItem($scope.componentsTree.children, item.id, $scope.loadComponentNiceName);
                }
                // go deeper in Components Tree
                else {
                    if ( item.children ) {
                        $scope.generateDOMTreeNavigator(item, searchId, DOMTree);
                    }
                }

                if (item.id == searchId) {

                    // stop forEach
                    isBrake = true;
                    
                    // remove old node
                    $scope.removeDOMTreeNavigatorNode(item.id);

                    // get parent node to insert
                    
                    var parentId = item.options['ct_parent'] < 100000 ? item.options['ct_parent'] : 0;

                    var parentNode = document.getElementById("ct-dom-tree-node-"+parentId);
                    
                    parentNode = angular.element(parentNode);

                    // compile and insert HTML to DOM Tree navigator

                    $scope.cleanInsert(DOMTree.HTML, parentNode, index+3, reorder);

                    // reload sortable
                    jQuery(parentNode).addClass("ct-dom-tree-node-expanded").removeClass("ct-dom-tree-no-children");
                }
            }
        });
    }


    /**
     * Update DOM Tree Navigator
     * 
     * @since 0.1.5
     */

    $scope.updateDOMTreeNavigator = function(id, reorder) {
        
        // get container to insert
        var navigatorContainer = document.getElementById("ct-dom-tree");

        if ( !navigatorContainer ) {
            return false;
        }

        if ( !$scope.showSidePanel ) {
            //return false;
        }

        if ($scope.log) {
            console.log("updateDOMTreeNavigator()", id);
        }
        $scope.functionStart("updateDOMTreeNavigator");

        var DOMTree = {};
            DOMTree.HTML = "";

        var activate    = ' ng-mousedown="triggerCodeMirrorBlur(0);" ng-click="activateComponent(0, \'root\')"',
            ngClass     = ' ng-class="{\'ct-dom-tree-node-selected\' : isActiveId(0)}"';

        if ($scope.isDev()) {
            var componentize = "<span ng-click=\"showPageComponentize();\" class=\"ct-node-details\">Componentize</span>";
        }
        else {
            var componentize = "";
        }

        // init tree navigator
        var body = document.getElementsByTagName("BODY")[0];
        var treeNavigatorHTML = 
            "<div id=\"ct-dom-tree-node-0\" ng-attr-tree-id=\"0\" dragula=\"'ct-dom-tree'\" class=\"ct-accept-drops ct-elements-managers-bottom\">"+
                "<span></span><span></span>"+ // fake elements for index offset
                "<div " + activate + ngClass + " ng-attr-tree-id=\"0\" ng-click=\"activateComponent(0, 'root')\" class=\"ct-dom-tree-body-anchor ct-dom-tree-node-anchor ct-dom-tree-parent\">" +
                    "<span class=\"ct-icon ct-dom-parent-icon\"></span>" + (body.classList.contains('ct_inner')?"Inner Content":"Body") +
                    componentize+
                "</div>";

        // create jqLite element and clear HTML
        if (undefined === id) {
            navigatorContainer = angular.element(navigatorContainer);
            navigatorContainer.empty();
        }
        
        // generate
        $scope.functionStart("generateDOMTreeNavigator");
        if(!reorder)
            $scope.generateDOMTreeNavigator($scope.componentsTree, id, DOMTree, false);
        $scope.functionEnd("generateDOMTreeNavigator");
        
        // close navigator
        treeNavigatorHTML += ( DOMTree.HTML + "</div></div>" );
        
        if (undefined === id) {
            // compile and insert HTML to DOM Tree navigator
            $scope.cleanInsert(treeNavigatorHTML, navigatorContainer);
        }

        $scope.functionEnd("updateDOMTreeNavigator");
    }
    

    /**
     * Remove single node from DOM Tree Navigator
     * 
     * @since 0.2.5
     */
    
    $scope.removeDOMTreeNavigatorNode = function(id) {

        if ($scope.log) {
            console.log("try removeDOMTreeNavigatorNode()",id);
        }

        // remove old node
        var node = document.getElementById("ct-dom-tree-node-"+id);
        node = angular.element(node);

        var parentNode = jQuery(node).parent();

        if (node.length > 0 && $scope.log) {
            console.log("DOMTreeNavigatorNode() removed", id, node);
        }
        if ( node.scope() !== undefined ) {
            node.scope().$destroy();
        }
        node.remove();
        node = null;     

        if ( jQuery(parentNode).children().length <= 3 ) { // there is always 3 children: anchor, dashed vertical and horizontla
            jQuery(parentNode).addClass("ct-dom-tree-no-children");
        }
    }


    /**
     * Toggle DOM Tree navigator node
     *
     * @since 0.1.5
     */

    $scope.toggleNode = function(id) {
        
        /*var nodeName = $event.currentTarget,
            id = nodeName.attributes['ng-attr-node-id'].value;*/

        $scope.toggledNodes[id] = !$scope.toggledNodes[id];
    }


    /**
     * Expand all nodes in DOM Tree
     *
     * @since 0.3.0
     */

    $scope.expandAllNodes = function() {
        
        for(var id in $scope.component.options) { 
            if ($scope.component.options.hasOwnProperty(id)) {
                $scope.toggledNodes[id] = true;
            }
        }
    }


    /**
     * Collapse all nodes in DOM Tree
     *
     * @since 0.3.0
     */

    $scope.collapseAllNodes = function() {

        $scope.toggledNodes = [];
    }


    /**
     * Toggle all node parents and scroll DOM Tree to this element
     *
     * @since 0.3.0
     */

    $scope.highlightDOMNode = function(id, $event) {

        if ($scope.log) {
            console.log("highlightDOMNode()", id);
        }

        if(id > 100000)  { // this is a component in the outer template, while trying to edit inner content (no need to hilite this)
            return;
        }

        if ($scope.isShowTab('sidePanel','DOMTree') && $scope.showSidePanel) {
            // continue
        }
        else {
            return false;
        }

        if ($event) {
            $event.stopPropagation();
        }

        jQuery("#ct-dom-tree-node-"+id).parents(".ct-dom-tree-node:not(.ct-dom-tree-node-expanded)").each(function(){
            var parentId = jQuery(this).attr("ng-attr-tree-id");
            $scope.toggledNodes[parentId] = true;
        });

        var timeout = $timeout(function() {
            var container           = jQuery("#ct-dom-tree-node-0"),
                containerScrollTop  = container.scrollTop(),
                target              = jQuery("#ct-dom-tree-node-"+id);

            // scroll back to default top 0
            container.scrollTop(0);

            // get target offset
            var targetOffsetTop = target.offset().top;

            // scroll back to saved position
            container.scrollTop(containerScrollTop);

            // finally animate
            container.stop().animate({
                scrollTop: targetOffsetTop - container.offset().top - 120
            }, 500);
            
            // cancel timeout
            $timeout.cancel(timeout);
        }, 500, false);
    }


    /**
     * Show folder's content by its id
     * 
     * @since 0.4.0
     * @author Ilya K.
     */
    
    $scope.openFolder = function(id) {

        $scope.closeAllFolders();
        $scope.openFolders[id] = true;
        
        var timeout = $timeout(function() {
            jQuery(".ct-add-item-button-image",".ct-folder-"+id).each( function() {
                jQuery(this).attr("src",jQuery(this).data("src")); 
            });
        }, 0, false);
    }


    /**
     * Close all folders
     * 
     * @since 0.4.0
     * @author Ilya K.
     */
    
    $scope.closeAllFolders = function(id) {

        $scope.openFolders = {};
    }


    /**
     * Check if folder open
     * 
     * @since 0.4.0
     * @author Ilya K.
     */
    
    $scope.isShowFolder = function(id) {
        
        return ( $scope.openFolders[id] ) ? true : false;
    }

});