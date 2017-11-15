<div id="ct-sidepanel" class="ct-panel-elements-managers" ng-show="showSidePanel" ng-class="{'ct-sidepanel-show':showSidePanel}">
	<div class="ct-elements-managers-head">
		<div class="ct-tabs clearfix">
			<div id="ct-dom-tree-tab" class="ct-tab"
				ng-click="switchTab('sidePanel','DOMTree');"
				ng-class="{'ct-active' : isShowTab('sidePanel','DOMTree')}">
				<?php _e("DOM Tree", "component-theme"); ?>
			</div>

			<div id="ct-stylesheets-manager-tab" class="ct-tab"
				ng-click="switchTab('sidePanel','styleSheets');"
				ng-class="{'ct-active' : isShowTab('sidePanel','styleSheets')}">
				<?php _e("Stylesheets", "component-theme"); ?>
			</div>

			<div id="ct-style-sets-manager-tab" class="ct-tab"
				ng-click="switchTab('sidePanel','selectors');"
				ng-class="{'ct-active' : isShowTab('sidePanel','selectors')}">
				<?php _e("Selectors", "component-theme"); ?>
			</div>
		</div>
	</div>
	<div class="ct-elements-managers-body">
		
		<div class="ct-tab-panel ct-dom-tree-tab ct-active" ng-show="isShowTab('sidePanel','DOMTree')">
			<div class="ct-elements-managers-top clearfix">
				<div class="ct-elements-managers-top-item">
					<div class="ct-textbox ct-searchbox">
						<span class="ct-icon ct-magnify-icon"></span>
						<input type="text" value="" placeholder="Search" ng-change="expandAllNodes()" ng-model="domTreeFilter.domTreeSearchKeyword" />
					</div>
				</div>
				<div class="ct-elements-managers-top-item ct-button ct-butt-expand-all"
					ng-click="expandAllNodes()">
					<?php _e("Expand All", "component-theme"); ?>
				</div>
				<div class="ct-elements-managers-top-item ct-button ct-butt-collapse-all"
					ng-click="collapseAllNodes()">
					<?php _e("Collapse All", "component-theme"); ?>
				</div>
			</div>
			<div id="ct-dom-tree">
				<!-- Content added by Angular -->
			</div>
		</div>
		<!-- .ct-dom-tree-tab -->

		<div class="ct-tab-panel ct-style-sheets-tab" ng-if="isShowTab('sidePanel','styleSheets')">

			<div class="ct-elements-managers-top clearfix">
				<div class="ct-elements-managers-top-item">
					<div class="ct-textbox ct-textbox-big">
						<input type="text" value="" placeholder="Enter stylesheet name" 
							ng-model="stylesheetToAdd.name"/>
					</div>
				</div>
				<div class="ct-elements-managers-top-item">
					<div class="ct-elements-managers-buttons">
						<div class="ct-button ct-icon-right"
							ng-click="addStyleSheet()">
							<?php _e("Add", "component-theme"); ?>
							<span class="ct-icon ct-plus-icon"></span>
						</div>
					</div>
				</div>
				<!-- <div class="ct-elements-managers-top-item ct-float-right">
					<div class="ct-button ct-control-button ct-icon-only">
						<span class="ct-icon ct-magnify-icon"></span>
					</div>
				</div> -->
			</div>

			<div class="ct-elements-managers-bottom">
				<div class="ct-css-node">						        	
					<div class="ct-css-node-header ct-node-options-active"
						ng-repeat="stylesheet in notSorted(styleSheets)"
						
						ng-class="{'ct-active-selector':selectorToEdit=='.'+class}">
						{{stylesheet}}
						<div class="ct-node-options">
            				<span class="ct-node-details"
            					ng-if="isDev()"
            					ng-click="showStyleSheetDialog(stylesheet);" >
            					Add to Design Set
            				</span>
							<span class="ct-icon ct-cssjs-icon"
								ng-click="setStyleSheetToEdit(stylesheet)"
								title="<?php _e("Highlight selector", "component-theme"); ?>">
								</span>
							<!-- <span class="ct-icon ct-copy-item-icon"></span> -->
							<span class="ct-icon ct-delete-icon"
								title="<?php _e("Delete stylesheet", "component-theme"); ?>"
								ng-click="deleteStyleSheet(stylesheet,$event)"></span>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- .ct-style-sheets-tab -->

		<div class="ct-tab-panel ct-selectors-tab" ng-if="isShowTab('sidePanel','selectors')">
			<div class="ct-elements-managers-bottom">
				<div class="ct-css-node">
					<?php
						
						/**
						 * Add-ons hook
						 *
						 * @since 1.4
						 */

						do_action("oxygen_sidepanel_before_classes");
					?>
					
					<!-- classes -->
					<div>
						<div class="ct-css-node-header ct-node-options-active ct-style-set-node"
							ng-dblclick="expandedStyleSets['classes']=!expandedStyleSets['classes']"
							ng-class="{'ct-style-set-expanded':expandedStyleSets['classes']}">
							<span class="ct-icon ct-dom-parent-icon"></span>
							<?php _e("Classes","component-theme"); ?>
						</div>
						<div class="ct-style-set-child-selector">
							<div class="ct-css-node-header ct-node-options-active"
								ng-repeat="class in notSorted(classes)"
								ng-click="setCustomSelectorToEdit('.'+class);"
								ng-class="{'ct-active-selector':selectorToEdit=='.'+class}">
								.{{class}}
								<div class="ct-node-options">
									<span class="ct-icon ct-visible-icon"
										ng-click="highlightSelector(true,'.'+class,$event)"
										title="<?php _e("Highlight selector", "component-theme"); ?>">
										</span>
									<!-- <span class="ct-icon ct-copy-item-icon"></span> -->
									<span class="ct-icon ct-delete-icon"
										title="<?php _e("Delete class and all references", "component-theme"); ?>"
										ng-click="tryDeleteClass(class,$event)"></span>
								</div>
							</div>
						</div>
					</div>
					<!-- /classes -->
					
					<?php
					
						/**
						 * Add-ons hook
						 *
						 * @since 1.4
						 */
					
						do_action("oxygen_sidepanel_after_classes");
					?>
				</div>
			</div>
		</div>
		<!-- .ct-selectors-tab -->

	</div>
</div><!-- .ct-panel-elements-managers -->