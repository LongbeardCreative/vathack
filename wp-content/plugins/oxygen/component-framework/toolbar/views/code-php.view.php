<textarea ui-codemirror="{
      lineNumbers: true,
      newlineAndIndent: false,
      mode: 'php',
      type: 'php',
      onLoad : codemirrorLoaded
    }" <?php $this->ng_attributes('code-php','model'); ?>></textarea>

<div class="ct-button ct-control-button ct-apply-code-button ct-apply-php-button"
	ng-click="applyCodeBlockPHP()">
	APPLY
</div>
<div data-collapse="Collapse" data-expand="Expand" class="ct-button ct-control-button ct-expand-button"
  ng-click="toggleExpandEditor($event)">
  Expand
</div>