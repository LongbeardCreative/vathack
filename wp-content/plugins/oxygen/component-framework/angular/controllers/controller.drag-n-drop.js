CTFrontendBuilder.controller("ControllerDragnDrop", function($scope, $timeout, dragulaService) {

	var dragBottomBubble = null,
        expandTimeout = null,
        setExpandTrigger = 0;
    
    /**
     * Set options
     */
    
    dragulaService.options($scope, 'ct-dom-tree', {
        
        isContainer: function (el) {
            
            //console.log('isContainer', el, dragBottomBubble);
            if ( el.classList.contains('ct-draggable') && dragBottomBubble ) {
                return true;
            }
            else if ( el.classList.contains('ct-draggable') ) {
                dragBottomBubble = true;
                return false;
            }
            else {
                return false;
            }

        },

        moves: function(el, ctn, target) {

            dragBottomBubble = null;

            // jQuery closest is used to make sure that clicking on any child element of the .ct-handle such as text, icons initiates the drag as well
            return target.classList.contains('ct-draggable') || jQuery(target).closest('.ct-handle').length > 0; 

        },

        accepts: function (el, target, source, sibling) {
            
            var elId = parseInt(el.getAttribute('ng-attr-tree-id'));

            // collapse the child elements hierarchy
            if($scope.toggledNodes[elId])
                $scope.toggledNodes[elId] = false;


            // if the target is no more an expandable one, clear the expand timeout
            if(expandTimeout && parseInt(target.getAttribute('ng-attr-tree-id')) > 0 && parseInt(target.getAttribute('ng-attr-tree-id')) !== setExpandTrigger) {
                clearTimeout(expandTimeout);
                expandTimeout = false;
                setExpandTrigger = 0;

            }

            // Here decision is made to accept the drop inside a nestable container as a child or a sibling
            if(target.classList.contains('ct-accept-drops') && parseInt(target.getAttribute('ng-attr-tree-id')) > 0) {
                
                var targetId = parseInt(target.getAttribute('ng-attr-tree-id'));
                var targetAnchor = jQuery('#ct-dom-tree-node-'+targetId+' >.ct-dom-tree-node-anchor');
                
                // if the target node is not expanded, expand it.
                if(setExpandTrigger === 0 && !$scope.toggledNodes[targetId] && targetId !== parseInt(el.getAttribute('ng-attr-tree-id'))) {
                    
                    setExpandTrigger = targetId;

                    expandTimeout = setTimeout(function() {
                            targetAnchor.find('.ct-expand-butt').trigger('click');
                            setExpandTrigger = 0;
                        }, 1000);

                }

                // test if the drop is intended to be a child
                var targetX = jQuery(target).offset().left;
                var ghostX = jQuery('.gu-mirror').offset().left;
                if(ghostX - targetX < 16)
                    return false;
            }

            //console.log(el, target, source, sibling);
            //console.log(angular.element(target).closest('.ct-sortable-section').length);
            
            // don't allow to insert before anchor
            if( sibling && sibling.classList.contains('ct-dom-tree-node-anchor') ) {
                return false;
            }

            // don't allow to insert 'li' to any components but 'ul'
            if( el.classList.contains('ct-sortable-li') && !target.classList.contains('ct-sortable-ul') ) {
                return false;
            }

            // don't allow to insert any components to 'ul' but 'li'
            if( !el.classList.contains('ct-sortable-li') && target.classList.contains('ct-sortable-ul') ) {
                return false;
            }
            
            // don't allow to insert 'column' to any components but 'columns'
            if( el.classList.contains('ct-sortable-column') && !target.classList.contains('ct-sortable-columns') ) {
                return false;
            }

            // don't allow to insert any components to 'columns' but 'column'
            if( !el.classList.contains('ct-sortable-column') && target.classList.contains('ct-sortable-columns') ) {
                return false;
            }

            // don't allow to insert 'section' inside any other 'section'
            if( el.classList.contains('ct-sortable-section') && angular.element(target).closest('.ct-sortable-section').length > 0 ) {
                return false;
            }

            // don't allow to insert 'link wrapper' inside any other 'link wrapper'
            if( el.classList.contains('ct-sortable-link') && angular.element(target).closest('.ct-sortable-link').length > 0 ) {
                return false;
            }

            // don't allow to insert 'text link' inside any other 'link wrapper'
            if( el.classList.contains('ct-sortable-link-text') && angular.element(target).closest('.ct-sortable-link').length > 0 ) {
                return false;
            }

            if( target.classList.contains('gu-transit') || !target.classList.contains('ct-accept-drops')) {
                return false;
            }

            var componentId         = jQuery(el).attr("ng-attr-tree-id"),
                component           = $scope.getComponentById(componentId),
                targetComponentId   = jQuery(target).attr("ng-attr-tree-id"),
                targetComponent     = $scope.getComponentById(targetComponentId);

            // don't allow to insert any component with 'section' inside any other component inside 'section'
            if( jQuery(component).find(".ct-section").length > 0 && jQuery(targetComponent).closest(".ct-section").length > 0 ) {
               return false;
            }

            // don't allow to insert any component with 'link wraper/text link' inside any other component inside 'link wrapper'
            if( jQuery(component).find(".ct-link").length > 0 && jQuery(targetComponent).closest(".ct-link").length > 0 ) {
               return false;
            }
            if( jQuery(component).find(".ct-link-text").length > 0 && jQuery(targetComponent).closest(".ct-link").length > 0 ) {
               return false;
            }
            
            return true;
        },
        revertOnSpill: true,
        mirrorContainer: document.body

    });


    /**
     * Drop Event
     */
    
    $scope.$on('ct-dom-tree.drop', function (e, el, endParent, startParent) {
    	

        //console.log('drop', el, endParent, startParent);

        // fix offset
    	var newKey          = el.index() - 3,
            startParentId   = startParent[0].attributes['ng-attr-tree-id'].value,
            endParentId     = endParent[0].attributes['ng-attr-tree-id'].value;

        // make changes to Components Tree
        $scope.componentsReorder(el, newKey, startParentId, endParentId, startParent, endParent);

        // rebuild DOM based on new tree
        if ( el.hasClass('ct-sortable-navigator-column') ) {
            
            if ( startParentId == endParentId ) {
                $scope.rebuildDOM(startParentId);
                $scope.rebuildDOM(endParentId);
            } 
            else {
                $scope.rebuildDOM(startParentId);
            }
        }
        else {
            // rebuildDOM is already called in contoller.tree.js by this time
            // remove it to avoid double rebuild
            //var droppedItemId = el[0].attributes['ng-attr-tree-id'].value;
            //$scope.rebuildDOM(droppedItemId);
        }
        
        // expand new parent after drop
        jQuery(endParent).addClass("ct-dom-tree-node-expanded").removeClass("ct-dom-tree-no-children");

        // collapse old parent if no children left
        if ( jQuery(startParent).children().length <= 3 ) { // there is always 3 children: anchor, dashed vertical and horizontla
            jQuery(startParent).addClass("ct-dom-tree-no-children");
        }

    });


    /**
     * Drag end event
     */

    $scope.$on('ct-dom-tree.dragend', function (e, el) {
        
        dragBottomBubble = null;
    });

})