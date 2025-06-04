<?php
namespace HydraBooking\Services\Integrations\Zoom;

use HydraBooking\DB\Booking;
use HydraBooking\DB\BookingMeta;
use HydraBooking\DB\Host;
use HydraBooking\DB\Meeting;

class ZoomServices {

	public $account_id;
	protected $client_id;

	protected $client_secret;


	protected $access_token;

	public $revokeUrl = 'https://zoom.us/oauth/revoke';

	public $tokenUrl = 'https://zoom.us/oauth/token';



	public function __construct( ) {
 
		$this->setApiDetails();
	}

	public function setApiDetails(  ) {
		$_tfhb_integration_settings = get_option( '_tfhb_integration_settings' );
		if ( ! empty( $_tfhb_integration_settings['zoom_meeting'] ) && ! empty( $_tfhb_integration_settings['zoom_meeting']['connection_status'] ) && $_tfhb_integration_settings['zoom_meeting']['status'] == true ) {
			$this->account_id     = $_tfhb_integration_settings['zoom_meeting']['account_id'];
			$this->client_id  = $_tfhb_integration_settings['zoom_meeting']['app_client_id'];
			$this->client_secret = $_tfhb_integration_settings['zoom_meeting']['app_secret_key'];
		}
	}

	// setHostApiDetails
	public function setHostApiDetails( $host_id ) {
		$host = new Host();
		$host_meta = $host->get( $host_id );
		$_tfhb_host_integration_settings = get_user_meta( $host_meta->user_id, '_tfhb_host_integration_settings', true );
		
		if ( ! empty( $_tfhb_host_integration_settings['zoom_meeting'] ) && ! empty( $_tfhb_host_integration_settings['zoom_meeting']['connection_status'] ) && ! empty( $_tfhb_host_integration_settings['zoom_meeting']['status'] ) && $_tfhb_host_integration_settings['zoom_meeting']['status'] == true ) {
		 
			$this->account_id     = $_tfhb_host_integration_settings['zoom_meeting']['account_id'];
			$this->client_id  = $_tfhb_host_integration_settings['zoom_meeting']['app_client_id'];
			$this->client_secret = $_tfhb_host_integration_settings['zoom_meeting']['app_secret_key'];
		}
	}

	// Set Client Data for Zoom API.
	public function setClientData(  ) {
		$_tfhb_integration_settings = get_option( '_tfhb_integration_settings' );
		$google_calendar            = isset( $_tfhb_integration_settings['google_calendar'] ) ? $_tfhb_integration_settings['google_calendar'] : array();

	}

	// Generate Access Token. 
	public function generateAccessToken() {
		// Fetch the access token
		$body    = array(
			'grant_type' => 'account_credentials',
			'account_id' => $this->account_id,
		);
		$headers = array(
			'Authorization' => 'Basic ' . base64_encode( $this->client_id . ':' . $this->client_secret ),

		);

		$response = wp_remote_post(
			$this->tokenUrl,
			array(
				'headers' => $headers,
				'body'    => $body,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		} else {
			$body               = wp_remote_retrieve_body( $response );
			$body               = json_decode( $body, true );
			$this->access_token = $body['access_token'];
			return $body;
		}
	}


	/* Create Zoom Meeting
	 * @param $single_booking_meta
	 * @param $meta_data
	 * @param $host_meta
	 * @return void
	 */
	public function tfhb_create_zoom_meeting( $booking) { 

		
		$BookingMeta = new BookingMeta();
		// check if the meeting id is available
		$get_booking_meta = $BookingMeta->getWithIdKey( $booking->booking_id, 'zoom_meeting', 1 ); 
		

		if ( $get_booking_meta ) { 
			// if the meeting id is available then update the meeting and add the attendees data
			$this->tfhb_add_attendee_to_zoom_meeting( $booking, $get_booking_meta );
			return false;

		}else{
			
			$location =  $this->create_zoom_meeting( $booking);
			
			return $location;
		}
		
 
	}

	// Add new Attendee to Zoom Meeting.
	public function tfhb_add_attendee_to_zoom_meeting( $booking, $get_booking_meta ) {

		$this->setHostApiDetails( $booking->host_id ); 
		$access_response = $this->generateAccessToken();

		$BookingMeta = new BookingMeta();
		$events = json_decode( $get_booking_meta->value, true );

		$new_events_data = array();
		foreach ($events as $key => $event) { 
			$attendees = json_decode( $booking->attendees, true );
			$attendees_data = $event['settings']['meeting_invitees']; 
			
			$attendees_data[] = array(
				'email' => $booking->email,
				'name'  => $booking->attendee_name,
			);
			
			$event['settings']['meeting_invitees'] = $attendees_data;
			
			$response = wp_remote_request(
				'https://api.zoom.us/v2/meetings/' . $event['id'],
				array(
					'method'  => 'PATCH',
					'body'    => wp_json_encode( $event ),
					'headers' => array(
						'Authorization' => 'Bearer ' . $this->access_token,
						'Content-Type'  => 'application/json',
					),
				)
			);
			// Handle the response
			if ( is_wp_error( $response ) ) {
				return $response; // Return the WP_Error object
			}

		
			$response_body = wp_remote_retrieve_body( $response );
			$events[$key] = $event;
		}  

		$bookingMetaData = array(
			'id' => $get_booking_meta->id,
			'value'      => wp_json_encode( $events, true ),
		);

		$BookingMeta->update( $bookingMetaData );


	}

	// Remove Attendee from Zoom Meeting.
	public function remove_attendde_location_from_existing_booking($booking_data, $attendee){

		$this->setHostApiDetails( $booking_data->host_id );
		$access_response = $this->generateAccessToken();
		$BookingMeta = new BookingMeta();
		$booking_meta = $BookingMeta->getWithIdKey( $booking_data->id, 'zoom_meeting', 1 );
		$events = json_decode( $booking_meta->value, true );

		$new_events_data = array();
		foreach ($events as $key => $event) { 
			$attendees = json_decode( $booking_data->attendees, true );
			$attendees_data = $event['settings']['meeting_invitees']; 
			
			$attendees_data = array_filter($attendees_data, function($value) use ($attendee) {
				return $value['email'] != $attendee->email;
			});
			$event['settings']['meeting_invitees'] = $attendees_data; 
			$response = wp_remote_request(
				'https://api.zoom.us/v2/meetings/' . $event['id'],
				array(
					'method'  => 'PATCH',
					'body'    => wp_json_encode( $event ),
					'headers' => array(
						'Authorization' => 'Bearer ' . $access_response['access_token'],
						'Content-Type'  => 'application/json',
					),
				)
			);
			// Handle the response
			if ( is_wp_error( $response ) ) {
				return $response; // Return the WP_Error object
			}

			$response_body = wp_remote_retrieve_body( $response );
			$events[$key] = $event;
		}  

		$bookingMetaData = array(
			'id' => $booking_meta->id,
			'value'      => wp_json_encode( $events, true ),
		);

		$BookingMeta->update( $bookingMetaData );
 
	}

	// Cancel Zoom Meeting.
	public function tfhb_cancel_zoom_meeting( $single_booking_meta ) {

		$this->setHostApiDetails( $single_booking_meta->host_id );
		$access_response = $this->generateAccessToken();
		$BookingMeta = new BookingMeta();
		$booking_meta = $BookingMeta->getWithIdKey( $single_booking_meta->id, 'zoom_calendar', 1 );
		


		if ( ! $booking_meta ) {
			return false;
		}
 
		$zoom_calendar = json_decode( $booking_meta->value );
		$zoom_event_body = array();
		foreach ( $zoom_calendar as $key => $value ) { 
 
			$response = wp_remote_request(
				'https://api.zoom.us/v2/meetings/' . $value->id,
				array(
					'method'  => 'DELETE',
					'headers' => array(
						'Authorization' => 'Bearer ' . $access_response['access_token'],
						'Content-Type'  => 'application/json',
					),
				)
			);

			// Handle the response
			if ( is_wp_error( $response ) ) {
				return $response; // Return the WP_Error object
			}

			$response_body = wp_remote_retrieve_body( $response );
			$zoom_event_body[] = json_decode( $response_body, true );
		}
		// Remove Zoom Calendar Meta
		$BookingMeta->delete( $booking_meta->id );

		// remove zoom meeting from booking data 
		$booking = new Booking();
		$getBookingData = $booking->get( $single_booking_meta->id );

		$meeting_locations = $getBookingData->meeting_locations;

		$meeting_locations->zoom->address = array(
			'link' => '',
			'password' => '',
		);

		$meeting_locations = !is_array($meeting_locations) ?  json_decode($meeting_locations)  :  $meeting_locations;

		$update                     = array();
		$update['id']               = $single_booking_meta->id;
		$update['meeting_locations'] = $meeting_locations;

		$booking->update( $update );

	 
	}


	// reshedule Zoom Meeting.
	public function tfhb_reshedule_zoom_meeting( $single_booking_meta ) {
		

		$this->setHostApiDetails( $single_booking_meta->host_id );
		$access_response = $this->generateAccessToken();
		$BookingMeta = new BookingMeta();
		$host = new Host();
		$meeting = new Meeting();
		$meeting_data = $meeting->get( $single_booking_meta->meeting_id );
		$host_meta = $host->get( $single_booking_meta->host_id );

		$booking_meta = $BookingMeta->getWithIdKey( $single_booking_meta->id, 'zoom_calendar', 1 );
	
		if ( ! $booking_meta ) {
			return false;
		}

		$zoom_calendar = json_decode( $booking_meta->value );

		$zoom_event_body = array();
		$meeting_dates = explode( ',', $single_booking_meta->meeting_dates );
		foreach ( $zoom_calendar as $key => $value ) {
			$meeting_date = $meeting_dates[ $key ];
			$start_time_combined = $meeting_date . ' ' . $single_booking_meta->start_time;
		
			$date = new \DateTime( $start_time_combined, new \DateTimeZone(! empty( $single_booking_meta->attendee_time_zone ) ? $single_booking_meta->attendee_time_zone : '') );
			$date->setTimezone( new \DateTimeZone('UTC') );
			$time_in_24_hour_format = $date->format('H:i:s');

			$attendee_data = array(
				array(
					'email' => $single_booking_meta->email,
					'name'  => $single_booking_meta->attendee_name,
				),
			);

			$data = array(
				'topic'      => ! empty( $meeting_data->title ) ? $meeting_data->title : '',
				'type'       => 2, // Scheduled Meeting
				'start_time' => $meeting_date . 'T' . $time_in_24_hour_format . 'Z',
				'timezone'   => ! empty( $single_booking_meta->attendee_time_zone ) ? $single_booking_meta->attendee_time_zone : '',
				'duration'   => $meeting_data->duration,
				'password'   => '123456',
				'settings'   => array(
					'join_before_host' => true,
					'mute_upon_entry'  => true,
					'waiting_room'     => false,
				),
				'contact_email' => $host_meta->email,
				'contact_name'  => $host_meta->email,
			);
	 

			$response = wp_remote_request(
				'https://api.zoom.us/v2/meetings/' . $value->id,
				array(
					'method'  => 'PATCH',
					'body'    => wp_json_encode( $data ),
					'headers' => array(
						'Authorization' => 'Bearer ' . $access_response['access_token'],
						'Content-Type'  => 'application/json',
					),
				)
			);
			
			// Handle the response
			if ( is_wp_error( $response ) ) {
				return $response; // Return the WP_Error object
			}

			$response_body = wp_remote_retrieve_body( $response ); 
			
			$zoom_event_body[] = json_decode( $response_body, true );
		}
		


		$bookingMetaData = array(
			'id' => $booking_meta->id, 
			'value'      => wp_json_encode( $zoom_event_body, true ),
		);
 
		$BookingMeta->update( $bookingMetaData );
		
		$booking = new Booking();

		$getBookingData = $booking->get( $single_booking_meta->id );
			
		$meeting_locations =  $getBookingData->meeting_locations;
		 
	 
		$zoom_link = '';
		$password = '';
		foreach ( $zoom_event_body as $key => $mvalue ) {
			$zoom_link .=  $mvalue['join_url'] . ' | ';
			$password .=  $mvalue['password'] . ' | ';
		} 

		$meeting_locations->zoom->address = array(
			'link' => $zoom_link,
			'password' => $password,
		);

	 
		$meeting_locations = !is_array($meeting_locations) ?  json_decode($meeting_locations)  :  $meeting_locations;
		
	 
		$update                     = array();
		$update['id']               = $booking_meta->id;
		$update['meeting_locations'] = $meeting_locations;


		$booking->update( $update ); 

		return  true;
		
	}

	// Update Zoom Settings in the database.
	public function updateZoomSettings( $data = null ) {

		if ( $data == null ) {
			return array(
				'status'  => false,
				'message' => 'Invalid Data',
			);
		}

		$this->account_id = sanitize_text_field( $data['account_id'] );
		$this->client_id = sanitize_text_field( $data['app_client_id'] );
		$this->client_secret = sanitize_text_field( $data['app_secret_key'] );  
	
		$_tfhb_integration_settings = get_option( '_tfhb_integration_settings' );
		// return error message if data is not set
		if ( ! isset( $data['account_id'] ) || ! isset( $data['app_client_id'] ) || ! isset( $data['app_secret_key'] ) ) {
			$data = array(
				'status'  => false,
				'message' => 'Invalid Data',
			);
			return $data;
		}

		$zoom_meeting['type']              = sanitize_text_field( $data['meeting'] );
		$zoom_meeting['status']            = sanitize_text_field( $data['status'] );
		$zoom_meeting['connection_status'] = 1;
		$zoom_meeting['account_id']        = sanitize_text_field( $data['account_id'] );
		$zoom_meeting['app_client_id']     = sanitize_text_field( $data['app_client_id'] );
		$zoom_meeting['app_secret_key']    = sanitize_text_field( $data['app_secret_key'] );

		$response = $this->generateAccessToken();

		if ( isset( $response['error'] ) ) {

			$data = array(
				'status'  => false,
				'message' => $response['reason'],
			);

			return $data;

		} else {

			$_tfhb_integration_settings['zoom_meeting'] = $zoom_meeting;

			// update option
			update_option( '_tfhb_integration_settings', $_tfhb_integration_settings );


			// get Option 
			$option = get_option( '_tfhb_integration_settings' );


			$data = array(
				'status'  => true,
				'integration_settings'  => $option,
				'message' => 'Zoom Integration Settings Updated Successfully',
			);
			return $data;
		}
	}

	// Update Zoom Settings in the database.
	public function updateHostsZoomSettings( $data = null, $user_id = null ) {
		

		if ( $data == null || $user_id == null ) {
			return array(
				'status'  => false,
				'message' => 'Invalid Data',
			);
		}
 
		$this->account_id = sanitize_text_field( $data['account_id'] );
		$this->client_id = sanitize_text_field( $data['app_client_id'] );
		$this->client_secret = sanitize_text_field( $data['app_secret_key'] );   

		$_tfhb_host_integration_settings = get_user_meta( $user_id, '_tfhb_host_integration_settings', true );

		// return error message if data is not set
		if ( ! isset( $data['account_id'] ) || ! isset( $data['app_client_id'] ) || ! isset( $data['app_secret_key'] ) ) {

			$data = array(
				'status'  => false,
				'message' => 'Invalid Data',
			);
			return $data;
		}
		
		$zoom_meeting['type']              = 'zoom-meeting';
		$zoom_meeting['status']            = sanitize_text_field( $data['status'] );
		$zoom_meeting['connection_status'] = 1;
		$zoom_meeting['account_id']        = sanitize_text_field( $data['account_id'] );
		$zoom_meeting['app_client_id']     = sanitize_text_field( $data['app_client_id'] );
		$zoom_meeting['app_secret_key']    = sanitize_text_field( $data['app_secret_key'] );

		$response = $this->generateAccessToken();

		if ( isset( $response['error'] ) ) {
			$data = array(
				'status'  => false,
				'message' => $response['reason'],
			);
			return $data;
		} else { 
			$_tfhb_host_integration_settings['zoom_meeting'] = $zoom_meeting; 

			// update user meta
			update_user_meta( $user_id, '_tfhb_host_integration_settings', $_tfhb_host_integration_settings );


			$data = array(
				'status'  => true,
				'message' => 'Zoom Integration Settings Updated Successfully',
			);
			return $data;
		}
	}

	public function create_zoom_meeting( $booking ) {

		$this->setHostApiDetails( $booking->host_id ); 
		$access_response = $this->generateAccessToken();
		$event_data = $this->zoomMeetingBody( $booking);
 
		
		$zoom_event_body = array();
		foreach($event_data as $data){
			$response = wp_remote_post(
				'https://api.zoom.us/v2/users/me/meetings',
				array(
					'body'    => wp_json_encode( $data ),
					'headers' => array(
						'Authorization' => 'Bearer ' . $access_response['access_token'],
						'Content-Type'  => 'application/json',
					),
				)
			);
	
			// Handle the response
			if ( is_wp_error( $response ) ) {
				return $response; // Return the WP_Error object
			}
	
			$response_body = wp_remote_retrieve_body( $response );
			$zoom_event_body[] = json_decode( $response_body, true );

			
		}
		$bookingMetaData = array(
			'booking_id' => $booking->booking_id,
			'meta_key'   => 'zoom_meeting',
			'value'      => wp_json_encode( $zoom_event_body, true ),
		);
 
		$BookingMeta = new BookingMeta(); 
		$BookingMeta->add( $bookingMetaData );
 
		$zoom_link = '';
		$password = '';
		foreach ( $zoom_event_body as $key => $mvalue ) {
			$zoom_link .=  $mvalue['join_url'] . ' | ';
			$password .=  $mvalue['password'] . ' | ';
		} 
		$address = array(
			'link' => $zoom_link,
			'password' => $password,
		);
		
		return $address;
 
	}

	public function update_zoom_meeting( $meeting_schedule_id, $booking_meta, $meeting_meta, $host_meta ) {
		
		$this->setHostApiDetails( $booking_meta->host_id );
		$access_response = $this->generateAccessToken();

		$data = $this->zoomMeetingBody( $booking_meta, $meeting_meta, $host_meta );

		$response = wp_remote_request(
			'https://api.zoom.us/v2/meetings/' . $meeting_schedule_id,
			array(
				'method'  => 'PATCH',
				'body'    => wp_json_encode( $data ),
				'headers' => array(
					'Authorization' => 'Bearer ' . $access_response['access_token'],
					'Content-Type'  => 'application/json',
				),
			)
		);

		// Handle the response
		if ( is_wp_error( $response ) ) {
			return $response; // Return the WP_Error object
		}

		$response_body = wp_remote_retrieve_body( $response );

		return $response_body;
	}



 

	public function zoomMeetingBody( $booking) { 
		$meeting_dates = explode( ',', $booking->meeting_dates );
 
		$event_data = array();

		$attendees_data = array();
		$attendees_data[] = array(
			'email' => $booking->host_email,
			'name'  => $booking->host_first_name,
		);
		$attendees_data[] = array(
			'email' => $booking->email,
			'name'  => $booking->attendee_name,
		);

		// Calculate duration
		$duration = $booking->duration + $booking->buffer_time_before + $booking->buffer_time_after + $booking->meeting_interval;
		 


		foreach($meeting_dates as $meeting_date){ 
			$start_time_combined = $meeting_date . ' ' . $booking->start_time;
		
			$date = new \DateTime( $start_time_combined, new \DateTimeZone(! empty( $booking->availability_time_zone ) ? $booking->availability_time_zone : '') );
			$date->setTimezone( new \DateTimeZone('UTC') );
			$time_in_24_hour_format = $date->format('H:i:s');
		
			
			$data = array(
				'topic'      => ! empty( $booking->title ) ? $booking->title : '',
				'type'       => 2, // Scheduled Meeting
				'start_time' => $meeting_date . 'T' . $time_in_24_hour_format . 'Z',
				'timezone'   => ! empty( $booking->host_time_zone ) ? $booking->host_time_zone : '',
				'duration'   => $duration,
				'password'   => '123456',
				'settings'   => array(
					'join_before_host' => true,
					'mute_upon_entry'  => true,
					'waiting_room'     => false, 
					'meeting_invitees' => $attendees_data,
				),
				'contact_email' => $booking->host_email,
				'contact_name'  => $booking->host_email,   
			);

			$event_data[] = $data;
		}
		
		return $event_data;
	}


	// zoom reshcedule meeting 

}
