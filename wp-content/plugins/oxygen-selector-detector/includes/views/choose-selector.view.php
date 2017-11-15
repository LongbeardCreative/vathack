<div class="ct-choose-selector-wrap" ng-class="{'ct-choose-selector-active':selectorDetector.chooseBox}" ng-controller="ControllerSelectorDetector">
	<div class="ct-choose-selector" ct-float-editor>
		<div class="ct-choose-selector-heading ct-draggable-handle">
			<?php _e("::: Choose a selector", "component-theme"); ?>
		</div>
		<div class="ct-hide-choose-selector-icon" ng-click="selectorDetector.chooseBox=false"></div>
		<select ng-show="appliedSelectors.length>0" ng-change="parseAppliedSelectors(chooseSelectorBoxValue)" ng-model="chooseSelectorBoxValue" class="ct-applied-selectors-list ct-select">
			<option ng-repeat="selector in appliedSelectors" ng-value="selector">
				{{selector}}
			</option>
		</select>
		<div id="ct-choose-selector-content">
		</div>
		<div class="ct-choose-selector-bottom-options">
			<label>
				<input type="radio" ng-model="chooseSelectorBehavior" value="update">
				<?php _e("Overwrite Current Selector, Keep Styles","component-theme"); ?>
			</label><br/>
			<label>
				<input type="radio" ng-model="chooseSelectorBehavior" value="create">
				<?php _e("Create A New Selector","component-theme"); ?>
			</label><br/>
		</div>
	</div>
</div>