<?php
add_action( 'admin_menu', 'pf_add_admin_menu' );
add_action( 'admin_init', 'pf_settings_init' );

function pf_add_admin_menu(  ) { 

	add_submenu_page( 'ct_dashboard_page', 'Profectus Settings', 'Profectus Settings', 'manage_options', 'profectus', 'pf_options_page' );

}

//ADMIN JS
function pf_admin_js() {
	wp_enqueue_script( 'pf_admin_js', plugin_dir_url( __FILE__ ) . 'inc/pf_admin.js', array( 'jquery' ), false, false );
}
add_action( 'admin_enqueue_scripts', 'pf_admin_js' );


function pf_settings_init(  ) { 
	register_setting( 'pluginPage', 'pf_settings' );

	//API SETTINGS SECTION
	// add_settings_section( 'pf_API_section', __( 'API Engine', 'profectus' ), 'pf_api_section_callback', 'pluginPage' );

	// 	add_settings_field( 'pf_api_field', __( 'Source', 'profectus' ), 'pf_api_field_render', 'pluginPage', 'pf_API_section' );

	//SCRIPTS SETTINGS SECTION
	add_settings_section( 'pf_script_section', __( 'Custom Scripts for Oxygen', 'profectus' ), 'pf_script_section_callback', 'pluginPage' );

		add_settings_field( 'pf_script_field', __( 'Script', 'profectus' ), 'pf_script_field_render', 'pluginPage', 'pf_script_section' );

	//SCRIPTS SETTINGS SECTION
	add_settings_section( 'pf_devmode_section', __( 'Development Mode', 'profectus' ), 'pf_devmode_section_callback', 'pluginPage' );

		add_settings_field( 'pf_devmode_field', __( 'Development Mode', 'profectus' ), 'pf_devmode_field_render', 'pluginPage', 'pf_devmode_section' );

}

//****************************************************************************

function pf_api_field_render(  ) { 

	$options = get_option( 'pf_settings' );
	?>
	<select name='pf_settings[pf_api_source]'>
		<option value='1' <?php selected( $options['pf_api_source'], 1 ); ?>>Oxygen</option>
		<option value='2' <?php selected( $options['pf_api_source'], 2 ); ?>>Longbeard</option>
	</select>

<?php

}

function pf_script_field_render(  ) { 

	$options = get_option( 'pf_settings' );
	$scripts = $options['pf_script_url'];
	foreach ($scripts as $script) { ?>
		<span><?php echo $script ?></span><br>
	<?php }
	echo "<input id='pf_script_field' name='pf_settings[pf_script_url]' size='40' type='text' value='" . $scripts . "' placeholder='Script URL' />";

}

function pf_devmode_field_render(  ) { 

	$options = get_option( 'pf_settings' );
	?>
	<select name='pf_settings[pf_devmode]'>
		<option value='1' <?php selected( $options['pf_devmode'], 1 ); ?>>ON</option>
		<option value='2' <?php selected( $options['pf_devmode'], 2 ); ?>>OFF</option>
	</select>

<?php

}

//CALLBACKS
function pf_api_section_callback(  ) { 

	echo __( 'Tweak and Engage Profectus Engine', 'profectus' );

}

function pf_script_section_callback(  ) { 

	echo __( 'Add External Scripts to Oxygen', 'profectus' );

}

function pf_devmode_section_callback(  ) { 

	echo __( 'Enable Development Mode', 'profectus' );

}

//Display the options page
function pf_options_page(  ) { 
settings_errors();
	?>
	<form action='options.php' method='post'>

		<h2>Profectus Settings</h2>

		<?php
		settings_fields( 'pluginPage' );
		do_settings_sections( 'pluginPage' );
		submit_button();
		?>

	</form>
	<?php

}