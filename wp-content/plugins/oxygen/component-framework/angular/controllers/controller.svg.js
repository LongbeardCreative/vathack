/**
 * All SVG staff here
 * 
 * @since 0.2.1
 */

CTFrontendBuilder.controller("ControllerSVGIcons", function($scope, $http, $timeout) {

	/**
	 * Set current SVG Set to choose from
	 * 
	 * @since 0.2.1
	 */

	$scope.setCurrentSVGSet = function( name ) {

		$scope.currentSVGSet 	= name;
		$scope.showSVGSets 		= false;
	}


	/**
	 * Set current SVG icon
	 * 
	 * @since 0.2.1
	 */

	$scope.setSVGIcon = function( id, title ) {

		var activeId 	= $scope.component.active.id,
			activeName 	= $scope.component.active.name;

		id = $scope.currentSVGSet.split(' ').join('')+id;
		// update model and Tree
		$scope.component.options[activeId]['model']['icon-id'] = id;
		$scope.setOption(activeId, activeName, "icon-id");

		$scope.iconFilter.title = title;
		// close grid and call resize
		$scope.switchActionTab('SVGIcons');
	}

});