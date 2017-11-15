/**
 * All Media Queries stuff
 * 
 */

CTFrontendBuilder.controller("ControllerMediaQueries", function($scope, $http, $timeout) {


	$scope.currentMedia = "default";


	// Default Media Queries
	$scope.mediaList = {

		"default" 	
					: {
						maxSize : "100%",
						title : "All devices"
					},
		
		"tablet" 	
					: {
						maxSize : '992px', 
						title : "Less than 992px"
					},

		"phone-landscape"
				 	: {
						maxSize : '768px', 
						title : "Less than 768px"
					},

		"phone-portrait"
				 	: {
						maxSize : '480px', 
						title : "Less than 480px"
					},
	}


	/**
	 * Set current Media Query to edit
	 * 
	 * @since 0.3.2
	 */
	
	$scope.setCurrentMedia = function(name, viewportUpdate) {

		if ( $scope.getCurrentMedia() == name ) {
			return;
		}

		//console.log("setCurrentMedia", name);
		
		$scope.currentMedia = name;

		// update viewport
		if (viewportUpdate === undefined || viewportUpdate ) {
			var size = $scope.getMediaSize(name);
			$scope.adjustViewport(size);

			if (name=="default") {
				$scope.hideViewportRuller();
			}
			else {
				$scope.showViewportRuller();
			}
		}
		$scope.adjustViewportContainer();

		// disable stuff
        $scope.disableContentEdit();
        $scope.disableSelectable();
        $scope.closeAllTabs(["advancedSettings","componentBrowser"]); // keep certain sections

		// apply options
		$scope.applyModelOptions();

        // update all components styles
        $scope.updateAllComponentsCacheStyles();
        $scope.classesCached = false;
        $scope.outputCSSOptions(-1, true);
        $scope.checkTabs();
	}


	/**
	 * Get currently editing Media Query
	 * 
	 * @since 0.3.2
	 */

	$scope.getCurrentMedia = function() {

		return $scope.currentMedia;
	}


	/**
	 * Get Media Query Title by name
	 * 
	 * @since 0.3.2
	 */

	$scope.getMediaTitle = function(name) {

		return $scope.mediaList[name].title;
	}


	/**
	 * Get Media Query Size by name
	 * 
	 * @since 0.3.2
	 */

	$scope.getMediaSize = function(name) {

		return $scope.mediaList[name].maxSize;
	}


	/**
	 * Get currently editing Media Query Size
	 * 
	 * @since 0.3.2
	 */
	
	$scope.getMediaNameBySize = function(width) {

		var medias = [];

		for(var media in $scope.mediaList) { 
			if ($scope.mediaList.hasOwnProperty(media)) {

				//console.log(width + " < " + parseInt($scope.mediaList[media]['maxSize']));
				
				if ( width < parseInt($scope.mediaList[media]['maxSize']) ) {
					medias.push(media);
				}
			}
		}

		if ( medias.length > 0 ) {
			return medias[medias.length-1];
		}
		else {
			return "default";
		}
	}


	/**
	 * Get all medias that may apply to current i.e for max-size: 768px, also should apply max-size: 992px
	 * 
	 * @since 0.3.2
	 */

	$scope.getAllMediaNames = function(name) {

		if (name=="default") {
			return [];
		}

		var medias = [];

		for(var media in $scope.mediaList) { 
			if ($scope.mediaList.hasOwnProperty(media)) {
				
				if ( parseInt($scope.mediaList[name]['maxSize']) < parseInt($scope.mediaList[media]['maxSize']) ) {
					medias.push(media);
				}
			}
		}
		
		medias.push(name);
		
		return medias;
	}


	/**
	 * Set current Media parameter value
	 * 
	 * @since 0.3.2
	 */
	
	$scope.setMediaParameter = function(id, parameter, value) {

		// create objects if not exist
		if (!$scope.component.options[id]['media']) {
			$scope.component.options[id]['media'] = {};
		}
		if (!$scope.component.options[id]['media'][$scope.currentMedia]) {
			$scope.component.options[id]['media'][$scope.currentMedia] = {};
		}
		if (!$scope.component.options[id]['media'][$scope.currentMedia][$scope.currentState]) {
			$scope.component.options[id]['media'][$scope.currentMedia][$scope.currentState] = {};
		}

		$scope.component.options[id]['media'][$scope.currentMedia][$scope.currentState][parameter] = value;
	}


	/**
	 * Check if currently editing component or class has any media styles
	 * 
	 * @since 0.3.2
	 * @return {bool}
	 */

	$scope.isHasMedia = function(mediaName, id) {

		if (undefined == id) {
			id = $scope.component.active.id;
		}

		if ( $scope.isEditing("id") ) {
			if ($scope.component.active.name == "ct_reusable") {

				var viewId = $scope.component.options[id].original['view_id'];
				
				if ( !$scope.postsData[viewId] ) {
					return false;
				}
				
				var reusableTree = $scope.postsData[viewId]["post_tree"];
				
				if ($scope.findMedia(reusableTree, mediaName)) {
					return true;
				}
				else {
					return false;
				}
			}
			else {
				if ( $scope.component.options[id]['media'] && 
					 $scope.component.options[id]['media'][mediaName] && 
					 $scope.component.options[id]['media'][mediaName][$scope.currentState] ) {
					
					return true;
				}
				else {
					return false;
				}
			}
		}

		if ( $scope.isEditing("class") ) {
			
			if ( $scope.classes[$scope.currentClass] &&
				 $scope.classes[$scope.currentClass]['media'] && 
				 $scope.classes[$scope.currentClass]['media'][mediaName] && 
				 $scope.classes[$scope.currentClass]['media'][mediaName][$scope.currentState] ) {
				
				return true;
			}
			else {
				return false;
			}
		}

		if ( $scope.isEditing("custom-selector") && !$scope.isEditing("class") ) {
			
			if ( $scope.customSelectors[$scope.selectorToEdit] &&
				 $scope.customSelectors[$scope.selectorToEdit]['media'] && 
				 $scope.customSelectors[$scope.selectorToEdit]['media'][mediaName] && 
				 $scope.customSelectors[$scope.selectorToEdit]['media'][mediaName][$scope.currentState] ) {
				
				return true;
			}
			else {
				return false;
			}
		}		
	}


	/**
	 * Remove media styles from component
	 * 
	 * @since 0.3.2
	 */

	$scope.removeComponentMedia = function(mediaName, id) {

		$scope.cancelDeleteUndo();

		if ( $scope.isEditing("id") ) {
			
			if (undefined == id) {
				id = $scope.component.active.id;
			}

			if ( $scope.component.options[id]['media'] && 
				 $scope.component.options[id]['media'][mediaName] && 
				 $scope.component.options[id]['media'][mediaName][$scope.currentState] ) {
				
				$scope.component.options[id]['media'][mediaName][$scope.currentState] = {};
				delete $scope.component.options[id]['media'][mediaName][$scope.currentState];

				// update Tree
            	$scope.findComponentItem($scope.componentsTree.children, id, $scope.removeMediaFromTree, mediaName);
			}
		}

		if ( $scope.isEditing("class") ) {

			if ( $scope.classes[$scope.currentClass] &&
				 $scope.classes[$scope.currentClass]['media'] && 
				 $scope.classes[$scope.currentClass]['media'][mediaName] && 
				 $scope.classes[$scope.currentClass]['media'][mediaName][$scope.currentState] ) {

				$scope.classes[$scope.currentClass]['media'][mediaName][$scope.currentState] = {};
				delete $scope.classes[$scope.currentClass]['media'][mediaName][$scope.currentState];
			}
		}

		$scope.unsavedChanges();
	}


	/**
	 * Remove media styles from Components Tree
	 * 
	 * @since 1.0.1
	 * @author Ilya K.
	 */
	
	$scope.removeMediaFromTree = function(key, item, mediaName) {
		delete item.options.media[mediaName];
	}

})