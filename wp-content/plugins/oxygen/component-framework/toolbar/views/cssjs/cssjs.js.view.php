<textarea ui-codemirror="{
      lineNumbers: true,
      mode: 'javascript',
      type: 'custom-js',
      onLoad : codemirrorLoaded
    }" <?php $this->ng_attributes('custom-js','model'); ?>></textarea>

<div class="ct-button ct-control-button ct-apply-code-button ct-apply-component-js-button"
	ng-click="applyComponentJS()">
	APPLY
</div>
<div data-collapse="Collapse" data-expand="Expand" class="ct-button ct-control-button ct-expand-button"
  ng-click="toggleExpandEditor($event)">
  Expand
</div>
<div class="ct-js-error-container"></div>