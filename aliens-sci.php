<?php
/**
 * Plugin Name: Custom fields to api
 * Description: Puts all code from Custom fields to api.
 * Author: Maher Samy
 * Author URI: https://plus.google.com/104344598302998313721
 * Version: 1.0.1
 * Plugin URI: https://wordpress.org/plugins/aliens-sci/
 */


class AliensAPI {
	/**
	 * @var object 	$plugin 			All base plugin configuration is stored here
	 */
	protected $plugin;

	/**
	 * @var string 	$apiVersion 		Stores the version number of the REST API
	 */
	protected $apiVersion;

	/**
	 * Constructor
	 *
	 * @author Maher Samy  <2mfsamy@gmail.com>
	 *
	 * @since 1.0.1
	 */
	function __construct() {

		$this->plugin = new StdClass;
		$this->plugin->title = 'Custom fields to api';
		$this->plugin->name = 'aliens-sci';
        $this->plugin->folder = WP_PLUGIN_DIR . '/' . $this->plugin->name;
        $this->plugin->url = WP_PLUGIN_URL . '/' . str_replace(basename( __FILE__), "", plugin_basename(__FILE__));
		$this->plugin->version = '1.0.1';

		$this->apiVersion = (REST_API_VERSION) ?: get_option( 'rest_api_plugin_version', get_option( 'json_api_plugin_version', null ) );

		if($this->_isAPIVersionOne()) {
			$this->_versionOneSetup();
		}

		if($this->_isAPIVersionTwo()) {
			$this->_versionTwoSetup();	
		}
	}

	private function dd($data) {
		if( WP_DEBUG ) {
			echo '<pre>';
			print_r($data);
			echo '</pre>';
			die();
		}
	}

	
	private function _versionOneSetup() {
		// Filters
		add_filter( 'json_prepare_post', array( $this, 'addACFDataPost'), 10, 3 ); // Posts
		add_filter( 'json_prepare_term', array( $this, 'addACFDataTerm'), 10, 3 ); // Taxonomy Terms
		add_filter( 'json_prepare_user', array( $this, 'addACFDataUser'), 10, 3 ); // Users
		add_filter( 'json_prepare_comment', array( $this, 'addACFDataComment'), 10, 3 ); // Comments

		// Endpoints
		add_filter( 'json_endpoints', array( $this, 'registerRoutes' ), 10, 3 );
	}

	private function _versionTwoSetup() {
		// Actions
		add_action( 'rest_api_init', array( $this, 'addACFDataPostV2' ) ); // Posts
		add_action( 'rest_api_init', array( $this, 'addACFDataTermV2' ) ); // Taxonomy Terms
		add_action( 'rest_api_init', array( $this, 'addACFDataUserV2' ) ); // Users
		add_action( 'rest_api_init', array( $this, 'addACFDataCommentV2' ) ); // Comments

		add_action( 'rest_api_init', array( $this, 'addACFOptionRouteV2') );
	}

	private function _getAPIBaseVersion() {
		$version = $this->apiVersion;

		if( is_null( $version ) ) {
			return false;
		}

		$baseNumber = substr( $version, 0, 1 );

		if( $baseNumber === '1' ) {
			return 1;
		}

		if( $baseNumber === '2' ) {
			return 2;
		}

		return false;
	}

	private function _isAPIVersionOne() {
		if($this->_getAPIBaseVersion() === 1) { 
			return true;
		}

		return false;
	}

	private function _isAPIVersionTwo() {
		if($this->_getAPIBaseVersion() === 2) { 
			return true;
		}

		return false;
	}

	function addACFDataUser( $data, $user, $context ) {
		$data['acf'] = $this->_getData( $user->ID, 'user' );
		return $data;
	}

	function addACFDataTerm( $data, $term, $context = null ) {
		$data['acf'] = get_fields( $term, 'term' );
		return $data;
	}

	function addACFDataPost( $data, $post, $context ) {
		$data['acf'] = $this->_getData( $post['ID'] );
		return $data;
	}

	function addACFDataPostV2() {
		// Posts
		register_api_field( 'post',
	        'acf',
	        array(
	            'get_callback'    => array( $this, 'addACFDataPostV2cb' ),
	            'update_callback' => null,
	            'schema'          => null,
	        )
	    );

		register_api_field( 'page',
	        'acf',
	        array(
	            'get_callback'    => array( $this, 'addACFDataPostV2cb' ),
	            'update_callback' => null,
	            'schema'          => null,
	        )
	    );

		$types = get_post_types(array(
			'public' => true,
			'_builtin' => false
		));
		foreach($types as $key => $type) {
			register_api_field( $type,
		        'acf',
		        array(
		            'get_callback'    => array( $this, 'addACFDataPostV2cb' ),
		            'update_callback' => null,
		            'schema'          => null,
		        )
		    );
		}
	}

	function addACFDataPostV2cb($object, $fieldName, $request) {
		return $this->_getData($object['id']);
	}


	function addACFDataTermV2() {
		register_api_field( 'term',
	        'acf',
	        array(
	            'get_callback'    => array( $this, 'addACFDataTermV2cb' ),
	            'update_callback' => null,
	            'schema'          => null,
	        )
	    );
	}


	function addACFDataTermV2cb($object, $fieldName, $request) {
		return $this->_getData($object['id'], 'term', $object);
	}


	function addACFDataUserV2() {
		register_api_field( 'user',
	        'acf',
	        array(
	            'get_callback'    => array( $this, 'addACFDataUserV2cb' ),
	            'update_callback' => null,
	            'schema'          => null,
	        )
	    );
	}

	function addACFDataUserV2cb($object, $fieldName, $request) {
		return $this->_getData( $object['id'], 'user' );
	}

	
	function addACFDataCommentV2() {
		register_api_field( 'comment',
	        'acf',
	        array(
	            'get_callback'    => array( $this, 'addACFDataCommentV2cb' ),
	            'update_callback' => null,
	            'schema'          => null,
	        )
	    );
	}

	
	function addACFDataCommentV2cb( $object, $fieldName, $request ) {
		return $this->_getData( $object['id'], 'comment' );
	}

	
	private function _getData($id, $type = 'post', $object = array()) {
		switch($type) {
			case 'post':
			default:
				return get_fields($id);
				break;
			case 'term':
				return get_fields($object['taxonomy'] . '_' . $id);
				break;
			case 'user':
				return get_fields('user_' . $id);
				break;
			case 'comment':
				return get_fields('comment_' . $id);
			 	break;
			case 'options':
				return get_fields('option');
				break;
		}
	}

	
	function addACFOptionRouteV2() {
		register_rest_route( 'wp/v2/acf', '/options', array(
			'methods' => array(
				'GET'
			),
			'callback' => array( $this, 'addACFOptionRouteV2cb' )
		) );

		register_rest_route( 'wp/v2/acf', '/options/(?P<option>.+)', array(
			'methods' => array(
				'GET'
			),
			'callback' => array( $this, 'addACFOptionRouteV2cb' )
		) );
	}

	function addACFOptionRouteV2cb( WP_REST_Request $request ) {
		if($request['option']) {
			return get_field($request['option'], 'option');
		}

		return get_fields('option');
	}


	function addACFDataComment($data, $comment, $context) {
		$data['acf'] = $this->_getData('comment_' . $comment->comment_ID);
		return $data;
	}

	
	function getACFOptions() {
		return get_fields('options');
	}

	function getACFOption($name) {
		return get_field($name, 'option');
	}


	function registerRoutes( $routes ) {
		$routes['/option'] = array(
			array( array( $this, 'getACFOptions' ), WP_JSON_Server::READABLE )
		);
		$routes['/options'] = array(
			array( array( $this, 'getACFOptions' ), WP_JSON_Server::READABLE )
		);

		$routes['/options/(?P<name>[\w-]+)'] = array(
			array( array( $this, 'getACFOption' ), WP_JSON_Server::READABLE ),
		);

		return $routes;
	}

}

$AliensAPI = new AliensAPI();
