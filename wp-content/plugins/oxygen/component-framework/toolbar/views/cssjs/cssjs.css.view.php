<div class="fake-code-mirror cm-s-default">
	<pre class="CodeMirror-line"><span ng-show="isEditing('id')" style="padding-right: 0.1px;"><span class="cm-builtin">#{{component.options[component.active.id]['selector']}}{{currentState !== "original" ? ":"+currentState : ""}}</span>{</span><span ng-show="isEditing('class')" style="padding-right: 0.1px;"><span class="cm-builtin">.{{currentClass}}{{currentState !== "original" ? ":"+currentState : ""}}</span>{</span><span ng-show="isEditing('custom-selector')" style="padding-right: 0.1px;"><span class="cm-builtin">{{selectorToEdit}}{{currentState !== "original" ? ":"+currentState : ""}}</span>{</span></pre>
</div>

<textarea ui-codemirror="{
      lineNumbers: true,
      mode: 'css',
      type: 'custom-css',
      onLoad : codemirrorLoaded
    }" <?php $this->ng_attributes('custom-css','model'); ?>
    ng-change="applyComponentCSS()"></textarea>

<div class="fake-code-mirror fake-code-mirror-last cm-s-default">
	<pre class="CodeMirror-line"><span style="padding-right: 0.1px;">}</span></pre>
</div>

<div class="ct-button ct-control-button ct-apply-code-button ct-apply-component-css-button"
	ng-click="applyComponentCSS()">
	APPLY
</div>
<div data-collapse="Collapse" data-expand="Expand" class="ct-button ct-control-button ct-expand-button"
  ng-click="toggleExpandEditor($event)">
  Expand
</div>