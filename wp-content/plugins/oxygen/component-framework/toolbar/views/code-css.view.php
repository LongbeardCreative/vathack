<textarea ui-codemirror="{
      lineNumbers: true,
      newlineAndIndent: false,
      mode: 'css',
      type: 'css',
      onLoad : codemirrorLoaded
    }" <?php $this->ng_attributes('code-css','model'); ?>
    ng-change="applyCodeBlockCSS();"></textarea>

<div class="ct-button ct-control-button ct-apply-code-button ct-apply-css-button"
	ng-click="applyCodeBlockCSS()">
	APPLY
</div>
<div data-collapse="Collapse" data-expand="Expand" class="ct-button ct-control-button ct-expand-button"
  ng-click="toggleExpandEditor($event)">
  Expand
</div>