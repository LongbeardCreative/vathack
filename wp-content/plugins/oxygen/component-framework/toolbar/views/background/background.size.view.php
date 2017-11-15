<div class="ct-panel-column">
	<h3><?php _e("Size", "component-theme"); ?></h3>
	<ul class="ct-button-list">
		<li>
			<label>
				<input type="radio" name="background-size" value="auto"
					<?php $this->ng_attributes('background-size', 'model,change'); ?>
					ng-click="radioButtonClick(component.active.name, 'background-size', 'auto')"
					ng-class="isInherited(component.active.id, 'background-size', 'auto')"/>
				<div class="ct-button ct-control-button">
					<span class="ct-icon ct-auto-icon"></span> <?php _e("auto", "component-theme"); ?>
				</div>
			</label>
		</li>
		<li>
			<label>
				<input type="radio" name="background-size" value="cover"
					<?php $this->ng_attributes('background-size', 'model,change'); ?>
					ng-click="radioButtonClick(component.active.name, 'background-size', 'cover')"
					ng-class="isInherited(component.active.id, 'background-size', 'cover')"/>
				<div class="ct-button ct-control-button">
					<span class="ct-icon ct-resize-type1-icon"></span> <?php _e("resize to cover", "component-theme"); ?>
				</div>
			</label>
		</li>
		<li>
			<label>
				<input type="radio" name="background-size" value="contain"
					<?php $this->ng_attributes('background-size', 'model,change'); ?>
					ng-click="radioButtonClick(component.active.name, 'background-size', 'contain')"
					ng-class="isInherited(component.active.id, 'background-size', 'contain')"/>
				<div class="ct-button ct-control-button">
					<span class="ct-icon ct-resize-type2-icon"></span> <?php _e("resize to contain", "component-theme"); ?>
				</div>
			</label>
		</li>
		<li>
			<label>
				<input type="radio" name="background-size" value="manual"
					<?php $this->ng_attributes('background-size', 'model,change'); ?>
					ng-click="radioButtonClick(component.active.name, 'background-size', 'manual')"
					ng-class="isInherited(component.active.id, 'background-size', 'manual')"/>
				<div class="ct-button ct-control-button">
					<span class="ct-icon ct-resize-type2-icon"></span> <?php _e("manual", "component-theme"); ?>
				</div>
			</label>
		</li>
	</ul>

	<h3><?php _e("Repeat", "component-theme"); ?></h3>
	<ul class="ct-button-list">
		<li>
			<label>
				<input type="radio" name="background-repeat" value="no-repeat"
					<?php $this->ng_attributes('background-repeat', 'model,change'); ?>
					ng-click="radioButtonClick(component.active.name, 'background-repeat', 'no-repeat')"
					ng-class="isInherited(component.active.id, 'background-repeat', 'no-repeat')"/>
				<div class="ct-button ct-control-button">
					<span class="ct-icon ct-no-repeat-icon"></span> <?php _e("no repeat", "component-theme"); ?>
				</div>
			</label>
		</li>
		<li>
			<label>
				<input type="radio" name="background-repeat" value="repeat"
					<?php $this->ng_attributes('background-repeat', 'model,change'); ?>
					ng-click="radioButtonClick(component.active.name, 'background-repeat', 'repeat')"
					ng-class="isInherited(component.active.id, 'background-repeat', 'repeat')"/>
				<div class="ct-button ct-control-button">
					<span class="ct-icon ct-repeat-icon"></span> <?php _e("repeat", "component-theme"); ?>
				</div>
			</label>
		</li>
		<li>
			<label>
				<input type="radio" name="background-repeat" value="repeat-x"
					<?php $this->ng_attributes('background-repeat', 'model,change'); ?>
					ng-click="radioButtonClick(component.active.name, 'background-repeat', 'repeat-x')"
					ng-class="isInherited(component.active.id, 'background-repeat', 'repeat-x')"/>
				<div class="ct-button ct-control-button">
					<span class="ct-icon ct-repeat-x-icon"></span> <?php _e("repeat x", "component-theme"); ?>
				</div>
			</label>
		</li>
		<li>
			<label>
				<input type="radio" name="background-repeat" value="repeat-y"
					<?php $this->ng_attributes('background-repeat', 'model,change'); ?>
					ng-click="radioButtonClick(component.active.name, 'background-repeat', 'repeat-y')"
					ng-class="isInherited(component.active.id, 'background-repeat', 'repeat-y')"/>
				<div class="ct-button ct-control-button">
					<span class="ct-icon ct-repeat-y-icon"></span> <?php _e("repeat y", "component-theme"); ?>
				</div>
			</label>
		</li>
	</ul>

	<h3><?php _e("Position", "component-theme"); ?></h3>
	<div class="ct-float-left ct-option-container ct-position-container">
		<div class="ct-measurebox-container"
			ng-class="{'ct-word-selected':getOptionUnit('background-position-left')=='auto'}">
			<div class="ct-measurebox-wrap">
				<div class="ct-measurebox">
					<input class="ct-measure-value ct-number-font" type="text" spellcheck="false"
						<?php $this->ng_attributes('background-position-left'); ?>/>
					<div class="ct-measure-type">{{getOptionUnit('background-position-left')}}</div>
				</div>
				<?php $this->measure_type_select('background-position-left'); ?>
			</div>
		</div>
		<div class="ct-orientation-text"><?php _e("from left", "component-theme"); ?></div>
	</div>
	<div class="ct-float-left ct-option-container ct-position-container">
		<div class="ct-measurebox-container"
			ng-class="{'ct-word-selected':getOptionUnit('background-position-top')=='auto'}">
			<div class="ct-measurebox-wrap">
				<div class="ct-measurebox">
					<input class="ct-measure-value ct-number-font" type="text" spellcheck="false"
						<?php $this->ng_attributes('background-position-top'); ?>/>
					<div class="ct-measure-type">{{getOptionUnit('background-position-top')}}</div>
				</div>
				<?php $this->measure_type_select('background-position-top'); ?>
			</div>
		</div>
		<div class="ct-orientation-text"><?php _e("from top", "component-theme"); ?></div>
	</div>
</div>

<div class="ct-panel-column">
	<div ng-show="getOption('background-size') == 'manual'">
		<h3><?php _e("Manual Size", "component-theme"); ?></h3>

		<div class="ct-float-left ct-option-container ct-position-container">
			<div class="ct-measurebox-container"
				ng-class="{'ct-word-selected':getOptionUnit('background-size-width')=='auto'}">
				<div class="ct-measurebox-wrap">
					<div class="ct-measurebox">
						<input class="ct-measure-value ct-number-font" type="text" spellcheck="false"
							<?php $this->ng_attributes('background-size-width'); ?>/>
						<div class="ct-measure-type">{{getOptionUnit('background-size-width')}}</div>
					</div>
					<?php $this->measure_type_select('background-size-width'); ?>
				</div>
			</div>
			<div class="ct-orientation-text"><?php _e("width", "component-theme"); ?></div>
		</div>
		
		<div class="ct-float-left ct-option-container ct-position-container">
			<div class="ct-measurebox-container"
				ng-class="{'ct-word-selected':getOptionUnit('background-size-height')=='auto'}">
				<div class="ct-measurebox-wrap">
					<div class="ct-measurebox">
						<input class="ct-measure-value ct-number-font" type="text" spellcheck="false"
							<?php $this->ng_attributes('background-size-height'); ?>/>
						<div class="ct-measure-type">{{getOptionUnit('background-size-height')}}</div>
					</div>
					<?php $this->measure_type_select('background-size-height'); ?>
				</div>
			</div>
			<div class="ct-orientation-text"><?php _e("height", "component-theme"); ?></div>
		</div>
	</div>
	<div style="clear:both">
		<h3><?php _e("Background Clip", "component-theme"); ?></h3>
		<ul class="ct-button-list">
			<li>
				<label>
					<input type="radio" name="background-clip" value="border-box"
						<?php $this->ng_attributes('background-clip', 'model,change'); ?>
						ng-click="radioButtonClick(component.active.name, 'background-clip', 'border-box')"
						ng-class="isInherited(component.active.id, 'background-clip', 'border-box')"/>
					<div class="ct-button ct-control-button">
						<span class="ct-icon ct-auto-icon"></span> <?php _e("border box", "component-theme"); ?>
					</div>
				</label>
			</li>
			<li>
				<label>
					<input type="radio" name="background-clip" value="padding-box"
						<?php $this->ng_attributes('background-clip', 'model,change'); ?>
						ng-click="radioButtonClick(component.active.name, 'background-clip', 'padding-box')"
						ng-class="isInherited(component.active.id, 'background-clip', 'padding-box')"/>
					<div class="ct-button ct-control-button">
						<span class="ct-icon ct-auto-icon"></span> <?php _e("padding box", "component-theme"); ?>
					</div>
				</label>
			</li><li>
				<label>
					<input type="radio" name="background-clip" value="content-box"
						<?php $this->ng_attributes('background-clip', 'model,change'); ?>
						ng-click="radioButtonClick(component.active.name, 'background-clip', 'content-box')"
						ng-class="isInherited(component.active.id, 'background-clip', 'content-box')"/>
					<div class="ct-button ct-control-button">
						<span class="ct-icon ct-auto-icon"></span> <?php _e("content box", "component-theme"); ?>
					</div>
				</label>
			</li>
		</ul>
	</div>
</div>