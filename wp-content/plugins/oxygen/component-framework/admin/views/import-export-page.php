<div class="wrap">

	<h2><?php _e("Import/Export Component Theme options", "component-theme"); ?></h2>

	<?php if ( isset($import_errors) && $import_errors ) : ?>
		
		<div id="message" class="error notice below-h2">
		<?php foreach ( $import_errors as $error ) : ?>
			
			<p><?php echo $error; ?></p>
		
		<?php endforeach; ?>
		</div>
	
	<?php endif; ?>

	<?php if ( isset($import_success) && $import_success ) : ?>
		
		<div id="message" class="updated notice below-h2">
		<?php foreach ( $import_success as $notice ) : ?>
			
			<p><?php echo $notice; ?></p>
		
		<?php endforeach; ?>
		</div>
	
	<?php endif; ?>
		
	<h3><?php _e("Export", "component-theme"); ?></h3>
	<p class="description"><?php _e("Copy code below to use on other install", "component-theme"); ?></p>

	<textarea cols="80" rows="10"><?php echo $export_json; ?></textarea>

	<h3><?php _e("Import", "component-theme"); ?></h3>
	<p class="description"><?php _e("Paste code below and click submit", "component-theme"); ?></p>
	
	<form action="" method="post">
		<textarea cols="80" rows="10" name="ct_import_json"><?php echo isset($import_json)?$import_json:''; ?></textarea>
		<p>
			<input type="submit" class="button button-primary" value="<?php _e("Submit", "component-theme"); ?>" name="submit">
		</p>
	</form>
</div>