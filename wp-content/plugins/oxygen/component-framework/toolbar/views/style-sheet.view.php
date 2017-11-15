<textarea ui-codemirror="{
      lineNumbers: true,
      newlineAndIndent: false,
      mode: 'css',
      type: 'stylesheet',
      onLoad : codemirrorLoaded
    }" ng-model="styleSheets[stylesheetToEdit]"
    ng-change="applyStyleSheet(stylesheetToEdit)"></textarea>

<div class="ct-button ct-control-button ct-apply-code-button ct-apply-css-button" 
	ng-click="applyStyleSheet(stylesheetToEdit)">
	APPLY
</div>
<div data-collapse="Collapse" data-expand="Expand" class="ct-button ct-control-button ct-expand-button"
  ng-click="toggleExpandEditor($event)">
  Expand
</div>
<div class="ct-js-error-container"></div>