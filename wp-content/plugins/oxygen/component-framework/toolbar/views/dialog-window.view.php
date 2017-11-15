<div id="ct-dialog-window" class="ct-dialog-window" 
	ng-if="dialogWindow" 
	ng-class="{'ct-add-form-dialog':dialogForms['showAddItemDialogForm']}"
	ng-click="hideDialogWindow()">
	<div class="ct-dialog-window-content-wrap"
		ng-click="$event.stopPropagation()">

		<?php do_action("ct_dialog_window"); ?>
		
		<div class="ct-close-dialog ct-action-button" ng-click="hideDialogWindow()"><i class="fa fa-times fa-lg"></i></div>
	</div>
</div><!-- #ct-dialog-window -->