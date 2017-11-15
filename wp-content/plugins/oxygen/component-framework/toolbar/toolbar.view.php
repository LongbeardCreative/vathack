<div id="ct-ui" class="ct-ui">
	<div id="ct-toolbar" class="ct-toolbar" 
		ng-class="{'oxygen-editing-media':isEditing('media'), 'oxygen-editing-class':isEditing('class'), 'oxygen-editing-state':isEditing('state'), 'oxygen-editing-special':isEditing('media')||isEditing('class')||isEditing('state')}" >
		
		<div id="ct-toolbar-main" class="clearfix">

			<div class="ct-toolbarsection ct-toolbarsection-one">
				<div class="ct-toolitem">
					<div class="ct-button ct-table-cell ct-border-right ct-add"
						ng-click="switchActionTab('componentBrowser')"
						ng-class="{'ct-active-tab' : isActiveActionTab('componentBrowser')}">
						<span class="ct-icon ct-add-icon"></span><?php _e("Add", "component-theme"); ?>
					</div>
				</div>
				<div class="ct-toolitem" ng-show="component.active.id < 100000 && !isEditing('style-sheet')">
					<div class="ct-button ct-table-cell" title="<?php _e("Remove Component", "component-theme"); ?>"
						ng-show="component.active.id > 0"
						ng-click="removeActiveComponent()">
							<span class="ct-icon ct-trash-icon"></span>
					</div>
					<div class="ct-button ct-table-cell" title="<?php _e("Duplicate Component", "component-theme"); ?>"
						ng-show="component.active.id > 0 && component.active.name != 'ct_span'"
						ng-click="duplicateComponent()">
							<span class="ct-icon ct-tab-icon"></span>
					</div>
				</div>
			</div>

			<div class="ct-toolbarsection ct-toolbarsection-two">
				<span ng-show="isEditing('style-sheet')">
					<div>
						<div class="ct-toolitem">
							<h3><?php _e( "Editing Stylesheet", "component-theme" ); ?></h3>
							<div class="ct-textbox ct-textbox-big">
								<input type="text" value="{{stylesheetToEdit}}" readonly>
							</div>
						</div>
					</div>
				</span>
				<span ng-show="!isEditing('style-sheet')">
					<?php do_action("ct_toolbar_component_header"); ?>
				</span>
			</div>

			<div class="ct-toolbarsection ct-toolbarsection-three clearfix" ng-show="component.active.name != 'ct_inner_content' && !isEditing('style-sheet')">
				<?php do_action("ct_toolbar_component_settings"); ?>
			</div>

			<div class="ct-toolbarsection ct-toolbarsection-four ct-toolbar-topright clearfix">
				<div class="ct-toolitem ct-nopaddingright" ng-class="{'ct-sidepanel-open' : showSidePanel }">
					<div class="ct-responsive-button ct-button ct-table-cell ct-lightbg ct-border-left"
						ng-click="toggleSidePanel()">
						<span class="ct-icon ct-burger-icon"></span>
					</div>
				</div>
				<div class="ct-toolitem ct-nopaddingright">
					<div class="ct-row">
					<?php
					/* Load the admin bar class code ready for instantiation */
					require_once( ABSPATH . WPINC . '/class-wp-admin-bar.php' );
					$admin_bar_class = apply_filters( 'wp_admin_bar_class', 'WP_Admin_Bar' );
					if ( class_exists( $admin_bar_class ) ) {
						$admin_bar = new $admin_bar_class;
						wp_admin_bar_edit_menu($admin_bar);
						$admin_url = $admin_bar->get_node('edit')->href;
					}
					else {
						$admin_url = admin_url();
					}
					
					
					?>
						<a href="<?php echo $admin_url;?>" class="ct-wp-button ct-button ct-table-cell ct-greybg ct-backtowp ct-border-right ct-history-button ct-border-left ct-button-centered">
							<span class="ct-wp-icon"></span> <?php _e("Back to WP", "component-theme"); ?>
						</a>
						<!--<div class="ct-undo-button ct-button ct-table-cell ct-greybg ct-border-right ct-halfwidth ct-history-button ct-border-left ct-button-centered">
							<span class="ct-icon ct-back-icon"></span>
						</div>
						<div class="ct-redo-button ct-button ct-table-cell ct-greybg ct-halfwidth ct-history-button ct-button-centered">
							<span class="ct-icon ct-forward-icon"></span>
						</div>-->
					</div>
					<div class="ct-row">
						<div class="ct-settings-button ct-button ct-table-cell ct-greybg ct-border-top ct-border-left"
							title="<?php _e("Component Settings", "component-theme"); ?>"
							ng-click="switchActionTab('settings')"
							ng-class="{'ct-active-tab' : isActiveActionTab('settings')}">
								<span class="ct-icon ct-settings-icon"></span> <?php _e("Settings", "component-theme"); ?>
						</div>
					</div>
				</div>
				<div class="ct-toolitem ct-nopaddingright">
					<div id="ct-save-button" class="ct-save-button ct-button ct-table-cell ct-color-butt ct-button-centered"
						title="<?php _e("Save", "component-theme"); ?>"
						ng-click="savePage()">
						<?php _e("Save", "component-theme"); ?>
					</div>
				</div>
			</div>
		
		</div>
		<!-- #ct-toolbar-main -->
		
		
		<div id="ct-components-browser" class="ct-toolbar-expanded ct-components-browser" 
			ng-show="isActiveActionTab('componentBrowser')"
			ng-class="{'ct-components-browser-open':isActiveActionTab('componentBrowser')}">
			<div class="ct-viewport clearfix">
				<div class="ct-panel-tabs-container ct-single-parent-tab">
					<div class="ct-panel-tabs-wrap">
						<div class="ct-panel-tabs">
							<?php do_action("ct_toolbar_components_anchors"); ?>
						</div>
					</div>
				</div>
				<div class="ct-panels">
		 			<?php do_action("ct_toolbar_components_list"); ?>
		 		</div>
			</div>
		</div>
		<!-- .ct-components-browser -->
		
		
		<div class="ct-toolbar-expanded ct-advanced-settings" ng-if="isActiveActionTab('advancedSettings')">
			<div class="ct-viewport clearfix">
				<div class="ct-panel-tabs-container" 
					ng-class="{'ct-single-parent-tab':isShowTab('advanced','typography')||isShowTab('advanced','code-php')||isShowTab('advanced','code-js')||isShowTab('advanced','code-css')}">
					<div class="ct-panel-tabs-wrap">
						<div class="ct-panel-tabs">
							<?php do_action("ct_toolbar_advanced_anchors"); ?>
						</div>
						<?php do_action("ct_toolbar_advanced_child_anchors"); ?>
					</div>
				</div>
				<div class="ct-panels clearfix">
					<?php do_action("ct_toolbar_before_advanced_settings"); ?>
			 		<?php do_action("ct_toolbar_advanced_settings"); ?>
				</div>
			</div>
		</div>
		<!-- .ct-advanced-settings -->
	
		
		<div class="ct-toolbar-expanded ct-advanced-settings ct-code-editor" ng-if="isActiveActionTab('codeEditor')">
			<div class="ct-viewport clearfix">
				<div class="ct-panel-tabs-container ct-single-parent-tab">
					<div class="ct-panel-tabs-wrap">
						<div class="ct-panel-tabs">
							<?php do_action("ct_toolbar_code_editor_anchors"); ?>
						</div>
					</div>
				</div>
				<div class="ct-panels clearfix">
			 		<?php do_action("ct_toolbar_code_editor_settings"); ?>
				</div>
			</div>
		</div>
		<!-- .ct-code-editor -->
		
		<div class="ct-toolbar-expanded ct-style-sheet" ng-if="isActiveActionTab('styleSheet')">
			<div class="ct-viewport">
				<div class="ct-viewport-styles clearfix">
					<?php require_once "views/style-sheet.view.php" ;?>
				</div>
			</div>
		</div>
		<!-- .ct-style-sheet -->

		<div class="ct-toolbar-expanded ct-svg-icons-list" ng-if="isActiveActionTab('SVGIcons')">
			<div class="ct-viewport">
				<div class="ct-viewport-inner clearfix">
					<?php do_action("ct_svg_icons_list"); ?>
				</div>
			</div>
		</div>
		<!-- .ct-svg-icons-list -->
		

		<div class="ct-toolbar-expanded ct-global-settings" ng-if="isActiveActionTab('settings')">


			<div class="ct-viewport clearfix">
				<div class="ct-panel-tabs-container ct-single-parent-tab">
					<div class="ct-panel-tabs-wrap">
						<div class="ct-panel-tabs">
							<div class="ct-panel-tab ct-button ct-panel-tab-no-subtabs"
								ng-click="switchTab('settings', 'page');"
								ng-class="{'ct-active' : isShowTab('settings','page')}">
									<span class="ct-icon ct-no-icon"></span> 
									<?php _e("Page settings", "component-theme"); ?>
							</div>

							<div class="ct-panel-tab ct-button ct-panel-tab-no-subtabs"
								ng-click="switchTab('settings', 'global');"
								ng-class="{'ct-active' : isShowTab('settings','global')}">
									<span class="ct-icon ct-no-icon"></span> 
									<?php _e("Global settings", "component-theme"); ?>
							</div>
						</div>
					</div>
				</div>
				<div class="ct-panels">
		 			<div class="ct-panel" ng-if="isShowTab('settings','page')">
						<?php do_action("ct_toolbar_page_settings"); ?>
					</div>

					<div class="ct-panel" ng-if="isShowTab('settings','global')">
						<?php do_action("ct_toolbar_global_settings"); ?>
					</div>
		 		</div>
			</div>


		</div>
		<!-- .ct-global-settings -->
	
		<?php require_once "views/side-panel.view.php"; ?>
		<?php //require_once "views/status-bar.view.php"; ?>
		<?php require_once "views/dialog-window.view.php";?>
		<?php require_once "views/notice-modal.view.php"; ?>

		<?php 
			/**
			 * Hook for add-ons to add UI elements inside the toolbar
			 *
			 * @since 1.4
			 */
			do_action("oxygen_before_toolbar_close"); 
		?>

	</div><!-- #ct-toolbar -->
</div>

<?php require_once "views/editor-panel.view.php"; ?>

<?php 
	/**
	 * Hook for add-ons to add UI elements outside the toolbar
	 *
	 * @since 1.4
	 */
	do_action("oxygen_after_toolbar"); 
?>

<div id="ct-page-overlay" class="ct-page-overlay"><i class="fa fa-cog fa-4x fa-spin"></i></div><!-- #ct-page-overlay -->