<div ng-repeat="set in notSorted(styleSets)">
	<div class="ct-css-node-header ct-node-options-active ct-style-set-node"
		ng-dblclick="expandedStyleSets[styleSets[set]]=!expandedStyleSets[styleSets[set]]"
		ng-class="{'ct-active-style-set':selectorToEdit==selector,'ct-style-set-expanded':expandedStyleSets[styleSets[set]]}">
		<span class="ct-icon ct-dom-parent-icon"></span>
		{{styleSets[set]}}
		<div class="ct-node-options">
			<span class="ct-icon ct-delete-icon"
				title="<?php _e("Delete style set and all its selector", "component-theme"); ?>"
				ng-click="deleteStyleSet(set)"></span>
		</div>
	</div>
	<div class="ct-style-set-child-selector">
		<div class="ct-css-node-header ct-node-options-active"
			ng-repeat="selector in notSorted(customSelectors)"
			ng-show="customSelectors[selector]['set_name']==styleSets[set]"
			ng-click="setCustomSelectorToEdit(selector);$parent.disableSelectorDetectorMode()"
			ng-class="{'ct-active-selector':selectorToEdit==selector}">
			{{customSelectors[selector]['friendly_name'] || selector}}
			<div class="ct-node-options">
				<span class="ct-icon ct-visible-icon"
					ng-click="highlightSelector(true,selector,$event)"
					title="<?php _e("Highlight selector", "component-theme"); ?>">
					</span>
				<!-- <span class="ct-icon ct-copy-item-icon"></span> -->
				<span class="ct-icon ct-delete-icon"
					title="<?php _e("Delete selector", "component-theme"); ?>"
					ng-click="deleteCustomSelector(selector,$event)"></span>
			</div>
		</div>
	</div>
</div>