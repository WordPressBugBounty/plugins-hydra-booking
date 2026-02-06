<?php
namespace HydraBooking\Admin\Controller;

// Use Namespace
use HydraBooking\Admin\Controller\RouteController;
use HydraBooking\Admin\Controller\DateTimeController;
use HydraBooking\Admin\Controller\CountryController;
use HydraBooking\Services\Integrations\Zoom\ZoomServices;
use HydraBooking\Services\Integrations\GoogleCalendar\GoogleCalendar;
use HydraBooking\Services\Integrations\OutlookCalendar\OutlookCalendar;
// Use DB
use HydraBooking\DB\Host;
use HydraBooking\DB\Availability;
// exit
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

class HostsController {


	// constaract
	public function __construct() { 


	}

	public function init() {
	}

	public function create_endpoint() {
		register_rest_route(
			'hydra-booking/v1',
			'/hosts/lists',
			array(
				'methods'  => 'GET',
				'callback' => array( $this, 'getHostsData' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
			)
		);
		register_rest_route(
			'hydra-booking/v1',
			'/hosts/create',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'CreateHosts' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
			)
		);
		register_rest_route(
			'hydra-booking/v1',
			'/hosts/delete',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'DeleteHosts' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
			)
		);
		register_rest_route(
			'hydra-booking/v1',
			'/hosts/update-status',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'UpdateHostsStatus' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
			)
		);
		// Get Single Host based on id
		register_rest_route(
			'hydra-booking/v1',
			'/hosts/(?P<id>[0-9]+)',
			array(
				'methods'  => 'GET',
				'callback' => array( $this, 'getTheHostData' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_integrations_permission'),
			)
		);
		register_rest_route(
			'hydra-booking/v1',
			'/hosts/information/update',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'updateHostInformation' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_integrations_permission'),
			)
		);

		// Availability

		register_rest_route(
			'hydra-booking/v1',
			'/hosts/availability/update',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'UpdateAvailabilitySettings' ),
				'permission_callback' => array( new RouteController(), 'tfhb_manage_custom_availability_permission' ),
			)
		);

		register_rest_route(
			'hydra-booking/v1',
			'/hosts/availability',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'GetAvailabilitySettings' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_hosts_permission'),
			)
		);

		register_rest_route(
			'hydra-booking/v1',
			'/hosts/availability/single',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'GetSingleAvailabilitySettings' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_integrations_permission'),
			)
		);

		register_rest_route(
			'hydra-booking/v1',
			'/hosts/availability/delete',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'DeleteAvailabilitySettings' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
			)
		);

		// Integration

		register_rest_route(
			'hydra-booking/v1',
			'/hosts/integration',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'GetIntegrationSettings' ),
				'permission_callback' => array( new RouteController(), 'tfhb_manage_integrations_permission' ),
			)
		);

		register_rest_route(
			'hydra-booking/v1',
			'/hosts/integration/update',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'UpdateIntegrationSettings' ),
				'permission_callback' => array( new RouteController(), 'tfhb_manage_integrations_permission' ),
			)
		);

		register_rest_route(
			'hydra-booking/v1',
			'/hosts/integration/fetch',
			array(
				'methods'  => 'GET',
				'callback' => array( $this, 'FetchIntegrationSettings' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_integrations_permission'),
			)
		);

		register_rest_route(
			'hydra-booking/v1',
			'/hosts/filter',
			array(
				'methods'  => 'GET',
				'callback' => array( $this, 'filterHosts' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_integrations_permission'),
				'args'     => array(
					'title' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);
	}
	
 
	public function getHostsData() {
		// Get all wp users list with
		$users    = get_users( array( 'role__in' => array( 'administrator', 'editor', 'tfhb_host' ) ) );
		$userData = array(
			array(
				'name' => __( 'Create new user', 'hydra-booking' ),
				'value' => 0,
			)
		);
		foreach ( $users as $user ) {
			// $userData[ $user->ID ] = $user->display_name . ' ( ' . $user->user_email . ' )' . ' - ( ' . $user->roles[0] . ' )';
			$userData[] = array(
				'name'  => $user->display_name . ' ( ' . $user->user_email . ' )' . ' - ( ' . $user->roles[0] . ' )',
				'value' => $user->ID,
			);
		} 

		// Hosts Lists
		$host      = new Host();
		$HostsList = $host->get();

		// Return response
		$data = array(
			'status'  => true,
			'users'   => $userData,
			'hosts'   => $HostsList,
			'message' =>  __( 'General Settings Updated Successfully', 'hydra-booking' ),
		);
		return rest_ensure_response( $data );
	}

	// Host Filter
	public function filterHosts( $request ) {
		$filterData = $request->get_param( 'filterData' );
		// Hosts Lists
		$host      = new Host();
		$HostsList = $host->get( '', $filterData );

		// Return response
		$data = array(
			'status'  => true,
			'hosts'   => $HostsList,
			'message' =>  __( 'General Settings Updated Successfully', 'hydra-booking' ),
		);
		return rest_ensure_response( $data );
	}

	// Create Hosts
	public function CreateHosts() {

		// Checked current user can manage option
		if (  ! current_user_can( 'manage_options' ) ) {
			// woocommerce payment
			$data = array(
				'status'  => false,
				'message' => __( 'You do not have sufficient permissions to create hosts.', 'hydra-booking' ),
				'data'    => $_tfhb_hosts_settings,
			);
			return rest_ensure_response( $data );
		}
		

		$request = json_decode( file_get_contents( 'php://input' ), true );

		// Check if user is selected
		if ( ! isset( $request['id'] ) && $request['id'] == '' ) {
			return rest_ensure_response(
				array(
					'status'  => false,
					'message' =>  __( 'Select User', 'hydra-booking' ),
				)
			);
		}
		
		$user_id = $request['id'];

		if ( $user_id == 0 ) {
			if ( empty( $request['username'] ) || empty( $request['email'] ) || empty( $request['password'] ) ) {
				return rest_ensure_response(
					array(
						'status'  => false,
						'message' =>  __( 'Please fill all the fields', 'hydra-booking' ),
					)
				);
			}

			// Create User with set user role
			$user_id = wp_create_user( sanitize_text_field( $request['username'] ), sanitize_text_field( $request['password'] ), sanitize_text_field( $request['email'] ) );
			if ( is_wp_error( $user_id ) ) {
				return rest_ensure_response(
					array(
						'status'  => false,
						'message' => $user_id->get_error_message(),
					)
				);
			}

			// Set User Role
			$user = new \WP_User( $user_id );
			$user->set_role( 'tfhb_host' );

		}
		// Get user Data
		$user = get_user_by( 'id', $user_id );

		// Check if user is valid
		if ( empty( $user ) ) {
			return rest_ensure_response(
				array(
					'status'  => false,
					'message' =>  __( 'Invalid User', 'hydra-booking' ),
				)
			);
		}

		// Check if user is already a host
		$host = new Host();

		$hostCheck = $host->get( array( 'user_id' => $user_id ) );
		if ( ! empty( $hostCheck ) ) {
			return rest_ensure_response(
				array(
					'status'  => false,
					'message' =>  __( 'This User is already a host', 'hydra-booking' ),
				)
			);
		}

		$data = array(
			'user_id'        => $user->ID,
			'first_name'     => get_user_meta( $user->ID, 'first_name', true ) != '' ? get_user_meta( $user->ID, 'first_name', true ) : $user->display_name,
			'last_name'      => get_user_meta( $user->ID, 'last_name', true ) != '' ? get_user_meta( $user->ID, 'last_name', true ) : '',
			'email'          => $user->user_email,
			'phone_number'   => '',
			'time_zone'      => '',
			'about'          => '',
			'avatar'         => '',
			'featured_image' => '',
			'status'         => 'activate',
		);

		// get Default Availability
		$Availability = new Availability();
		
		// get default availability
		$getAvailability = $Availability->get(
			array(
				'default_status' => true,
			),
			false,
			true,
		);
		if($getAvailability){
			 $data['availability_type'] = 'settings';
			 $data['availability_id'] = $getAvailability->id;
		}  


		// Insert Host
		$hostInsert = $host->add( $data );
		if ( ! $hostInsert['status'] ) {
			return rest_ensure_response(
				array(
					'status'  => false,
					'message' =>  __( 'Error while creating host', 'hydra-booking' ),
				)
			);
		}
		$hosts_id = $data['user_id'];
		unset( $data['user_id'] );
		$data['host_id'] = $hostInsert['insert_id'];


		

		// $data['host_id'] = $hostInsert['insert_id'];

	
		// Update user Option
		update_user_meta( $user_id, '_tfhb_host', $data );

		// Hosts Lists
		$HostsList = $host->get();

		// Return response
		$data = array(
			'status'  => true,
			'hosts'   => $HostsList,
			'id'      => $hosts_id,
			'message' => __( 'Host Created Successfully', 'hydra-booking' ),
		);

		return rest_ensure_response( $data );
	}

	// Delete Host
	public function DeleteHosts() {
		$request = json_decode( file_get_contents( 'php://input' ), true );
		// Check if user is selected
		$host_id = $request['id'];
		$user_id = $request['user_id'];
		if ( empty( $host_id ) || $host_id == 0 ) {
			return rest_ensure_response(
				array(
					'status'  => false,
					'message' =>  __( 'Invalid Host', 'hydra-booking' ),
				)
			);
		}
		// Delete Host
		$host       = new Host();
		$hostDelete = $host->delete( $host_id );
		if ( ! $hostDelete ) {
			return rest_ensure_response(
				array(
					'status'  => false,
					'message' => __( 'Error while deleting host', 'hydra-booking' ),
				)
			);
		}

		// Delete the user
		require_once ABSPATH . 'wp-admin/includes/user.php';
		$user_meta  = get_userdata( $user_id );
		$user_roles = ! empty( $user_meta->roles[0] ) ? $user_meta->roles[0] : '';
		if ( ! empty( $user_roles ) && 'tfhb_host' == $user_roles ) {
			$deleted = wp_delete_user( $user_id );
		}

		// Update user Option
		delete_user_meta( $user_id, '_tfhb_host' );
		// Hosts Lists
		$HostsList = $host->get();
		// Return response
		$data = array(
			'status'  => true,
			'hosts'   => $HostsList,
			'message' => __( 'Host Deleted Successfully', 'hydra-booking' ),
		);
		return rest_ensure_response( $data );
	}

	// Delete Host
	public function getTheHostData( $request ) {
		
		$id = $request['id'];
		
		// Check if user is selected
		if ( empty( $id ) || $id == 0 ) {
			return rest_ensure_response(
				array(
					'status'  => false,
					'message' =>  __( 'Invalid Host', 'hydra-booking' ),
				)
			);
		}
		// Get Host
		$host     = new Host();
		$HostData = $host->getHostById( $id ); 
		$_tfhb_host_availability_settings = get_user_meta( $HostData->user_id, '_tfhb_host', true );
		if ( ! empty( $_tfhb_host_availability_settings['availability'] ) ) {
			$HostData->availability = $_tfhb_host_availability_settings['availability'];
		}
		if ( empty( $HostData ) ) {
			return rest_ensure_response(
				array(
					'status'  => false,
					'message' => __( 'Invalid Host', 'hydra-booking' ),
				)
			);
		}
		if ( ! empty( $HostData->others_information ) ) {
			$HostData->others_information = json_decode( $HostData->others_information );
		}

		$DateTimeZone = new DateTimeController( 'UTC' );
		$time_zone    = $DateTimeZone->TimeZone();

		// Hosts Global Settings.
		$_tfhb_hosts_settings = get_option( '_tfhb_hosts_settings' );

		// host global availability
		$availability     = get_option( '_tfhb_availability_settings' ); 
		$count = 0;
		$availabilityData = array();
		foreach ( $availability as $key => $value ) {
			$title = isset($value['default_status']) && $value['default_status'] == true ? $value['title'] . '( ' . 'Default' . ' )' : $value['title'];
			$availabilityData[$count]['name'] = $title;
			$availabilityData[ $count ]['value'] =  "" . $value['id'] . "";
			$count++;
		}

		// Availability
		$integrations = array();
		$_tfhb_integration_settings = !empty(get_option( '_tfhb_integration_settings' )) && get_option( '_tfhb_integration_settings' ) != false ? get_option( '_tfhb_integration_settings' ) : array();
		$integrations['zoho_crm_status'] = isset( $_tfhb_integration_settings['zoho_crm']['status'] ) ? $_tfhb_integration_settings['zoho_crm']['status'] : 0;

		// Return response
		$data = array(
			'status'         => true,
			'host'           => $HostData,
			'time_zone'      => $time_zone,
			'settingsAvailabilityData'      => $availabilityData,
			'hosts_settings' => $_tfhb_hosts_settings,
			'integrations' => $integrations,
			'message'        =>  __( 'Host Data', 'hydra-booking' ),
		);
		return rest_ensure_response( $data );
	}

	// Update Host Information
	public function updateHostInformation() {
		$request = json_decode( file_get_contents( 'php://input' ), true );

		// return rest_ensure_response($request['others_information']);
		// Check if user is selected
		$host_id = $request['id'];
		if ( empty( $host_id ) || $host_id == 0 ) {
			return rest_ensure_response(
				array(
					'status'  => false,
					'message' =>  __( 'Invalid Host', 'hydra-booking' ),
				)
			);
		}
		// Get Host
		$host     = new Host();
		$HostData = $host->get( $host_id );

		if ( empty( $HostData ) ) {
			return rest_ensure_response(
				array(
					'status'  => false,
					'message' => __( 'Invalid Host', 'hydra-booking' ),
				)
			);
		}

		// Update Host
		$data       = array(
			'id'                 => $request['id'],
			'first_name'         => $request['first_name'],
			'last_name'          => $request['last_name'],
			'email'              => $request['email'],
			'phone_number'       => $request['phone_number'],
			'about'              => $request['about'],
			'avatar'             => $request['avatar'],
			'featured_image'     => $request['featured_image'],
			'availability_type'  => $request['availability_type'],
			'others_information' => $request['others_information'],
			'availability_id'    => $request['availability_id'],
			'time_zone'          => $request['time_zone'],
			'status'             => $request['status'],
		);
		$hostUpdate = $host->update( $data );
		if ( ! $hostUpdate['status'] ) {
			return rest_ensure_response(
				array(
					'status'  => false,
					'message' =>  __( 'Error while updating host', 'hydra-booking' ),
				)
			);
		}
		// Update user Option
		$data['host_id']      = $host_id;
		$data['availability'] = isset($request['availability']) ? $request['availability'] : array();
		update_user_meta( $host_id, '_tfhb_host', $data );
		// Hosts Lists
		$HostsList = $host->get();
		// Return response
		$data = array(
			'status'  => true,
			'message' =>  __( 'Host Information Updated Successfully', 'hydra-booking' ),
		);
		return rest_ensure_response( $data );
	}

	public function UpdateHostsStatus() {
		$request = json_decode( file_get_contents( 'php://input' ), true );

		$host_id = $request['id'];
		if ( $request['status'] == 1 || 'deactivate' == $request['status'] ) {
			$status = 'activate';
		}
		if ( 'activate' == $request['status'] ) {
			$status = 'deactivate';
		}

		if ( empty( $host_id ) || $host_id == 0 ) {
			return rest_ensure_response(
				array(
					'status'  => false,
					'message' =>  __( 'Invalid Host', 'hydra-booking' ),
				)
			);
		}

		// Get Host
		$host     = new Host();
		$HostData =  $host->getHostById( $host_id );
		$user_id = $HostData->user_id;

		if ( empty( $HostData ) ) {
			return rest_ensure_response(
				array(
					'status'  => false,
					'message' =>  __( 'Invalid Host', 'hydra-booking' ),
				)
			);
		}

		// Update Host
		$data       = array(
			'id'     => $request['id'],
			'status' => $status,
		);
		$hostUpdate = $host->update( $data );
		if ( ! $hostUpdate['status'] ) {
			return rest_ensure_response(
				array(
					'status'  => false,
					'message' =>  __( 'Error while updating host', 'hydra-booking' ),
				)
			);
		}

		// Get User MEta
		$_tfhb_host           = get_user_meta( $user_id, '_tfhb_host', true );
		$_tfhb_host['status'] = $status;

		// Update user Option
		update_user_meta( $user_id, '_tfhb_host', $_tfhb_host );

		// Hosts Lists
		$HostsList = $host->get();

		// Return response
		$data = array(
			'status'  => true,
			'hosts'   => $HostsList,
			'message' =>  __( 'Host Status Updated Successfully', 'hydra-booking' ),
		);

		return rest_ensure_response( $data );
	}

	// Get Integration Settings
	public function GetIntegrationSettings() {
		$request = json_decode( file_get_contents( 'php://input' ), true );

		// Get Host Data
		$host_id  = $request['id'];
		$host     = new Host();
		$hostData = $host->get( $host_id );
		$user_id  = $hostData->user_id;
		
		

		$_tfhb_host_integration_settings = is_array( get_user_meta( $user_id, '_tfhb_host_integration_settings', true ) ) ? get_user_meta( $user_id, '_tfhb_host_integration_settings', true ) : array();

		
		$_tfhb_integration_settings = get_option( '_tfhb_integration_settings' );

		// Google Calendar API
		$google_calendar = isset( $_tfhb_host_integration_settings['google_calendar'] ) ? $_tfhb_host_integration_settings['google_calendar'] : array();

		

		if ( isset($_tfhb_integration_settings['google_calendar']['status']) && $_tfhb_integration_settings['google_calendar']['status']  ) {

			$google_calendar['type']              = 'google_calendar';
			$GoogleCalendar                       = new GoogleCalendar();
			$google_calendar['access_url']        = $GoogleCalendar->GetAccessTokenUrl( $user_id, );
			$google_calendar['status']            = $_tfhb_integration_settings['google_calendar']['status'];
			$google_calendar['connection_status'] = $_tfhb_integration_settings['google_calendar']['connection_status'];

		}else{
			$google_calendar['type']              = 'google_calendar';
			$google_calendar['status']            = false;
			$google_calendar['connection_status'] = false;
		}


		// Zoom
		$zoom_meeting = isset( $_tfhb_host_integration_settings['zoom_meeting'] ) ? $_tfhb_host_integration_settings['zoom_meeting'] : array();
		
		
		
		if ( isset($_tfhb_integration_settings['zoom_meeting']['status']) && $_tfhb_integration_settings['zoom_meeting']['status']  ) {
		
			$zoom_meeting['type']              = 'zoom_meeting';  
			$zoom_meeting['connection_status'] = $_tfhb_integration_settings['zoom_meeting']['connection_status'];

		}else{
			$zoom_meeting['type']              = 'zoom_meeting';
			$zoom_meeting['status']            = false;
			$zoom_meeting['connection_status'] = false;
		}

		

		// Apple Calendar
		$apple_calendar = isset( $_tfhb_host_integration_settings['apple_calendar'] ) ? $_tfhb_host_integration_settings['apple_calendar'] : array();

		if ( isset($_tfhb_integration_settings['apple_calendar']['connection_status']) && $_tfhb_integration_settings['apple_calendar']['connection_status'] == true ) {

			$apple_calendar['type']              = 'calendar';
			$apple_calendar['status']            = $_tfhb_integration_settings['apple_calendar']['status'];
			$apple_calendar['connection_status'] = $_tfhb_integration_settings['apple_calendar']['connection_status'];
			$apple_calendar['apple_id']          = isset( $apple_calendar['apple_id'] ) ? $apple_calendar['apple_id'] : '';
			$apple_calendar['app_password']      = isset( $apple_calendar['app_password'] ) ? $apple_calendar['app_password'] : '';
		}

		// Mailchimp API
		$mailchimp = isset( $_tfhb_host_integration_settings['mailchimp'] ) ? $_tfhb_host_integration_settings['mailchimp'] : array();
		if (isset($_tfhb_integration_settings['mailchimp']['status']) && $_tfhb_integration_settings['mailchimp']['status'] == true ) {

			$mailchimp['type']              = 'mailchimp';
			$mailchimp['status']            = $_tfhb_host_integration_settings['mailchimp']['status'];
			$mailchimp['connection_status'] = $_tfhb_integration_settings['mailchimp']['status'];
			$mailchimp['key']               = $_tfhb_host_integration_settings['mailchimp']['key'];

		}else{
			$mailchimp['type']              = 'mailchimp';
			$mailchimp['status']            = 0; 
			$mailchimp['connection_status']            = 0; 
		}

		// Telegram
		$telegram = isset( $_tfhb_host_integration_settings['telegram'] ) ? $_tfhb_host_integration_settings['telegram'] : array();
		if (isset($_tfhb_integration_settings['telegram']['status']) && $_tfhb_integration_settings['telegram']['status'] == true ) {

			$telegram['type']              = 'telegram';
			$telegram['status']            = $_tfhb_host_integration_settings['telegram']['status'];
			$telegram['connection_status'] = $_tfhb_integration_settings['telegram']['status'];
			$telegram['bot_token']         = $_tfhb_host_integration_settings['telegram']['bot_token'];
			$telegram['chat_id']           = $_tfhb_host_integration_settings['telegram']['chat_id'];

		}else{
			$telegram['type']              = 'telegram';
			$telegram['status']            = 0; 
			$telegram['connection_status'] = 0; 
		}

		// twilio
		$twilio = isset( $_tfhb_host_integration_settings['twilio'] ) ? $_tfhb_host_integration_settings['twilio'] : array();
		if (isset($_tfhb_integration_settings['twilio']['status']) && $_tfhb_integration_settings['twilio']['status'] == true ) {

			$twilio['type']              = 'twilio';
			$twilio['status']            = $_tfhb_host_integration_settings['twilio']['status'];
			$twilio['connection_status'] = $_tfhb_integration_settings['twilio']['status'];
			$twilio['receive_number']    = $_tfhb_host_integration_settings['twilio']['receive_number'];
			$twilio['from_number']       = $_tfhb_host_integration_settings['twilio']['from_number'];
			$twilio['sid']           	 = $_tfhb_host_integration_settings['twilio']['sid'];
			$twilio['token']           	 = $_tfhb_host_integration_settings['twilio']['token'];
			$twilio['otp_type']          = $_tfhb_host_integration_settings['twilio']['otp_type'];

		}else{
			$twilio['type']              = 'twilio';
			$twilio['status']            = 0; 
			$twilio['connection_status'] = 0; 
		}

		// slack
		$slack = isset( $_tfhb_host_integration_settings['slack'] ) ? $_tfhb_host_integration_settings['slack'] : array();
		if (isset($_tfhb_integration_settings['slack']['status']) && $_tfhb_integration_settings['slack']['status'] == true ) {

			$slack['type']              = 'slack';
			$slack['status']            = $_tfhb_host_integration_settings['slack']['status'];
			$slack['connection_status'] = $_tfhb_integration_settings['slack']['status'];
			$slack['endpoint']          = $_tfhb_host_integration_settings['slack']['endpoint'];

		}else{
			$slack['type']              = 'slack';
			$slack['status']            = 0; 
			$slack['connection_status'] = 0; 
		}

		// Zoho
		$zoho = isset( $_tfhb_host_integration_settings['zoho'] ) ? $_tfhb_host_integration_settings['zoho'] : array();
		if (isset($_tfhb_integration_settings['zoho']['status']) && $_tfhb_integration_settings['zoho']['status'] ) {

			$zoho['type']          = 'zoho';
			$zoho['status']        = $_tfhb_host_integration_settings['zoho']['status'];
			$zoho['client_id']     = $_tfhb_host_integration_settings['zoho']['client_id'];
			$zoho['client_secret'] = $_tfhb_host_integration_settings['zoho']['client_secret'];
			$zoho['redirect_url']  = !empty($_tfhb_host_integration_settings['zoho']['redirect_url']) ? $_tfhb_host_integration_settings['zoho']['redirect_url'] : site_url('/wp-json/hydra-booking/v1/integration/zoho-api');
			$zoho['access_token']  = $_tfhb_host_integration_settings['zoho']['access_token'];
			$zoho['modules']       = json_decode( $_tfhb_host_integration_settings['zoho']['modules'] );
			$zoho['refresh_token'] = json_decode( $_tfhb_host_integration_settings['zoho']['refresh_token'] );

		}else{
			$zoho['type']              = 'zoho';
			$zoho['redirect_url']  = site_url('/wp-json/hydra-booking/v1/integration/zoho-api');
			$zoho['status']            = 0; 
			$zoho['connection_status'] = 0;
		}

		// Checked if woo
		$data = array(
			'status'                     => true,
			'integration_settings'       => $_tfhb_host_integration_settings,
			'google_calendar'            => $google_calendar,
			'zoom_meeting'               => $zoom_meeting, 
			'apple_calendar'             => $apple_calendar,
			'mailchimp'                  => $mailchimp,
			'zoho'                       => $zoho,
			'telegram'                   => $telegram,
			'twilio'                     => $twilio,
			'slack'                      => $slack,
			// '_tfhb_integration_settings' => $_tfhb_integration_settings,
		);

		$data = apply_filters( 'tfhb_get_host_integration_settings', $data, $user_id);
		return rest_ensure_response( $data );
	}

	// Update Integration Settings.
	public function UpdateIntegrationSettings() {

		$request = json_decode( file_get_contents( 'php://input' ), true );
		$key     = sanitize_text_field( $request['key'] );
		$data    = $request['value'];
		$host_id = $request['id'];
		$user_id = $request['user_id'];  
		$_tfhb_host_integration_settings = is_array( get_user_meta( $user_id, '_tfhb_host_integration_settings', true ) ) ? get_user_meta( $user_id, '_tfhb_host_integration_settings', true ) : array();

		$_tfhb_integration_settings = get_option( '_tfhb_integration_settings' );
		$responseData = array();
		if ( $key == 'zoom_meeting' ) {

			$zoom = new ZoomServices();
			$response = $zoom->updateHostsZoomSettings( $data, $user_id );
			$_tfhb_host_integration_settings = get_user_meta( $user_id, '_tfhb_host_integration_settings', true );
			if($response['status'] == false){
				return rest_ensure_response( $response );
			}
			$responseData['status'] = true;
			$responseData['type'] =  'zoom_meeting';
			$responseData['message'] = $response['message']; 
		

		} elseif ( $key == 'woo_payment' ) {
			$_tfhb_host_integration_settings['woo_payment']['type']        = sanitize_text_field( $data['type'] );
			$_tfhb_host_integration_settings['woo_payment']['status']      = sanitize_text_field( $data['status'] );
			$_tfhb_host_integration_settings['woo_payment']['woo_payment'] = sanitize_text_field( $data['woo_payment'] );

			// update User Meta
			update_user_meta( $user_id, '_tfhb_host_integration_settings', $_tfhb_host_integration_settings );

			 
			$responseData['status'] = true;
			$responseData['message'] = esc_html(__('Integration Settings Updated Successfully', 'hydra-booking')); 
		} elseif ( $key == 'google_calendar' ) {
			// Get Global Settings
			$_tfhb_host_integration_settings['google_calendar']['type']                 = sanitize_text_field( $data['type'] );
			$_tfhb_host_integration_settings['google_calendar']['status']               = sanitize_text_field( $data['status'] );
			$_tfhb_host_integration_settings['google_calendar']['connection_status']    = isset( $data['secret_key'] ) && ! empty( $data['secret_key'] ) ? 1 : sanitize_text_field( $data['connection_status'] );
			$_tfhb_host_integration_settings['google_calendar']['selected_calendar_id'] = $data['selected_calendar_id'];
			$_tfhb_host_integration_settings['google_calendar']['tfhb_google_calendar'] = $data['tfhb_google_calendar'];

			// update User Meta
			update_user_meta( $user_id, '_tfhb_host_integration_settings', $_tfhb_host_integration_settings );
 
			$responseData['status'] = true;
			$responseData['message'] = esc_html(__('Google Calendar Settings Updated Successfully', 'hydra-booking')); 
		} elseif ( $key == 'outlook_calendar' ) {
			// Get Global Settings
			$_tfhb_host_integration_settings['outlook_calendar']['type']                  = sanitize_text_field( $data['type'] );
			$_tfhb_host_integration_settings['outlook_calendar']['status']                = sanitize_text_field( $data['status'] );
			$_tfhb_host_integration_settings['outlook_calendar']['connection_status']     = isset( $data['secret_key'] ) && ! empty( $data['secret_key'] ) ? 1 : sanitize_text_field( $data['connection_status'] );
			$_tfhb_host_integration_settings['outlook_calendar']['selected_calendar_id']  = $data['selected_calendar_id'];
			$_tfhb_host_integration_settings['outlook_calendar']['tfhb_outlook_calendar'] = $data['tfhb_outlook_calendar'];

			// update User Meta
			update_user_meta( $user_id, '_tfhb_host_integration_settings', $_tfhb_host_integration_settings );
 
			$responseData['status'] = true;
			$responseData['message'] = esc_html(__('Outlook Calendar Settings Updated Successfully', 'hydra-booking')); 
		} elseif ( $key == 'apple_calendar' ) {
			// Get Global Settings
			$_tfhb_host_integration_settings['apple_calendar']['type']              = sanitize_text_field( $data['type'] );
			$_tfhb_host_integration_settings['apple_calendar']['status']            = sanitize_text_field( $data['status'] );
			$_tfhb_host_integration_settings['apple_calendar']['connection_status'] = isset( $data['secret_key'] ) && ! empty( $data['secret_key'] ) ? 1 : sanitize_text_field( $data['connection_status'] );
			$_tfhb_host_integration_settings['apple_calendar']['apple_id']          = $data['apple_id'];
			$_tfhb_host_integration_settings['apple_calendar']['app_password']      = $data['app_password'];

			// update User Meta
			update_user_meta( $user_id, '_tfhb_host_integration_settings', $_tfhb_host_integration_settings );
 
			$responseData['status'] = true;
			$responseData['message'] = esc_html(__('Apple Calendar Settings Updated Successfully', 'hydra-booking'));   
		} elseif ( $key == 'mailchimp' ) {
			$_tfhb_host_integration_settings['mailchimp']['type']   = 'mailchimp';
			$_tfhb_host_integration_settings['mailchimp']['status'] = sanitize_text_field( $data['status'] );
			$_tfhb_host_integration_settings['mailchimp']['key']    = sanitize_text_field( $data['key'] );

			// update User Meta
			update_user_meta( $user_id, '_tfhb_host_integration_settings', $_tfhb_host_integration_settings );

			 
			$responseData['status'] = true;
			$responseData['message'] = esc_html(__('Mailchimp Settings Updated Successfully', 'hydra-booking'));   
		} elseif ( $key == 'telegram_data' ) {
			$_tfhb_host_integration_settings['telegram']['type']   = 'telegram';
			$_tfhb_host_integration_settings['telegram']['status'] = sanitize_text_field( $data['status'] );
			$_tfhb_host_integration_settings['telegram']['bot_token']    = sanitize_text_field( $data['bot_token'] );
			$_tfhb_host_integration_settings['telegram']['chat_id']    = sanitize_text_field( $data['chat_id'] );

			// update User Meta
			update_user_meta( $user_id, '_tfhb_host_integration_settings', $_tfhb_host_integration_settings );

			$responseData['status'] = true;
			$responseData['message'] = esc_html(__('Telegram Settings Updated Successfully', 'hydra-booking'));   
		} elseif ( $key == 'twilio_data' ) {
			$_tfhb_host_integration_settings['twilio']['type']   = 'twilio';
			$_tfhb_host_integration_settings['twilio']['status'] = sanitize_text_field( $data['status'] );
			$_tfhb_host_integration_settings['twilio']['receive_number']    = sanitize_text_field( $data['receive_number'] );
			$_tfhb_host_integration_settings['twilio']['from_number']    = sanitize_text_field( $data['from_number'] );
			$_tfhb_host_integration_settings['twilio']['sid']    = sanitize_text_field( $data['sid'] );
			$_tfhb_host_integration_settings['twilio']['token']    = sanitize_text_field( $data['token'] );
			$_tfhb_host_integration_settings['twilio']['otp_type']    = sanitize_text_field( $data['otp_type'] );

			// update User Meta
			update_user_meta( $user_id, '_tfhb_host_integration_settings', $_tfhb_host_integration_settings );

			$responseData['status'] = true;
			$responseData['message'] = esc_html(__('Twilio Settings Updated Successfully', 'hydra-booking'));   
		} elseif ( $key == 'slack_data' ) {
			$_tfhb_host_integration_settings['slack']['type']   = 'slack';
			$_tfhb_host_integration_settings['slack']['status'] = sanitize_text_field( $data['status'] );
			$_tfhb_host_integration_settings['slack']['endpoint']    = sanitize_text_field( $data['endpoint'] );

			// update User Meta
			update_user_meta( $user_id, '_tfhb_host_integration_settings', $_tfhb_host_integration_settings );

			$responseData['status'] = true;
			$responseData['message'] = esc_html(__('Slack Settings Updated Successfully', 'hydra-booking'));   
		} elseif ( $key == 'zoho' ) {
			$_tfhb_host_integration_settings['zoho']['type']          = 'zoho';
			$_tfhb_host_integration_settings['zoho']['status']        = sanitize_text_field( $data['status'] );
			$_tfhb_host_integration_settings['zoho']['client_id']     = sanitize_text_field( $data['client_id'] );
			$_tfhb_host_integration_settings['zoho']['client_secret'] = sanitize_text_field( $data['client_secret'] );
			$_tfhb_host_integration_settings['zoho']['redirect_url']  = sanitize_url( $data['redirect_url'] );
			$_tfhb_host_integration_settings['zoho']['access_token']  = sanitize_text_field( $data['access_token'] );
			$_tfhb_host_integration_settings['zoho']['refresh_token'] = sanitize_text_field( $data['refresh_token'] );
			$_tfhb_host_integration_settings['zoho']['modules']       = wp_json_encode( $data['modules'] );
			
			// update User Meta
			update_user_meta( $user_id, '_tfhb_host_integration_settings', $_tfhb_host_integration_settings );

			 
			$responseData['status'] = true;
			$responseData['message'] = esc_html(__('Zoho Settings Updated Successfully', 'hydra-booking')); 
			 
		}
		
		// Get Updated Data
		$_tfhb_host_integration_settings = get_user_meta( $user_id, '_tfhb_host_integration_settings', true );
		 
		$responseData['host_integration_settings'] = $_tfhb_host_integration_settings; 
		return rest_ensure_response( $responseData );
	}

		// Get Availability Settings
	public function GetAvailabilitySettings() {
		$request = json_decode( file_get_contents( 'php://input' ), true );

		$DateTimeZone = new DateTimeController( 'UTC' );
		$time_zone    = $DateTimeZone->TimeZone();

		// Get Host Data
		$_tfhb_host_availability_settings = !empty( get_user_meta( $request['id'], '_tfhb_host', true ) ) ? get_user_meta( $request['id'], '_tfhb_host', true ) : array();
		// var_dump($_tfhb_host_availability_settings); exit();
		$data = array(
			'status'       => true,
			'time_zone'    => $time_zone,
			'availability' =>  isset($_tfhb_host_availability_settings['availability']) ? $_tfhb_host_availability_settings['availability'] : array(),
		);
		return rest_ensure_response( $data );
	}

		// Get Single Availability Settings
	public function GetSingleAvailabilitySettings() {
		$request = json_decode( file_get_contents( 'php://input' ), true );

		// Check if user is selected
		if ( empty( $request['host_id'] ) ) {
			return rest_ensure_response(
				array(
					'status'  => false,
					'message' =>  __( 'Host id is Empty', 'hydra-booking' ),
				)
			);
		}

		$host     = new Host();
		$HostData = $host->get( $request['host_id'] );

		// If Host Use existing availability
		if ( ! empty( $HostData->availability_type ) && 'settings' == $HostData->availability_type ) {
			if ( ! empty( $HostData->availability_id ) ) {
				$availability_id = $HostData->availability_id;
				$availability    = get_option( '_tfhb_availability_settings' );

				$filteredAvailability = array_filter(
					$availability,
					function ( $item ) use ( $availability_id ) {
						return $item['id'] == $availability_id;
					}
				);

				// If you expect only one result, you can extract the first item from the filtered array
				$defult_availability = reset( $filteredAvailability );
			} else {
				$defult_availability = array();
			}
		} else {
			// Get Single Host Data
			$_tfhb_host_availability_settings = get_user_meta( $request['host_id'], '_tfhb_host', true );
			$defult_availability              = ! empty( $_tfhb_host_availability_settings['availability'] ) ? $_tfhb_host_availability_settings['availability'][ $request['availability_id'] ] : array();
		}

		$data = array(
			'status'       => true,
			'availability' => $defult_availability,
		);
		return rest_ensure_response( $data );
	}

		// Delete Availability Settings
	public function DeleteAvailabilitySettings() {
		$request = json_decode( file_get_contents( 'php://input' ), true );

		// Get Host Data
		$_tfhb_host_availability_settings = get_user_meta( $request['user_id'], '_tfhb_host', true );
		// Delete Key
		unset( $_tfhb_host_availability_settings['availability'][ $request['key'] ] );
		// Data Update
		$_tfhb_availability_settings = update_user_meta( $request['user_id'], '_tfhb_host', $_tfhb_host_availability_settings );
		// Response
		$data = array(
			'status'       => true,
			'message'     =>  __( 'Availability Settings Deleted Successfully', 'hydra-booking' ),
			'availability' => $_tfhb_host_availability_settings['availability'],
		);
		return rest_ensure_response( $data );
	}


		// Update Availability Settings.
	public function UpdateAvailabilitySettings() {
		$request = json_decode( file_get_contents( 'php://input' ), true );

		// senitaized
		if ( ! isset( $request['host'] ) ) {
			// response
			$data = array(
				'status'  => false,
				'message' =>  __( 'Something Went Wrong, Please Try Again Later.', 'hydra-booking' ),
			);
			return rest_ensure_response( $data );
		}

		$_tfhb_host_info        = !empty(get_user_meta( $request['user_id'], '_tfhb_host', true )) ? get_user_meta( $request['user_id'], '_tfhb_host', true ) : array();
		$tfhb_host_availability = ! empty( $_tfhb_host_info['availability'] ) ? $_tfhb_host_info['availability'] : array();

		$availability['id']          = isset( $request['id'] ) ? sanitize_text_field( $request['id'] ) : '';
		$availability['user_id']     = isset( $request['user_id'] ) ? sanitize_text_field( $request['user_id'] ) : '';
		$availability['title']       = sanitize_text_field( $request['title'] );
		$availability['default_status']       = false;
		$availability['time_zone']   = sanitize_text_field( $request['time_zone'] );
		$availability['date_status'] = sanitize_text_field( $request['date_status'] );
		$availability['override']    = '';
		$availability['status']      = 'active';

		// time slots
		foreach ( $request['time_slots'] as $key => $value ) {

			$availability['time_slots'][ $key ]['day']    = sanitize_text_field( $value['day'] );
			$availability['time_slots'][ $key ]['status'] = sanitize_text_field( $value['status'] );

			foreach ( $value['times'] as $key2 => $value2 ) {
				$availability['time_slots'][ $key ]['times'][ $key2 ]['start'] = sanitize_text_field( $value2['start'] );
				$availability['time_slots'][ $key ]['times'][ $key2 ]['end']   = sanitize_text_field( $value2['end'] );
			}
		}

		// Date Slots
		foreach ( $request['date_slots'] as $key => $value ) {

			if( !empty($value['date']) ){
				$availability['date_slots'][ $key ]['date']      = sanitize_text_field( $value['date'] );
				$availability['date_slots'][ $key ]['available'] = sanitize_text_field( $value['available'] );

				foreach ( $value['times'] as $key2 => $value2 ) {
					$availability['date_slots'][ $key ]['times'][ $key2 ]['start'] = sanitize_text_field( $value2['start'] );
					$availability['date_slots'][ $key ]['times'][ $key2 ]['end']   = sanitize_text_field( $value2['end'] );
				}
			}
		}

		if( empty($request['date_slots']) ){
			$availability['date_slots'] = array();
		}

		if ( $availability['id'] == '' ) {

			$_tfhb_host_info['availability'][] = $availability;

		} else {

			foreach ( $tfhb_host_availability as $key => $value ) {

				if ( $key == $availability['id'] ) {
					$_tfhb_host_info['availability'][ $key ] = $availability;
				}
			}
		}
		if ( $availability['id'] == ''  ) {
			// Host Availability DB
			$availbility_data = array(
				'host'        => isset( $request['host'] ) ? sanitize_text_field( $request['host'] ) : '',
				'title'       => $availability['title'],
				'default_status'       => false,
				'time_zone'   => $availability['time_zone'],
				'override'    => '',
				'time_slots'  => $request['time_slots'],
				'date_status' => $availability['date_status'],
				'date_slots'  => $request['date_slots'],
				'status'      => 'active',
				'created_at'  => gmdate( 'y-m-d' ),
				'updated_at'  => gmdate( 'y-m-d' ),
			);

			$hostAvailability = new Availability();
			$insert           = $hostAvailability->add( $availbility_data );

			if ( ! $insert['status'] ) {
				return rest_ensure_response(
					array(
						'status'  => false,
						'message' =>  __( 'Error while creating host', 'hydra-booking' ),
					)
				);
			}
			$host_insert_availablekey = count( $tfhb_host_availability );
			$_tfhb_host_info['availability'][ $host_insert_availablekey ]['id'] = $insert['insert_id'];
			$_tfhb_host_info['availability'][ $host_insert_availablekey ]['available_id'] = $insert['insert_id'];
		} else {
			// Host Availability DB
			$availbility_data = array(
				'id'          => $request['available_id'],
				'host'        => isset( $request['host'] ) ? sanitize_text_field( $request['host'] ) : '',
				'title'       => $availability['title'],
				'default_status'       => false,
				'time_zone'   => $availability['time_zone'],
				'override'    => '',
				'time_slots'  => serialize( $request['time_slots'] ),
				'date_status' => $availability['date_status'],
				'date_slots'  => serialize( $request['date_slots'] ),
				'status'      => 'active',
				'created_at'  => gmdate( 'y-m-d' ),
				'updated_at'  => gmdate( 'y-m-d' ),
			);

			$hostAvailability = new Availability();
			$hostAvailability->update( $availbility_data );

			$_tfhb_host_info['availability'][ $request['id'] ]['available_id'] = $request['available_id'];
		}

		// update user meta
		$_tfhb_availability_settings = update_user_meta( $request['user_id'], '_tfhb_host', $_tfhb_host_info );

		$_tfhb_host_info = get_user_meta( $request['user_id'], '_tfhb_host', true );
		// response
		$data = array(
			'status'       => true,
			'availability' => $_tfhb_host_info['availability'],
			'message'      =>  __( 'Availability Updated Successfully', 'hydra-booking' ),
		);
		return rest_ensure_response( $data );
	}



		/**
		 * Fetch Integration Settings
		 */
	public function FetchIntegrationSettings() {
		$request = json_decode( file_get_contents( 'php://input' ), true );
		echo 'fetch';
	}


	/**
	 * Update Host Mail 
	 * 
	 */

	public function update_host_email($user_id, $old_user_data) {
		// Get the updated user data
		$user = get_userdata($user_id);
	
		// Check if the email has changed
		if ($old_user_data->user_email !== $user->user_email) {
			 // Get
			 $host = new Host();
			 $HostData = $host->getHostByUserId( $user_id );

			 if($HostData == false){
				 return false;
			 }

			 $HostData->email = $user->user_email;
			 $host->update( (array) $HostData );

			 // Update user Option
			 return true;

		}
	}

}
