<h3></h3>
<div class="ct-option-container ct-float-left">
	<div class="ct-colorpicker-inline">
		<input class="ct-color"
			colorpicker="rgba" 
			colorpicker-inline="true" 
			colorpicker-parent="true" 
			readonly type="text"
			<?php $this->ng_attributes('background-color','model,change'); ?>
			ng-style="{'background-color':getOption('background-color')}"/>
	</div>
</div>
<div class="ct-option-container ct-float-left">
	<div class="ct-colorpicker">
		<input class="ct-color"
			ng-style="{'background-color':getOption('background-color')}"/>
	</div>
</div>
<div class="ct-float-left">
	<div class="ct-textbox ct-textbox-big">
		<input type="text" spellcheck="false"
			ng-class="isInherited(component.active.id, 'background-color')"
			<?php $this->ng_attributes('background-color','model,change'); ?>/>
	</div>
</div>
<div class="clearfix"></div>