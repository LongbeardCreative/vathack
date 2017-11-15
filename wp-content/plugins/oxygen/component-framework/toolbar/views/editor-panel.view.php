<div class="ct-editor-panel-wrap">
<div class="ct-editor-panel" ct-float-editor
	ng-if="isActiveActionTab('contentEditing')">
	<div class="ct-float-left ct-option-container">
		<div class="ct-button ct-control-button ct-icon-only"
			ng-edit-role='bold' ct-edit-button title="<?php _e( 'Bold', 'component-theme' ); ?>">
			<span class="ct-icon ct-bold-icon"></span>			
		</div>
	</div>

	<div class="ct-float-left ct-option-container">
		<div class="ct-button ct-control-button ct-icon-only"
			ng-edit-role='italic' ct-edit-button title="<?php _e( 'Italic', 'component-theme' ); ?>">
			<span class="ct-icon ct-italic-icon"></span>			
		</div>
	</div>

	<div class="ct-float-left ct-option-container">
		<div class="ct-button ct-control-button ct-icon-only"
			ng-edit-role='underline' ct-edit-button title="<?php _e( 'Underline', 'component-theme' ); ?>">
			<span class="ct-icon ct-underline-icon"></span>			
		</div>
	</div>

	<div class="ct-float-left ct-option-container">
		<div class="ct-button ct-control-button ct-icon-only"
			ng-edit-role='link' ct-edit-button title="<?php _e( 'Link', 'component-theme' ); ?>">
			<span class="ct-icon ct-link-icon"></span>			
		</div>
	</div>

	<div class="ct-float-left ct-option-container">
		<div class="ct-button ct-control-button ct-icon-only"
			ng-edit-role='justifyleft' ct-edit-button title="<?php _e( 'Align Left', 'component-theme' ); ?>">
			<span class="ct-icon ct-paragraph-left-icon"></span>			
		</div>
	</div>

	<div class="ct-float-left ct-option-container">
		<div class="ct-button ct-control-button ct-icon-only"
			ng-edit-role='justifycenter' ct-edit-button title="<?php _e( 'Align Center', 'component-theme' ); ?>">
			<span class="ct-icon ct-paragraph-center-icon"></span>			
		</div>
	</div>

	<div class="ct-float-left ct-option-container">
		<div class="ct-button ct-control-button ct-icon-only"
			ng-edit-role='justifyright' ct-edit-button title="<?php _e( 'Align Right', 'component-theme' ); ?>">
			<span class="ct-icon ct-paragraph-right-icon"></span>			
		</div>
	</div>

	<div class="ct-float-left ct-option-container">
		<div class="ct-button ct-control-button ct-icon-only"
			ng-edit-role='undo' ct-edit-button title="<?php _e( 'Undo', 'component-theme' ); ?>">
			<span class="ct-icon ct-back-icon"></span>			
		</div>
	</div>

	<div class="ct-float-left ct-option-container">
		<div class="ct-button ct-control-button ct-icon-only"
			ng-edit-role='redo' ct-edit-button title="<?php _e( 'Redo', 'component-theme' ); ?>">
			<span class="ct-icon ct-forward-icon"></span>			
		</div>
	</div>

	<div class="ct-float-left ct-option-container">
		<div class="ct-button ct-control-button ct-icon-only"
			ng-edit-role='removeFormat' ct-edit-button title="<?php _e( 'Remove Format', 'component-theme' ); ?>">
			<span class="ct-icon ct-none-icon"></span>			
		</div>
	</div>

	<div class="ct-float-left ct-option-container">
		<div class="ct-button ct-control-button" title="<?php _e('Wrap with Span component', 'component-theme'); ?>"
			ng-show="component.active.name != 'ct_span'"
			ng-mousedown="wrapWithSpan()">
			&lt;span&gt;
		</div>
	</div>

	<div class="ct-float-left ct-option-container">
		<div class="ct-button ct-control-button" title="<?php _e('Done Editing', 'component-theme'); ?>"
			ng-mousedown="disableContentEdit()">
			Done
		</div>
	</div>

	<div class="ct-draggable-handle">:::</div>

	<div class="clearfix"></div>
</div>
</div>