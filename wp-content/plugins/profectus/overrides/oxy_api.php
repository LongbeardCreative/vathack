<?php 

/**
 * Class for all API calls and functions
 *
 * @since 0.4.0
 */

Class CT_API {

	private $server_url 		= "http://oxygenapp.com";
	private $api_url 			= "http://oxygenapp.com/api";
	private $consumer_key 		= "5bOAo7BrF5Jx";
	private $consumer_secret 	= "NnmlX77iimU7ISYtzklDumny6tJgNpjrDX5819TpnZ5wXglW";

	/**
	 * Constructor	
	 *
	 * @since 0.4.0
	 * @author Ilya K.
	 */
	
	function CT_API() {

		// nothing here
	}

	
	/**
	 * Send a remote request 
	 * 
	 * @since 1.0.3
	 * @author James Golovich
	 */

	function remote_request( $slug, $args = array() ) {
		
		$secret = get_option( 'oxygen_license_key' );
		
		$args = array_merge( array( 'method' => 'GET' ), $args );
		// Always adding a slash to the end of slug because the API always redirects/301 if it doesn't
		$url = $this->api_url . $slug;//trailingslashit( $slug );
		$site_hash = get_option( 'oxygen_license_site_hash' );
		$auth_data = array(
			'oa_key' => $site_hash,
			'oa_ts' => time(),
		);
		$url_parts = wp_parse_url( $url );
		$data = $site_hash . ':' . $args[ 'method' ] . ':' . $url_parts[ 'path' ] . ':' . $auth_data[ 'oa_ts' ];
		$auth_data[ 'oa_hash' ] = hash_hmac( 'md5', $data, $secret );
		//var_dump($data, $secret);
		if ( 'GET' === $args[ 'method' ] ) {
			// Add API data to URL
			$url = add_query_arg( $auth_data, $url );
		} else {
			// Add API data to body
			if ( isset( $args[ 'body' ] ) && is_array( $args[ 'body' ] ) ) {
				$args[ 'body' ] = array_merge( $args['body'], $auth_data );
			} else {
				$args[ 'body' ] = $auth_data;
			}
		}
		return wp_remote_request( $url, $args );
	}


	/**
	 * Send a remote GET request 
	 * 
	 * @since 1.0.3
	 * @author James Golovich
	 */

	function remote_get( $slug, $args = array() ) {
		$args = array_merge( $args, array( 'method' => 'GET' ) );
		return $this->remote_request( $slug, $args );
	}


	/**
	 * Send a remote POST request 
	 * 
	 * @since 1.0.3
	 * @author James Golovich
	 */

	function remote_post( $slug, $args = array() ) {
		$args = array_merge( $args, array( 'method' => 'POST' ) );
		return $this->remote_request( $slug, $args );
	}


	/**
	 * Check user's API token
	 *
	 * @since 0.4.0
	 * @author Ilya K.
	 */
	
	function check_api_token() {
 		
		$response = $this->remote_post( '/checktoken' );

		return $this->check_response( $response );
	}


	/**
	 * Design Sets
	 */
	

	/**
	 * Send Design Set data to create it on the server
	 *
	 * @since 0.4.0
	 * @author Ilya K.
	 */
	
	function create_design_set( $data ) {

 		$response = $this->remote_post( '/design_set', array (
 			'body' => $data,
 		));

		return $this->check_response( $response );
	}


	/**
	 * Get all user's Design Sets or certain Design Set if $data["id"] specified
	 *
	 * @since 0.4.0
	 * @author Ilya K.
	 */
	
	function get_design_sets( $data = array() ) {
		
		// add data as get parameters
		$url = add_query_arg( $data, '/design_set' );
		
		// Send request
		$response = $this->remote_get( $url );

		return $this->check_response( $response );
	}


	/**
	 * Send Design Set data to update it on the server
	 *
	 * @since 0.4.0
	 * @author Ilya K.
	 */
	
	function update_design_set( $data ) {

		// Send request
		$response = $this->remote_request( '/design_set', array (
			'method' => 'PUT',
			'body' 	 => json_encode( $data ),
		) );

		return $this->check_response( $response );
	}


	/**
	 * Delete Design Set on the server
	 *
	 * @since 0.4.0
	 * @author Ilya K.
	 */
	
	function delete_design_set( $data ) {
		
		// add data as get parameters
		$url = add_query_arg( $data, '/design_set' );
 		
 		// Send request
 		$response = $this->remote_request( $url, array (
			'method' => 'DELETE',
		) );

		return $this->check_response( $response );
	}


	/**
	 * Components
	 */

	/**
	 * Create component
	 *
	 * @since 0.4.0
	 * @author Ilya K.
	 */
	
	function create_component( $data ) {

		// Send request
		$response = $this->remote_post( '/components', array (
			'body' => $data,
		));

		return $this->check_response( $response );
	}


	/**
	 * Get components
	 *
	 * @since 0.4.0
	 * @author Ilya K.
	 */
	
	function get_components( $data = array() ) {

		// add data as get parameters
		$url = add_query_arg( $data, '/components' );

		// Send request
		$response = $this->remote_get( $url, array( 'timeout' => 30 ) );

		return $this->check_response( $response );
	}


	/**
	 * Send Component's data to update it on the server
	 *
	 * @since 0.4.0
	 * @author Ilya K.
	 */
	
	function update_component( $data ) {

		// Send request
		$response = $this->remote_request( '/components', array (
			'method' => 'PUT',
			'body' 	 => $data,
		) );

		return $this->check_response( $response );
	}


	/**
	 * Delete component on the server
	 *
	 * @since 0.4.0
	 * @author Ilya K.
	 */
	
	function delete_component( $data ) {
		
		// add data as get parameters
		$url = add_query_arg( $data, '/components' );
		
		// Send request
		$response = $this->remote_request( $url, array (
			'method' => 'DELETE',
		) );

		return $this->check_response( $response );
	}


	/**
	 * Pages
	 */

	/**
	 * Create page
	 *
	 * @since 0.4.0
	 * @author Ilya K.
	 */
	
	function create_page( $data ) {

		// Send request
		$response = $this->remote_post( '/pages', array (
			'body' => $data,
		));

		return $this->check_response( $response );
	}


	/**
	 * Get pages
	 *
	 * @since 0.4.0
	 * @author Ilya K.
	 */
	
	function get_pages( $data = array() ) {

		// add data as get parameters
		$url = add_query_arg( $data, '/pages' );

		// Send request
		$response = $this->remote_get( $url, array( 'timeout' => 30 ) );

		return $this->check_response( $response );
	}


	/**
	 * Send page's data to update it on the server
	 *
	 * @since 0.4.0
	 * @author Ilya K.
	 */
	
	function update_page( $data ) {

		// Send request
		$response = $this->remote_request( '/pages', array (
			'method' => 'PUT',
			'body' 	 => json_encode( $data ),
		) );

		return $this->check_response( $response );
	}


	/**
	 * Delete page on the server
	 *
	 * @since 0.4.0
	 * @author Ilya K.
	 */
	
	function delete_page( $data ) {
		
		// add data as get parameters
		$url = add_query_arg( $data, '/pages' );

		// Send request
		$response = $this->remote_request( $url, array (
			'method' => 'DELETE',
		) );

		return $this->check_response( $response );
	}


	/**
	 * Get categories, pages and components
	 *
	 * @since 1.0.1
	 * @author Ilya K.
	 */
	
	function get_base() {

		// Send request
		$response = $this->remote_get( '/get_base', array( 'timeout' => 30 ) );

		return $this->check_response( $response );
	}


	/**
	 * Post asset
	 *
	 * @since 0.4.0
	 * @author Ilya K.
	 */
	
	function post_asset( $data ) {

		// Send request
		$response = $this->remote_post( '/post_asset', array(
			'timeout' 	=> 30,
			'body' 		=> $data ) );

		return $this->check_response( $response );
	}


	/**
	 * Get categories
	 *
	 * @since 0.4.0
	 * @author Ilya K.
	 */
	
	function get_categories() {

		// Send request
		$response = $this->remote_get( '/get_categories', array( 'timeout' => 30 ) );

		return $this->check_response( $response );
	}


	/**
	 * Stylesheets
	 */

	/**
	 * Post style sheet to the DB
	 *
	 * @since 0.4.0
	 * @author Ilya K.
	 */
	
	function post_style_sheet( $data ) {

		// Send request
		$response = $this->remote_post( '/post_style_sheet', array (
			'body' => $data,
		));

		return $this->check_response( $response );
	}


	/**
	 * Get style sheet from the DB
	 *
	 * @since 0.4.0
	 * @author Ilya K.
	 */
	
	function get_style_sheets( $data ) {

		// add data as get parameters
		$url = add_query_arg( $data, '/get_style_sheets' );

		// Send request
		$response = $this->remote_get( $url );

		return $this->check_response( $response );
	}


	/**
	 * Check server response
	 *
	 * @since 0.4.0
	 * @author Ilya K.
	 */

	private function check_response( $api_response, $json = false ) {

		if ( is_wp_error( $api_response ) ) {
			return array (
					"status" 	=> "error",
					"message" 	=> sanitize_text_field( $api_response->get_error_message() ),
				);
		}

		//var_dump($response['body']);

		// decode server response
		$response = json_decode( $api_response["body"], true );
		
		if ( !$response ) {
			$response = array (
					"status" 	=> "error",
					"message" 	=> __("Failed decoding server response", "component-theme"),
					"response" 	=> sanitize_text_field( $api_response["body"] )
				);
		}

		if ( $json ) {
			return json_encode( $response );
		}
		else {
			return $response;
		}
	}

}


/**
 * API callback to take calls from Oxygen builder 
 * and send them to Oxygen API server
 *
 * @since 0.4.0
 * @author Ilya K.
 */

function ct_api_callback() {

	$nonce  	= $_REQUEST['nonce'];
	$post_id 	= $_REQUEST['post_id'];

	// check nonce
	if ( ! wp_verify_nonce( $nonce, 'oxygen-nonce-' . $post_id ) ) {
	    // This nonce is not valid.
	    die( 'Security check' ); 
	}

	// check user role
	if ( ! current_user_can( 'edit_posts' ) ) {
		die ( 'Security check' );
	}
	
	// actions allowed to be called from builder
	$actions = array( "get_components", "get_pages", "update_component", "update_page", "create_design_set", "post_style_sheet", "get_style_sheets" );
	$action = $_REQUEST['api_action'];

	if ( ! in_array( $action, $actions ) ) {
		die ( 'Security check' );	
	}

	$data = file_get_contents('php://input');
	
	// init API
	$ct_api = new CT_API();
	$result = $ct_api->$action( json_decode( $data, true ) );

	echo json_encode( $result );

	die();
}
// add api ajax callback
add_action('wp_ajax_ct_api_callback', 'ct_api_callback');

/**
 * Testing API
 */

//$oxygen_api = new CT_API();

/* Design Sets */

/*$response = $oxygen_api->create_design_set( array(
		"name" 			=> "category Design Set",
		"category_id" 	=> 1
	));

/*$response = $oxygen_api->get_design_sets( array(
		"id" 			=> 13,
		"category_id" 	=> 2
	));*/

/*$response = $oxygen_api->update_design_set( array(
		"id" 			=> 8,
		"name" 			=> "New name",
		"category_id" 	=> 1
	));*/

/*$response = $oxygen_api->delete_design_set( array(
		"id" => 7,
	));*/


/* Pages */

/*$response = $oxygen_api->create_page( array(
		"name" 			=> "Test Page",
		"content" 		=> "[asdasd]12313[/asdasd]",
		"design_set_id" => 8,
		"category_id" 	=> 2
	));*/

/*$response = $oxygen_api->get_pages( array(
		"id" 			=> 8,
		//"design_set_id" => 7,
		//"category_id" 	=> 2
	));*/

/*$response = $oxygen_api->update_page( array(
		"id" 			=> 7,
		"name" 			=> "New name",
		"content" 		=> "[][/]",
		"design_set_id" => 7,
		"category_id" 	=> 2,
	));*/

/*$response = $oxygen_api->delete_page( array(
	"id" => 2,
));*/


/* Components */

/*$response = $oxygen_api->create_component( array(
		"name" 			=> "Component",
		"content" 		=> "[asdasd]asdas[/asdasd]",
		"design_set_id" => 1,
		"category_id" 	=> 13,
		"screenshot" => 21,
		"status" => 'dev',
	));*/

/*$response = $oxygen_api->get_components( array(
		"id" 			=> 150, 
		//"design_set_id" => 12,
		//"category_id" 	=> 3
	));*/

/*$response = $oxygen_api->update_component( array(
		"id" 			=> 7,
		"name" 			=> "Test Component!!!",
		"content" 		=> "[asdasd][/asdasd]",
		"design_set_id" => 8,
		//"category_id" 	=> 3
	));*/

/*$response = $oxygen_api->delete_component( array(
		"id" => 2,
	));*/
	
/*$response = $oxygen_api->get_base();*/

/*echo "<pre>";
var_dump($response);

die();
*/