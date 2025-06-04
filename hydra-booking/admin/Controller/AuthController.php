<?php
namespace HydraBooking\Admin\Controller;
// exit
if ( ! defined( 'ABSPATH' ) ) { exit; }

// Use Namespace
use HydraBooking\Admin\Controller\RouteController;

use HydraBooking\DB\Host;

class AuthController {

	// constaract.
	public function __construct() {
	}

	public function create_endpoint() {
		register_rest_route(
			'hydra-booking/v1',
			'/user/auth',
			array(
				'methods'  => 'GET',
				'callback' => array( $this, 'getUserAuth' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
			)
		);
	}

	// Get Current user auth.
	public function getUserAuth() {
		$user_id   = $this->userID();
		$user_role = $this->userRole();
		$user_caps = $this->userAllCaps();

		// Return response.
		$data = array(
			'status' => true,
			'user'   => array(
				'id'   => $user_id,
				'role' => $user_role,
				'caps' => $user_caps,
			),
		);
		return rest_ensure_response( $data );
	}
	// Get Current user auth.
	public function userRole() {
		$user  = wp_get_current_user();
		$roles = (array) $user->roles;
		return $roles;
	}
	public function userID() {
		$user = wp_get_current_user();
		return $user->ID;
	}

	// Get Current User Host ID 
	public function userHostID() { 

		$host = new Host();
		$host = $host->getHostByUserID( $this->userID() );
		if( empty($host) ) {
			return false;
		}else{ 
			return $host->id;
		}
	}

	public function userData() {

		$user = wp_get_current_user();
		return $user;
	}
	public function userAllCaps() {

		$user = wp_get_current_user();
		return $user->allcaps;
	}

	// Update Host Role Capabilities
	public function updateHostRoleCapabilities( $role, $caps ) {
		$role = get_role( $role );
		foreach ( $caps as $cap => $status ) {
			$role->add_cap( $cap, $status );
		}
	}


	// Checked Host Role user Status when user login
	public function tfhb_restrict_unverified_user( $user, $username, $password ) {

		$user_obj      = get_user_by( 'login', $username );  
		if(!$user_obj){ 
			$user_obj = get_user_by( 'email', $username );
		}
		$allowed_roles = array( 'tfhb_host' );
		if ( $username != '' && $user_obj != false ) {

			if ( array_intersect( $allowed_roles, $user_obj->roles ) ) {
				$host = new Host(); 
				$value = $host->getHostByUserID( $user_obj->ID );
				if ( $value != '' && $value->status != 'activate' ) {
					$user = new \WP_Error( 'denied', 
					'<strong>'.esc_html(__('ERROR', 'hydra-booking')).'</strong>: '.esc_html(__('Your account is not Activate !', 'hydra-booking')).''
				);
					remove_action( 'authenticate', 'wp_authenticate_username_password', 20 );
				}
			}
		}

		return $user;
	}

 

	function tfhb_woocommerce_prevent_admin_access($prevent_access) {
		if (current_user_can('tfhb_host')) {
				$prevent_access = false;
		} 
		return $prevent_access;
	}
}
