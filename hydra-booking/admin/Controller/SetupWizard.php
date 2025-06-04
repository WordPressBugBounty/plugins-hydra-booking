<?php
namespace HydraBooking\Admin\Controller;
// exit
if ( ! defined( 'ABSPATH' ) ) { exit; }

// Use Namespace

// Use DB
use HydraBooking\admin\Controller\RouteController;
use HydraBooking\DB\Host;
use HydraBooking\DB\Meeting;
use HydraBooking\Admin\Controller\Helper;
use HydraBooking\Admin\Controller\DateTimeController;
use HydraBooking\DB\Availability;
// exit
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

class SetupWizard {


	// constaract
	public function __construct() {
 
	}


	public function create_endpoint() {
		register_rest_route(
			'hydra-booking/v1',
			'/setup-wizard/fetch',
			array(
				'methods'  => 'GET',
				'callback' => array( $this, 'fetchSetupWizard' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
			)
		);

		register_rest_route(
			'hydra-booking/v1',
			'/setup-wizard/import-meeting',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'ImportMeetingDemo' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
			)
		);
	}

	// Fatch Setup Wizard
	public function fetchSetupWizard() {

		$DateTimeZone = new DateTimeController( 'UTC' );
		$time_zone    = $DateTimeZone->TimeZone();
		// Get Current User email FORM wp_get_current_user
		$current_user       = wp_get_current_user();
		$current_user_email = $current_user->data->user_email;

		$data = array(
			'status'     => true,
			'time_zone'  => $time_zone,
			'user_email' => $current_user_email,
		);
		return rest_ensure_response( $data );
	}


	// Import Meeting Demo
	public function ImportMeetingDemo() {
		$request = json_decode( file_get_contents( 'php://input' ), true );


		// collect email form request
		$email_subscribe                         = array();
		$email_subscribe['email']                = $request['email'];
		$email_subscribe['subscribe_status']     = $request['enable_recevie_updates'];
		$email_subscribe['subscribe_date']       = gmdate( 'Y-m-d' );
		$email_subscribe['subscribe_time']       = gmdate( 'H:i:s' );
		$email_subscribe['subscribe_ip']         = $_SERVER['REMOTE_ADDR'];
		$email_subscribe['subscribe_user_agent'] = $_SERVER['HTTP_USER_AGENT'];
		$email_subscribe['subscribe_referer']    = $_SERVER['HTTP_REFERER'];
		// get subscriber device  from user agent

		// Update Email Subscribe Option
		update_option( 'tfhb_hydra_email_subscribe', $email_subscribe );

		// Update General Settings available
		$availability_settings =  !empty( get_option( '_tfhb_availability_settings' ) ) && get_option( '_tfhb_availability_settings' ) != 'false' ? get_option( '_tfhb_availability_settings' ) : array();
		// get last array data
		$last_data                    = ! empty( $availability_settings ) ? end( $availability_settings ) : array();
		$new_id                       =  isset($last_data['id']) ? $last_data['id'] + 1 : 1;
		$availabilityDataSingle       = $request['availabilityDataSingle'];


		$availabilityDataSingle['title'] =  $availabilityDataSingle['title'] != '' ? $availabilityDataSingle['title'] : 'No Title';
		$availabilityDataSingle['time_zone'] =   $availabilityDataSingle['time_zone'] != '' ? $availabilityDataSingle['time_zone'] : 'UTC';
		$Availability = new Availability();
		
		// get all availability data
		$getAvailability = $Availability->get(
			array(
				'default_status' => true,
			)
		);
		if(count($getAvailability) > 0){
			$availabilityDataSingle['default_status'] = 0;
		}  
		foreach ( $availabilityDataSingle['time_slots'] as $key => $value ) {

			$availabilityDataSingle['time_slots'][ $key ]['day']    = sanitize_text_field( $value['day'] );
			$availabilityDataSingle['time_slots'][ $key ]['status'] = sanitize_text_field( $value['status'] );

			foreach ( $value['times'] as $key2 => $value2 ) {
				$availabilityDataSingle['time_slots'][ $key ]['times'][ $key2 ]['start'] = sanitize_text_field( $value2['start'] );
				$availabilityDataSingle['time_slots'][ $key ]['times'][ $key2 ]['end']   = sanitize_text_field( $value2['end'] );
			}
		}
		// Date Slots
		foreach ( $availabilityDataSingle['date_slots'] as $key => $value ) {

			$availabilityDataSingle['date_slots'][ $key ]['date']      = sanitize_text_field( $value['date'] );
			$availabilityDataSingle['date_slots'][ $key ]['available'] = sanitize_text_field( $value['available'] );

			foreach ( $value['times'] as $key2 => $value2 ) {
				$availabilityDataSingle['date_slots'][ $key ]['times'][ $key2 ]['start'] = sanitize_text_field( $value2['start'] );
				$availabilityDataSingle['date_slots'][ $key ]['times'][ $key2 ]['end']   = sanitize_text_field( $value2['end'] );
			}
		}
		$availabilityDataSingle['id'] = $new_id;
		$availability_settings[]      = $availabilityDataSingle;

		// Insert into database

		$insert_availability = $Availability->add( $availabilityDataSingle );
		if ( $insert_availability['status'] === true ) {
			$availabilityDataSingle['id'] = $insert_availability['insert_id'];
		}
		update_option( '_tfhb_availability_settings', $availability_settings, true );
		
		// GET Current User
		$current_user = wp_get_current_user();
		$host         = $this->CreateHost( $current_user, $insert_availability['insert_id']);

		$request['host_id'] = $host->id;
		$request['user_id'] = $host->user_id;
		

		// Checked if Host Already Exist 
		if($request['skip_import'] == false){

			$meeting = $this->CreateDemoMeetings( $request );
		}else{
			$meeting = array();
		} 
		$data = array(
			'status'                => true,
			'message'               => __( 'General Settings Updated Successfully', 'hydra-booking' ),
			'meeting'               => $meeting,
			'email_subscribe'       => $email_subscribe,
			'availability_settings' => $availability_settings,
		);
		return rest_ensure_response( $data );
	}

	// Create New Host
	public function CreateHost( $user, $defult_availability_id ) {
		$user_id   = $user->ID;
		$host      = new Host();
		$host_data = $host->getHostByUserId( $user_id );

		if ( $host_data == null ) {

			$data = array(
				'user_id'        => $user->ID,
				'first_name'     => get_user_meta( $user->ID, 'first_name', true ) != '' ? get_user_meta( $user->ID, 'first_name', true ) : $user->display_name,
				'last_name'      => get_user_meta( $user->ID, 'last_name', true ) != '' ? get_user_meta( $user->ID, 'last_name', true ) : '',
				'email'          => $user->user_email,
				'phone_number'   => '',
				'time_zone'      => '',
				'about'          => '',
				'avatar'         => '',
				'availability_type'      => 'settings',
				'availability_id'      => $defult_availability_id,
				'featured_image' => '', 
				'status'         => 'activate',
			);

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
			$data['availability'] = array();

			// Update user Option
			update_user_meta( $user_id, '_tfhb_host', $data );

			// Hosts Lists
			$host_data = $host->get( $user_id );

		}

		return $host_data;
	}


	// Create Demo Meetings
	public function CreateDemoMeetings( $request ) {

		$business_type = isset( $request['business_type'] ) && !empty($request['business_type']) ? $request['business_type'] : 'Demo Meeting';
		// Create an array to store the post data for meeting the current row
		$meeting_category = $this->create_meeting_category( esc_html($business_type), '' );
		$meeting_post_data = array(
			'post_type'   => 'tfhb_meeting',
			'post_title'  => esc_html( $business_type ),
			'post_status' => 'publish',
			'post_author' => $request['user_id'],
		);
		
	
		// Create Meeting Category as per Business Type
		
		$meeting_post_id   = wp_insert_post( $meeting_post_data );
		$meeting_slug      = get_post_field( 'post_name', $meeting_post_id );
		$data              = array(
			'slug'                     => esc_html( $meeting_slug ),
			'host_id'                  => $request['host_id'],
			'user_id'                  => $request['user_id'],
			'post_id'                  => $meeting_post_id,
			'title'                    => esc_html( $business_type ),
			'description'              => '',
			'meeting_type'             => 'one-to-one',
			'duration'                 => '30',
			'meeting_locations'        => '[{"location":"Attendee Phone Number","address":""}]',
			'meeting_category'        =>  esc_html($meeting_category),
			'availability_range_type'  => 'indefinitely',
			'availability_type'        => 'custom',
			'availability_id'          => '0',
			'availability_custom'      => isset( $request['availabilityDataSingle'] ) ? wp_json_encode( $request['availabilityDataSingle'] ) : '',
			'booking_frequency'        => '[{"limit":5,"times":"days"}]',
			'recurring_status'         => '0',
			'recurring_repeat'         => '[{"limit":1,"times":"days"}]',
			'questions_type'           => 'custom',
			'questions'                => '[{"label":"Name","type":"Text","name":"name","placeholder":"Name","options":[],"required":1,"enable":1},{"label":"Email","type":"Email","name":"email","options":[],"placeholder":"Email","required":1,"enable":1},{"label":"Comment","type":"textarea","name":"comment","placeholder":"Comment","options":[],"required":0,"enable":1}]',
			'payment_status'           => 0,
			'payment_method'           => '',
			'max_book_per_slot'        => 1,
			'is_display_max_book_slot' => '0',
			'created_by'               => $request['user_id'],
			'updated_by'               => $request['user_id'],
			'created_at'               => gmdate( 'Y-m-d' ),
			'updated_at'               => gmdate( 'Y-m-d' ),
			'status'                   => 'publish',
		);

		$_tfhb_notification_settings = get_option( '_tfhb_notification_settings' );

		if(empty($_tfhb_notification_settings)){
			$default_notification =  new Helper();
			$_tfhb_notification_settings = $default_notification->get_default_notification_template(); 
		}
		$data['notification']   = $_tfhb_notification_settings;

		// Check if user is already a meeting
		$meeting = new Meeting();
		// Insert meeting
		$meetingInsert = $meeting->add( $data );
		if ( ! $meetingInsert['status'] ) {
			return rest_ensure_response(
				array(
					'status'  => false,
					'message' => __( 'Error while creating meeting', 'hydra-booking' ),
				)
			);
		}
		$meetings_id = $meetingInsert['insert_id'];

		// Meetings Id into Post Meta
		update_post_meta( $meeting_post_id, '__tfhb_meeting_id', $meetings_id );

		$data['id']                  = $meetings_id;
		$data['meeting_locations']   = json_decode( $data['meeting_locations'], true );
		$data['availability_custom'] = json_decode( $data['availability_custom'], true );
		$data['questions']           = json_decode( $data['questions'], true );
		// Updated post meta
		update_post_meta( $meeting_post_id, '__tfhb_meeting_opt', $data );

		// meetings Lists
		$meeting = $meeting->get( $meetings_id );

		// Get Meeting Permalink 
		$meeting->permalink = get_permalink( $meeting_post_id );
		
		return $meeting;
	}

	/**
	 * Create Meeting category
	 *
	 * @return object
	 */
	public function create_meeting_category($title, $description = '') {

		// if the term doesn't exist, then create it
		if ( term_exists( $title, 'meeting_category' ) ) {
			return;
		}
		// Insert the term
		$term = wp_insert_term(
			$title,   // The term
			'meeting_category', // The taxonomy
			array(
				'description' => $description,
				'slug'        => sanitize_title( $title ),
			)
		);
		// term id
		return  $term['term_id'];

	}
}
