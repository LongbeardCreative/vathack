<div class="ct-panel-column">
	<ul class="ct-size-details">
		<li ng-hide="isActiveName('ct_column')&&isEditing('media')">
			<h3><?php _e("Width", "component-theme"); ?></h3>
			<div class="ct-measurebox-container clearfix"
				ng-class="{'ct-word-selected':getOptionUnit('width')=='auto'}"
				ng-show="isActiveName('ct_column')">
				<div class="ct-measurebox-wrap">
					<div class="ct-measurebox ct-column-width"
						ng-class="isInherited(component.active.id, 'width')">
						<input class="ct-measure-value ct-number-font" type="text" spellcheck="false"
							ng-change="setOption(component.active.id,'ct_column','width'); updateColumnsOnChange(component.active.id,{{component.options[component.active.id]['model']['width']}})"
							<?php $this->ng_attributes('width',"class,model"); ?>/>
						<div class="ct-measure-type">{{getOptionUnit('width')}}</div>
					</div>
				</div>
			</div>
			<div class="ct-measurebox-container clearfix"
				ng-class="{'ct-word-selected':getOptionUnit('width')=='auto'}"
				ng-hide="isActiveName('ct_column')">
				<div class="ct-measurebox-wrap">
					<div class="ct-measurebox">
						<input class="ct-measure-value ct-number-font" type="text" spellcheck="false"
							<?php $this->ng_attributes('width'); ?> />
						<div class="ct-measure-type">{{getOptionUnit('width')}}</div>
					</div>
					<?php $this->measure_type_select('width'); ?>
				</div>
			</div>
		</li>
		<li>
			<h3><?php _e("Min-width", "component-theme"); ?></h3>
			<div class="ct-measurebox-container clearfix"
				ng-class="{'ct-word-selected':getOptionUnit('min-width')=='auto'}">
				<div class="ct-measurebox-wrap">
					<div class="ct-measurebox">
						<input class="ct-measure-value ct-number-font" type="text" spellcheck="false"
							<?php $this->ng_attributes('min-width'); ?> />
						<div class="ct-measure-type">{{getOptionUnit('min-width')}}</div>
					</div>
					<?php $this->measure_type_select('min-width'); ?>
				</div>
			</div>
		</li>
		<li>
			<h3><?php _e("Max-width", "component-theme"); ?></h3>
			<div class="ct-measurebox-container clearfix"
				ng-class="{'ct-word-selected':getOptionUnit('max-width')=='auto'}">
				<div class="ct-measurebox-wrap">
					<div class="ct-measurebox">
						<input class="ct-measure-value ct-number-font" type="text" spellcheck="false"
							<?php $this->ng_attributes('max-width'); ?> />
						<div class="ct-measure-type">{{getOptionUnit('max-width')}}</div>
					</div>
					<?php $this->measure_type_select('max-width'); ?>
				</div>
			</div>
		</li>
	</ul>
</div>
<div class="ct-panel-column">
	<ul class="ct-size-details">
		<li>
			<h3><?php _e("Height", "component-theme"); ?></h3>
			<div class="ct-measurebox-container clearfix"
				ng-class="{'ct-word-selected':getOptionUnit('height')=='auto'}">
				<div class="ct-measurebox-wrap">
					<div class="ct-measurebox">
						<input class="ct-measure-value ct-number-font" type="text" spellcheck="false"
							<?php $this->ng_attributes('height'); ?> />
						<div class="ct-measure-type">{{getOptionUnit('height')}}</div>
					</div>
					<?php $this->measure_type_select('height'); ?>
				</div>
			</div>	
		</li>
		<li>
			<h3><?php _e("Min-height", "component-theme"); ?></h3>
			<div class="ct-measurebox-container clearfix"
				ng-class="{'ct-word-selected':getOptionUnit('min-height')=='auto'}">
				<div class="ct-measurebox-wrap">
					<div class="ct-measurebox">
						<input class="ct-measure-value ct-number-font" type="text" spellcheck="false"
							<?php $this->ng_attributes('min-height'); ?> />
						<div class="ct-measure-type">{{getOptionUnit('min-height')}}</div>
					</div>
					<?php $this->measure_type_select('min-height'); ?>
				</div>
			</div>	
		</li>
		<li>
			<h3><?php _e("Max-height", "component-theme"); ?></h3>
			<div class="ct-measurebox-container clearfix"
				ng-class="{'ct-word-selected':getOptionUnit('max-height')=='auto'}">
				<div class="ct-measurebox-wrap">
					<div class="ct-measurebox">
						<input class="ct-measure-value ct-number-font" type="text" spellcheck="false"
							<?php $this->ng_attributes('max-height'); ?> />
						<div class="ct-measure-type">{{getOptionUnit('max-height')}}</div>
					</div>
					<?php $this->measure_type_select('max-height'); ?>
				</div>
			</div>	
		</li>
	</ul>
</div>