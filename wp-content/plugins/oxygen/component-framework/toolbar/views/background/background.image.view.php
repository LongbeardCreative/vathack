<div class="ct-panel-column ct-float-left">
	
	<h3><?php _e( "URL / Upload", "component-theme" ); ?></h3>
	<div class="ct-option-container ct-float-left">
		<div class="ct-textbox ct-textbox-huge ct-textbox-browse">
			<span class="ct-textbox-browse-butt ct-media-button"
							data-mediaTitle="Select Background Image" 
							data-mediaButton="Select Background" 
							data-mediaProperty="background-image">Browse</span>
			<input type="text" spellcheck="false"
				<?php $this->ng_attributes('background-image','model,change'); ?>/>
		</div>
	</div>
	<div class="ct-float-left">
		<div class="ct-button ct-control-button ct-icon-only"
			ng-click="setOptionModel('background-image','')">
			<span class="ct-icon ct-none-icon"></span>
		</div>
	</div>

	<div class="clearfix">
	</div>
	
	<h3><?php _e("Image Overlay Color", "component-theme" ); ?></h3>
	<div class="ct-option-container ct-float-left">
		<div class="ct-colorpicker">
			<input class="ct-color"
				readonly colorpicker="rgba" colorpicker-fixed-position="true" type="text" spellcheck="false"
				<?php $this->ng_attributes('overlay-color','model,change'); ?>
				ng-style="{'background-color':getOption('overlay-color')}"/>
		</div>
	</div>
	
	<div class="ct-float-left">
		<div class="ct-textbox ct-textbox-big">
			<input type="text" spellcheck="false"
				<?php $this->ng_attributes('overlay-color','model,change'); ?>/>
		</div>
	</div>
</div>