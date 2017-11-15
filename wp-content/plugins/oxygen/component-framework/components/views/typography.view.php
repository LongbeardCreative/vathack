<div class="ct-selectbox ct-font-family-select ct-select-search-enabled">
	<ul class="ct-select">
		<li class="ct-selected"
			ng-class="isInherited(component.active.id, 'font-family')"
			>{{getComponentFont(component.active.id, true)}}<span class="ct-icon ct-dropdown-icon"></span></li>
		<li class="ct-searchbar">
			<div class="ct-textbox">
				<input ng-model="fontsFilter" type="text" value="" placeholder="<?php _e("Search...", "component-theme"); ?>" spellcheck="false"/>
			</div>
		</li>
		<li>
			<ul class="ct-dropdown-list">
				<li ng-click="setComponentFont(component.active.id, component.active.name, '');"
					title="<?php _e("Unset font", "component-theme"); ?>">
						<?php _e("Default", "component-theme"); ?>
				</li>
				<li ng-repeat="(name,font) in globalSettings.fonts | filter:{font:fontsFilter}"
					ng-click="setComponentFont(component.active.id, component.active.name, ['global', name]);"
					title="<?php _e("Apply global font", "component-theme"); ?>">
						{{name}} ({{font}})
				</li>
				<li ng-repeat="name in ['Inherit'] | filter:fontsFilter"
					ng-click="setComponentFont(component.active.id, component.active.name, name);"
					title="<?php _e("Use parent element font", "component-theme"); ?>">
						Inherit
				</li>
				<li ng-repeat="font in typeKitFonts | filter:{'name':fontsFilter} | limitTo: 20"
					ng-click="setComponentFont(component.active.id, component.active.name, font.slug);"
					title="<?php _e("Apply this font family", "component-theme"); ?>">
						{{font.name}}
				</li>
				<li ng-repeat="font in fontsList | filter:fontsFilter | limitTo: 20"
					ng-click="setComponentFont(component.active.id, component.active.name, font);"
					title="<?php _e("Apply this font family", "component-theme"); ?>">
						{{font}}
				</li>
			</ul>
		</li>
    </ul>
</div>

</div>
<div class="ct-toolitem">
	<h3><?php _e("Size","component-theme"); ?></h3>
	<div class="ct-measurebox-container "
		ng-class="{'ct-word-selected':getOptionUnit('font-size')=='auto'}">
		<div class="ct-measurebox-wrap">
			<div class="ct-measurebox ct-toolbarmeasurebox"
				ng-class="isInherited(component.active.id, 'font-size')">
				<input class="ct-measure-value ct-fontsize ct-number-font" type="text" spellcheck="false"
					<?php $this->ng_attributes('font-size'); ?> />
				<div class="ct-measure-type">{{getOptionUnit('font-size')}}</div>
			</div>
			<?php CT_Toolbar::measure_type_select('font-size'); ?>
		</div>
	</div>
</div>

<div class="ct-toolitem">
	<h3><?php _e("Weight","component-theme"); ?></h3>
	<div class="ct-selectbox ct-weight-select">
		<ul class="ct-select">
			<li class="ct-selected"
				ng-class="isInherited(component.active.id, 'font-weight')">
				{{getOption('font-weight')}}<span class="ct-icon ct-dropdown-icon"></span></li>									
			<li>
				<ul class="ct-dropdown-list">
					<li ng-click="setOptionModel('font-weight','')">&nbsp;</li>
					<li ng-click="setOptionModel('font-weight','100')">100</li>
					<li ng-click="setOptionModel('font-weight','200')">200</li>
					<li ng-click="setOptionModel('font-weight','300')">300</li>
					<li ng-click="setOptionModel('font-weight','400')">400</li>
					<li ng-click="setOptionModel('font-weight','500')">500</li>
					<li ng-click="setOptionModel('font-weight','600')">600</li>
					<li ng-click="setOptionModel('font-weight','700')">700</li>
					<li ng-click="setOptionModel('font-weight','800')">800</li>
					<li ng-click="setOptionModel('font-weight','900')">900</li>
				</ul>
			</li>									
	    </ul>
	</div>

<!-- DON'T ADD </div> HERE -->
