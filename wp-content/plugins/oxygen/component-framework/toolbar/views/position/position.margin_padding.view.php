<h3></h3>
<div class="ct-margin-padding">
	<div class="ct-size-box" ng-if="isActiveName('ct_section')">
		<span class="ct-size-box-name"><?php _e("Container Padding", "component-theme"); ?></span>
		<div class="ct-measurebox-container ct-top-measure ct-measurebox-with-options"
			ng-class="{'ct-word-selected':getOptionUnit('container-padding-top')=='auto'}">
			<div class="ct-measurebox-wrap">
				<div class="ct-measurebox"
					ng-class="isInherited(component.active.id, 'container-padding-top')">
					<input class="ct-measure-value ct-number-font" type="text" spellcheck="false"
						<?php $this->ng_attributes('container-padding-top'); ?> />
					<div class="ct-measure-type">{{getOptionUnit('container-padding-top')}}</div>
				</div>
			</div>
			<?php $this->measure_type_select('container-padding-top'); ?>
			<?php $this->measure_box_options('container-padding-top', 'container-padding-bottom', __("Apply to bottom","component-theme") ); ?>
		</div>
		<div class="ct-measurebox-container ct-right-measure ct-measurebox-with-options"
			ng-class="{'ct-word-selected':getOptionUnit('container-padding-right')=='auto'}">
			<div class="ct-measurebox-wrap">
				<div class="ct-measurebox"
					ng-class="isInherited(component.active.id, 'container-padding-right')">
					<input class="ct-measure-value ct-number-font" type="text" spellcheck="false"
						<?php $this->ng_attributes('container-padding-right'); ?> />
					<div class="ct-measure-type">{{getOptionUnit('container-padding-right')}}</div>
				</div>
			</div>
			<?php $this->measure_type_select('container-padding-right'); ?>
			<?php $this->measure_box_options('container-padding-right', 'container-padding-left', __("Apply to left","component-theme") ); ?>
		</div>
		<div class="ct-measurebox-container ct-bottom-measure ct-measurebox-with-options"
			ng-class="{'ct-word-selected':getOptionUnit('container-padding-bottom')=='auto'}">
			<div class="ct-measurebox-wrap">
				<div class="ct-measurebox"
					ng-class="isInherited(component.active.id, 'container-padding-bottom')">
					<input class="ct-measure-value ct-number-font" type="text" spellcheck="false"
						<?php $this->ng_attributes('container-padding-bottom'); ?> />
					<div class="ct-measure-type">{{getOptionUnit('container-padding-bottom')}}</div>
				</div>
			</div>
			<?php $this->measure_type_select('container-padding-bottom'); ?>
			<?php $this->measure_box_options('container-padding-bottom', 'container-padding-top', __("Apply to top","component-theme") ); ?>
		</div>
		<div class="ct-measurebox-container ct-left-measure ct-measurebox-with-options"
			ng-class="{'ct-word-selected':getOptionUnit('container-padding-left')=='auto'}">
			<div class="ct-measurebox-wrap">
				<div class="ct-measurebox"
					ng-class="isInherited(component.active.id, 'container-padding-left')">
					<input class="ct-measure-value ct-number-font" type="text" spellcheck="false"
						<?php $this->ng_attributes('container-padding-left'); ?> />
					<div class="ct-measure-type">{{getOptionUnit('container-padding-left')}}</div>
				</div>
			</div>
			<?php $this->measure_type_select('container-padding-left'); ?>
			<?php $this->measure_box_options('container-padding-left', 'container-padding-right', __("Apply to right","component-theme") ); ?>
		</div>						
	</div>

	<div class="ct-size-box">
		<span class="ct-size-box-name"><?php _e("Padding", "component-theme"); ?></span>
		<div class="ct-measurebox-container ct-top-measure ct-measurebox-with-options"
			ng-class="{'ct-word-selected':getOptionUnit('padding-top')=='auto'}">
			<div class="ct-measurebox-wrap">
				<div class="ct-measurebox"
					ng-class="isInherited(component.active.id, 'padding-top')">
					<input class="ct-measure-value ct-number-font" type="text" spellcheck="false"
						<?php $this->ng_attributes('padding-top'); ?> />
					<div class="ct-measure-type">{{getOptionUnit('padding-top')}}</div>
				</div>
			</div>
			<?php $this->measure_type_select('padding-top'); ?>
			<?php $this->measure_box_options('padding-top', 'padding-bottom', __("Apply to bottom","component-theme") ); ?>
		</div>
		<div class="ct-measurebox-container ct-right-measure ct-measurebox-with-options"
			ng-class="{'ct-word-selected':getOptionUnit('padding-right')=='auto'}">
			<div class="ct-measurebox-wrap">
				<div class="ct-measurebox"
					ng-class="isInherited(component.active.id, 'padding-right')">
					<input class="ct-measure-value ct-number-font" type="text" spellcheck="false"
						<?php $this->ng_attributes('padding-right'); ?> />
					<div class="ct-measure-type">{{getOptionUnit('padding-right')}}</div>
				</div>
			</div>
			<?php $this->measure_type_select('padding-right'); ?>
			<?php $this->measure_box_options('padding-right', 'padding-left', __("Apply to left","component-theme") ); ?>
		</div>
		<div class="ct-measurebox-container ct-bottom-measure ct-measurebox-with-options"
			ng-class="{'ct-word-selected':getOptionUnit('padding-bottom')=='auto'}">
			<div class="ct-measurebox-wrap">
				<div class="ct-measurebox"
					ng-class="isInherited(component.active.id, 'padding-bottom')">
					<input class="ct-measure-value ct-number-font" type="text" spellcheck="false"
						<?php $this->ng_attributes('padding-bottom'); ?> />
					<div class="ct-measure-type">{{getOptionUnit('padding-bottom')}}</div>
				</div>
			</div>
			<?php $this->measure_type_select('padding-bottom'); ?>
			<?php $this->measure_box_options('padding-bottom', 'padding-top', __("Apply to top","component-theme") ); ?>
		</div>
		<div class="ct-measurebox-container ct-left-measure ct-measurebox-with-options"
			ng-class="{'ct-word-selected':getOptionUnit('padding-left')=='auto'}">
			<div class="ct-measurebox-wrap">
				<div class="ct-measurebox"
					ng-class="isInherited(component.active.id, 'padding-left')">
					<input class="ct-measure-value ct-number-font" type="text" spellcheck="false"
						<?php $this->ng_attributes('padding-left'); ?> />
					<div class="ct-measure-type">{{getOptionUnit('padding-left')}}</div>
				</div>
			</div>
			<?php $this->measure_type_select('padding-left'); ?>
			<?php $this->measure_box_options('padding-left', 'padding-right', __("Apply to right","component-theme") ); ?>
		</div>						
	</div>

	<div class="ct-size-box" ng-if="!isActiveName('ct_column')">
		<span class="ct-size-box-name"><?php _e("Margin", "component-theme"); ?></span>
		<div class="ct-measurebox-container ct-top-measure ct-measurebox-with-options"
			ng-class="{'ct-word-selected':getOptionUnit('margin-top')=='auto'}">
			<div class="ct-measurebox-wrap">
				<div class="ct-measurebox"
					ng-class="isInherited(component.active.id, 'margin-top')">
					<input class="ct-measure-value ct-number-font" type="text" spellcheck="false"
						<?php $this->ng_attributes('margin-top'); ?> />
					<div class="ct-measure-type">{{getOptionUnit('margin-top')}}</div>
				</div>
			</div>
			<?php $this->measure_type_select('margin-top'); ?>
			<?php $this->measure_box_options('margin-top', 'margin-bottom', __("Apply to bottom","component-theme") ); ?>
		</div>
		<div class="ct-measurebox-container ct-right-measure ct-measurebox-with-options"
			ng-class="{'ct-word-selected':getOptionUnit('margin-right')=='auto'}">
			<div class="ct-measurebox-wrap">
				<div class="ct-measurebox"
					ng-class="isInherited(component.active.id, 'margin-right')">
					<input class="ct-measure-value ct-number-font" type="text" spellcheck="false"
						<?php $this->ng_attributes('margin-right'); ?> />
					<div class="ct-measure-type">{{getOptionUnit('margin-right')}}</div>
				</div>
			</div>
			<?php $this->measure_type_select('margin-right'); ?>
			<?php $this->measure_box_options('margin-right', 'margin-left', __("Apply to left","component-theme") ); ?>
		</div>
		<div class="ct-measurebox-container ct-bottom-measure ct-measurebox-with-options"
			ng-class="{'ct-word-selected':getOptionUnit('margin-bottom')=='auto'}">
			<div class="ct-measurebox-wrap">
				<div class="ct-measurebox"
					ng-class="isInherited(component.active.id, 'margin-bottom')">
					<input class="ct-measure-value ct-number-font" type="text" spellcheck="false"
						<?php $this->ng_attributes('margin-bottom'); ?> />
					<div class="ct-measure-type">{{getOptionUnit('margin-bottom')}}</div>
				</div>
			</div>
			<?php $this->measure_type_select('margin-bottom'); ?>
			<?php $this->measure_box_options('margin-bottom', 'margin-top', __("Apply to top","component-theme") ); ?>
		</div>
		<div class="ct-measurebox-container ct-left-measure ct-measurebox-with-options"
			ng-class="{'ct-word-selected':getOptionUnit('margin-left')=='auto'}">
			<div class="ct-measurebox-wrap">
				<div class="ct-measurebox"
					ng-class="isInherited(component.active.id, 'margin-left')">
					<input class="ct-measure-value ct-number-font" type="text" spellcheck="false"
						<?php $this->ng_attributes('margin-left'); ?> />
					<div class="ct-measure-type">{{getOptionUnit('margin-left')}}</div>
				</div>
			</div>
			<?php $this->measure_type_select('margin-left'); ?>
			<?php $this->measure_box_options('margin-left', 'margin-right', __("Apply to right","component-theme") ); ?>
		</div>
	</div>
</div>