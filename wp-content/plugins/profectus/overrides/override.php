<?php
global $profectus_engine;
$profectus_on = TRUE;

//GET plugin directory
$plugin_root = WP_CONTENT_DIR . '/plugins';
$oxygen_api_dir = $plugin_root . '/oxygen/component-framework/includes/';
$profectus_overrides_dir = $plugin_root . '/profectus/overrides/';
$api_source = get_option( 'pf_settings' )['pf_api_source'];
$pf_using = get_option( 'pf_using' );

//Rename Old Oxygen API
// if ( file_exists( $oxygen_api_dir . 'api.php' ) && $profectus_on == FALSE ) {
// 	rename( $oxygen_api_dir . 'api.php', $oxygen_api_dir . 'api-oxygen.php' );
// }

if ( $api_source == 1 &&  $pf_using !== 1 ) {
	copy( $profectus_overrides_dir . 'lb_api.php', $oxygen_api_dir . 'api.php' );
} else if ( $api_source == 2 &&  $pf_using !== 2 ) {
	copy( $profectus_overrides_dir . 'oxy_api.php', $oxygen_api_dir . 'api.php' );
}

function pf_using( $source ) {
	if ( ! get_option( 'pf_using' ) ) {
		add_option( 'pf_using', $source, '', 'no' );
	} else {
		update_option( 'pf_using', $source, 'no' );
	}
}