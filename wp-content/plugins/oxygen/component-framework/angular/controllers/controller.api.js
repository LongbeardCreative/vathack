/**
 * All API callbacks to handle server responses
 * 
 */

CTFrontendBuilder.controller("ControllerAPI", function($scope, $http, $timeout) {

	$scope.itemOptions = {};

	/**
	 * Show componentize dialog
	 * 
	 * @since 0.4.0
	 * @author Ilya K.
	 */
	
	$scope.showAddItemDialog = function(id, type, termId, termType) {
		
		$scope.showDialogWindow();
		
		$scope.itemOptions = {
			id: 		 id,
			type: 		 type,
			termId: 	 termId,
			termType: 	 termType,
			currentItem: $scope.getAPIItem(id, type)
		}
		
		$scope.dialogForms['showAddItemDialogForm'] = true;

		jQuery(document).on("keydown", $scope.switchComponent);
	}


	/**
	 * Loop components within one category/design set
	 * 
	 * @since 0.4.0
	 * @author Ilya K.
	 */

	$scope.switchComponent = function(event, direction) {

		if (direction==undefined) {
			// stop if not left or right arrows
			if (event.keyCode != 37 && event.keyCode != 39) {
				return;
			}
			if (event.keyCode == 37) {
				direction = "left";
			}
			if (event.keyCode == 39) {
				direction = "right";
			}
		}

		var currentTerm = $scope.getAPITerm($scope.itemOptions),
			currentKey 	= null;

		// get items list to switch between
		if ( $scope.itemOptions.termType == "design_sets" ) {
			var termItems = currentTerm["children"][$scope.itemOptions.type]["items"];
		}

		if ( $scope.itemOptions.termType == "components" ) {
			var termItems = currentTerm["items"];
		}

		if ( $scope.itemOptions.termType == "pages" ) {
			var termItems = currentTerm;
		}

		// get current item key (not the ID!)
		for (var key in termItems) {
			if (termItems[key].id == $scope.itemOptions.id) {
				currentKey = key;
			}
		}

		if (direction == "left"){
			currentKey--;
			if (currentKey < 0){
				currentKey = termItems.length - 1;
			}
		}

		if (direction == "right"){
			currentKey++;
			if (currentKey > termItems.length - 1){
				currentKey = 0;
			}
		}

		// update id
		var currentItemId = termItems[currentKey].id;

		// update scope
		$scope.itemOptions.id 			= currentItemId;
		$scope.itemOptions.currentItem 	= $scope.getAPIItem(currentItemId, $scope.itemOptions.type);

		if (event) {
			$scope.$apply();
		}
	}


	/**
	 * Insert in builder
	 *
	 * @since 0.4.0
	 * @author Ilya K.
	 */
	
	$scope.addItem = function(id, type, $event) {

		$scope.cancelDeleteUndo();

		if ( id == undefined ) {
			id = $scope.itemOptions.id
		}

		if ( type == undefined ) {
			type = $scope.itemOptions.type;
		}

		if ( $event !== undefined ) {
			$event.stopPropagation();
		}

		switch (type) {

			case 'component' :

				// get component from server
				$scope.makeAPICall("get_components", {
					"id": id
				}, $scope.addReusableChildren);

			break;

			case 'page' :

				// get page from server
				$scope.makeAPICall("get_pages", {
					"id": id
				}, $scope.addReusableChildren);

			break;
		}

		$scope.hideDialogWindow();
	}


	/**
	 * Get item from scope.api_component or .api_pages by id
	 *
	 * @since 0.4.0
	 * @author Ilya K.
	 */
	
	$scope.getAPIItem = function(id, type, property) {

		if (type == "component") {
			var items = $scope.api_components
		}

		if (type == "page") {
			var items = $scope.api_pages
		}

		var result = items.filter(function(item) {
			return item.id == id;
		});

		if ( property !== undefined ) {
			return result ? result[0][property] : null;
		}
		else {
			return result ? result[0] : null;	
		}
	}


	/**
	 * Get term from scope.folders by id and type
	 *
	 * @since 0.4.0
	 * @author Ilya K.
	 */
	
	$scope.getAPITerm = function(options) {

		if (options.termType == "design_sets" || options.termType == "components") {
			var termItems = $scope.folders[options.termType]["children"]
		}

		if (options.termType == "pages") {
			return $scope.folders[options.termType]["items"]
		}

		// recursively find term in folders
		function searchFoldersTree(termItems, id) {
			var result = false;
			for (var key in termItems) {
				if (termItems[key].id == id) {
					return termItems[key];
				}
				if (termItems[key]["children"]) {
					result = searchFoldersTree(termItems[key]["children"], id)		
				}
			}
			return result;
		}
		result = searchFoldersTree(termItems, options.termId)

		return result;	
	}


	/**
	 * Show form to update component screenshot
	 *
	 * @since 0.4.0
	 * @author Ilya K.
	 */

	$scope.showUpdateScreenshot = function() {

		$scope.hideDialogWindow();
		$scope.showDialogWindow();
		$scope.dialogForms['showUploadAsset'] = true;
	}


	/**
	 * Update component screenshot
	 *
	 * @since 0.4.0
	 * @author Ilya K.
	 */

	$scope.updateScreenshot = function() {

		$scope.postAsset($scope.componentizeOptions.screenshot, function(){
			
			if ($scope.itemOptions.type == "component"){
				action = "update_component";
			}
			if ($scope.itemOptions.type == "page"){
				action = "update_page";
			}

			$scope.makeAPICall(action, {
				"id": $scope.itemOptions.id,
				"screenshot": $scope.componentizeOptions.assetId
			});

			$scope.componentizeOptions.id = null;
			$scope.hideDialogWindow();
		});
	}


	/**
	 * Show create design set dialog
	 *
	 * @since 0.4.0
	 * @author Ilya K.
	 */

	$scope.showCreateDesignSet = function() {

		$scope.hideDialogWindow();
		$scope.showDialogWindow();
		$scope.dialogForms['showAddDesignSet'] = true;
	}


	/**
	 * Send new design set data to server to create
	 *
	 * @since 0.4.0
	 * @author Ilya K.
	 */

	$scope.createDesignSet = function() {

		$scope.makeAPICall("create_design_set", {
			"name": $scope.componentizeOptions.setName,
			"status": $scope.componentizeOptions.status
		});

		$scope.hideDialogWindow();

	}


	/**
	 * Show style sheet dialog 
	 * 
	 * @since 0.4.0
	 * @author Ilya K.
	 */
	
	$scope.showStyleSheetDialog = function(name) {

		$scope.componentizeOptions.stylesheetName = name;
		
		$scope.showDialogWindow();
		$scope.dialogForms['stylesheet'] = true;
	}


	/**
	 * Post style sheet to the DB
	 * 
	 * @since 0.4.0
	 * @author Ilya K.
	 */
	
	$scope.postStyleSheet = function() {
		
		$scope.makeAPICall("post_style_sheet", {
			"name": $scope.componentizeOptions.stylesheetName,
			"content": $scope.styleSheets[$scope.componentizeOptions.stylesheetName],
			"design_set_id": $scope.componentizeOptions.designSetId
		});

		$scope.hideDialogWindow();
	}

})