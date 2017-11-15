/**
 * All Fonts staff here
 * 
 */

CTFrontendBuilder.controller("ControllerFonts", function($scope, $http, $timeout) {

	// TODO: change API key from personal to corporate?
	$scope.APIKey 		= "AIzaSyBlDz9OGMf_5_-QxgHPTjjmvYVzEauwcQE";
	$scope.WebFontsUrl 	= "https://www.googleapis.com/webfonts/v1/webfonts";

	// Set default web fonts
	$scope.webSafeFonts = [
		"Georgia, serif",
		"Times New Roman, Times, serif",
		"Arial, Helvetica, sans-serif",
		"Arial Black, Gadget, sans-serif",
		"Tahoma, Geneva, sans-serif",
		"Verdana, Geneva, sans-serif",
		"Courier New, Courier, monospace"
	];

	$scope.fontsList = angular.copy($scope.webSafeFonts);

	$scope.showFonts 	= [];
	$scope.loadedFonts 	= [];


	/**
     * Load Google Webfonts via AJAX
     * 
     * @since 0.1.9
     */

	$scope.getWebFontsList = function() {

		// Send AJAX request
		$http({
			method: "GET",
			url: $scope.WebFontsUrl,
			params: {
				"key" : $scope.APIKey,
				"sort" : "popularity"
			}
		})
		.success(function(fonts, status, headers, config) {
			
			 angular.forEach(fonts.items, function(item) {
			 	$scope.fontsList.push(item.family);
			 });
		})
		.error(function(data, status, headers, config) {
			console.log(data);
		});
	}


	/**
	 * Apply font family for certain component by it's ID
	 *
	 * @since 0.1.9
	 */

	$scope.setComponentFont = function(id, name, font) {

		$scope.loadWebFont(font);

		// update model
		$scope.component.options[id]['model']['font-family'] = font;

		// update Tree
		$scope.setOption(id, name, "font-family");

		$scope.showFonts    = [];
		$scope.fontsFilter  = "";
	}


	/**
	 * Get component font value
	 *
	 * @since 0.1.9
	 */

	$scope.getComponentFont = function(id, isName, stateName) {

		if ( id == 0 ) {
			return "";
		}

		if ( !stateName ) {
			stateName = 'model';
		}

		// use currently active component if id is not defined
		if ( !id ) {
			id = $scope.component.active.id;
		}

		if ( $scope.component.options[id] && $scope.component.options[id][stateName] !== undefined ) { 
			var font = $scope.component.options[id][stateName]['font-family'];
		}
		
		if ( !font ) {
			return;
		}

		if ( font[0] == 'global' ) {
			// global fonts
			if ( isName === true ) {
				return font[1] + " (" + $scope.getGlobalFont(font[1]) + ")";
			}
			else {
				return $scope.getGlobalFont(font[1]);
			}
		}
		else {
			return font;
		}
	}


	/**
	 * Apply font family for global variable
	 *
	 * @since 0.1.9
	 */

	$scope.setGlobalFont = function(name, font) {

		$scope.globalSettings.fonts[name] = font;

		$scope.fontsFilter  = "";

        $scope.loadWebFont(font);

        $scope.updateAllComponentsCacheStyles();
        $scope.outputCSSOptions();

        if ( name == "Text" || name == "Display" ) {
        	$scope.updateGlobalFontCSS();
        }

        $scope.unsavedChanges();
	}


	/**
	 * Return global font family by custom name
	 *
	 * @since 0.1.9
	 */

	$scope.getGlobalFont = function(name) {

		return $scope.globalSettings.fonts[name];
	}


	/**
	 * Add new custom global font
	 *
	 * @since 0.1.9
	 */

	$scope.addGlobalFont = function() {

		var name = prompt("Custom global font name (i.e 'My Font'):");
        
        if (name != null) {
			return $scope.globalSettings.fonts[name] = "Open Sans";
		}

		$scope.unsavedChanges();
	}


	/**
	 * Delete global font
	 *
	 * @since 0.1.9
	 */

	$scope.deleteGlobalFont = function(name) {

		var confirmed = confirm("Are you sure to delete \""+name+"\" font?");
        
        if ( !confirmed ) {
            return false;
        }

        // delete from global settings
		delete $scope.globalSettings.fonts[name];

		// delete from classes
		angular.forEach($scope.classes, function(classStates, className) {

			angular.forEach(classStates, function(stateOption, stateName) {

				if ( stateOption['font-family'] && stateOption['font-family'][1] == name ) {
					delete stateOption['font-family'];
				}
			});
		});

		$scope.unsavedChanges();
	}


	/**
	 * Check global font on options apply and delete fonts that doesn't exist in global settings
	 *
	 * @since 0.1.9
	 */

	$scope.checkGlobalFont = function(id, item) {

		angular.forEach(item.options, function(stateOptions) {
			
			angular.forEach(stateOptions, function(value, name) {

				var font = true;

				// check if global font is exist in global settings
				if ( name == "font-family" && value[0] == 'global' ) {
					font = $scope.getGlobalFont(value[1]);
				}

				// apply option
				if ( font == undefined ) {
					// update Tree
					delete stateOptions[name];
				}
			});			
		});
	}


	/**
	 * Load Google Web font
	 *
	 * @since 0.1.9
	 */

	$scope.loadWebFont = function(font) {

		// Skip Inherit
		if ( font == "Inherit" ) {
			return false;
		}

		// check if global font used
		if ( font[0] == 'global' ) {
			name = $scope.getGlobalFont(font[1]);
		}
		else {
			name = font;
		}

		// Don't load Web Safe fonts
		if ( $scope.webSafeFonts.indexOf(name) > -1 ) {
			return false;
		}

		// Don't load Typekit fonts
		if ($scope.typeKitFonts) {
			for(var i = 0, len = $scope.typeKitFonts.length; i < len; i++) {
				if ($scope.typeKitFonts[i].slug === name) {
					return false;
				}
			}
		}

		// Don't load fonts that already had been loaded
		if ( $scope.loadedFonts.indexOf(name) > -1 ) {
			return false;
		}

		$scope.loadedFonts.push(name);

		if(name && name !== '') {
			name += ":100,200,300,400,500,600,700,800,900";

			// finally load font
			WebFont.load({
				google: {
					families: [name]
				}
			});
		}
	}


	/**
	 * Update 'Text' global font CSS styles
	 *
	 * @since 0.2
	 */

	$scope.updateGlobalFontCSS = function() {

		var textFont 	= $scope.getGlobalFont('Text'),
			displayFont = $scope.getGlobalFont('Display');

		$scope.loadWebFont(textFont);
        $scope.loadWebFont(displayFont);
			
		if ( textFont.indexOf(',') === -1 ) {
			textFont = "'"+textFont+"'";
		}
		if ( displayFont.indexOf(',') === -1 ) {
			displayFont = "'"+displayFont+"'";
		}

		var style = ".ct-builder{font-family:"+textFont+"}";
		style += ".ct-headline{font-family:"+displayFont+"}";

		// output to head
        $scope.outputCSSStyles("ct-global-font", style);
	}


	/**
	 * Load all global fonts added in settings
	 *
	 * @since 0.4.0
	 */
	
	$scope.loadSavedGlobalFonts = function() {

		angular.forEach($scope.globalSettings.fonts, function(font, key) {
			$scope.loadWebFont(font);
		})
	}

});