<?php
namespace HydraBooking\Admin\Controller;

// Use Namespace
use HydraBooking\Admin\Controller\RouteController;
use HydraBooking\Admin\Controller\DateTimeController;
use HydraBooking\Admin\Controller\CountryController;
use HydraBooking\Services\Integrations\Woocommerce\WooBooking;
use HydraBooking\Admin\Controller\Helper;

// Use DB
use HydraBooking\DB\Meeting;
use HydraBooking\DB\Host;

if ( ! defined( 'ABSPATH' ) ) {
	exit; }

class MeetingController {


	// constaract
	public function __construct() {
	}

	public function init() {
	}

	public function create_endpoint() {
		register_rest_route(
			'hydra-booking/v1',
			'/meetings/lists',
			array(
				'methods'  => 'GET',
				'callback' => array( $this, 'getMeetingsData' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
			)
		);
		register_rest_route(
			'hydra-booking/v1',
			'/meetings/create',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'CreateMeeting' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
			)
		);
		register_rest_route(
			'hydra-booking/v1',
			'/meetings/delete',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'DeleteMeeting' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
			)
		);
		// Get Single Meeting based on id
		register_rest_route(
			'hydra-booking/v1',
			'/meetings/(?P<id>[0-9]+)',
			array(
				'methods'  => 'GET',
				'callback' => array( $this, 'getMeetingData' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
			)
		);
		register_rest_route(
			'hydra-booking/v1',
			'/meetings/details/update',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'updateMeeting' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
			)
		);

		register_rest_route(
			'hydra-booking/v1',
			'/meetings/clone',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'cloneMeeting' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
			)
		);

		register_rest_route(
			'hydra-booking/v1',
			'/meetings/webhook/update',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'updateMeetingWebhook' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
			)
		);
		register_rest_route(
			'hydra-booking/v1',
			'/meetings/webhook/delete',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'deleteMeetingWebhook' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
			)
		);
	 

		register_rest_route(
			'hydra-booking/v1',
			'/meetings/integration/update',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'updateMeetingIntegration' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
			)
		);
		register_rest_route(
			'hydra-booking/v1',
			'/meetings/integration/delete',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'deleteMeetingIntegration' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
			)
		);
		register_rest_route(
			'hydra-booking/v1',
			'/meetings/integration/fields',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'getIntegrationModulsFields' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
			)
		);

		register_rest_route(
			'hydra-booking/v1',
			'/meetings/filter',
			array(
				'methods'  => 'GET',
				'callback' => array( $this, 'filterMeetings' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
				'args'     => array(
					'title' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		// Meeting Category Endpoint
		register_rest_route(
			'hydra-booking/v1',
			'/meetings/categories',
			array(
				'methods'  => 'GET',
				'callback' => array( $this, 'getMeetingsCategories' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
			)
		);
		register_rest_route(
			'hydra-booking/v1',
			'/meetings/categories/create-update',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'createupdateMeeting' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
			)
		);
		register_rest_route(
			'hydra-booking/v1',
			'/meetings/categories/delete',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'DeleteCategory' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
			)
		);

		// Get Single Host based on id
		register_rest_route(
			'hydra-booking/v1',
			'/meetings/single-host-availability/(?P<id>[0-9]+)',
			array(
				'methods'  => 'GET',
				'callback' => array( $this, 'getTheHostAvailabilityData' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_integrations_permission'),
			)
		);

		register_rest_route(
			'hydra-booking/v1',
			'/meetings/question/forms-list',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'getQuestionFormsList' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
			)
		);


		register_rest_route(
			'hydra-booking/v1',
			'/meetings/payment/payment-method',
			array(
				'methods'  => 'GET',
				'callback' => array( $this, 'fetchMeetingsPaymentIntegration' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
			)
		);
	}
	public function getMeetingList() {
		$current_user = wp_get_current_user();
		// get user role
		$current_user_role = ! empty( $current_user->roles[0] ) ? $current_user->roles[0] : '';
		$current_user_id   = $current_user->ID;

		// Meeting Lists
		$meeting = new Meeting();

		if ( ! empty( $current_user_role ) && 'administrator' == $current_user_role ) {
			$MeetingsList = $meeting->get();
		} 

		if ( ! empty( $current_user_role ) && 'tfhb_host' == $current_user_role ) {
			$MeetingsList = $meeting->get( null, null, $current_user_id );
		}

		// add meeting permalink key into the meeting list using post id using array map
		$MeetingsList = array_map(
			function ( $meeting ) {
				$meeting->permalink = get_permalink( $meeting->post_id );
				return $meeting;
			},
			$MeetingsList
		);
		return $MeetingsList;
	}
	// Meeting List
	public function getMeetingsData() {

		$MeetingsList = $this->getMeetingList();

		// Return response
		$data = array(
			'status'   => true,
			'meetings' => $MeetingsList,
			'message'  => __( 'Meeting Data Successfully Retrieve!', 'hydra-booking' ),
		);
		return rest_ensure_response( $data );
	}

	// getMeetingsCategories List
	public function getMeetingsCategories() {

		$terms = get_terms(
			array(
				'taxonomy'   => 'meeting_category',
				'hide_empty' => false, // Set to true to hide empty terms
			)
		);
		// Prepare the response data
		$term_array = array();
		foreach ( $terms as $term ) {
			$term_array[] = array(
				'id'          => $term->term_id,
				'name'        => $term->name,
				'description' => $term->description,
				'slug'        => $term->slug,
			);
		}

		// Return response
		$data = array(
			'status'   => true,
			'category' => $term_array,
			'message'  =>  __( 'Meeting Category Data Successfully Retrieve!', 'hydra-booking' ),
		);
		return rest_ensure_response( $data );
	}

	// createupdateMeeting
	public function createupdateMeeting() {
		$request = json_decode( file_get_contents( 'php://input' ), true );

		// Sanitize data
		$title       = ! empty( $request['title'] ) ? sanitize_text_field( $request['title'] ) : 'No Title';
		$description = ! empty( $request['description'] ) ? sanitize_text_field( $request['description'] ) : '';

		// Check if taxonomy is registered
		if ( ! taxonomy_exists( 'meeting_category' ) ) {
			return rest_ensure_response(
				array(
					'status'  => false,
					'message' => __( 'Invalid taxonomy.', 'hydra-booking' ),
				)
			);
		}

		if ( empty( $request['id'] ) ) {
			// Insert the term
			$term = wp_insert_term(
				$title,   // The term
				'meeting_category', // The taxonomy
				array(
					'description' => $description,
					'slug'        => sanitize_title( $title ),
				)
			);

			// Check for errors
			if ( is_wp_error( $term ) ) {
				return rest_ensure_response(
					array(
						'status'  => false,
						'message' => $term->get_error_message(),
					)
				);
			}
		} else {
			// Update the term
			$term_id = intval( $request['id'] );
			$term    = wp_update_term(
				$term_id,
				'meeting_category',
				array(
					'name'        => $title,
					'description' => $description,
					'slug'        => sanitize_title( $title ),
				)
			);

			// Check for errors
			if ( is_wp_error( $term ) ) {
				return rest_ensure_response(
					array(
						'status'  => false,
						'message' => $term->get_error_message(),
					)
				);
			}
		}

		$terms = get_terms(
			array(
				'taxonomy'   => 'meeting_category',
				'hide_empty' => false, // Set to true to hide empty terms
			)
		);
		// Prepare the response data
		$term_array = array();
		foreach ( $terms as $term ) {
			$term_array[] = array(
				'id'          => $term->term_id,
				'name'        => $term->name,
				'description' => $term->description,
				'slug'        => $term->slug,
			);
		}

		// Success response
		return rest_ensure_response(
			array(
				'status'   => true,
				'category' => $term_array,
				'message'  => empty( $request['id'] ) ? __('Meeting Category Successfully Added!', 'hydra-booking') : __('Meeting Category Successfully Updated!', 'hydra-booking'),
			)
		);
	}

	// Webhook Integrations
	public function updateMeetingWebhook() {
		$request = json_decode( file_get_contents( 'php://input' ), true );

		// Get Meeting
		$meeting     = new Meeting();
		$MeetingData = $meeting->get( $request['meeting_id'] );

		// Decode existing webhook data if it exists
		$webHookdata = ! empty( $MeetingData->webhook ) ? json_decode( $MeetingData->webhook, true ) : array();

		$key = isset( $request['key'] ) ? $request['key'] : '';

		// New webhook data to be updated
		$newWebHookdata = array(
			'webhook'        => ! empty( $request['webhook'] ) ? $request['webhook'] : '',
			'url'            => ! empty( $request['url'] ) ? $request['url'] : '',
			'request_method' => ! empty( $request['request_method'] ) ? $request['request_method'] : '',
			'request_format' => ! empty( $request['request_format'] ) ? $request['request_format'] : '',
			'events'         => ! empty( $request['events'] ) ? $request['events'] : '',
			'request_body'   => ! empty( $request['request_body'] ) ? $request['request_body'] : '',
			'request_header' => ! empty( $request['request_header'] ) ? $request['request_header'] : '',
			'headers'        => ! empty( $request['headers'] ) ? $request['headers'] : '',
			'bodys'          => ! empty( $request['bodys'] ) ? $request['bodys'] : '',
			'status'         => ! empty( $request['status'] ) ? $request['status'] : '',
		);

		if ( $key !== '' && isset( $webHookdata[ $key ] ) ) {
			// Update the existing webhook data at the specified key
			$webHookdata[ $key ] = $newWebHookdata;
		} else {
			// Append the new webhook data
			$webHookdata[] = $newWebHookdata;
		}

		// Encode the updated webhook data back to JSON
		$encodedWebHookdata = wp_json_encode( $webHookdata );

		$data = array(
			'id'      => $request['meeting_id'],
			'webhook' => $encodedWebHookdata,
		);

		// Update the meeting with the new webhook data
		$MeetingUpdate = $meeting->update( $data );

		// Retrieve updated meeting data
		$updateMeetingData = $meeting->get( $request['meeting_id'] );

		return rest_ensure_response(
			array(
				'status'  => true,
				'webhook' => $updateMeetingData->webhook,
				'message' =>  __( 'Webhook Successfully Updated!', 'hydra-booking' ),
			)
		);
	}

	// Webhook Delete
	public function deleteMeetingWebhook( $request ) {
		// Get Meeting
		$meeting     = new Meeting();
		$MeetingData = $meeting->get( $request['meeting_id'] );

		$key = $request['key'];

		// Decode existing webhook data if it exists
		$webHookdata = ! empty( $MeetingData->webhook ) ? json_decode( $MeetingData->webhook, true ) : array();

		// Check if the key exists in the array
		if ( isset( $webHookdata[ $key ] ) ) {
			// Remove the element at the specified key
			unset( $webHookdata[ $key ] );

			// Re-index the array to maintain sequential keys
			$webHookdata = array_values( $webHookdata );

			// Encode the updated webhook data back to JSON
			$encodedWebHookdata = wp_json_encode( $webHookdata );

			// Update the meeting with the new webhook data
			$data              = array(
				'id'      => $request['meeting_id'],
				'webhook' => $encodedWebHookdata,
			);
			$MeetingUpdate     = $meeting->update( $data );
			$updateMeetingData = $meeting->get( $request['meeting_id'] );

			return rest_ensure_response(
				array(
					'status'  => true,
					'webhook' => $updateMeetingData->webhook,
					'message' =>  __( 'Webhook Successfully Deleted!', 'hydra-booking' ),
				)
			);
		} else {
			return rest_ensure_response(
				array(
					'status'  => false,
					'message' =>  __( 'Webhook key does not exist!', 'hydra-booking' ),
				)
			);
		}
	}


	// Integrations
	public function updateMeetingIntegration() {
		$request = json_decode( file_get_contents( 'php://input' ), true );

		// Get Meeting
		$meeting     = new Meeting();
		$MeetingData = $meeting->get( $request['meeting_id'] );

		// Decode existing integrations data if it exists
		$Integrationsdata = ! empty( $MeetingData->integrations ) ? json_decode( $MeetingData->integrations, true ) : array();

		$key = isset( $request['key'] ) ? $request['key'] : '';

		// New webhook data to be updated
		$newIntegrationsdata = array(
			'title'    => ! empty( $request['title'] ) ? $request['title'] : '',
			'webhook'  => ! empty( $request['webhook'] ) ? $request['webhook'] : '',
			'bodys'    => ! empty( $request['bodys'] ) ? $request['bodys'] : '',
			'events'   => ! empty( $request['events'] ) ? $request['events'] : '',
			'url'      => ! empty( $request['url'] ) ? $request['url'] : '',
			'audience' => 'Mailchimp' == $request['webhook'] && ! empty( $request['audience'] ) ? $request['audience'] : '',
			'tags'     => 'FluentCRM' == $request['webhook'] && ! empty( $request['tags'] ) ? $request['tags'] : '',
			'lists'    => 'FluentCRM' == $request['webhook'] && ! empty( $request['lists'] ) ? $request['lists'] : '',
			'modules'  => 'ZohoCRM' == $request['webhook'] && ! empty( $request['modules'] ) ? $request['modules'] : '',
			'fields'   => ! empty( $request['fields'] ) ? $request['fields'] : '',
			'status'   => ! empty( $request['status'] ) ? $request['status'] : '',
			'request_body'   => ! empty( $request['request_body'] ) ? $request['request_body'] : '',
		);

		if ( $key !== '' && isset( $Integrationsdata[ $key ] ) ) {
			// Update the existing webhook data at the specified key
			$Integrationsdata[ $key ] = $newIntegrationsdata;
		} else {
			// Append the new webhook data
			$Integrationsdata[] = $newIntegrationsdata;
		}

		// Encode the updated webhook data back to JSON
		$encodedIntegrationsdata = wp_json_encode( $Integrationsdata );

		$data = array(
			'id'           => $request['meeting_id'],
			'integrations' => $encodedIntegrationsdata,
		);

		// Update the meeting with the new webhook data
		$MeetingUpdate = $meeting->update( $data );

		// Retrieve updated meeting data
		$updateMeetingData = $meeting->get( $request['meeting_id'] );

		return rest_ensure_response(
			array(
				'status'       => true,
				'integrations' => $updateMeetingData->integrations,
				'message'      =>  __( 'Integrations Successfully Updated!', 'hydra-booking' ),
			)
		);
	}

	// Integration Delete
	public function deleteMeetingIntegration( $request ) {
		// Get Meeting
		$meeting     = new Meeting();
		$MeetingData = $meeting->get( $request['meeting_id'] );

		$key = $request['key'];

		// Decode existing webhook data if it exists
		$Integrationsdata = ! empty( $MeetingData->integrations ) ? json_decode( $MeetingData->integrations, true ) : array();

		// Check if the key exists in the array
		if ( isset( $Integrationsdata[ $key ] ) ) {
			// Remove the element at the specified key
			unset( $Integrationsdata[ $key ] );

			// Re-index the array to maintain sequential keys
			$Integrationsdata = array_values( $Integrationsdata );

			// Encode the updated Integrations data back to JSON
			$encodedIntegrationsdata = wp_json_encode( $Integrationsdata );

			// Update the meeting with the new Integrations data
			$data              = array(
				'id'           => $request['meeting_id'],
				'integrations' => $encodedIntegrationsdata,
			);
			$MeetingUpdate     = $meeting->update( $data );
			$updateMeetingData = $meeting->get( $request['meeting_id'] );

			return rest_ensure_response(
				array(
					'status'       => true,
					'integrations' => $updateMeetingData->integrations,
					'message'      =>  __( 'Integrations Successfully Deleted!', 'hydra-booking' ),
				)
			);
		} else {
			return rest_ensure_response(
				array(
					'status'  => false,
					'message' =>  __( 'Integrations key does not exist!', 'hydra-booking' ),
				)
			);
		}
	}

	// Category Delete
	public function DeleteCategory() {
		$request = json_decode( file_get_contents( 'php://input' ), true );

		if ( empty( $request['id'] ) ) {
			return rest_ensure_response(
				array(
					'status'  => false,
					'message' =>  __( 'Term ID is required.', 'hydra-booking' ),
				)
			);
		}
		$term_id = intval( $request['id'] );
		$result  = wp_delete_term( $term_id, 'meeting_category' );

		$terms = get_terms(
			array(
				'taxonomy'   => 'meeting_category',
				'hide_empty' => false, // Set to true to hide empty terms
			)
		);
		// Prepare the response data
		$term_array = array();
		foreach ( $terms as $term ) {
			$term_array[] = array(
				'id'          => $term->term_id,
				'name'        => $term->name,
				'description' => $term->description,
				'slug'        => $term->slug,
			);
		}

		return rest_ensure_response(
			array(
				'status'   => true,
				'category' => $term_array,
				'message'  =>  __( 'Meeting Category Successfully Deleted!', 'hydra-booking' ),
			)
		);
	}

	// Meeting Filter
	public function filterMeetings( $request ) { 
		// Capability check: only users with tfhb_manage_options may filter
		if ( ! current_user_can( 'tfhb_manage_meetings' ) ) {
			return rest_ensure_response(
				array(
					'status'  => false,
					'message' => __( 'You do not have permission to filter meetings.', 'hydra-booking' ),
				)
			);
		}
		$filterData = $request->get_param( 'filterData' ); 
		// Filter meetings by current user if not admin
		$current_user = wp_get_current_user();
		$current_user_role = ! empty( $current_user->roles[0] ) ? $current_user->roles[0] : '';
		$current_user_id   = $current_user->ID;

		if ( ! empty( $current_user_role ) && 'tfhb_host' == $current_user_role ) { 
			$filterData['user_id'] = $current_user_id;
		}
		
		// Meeting Lists
		$meeting      = new Meeting();
		$MeetingsList = $meeting->get( '', $filterData );

		// Return response
		$data = array(
			'status'   => true,
			'meetings' => $MeetingsList,
			'message'  =>  __( 'Meeting Data Successfully Retrieve!', 'hydra-booking' ),
		);
		return rest_ensure_response( $data );
	}

	// Create meetings
	public function CreateMeeting() {
		$request      = json_decode( file_get_contents( 'php://input' ), true );
		$request_data = $request['data'];
		// Get Current User
		$current_user = wp_get_current_user();
		// get user id
		$current_user_id = $current_user->ID;
		$host = new Host ();
		$host_data = $host->getHostByUserId( $current_user_id ); 
		
		// if host is not found, return error 
		if( empty($host_data) ){
			return rest_ensure_response(
				array(
					'status'  => false,
					'message' =>  __( 'Host not found', 'hydra-booking' ),
				)
			);
		}

		if(tfhb_is_pro_active() == false && $request_data['meeting_type'] == 'one-to-group'){
			return rest_ensure_response(
				array(
					'status'  => false,
					'message' =>  __( 'Please activate the pro version to create one to group meeting', 'hydra-booking' ),
				)
			);
		}
 

		// Create an array to store the post data for meeting the current row
		$meeting_post_data = array(
			'post_type'   => 'tfhb_meeting',
			'post_title'  => esc_html( 'No Title' ),
			'post_status' => 'publish',
			'post_author' => $current_user_id,
		);
		$meeting_post_id   = wp_insert_post( $meeting_post_data );
		
		$data = array(
			'user_id'      => $current_user_id,
			'host_id'      => $host_data->id,
			'meeting_type' => isset( $request_data['meeting_type'] ) ? sanitize_text_field( $request_data['meeting_type'] ) : '',
			'post_id'      => $meeting_post_id,
			'created_by'   => $current_user_id,
			'updated_by'   => $current_user_id,
			'created_at'   => gmdate( 'Y-m-d' ),
			'updated_at'   => gmdate( 'Y-m-d' ),
			'status'       => 'draft',
		);

		// Check if user is already a meeting
		$meeting = new Meeting();
		// Insert meeting
		$meetingInsert = $meeting->add( $data );
		if ( ! $meetingInsert['status'] ) {
			return rest_ensure_response(
				array(
					'status'  => false,
					'message' =>  __( 'Error while creating meeting', 'hydra-booking' ),
				)
			);
		}
		$meetings_id = $meetingInsert['insert_id'];

		// Meetings Id into Post Meta
		update_post_meta( $meeting_post_id, '__tfhb_meeting_id', $meetings_id );

		// meetings Lists
		$meetingsList = $meeting->get();

		// Return response
		$data = array(
			'status'   => true,
			'meetings' => $meetingsList,
			'id'       => $meetings_id,
			'message'  => __( 'Meeting Created Successfully!', 'hydra-booking' ),
		);

		return rest_ensure_response( $data );
	}

	// Delete Meeting
	public function DeleteMeeting() {
		$request = json_decode( file_get_contents( 'php://input' ), true );
		// Check if user is selected
		$meeting_id = $request['id'];
		$post_id    = $request['post_id'];
		if ( empty( $meeting_id ) || $meeting_id == 0 ) {
			return rest_ensure_response(
				array(
					'status'  => false,
					'message' =>  __( 'Invalid Meeting', 'hydra-booking' ),
				)
			);
		}

		$current_user = wp_get_current_user();
		// get user role
		$current_user_role = ! empty( $current_user->roles[0] ) ? $current_user->roles[0] : '';
		$current_user_id   = $current_user->ID;

		// Delete Meeting
		$meeting       = new Meeting();
		$meetingDelete = $meeting->delete( $meeting_id );
		if ( ! $meetingDelete ) {
			
			return rest_ensure_response(
				array(
					'status'  => false, 
					'message' =>  __( 'Error while deleting meeting', 'hydra-booking' ),
				)
			);
		}

		// Delete Post and Post Meta
		if ( ! empty( $post_id ) ) {
			// Delete Post
			wp_delete_post( $post_id, true );
			// Delete Post Meta
			delete_post_meta( $post_id, '__tfhb_meeting_opt' );
		}

		// Meeting Lists
		$MeetingsList = $this->getMeetingList();  
		// Return response
		$data = array(
			'status'   => true,
			'meetings' => $MeetingsList,
			'data'     => $current_user_id,
			'message'  => __( 'Meeting Deleted Successfully!', 'hydra-booking' ),
		);
		return rest_ensure_response( $data );
	}

	private function ensureBuilderKeyExists(&$notifications) {
		if(is_array($notifications)|| is_object($notifications)){
			foreach ($notifications as $role => &$notificationsData) {
				foreach ($notificationsData as $key => &$notification) {
					if (!isset($notification['builder'])) {
						$notification['builder'] = '';
					}
				}
			}
		}
    }

	private function ensure_notification_channel_defaults( &$notification, $channel ) {
		// Decode if it's a string
		if ( ! isset( $notification ) || ! is_object( $notification ) ) {
			if ( is_string( $notification ) ) {
				$decoded = json_decode( $notification );
				$notification = ( is_object( $decoded ) || is_array( $decoded ) ) ? (object) $decoded : new \stdClass();
			} else {
				$notification = new \stdClass();
			}
		}
	
		// Make sure the channel exists
		if ( ! isset( $notification->$channel ) || ! is_object( $notification->$channel ) ) {
			$notification->$channel = new \stdClass();
		}
	
		$default_notification_data =  new Helper();
		$_tfhb_default_notification_settings = $default_notification_data->get_default_notification_template(); 
		
		if($channel=='slack' || $channel=='twilio' || $channel=='telegram'){
			$defaultKeys = [
				'booking_confirmation',
				'booking_cancel',
				'booking_reschedule',
			];
		}else{
			$defaultKeys = [
				'booking_confirmation',
				'booking_pending',
				'booking_cancel',
				'booking_reschedule',
				'booking_reminder',
			];
		}
	
		foreach ( $defaultKeys as $key ) {
			if ( ! isset( $notification->{$channel}->$key ) ) {
				$notification->{$channel}->$key = (object) [
					'status' => 0,
					'body' => !empty($_tfhb_default_notification_settings[$channel][$key]['body']) ? $_tfhb_default_notification_settings[$channel][$key]['body'] : '',
					'builder' => '',
				];
			}
		}
	}
	

	// Get Single Meeting
	public function getMeetingData( $request ) {
		$id = $request['id'];
		// Check if user is selected
		if ( empty( $id ) || $id == 0 ) {
			return rest_ensure_response(
				array(
					'status'  => false,
					'message' =>  __( 'Invalid Meeting', 'hydra-booking' ),
				)
			);
		}
		// Get Meeting
		$meeting     = new Meeting();
		$MeetingData = $meeting->get( $id );

		// Integration
		$integrations = array();

		if ( empty( $MeetingData ) ) {
			return rest_ensure_response(
				array(
					'status'  => false,
					'message' =>  __( 'Invalid Meeting', 'hydra-booking' ),
				)
			);
		}

		// Notification
		if ( empty( $MeetingData->notification ) ) {
			$_tfhb_notification_settings = get_option( '_tfhb_notification_settings' );

			if(empty($_tfhb_notification_settings)){
				$default_notification =  new Helper();
				$_tfhb_notification_settings = $default_notification->get_default_notification_template(); 
			}
			$MeetingData->notification   = $_tfhb_notification_settings;
		}

		$this->ensureBuilderKeyExists($MeetingData->notification);
		
		// Ensure slack, telegram, twilio default notification channels exist
		if(isset($MeetingData->notification)){
			// Work with objects for consistency
			$isArray = is_array($MeetingData->notification);
			
			// Convert to object for processing
			if($isArray){
				$MeetingData->notification = json_decode(json_encode($MeetingData->notification));
			}
			
			// Now work with object
			if(is_object($MeetingData->notification)){
				if(empty($MeetingData->notification->slack)){
					$this->ensure_notification_channel_defaults( $MeetingData->notification, 'slack' );
				}
				if(empty($MeetingData->notification->telegram)){
					$this->ensure_notification_channel_defaults( $MeetingData->notification, 'telegram' );
				}
				if(empty($MeetingData->notification->twilio)){
					$this->ensure_notification_channel_defaults( $MeetingData->notification, 'twilio' );
				}
			}
			
			// Convert back to array if it was originally an array
			if($isArray){
				$MeetingData->notification = json_decode(json_encode($MeetingData->notification), true);
			} 
		}
		

		// Integration
		$_tfhb_integration_settings = !empty(get_option( '_tfhb_integration_settings' )) && get_option( '_tfhb_integration_settings' ) != false ? get_option( '_tfhb_integration_settings' ) : array();
		if ( ! file_exists( WP_PLUGIN_DIR . '/' . 'woocommerce/woocommerce.php' ) ) {
			$woo_connection_status = 0;

		} elseif ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			$woo_connection_status = 0;
		} else {
			$woo_connection_status = 1;
		}

		if ( ! isset( $_tfhb_integration_settings['woo_payment'] ) ) {
			$_tfhb_integration_settings['woo_payment']['type']              = 'type';
			$_tfhb_integration_settings['woo_payment']['status']            = 0;
			$_tfhb_integration_settings['woo_payment']['connection_status'] = $woo_connection_status;
		} else {
			$_tfhb_integration_settings['woo_payment']['connection_status'] = $woo_connection_status;
		}
		// if(empty($MeetingData->payment_meta)){
		// $MeetingData->payment_meta = $_tfhb_integration_settings;
		// }

		// Time Zone
		$DateTimeZone = new DateTimeController( 'UTC' );
		$time_zone    = $DateTimeZone->TimeZone();

		// WooCommerce Product
		$woo_commerce = new WooBooking();
		$wc_product   = $woo_commerce->getAllProductList();

		// Webhook status
		$setting_webhook = isset( $_tfhb_integration_settings['webhook']['status'] ) ? $_tfhb_integration_settings['webhook']['status'] : 0;

		// google  Meeting 
		$integrations['google_calendar_status'] = isset( $_tfhb_integration_settings['google_calendar']['status'] ) ? $_tfhb_integration_settings['google_calendar']['status'] : 0;
		$integrations['outlook_calendar_status'] = isset( $_tfhb_integration_settings['outlook_calendar']['status'] ) ? $_tfhb_integration_settings['outlook_calendar']['status'] : 0;
		
		// Zoom Meeting
		$integrations['zoom_meeting_status'] = isset( $_tfhb_integration_settings['zoom_meeting']['status'] ) ? $_tfhb_integration_settings['zoom_meeting']['status'] : 0;
		$integrations['cf7_status'] = isset( $_tfhb_integration_settings['cf7']['status'] ) ? $_tfhb_integration_settings['cf7']['status'] : 0;
		$integrations['fluent_status'] = isset( $_tfhb_integration_settings['fluent']['status'] ) ? $_tfhb_integration_settings['fluent']['status'] : 0;
		$integrations['forminator_status'] = isset( $_tfhb_integration_settings['forminator']['status'] ) ? $_tfhb_integration_settings['forminator']['status'] : 0;
		$integrations['gravity_status'] = isset( $_tfhb_integration_settings['gravity']['status'] ) ? $_tfhb_integration_settings['gravity']['status'] : 0;
		$integrations['webhook_status'] = isset( $_tfhb_integration_settings['webhook']['status'] ) ? $_tfhb_integration_settings['webhook']['status'] : 0;
		$integrations['fluent_crm_status'] = isset( $_tfhb_integration_settings['fluent_crm']['status'] ) ? $_tfhb_integration_settings['fluent_crm']['status'] : 0;
		$integrations['zoho_crm_status'] = isset( $_tfhb_integration_settings['zoho_crm']['status'] ) ? $_tfhb_integration_settings['zoho_crm']['status'] : 0;
		$integrations['pabbly_status'] = isset( $_tfhb_integration_settings['pabbly']['status'] ) ? $_tfhb_integration_settings['pabbly']['status'] : 0;
		$integrations['zapier_status'] = isset( $_tfhb_integration_settings['zapier']['status'] ) ? $_tfhb_integration_settings['zapier']['status'] : 0;
		 

		// Meeting Category
		$terms = get_terms(
			array(
				'taxonomy'   => 'meeting_category',
				'hide_empty' => false, // Set to true to hide empty terms
			)
		);
		// Prepare the response data
		$term_array = array();
		foreach ( $terms as $term ) {
			$term_array[] = array(
				'name'  => $term->name,
				'value' => '' . $term->term_id . '',
			);
		}

		$_tfhb_host_integration_settings = ! empty( $MeetingData->host_id ) ? get_user_meta( $MeetingData->host_id, '_tfhb_host_integration_settings', true ) : '';
		$api_key                         = ! empty( $_tfhb_integration_settings['mailchimp']['key'] ) ? $_tfhb_integration_settings['mailchimp']['key'] : '';
		$api_key                         = ! empty( $_tfhb_host_integration_settings['mailchimp']['key'] ) ? $_tfhb_host_integration_settings['mailchimp']['key'] : $api_key;
		$mailchimp_Data                  = array();
		if ( $api_key != '' ) {
			$response = $this->set_config( $api_key, 'ping' );
			$response = json_decode( $response );
			if ( isset( $response->health_status ) ) { // Display success message
				$mailchimp_Data['status']   = true;
				$aud_lists                  = $this->get_audiance( $api_key );
				$mailchimp_Data['audience'] = $aud_lists;
			} else {
				$mailchimp_Data['status'] = false;
			}
		} else {
			$mailchimp_Data['status'] = false;
		}

		// FluentCRM
		$fluentcrm_Data = array();
		if(!empty($integrations['fluent_crm_status'])){
			if ( ! file_exists( WP_PLUGIN_DIR . '/' . 'fluent-crm/fluent-crm.php' ) ) {
				$fluentcrm_Data['status'] = false;
				$fluentcrm_Data['error_msg'] =  __( 'Install and activate the Fluent CRM plugin.', 'hydra-booking' );
			} elseif ( ! is_plugin_active( 'fluent-crm/fluent-crm.php' ) ) {
				$fluentcrm_Data['status'] = false;
				$fluentcrm_Data['error_msg'] =  __( 'Activate the Fluent CRM plugin.', 'hydra-booking' );
			}else{
				$fluentcrm_Data['status'] = true;
			}
		} else {
			$fluentcrm_Data['status'] = false;
		}
		if ( $fluentcrm_Data['status'] ) {
			global $wpdb;
			// Check if table exists
			$fluent_crm_tags    = $wpdb->prefix . 'fc_tags';
			$fluent_crm_lists   = $wpdb->prefix . 'fc_lists';
			$tags_table_exists  = $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}fc_tags'" ) == $fluent_crm_tags;
			$lists_table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}fc_lists'" ) == $fluent_crm_lists;

			if ( $tags_table_exists ) {
				// Table exists, retrieve data
				$results = $wpdb->get_results( "SELECT id, title FROM {$wpdb->prefix}fc_tags", ARRAY_A );

				// Check if results are not empty
				if ( ! empty( $results ) ) {
					// Output the results as an array
					$tags_array = array();
					foreach ( $results as $row ) {
						$tags_array[] = array(
							'name'  => $row['title'],
							'value' => $row['id'],
						);
					}
					$fluentcrm_Data['tags'] = $tags_array;
				}
			}

			if ( $lists_table_exists ) {
				// Table exists, retrieve data
				$results = $wpdb->get_results( "SELECT id, title FROM {$wpdb->prefix}fc_lists", ARRAY_A );

				// Check if results are not empty
				if ( ! empty( $results ) ) {
					// Output the results as an array
					$lists_array = array();
					foreach ( $results as $row ) {
						$lists_array[] = array(
							'name'  => $row['title'],
							'value' => $row['id'],
						);
					}
					$fluentcrm_Data['lists'] = $lists_array;
				}
			}
		}

		// Zoho
		$client_id     = ! empty( $_tfhb_host_integration_settings['zoho']['client_id'] ) ? $_tfhb_host_integration_settings['zoho']['client_id'] : '';
		$client_secret = ! empty( $_tfhb_host_integration_settings['zoho']['client_secret'] ) ? $_tfhb_host_integration_settings['zoho']['client_secret'] : '';
		$access_token  = ! empty( $_tfhb_host_integration_settings['zoho']['access_token'] ) ? $_tfhb_host_integration_settings['zoho']['access_token'] : '';
		$zoho_modules  = ! empty( $_tfhb_host_integration_settings['zoho']['modules'] ) ? json_decode( $_tfhb_host_integration_settings['zoho']['modules'] ) : '';

		$zohocrm_Data = array();
		if ( ! empty( $access_token ) ) {
			$zohocrm_Data['status']  = true;
			$zohocrm_Data['modules'] = $zoho_modules;
		} else {
			$zohocrm_Data['status'] = false;
		}

		// Telegram
		if(!empty($_tfhb_host_integration_settings['telegram']) && !empty($_tfhb_host_integration_settings['telegram']['status']) && ! empty( $_tfhb_host_integration_settings['telegram']['bot_token'] ) && ! empty( $_tfhb_host_integration_settings['telegram']['chat_id'] )){
			$telegram_status = ! empty( $_tfhb_host_integration_settings['telegram']['status'] ) ? $_tfhb_host_integration_settings['telegram']['status'] : '';
			$telegram_bot_token = ! empty( $_tfhb_host_integration_settings['telegram']['bot_token'] ) ? $_tfhb_host_integration_settings['telegram']['bot_token'] : '';
			$telegram_chat_id  = ! empty( $_tfhb_host_integration_settings['telegram']['chat_id'] ) ? $_tfhb_host_integration_settings['telegram']['chat_id'] : '';
		}else{
			$telegram_status = ! empty( $_tfhb_integration_settings['telegram']['status'] ) ? $_tfhb_integration_settings['telegram']['status'] : '';
			$telegram_bot_token = ! empty( $_tfhb_integration_settings['telegram']['bot_token'] ) ? $_tfhb_integration_settings['telegram']['bot_token'] : '';
			$telegram_chat_id  = ! empty( $_tfhb_integration_settings['telegram']['chat_id'] ) ? $_tfhb_integration_settings['telegram']['chat_id'] : '';
		}

		$telegram_Data = array();
		if ( ! empty( $telegram_status ) && ! empty( $telegram_bot_token ) && ! empty( $telegram_chat_id ) ) {
			$telegram_Data['status']  = true;
		} else {
			$telegram_Data['status'] = false;
		}

		// Slack
		if(!empty($_tfhb_host_integration_settings['slack']) && !empty($_tfhb_host_integration_settings['slack']['status'])){
			$slack_status = ! empty( $_tfhb_host_integration_settings['slack']['status'] ) ? $_tfhb_host_integration_settings['slack']['status'] : '';
			$slack_endpoint = ! empty( $_tfhb_host_integration_settings['slack']['endpoint'] ) ? $_tfhb_host_integration_settings['slack']['endpoint'] : '';
		}else{
			$slack_status = ! empty( $_tfhb_integration_settings['slack']['status'] ) ? $_tfhb_integration_settings['slack']['status'] : '';
			$slack_endpoint = ! empty( $_tfhb_integration_settings['slack']['endpoint'] ) ? $_tfhb_integration_settings['slack']['endpoint'] : '';
		}

		$slack_Data = array();
		if ( ! empty( $slack_status ) && ! empty( $slack_endpoint ) ) {
			$slack_Data['status']  = true;
		} else {
			$slack_Data['status'] = false;
		}

		// Twilio
		if(!empty($_tfhb_host_integration_settings['twilio']) && !empty($_tfhb_host_integration_settings['twilio']['status'])){
			$twilio_status = ! empty( $_tfhb_host_integration_settings['twilio']['status'] ) ? $_tfhb_host_integration_settings['twilio']['status'] : '';
			$twilio_receive_number = ! empty( $_tfhb_host_integration_settings['twilio']['receive_number'] ) ? $_tfhb_host_integration_settings['twilio']['receive_number'] : '';
			$twilio_from_number = ! empty( $_tfhb_host_integration_settings['twilio']['from_number'] ) ? $_tfhb_host_integration_settings['twilio']['from_number'] : '';
			$twilio_sid = ! empty( $_tfhb_host_integration_settings['twilio']['sid'] ) ? $_tfhb_host_integration_settings['twilio']['sid'] : '';
			$twilio_token = ! empty( $_tfhb_host_integration_settings['twilio']['token'] ) ? $_tfhb_host_integration_settings['twilio']['token'] : '';
		}else{
			$twilio_status = ! empty( $_tfhb_integration_settings['twilio']['status'] ) ? $_tfhb_integration_settings['twilio']['status'] : '';
			$twilio_receive_number = ! empty( $_tfhb_integration_settings['twilio']['receive_number'] ) ? $_tfhb_integration_settings['twilio']['receive_number'] : '';
			$twilio_from_number = ! empty( $_tfhb_integration_settings['twilio']['from_number'] ) ? $_tfhb_integration_settings['twilio']['from_number'] : '';
			$twilio_sid = ! empty( $_tfhb_integration_settings['twilio']['sid'] ) ? $_tfhb_integration_settings['twilio']['sid'] : '';
			$twilio_token = ! empty( $_tfhb_integration_settings['twilio']['token'] ) ? $_tfhb_integration_settings['twilio']['token'] : '';
		}

		$twilio_Data = array();
		if ( ! empty( $twilio_status ) && ! empty( $twilio_receive_number ) && ! empty( $twilio_from_number ) && ! empty( $twilio_sid ) && ! empty( $twilio_token ) ) {
			$twilio_Data['status']  = true;
		} else {
			$twilio_Data['status'] = false;
		}

		// Fetch Questions Data
		$questions_form_type = ! empty( $MeetingData->questions_form_type ) ? $MeetingData->questions_form_type : '';
		$questions_form      = ! empty( $MeetingData->questions_form ) ? $MeetingData->questions_form : 0;
		$formsList           = array();
		if ( $questions_form_type ) {
			$formsList = $this->getQuestionFormsData( $questions_form_type );
		}

		// add permalink into getMeetingData 
		$meetingData = (array) $MeetingData;
		$meetingData['permalink'] = get_permalink($MeetingData->post_id);
		// again array to object
		$MeetingData = (object) $meetingData;

		// Return response
		$data = array(
			'status'           => true,
			'meeting'          => $MeetingData,
			'time_zone'        => $time_zone,
			'wc_product'       => $wc_product,
			'meeting_category' => $term_array,
			'mailchimp'        => $mailchimp_Data,
			'fluentcrm'        => $fluentcrm_Data,
			'zohocrm'          => $zohocrm_Data,
			'formsList'        => $formsList,
			'integrations'     => $integrations,
			'telegram'     	   => $telegram_Data,
			'slack'            => $slack_Data,
			'twilio'           => $twilio_Data,
			'message'          =>  __( 'Meeting Data','hydra-booking' ),
		);
		return rest_ensure_response( $data );
	}

	// Update Meeting Information
	public function updateMeeting() {
		$request = json_decode( file_get_contents( 'php://input' ), true );
		// Check if user is selected
		$meeting_id = $request['id'];
		$user_id    = $request['user_id'];
		if ( empty( $meeting_id ) || $meeting_id == 0 ) {
			return rest_ensure_response(
				array(
					'status'  => false,
					'message' =>  __( 'Invalid Meeting', 'hydra-booking' ),
				)
			);
		}
		// Get Meeting
		$meeting     = new Meeting();
		$MeetingData = $meeting->get( $meeting_id );

		if ( empty( $MeetingData ) ) {
			return rest_ensure_response(
				array(
					'status'  => false,
					'message' => __( 'Invalid Meeting', 'hydra-booking' ),
				)
			);
		}

		// Get Current User
		$current_user = wp_get_current_user();
		// get user id
		$current_user_id = $current_user->ID; 
		
		// Custom Availability
		if (isset($request['availability_custom'])) {
			$availability_custom = $request['availability_custom'];
			// Remove empty date_slots
			if (isset($availability_custom['date_slots'])) {
				$availability_custom['date_slots'] = array_filter($availability_custom['date_slots'], function ($slot) {
					return !empty($slot['date']); // Keep only if 'date' is not empty
				});
				// Re-index array to maintain sequential keys (optional)
				$availability_custom['date_slots'] = array_values($availability_custom['date_slots']);
			}
		}else{
			$availability_custom = '';
		}

		// Update Meeting
		$data = array(
			'id'                       => $request['id'],
			'user_id'                  => $request['user_id'],
			'title'                    => isset( $request['title'] ) ? sanitize_text_field( $request['title'] ) : '',
			'host_id'                  => isset( $request['host_id'] ) ? sanitize_key( $request['host_id'] ) : '',
			'description'              => isset( $request['description'] ) ? sanitize_text_field( $request['description'] ) : '',
			'meeting_type'             => isset( $request['meeting_type'] ) ? sanitize_text_field( $request['meeting_type'] ) : '',
			'duration'                 => isset( $request['duration'] ) ? sanitize_text_field( $request['duration'] ) : '',
			'custom_duration'          => isset( $request['custom_duration'] ) ? sanitize_text_field( $request['custom_duration'] ) : '',
			'meeting_locations'        => isset( $request['meeting_locations'] ) ? $request['meeting_locations'] : '',
			'meeting_category'         => isset( $request['meeting_category'] ) ? sanitize_text_field( $request['meeting_category'] ) : '',
			'availability_type'        => isset( $request['availability_type'] ) ? sanitize_text_field( $request['availability_type'] ) : '',
			'availability_range_type'  => isset( $request['availability_range_type'] ) ? sanitize_text_field( $request['availability_range_type'] ) : '',
			'availability_range'       => isset( $request['availability_range'] ) ? $request['availability_range'] : '',
			'availability_id'          => isset( $request['availability_id'] ) ? sanitize_text_field( $request['availability_id'] ) : '',
			'availability_custom'      => $availability_custom,
			'buffer_time_before'       => isset( $request['buffer_time_before'] ) ? sanitize_text_field( $request['buffer_time_before'] ) : '',
			'buffer_time_after'        => isset( $request['buffer_time_after'] ) ? sanitize_text_field( $request['buffer_time_after'] ) : '',
			'booking_frequency'        => isset( $request['booking_frequency'] ) ? $request['booking_frequency'] : '',
			'meeting_interval'         => isset( $request['meeting_interval'] ) ? sanitize_text_field( $request['meeting_interval'] ) : '',
			'recurring_status'         => isset( $request['recurring_status'] ) ? sanitize_text_field( $request['recurring_status'] ) : '',
			'recurring_repeat'         => isset( $request['recurring_repeat'] ) ? $request['recurring_repeat'] : '',
			'recurring_maximum'        => isset( $request['recurring_maximum'] ) ? sanitize_text_field( $request['recurring_maximum'] ) : '',
			'attendee_can_cancel'      => isset( $request['attendee_can_cancel'] ) ? sanitize_text_field( $request['attendee_can_cancel'] ) : '',
			'attendee_can_reschedule'  => isset( $request['attendee_can_reschedule'] ) ? sanitize_text_field( $request['attendee_can_reschedule'] ) : '',
			'questions_type'           => isset( $request['questions_type'] ) ? sanitize_text_field( $request['questions_type'] ) : '',
			'questions_form_type'      => isset( $request['questions_form_type'] ) ? sanitize_text_field( $request['questions_form_type'] ) : '',
			'questions_form'           => isset( $request['questions_form'] ) ? sanitize_text_field( $request['questions_form'] ) : '',
			'questions'                => isset( $request['questions'] ) ? $request['questions'] : '',
			'notification'             => isset( $request['notification'] ) ? $request['notification'] : '',
			'payment_status'           => isset( $request['payment_status'] ) ? sanitize_text_field( $request['payment_status'] ) : '',
			'meeting_price'            => isset( $request['meeting_price'] ) ? sanitize_text_field( $request['meeting_price'] ) : '',
			'payment_currency'         => isset( $request['payment_currency'] ) ? sanitize_text_field( $request['payment_currency'] ) : '',
			'payment_method'           => isset( $request['payment_method'] ) ? sanitize_text_field( $request['payment_method'] ) : '',
			'max_book_per_slot'        => isset( $request['max_book_per_slot'] ) ? sanitize_text_field( $request['max_book_per_slot'] ) : '',
			'is_display_max_book_slot' => isset( $request['is_display_max_book_slot'] ) ? sanitize_text_field( $request['is_display_max_book_slot'] ) : '',
			'payment_meta'             => isset( $request['payment_meta'] ) ? $request['payment_meta'] : '',
			'updated_at'               => gmdate( 'Y-m-d' ),
			'updated_by'               => $current_user_id,
		);
		$host = new Host ();
		$host_data = $host->getHostById( $data['host_id'] );
		if($host_data){ 
			$data['user_id'] = $host_data->user_id;
		} 

		// this is a temporay fix it will be removed in version 2.0.0 or higher version
		foreach($data['questions'] as $key => $question){

			if(!isset($question['name']) || empty($question['name'])){
				$baseName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $question['label']));

				 
                $count = count( array_filter( array_map( function($item) use ($baseName) { return $item['name'] == $baseName; }, $data['questions'] ) ) );
                if ( $count > 0 ) {
                    $uniqueName = $baseName. '_'. substr( md5( mt_rand() ), 0, 2 );
                } else {
                    $uniqueName = $baseName;
                } 
                $data['questions'][$key]['name'] = $uniqueName; 
			}
			if(!isset($question['enable']) ) {
				$data['questions'][$key]['enable'] = 1;
			}

		}
		// ******** end of fix

		// if Payment Methood is woo_payment
		if ( 'woo_payment' == $data['payment_method'] &&  class_exists( 'WooCommerce' ) ) {
			$products              = wc_get_product( $data['payment_meta']['product_id'] ); 
			$data['meeting_price'] = $products->price;
			$data['payment_currency'] = get_woocommerce_currency();

		}

		// Meeting Update into
		$meeting_post_data = array(
			'ID'           => $MeetingData->post_id,
			'post_title'   => isset( $request['title'] ) ? sanitize_text_field( $request['title'] ) : '',
			'post_content' => isset( $request['description'] ) ? sanitize_text_field( $request['description'] ) : '',
			'post_author'  => $current_user_id,
			'post_name'    => isset( $request['title'] ) ? sanitize_title( $request['title'] ) : '',
		);
		wp_update_post( $meeting_post_data );

		$data['slug'] = get_post_field( 'post_name', $MeetingData->post_id );

		$meetingUpdate = $meeting->update( $data );
		if ( ! $meetingUpdate['status'] ) {
			return rest_ensure_response(
				array(
					'status'  => false,
					'message' =>  __( 'Error while updating meeting', 'hydra-booking' ),
				)
			);
		}

		// Updated Meeting post meta
		if ( $MeetingData->post_id ) {

			// Updated post meta
			update_post_meta( $MeetingData->post_id, '__tfhb_meeting_opt', $data );

		}
		$GetMeeting =  (array) $meeting->get( $meeting_id ); 

		
		$GetMeeting['permalink'] = get_permalink($GetMeeting['post_id']); 
		// Return response
		$data = array(
			'status'  => true,
			'message' =>  __( 'Meeting Updated Successfully', 'hydra-booking' ),
			'data'    => $data,
			'meeting' => $GetMeeting,
		);
		return rest_ensure_response( $data );
	}

	/*
	 *  Clone Meeting
	 */
	public function cloneMeeting( ){
		$request = json_decode( file_get_contents( 'php://input' ), true );

		$current_user = wp_get_current_user();
		// get user id
		$current_user_id = $current_user->ID;

		$get_meeting_id = $request['id'];
		$meeting = new Meeting();
		$meeting_data = (array) $meeting->getWithID( $get_meeting_id );
		unset($meeting_data['id']);
		$meeting_data['created_by'] = $current_user_id;
		$meeting_data['updated_by'] = $current_user_id;
		$meeting_data['created_at'] = gmdate( 'Y-m-d' );
		$meeting_data['updated_at'] = gmdate( 'Y-m-d' );


		// Create an array to store the post data for meeting the current row
		$meeting_post_data = array(
			'post_type'   => 'tfhb_meeting',
			'post_title'  => $meeting_data['title'] . esc_html( '( Clone )' ),
			'post_status' => 'publish',
			'post_author' => $current_user_id,
		);
		$meeting_post_id   = wp_insert_post( $meeting_post_data ); 

		$meeting_data['post_id'] = $meeting_post_id;
		$meeting_data['slug'] = get_post_field( 'post_name', $meeting_post_id );
		$meeting_data['title'] =  $meeting_data['title'] . esc_html( '( Clone )' );
	
		$meetingInsert = $meeting->add( $meeting_data ); 
		$meetings_id = $meetingInsert['insert_id'];
			// Meetings Id into Post Meta
		update_post_meta( $meeting_post_id, '__tfhb_meeting_id', $meetings_id );
 
		// Updated post meta
		$meeting_data['id'] = $meetings_id;

		// Set the meeting data
		$meeting_data['meeting_locations'] = !is_array($meeting_data['meeting_locations']) ? json_decode($meeting_data['meeting_locations'], true) : $meeting_data['meeting_locations'];
		$meeting_data['availability_range'] =  !is_array($meeting_data['availability_range']) ? json_decode($meeting_data['availability_range'] , true) : $meeting_data['availability_range'];
		$meeting_data['availability_custom'] =  !is_array($meeting_data['availability_custom']) ? json_decode($meeting_data['availability_custom'], true) : $meeting_data['availability_custom'];
		$meeting_data['booking_frequency'] = !is_array($meeting_data['booking_frequency']) ? json_decode($meeting_data['booking_frequency'], true) : $meeting_data['booking_frequency'];
		$meeting_data['recurring_repeat'] = !is_array($meeting_data['recurring_repeat']) ? json_decode($meeting_data['recurring_repeat'], true) : $meeting_data['recurring_repeat'];
		$meeting_data['questions'] = !is_array($meeting_data['questions']) ? json_decode($meeting_data['questions'], true) : $meeting_data['questions'];
		$meeting_data['notification'] =  !is_array($meeting_data['notification']) ? json_decode($meeting_data['notification'], true) : $meeting_data['notification'];
		$meeting_data['payment_meta'] =  !is_array($meeting_data['payment_meta']) ? json_decode($meeting_data['payment_meta'], true) : $meeting_data['payment_meta'];

		update_post_meta( $meeting_post_id, '__tfhb_meeting_opt', $meeting_data );

		$MeetingsList = $this->getMeetingList(); 

		// Return response
		$data = array(
			'status'   => true,
			'meetings' => $MeetingsList,
			'message'  =>  __( 'Meeting Cloned Successfully', 'hydra-booking' ),
		);

		return rest_ensure_response( $data );
	} 
	// Host availability
	public function getTheHostAvailabilityData( $request ) {

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

		if ( 'settings' == $HostData->availability_type ) {
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
				$HostData->availability = reset( $filteredAvailability );
			} else {
				$HostData->availability = '';
			}
		} else {
			
			$_tfhb_host_availability_settings = get_user_meta( $HostData->user_id, '_tfhb_host', true );
			
			if ( ! empty( $_tfhb_host_availability_settings['availability'] ) ) {
				$HostData->availability = $_tfhb_host_availability_settings['availability'];

			}
			
			if ( empty( $HostData ) ) {
				return rest_ensure_response(
					array(
						'status'  => false,
						'message' =>  __( 'Invalid Host', 'hydra-booking' ),
					)
				);
			}
		}

		$DateTimeZone = new DateTimeController( 'UTC' );
		$time_zone    = $DateTimeZone->TimeZone();
	
		// Return response
		$data = array(
			'status'        => true,
			'host'          => $HostData,
			'host_availble' => $HostData->availability_type,
			'time_zone'     => $time_zone,
			'message'       =>  __( 'Host Availability Data', 'hydra-booking' ),
		);
		return rest_ensure_response( $data );
	}

	/* Mailchimp audiance List */
	private function get_audiance( $api_key ) {

		$response = $this->set_config( $api_key, 'lists' );
		$audience = array();
		$response = json_decode( $response, true );
		$x        = 0;
		if ( isset( $response['lists'] ) && $response != null ) {
			foreach ( $response['lists'] as $list ) {
				$audience[] = array(
					'name'  => $list['name'],
					'value' => '' . $list['id'] . '',
				);

				++$x;
			}
		}
		return $audience;
	}

	/* Mailchimp config set */
	private function set_config( $api_key = '', $path = '' ) {

		$server_prefix = explode( '-', $api_key );

		if ( ! isset( $server_prefix[1] ) ) {
			return;
		}
		$server_prefix = $server_prefix[1];

		$url = "https://$server_prefix.api.mailchimp.com/3.0/$path";

		$curl = curl_init( $url );
		curl_setopt( $curl, CURLOPT_URL, $url );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );

		$headers = array(
			"Authorization: Bearer $api_key",
		);
		curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers );
		// for debug only!
		curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, false );
		curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );

		$resp = curl_exec( $curl );
		curl_close( $curl );

		return $resp;
	}

	/* Modules Fileds */
	public function getIntegrationModulsFields( $request ) {
		$host      = ! empty( $request['host_id'] ) ? $request['host_id'] : '';
		$hook_type = ! empty( $request['webhook'] ) ? $request['webhook'] : '';

		$_tfhb_host_integration_settings = is_array( get_user_meta( $host, '_tfhb_host_integration_settings', true ) ) ? get_user_meta( $host, '_tfhb_host_integration_settings', true ) : array();

		if ( 'ZohoCRM' == $hook_type ) {
			$access_token = ! empty( $_tfhb_host_integration_settings['zoho']['access_token'] ) ? $_tfhb_host_integration_settings['zoho']['access_token'] : '';
			$access_token = $this->refreshToken( $host );
			// The Zoho CRM API URL to get all modules
			$api_url = 'https://www.zohoapis.com/crm/v6/settings/fields?module=' . $request['module'];

			// Initialize cURL session
			$ch = curl_init();
			// Set the URL and other necessary options
			curl_setopt( $ch, CURLOPT_URL, $api_url );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

			// Set the headers, including the authorization token
			$headers = array(
				'Authorization: Zoho-oauthtoken ' . $access_token,
				'Content-Type: application/json',
			);
			curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );

			// Execute the cURL session and fetch the response
			$response = curl_exec( $ch );

			// Check for cURL errors
			if ( curl_errno( $ch ) ) {
				echo 'Error:' . esc_attr(curl_error( $ch ));
			}
			// Close the cURL session
			curl_close( $ch );

			// Decode the JSON response
			$response_data = json_decode( $response, true );

			$fields        = array();
			$response_data = json_decode( $response, true );
			if ( isset( $response_data['fields'] ) ) {
				// Loop through each field and print its name
				foreach ( $response_data['fields'] as $field ) {
					$fields[] = array(
						'name'  => $field['field_label'],
						'value' => $field['api_name'],
					);
				}
			}
		} elseif ( 'Mailchimp' == $hook_type ) {
			$_tfhb_integration_settings = get_option( '_tfhb_integration_settings' );
			$api_key                    = ! empty( $_tfhb_integration_settings['mailchimp']['key'] ) ? $_tfhb_integration_settings['mailchimp']['key'] : '';
			$api_key                    = ! empty( $_tfhb_host_integration_settings['mailchimp']['key'] ) ? $_tfhb_host_integration_settings['mailchimp']['key'] : $api_key;

			$mail_fields = $this->get_mailchimp_fields( $api_key, $request['module'] );
			if ( ! empty( $mail_fields ) ) {
				$fields      = array(
					array(
						'name'  => 'Email Address',
						'value' => 'EMAIL',
					),
				);
				$mail_fields = json_decode( $mail_fields );
				if ( ! empty( $mail_fields->merge_fields ) ) {
					foreach ( $mail_fields->merge_fields as $field ) {
						$fields[] = array(
							'name'  => $field->name,
							'value' => $field->tag,
						);
					}
				}
			}
		} else {
			$fields = array(
				array(
					'name'  => 'First Name',
					'value' => 'first_name',
				),
				array(
					'name'  => 'Last Name',
					'value' => 'last_name',
				),
				array(
					'name'  => 'Email',
					'value' => 'email',
				),
				array(
					'name'  => 'Phone',
					'value' => 'phone',
				),
				array(
					'name'  => 'Timezone',
					'value' => 'timezone',
				),
				array(
					'name'  => 'Address',
					'value' => 'address_line_1',
				),
				array(
					'name'  => 'Postal Code',
					'value' => 'postal_code',
				),
				array(
					'name'  => 'City',
					'value' => 'city',
				),
				array(
					'name'  => 'Country',
					'value' => 'country',
				),
			);
		}

		// Return response
		$data = array(
			'status'  => true,
			'fields'  => $fields,
			'message' => 'Fields Data',
		);
		return rest_ensure_response( $data );
	}


	/* Mailchimp Fields */
	private function get_mailchimp_fields( $api_key = '', $module = '' ) {

		$server_prefix = explode( '-', $api_key );

		if ( ! isset( $server_prefix[1] ) ) {
			return;
		}
		$server_prefix = $server_prefix[1];

		$url  = "https://$server_prefix.api.mailchimp.com/3.0/lists/$module/merge-fields";
		$curl = curl_init( $url );
		curl_setopt( $curl, CURLOPT_URL, $url );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );

		$headers = array(
			"Authorization: Bearer $api_key",
		);
		curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers );
		// for debug only!
		curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, false );
		curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );

		$resp = curl_exec( $curl );
		curl_close( $curl );

		return $resp;
	}

	// Refresh Token
	public function refreshToken( $host ) {
		$_tfhb_host_integration_settings = is_array( get_user_meta( $host, '_tfhb_host_integration_settings', true ) ) ? get_user_meta( $host, '_tfhb_host_integration_settings', true ) : array();

		$client_id     = ! empty( $_tfhb_host_integration_settings['zoho']['client_id'] ) ? $_tfhb_host_integration_settings['zoho']['client_id'] : '';
		$client_secret = ! empty( $_tfhb_host_integration_settings['zoho']['client_secret'] ) ? $_tfhb_host_integration_settings['zoho']['client_secret'] : '';
		$access_token  = ! empty( $_tfhb_host_integration_settings['zoho']['access_token'] ) ? $_tfhb_host_integration_settings['zoho']['access_token'] : '';
		$refresh_token = ! empty( $_tfhb_host_integration_settings['zoho']['refresh_token'] ) ? $_tfhb_host_integration_settings['zoho']['refresh_token'] : '';

		$url  = 'https://accounts.zoho.com/oauth/v2/token';
		$data = array(
			'grant_type'    => 'refresh_token',
			'client_id'     => $client_id,
			'client_secret' => $client_secret,
			'refresh_token' => $refresh_token,
		);

		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_POST, true );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $data ) );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-Type: application/x-www-form-urlencoded' ) );

		$response = curl_exec( $ch );
		if ( curl_errno( $ch ) ) {
			echo 'Error:' . esc_attr(curl_error( $ch ));
		}
		curl_close( $ch );

		$response_data = json_decode( $response, true );

		if ( ! empty( $response_data['access_token'] ) ) {
			$_tfhb_host_integration_settings['zoho']['access_token'] = $response_data['access_token'];

			// save to user metadata
			update_user_meta( $host, '_tfhb_host_integration_settings', $_tfhb_host_integration_settings );

			return $response_data['access_token'];
		}
	}

	// Meeting Questions Forms List
	public function getQuestionFormsList() {
		$request       = json_decode( file_get_contents( 'php://input' ), true );
		$form_type     = $request['form_type'];
		$questionForms = $this->getQuestionFormsData( $form_type );

		$data = array(
			'status'        => true,
			'questionForms' => $questionForms,
			'message'       => 'Question Forms Data',
		);
		return rest_ensure_response( $data );
	}

	// Fetch Meeting integrations Settings
	public function fetchMeetingsPaymentIntegration(){
		// Gett intrigations settings
		$_tfhb_integration_settings = get_option( '_tfhb_integration_settings' );
		$integrations = array();
		$integrations['woo_payment'] = isset( $_tfhb_integration_settings['woo_payment']['status'] ) && $_tfhb_integration_settings['woo_payment']['status'] == true ? false : true;
		$integrations['paypal'] = isset( $_tfhb_integration_settings['paypal']['status'] ) && !empty($_tfhb_integration_settings['paypal']['client_id'] ) && $_tfhb_integration_settings['paypal']['status'] == true ? false : true;
		$integrations['stripe'] = isset( $_tfhb_integration_settings['stripe']['status'] ) && !empty($_tfhb_integration_settings['stripe']['public_key'] ) && $_tfhb_integration_settings['stripe']['status'] == true ? false : true;
		
		$country = new CountryController();
		$currency_list = $country->currency_list();

		$data = array(
			'status'        => true,
			'integrations' => $integrations, 
			'_tfhb_integration_settings' => $_tfhb_integration_settings, 
			'currency_list' => $currency_list, 
		);
		return rest_ensure_response( $data );


	}
	// Fetch Forms list based on form Types
	public function getQuestionFormsData( $form_type ) {
		$questionForms = array();
		if ( $form_type == 'wpcf7' ) {
			$args  = array(
				'post_type'      => 'wpcf7_contact_form',
				'posts_per_page' => -1,
			);
			$forms = get_posts( $args );

			foreach ( $forms as $form ) {
				$questionForms[] = array(
					'name'  => $form->post_title,
					'value' => $form->ID,
				);
			}
		} elseif ( $form_type == 'forminator-forms' ) {
			$args  = array(
				'post_type'      => 'forminator_forms',
				'posts_per_page' => -1,
			);
			$forms = get_posts( $args );

			foreach ( $forms as $form ) {
				$questionForms[] = array(
					'name'  => $form->post_title,
					'value' => $form->ID,
				);
			}
		} elseif ( $form_type == 'fluent-forms' ) {
			// Query arguments get custom fluentform_forms data all into custom database table
			global $wpdb;
			$results    = $wpdb->get_results( "SELECT id, title FROM {$wpdb->prefix}fluentform_forms" );
			foreach ( $results as $form ) {
				$questionForms[] = array(
					'name'  => $form->title,
					'value' => $form->id,
				);
			}
		} elseif ( $form_type == 'gravityforms' ) {
			// Query arguments get custom fluentform_forms data all into custom database table
			global $wpdb;
			$results    = $wpdb->get_results( "SELECT id, title FROM {$wpdb->prefix}gf_form" );
			foreach ( $results as $form ) {
				$questionForms[] = array(
					'name'  => $form->title,
					'value' => $form->id,
				);
			}
		}
		return $questionForms;
	}
}
