<div class="ct-radius-details-panel">
	<h3></h3>
	<div class="ct-size-box ct-border-radius-box">

		<div class="ct-measurebox-container ct-measurebox-with-options ct-top-measure ct-apply-all"
			ng-class="{'ct-word-selected':getOptionUnit('border-top-left-radius')=='auto'}">
			<div class="ct-measurebox-wrap">
				<div class="ct-measurebox" ng-class="isInherited(component.active.id, 'border-top-left-radius')">
					<input class="ct-measure-value ct-number-font" type="text" spellcheck="false"
						<?php $this->ng_attributes('border-top-left-radius'); ?> />
					<div class="ct-measure-type">{{getOptionUnit('border-top-left-radius')}}</div>
				</div>
				<?php $this->measure_type_select('border-top-left-radius'); ?>
			</div>
			<div class="ct-measurebox-options">
				<div class="ct-checkbox">
					<label><input class="ct-apply-all-trigger" type="checkbox" checked="checked" data-option="border-top-left-radius"/>
						<span class="ct-checkbox-box"></span><span><?php _e("Apply All", "component-theme"); ?></span></label>
				</div>
			</div>
			<!-- <div class="ct-measurebox-options-bottom clearfix">
						<div class="ct-checkbox">
							<label><input type="checkbox"/>
								<span class="ct-checkbox-box"></span><span><?php _e("Eleptical", "component-theme"); ?></span></label>
						</div>
					</div> -->		
		</div>
		<!-- .ct-measurebox-container -->
		
		<div class="ct-measurebox-container ct-measurebox-with-options ct-right-measure ct-apply-all"
			ng-class="{'ct-word-selected':getOptionUnit('border-top-right-radius')=='auto'}">
			<div class="ct-measurebox-wrap">
				<div class="ct-measurebox" ng-class="isInherited(component.active.id, 'border-top-right-radius')">
					<input class="ct-measure-value ct-number-font" type="text" spellcheck="false"
						<?php $this->ng_attributes('border-top-right-radius'); ?> />
					<div class="ct-measure-type">{{getOptionUnit('border-top-right-radius')}}</div>
				</div>
				<?php $this->measure_type_select('border-top-right-radius'); ?>
			</div>
			<div class="ct-measurebox-options">
				<div class="ct-checkbox">
					<label><input class="ct-apply-all-trigger" type="checkbox" checked="checked" data-option="border-top-right-radius"/>
						<span class="ct-checkbox-box"></span><span><?php _e("Apply All", "component-theme"); ?></span></label>
				</div>
			</div>
			<!-- <div class="ct-measurebox-options-bottom clearfix">
						<div class="ct-checkbox">
							<label><input type="checkbox"/>
								<span class="ct-checkbox-box"></span><span><?php _e("Eleptical", "component-theme"); ?></span></label>
						</div>
					</div> -->		
		</div>
		<!-- .ct-measurebox-container -->

		<div class="ct-measurebox-container ct-measurebox-with-options ct-bottom-measure ct-apply-all"
			ng-class="{'ct-word-selected':getOptionUnit('border-bottom-right-radius')=='auto'}">
			<div class="ct-measurebox-wrap">
				<div class="ct-measurebox" ng-class="isInherited(component.active.id, 'border-bottom-right-radius')">
					<input class="ct-measure-value ct-number-font" type="text" spellcheck="false"
						<?php $this->ng_attributes('border-bottom-right-radius'); ?> />
					<div class="ct-measure-type">{{getOptionUnit('border-bottom-right-radius')}}</div>
				</div>
				<?php $this->measure_type_select('border-bottom-right-radius'); ?>
			</div>
			<div class="ct-measurebox-options">
				<div class="ct-checkbox">
					<label><input class="ct-apply-all-trigger" type="checkbox" checked="checked" data-option="border-bottom-right-radius"/>
						<span class="ct-checkbox-box"></span><span><?php _e("Apply All", "component-theme"); ?></span></label>
				</div>
			</div>
			<!-- <div class="ct-measurebox-options-bottom clearfix">
						<div class="ct-checkbox">
							<label><input type="checkbox"/>
								<span class="ct-checkbox-box"></span><span><?php _e("Eleptical", "component-theme"); ?></span></label>
						</div>
					</div> -->		
		</div>
		<!-- .ct-measurebox-container -->

		<div class="ct-measurebox-container ct-measurebox-with-options ct-left-measure ct-apply-all"
			ng-class="{'ct-word-selected':getOptionUnit('border-bottom-left-radius')=='auto'}">
			<div class="ct-measurebox-wrap">
				<div class="ct-measurebox" ng-class="isInherited(component.active.id, 'border-bottom-left-radius')">
					<input class="ct-measure-value ct-number-font" type="text" spellcheck="false"
						<?php $this->ng_attributes('border-bottom-left-radius'); ?> />
					<div class="ct-measure-type">{{getOptionUnit('border-bottom-left-radius')}}</div>
				</div>
				<?php $this->measure_type_select('border-bottom-left-radius'); ?>
			</div>
			<div class="ct-measurebox-options">
				<div class="ct-checkbox">
					<label><input class="ct-apply-all-trigger" type="checkbox" checked="checked" data-option="border-bottom-left-radius"/>
						<span class="ct-checkbox-box"></span><span><?php _e("Apply All", "component-theme"); ?></span></label>
				</div>
			</div>
			<!-- <div class="ct-measurebox-options-bottom clearfix">
						<div class="ct-checkbox">
							<label><input type="checkbox"/>
								<span class="ct-checkbox-box"></span><span><?php _e("Eleptical", "component-theme"); ?></span></label>
						</div>
					</div> -->		
		</div>
		<!-- .ct-measurebox-container -->

	</div>
	<!-- .ct-size-box -->
</div>
<!-- .ct-radius-details-panel -->