<div class="ct-panel-column ct-float-left">
	
	<div class="ct-panel-options-wrap clearfix">
		<div class="ct-option-container ct-float-left">
			<h3><?php _e("Color","component-theme"); ?></h3>
			<div class="ct-colorpicker">
				<input class="ct-color"
					readonly colorpicker="rgba" colorpicker-fixed-position="true" type="text"
					<?php $this->ng_attributes("border-'+currentBorder+'-color",'model,change'); ?>
					ng-style="{'background-color':getOption('border-'+currentBorder+'-color'),'color':getOption('border-'+currentBorder+'-color')}"/>
			</div>
		</div>
		<div class="ct-option-container ct-float-left">
			<h3>&nbsp;</h3>
			<div class="ct-textbox ct-textbox-big" ng-class="isInherited(component.active.id, 'border-'+currentBorder+'-color')">
				<input type="text"
					<?php $this->ng_attributes("border-'+currentBorder+'-color",'model,change'); ?>/>
			</div>
		</div>
		<div class="ct-option-container ct-float-left">
			<h3><?php _e("Size","component-theme"); ?></h3>
			<div class="ct-measurebox-container"
				ng-class="{'ct-word-selected':getOptionUnit('border-'+currentBorder+'-width')=='auto'}">
				<div class="ct-measurebox-wrap">
					<div class="ct-measurebox" ng-class="isInherited(component.active.id, 'border-'+currentBorder+'-width')">
						<input class="ct-measure-value ct-number-font" type="text"
							<?php $this->ng_attributes("border-'+currentBorder+'-width"); ?> />
						<div class="ct-measure-type">{{getOptionUnit('border-'+currentBorder+'-width')}}</div>
					</div>
					<?php $this->measure_type_select("border-'+currentBorder+'-width", "px,em"); ?>
				</div>
			</div>
		</div>
	</div>
	
	<div class="ct-panel-options-wrap clearfix">
		<h3><?php _e("Display", "component-theme"); ?></h3>
		<ul class="ct-button-list">
			<li>
				<label>
					<input type="radio" name="border-style" value="none"
						<?php $this->ng_attributes("border-'+currentBorder+'-style", 'model,change'); ?>
						ng-click="radioButtonClick(component.active.name, 'border-'+currentBorder+'-style', 'none')"
						ng-class="isInherited(component.active.id, 'border-'+currentBorder+'-style', 'none')"/>
					<div class="ct-button ct-control-button">
						<span class="ct-icon ct-inline-icon"></span> none
					</div>
				</label>
			</li>
			<li>
				<label>
					<input type="radio" name="border-style" value="solid"
						<?php $this->ng_attributes("border-'+currentBorder+'-style", 'model,change'); ?>
						ng-click="radioButtonClick(component.active.name, 'border-'+currentBorder+'-style', 'solid')"
						ng-class="isInherited(component.active.id, 'border-'+currentBorder+'-style', 'solid')"/>
					<div class="ct-button ct-control-button">
						<span class="ct-icon ct-inline-block-icon"></span> solid
					</div>
				</label>
			</li>
			<li>
				<label>
					<input type="radio" name="border-style" value="dashed"
						<?php $this->ng_attributes("border-'+currentBorder+'-style", 'model,change'); ?>
						ng-click="radioButtonClick(component.active.name, 'border-'+currentBorder+'-style', 'dashed')"
						ng-class="isInherited(component.active.id, 'border-'+currentBorder+'-style', 'dashed')"/>
					<div class="ct-button ct-control-button">
						<span class="ct-icon ct-block-icon"></span> dashed
					</div>
				</label>
			</li>
			<li>
				<label>
					<input type="radio" name="border-style" value="dotted"
						<?php $this->ng_attributes("border-'+currentBorder+'-style", 'model,change'); ?>
						ng-click="radioButtonClick(component.active.name, 'border-'+currentBorder+'-style', 'dotted')"
						ng-class="isInherited(component.active.id, 'border-'+currentBorder+'-style', 'dotted')"/>
					<div class="ct-button ct-control-button">
						<span class="ct-icon ct-none-icon"></span> dotted
					</div>
				</label>
			</li>
		</ul>
	</div>

</div><!-- .ct-panel-column -->	

<div class="ct-panel-column">
	<h3><?php _e("Currently editing", "component-theme"); ?></h3>
	<div class="ct-size-box">
		<span class="ct-size-box-name">
			<div class="ct-button ct-control-button"
				ng-click="currentBorder='all'"
				ng-class="{'ct-active':currentBorder=='all'}">
				<?php _e("all borders", "component-theme"); ?>
			</div>
		</span>
		<div class="ct-measurebox-container ct-top-measure">
			<div class="ct-button ct-control-button"
				ng-click="currentBorder='top'"
				ng-class="{'ct-active':currentBorder=='top'}">
				top <span ng-show="isBorderHasStyles('top')" class="ct-icon ct-asteriks-icon"></span>
			</div>
		</div>
		<div class="ct-measurebox-container ct-right-measure ct-measurebox-with-options">
			<div class="ct-button ct-control-button"
				ng-click="currentBorder='right'"
				ng-class="{'ct-active':currentBorder=='right'}">
				right <span ng-show="isBorderHasStyles('right')" class="ct-icon ct-asteriks-icon"></span>
			</div>
		</div>
		<div class="ct-measurebox-container ct-bottom-measure ct-measurebox-with-options">
			<div class="ct-button ct-control-button"
				ng-click="currentBorder='bottom'"
				ng-class="{'ct-active':currentBorder=='bottom'}">
				bottom <span ng-show="isBorderHasStyles('bottom')" class="ct-icon ct-asteriks-icon"></span>
			</div>
		</div>
		<div class="ct-measurebox-container ct-left-measure ct-measurebox-with-options">
			<div class="ct-button ct-control-button"
				ng-click="currentBorder='left'"
				ng-class="{'ct-active':currentBorder=='left'}">
				left <span ng-show="isBorderHasStyles('left')" class="ct-icon ct-asteriks-icon"></span>
			</div>
		</div>
	</div>
</div>