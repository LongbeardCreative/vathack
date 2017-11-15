<?php

if( !class_exists( 'EDD_SL_Plugin_Updater' ) ) {
	// load our custom updater
	include( dirname( __FILE__ ) . '/EDD_SL_Plugin_Updater.php' );
}
	
Class OxygenSelectorDetectorUpdater { // class name should be unique for each add-on

	public $prefix 		= "edd_osd_"; // should be also unique for each add-on
	public $oxygen_url 	= "http://oxygenapp.com";
	public $addon_name 	= "Selector Detector"; // should be exact as EDD item name

	
	/**
	 * Add the actions in the constructor
	 * 
	 * @since 1.0
	 */

	function __construct() {

		add_action( 'admin_init', array( $this, 'init'), 0 );
		add_action( 'admin_init', array( $this, 'activate_license' ) );
		add_action( 'oxygen_license_admin_screen', array( $this, 'license_screen' ), 20 );
	}

	
	/**
	 * Initialize EDD_SL_Plugin_Updater class
	 * 
	 * @since 1.0
	 */

	function init() {

		// retrieve our license key from the DB
		$license_key = trim( get_option( $this->prefix . 'license_key' ) );

		// setup the updater
		$edd_updater = new EDD_SL_Plugin_Updater( 
			$this->oxygen_url, 
			plugin_dir_path( dirname( dirname( __FILE__ ) ) ) . "selector-detector.php", // main plugin file, specify for each add-on
			array( 
				'version' 	=> '1.0', 				// current version number
				'license' 	=> $license_key, 		// license key (used get_option above to retrieve from DB)
				'item_name' => $this->addon_name, 	// name of this plugin
				'author' 	=> 'Soflyy'  			// author of this plugin
			)
		);
	}


	/**
	 * License screen HTML output
	 * 
	 * @since 1.0
	 */

	function license_screen() {
		
		$license 	= get_option( $this->prefix . 'license_key' );
		$status 	= get_option( $this->prefix . 'license_status' );
		
		?>
		<div class="oxygen-license-wrap <?php echo $this->prefix . 'license-wrap'; ?>">
			<h2><?php echo $this->addon_name; ?></h2>
			<form method="post" action="">
			
				<?php wp_nonce_field( $this->prefix . 'submit_license', $this->prefix . 'license_nonce_field' ); ?>
				
				<table class="form-table">
					<tbody>
						<tr valign="top">
							<td>
								<input id="<?php echo $this->prefix; ?>license_key" name="<?php echo $this->prefix; ?>license_key" type="text" class="regular-text" value="<?php esc_attr_e( $license ); ?>" />
								<label class="description" for="<?php echo $this->prefix; ?>license_key"><?php echo $status; ?></label>
								<p class="description"><?php _e('Enter your license key to get updates'); ?></p>
							</td>
						</tr>
					</tbody>
				</table>	
				<?php submit_button( __("Submit","oxygen"), "primary", $this->prefix."submit_license" ); ?>
			
			</form>		
		</div>
		<?php
	}


	/**
	 * Send license key to OxygenApp.com EDD to activate license
	 * 
	 * @since 1.0
	 */

	function activate_license() {

		// listen for our activate button to be clicked
		if( isset( $_POST[$this->prefix."submit_license"] ) ) {

			// run a quick security check 
		 	if( ! wp_verify_nonce( $_POST[$this->prefix . 'license_nonce_field'], $this->prefix . 'submit_license' ) ) 	
				return;

			update_option( $this->prefix . 'license_key', trim( $_POST[$this->prefix . 'license_key'] ) );

			// retrieve the license from the database
			$license = trim( get_option( $this->prefix . 'license_key' ) );

			// data to send in our API request
			$api_params = array( 
				'edd_action'=> 'activate_license', 
				'license' 	=> $license, 
				'item_name' => urlencode( $this->addon_name ), // the name of our product in EDD
				'url'       => home_url()
			);
			
			// Call the custom API.
			$response = wp_remote_get( add_query_arg( $api_params, $this->oxygen_url ), array( 'timeout' => 15, 'sslverify' => false ) );

			// make sure the response came back okay
			if ( is_wp_error( $response ) )
				return false;

			// decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );
			
			// $license_data->license will be either "valid" or "invalid"

			update_option( $this->prefix . 'license_status', $license_data->license );

		}
	}


	/**
	 * Send license key to OxygenApp.com EDD to deactivate license
	 * Not used anywhere though
	 * 
	 * @since 1.0
	 */

	function deactivate_license() {

		// listen for our activate button to be clicked
		if( isset( $_POST[$this->prefix.'license_deactivate'] ) ) {

			// run a quick security check 
		 	if( ! wp_verify_nonce( $_POST[$this->prefix . 'license_nonce_field'], $this->prefix . 'submit_license' ) )
				return;

			// retrieve the license from the database
			$license = trim( get_option( $this->prefix . 'license_key' ) );

			// data to send in our API request
			$api_params = array( 
				'edd_action'=> 'deactivate_license', 
				'license' 	=> $license, 
				'item_name' => urlencode( $this->addon_name ), // the name of our product in EDD
				'url'       => home_url()
			);
			
			// Call the custom API.
			$response = wp_remote_get( add_query_arg( $api_params, $this->oxygen_url ), array( 'timeout' => 15, 'sslverify' => false ) );

			// make sure the response came back okay
			if ( is_wp_error( $response ) )
				return false;

			// decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );
			
			// $license_data->license will be either "deactivated" or "failed"
			if( $license_data->license == 'deactivated' )
				delete_option( $this->prefix . 'license_status' );

		}
	}

}

// instantinate the class
$updater = new OxygenSelectorDetectorUpdater();