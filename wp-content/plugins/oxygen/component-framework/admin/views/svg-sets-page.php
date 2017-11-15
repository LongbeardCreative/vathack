<div class="wrap">

	<h2><?php _e("SVG Sets", "component-theme"); ?></h2>
	<?php if ( ! empty( $svg_sets ) ) : ?>
		
	<h3><?php _e("Uploaded Sets", "component-theme"); ?></h3>
		<form action="" method="post">
		<?php foreach ( $svg_sets as $name => $set ) : ?>
			
			<?php echo $name; ?><br/>

		<?php endforeach; ?>
		</form>

	<?php endif; ?>

	<h3><?php _e("Add New Set", "component-theme"); ?></h3>
	<form action="" method="post" enctype="multipart/form-data">

		<?php _e("Name of the Set", "component-theme"); ?> <input type="text" name="ct_svg_set_name"><br/>
		<?php _e("SVG file to upload", "component-theme"); ?> <input type="file" name="ct_svg_set_file" id="ct-svg-set-file">
		<p>
			<input type="submit" class="button button-primary" value="<?php _e("Submit", "component-theme"); ?>" name="submit">
		</p>
	</form>
</div>