/**
 * All Templates/Views related functions
 * 
 */

CTFrontendBuilder.controller("ControllerTemplates", function($scope, $timeout){

	$scope.replaceAfterReusable = false;
	$scope.template = {};
	$scope.template.postData = {};

	$scope.componentizeOptions = {};
	$scope.componentizeOptions.name 	= "Re-usable component";
	$scope.componentizeOptions.pageName = "Re-usable page";
	$scope.componentizeOptions.setName  = "My Design Set";
	$scope.componentizeOptions.status 	= "public";

	$scope.separatorAdded = false;

	
	/**
     * Load term data by term id
     * 
     * @since 0.3.3
     * @author Ilya K.
     */

	$scope.loadTemplatesTerm = function(id){

		// make AJAX call
		$scope.loadTemplateData($scope.setTemplateData, id);
	}
	

	/**
     * Callback to set post data
     * 
     * @since 0.2.0
     * @author Ilya K.
     */

	$scope.setTemplateData = function(data) {

		if ($scope.log) {
			console.log("setTemplateData()", data);
		}

		// update body classes if set in data
		if (undefined!==data.bodyClass) {
			jQuery("body").removeClass().addClass(data.bodyClass).addClass("oxygen-builder-body");
		}
		
		if (undefined!==data.postData) {
			$scope.template.postData = data.postData;
		}

		if (undefined!==data.postsList) {

			if (data.postsList === null) {
				//alert("No posts found to preview");
			}
			else {
				$scope.template.postsList = data.postsList;

				// load first post
				$scope.loadTemplatesTerm(data.postsList[0].id);
				return;
			}
		}

		if (undefined!==data.termsList && data.termsList[0]) {
			$scope.template.termsList = data.termsList;

			// load first term
			$scope.loadTemplatesTerm(data.termsList[0].id);
			return;
		}

		//console.log(data);

		// rebuild DOM
		// TODO: rebuild only code blocks??
		$scope.rebuildDOM(0);

		$scope.outputCSSOptions();
	}


	/**
     * Load Re-usable part via AJAX by it's ID
     * If componentId defined load only children into this component
     * 
     * @since 0.2.3
     * @author Ilya K.
     */

	$scope.loadReusablePart = function(viewPostId, componentId) {

		if (componentId !== undefined) {
			$scope.loadComponentsTree($scope.addReusableChildren, viewPostId, componentId);
		}
		else {
			$scope.loadComponentsTree($scope.addReusable, viewPostId, componentId);
		}		
	}

	/**
     * Insert "Re-usable part" Component to the Components Tree and rebuild the DOM
	 * 
     * @since 0.2.3
     * @author Ilya K.
     */

	$scope.addReusable = function(data, viewPostId) {
		
		var tree = {};

		// response from oxygen server
		if ( data[0] !== undefined && data[0]["content"] !== undefined ) {
			tree = data[0]["content"];
		}
		// response from user install
		else {
			tree = data;
		}

		if ($scope.log) {
			console.log("addReusable()", viewPostId);
		}

		var componentId = $scope.component.active.id;
		
		var reusable = {
			id: 0,
			name: "ct_reusable",
			options: {
				view_id: viewPostId
			}
		}

		var componentToInsert = $scope.getClosestPossibleInsert(componentId, tree, $scope.replaceAfterReusable);
		
		$scope.componentBuffer = reusable;
		$scope.componentInsertId = componentToInsert.id;

		if (componentToInsert.index >= 0) {
			$scope.newComponentKey = componentToInsert.index;
		}

		var insertedId = $scope.component.id;

		// update new element ids and selector
		$scope.updateNewComponentIds($scope.componentBuffer, componentToInsert);

		// paste reusable to current tree
		$scope.findComponentItem($scope.componentsTree.children, componentToInsert.id, $scope.pasteComponentToTree);

		if($scope.replaceAfterReusable) {

			$scope.removeComponentById($scope.replaceAfterReusable)
			$scope.replaceAfterReusable = false;
		}

		// disable undo option
        $scope.cancelDeleteUndo();

		// updates
		$scope.rebuildDOM(insertedId);
		$scope.updateDOMTreeNavigator(insertedId);

		$scope.unsavedChanges();
	}


	/**
     * Insert "Re-usable part" chidlren to the Components Tree and build each child DOM node
     * 
     * @since 0.2.3
     * @author Ilya K.
     */

	$scope.addReusableChildren = function(data, viewPostId, componentId) {

		if (componentId == undefined) {
			componentId = $scope.component.active.id;
		}

		if ($scope.log) {
			console.log("addReusableChildren()", data, viewPostId, componentId);
		}
		
		var tree = {};

		// response from oxygen server
		if ( data[0] !== undefined && data[0]["content"] !== undefined ) {
			try {
				tree = JSON.parse($scope.stripSlashes(data[0]["content"]));
				$scope.parseTreeClassesStyles(tree);

				var designSetId = data[0]["design_set_id"];

				// add stylsheets
				$scope.makeAPICall("get_style_sheets", {
					"design_set_id": designSetId
				}, $scope.addDesignSetStyleSheets);

				// check options
				if ( $scope.api_design_sets[designSetId] && $scope.api_design_sets[designSetId].options ) {
					$scope.checkGlobalOptions($scope.api_design_sets[designSetId].options);
				}
			}
			catch (err) {
				console.log(err);
				alert("Error parsing component JSON");
				return false;
			}
		}
		// response from user install
		else {
			tree = data;
		}

        var componentToInsert = $scope.getClosestPossibleInsert(componentId, tree);

		angular.forEach(tree.children, function(child, key) {
			
			var insertedId = $scope.component.id;
			
			$scope.componentBuffer = child;
			
			if (componentToInsert.index >= 0) {
				$scope.newComponentKey = componentToInsert.index + key;
			}

			// update new element ids and selector
            $scope.updateNewComponentIds($scope.componentBuffer, componentToInsert);
		
			// paste loaded content to current tree
        	$scope.findComponentItem($scope.componentsTree.children, componentToInsert.id, $scope.pasteComponentToTree);

        	// disable undo option
        	$scope.cancelDeleteUndo();

        	// updates
        	$scope.rebuildDOM(insertedId);
        	$scope.updateDOMTreeNavigator();
        	$scope.updateComponentCacheStyles(insertedId);
		})

		// updates
		$scope.outputCSSOptions();

		// hide dialog widnow for API
		$scope.hideDialogWindow();
	}


	/**
     * Insert "Re-usable part" Content to the DOM
     * 
     * @since 0.2.3
     * @author Ilya K.
     */

	$scope.addReusableContent = function(data, componentId) {

		if (!data) {
			return;
		}

		if ($scope.log) {
			console.log("addReusableContent()", data);
		}

		var componentToInsert = $scope.getComponentById(componentId);

		if (componentToInsert) {
			componentToInsert.append(data.post_content);
		}

		// holder for reusable CSS
		$scope.reusableCSS 			= {};
		$scope.reusableCSS.styles 	= "";

		// add this item CSS
		if (data.post_tree) {
			$scope.generateTreeCSS(data.post_tree, $scope.reusableCSS);
		}

		// output CSS styles generated for all reusable's items
		$scope.outputCSSStyles("ct-re-usable-styles-"+data.ID, $scope.reusableCSS.styles);
	}


	/**
	 * Get closest possibly nestable for re-usable part component ID and children count
	 *
	 * @since 0.2.3
	 */
	
	 $scope.getClosestPossibleInsert = function(componentId, tree, atleastParent) {

	 	if ($scope.log) {
			console.log("getClosestPossibleInsert()", componentId, tree);
		}

	 	if (undefined == componentId) {
	 		return {};
	 	}

	 	var componentToInsertIn = $scope.getComponentById(componentId);
	 		insertData = {
	 			componentToInsertIn : componentToInsertIn,
				componentInsertId : 0,
				parentId : 0,
				index : (componentId > 0 ) ? componentToInsertIn.index() + 1 : -1
			},
			i = 0;

		var isNestable 	= insertData.componentToInsertIn[0].attributes['is-nestable'],
			isReusable	= insertData.componentToInsertIn[0].classList.contains("ct_reusable");

		var treeJSON 	= JSON.stringify(tree),
			hasSection 	= (treeJSON.indexOf('"name":"ct_section"') > -1),
			hasLink 	= (treeJSON.indexOf('"name":"ct_link"') > -1 || 
						   treeJSON.indexOf('"name":"ct_link_text"') > -1);
		
		function findClosestParent(insertData, className) {

			var parent 		= insertData.componentToInsertIn,
				parentId 	= insertData.parentId;

			while ( !parent.hasClass(className) && !parent.hasClass('ct-builder') ) {
				parent = parent.parent();
			}

			insertData.parentId = parent[0].getAttribute('ng-attr-component-id');

			// found component in parents
			if (insertData.parentId != 0) {

				insertData.index = parent.index() + 1;

			    // go level up from component
			    insertData.componentToInsertIn = jQuery(parent).parent().closest("[is-nestable]");
			    insertData.componentInsertId   = insertData.componentToInsertIn[0].getAttribute('ng-attr-component-id');
			}
			else {
				// set previous parent back
				insertData.parentId = parentId;
			}
		}

		// search for section in parents
		if (hasSection) {
			findClosestParent(insertData, "ct_section");
		}

		// search for link wrapper in parents
		if (hasLink) {
			findClosestParent(insertData, "ct_link");
		}

		if (insertData.parentId == 0) {

			//console.log(componentToInsertIn, isNestable, isReusable);
		
			// adding to nestable component
			if ( isNestable && !isReusable && !atleastParent ) {
			    insertData.componentInsertId = componentId;
			    insertData.index = -1
			}
			// find nestable parent
			else {

				while ( ((!isNestable || atleastParent) || isReusable) && i < 10 ) {
				
			        insertData.componentToInsertIn = jQuery(insertData.componentToInsertIn).parent().closest("[is-nestable]");

					if ( insertData.componentToInsertIn ) {
						isNestable 	= insertData.componentToInsertIn[0].attributes['is-nestable'];
						isReusable	= insertData.componentToInsertIn[0].classList.contains("ct_reusable");
			        	insertData.componentInsertId = insertData.componentToInsertIn[0].getAttribute('ng-attr-component-id');
			        }

			        if(isNestable)
			        	atleastParent = false;
			        // prevent infinite loop
			        i++;
				}
			}
		}

        return {
        	id: insertData.componentInsertId,
        	index: insertData.index,
        }
	}


	/**
     * Check if component can be componentized as Re-usable part
     * 
     * @since 0.2.3
     * @author Ilya K.
     */

	$scope.isCanComponentize = function(id, name) {

		if ( name == "ct_reusable" ) {
			return false;
		}

		if ( name == "ct_column" ) {
			return false;
		}

		if ( name == "ct_li" ) {
			return false;
		}

		if ( id > 0 ) {
			return true;
		}
		else {
			return false;
		}
	}


	/**
	 * Send Tree node to save as View CPT
	 * 
	 * @since 0.2.3
	 * @author Ilya K.
	 */
	
	$scope.saveReusable = function(id) {

		$scope.cancelDeleteUndo();

		if (undefined === id) {
			id = $scope.component.active.id;
		}

		var name = prompt("Componentize name", $scope.componentizeOptions.name);
        
		if (name!=null) {

			$scope.showLoadingOverlay("saveReusable()");

			$scope.componentizeOptions.name = name;

			// choose components to componentize
			if ( $scope.isSelectableEnabled && $scope.isDOMNodesSelected ) {
				
				var parent      = jQuery("#ct-dom-tree").find('.ct-selected-dom-node').first().parent().parent(),
					nodes       = parent.children('.ct-dom-tree-node').has('.ct-selected-dom-node'),
					ids         = [];

				// get top level selected component ids
				nodes.each(function(){
					ids.push(jQuery(this).attr('ng-attr-tree-id')); 
				});

				$scope.selectedComponents = [];
				
				// add all components from tree
				for (var i = 0, id; id = ids[i], i <= ids.length - 1; i++) {
					$scope.findComponentItem($scope.componentsTree.children, id, $scope.addSelectedComponent);
				}
				
				$scope.saveComponentAsView(null, $scope.selectedComponents);
			}
			else {
				// save component as View CPT
				$scope.findComponentItem($scope.componentsTree.children, id, $scope.saveComponentAsView);
			}
		}
		else {
			alert("Name can't be empty");
		}
	}


	/**
	 * Replace components saved as re-usable with actual Re-usable component
	 * 
	 * @since 1.0.1
	 * @author Ilya K.
	 */
	
	$scope.replaceReusablePart = function(id, post_id) {
		
		$scope.replaceAfterReusable = id;

		$scope.loadReusablePart(post_id);
	};


	/**
	 * Show componentize dialog
	 * 
	 * @since 0.4.0
	 * @author Ilya K.
	 */
	
	$scope.showComponentize = function(id) {
		
		$scope.showDialogWindow();
		$scope.componentizeOptions.id = id;
		$scope.dialogForms['showComponentizeForm'] = true;
	}


	/**
	 * Send Tree node to save on server
	 * 
	 * @since 0.2.3
	 * @author Ilya K.
	 */
	
	$scope.componentize = function() {

		// TODO: add validation here to not send request to the server

		// choose components to componentize
		if ( $scope.isSelectableEnabled && $scope.isDOMNodesSelected ) {
			
			var parent      = jQuery("#ct-dom-tree").find('.ct-selected-dom-node').first().parent().parent(),
				nodes       = parent.children('.ct-dom-tree-node').has('.ct-selected-dom-node'),
				ids         = [];

			// get top level selected component ids
			nodes.each(function(){
				ids.push(jQuery(this).attr('ng-attr-tree-id'));
			});

			$scope.selectedComponents = [];
			
			// add all components from tree
			for (var i = 0, id; id = ids[i], i <= ids.length - 1; i++) {
				$scope.findComponentItem($scope.componentsTree.children, id, $scope.addSelectedComponent);
			}
			
			$scope.postComponentize(null, $scope.selectedComponents);
		}
		else {
			if ($scope.componentizeOptions.idToUpdate>0) {
				// update existed component
				$scope.componentizeOptions.assetId = null;
				$scope.findComponentItem($scope.componentsTree.children, $scope.componentizeOptions.id, $scope.postComponentize);
				$scope.componentizeOptions.id = null;
			}
			else {
				// post new component
				$scope.postAsset($scope.componentizeOptions.screenshot, function(){
					$scope.findComponentItem($scope.componentsTree.children, $scope.componentizeOptions.id, $scope.postComponentize);
					$scope.componentizeOptions.id = null;
				});
			}
		}

		$scope.hideDialogWindow();
	}


	/**
	 * Show componentize dialog for page
	 * 
	 * @since 0.4.0
	 * @author Ilya K.
	 */
	
	$scope.showPageComponentize = function(id) {
		
		$scope.showDialogWindow();
		$scope.dialogForms['showPageComponentizeForm'] = true;
	}


	/**
	 * Send whole Components Tree to server
	 * 
	 * @since 0.4.0
	 * @author Ilya K.
	 */

	$scope.tryPageComponentize = function() {

		// post component to server
		$scope.postAsset($scope.componentizeOptions.screenshot, function(){
			$scope.pageComponentize();
		});

		$scope.hideDialogWindow();
	}


	/**
	 * Simply adds one component to scope variable to save selectaed as componentize
	 * 
	 * @since 0.2.4
	 */

	$scope.addSelectedComponent = function(key, component) {

		$scope.selectedComponents.push(component);
	}


	/**
	 * Add widget component
	 * 
	 * @since 0.2.3
	 */
	
	$scope.addWidget = function(className, idBase, prettyName) {

		var widgetId = $scope.component.id;

		$scope.addComponent('ct_widget', 'widget');

		// update class name
		$scope.component.options[widgetId]['model']['class_name'] = className;
		$scope.setOption(widgetId, "ct_widget", "class_name");

		// update id base
		$scope.component.options[widgetId]['model']['id_base'] = idBase;
		$scope.setOption(widgetId, "ct_widget", "id_base");

		// update id base
		$scope.component.options[widgetId]['model']['pretty_name'] = prettyName;
		$scope.setOption(widgetId, "ct_widget", "pretty_name");

		$scope.rebuildDOM(widgetId);
	}


	/**
	 * Load widget form with options
	 * 
	 * @since 0.2.3
	 */
	
	$scope.loadWidgetForm = function() {

		$scope.renderWidget($scope.component.active.id, true);
	}


	/**
	 * Apply widget options and render
	 * 
	 * @since 0.2.3
	 */

	$scope.applyWidgetInstance = function(id) {

		if (!id) {
			id = $scope.component.active.id;
		}

		var widgetOptions = $scope.component.options[id];
		
		if (widgetOptions.name != "ct_widget") {
			return false;
		}
		
		var values = {},
			inputs = jQuery('#ct-widget-form').serializeArray(),

			// get base
			idBase = widgetOptions['original']['id_base'];

		jQuery.each(inputs, function(i, field) {

			// remove widget-id_base[1][...]
			var name = field.name.replace(idBase, "");
			//console.log(name);
			name = name.replace(/widget-\[\d\]\[/, "");
			//console.log(name);
			name = name.replace("]", "");
			//console.log(name);

			values[name] = field.value;
		});

		// update model and Components Tree
		$scope.component.options[$scope.component.active.id]['model']['instance'] = values;
		$scope.setOption($scope.component.active.id, "ct_widget", "instance");

		// render widget with new options
		$scope.renderWidget($scope.component.active.id);
	}


	/**
	 * Show dialog with a form for HTML component
	 * 
	 * @since 0.2.3
	 */

	$scope.loadHTMLForm = function() {
		
		$scope.showDialogWindow();

		angular.element("#ct-html-component-form").val($scope.component.options[$scope.component.active.id]['model']['html']);
	}


	/**
	 * Apply HTML code from the form to component
	 * 
	 * @since 0.2.3
	 */

	$scope.applyHTMLForm = function() {
		
		var html = angular.element("#ct-html-component-form").val(),
			activeComponent = $scope.getActiveComponent();

		activeComponent.html(html);
		
		// update model and Components Tree
		$scope.component.options[$scope.component.active.id]['model']['html'] = html;
		$scope.setOption($scope.component.active.id, "ct_html", "html");
	}


	/**
	 * Apply all code block parts: PHP, JS, CSS
	 * 
	 * @since 0.3.1
	 * @author Ilya K.
	 */

	$scope.applyCodeBlock = function(componentId, updateTree) {

		if ($scope.log) {
			console.log("applyCodeBlock()", componentId, updateTree);
		}

		$scope.applyCodeBlockPHP(componentId, updateTree);
		$scope.applyCodeBlockJS(componentId, updateTree);
		$scope.applyCodeBlockCSS(componentId, updateTree);
	}


	/**
	 * Get executed code block PHP
	 * 
	 * @since 0.3.1
	 * @author Ilya K.
	 */

	$scope.applyCodeBlockPHP = function(componentId, updateTree) {

		if (componentId === undefined) {
			componentId = $scope.component.active.id;
		}

		if (updateTree === undefined) {
			updateTree = true;
		}

		if ($scope.log) {
			console.log("applyCodeBlockPHP()", componentId);
		}

		// update Tree
		if (updateTree) {
			$scope.setOption(componentId, 'ct_code_block', 'code-php', false, false);
		}
			
		var selector 	= "#" + $scope.component.options[componentId]['selector'],
			code 		= $scope.getOption('code-php', componentId);
		
		$scope.execCode(code, selector, $scope.insertElementContent);
	}


	/**
	 * Get executed code block JS
	 * 
	 * @since 0.3.1
	 * @author Ilya K.
	 */

	$scope.applyCodeBlockJS = function(componentId, updateTree) {

		$scope.applyingComponentJS = true;
        jQuery(".ct-js-error-container", "#ct-toolbar").hide().html("");

		if (componentId === undefined) {
			componentId = $scope.component.active.id;
		}

		if (updateTree === undefined) {
			updateTree = true;
		}

		if ($scope.log) {
			console.log("applyCodeBlockJS()", componentId, updateTree);
		}

		// update Tree
		if (updateTree) {
			$scope.setOption(componentId, 'ct_code_block', 'code-js', false, false);
		}
		
		// output to DOM
		code = $scope.getOption('code-js', componentId);
		$scope.outputJSScript("js-code-", componentId, code)

		$scope.applyingComponentJS = false;
	}


	/**
	 * Get executed code block CSS
	 * 
	 * @since 0.3.1
	 * @author Ilya K.
	 */

	$scope.applyCodeBlockCSS = function(componentId, updateTree) {

		if (componentId === undefined) {
			componentId = $scope.component.active.id;
		}

		if (updateTree === undefined) {
			updateTree = true;
		}

		if ($scope.log) {
			console.log("applyCodeBlockCSS()", componentId, updateTree);
		}

		// update Tree
		if (updateTree) {
			$scope.setOption(componentId, 'ct_code_block', 'code-css', false, false);
		}

		// output to <head>
		code = $scope.getOption('code-css', componentId);
		
		var selector = $scope.component.options[componentId]['selector'];
    	code = code.replace(new RegExp("%%ELEMENT_ID%%", 'g'), selector);

		$scope.outputCSSStyles("ct_code_block_css_"+componentId, code);
	}


	/**
     * Append <script> element to DOM with passed code
     *
     * @since 0.3.1
     * @author Ilya K.
     */
    
    $scope.outputJSScript = function(name, id, code) {

    	if ($scope.log){
    		console.log("outputJSScript()", name, id)
    	}

    	// replace %%ELEMENT_ID%% with actual id attribute
    	var selector = $scope.component.options[id]['selector'];

    	code = code.replace(new RegExp("%%ELEMENT_ID%%", 'g'), selector);
    	
    	// remove old script
    	var oldScript = document.getElementById(name+id);
    	oldScript && document.body.removeChild(oldScript);
    	
    	// create new script
    	var script = document.createElement('script');
		script.type = 'text/javascript';
		script.setAttribute("id", name+id);

		try {
			script.appendChild(document.createTextNode(code));
			document.body.appendChild(script);
		} catch (e) {
			script.text = code;
			document.body.appendChild(script);
		}
    }

    /**
     * Add window.onerror function to output errors as notice in JS tab
     * 
     * @since 1.0.1
     * @author Ilya K.
     */
    
    $scope.setupJSErrorNotice = function() {

    	window.onerror = function myErrorHandler(errorMsg, url, lineNumber) {
            if ( $scope.applyingComponentJS ) {
                jQuery(".ct-js-error-container", "#ct-toolbar").show().html(errorMsg+" on line #"+lineNumber);
            }
        }
    }


	/**
     * Insert text/html content to any HTML element by selector
     *
     * @since 0.2.4
     * @author Ilya K.
     */
    
    $scope.insertElementContent = function(result, placholderSelector) {

    	if ($scope.log) {
    		console.log("insertElementContent()", result, placholderSelector)
    	}

        var component = angular.element(placholderSelector);

        component.html("");
        $scope.cleanInsert("<span>"+result+"</span>", component);
    }


    /**
     * Get ID of a Link Wrapper
     *
     * @since 0.3.1
     * @author Ilya K.
     */
    
    $scope.getLinkId = function(id) {

    	if ( undefined === id ) {
    		id = $scope.component.active.id;
    	}

    	// get closest parent
    	var link = jQuery("[ng-attr-component-id='"+id+"']", "#ct-builder").closest(".ct-links");

    	// link found
    	if (link.length > 0) {
    		return link.attr("ng-attr-component-id");
    	}
    	// not found
    	else {
    		return false;
    	}
    }
    

    /**
     * Get ID of a Link Wrapper
     *
     * @since 0.3.1
     * @author Ilya K.
     */
    
    $scope.addSeparator = function(id) {

    	$scope.separatorAdded = true;
    }


    /**
     * Add classes styles to components tree to pass with API
     *
     * @since 0.4.0
     * @author Ilya K.
     */
    
    $scope.addComponentClassesStyles = function(component) {

    	if (component.options && component.options.classes !== undefined ) {
    		for (var className in $scope.classes) {
				if (component.options && component.options.classes.indexOf(className) > -1) {
					
					var index = component.options.classes.indexOf(className);
					component.options.classes.splice(index,1);
					
					component.options.classes.push({
						'name': className,
						'styles': $scope.classes[className]
					})
				}
			}
		}
		
		// loop children
		for(var key in component.children) { 
			if (component.children.hasOwnProperty(key)) {
				// get child
				var child = component.children[key];
				$scope.addComponentClassesStyles(child);
			}
		}
    }


    /**
     * Parse components tree from API to add classes styles
     *
     * @since 0.4.0
     * @author Ilya K.
     */
    
    $scope.parseTreeClassesStyles = function(component) {

    	if (component.options && component.options.classes !== undefined ) {
    		for (var key in component.options.classes) { 
				if ( component.options.classes.hasOwnProperty(key)) {

					if (component.options.classes[key]["name"] != undefined) {
						var className = component.options.classes[key]["name"],
							styles = component.options.classes[key]["styles"];
						
						// add class name back to the tree
						component.options.classes[key] = className;

						// check if class exist
						if ( $scope.classes[className] !== undefined ) {
							//console.log("'"+className+"' class already exist in your install. Styles for this class won't be added.");
						}
						else {
							// add class styles to global classes object
							$scope.classes[className] = styles;
						}
					}
				}
			}
			$scope.classesCached = false;
			$scope.outputCSSOptions();
		}
		
		// loop children
		for(var key in component.children) { 
			if (component.children.hasOwnProperty(key)) {
				// get child
				var child = component.children[key];
				$scope.parseTreeClassesStyles(child);
			}
		}
	}

});