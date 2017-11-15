/**
 * All columns DOM manipulations here
 * 
 */

CTFrontendBuilder.controller("ControllerColumns", function($scope, $timeout) {

    $scope.columns = [];
    $scope.emptyColumnsComponent = [];

    /**
     * Set columns inside given Columns component
     *
     * @since 0.1.5
     */
    
    $scope.setColumns = function(id, item) {
        
        var columnsCount    = item.children.length,
            difference      = $scope.columns[id] - columnsCount,
            lastColumn      = item.children[columnsCount-1];

        if ( difference > 0 ) {
            for (var i = 0; i < difference; i++) {
                $scope.addColumn(id);
            };
        }

        if ( difference < 0 ) {
            for (var i = 0; i > difference; i--) {
                $scope.removeColumn(lastColumn.id);
            };
        }
    }
    

    /**
     * Update columns inside given Columns component
     *
     * @since 0.1.5
     */
    
    $scope.updateColumns = function(id) {
        
        $scope.findComponentItem($scope.componentsTree.children, id, $scope.setColumns);
    }


    /**
     * Add a column to currently active Columns component
     *
     * @since 0.1.5
     */

    $scope.addColumn = function(id) {

        //console.log('addColumn');

        $scope.applyComponentDefaultOptions($scope.component.id, "ct_column");

        var componentName       = "ct_column",
            columnsComponent    = $scope.getActiveComponent(),
            columnTemplate      = $scope.getComponentTemplate(componentName, $scope.component.id);

        // create column tree node
        column = {
            id : $scope.component.id, 
            name : componentName
        };

        // insert to Components Tree
        $scope.findComponentItem($scope.componentsTree.children, $scope.component.active.id, $scope.insertComponentToTree, column);

        // update all columns widths
        $scope.updateColumnsOnAdd(columnsComponent, columnTemplate);

        // activate column and update options
        //$scope.activateComponent(column.id, column.name);

        // increment id
        $scope.component.id++;

        // activate columns component back
        $scope.activateComponent(id, "ct_columns");
    };


    /**
     * Remove column from currently active Columns component
     * 
     * @since 0.1.5
     */

    $scope.removeColumn = function(id) {

        if ( $scope.columns[$scope.component.active.parent.id] == 1 ) {
            alert("You can not delete the last column");
            return false;
        } else {
            $scope.columns[$scope.component.active.parent.id]--;
        }
        
        var column = $scope.getComponentById(id),
            columnsComponent = column.parent();

        // remove from DOM
        $scope.updateColumnsOnRemove(columnsComponent, column);

        // remove from Components Tree
        $scope.findParentComponentItem($scope.componentsTree, id, $scope.removeComponentFromTree);

        //$scope.updateDOMTreeNavigator(id);
        $scope.removeDOMTreeNavigatorNode(id);
    }


    /**
     * Update columns widths and remove given column
     *
     * @since 0.1.6
     */

    $scope.updateColumnsOnRemove = function(columnsComponent, column) {
        
        var columns = columnsComponent[0].querySelectorAll('.ct-column');
        if ( columns.length > 0 ) {
            
            var newColumnsNumber    = columns.length - 1,
                columnId            = column[0].getAttribute('ng-attr-component-id'),
                freeSpace           = parseFloat($scope.getOption("width",columnId));

            // remove column from DOM
            column.remove();

            angular.forEach(columns, function(column) {
                    
                var // get column ID
                    columnId = column.getAttribute('ng-attr-component-id'),
                    // get width value
                    columnWidth = parseFloat($scope.getOption("width",columnId)),//parseFloat(column.style.width);
                    // calculate new value
                    columnWidth = (columnWidth + (freeSpace / newColumnsNumber) ).toFixed(2);
                
                // update scope
                $scope.setOptionModel("width", columnWidth, columnId, "ct_column");
                //column.setAttribute('size', columnWidth + "%");
            });

            $scope.cleanInsert(columnsComponent);
        }

        // make sure sum is always 100
        $scope.checkColumnsWidthSum(columnsComponent[0].querySelectorAll('.ct-column'));
    }


    /**
     * Update columns widths when one of them chenaged
     *
     * @since 0.3.1
     */

    $scope.updateColumnsOnChange = function(id, oldWidth) {

        var newWidth            = $scope.getOption("width",id),
            diff                = oldWidth - parseFloat(newWidth),
            column              = $scope.getComponentById(id),
            columnsComponent    = column.parent(),
            columns             = columnsComponent[0].querySelectorAll('.ct-column');

        if ($scope.log) {
            console.log("updateColumnsOnChange()", oldWidth, newWidth);
        }

        // get right column
        var columnToChange = column.next();

        // if no right column, get left
        if (!columnToChange[0]) {
            columnToChange = column.prev();            
        }

        // we have only one column???
        if (!columnToChange[0]) {
            return false;
        }
        
        var columnToChangeId    = columnToChange[0].getAttribute('ng-attr-component-id'),
            columnToChangeWidth = parseFloat($scope.getOption("width",columnToChangeId));

        // we make column wider
        if ( diff < 0 ) {

            // we have enough width to subtract
            if (columnToChangeWidth > Math.abs(diff)) {

                var newColumnWidth = (columnToChangeWidth + diff).toFixed(2);
                $scope.setOptionModel("width", newColumnWidth, columnToChangeId, "ct_column");
            }
            // make all other columns equal width
            else {

                if (columns.length > 0) {
            
                    var columnsNumber   = columns.length - 1,
                        freeSpace       = 100 - newWidth,
                        newColumnWidth  = (freeSpace / columnsNumber).toFixed(2);

                    angular.forEach(columns, function(column) {
                            
                        var columnId = column.getAttribute('ng-attr-component-id');

                        if (columnId != id) {
                            // update scope
                            $scope.setOptionModel("width", newColumnWidth, columnId, "ct_column");
                        }
                    });
                }
            }
        }

        // we make column thiner
        if ( diff > 0 ) {
            var newColumnWidth = (columnToChangeWidth + diff).toFixed(2);
            $scope.setOptionModel("width", newColumnWidth, columnToChangeId, "ct_column");
        }

        // make sure sum is always 100
        $scope.checkColumnsWidthSum(columns);
    }


    /**
     * Update columns widths and add given column
     *
     * @since 0.1.6
     */

    $scope.updateColumnsOnAdd = function(columnsComponent, column) {

        if ($scope.log) {
            console.log("updateColumnsOnAdd()");
        }
        
        var columns = columnsComponent[0].querySelectorAll('.ct-column');
        if ( columns.length > 0 ) {
            
            var columnsNumber       = columns.length,
                columnsWrap         = angular.element(columns[0]).parent(),
                columnsWrapWidth    = columnsWrap[0].offsetWidth,
                newColumnWidth      = (100 / (columnsNumber+1)).toFixed(2);

            angular.forEach(columns, function(column) {
                var // get column ID
                    columnId = column.getAttribute('ng-attr-component-id'),
                    // get width value
                    columnWidth = parseFloat($scope.getOption("width",columnId)), //parseFloat(column.style.width);
                    // calculate new value
                    columnWidth = (((100 - newColumnWidth) / 100) * columnWidth).toFixed(2);
                
                // update scope
                $scope.setOptionModel("width", columnWidth, columnId, "ct_column");
            });

            // update new column width
            $scope.setOptionModel("width", newColumnWidth, $scope.component.id, "ct_column");
        }
        
        var innerWrap = $scope.getInnerWrap(columnsComponent);
        innerWrap.append(column);
        
        $scope.cleanInsert(columnsComponent);

        // make sure sum is always 100
        $scope.checkColumnsWidthSum(columnsComponent[0].querySelectorAll('.ct-column'));
    }

    
    /**
     * Check columns sum to be 100% and adjust last column if needed
     *
     * @since 0.3.1
     */
    
    $scope.checkColumnsWidthSum = function(columns) {

        if ($scope.log) {
            console.log("checkColumnsWidthSum()", columns)
        }

        if (columns.length > 0) {

            var widthSum = 0;

            // calculate sum
            angular.forEach(columns, function(column) {

                var columnId;

                // check if tree node
                if (column.options){
                    columnId = column.id;
                }
                else {
                    columnId = column.getAttribute('ng-attr-component-id');
                }

                widthSum += parseFloat($scope.getOption("width",columnId));
            });

            // calculate the diff
            var lastDiff = parseFloat(100 - widthSum).toFixed(2);

            // get last column
            var lastColumnId;
    
            // check if tree node
            if (columns[0] && columns[0].name == "ct_column"){
                lastColumnId = jQuery(columns).last()[0].id;
            }
            // or jQuery object
            else {
                lastColumnId = jQuery(columns).last()[0].getAttribute("ng-attr-component-id");
            }
            
            var lastColumnWidth     = parseFloat($scope.getOption("width",lastColumnId)),
                newLastColumnWidth  = parseFloat(lastColumnWidth) + parseFloat(lastDiff);

            // update last column
            $scope.setOptionModel("width", newLastColumnWidth.toFixed(2), lastColumnId, "ct_column");
            //console.log(widthSum, lastDiff, lastColumnId);
        }
    }


    /**
     * Check if all columns are empty
     *
     * @since 0.2.3
     */

    $scope.checkEmptyColumns = function(id) {

        $scope.emptyColumnsComponent[id] = false;

        // find columns component
        $scope.findComponentItem($scope.componentsTree.children, id, $scope.checkEmptyColumnsCallback);

        if ( ! $scope.emptyColumnsComponent[id] ) {
            return "ct-columns-empty";
        }
    }


    /**
     * Check if all columns are empty (Callback)
     *
     * @since 0.2.3
     */

    $scope.checkEmptyColumnsCallback = function(key, columnsComponent, id) {
        
        angular.forEach(columnsComponent.children, function(column) {

            if ( column.children ) {
                $scope.emptyColumnsComponent[columnsComponent.id] = true;
            }
        });
    }

});