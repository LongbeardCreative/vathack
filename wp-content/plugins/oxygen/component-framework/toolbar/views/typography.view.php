<div class="ct-typography-details-panel">
	<div class="ct-panel-column">								
		<div class="ct-panel-options-wrap clearfix">
			<h3><?php _e("Font family", "component-theme"); ?></h3>
			<div class="ct-selectbox ct-font-family-select ct-select-search-enabled">
				<ul class="ct-select">
					<li class="ct-selected" ng-class="isInherited(component.active.id, 'font-family')">
						{{getComponentFont(component.active.id, true)}}
						<span class="ct-icon ct-dropdown-icon"></span>
					</li>
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
							<li ng-repeat="font in typeKitFonts | filter:fontsFilter | limitTo: 20"
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
		<div class="ct-panel-options-wrap clearfix">
			<div class="ct-float-left ct-option-container">
				<h3><?php _e("Size", "component-theme"); ?></h3>
				<div class="ct-measurebox-container "
					ng-class="{'ct-word-selected':getOptionUnit('font-size')=='auto'}">
					<div class="ct-measurebox-wrap">
						<div class="ct-measurebox" ng-class="isInherited(component.active.id, 'font-size')">
							<input class="ct-measure-value ct-number-font" type="text" spellcheck="false"
								<?php $this->ng_attributes('font-size'); ?> />
							<div class="ct-measure-type">{{getOptionUnit('font-size')}}</div>
						</div>
						<?php $this->measure_type_select('font-size'); ?>
					</div>
				</div>
			</div>
			<div class="ct-float-left">
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
			</div>
		</div>
	</div>

	<div class="ct-panel-column">								
		
		<div class="ct-panel-options-wrap clearfix">
			<h3><?php _e("Color","component-theme"); ?></h3>
			<div class="ct-option-container ct-float-left">
				<div class="ct-colorpicker">
					<input class="ct-color"
						readonly colorpicker="rgba" colorpicker-fixed-position="true" type="text"
						<?php $this->ng_attributes('color','model,change'); ?>
						ng-style="{'background-color':getOption('color'),'color':getOption('color')}"/>
				</div>
			</div>
			<div class="ct-float-left">
				<div class="ct-textbox ct-textbox-big" ng-class="isInherited(component.active.id, 'color')">
					<input type="text"
						<?php $this->ng_attributes('color','model,change'); ?>/>
				</div>
			</div>
			<div class="ct-selected-color-fav ct-float-left"><span class="ct-icon ct-heart-icon"></span></div>
		</div>

		<div class="ct-panel-options-wrap clearfix">
			<h3><?php _e("Text-align","component-theme"); ?></h3>
			<ul class="ct-button-list">
				<li>
					<label>
						<input type="radio" name="text-align" value="left"
							<?php $this->ng_attributes('text-align', 'model,change'); ?>
							ng-click="radioButtonClick(component.active.name, 'text-align', 'left')"
							ng-class="isInherited(component.active.id, 'text-align', 'left')"/>
						<div class="ct-button ct-control-button ct-icon-only">
							<span class="ct-icon ct-paragraph-left-icon"></span>
						</div>
					</label>
				</li>
				<li>
					<label>
						<input type="radio" name="text-align" value="center"
							<?php $this->ng_attributes('text-align', 'model,change'); ?>
							ng-click="radioButtonClick(component.active.name, 'text-align', 'center')"
							ng-class="isInherited(component.active.id, 'text-align', 'center')"/>
						<div class="ct-button ct-control-button ct-icon-only">
							<span class="ct-icon ct-paragraph-center-icon"></span>
						</div>
					</label>
				</li>
				<li>
					<label>
						<input type="radio" name="text-align" value="justify"
							<?php $this->ng_attributes('text-align', 'model,change'); ?>
							ng-click="radioButtonClick(component.active.name, 'text-align', 'justify')"
							ng-class="isInherited(component.active.id, 'text-align', 'justify')"/>
						<div class="ct-button ct-control-button ct-icon-only">
							<span class="ct-icon ct-paragraph-justify-icon"></span>
						</div>
					</label>
				</li>
				<li>
					<label>
						<input type="radio" name="text-align" value="right"
							<?php $this->ng_attributes('text-align', 'model,change'); ?>
							ng-click="radioButtonClick(component.active.name, 'text-align', 'right')"
							ng-class="isInherited(component.active.id, 'text-align', 'right')"/>
						<div class="ct-button ct-control-button ct-icon-only">
							<span class="ct-icon ct-paragraph-right-icon"></span>
						</div>
					</label>
				</li>
			</ul>
		</div>
		
		<!-- <div class="ct-panel-options-wrap clearfix">
			<h3><?php _e("Direction","component-theme"); ?></h3>
			<ul class="ct-button-list">
				<li>
					<label>
						<input type="radio" name="direction" value="ltr"
							<?php $this->ng_attributes('direction', 'model,change'); ?>
							ng-click="radioButtonClick(component.active.name, 'direction', 'ltr')"
							ng-class="isInherited(component.active.id, 'direction', 'ltr')"/>
						<div class="ct-button ct-control-button ct-icon-right">
							ltr <span class="ct-icon ct-direction-right-icon"></span>
						</div>
					</label>
				</li>
				<li>
					<label>
						<input type="radio" name="direction" value="rtl"
							<?php $this->ng_attributes('direction', 'model,change'); ?>
							ng-click="radioButtonClick(component.active.name, 'direction', 'rtl')"
							ng-class="isInherited(component.active.id, 'direction', 'rtl')"/>
						<div class="ct-button ct-control-button">
							<span class="ct-icon ct-direction-left-icon"></span> rtl
						</div>
					</label>
				</li>
			</ul>
		</div> -->
	</div>

	<div class="ct-panel-column">
		<div class="ct-panel-options-wrap clearfix">
			<h3><?php _e("Line-Height","component-theme"); ?></h3>
			<div class="ct-measurebox-container"
				ng-class="{'ct-word-selected':getOptionUnit('line-height')=='auto'}">
				<div class="ct-measurebox-wrap">
					<div class="ct-measurebox ct-double-sided-items" ng-class="isInherited(component.active.id, 'line-height')">
						<div class="ct-type-icon"><span class="ct-icon ct-line-height-icon"></span></div>
						<input class="ct-measure-value ct-number-font" type="text"
							<?php $this->ng_attributes('line-height'); ?> />
						<div class="ct-measure-type">{{getOptionUnit('line-height')}}</div>
					</div>
					<?php $this->measure_type_select('line-height'); ?>
				</div>
			</div>
		</div>
		<div class="ct-panel-options-wrap clearfix">
			<h3><?php _e("Letter-Spacing","component-theme"); ?></h3>
			<div class="ct-measurebox-container"
				ng-class="{'ct-word-selected':getOptionUnit('letter-spacing')=='auto'}">
				<div class="ct-measurebox-wrap">
					<div class="ct-measurebox ct-double-sided-items" ng-class="isInherited(component.active.id, 'letter-spacing')">
						<div class="ct-type-icon"><span class="ct-icon ct-letter-spacing-icon"></span></div>
						<input class="ct-measure-value ct-number-font" type="text"
							<?php $this->ng_attributes('letter-spacing'); ?> />
						<div class="ct-measure-type">{{getOptionUnit('letter-spacing')}}</div>
					</div>
					<?php $this->measure_type_select('letter-spacing'); ?>
				</div>
			</div>
		</div>
	</div>

	<div class="ct-panel-column">
		<!-- <div class="ct-panel-options-wrap clearfix">
			<h3><?php _e("List-style","component-theme"); ?></h3>
			<ul class="ct-button-list">
				<li>
					<label>
						<input type="radio" name="list-style-type" value="disc"
							<?php $this->ng_attributes('list-style-type', 'model,change'); ?>
							ng-click="radioButtonClick(component.active.name, 'list-style-type', 'disc')"
							ng-class="isInherited(component.active.id, 'list-style-type', 'disc')"/>
						<div class="ct-button ct-control-button">
							<span class="ct-icon ct-disc-icon"></span> disc
						</div>
					</label>
				</li>
				<li>
					<label>
						<input type="radio" name="list-style-type" value="square"
							<?php $this->ng_attributes('list-style-type', 'model,change'); ?>
							ng-click="radioButtonClick(component.active.name, 'list-style-type', 'square')"
							ng-class="isInherited(component.active.id, 'list-style-type', 'square')"/>
						<div class="ct-button ct-control-button">
							<span class="ct-icon ct-no-repeat-icon"></span> square
						</div>
					</label>
				</li>
				<li>
					<label>
						<input type="radio" name="list-style-type" value="lower-alpha"
							<?php $this->ng_attributes('list-style-type', 'model,change'); ?>
							ng-click="radioButtonClick(component.active.name, 'list-style-type', 'lower-alpha')"
							ng-class="isInherited(component.active.id, 'list-style-type', 'lower-alpha')"/>
						<div class="ct-button ct-control-button">
							<span class="ct-icon ct-lower-alpha-icon"></span> lower-alpha
						</div>
					</label>
				</li>
				<li>
					<label>
						<input type="radio" name="list-style-type" value="decimal"
							<?php $this->ng_attributes('list-style-type', 'model,change'); ?>
							ng-click="radioButtonClick(component.active.name, 'list-style-type', 'decimal')"
							ng-class="isInherited(component.active.id, 'list-style-type', 'decimal')"/>
						<div class="ct-button ct-control-button">
							<span class="ct-icon ct-decimal-icon"></span> decimal
						</div>
					</label>
				</li>
			</ul>
		</div> -->
		<div class="ct-panel-options-wrap clearfix">
			<h3><?php _e("Text-Decoration","component-theme"); ?></h3>
			<ul class="ct-button-list">
				<li>
					<label>
						<input type="radio" name="text-decoration" value="none"
							<?php $this->ng_attributes('text-decoration', 'model,change'); ?>
							ng-click="radioButtonClick(component.active.name, 'text-decoration', 'none')"
							ng-class="isInherited(component.active.id, 'text-decoration', 'none')"/>
						<div class="ct-button ct-control-button">
							<span class="ct-icon ct-none-icon"></span> none
						</div>
					</label>
				</li>
				<li>
					<label>
						<input type="radio" name="text-decoration" value="underline"
							<?php $this->ng_attributes('text-decoration', 'model,change'); ?>
							ng-click="radioButtonClick(component.active.name, 'text-decoration', 'underline')"
							ng-class="isInherited(component.active.id, 'text-decoration', 'underline')"/>
						<div class="ct-button ct-control-button ct-icon-only">
							<span class="ct-icon ct-underline-icon"></span>
						</div>
					</label>
				</li>
				<li>
					<label>
						<input type="radio" name="text-decoration" value="overline"
							<?php $this->ng_attributes('text-decoration', 'model,change'); ?>
							ng-click="radioButtonClick(component.active.name, 'text-decoration', 'overline')"
							ng-class="isInherited(component.active.id, 'text-decoration', 'overline')"/>
						<div class="ct-button ct-control-button ct-icon-only">
							<span class="ct-icon ct-overline-icon"></span>
						</div>
					</label>
				</li>
				<li>
					<label>
						<input type="radio" name="text-decoration" value="line-through"
							<?php $this->ng_attributes('text-decoration', 'model,change'); ?>
							ng-click="radioButtonClick(component.active.name, 'text-decoration', 'line-through')"
							ng-class="isInherited(component.active.id, 'text-decoration', 'line-through')"/>
						<div class="ct-button ct-control-button ct-icon-only">
							<span class="ct-icon ct-line-through-icon"></span>
						</div>
					</label>
				</li>
			</ul>
		</div>
		<div class="ct-panel-options-wrap clearfix">
			<h3><?php _e("Font style","component-theme"); ?></h3>
			<ul class="ct-button-list">
				<li>
					<label>
						<input type="radio" name="font-style" value="normal"
							<?php $this->ng_attributes('font-style', 'model,change'); ?>
							ng-click="radioButtonClick(component.active.name, 'font-style', 'normal')"
							ng-class="isInherited(component.active.id, 'font-style', 'normal')"/>
						<div class="ct-button ct-control-button">
							<span class="ct-icon ct-none-icon"></span> normal
						</div>
					</label>
				</li>
				<li>
					<label>
						<input type="radio" name="font-style" value="italic"
							<?php $this->ng_attributes('font-style', 'model,change'); ?>
							ng-click="radioButtonClick(component.active.name, 'font-style', 'italic')"
							ng-class="isInherited(component.active.id, 'font-style', 'italic')"/>
						<div class="ct-button ct-control-button">
							<span class="ct-icon ct-italic-icon2"></span> italic
						</div>
					</label>
				</li>
			</ul>
		</div>
		<div class="ct-panel-options-wrap clearfix">
			<h3><?php _e("Text-Transform","component-theme"); ?></h3>
			<ul class="ct-button-list">
				<li>
					<label>
						<input type="radio" name="text-transform" value="none"
							<?php $this->ng_attributes('text-transform', 'model,change'); ?>
							ng-click="radioButtonClick(component.active.name, 'text-transform', 'none')"
							ng-class="isInherited(component.active.id, 'text-transform', 'none')"/>
						<div class="ct-button ct-control-button">
							<span class="ct-icon ct-none-icon"></span> none
						</div>
					</label>
				</li>
				<li>
					<label>
						<input type="radio" name="text-transform" value="capitalize"
							<?php $this->ng_attributes('text-transform', 'model,change'); ?>
							ng-click="radioButtonClick(component.active.name, 'text-transform', 'capitalize')"
							ng-class="isInherited(component.active.id, 'text-transform', 'capitalize')"/>
						<div class="ct-button ct-control-button">
							<span class="ct-icon ct-capitalize-icon"></span> capitalize
						</div>
					</label>
				</li>
				<li>
					<label>
						<input type="radio" name="text-transform" value="uppercase"
							<?php $this->ng_attributes('text-transform', 'model,change'); ?>
							ng-click="radioButtonClick(component.active.name, 'text-transform', 'uppercase')"
							ng-class="isInherited(component.active.id, 'text-transform', 'uppercase')"/>
						<div class="ct-button ct-control-button">
							<span class="ct-icon ct-uppercase-icon"></span> uppercase
						</div>
					</label>
				</li>
				<li>
					<label>
						<input type="radio" name="text-transform" value="lowercase"
							<?php $this->ng_attributes('text-transform', 'model,change'); ?>
							ng-click="radioButtonClick(component.active.name, 'text-transform', 'lowercase')"
							ng-class="isInherited(component.active.id, 'text-transform', 'lowercase')"/>
						<div class="ct-button ct-control-button">
							<span class="ct-icon ct-lowercase-icon"></span> lowercase
						</div>
					</label>
				</li>
			</ul>
		</div>
	</div>
</div>