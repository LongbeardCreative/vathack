<div class="ct-css-node-header ct-node-options-active ct-style-set-selector"
	ng-repeat="selector in notSorted(customSelectors)"
	ng-show="!customSelectors[selector]['set_name']"
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