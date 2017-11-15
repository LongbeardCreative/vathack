/**
 * All tree manipulations here
 * 
 */

CTFrontendBuilder.controller("ComponentsTree", function($scope) {

    /**
     * Components Tree object. Contain all data needed to generate DOM.
     * Passed to WordPress as JSON on save.
     * 
     */
    
    // Example sctructure
    $scope.componentsTreeExample = {

        'name' : 'root',
        'children': [ 
                {
                    'id' : 1,
                    'name' : 'section',
                    'options' : {
                            'ct_parent': 0,
                            'ct_id': 1,
                            'original' : {
                                'background' : '#515151',
                            },
                            'hover' : {
                                'background' : '#cccccc',
                            }
                        },
                    'children': [
                        {
                            'id' : 2,
                            'name' : 'button',
                            'options' : {
                                'ct_parent': 1,
                                'ct_id': 2,
                                'original' : {
                                        'value' : 'Click Me!',
                                    },
                            }
                        }
                    ],
                },
                {
                    'id' : 3,
                    'name' : 'section',
                    'options' : {
                        'ct_parent': 0,
                        'ct_id': 1,
                        'original' : {
                                'background' : '#515151',
                            },
                        'hover' : {
                                'background' : '#333333',
                            },
                        'media' : [
                            {   
                                'size' : "748px",
                                'original' : {
                                    'background' : '#929292',
                                },
                            },
                        ]
                        },
                    'children': [
                        { 
                            'id' : 4,
                            'name' : 'headline',
                            'options' : {
                                'ct_parent': 3,
                                'ct_id': 4,
                                'original' : {
                                    'tag' : 'h3',
                                    'text' : 'I am a headline!',
                                    
                                },
                            }
                        },
                    ]
                },
            ]
        }

    // Tree
    $scope.componentsTree = [
        {
            'id' : 0,
            'name' : 'root',
            'depth' : 0
        }
    ];


    /**
     * Recursively find component in Components Tree by ID
     * and pass it to callback function
     * 
     * @since 0.1
     */

    $scope.findComponentItem = function(node, id, callback, variable) {

        var returnVal;

        if ($scope.log) {
            //console.log("findComponentItem()", id);
        }

        isBreak = false;

        // if root
        if ( id == 0 ) {
            returnVal = callback(id, $scope.componentsTree, variable);
            if(typeof(returnVal) === 'undefined')
                return false;
            else
                return returnVal;
        }
        
        angular.forEach(node, function(item) {

            if ( !isBreak ) {
                if ( item.id == id ) {
                    // do something if find
                    returnVal = callback(id, item, variable);
                    // stop the loop
                    isBreak = true;
                } 
                else {
                    // go deeper in Components Tree
                    if ( item.children ) {
                        returnVal = $scope.findComponentItem(item.children, id, callback, variable);
                    }
                }
            }

        });

        if(typeof(returnVal) !== 'undefined')
            return returnVal;
    };


    /**
     * Finds if a component has a parent, other than the body
     * 
     * @since 0.3.4
     * @author Gagan Goraya
     */

    $scope.hasParent = function(id) {
        return $scope.findParentComponentItem($scope.componentsTree, id,  function( val ) {return val;} ) > 0;
    };

    /**
     * Recursively find a parent of component in Components Tree
     * and pass it to callback function
     * 
     * @since 0.1
     * @author Ilya K.
     */

    $scope.findParentComponentItem = function(node, id, callback, variable) {

        var returnVal;

        if ($scope.log) {
            //console.log("findParentComponentItem", id);
        }

        isBreak = false;

        angular.forEach(node.children, function(item) {

            if ( !isBreak ) {
                if ( item.id == id ) {
                    // do something if find
                    returnVal = callback(item.options['ct_parent'], node, id, variable);        
                    // stop the loop
                    isBreak = true;
                } 
                else {
                    // go deeper in Components Tree
                    if ( item.children ) {
                        returnVal = $scope.findParentComponentItem(item, id, callback, variable);
                    }
                }
            }

        });

        if(typeof(returnVal) !== 'undefined')
            return returnVal;
    };


    /**
     * Remove component from Components Tree
     * 
     * @since 0.1
     * @author Ilya K.
     */

    $scope.removeComponentFromTree = function(key, item, idToRemove, component) {

        if ($scope.log) {
            console.log("removeComponentFromTree()", idToRemove);
        }

        // fix for removing link from span
        if (component && component.removeName == "ct_span" && component.parentName == "ct_link") {
            
            item.options.ct_content = 
            item.options.ct_content.replace("<span id=\"ct-placeholder-"+component.parentId+"\"></span>",
                                            "<span id=\"ct-placeholder-"+component.removeId+"\"></span>");
        }

        angular.forEach(item.children, function(child, id){
            if ( child.id == idToRemove ) {
                item.children.splice(id, 1);
                $scope.newComponentKey = id;
            }
        })

        // remove children property if there is no more children left
        if ( item.children.length == 0 ) {
            delete item.children;
        }
        
        if ( component && component.removeName == "ct_span" && component.parentName == "ct_link" ) {
            $scope.rebuildDOM(item.id);
        }
    }


    /**
     * Insert new component to given Components Tree item
     * 
     * @since 0.1
     * @author Ilya K.
     */

    $scope.insertComponentToTree = function(key, parent, component, componentFromParent) {

        if ($scope.log) {
            console.log("insertComponentToTree()", key, parent, component );
        }

        if ( typeof component !== 'object' ) {
            component = componentFromParent;
        }
        
        var element = $scope.getComponentById(key),
            nestable = element.attr("is-nestable");
        
        // look for parent if component not nestable and this is not a column, span or link over the span
        if ( !nestable && component.name != "ct_column" && component.name != "ct_span" && 
             !(component.name == "ct_link" && component.currentName == "ct_span") ) {       
            
            $scope.findParentComponentItem($scope.componentsTree, key, $scope.insertComponentToTree, component);
            return;
        }
        
        // create empty children object if not exist
        if ( !parent.children ) {
            parent.children = [];
        }

        var child = {
            'id': component.id,
            'name': component.name,
            'options': {
                'ct_id': component.id,
                'ct_parent': key,
                'selector': component.name + "_" + component.id + "_post_" + CtBuilderAjax.postId,
                'original': {}
            },
        }
        
        if ( component.isShortcode ) {
            child.options['ct_shortcode'] = "true";
        }

        if ( component.isWidget ) {
            child.options['ct_widget'] = "true";
        }

        // link and span fix
        if ( component.name == "ct_link" && component.currentName == "ct_span" ) {
            
            parent.options.ct_content = 
            parent.options.ct_content.replace(  "<span id=\"ct-placeholder-"+component.currentId+"\"></span>",
                                                "<span id=\"ct-placeholder-"+component.id+"\"></span>")
        }

        // add columns number for columns component
        if ( component.name == "ct_columns" ) {
            $scope.columns[component.id] = 1;
        } 
        
        var depth = $scope.calculateDepth(component, parent);

        // apply component depth if defined
        if ( depth ) {
            child.depth = depth;
        }

        // check if index specified
        if ( component.index ) {
            $scope.idToInsert = component.index;
        }

        // paste new child
        if ( $scope.idToInsert >= 0 ) {
            parent.children.splice($scope.idToInsert, 0, child);
            $scope.idToInsert = -1;
        } else {
            parent.children.push(child);
        }

        $scope.updateDOMTreeNavigator(component.id);
    }


    /**
     * Insert a component to a parent of given item
     * 
     * @since 0.1
     */

    $scope.insertComponentToParent = function(key, item, component) {

        $scope.findComponentItem($scope.componentsTree.children, item.options.ct_parent, $scope.insertComponentToTree, component);
    }


    /**
     * Insert a component to a grand parent of given item
     * 
     * @since 0.1.6
     */

    $scope.insertComponentToGrandParent = function(key, item, component) {

        $scope.findParentComponentItem($scope.componentsTree, item.options.ct_parent, $scope.insertComponentToTree, component);
    }


    /**
     * helper function to return a component item from the tree
     * 
     * @since 1.2.0
     */

    $scope.getComponentItem = function(key, item, variable) {
        return item;
    }

    /**
     * Paste existing component to Tree (callback in componentsReorder() and wrapWithComponent())
     * 
     * @since 0.1.3
     */

    $scope.pasteComponentToTree = function(key, parent, id) {

        if ($scope.log) {
            console.log("pasteComponentToTree()", key, parent, id, $scope.componentBuffer);
        }

        if(jQuery('body').hasClass('ct_inner') && (parseInt(key) === 0 || parseInt(key) > 1000)) {
            if(jQuery('.ct-inner-content.ct-component').length > 0) {
                key = parseInt(jQuery('.ct-inner-content.ct-component').attr('ng-attr-component-id'));
                parent = $scope.findComponentItem($scope.componentsTree.children, key, $scope.getComponentItem);
                $scope.componentBuffer.options.ct_parent = key;
            }
        } 

        if ( parent.name == 'ct_inner_content' && (CtBuilderAjax['query'] && CtBuilderAjax['query']['post_type'] && CtBuilderAjax['query']['post_type'] === 'ct_template') && !jQuery('body').hasClass('ct_inner') ) {
            // paste it next to the ct_inner_content

            var lastparent = parent;

            // avoid nesting inside ct_inner_content, rather go for its parent
            key = parseInt(parent.options.ct_parent);
            parent = $scope.findComponentItem($scope.componentsTree.children, key, $scope.getComponentItem);
            $scope.componentBuffer.options.ct_parent = key;

            // paste it next to the ct_inner_content

            $scope.newComponentKey = _.indexOf(parent.children, lastparent) + 1;

        }

        if ( !$scope.componentBuffer ) {
            return false;
        }



        if ( parent.name == "ct_columns" ) {

            var columnsNumber = parent.children.length;

            // check columns number
            if ( columnsNumber == 12 ) {
                alert("Max number of columns is 12");
                $scope.componentBuffer = false;
                return false;
            }
        }
        
        $scope.updateComponentDepth($scope.componentBuffer, parent);
        
        // check if parent already have children
        if ( !parent.children ) {
            
            parent.children = [];
            parent.children.push($scope.componentBuffer);
        } 
        else {

            if ( $scope.newComponentKey >= 0 ) {
                parent.children.splice($scope.newComponentKey, 0, $scope.componentBuffer);
                $scope.newComponentKey = -1;
            } else {
                parent.children.push($scope.componentBuffer);
            }
        }

        // update columns widths
        if ( $scope.componentBuffer.name == "ct_column" && !$scope.reorderSameParent ) {

            newColumnWidth = parseFloat($scope.componentBuffer.options.original.width);
            // change all columns
            angular.forEach(parent.children, function(column) {
                // get width value
                columnWidth = parseFloat(column.options.original.width);
                // calculate new value
                columnWidth = (columnWidth / ((100 + newColumnWidth) / 100)).toFixed(2);
                // update width
                $scope.setOptionModel("width", columnWidth, column.id, "ct_column");
                //column.options.original.width = columnWidth + "%";
            });

            // make sure sum is always 100
            $scope.checkColumnsWidthSum(parent.children);
        }

        $scope.unsavedChanges();
    }
    

    /**
     * Calculate new element depth based on its parent depth
     * 
     * @since 0.1.7
     */

    $scope.calculateDepth = function(component, parent) {

        var depth = false;

        // Columns
        if ( component.name == 'ct_column' && parent.name == 'ct_columns' ) {
            depth = parseInt(parent.depth);
        }
        if ( component.name == 'ct_columns' ) {
            depth = parseInt(parent.depth) + 1;
        }

        // Div Blocks
        if ( component.name == 'ct_div_block' ) {
            depth = parseInt(parent.depth) + 1;
        }
        if ( component.name == 'ct_div_block' && parent.name == 'ct_column' ) {
            depth = parseInt(parent.depth);
        }

        // Link Wrappers
        if ( component.name == 'ct_link' ) {
            depth = parseInt(parent.depth) + 1;
        }
        if ( component.name == 'ct_link' && parent.name == 'ct_column' ) {
            depth = parseInt(parent.depth);
        }

        // Sections
        if ( component.name == 'ct_section' ) {
            depth = parseInt(parent.depth) + 1;
        }
        if ( component.name == 'ct_section' && parent.name == 'ct_column' ) {
            depth = parseInt(parent.depth);
        }

        // Inner Content
        if ( component.name == 'ct_inner_content' ) {
            depth = parseInt(parent.depth) + 1;
        }

        return depth;
    }


    /**
     * Update component depth values
     * 
     * @since 0.1.7
     * @author Ilya K.
     */

    $scope.updateComponentDepth = function(component, parent) {

        var depth = $scope.calculateDepth(component, parent);

        // apply component depth if defined
        if ( depth ) {
            component.depth = depth;
        }

        // update children
        if (component.children) {
            
            angular.forEach( component.children, function(child) {
                $scope.updateComponentDepth(child,component);
            });
        }
    }


    /**
     * Update copied components IDs and selectors
     * 
     * @since 0.1.6
     * @author Ilya K.
     */

    $scope.updateNewComponentIds = function(item, parent) {

        if ($scope.log) {
            console.log("updateNewComponentIds()", item, parent);
        }

        // TODO: update only selectors that was not changed by user
        item.options.selector = item.name + "_" + $scope.component.id + "_post_" + CtBuilderAjax.postId;

        // update placeholders
        if ( parent.options && parent.options.ct_content ) {
            parent.options.ct_content = 
            parent.options.ct_content.replace(  "<span id=\"ct-placeholder-"+item.id+"\"></span>",
                                                "<span id=\"ct-placeholder-temporary-"+$scope.component.id+"\"></span>")
        }

        // update ids
        item.id                 = $scope.component.id;
        item.options.ct_id      = $scope.component.id;
        item.options.ct_parent  = parent.id;

        // update children
        if (item.children) {
            angular.forEach( item.children, function(child) {
                $scope.component.id++;
                $scope.updateNewComponentIds(child,item);
            });
        }

        if ( item.options && item.options.ct_content ) {
            // remove placeholders for not existing children
            item.options.ct_content = item.options.ct_content.replace(new RegExp('<span id="ct-placeholder-[0-9]+"></span>',"g"),"")
            // turn temporary placeholders back to normal
            item.options.ct_content = item.options.ct_content.replace(new RegExp('ct-placeholder-temporary-',"g"),"ct-placeholder-")
        }
    }


    /**
     * Copy component tree node
     * 
     * @since 0.1.6
     * @author Ilya K.
     */

    $scope.copyComponentTreeNode = function(key, item, idToCopy) {

        angular.forEach( item.children, function(child, key) {

            if ( child.id == idToCopy ) {

                // process columns
                if ( child.name == "ct_column" && !$scope.reorderSameParent ) {

                    columnsNumber = item.children.length;

                    // check columns number
                    if ( columnsNumber == 12 ) {
                        alert("Max number of columns is 12");
                        $scope.componentBuffer = false;
                        return false;
                    }
                }
                
                // save in scope 
                $scope.componentBuffer = angular.copy(child);

                // update new element ids and selector
                $scope.updateNewComponentIds($scope.componentBuffer, item);

                // save id
                $scope.newComponentKey = key+1;
            }
        })
    }


    /**
     * Cut components from Tree (callback in componentsReorder())
     * 
     * @since 0.1.3
     * @author Ilya K.
     */

$scope.cutComponentFromTree = function(key, item, idToRemove) {

        if ($scope.log) {
            console.log("cutComponentFromTree()", idToRemove);
        }

        $scope.idToInsert = -1;

        angular.forEach( item.children, function(child, index){
            
            if ( child.id == idToRemove ) {

                // process columns
                if ( child.name == "ct_column" && !$scope.reorderSameParent ) {

                    newColumnsNumber    = item.children.length - 1;
                    freeSpace           = parseFloat(child.options.original.width);

                    angular.forEach(item.children, function(column) {

                        if (column.id != idToRemove) {

                            // get width value
                            columnWidth = parseFloat(column.options.original.width);
                            // calculate new value
                            columnWidth = (columnWidth + (freeSpace / newColumnsNumber) ).toFixed(2);
                            // update scope
                            //column.options.original.width = columnWidth + "%";
                            $scope.setOptionModel("width", columnWidth, column.id, "ct_column");
                        };
                    });
                }

                // update parent ID
                child.options.ct_parent = $scope.componentInsertId;

                // save in scope 
                $scope.componentBuffer = angular.copy(child);

                // remove from tree
                item.children.splice(index, 1);

                // remove children object if no children left
                if (item.children.length == 0) {
                    delete item.children;
                }

                // save id
                $scope.idToInsert = index;
            }
        })
    }


    /**
     * update component's parent from Tree (callback in componentsReorder())
     * 
     * @since 1.2.0
     * @author Gagan Goraya.
     */

    $scope.updateComponentParentId = function(key, item) {

        if ($scope.log) {
            console.log("updateComponentParentId()", key);
        }
        
        item.options.ct_parent = parseInt($scope.componentInsertId);
        
    }

    /**
     * Reorder components in Tree
     * 
     * @since 0.3.1
     */

    $scope.componentsReorder = function(item, index, startParentId, endParentId) {

        if ($scope.log) {
            console.log(item, index, startParentId, endParentId);
        }

        var attr        = (item[0].attributes['ng-attr-tree-id']) ? 'ng-attr-tree-id' :     // when dragging in DOM tree
                                                                    'ng-attr-component-id', // when dragging component
            componentId = item[0].attributes[attr].value,
            parent      = item.parent();
        
        if(jQuery('body').hasClass('ct_inner') && (parseInt(endParentId) === 0 || parseInt(endParentId) > 1000 || typeof(endParentId) === 'undefined')) {
            console.log('in');
            //reassign the component to the ct_inner_content
            if(jQuery('.ct-inner-content.ct-component').length > 0) {
                endParentId = parseInt(jQuery('.ct-inner-content.ct-component').attr('ng-attr-component-id'));
               
            }
        }

        // save parent ID in scope
        $scope.componentInsertId = endParentId;

        // save order in scope
        // $scope.oldComponentKey = 0;
        $scope.newComponentKey = index;
        $scope.reorderSameParent = ( startParentId == endParentId );

        // cut component from tree
        $scope.findParentComponentItem($scope.componentsTree, componentId, $scope.cutComponentFromTree);

        // find component to paste
        $scope.findComponentItem($scope.componentsTree.children, $scope.componentInsertId, $scope.pasteComponentToTree);

        var endParent = $scope.getComponentById(endParentId);

        //console.log(endParent,endParent.hasClass("ct-columns"));

        if (endParent.hasClass("ct-columns")) {
            //$scope.rebuildDOM(endParentId);
            $scope.columns[startParentId]--;
            $scope.columns[endParentId]++;
        }


        // update the parent of the pasted item
        $scope.findComponentItem($scope.componentsTree.children, componentId, $scope.updateComponentParentId);

        $scope.rebuildDOM(componentId, true);
        $scope.updateDOMTreeNavigator(componentId, true);

        // disable undo option
        $scope.cancelDeleteUndo();
    }


    /**
     * Check if we have selected or active components and wrap it with new component
     * 
     * @since 0.2.4
     */

    $scope.wrapWith = function(wrapperComponentName) {

        if ( $scope.isSelectableEnabled && $scope.isDOMNodesSelected ) {
            $scope.wrapSelectedComponentWith(wrapperComponentName);
            $scope.isDOMNodesSelected = false;
        }
        else {
            $scope.wrapComponentWith(wrapperComponentName);
        }
    }

    
    /**
     * Wrap component with new component
     * 
     * @since 0.1.5
     */

    $scope.wrapComponentWith = function(wrapperComponentName, componentId, parentId) {
        
        // component id to cut
        if (undefined === componentId) {
            componentId = $scope.component.active.id;
        }

        if (undefined === parentId) {
            parentId = $scope.component.active.parent.id;
        }

        if ($scope.log) {
            console.log("wrapComponentWith()", wrapperComponentName, componentId, parentId);
        }
        
        var newComponentId = $scope.component.id;
        
        newComponent = {
            id : newComponentId, 
            name : wrapperComponentName,
            currentId : $scope.component.active.id,
            currentName : $scope.component.active.name
        }

        // set component id to insert
        $scope.componentInsertId = newComponent.id;

        // cut component
        $scope.findParentComponentItem($scope.componentsTree, componentId, $scope.cutComponentFromTree);
        $scope.removeComponentFromDOM(componentId);
        $scope.removeDOMTreeNavigatorNode(componentId);
        
        // insert new component to the parent of cutted component
        $scope.findComponentItem($scope.componentsTree.children, parentId, $scope.insertComponentToTree, newComponent);

        // find component to paste
        $scope.findComponentItem($scope.componentsTree.children, $scope.componentInsertId, $scope.pasteComponentToTree);

        // update current active parent
        $scope.findParentComponentItem($scope.componentsTree, componentId, $scope.updateCurrentActiveParent);
        
        if (newComponent.currentName == "ct_span") {
            $scope.rebuildDOM(parentId);
            //$scope.activateComponent(parentId);
            $scope.updateDOMTreeNavigator(parentId);
        }
        else {
            $scope.rebuildDOM(newComponent.id);
            $scope.activateComponent(newComponent.id);
            if(parentId > 0)
                $scope.updateDOMTreeNavigator(parentId);
            else
                $scope.updateDOMTreeNavigator();
            $scope.toggleNode(newComponent.id);
        }

        // disable undo option
        $scope.cancelDeleteUndo();

        return newComponentId;
    }


    /**
     * Wrap selected component(s) with new component
     *
     * @since 0.2.4
     */
    
    $scope.wrapSelectedComponentWith = function(wrapperComponentName) {

        var parent      = jQuery("#ct-dom-tree").find('.ct-selected-dom-node').first().parent().parent(),
            parentId    = parent.attr('ng-attr-tree-id'),
            nodes       = parent.children('.ct-dom-tree-node').has('.ct-selected-dom-node'),
            ids         = [];

        // get top level selected component ids
        nodes.each(function(){
            ids.push(jQuery(this).attr('ng-attr-tree-id'));
        });

        // create wrapper component
        newComponent = {
            id : $scope.component.id,
            name : wrapperComponentName,
            currentId : parentId,
            //currentName : $scope.component.active.name
        }

        // set component id to insert
        $scope.componentInsertId = newComponent.id;

        // insert new component to the parent of cutted component
        $scope.findComponentItem($scope.componentsTree.children, parentId, $scope.insertComponentToTree, newComponent);

        for (var i = ids.length - 1, id; id = ids[i], i >= 0; i--) {
            
            // cut component
            $scope.findParentComponentItem($scope.componentsTree, id, $scope.cutComponentFromTree);
            $scope.removeComponentFromDOM(id);

            // find component to paste
            $scope.findComponentItem($scope.componentsTree.children, $scope.componentInsertId, $scope.pasteComponentToTree);
        }

        // disable undo option
        $scope.cancelDeleteUndo();

        $scope.rebuildDOM(parentId);
        //$scope.updateDOMTreeNavigator(parentId);
    }


    /**
     * Remove parent of currently active component
     *
     * @since 0.1.8
     */

    $scope.removeActiveParent = function() {

        if ($scope.log) {
            console.log("removeActiveParent");
        }

        // component id to cut
        componentId = $scope.component.active.id;
        parentId    = $scope.component.active.parent.id

        component = {
            removeId    : $scope.component.active.id,
            removeName  : $scope.component.active.name,
            parentId    : $scope.component.active.parent.id,
            parentName  : $scope.component.active.parent.name
        }

        // update active parent
        $scope.findParentComponentItem($scope.componentsTree, parentId, $scope.updateCurrentActiveParent);

        // set component id to insert
        $scope.componentInsertId = $scope.component.active.parent.id;

        // cut component
        $scope.findParentComponentItem($scope.componentsTree, componentId, $scope.cutComponentFromTree);
        $scope.removeComponentFromDOM(componentId);

        // remove parent
        $scope.findParentComponentItem($scope.componentsTree, parentId, $scope.removeComponentFromTree, component);
        $scope.removeComponentFromDOM(parentId);

        // paste component
        $scope.findComponentItem($scope.componentsTree.children, $scope.componentInsertId, $scope.pasteComponentToTree);

        // update current active parent
        $scope.findParentComponentItem($scope.componentsTree, componentId, $scope.updateCurrentActiveParent);

        // disable undo option
        $scope.cancelDeleteUndo();
        
        $scope.rebuildDOM(componentId);
        $scope.removeDOMTreeNavigatorNode(parentId);
        $scope.updateDOMTreeNavigator(componentId);
    }
    

    /**
     * Callback for AJAX call. Build DOM and other stuff
     *
     * @since 0.2.3
     * @author Ilya K.
     */

    $scope.builderInit = function(tree) {

        if ($scope.log) {
            console.log("builderInit()", tree);
        }

        $scope.showLoadingOverlay("builderInit()");

        // set scope tree to the one saved in WordPress
        $scope.componentsTree = tree;

        if (tree === false) {
            alert("Error occured while trying to build a page.");
            return;
        }
        
        // load post data via AJAX call
        if (CtBuilderAjax.ctTemplate) {
            $scope.loadTemplateData($scope.setTemplateData);
        }
		else {
			// update DOM
			if (tree.children) {
				$scope.buildComponentsFromTree(tree.children);
			}
		}

        // increment id
        $scope.component.id++;

        // show builder ui
        document.getElementById("ct-ui").style.display = "block";

        // some functions
        $scope.updateBreadcrumbs();
        $scope.updateDOMTreeNavigator();
        $scope.outputCSSOptions();

        $scope.hideLoadingOverlay("builderInit()");

        // if its inner content of a page  being edited

/*        if(jQuery('body').hasClass('ct_inner'))
            setTimeout(function() {
                $scope.disableOuterDivs();     
            }, 0);
     */      

        // auto save page revisions
        /*var autoSaveTimer = setInterval(function() {
            $scope.savePage(true);
        }, 1000 * 60 * 2);*/
    }


    /**
     * Recursively find if tree node has media styles
     * 
     * @since 0.4.0
     * @author Ilya K.
     */

    $scope.findMedia = function(node, mediaName) {

        var returnVal,
            isBreak = false;
        
        angular.forEach(node, function(item) {

            if ( !isBreak ) {
                if ( item.options['media'] && 
                     item.options['media'][mediaName] && 
                     item.options['media'][mediaName][$scope.currentState] ) {
                    // do something if find
                    returnVal = true;
                    // stop the loop
                    isBreak = true;
                } 
                else {
                    // go deeper in Components Tree
                    if ( item.children ) {
                        returnVal = $scope.findMedia(item.children, mediaName);
                    }
                }
            }

        });

        if(typeof(returnVal) !== 'undefined')
            return returnVal
    }


    /**
     * Recursively find components in Components Tree by name
     * and update tag name
     * 
     * @since 0.4.0
     * @author Ilya K.
     */

    $scope.updateTagsByName = function(id, node, variable) {

        angular.forEach(node.children, function(item) {

            if ( item.name == variable.from ) {
                // update tag
                $scope.updateTreeComponentTag(item.id, item, variable.to)
            } 

            // go deeper in Components Tree
            if ( item.children ) {
                $scope.updateTagsByName(item.id, item, variable);
            }

        });
    };


});