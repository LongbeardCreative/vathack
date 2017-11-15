CTFrontendBuilder.directive("ngBuilderWrap", function ($compile, $timeout, $interval) {
    
    return {
        restrict: 'A',
        link: function(scope, element) {

            /**
             * Some components shouldn't be a child of itslef or other components
             * i.e. section can't be a child of a section or any section children
             */
            scope.insertBeforeComponent = function(name, insertData) {

                var parent = insertData.activeComponent,
                    parentId;

                // search for component in parents
                while ( !parent.hasClass(name) && !parent.hasClass('ct-builder') ) {
                    parent = parent.parent();
                }

                parentId = parent[0].getAttribute('ng-attr-component-id');

                // found component in parents
                if (parentId != 0) {

                    insertData.index = parent.index() + 1;

                    // go level up from component
                    parent = jQuery(parent).parent().closest("[is-nestable]"); 
                    insertData.idToInsert = parent[0].getAttribute('ng-attr-component-id');

                    // activate new component
                    scope.activateComponent(insertData.idToInsert);
                    insertData.activeComponent = scope.getActiveComponent();
                }
            };

            scope.addComponents = function(first, second) {
                
                scope.addComponent(first);

                // Fix for inserting second after first and not inside of it.
                // Need a timeout to update a controller scope
                if ( first == "ct_columns") {
                    var timeout = $timeout(function() {
                        
                        // add first column
                        scope.addComponent(second, false, true);

                        $interval.cancel(timeout);
                    }, 0, false);


                    var timeout2 = $timeout(function() {

                        if ( first == "ct_columns") {
                            scope.addColumn(scope.component.active.id);
                            scope.columns[scope.component.active.id] = 2;
                        }
                        
                        $interval.cancel(timeout2);
                    }, 500, false);
                }
            };
            
            scope.addComponent = function(componentName, type, notActivate) {

                if(componentName === "ct_inner_content" && jQuery('div.ct-component.ct-inner-content').length > 0) {
                    alert('You cannot add more than one Inner Content component to a template.');
                    return;
                }

                if(componentName === "ct_inner_content")
                    scope.innerContentAdded = true;

                var parent = false;
                //console.log("addComponent", componentName);

                // set default options first
                scope.applyComponentDefaultOptions(scope.component.id, componentName);

                var insertData = {},
                    componentTemplate   = scope.getComponentTemplate(componentName, scope.component.id, type);

                insertData.idToInsert          = scope.component.active.id;
                insertData.activeComponent     = scope.getActiveComponent();
                insertData.index               = false;
                
                if (!insertData.activeComponent) {

                    insertData.activeComponent = document.getElementsByClassName("ct-builder"); 
                    // create jqLite element
                    insertData.activeComponent = angular.element(insertData.activeComponent);

                    insertData.idToInsert = 0;
                }

                // section can't be a child of a section
                if ( componentName == "ct_section" ) {
                    scope.insertBeforeComponent("ct_section", insertData);
                }

                // link wrapper or text link can't be a child of a link wrapper
                if ( componentName == "ct_link" || componentName == "ct_link_text") {
                    scope.insertBeforeComponent("ct_link", insertData);
                }

                // while editing an outer template, do not nest anything under ct_inner_content
                var body = document.getElementsByTagName("BODY")[0];
                if ( scope.component.active.name == "ct_inner_content" && !body.classList.contains('ct_inner')) {
                    scope.insertBeforeComponent("ct_inner_content", insertData);
                }

                // check if we add a column into a columns
                if ( scope.component.active.name == "ct_columns" && componentName == "ct_column" ) {
                    
                    var innerWrap = scope.getInnerWrap(insertData.activeComponent);
                    innerWrap.append(componentTemplate);
                    
                    scope.cleanInsert(insertData.activeComponent);

                    // insert to Components Tree active element
                    callback = scope.insertComponentToTree;

                    // update model
                    scope.setOptionModel("width", "100", scope.component.id, "ct_column");
                } 
                // ul
                else if ( scope.component.active.name == "ct_ul" && componentName != "ct_li" ) {
                    
                    parent = insertData.activeComponent.parent();

                    // insert to Components Tree active element parent
                    callback = scope.insertComponentToParent;

                    // save current component index
                    insertData.index = insertData.activeComponent.index() + 1;

                    // append to parent
                    scope.cleanInsert(componentTemplate, parent, insertData.index);
                } 
                // li
                else if ( scope.component.active.name == "ct_li" && componentName != "ct_li" ) {
                    
                    parent = insertData.activeComponent.parent().parent();
                    
                    callback = scope.insertComponentToGrandParent;

                    // save current component index
                    insertData.index = insertData.activeComponent.parent().index() + 1;

                    scope.cleanInsert(componentTemplate, parent, insertData.index);
                } 
                // handle nestable components
                else if ( insertData.activeComponent[0].attributes['is-nestable'] ) {

                    // append to element

                    if(jQuery('body').hasClass('ct_inner') && (parseInt(insertData.idToInsert) === 0 || parseInt(insertData.idToInsert) > 1000)) {
                        //reassign the component to the ct_inner_content
                        
                        if(jQuery('.ct-inner-content.ct-component').length > 0) {
                            
                            insertData.idToInsert = parseInt(jQuery('.ct-inner-content.ct-component').attr('ng-attr-component-id'));
                            insertData.index = false;
                            scope.activateComponent(insertData.idToInsert);
                            insertData.activeComponent = scope.getActiveComponent();
                            //parent = insertData.activeComponent;
                            callback = scope.insertComponentToTree;
                        }
                    }

                    var innerWrap = scope.getInnerWrap(insertData.activeComponent);
                    scope.cleanInsert(componentTemplate, innerWrap, insertData.index);

                    // insert to Components Tree active element
                    callback = scope.insertComponentToTree;
                }
                // not nestable elements
                else {
                    
                    parent = insertData.activeComponent.parent();
                    // insert to Components Tree active element parent
                    callback = scope.insertComponentToParent;

                    // find first nestable parent
                    if ( ! parent[0].attributes['is-nestable'] && ! parent.hasClass('ct-inner-wrap') ) {
                        while ( ! parent[0].attributes['is-nestable'] && ! parent.hasClass('ct-inner-wrap') ) {
                            parent = parent.parent();
                        }
                        // insert to Components Tree active element grand parent
                        callback = scope.insertComponentToGrandParent;
                    }

                    // get index to insert after current component
                    insertData.index = insertData.activeComponent.index() + 1;

                    // append to parent
                    scope.cleanInsert(componentTemplate, parent, insertData.index);
                }

                if ( type == "shortcode" ) {
                    var isShortcode = true;
                }

                if ( type == "widget" ) {
                    var isWidget = true;
                }

                var component = {
                    id : scope.component.id,
                    name : componentName,
                    isShortcode : isShortcode,
                    isWidget: isWidget,
                    index: insertData.index,
                };
                
                // insert to Components Tree
                scope.findComponentItem(scope.componentsTree.children, insertData.idToInsert, callback, component);

                // activate component
                if (!notActivate) {
                    scope.activateComponent(scope.component.id, componentName);
                }

                // Lets keep the newly added component in memory, this will help to distinguish these from the ones loaded from db
                scope.justaddedcomponents = scope.justaddedcomponents || [];
                scope.justaddedcomponents.push(scope.component.id);

                // update sortable
                //jQuery('.ct-sortable').sortable('update');

                // update options
                scope.setOption(scope.component.id, componentName);
               
                // rebuild HTML component
                if ( componentName == "ct_html") {
                    scope.rebuildDOM(scope.component.id);
                }

                // increment id
                scope.component.id++;

                scope.closeAllTabs();
                scope.cancelDeleteUndo();
                scope.unsavedChanges();
            };
        }
    };
});


/**
 * Make HTML5 "contenteditable" support ng-module
 * To enforce plain text mode, use attr data-plaintext="true"
 */
CTFrontendBuilder.directive("contenteditable", function($timeout,$interval) {

    return {
        restrict: "A",
        require: "ngModel",
        link: function(scope, element, attrs, ngModel) {

            element.unbind("paste input");

            function read() {
                ngModel.$setViewValue(element.html());
            }

            function getCaretPosition() {
                if(window.getSelection) {
                    selection = window.getSelection();
                    if(selection.rangeCount) {
                        range = selection.getRangeAt(0);
                        return(element.text().length-range.endOffset);
                    }
                }
            }

            function setCaretPosition(caretOffsetRight) {
                var range, selection;
                
                

                if(document.createRange) {
                    range = document.createRange();
                    if(element.get(0) && element.get(0).childNodes[0]) {
                        var offset = element.text().length;
                        
                        range.setStart(element.get(0), 0);
                        
                        if(caretOffsetRight > 0 && caretOffsetRight <= offset) {
                            offset -= caretOffsetRight;
                        }
                        range.setEnd(element.get(0).childNodes[0], offset);
                        range.collapse(false);
                        selection = window.getSelection();
                        selection.removeAllRanges();
                        selection.addRange(range);
                        
                    }
                    
                }
                else if(document.selection) {
                    range = document.body.createTextRange();
                    if(element.get(0) && element.get(0).childNodes[0]) {
                        var offset = element.text().length;
                            
                        range.setStart(element.get(0), 0);
                        
                        if(caretOffsetRight > 0 && caretOffsetRight <= offset) {
                            offset -= caretOffsetRight;
                        }
                        range.setEnd(element.get(0).childNodes[0], offset);
                        range.collapse(false);
                        range.select();
                    }
                }
            }

            ngModel.$render = function() {

                element.html(ngModel.$viewValue || "");

                // check for "ct_span" components inside content
                if (ngModel.$viewValue && ngModel.$viewValue.indexOf("ct-placeholder") >= 0) {
                    
                    var html = angular.element("<span>"+ngModel.$viewValue+"</span>");

                    // loop all child nodes
                    angular.forEach(html.find("*"), function(child) {

                        var childNode   = angular.element(child),
                            idAttr      = childNode.attr('id');

                        if ( idAttr !== undefined ) {
                            var componentId = idAttr.replace("ct-placeholder-", "");
                            // build ct_span to replace placeholder
                            scope.rebuildDOM(componentId);
                        }
                    });
                }
            };

            // save element content
            element.bind("input", function() {
                scope.$apply(read);

                // if it is plaintext mode, replace any html formatting
                if(typeof(attrs['plaintext']) !== 'undefined' && attrs['plaintext'] === "true") {
                    var caretPosition = getCaretPosition();
                    element.html(jQuery('<span>').html(element.html()).text());
                    setCaretPosition(caretPosition);
                }

                // if default text is provided and current text is blank. populate with defaulttext
                if(element.html().trim() === '' && typeof(attrs['defaulttext']) !== 'undefined' && attrs['defaulttext'].trim() !== '') {
                    element.text(attrs['defaulttext']);
                }

                // timeout for angular
                var timeout = $timeout(function() {
                    scope.setOption(scope.component.active.id, scope.component.active.name, 'ct_content');
                    $interval.cancel(timeout);
                }, 20, false);
            })

            // trick to update content after paste event performed
            element.bind("paste", function() {
                setTimeout(function() {element.trigger("input");}, 0);
            });
            
            // if data-plaintext is NOT set to "true"
            if(typeof(attrs['plaintext']) === 'undefined' || attrs['plaintext'] !== "true") {
                // enable content editing on double click
                element.bind("dblclick", function() {

                    if ( element.attr("ng-attr-template-tag") ) {
                        scope.activateActionTab("templateTags");
                    }
                    else {
                        scope.enableContentEdit();
                    }
                    scope.$apply();
                });

                // format as <p> on enter/return press
                if ( element[0].attributes['ng-attr-paragraph'] ) {
                    element.bind('keypress', function(e){
                        if ( e.keyCode == 13 ) { 
                            document.execCommand('formatBlock', false, 'p');
                        }
                    });
                }
                else {
                    // format as <br/>
                    element.bind('keypress', function(e){
                        if ( e.keyCode == 13 ) { 
                            document.execCommand('insertHTML', false, '<br><br>');
                            return false;
                        }
                    });
                }
            } 
            // else if it is plaintext mode
            else {
                // we do not need line breaks
                element.bind('keypress', function(e){
                    
                    if ( e.keyCode == 13 ) { 
                        element.blur();
                        return false;
                    }
                });
            }
            
            // if ngBlur is provided
            if(typeof(attrs['ngBlur']) !== 'undefined' || attrs['ngBlur'] !== "") {
                element.bind('blur', function() {
                    scope.$apply(attrs.ngBlur);
                })
            }

        }
    };
});

/**
 * Attach actions to content editor buttons
 */
CTFrontendBuilder.directive('ctEditButton', function($timeout,$interval) {

    return {
        link:function(scope,element,attrs) {

            element.bind('mousedown', function(event) {

                event.preventDefault();
                
                var role = attrs.ngEditRole;
                
                switch(role) {
                    case 'link':
                        var sLnk=prompt('Write the URL','http:\/\/');
                        if(sLnk&&sLnk!=''){
                            document.execCommand('createlink', false, sLnk);
                        }
                    case 'p':
                        document.execCommand('formatBlock', false, role);
                        break;
                    default:
                        document.execCommand(role, false, null);
                        break;
                }
                scope.$apply();
                // timeout for angular
                var timeout = $timeout(function() {
                    scope.setOption(scope.component.active.id, scope.component.active.name, 'ct_content');
                    $interval.cancel(timeout);
                }, 0, false);
            })
        }
    }
})


/**
 * Fix for Webkit to force CSS apply on input change
 * 
 */

CTFrontendBuilder.directive("ngModel",function($timeout, $interval){
    return {
        restrict: 'A',
        priority: -1, // give it lower priority than built-in ng-model
        link: function(scope, element, attr) {
            scope.$watch(attr.ngModel,function(value){
                if (element.is(':radio')){
                    element.next().addClass('ct-update-css');
                    element.next().removeClass('ct-update-css');
                }
            });
        }
      }
});


/**
 * Float editor panel. Use jQuery Draggable for user custom drag
 *
 */

CTFrontendBuilder.directive('ctFloatEditor', function() {

    return {
        link:function(scope,el,attrs) {

            var toolBar                 = angular.element("#ct-toolbar"),
                toolBarHeight           = toolBar.outerHeight(),
                
                activeComponent         = scope.getActiveComponent(),
                activeComponentHeight   = activeComponent.outerHeight(),
                
                windowPosition          = activeComponent[0].getBoundingClientRect(),
                documentPosition        = activeComponent.offset();


            // init jQuery UI draggable
            el.draggable({ 
                handle: ".ct-draggable-handle",
                containment: "document"
            })

            var draggableHeight = el.outerHeight(),
                draggableWidth  = el.outerWidth(),
                yOffset = xOffset = 0;

            if (el.is(".ct-choose-selector")) {
                yOffset = 110;
            }
            else 
            // calcualte Y offset
            if ( toolBarHeight + draggableHeight > windowPosition.top ) {
                yOffset = activeComponentHeight + 8;
            }
            else {
                yOffset = -(draggableHeight + 8)
            }

            var domTreeWidth = (scope.showSidePanel) ? 330 : 0;

            // calcualte X offset
            if ( windowPosition.left + draggableWidth > document.documentElement.clientWidth - 20 - domTreeWidth) {
                xOffset = (document.documentElement.clientWidth - 20 - domTreeWidth) - (windowPosition.left + draggableWidth);
            }

            // place draggable
            el.css({
                'top' : documentPosition.top + yOffset, 
                'left' : documentPosition.left + xOffset,
            })
        }
    }
})


/**
 * Helps an input text field gain focus based on a condition
 * 
 * @since 0.3.3
 * @author Gagan Goraya
 *
 * usage: <input type="text" focus-me="booleanValue" />
 */
 
CTFrontendBuilder.directive('focusMe', function($timeout) {
  return {
    scope: { trigger: '=focusMe' },
    link: function(scope, element) {
      scope.$watch('trigger', function(value) {
        if(value === true) { 
          $timeout(function() {
            element[0].focus();
            scope.trigger = false;
          });
        }
      });
    }
  };
});


CTFrontendBuilder.directive('fileModel', ['$parse', function ($parse) {
    return {
        restrict: 'A',
        link: function(scope, element, attrs) {
            var model = $parse(attrs.fileModel);
            var modelSetter = model.assign;
            
            element.bind('change', function(){
                scope.$apply(function(){
                    modelSetter(scope, element[0].files[0]);
                });
            });
        }
    };
}]);


/**
 * Not used anywhere, just ideas
 */

CTFrontendBuilder.directive('ctResizable', function() {

    return {
        link:function(scope,el,attrs) {

            el.resizable();
        }
    }
})
CTFrontendBuilder.directive('ctUnits', function($compile) {

    return {
        link:function(scope,el,attrs) {

            el.after($compile('<div class="original"><input id="value" type="number" step="1"/><input id="units" type="hidden" name="units" value="px"/></div>')(scope));
        }
    }
})

