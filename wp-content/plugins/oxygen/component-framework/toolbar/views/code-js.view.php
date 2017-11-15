<textarea ui-codemirror="{
      lineNumbers: true,
      newlineAndIndent: false,
      mode: 'javascript',
      type: 'js',
      onLoad : codemirrorLoaded
    }" <?php $this->ng_attributes('code-js','model'); ?>></textarea>

<div class="ct-button ct-control-button ct-apply-code-button ct-apply-js-button"
	ng-click="applyCodeBlockJS()">
	APPLY
</div>
<div data-collapse="Collapse" data-expand="Expand" class="ct-button ct-control-button ct-expand-button"
  ng-click="toggleExpandEditor($event)">
  Expand
</div>
<div class="ct-js-error-container"></div>