<div class="ct-panel-column">
	<h3><?php _e("Display", "component-theme"); ?></h3>
	<ul class="ct-button-list">
		<li>
			<label>
				<input type="radio" name="display" value="inline"
					<?php $this->ng_attributes('display', 'model,change'); ?>
					ng-click="radioButtonClick(component.active.name, 'display', 'inline')"
					ng-class="isInherited(component.active.id, 'display', 'inline')"/>
				<div class="ct-button ct-control-button">
					<span class="ct-icon ct-inline-icon"></span> inline
				</div>
			</label>
		</li>
		<li>
			<label>
				<input type="radio" name="display" value="inline-block"
					<?php $this->ng_attributes('display', 'model,change'); ?>
					ng-click="radioButtonClick(component.active.name, 'display', 'inline-block')"
					ng-class="isInherited(component.active.id, 'display', 'inline-block')"/>
				<div class="ct-button ct-control-button">
					<span class="ct-icon ct-inline-block-icon"></span> inline-block
				</div>
			</label>
		</li>
		<li>
			<label>
				<input type="radio" name="display" value="block"
					<?php $this->ng_attributes('display', 'model,change'); ?>
					ng-click="radioButtonClick(component.active.name, 'display', 'block')"
					ng-class="isInherited(component.active.id, 'display', 'block')"/>
				<div class="ct-button ct-control-button">
					<span class="ct-icon ct-block-icon"></span> block
				</div>
			</label>
		</li>
		<li>
			<label>
				<input type="radio" name="display" value="none"
					<?php $this->ng_attributes('display', 'model,change'); ?>
					ng-click="radioButtonClick(component.active.name, 'display', 'none')"
					ng-class="isInherited(component.active.id, 'display', 'none')"/>
				<div class="ct-button ct-control-button">
					<span class="ct-icon ct-none-icon"></span> none
				</div>
			</label>
		</li>
	</ul>

	<h3><?php _e("Float", "component-theme"); ?></h3>
	<ul class="ct-button-list">
		<li>
			<label>
				<input type="radio" name="float" value="none"
					<?php $this->ng_attributes('float', 'model,change'); ?>
					ng-click="radioButtonClick(component.active.name, 'float', 'none')"
					ng-class="isInherited(component.active.id, 'float', 'none')"/>
				<div class="ct-button ct-control-button">
					<span class="ct-icon ct-none-icon"></span> none
				</div>
			</label>
		</li>
		<li>
			<label>
				<input type="radio" name="float" value="left"
					<?php $this->ng_attributes('float', 'model,change'); ?>
					ng-click="radioButtonClick(component.active.name, 'float', 'left')"
					ng-class="isInherited(component.active.id, 'float', 'left')"/>
				<div class="ct-button ct-control-button">
					<span class="ct-icon ct-left-float-icon"></span> left
				</div>
			</label>
		</li>
		<li>
			<label>
				<input type="radio" name="float" value="right"
					<?php $this->ng_attributes('float', 'model,change'); ?>
					ng-click="radioButtonClick(component.active.name, 'float', 'right')"
					ng-class="isInherited(component.active.id, 'float', 'right')"/>
				<div class="ct-button ct-control-button">
					<span class="ct-icon ct-right-float-icon"></span> right
				</div>
			</label>
		</li>
	</ul>

	<h3><?php _e("Visibility", "component-theme"); ?></h3>
	<ul class="ct-button-list">
		<li>
			<label>
				<input type="radio" name="visibility" value="visible"
					<?php $this->ng_attributes('visibility', 'model,change'); ?>
					ng-click="radioButtonClick(component.active.name, 'visibility', 'visible')"
					ng-class="isInherited(component.active.id, 'visibility', 'visible')"/>
				<div class="ct-button ct-control-button">
					<span class="ct-icon ct-visible-icon"></span> visible
				</div>
			</label>
		</li>
		<li>
			<label>
				<input type="radio" name="visibility" value="hidden"
					<?php $this->ng_attributes('visibility', 'model,change'); ?>
					ng-click="radioButtonClick(component.active.name, 'visibility', 'hidden')"
					ng-class="isInherited(component.active.id, 'visibility', 'hidden')"/>
				<div class="ct-button ct-control-button">
					<span class="ct-icon ct-hidden-icon"></span> hidden
				</div>
			</label>
		</li>
	</ul>

</div>
<div class="ct-panel-column">

	<h3><?php _e("Clear", "component-theme"); ?></h3>
	<ul class="ct-button-list">
		<li>
			<label>
				<input type="radio" name="clear" value="none"
					<?php $this->ng_attributes('clear', 'model,change'); ?>
					ng-click="radioButtonClick(component.active.name, 'clear', 'none')"
					ng-class="isInherited(component.active.id, 'clear', 'none')"/>
				<div class="ct-button ct-control-button">
					<span class="ct-icon ct-none-icon"></span> none
				</div>
			</label>
		</li>
		<li>
			<label>
				<input type="radio" name="clear" value="left"
					<?php $this->ng_attributes('clear', 'model,change'); ?>
					ng-click="radioButtonClick(component.active.name, 'clear', 'left')"
					ng-class="isInherited(component.active.id, 'clear', 'left')"/>
				<div class="ct-button ct-control-button">
					<span class="ct-icon ct-left-clear-icon"></span> left
				</div>
			</label>
		</li>
		<li>
			<label>
				<input type="radio" name="clear" value="right"
					<?php $this->ng_attributes('clear', 'model,change'); ?>
					ng-click="radioButtonClick(component.active.name, 'clear', 'right')"
					ng-class="isInherited(component.active.id, 'clear', 'right')"/>
				<div class="ct-button ct-control-button">
					<span class="ct-icon ct-right-clear-icon"></span> right
				</div>
			</label>
		</li>
		<li>
			<label>
				<input type="radio" name="clear" value="both"
					<?php $this->ng_attributes('clear', 'model,change'); ?>
					ng-click="radioButtonClick(component.active.name, 'clear', 'both')"
					ng-class="isInherited(component.active.id, 'clear', 'both')"/>
				<div class="ct-button ct-control-button">
					<span class="ct-icon ct-both-clear-icon"></span> both
				</div>
			</label>
		</li>
	</ul>

	<h3><?php _e("Overflow", "component-theme"); ?></h3>
	<ul class="ct-button-list">
		<li>
			<label>
				<input type="radio" name="overflow" value="visible"
					<?php $this->ng_attributes('overflow', 'model,change'); ?>
					ng-click="radioButtonClick(component.active.name, 'overflow', 'visible')"
					ng-class="isInherited(component.active.id, 'overflow', 'visible')"/>
				<div class="ct-button ct-control-button">
					<span class="ct-icon ct-visible-icon"></span> visible
				</div>
			</label>
		</li>
		<li>
			<label>
				<input type="radio" name="overflow" value="hidden"
					<?php $this->ng_attributes('overflow', 'model,change'); ?>
					ng-click="radioButtonClick(component.active.name, 'overflow', 'hidden')"
					ng-class="isInherited(component.active.id, 'overflow', 'hidden')"/>
				<div class="ct-button ct-control-button">
					<span class="ct-icon ct-hidden-icon"></span> hidden
				</div>
			</label>
		</li>
		<li>
			<label>
				<input type="radio" name="overflow" value="scroll"
					<?php $this->ng_attributes('overflow', 'model,change'); ?>
					ng-click="radioButtonClick(component.active.name, 'overflow', 'scroll')"
					ng-class="isInherited(component.active.id, 'overflow', 'scroll')"/>
				<div class="ct-button ct-control-button">
					<span class="ct-icon ct-scroll-icon"></span> scroll
				</div>
			</label>
		</li>
	</ul>
</div>

<div class="ct-panel-column">
	<h3><?php _e("Position", "component-theme"); ?></h3>
	<ul class="ct-button-list">
		<li>
			<label>
				<input type="radio" name="position" value="static"
					<?php $this->ng_attributes('position', 'model,change'); ?>
					ng-click="radioButtonClick(component.active.name, 'position', 'static')"
					ng-class="isInherited(component.active.id, 'position', 'static')"/>
				<div class="ct-button ct-control-button">
					<span class="ct-icon ct-static-icon"></span> static
				</div>
			</label>
		</li>
		<li>
			<label>
				<input type="radio" name="position" value="absolute"
					<?php $this->ng_attributes('position', 'model,change'); ?>
					ng-click="radioButtonClick(component.active.name, 'position', 'absolute')"
					ng-class="isInherited(component.active.id, 'position', 'absolute')"/>
				<div class="ct-button ct-control-button">
					<span class="ct-icon ct-absolute-icon"></span> absolute
				</div>
			</label>
		</li>
		<li>
			<label>
				<input type="radio" name="position" value="relative"
					<?php $this->ng_attributes('position', 'model,change'); ?>
					ng-click="radioButtonClick(component.active.name, 'position', 'relative')"
					ng-class="isInherited(component.active.id, 'position', 'relative')"/>
				<div class="ct-button ct-control-button">
					<span class="ct-icon ct-relative-icon"></span> relative
				</div>
			</label>
		</li>
	</ul>

	<div class="ct-size-box ct-position-size-box" ng-show="component.options[component.active.id]['model']['position']=='absolute'||component.options[component.active.id]['model']['position']=='relative'">
		<div class="ct-measurebox-descriptor ct-top-measurebox-descriptor">top</div>
		<div class="ct-measurebox-descriptor ct-right-measurebox-descriptor">right</div>
		<div class="ct-measurebox-descriptor ct-bottom-measurebox-descriptor">bottom</div>
		<div class="ct-measurebox-descriptor ct-left-measurebox-descriptor">left</div>

		<div class="ct-size-box-logo ct-icon ct-static-icon"></div>
		
		<div class="ct-measurebox-container ct-top-measure"
			ng-class="{'ct-word-selected':getOptionUnit('top')=='auto'}">
			<div class="ct-measurebox-wrap">
				<div class="ct-measurebox">
					<input class="ct-measure-value ct-number-font" type="text" spellcheck="false"
						<?php $this->ng_attributes('top'); ?> />
					<div class="ct-measure-type">{{getOptionUnit('top')}}</div>
				</div>
			</div>
			<?php $this->measure_type_select('top'); ?>
		</div>
		<div class="ct-measurebox-container ct-right-measure"
			ng-class="{'ct-word-selected':getOptionUnit('right')=='auto'}">
			<div class="ct-measurebox-wrap">
				<div class="ct-measurebox">
					<input class="ct-measure-value ct-number-font" type="text" spellcheck="false"
						<?php $this->ng_attributes('right'); ?> />
					<div class="ct-measure-type">{{getOptionUnit('right')}}</div>
				</div>
			</div>
			<?php $this->measure_type_select('right'); ?>
		</div>
		<div class="ct-measurebox-container ct-bottom-measure"
			ng-class="{'ct-word-selected':getOptionUnit('bottom')=='auto'}">
			<div class="ct-measurebox-wrap">
				<div class="ct-measurebox">
					<input class="ct-measure-value ct-number-font" type="text" spellcheck="false"
						<?php $this->ng_attributes('bottom'); ?> />
					<div class="ct-measure-type">{{getOptionUnit('bottom')}}</div>
				</div>
			</div>
			<?php $this->measure_type_select('bottom'); ?>
		</div>
		<div class="ct-measurebox-container ct-left-measure"
			ng-class="{'ct-word-selected':getOptionUnit('left')=='auto'}">
			<div class="ct-measurebox-wrap">
				<div class="ct-measurebox">
					<input class="ct-measure-value ct-number-font" type="text" spellcheck="false"
						<?php $this->ng_attributes('left'); ?> />
					<div class="ct-measure-type">{{getOptionUnit('left')}}</div>
				</div>
			</div>
			<?php $this->measure_type_select('left'); ?>
		</div>					
	</div>
</div>