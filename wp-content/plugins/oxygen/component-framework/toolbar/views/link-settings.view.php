<div class="ct-float-left ct-option-container">
	<div class="ct-inline-option-title">
		<?php _e("URL", "component-theme"); ?>
	</div>
	<div class="ct-inline-block ct-textbox ct-textbox-huge">
		<input type="text" spellcheck="false"
			ng-model="component.options[component.active.id]['model']['url']" 
			ng-change="setOption(component.active.id,'ct_link','url')" />
	</div>
</div>

<div class="ct-float-left ct-option-container">
	<div class="ct-table-cell ct-padding-right">
		<?php _e("Opens in", "component-theme"); ?>
	</div>
	<div class="ct-table-cell ct-selectbox">
		<ul class="ct-select">
			<li class="ct-selected">
				{{component.options[component.active.id]['model']['target']}}
				<span class="ct-icon ct-dropdown-icon"></span>
			</li>
			<li>
				<ul class="ct-dropdown-list">
					<li ng-click="setOptionModel('target', '_self', component.active.id)"><?php _e("Same window", "component-theme"); ?></li>
					<li ng-click="setOptionModel('target', '_blank', component.active.id)"><?php _e("New window", "component-theme"); ?></li>
				</ul>
			</li>									
		</ul>
	</div>
</div>

<div class="ct-float-left ct-option-container">
	<div class="ct-button ct-control-button ct-icon-only" title="<?php _e("Remove link from component", "component-theme"); ?>"
		ng-click="removeLink()">
		<span class="ct-icon ct-none-icon"></span>			
	</div>
</div>