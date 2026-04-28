<?php

namespace HydraBooking\Services\Integrations\GoogleCalendar;
// exit
if ( ! defined( 'ABSPATH' ) ) { exit; }

use HydraBooking\Admin\Controller\RouteController;
use HydraBooking\Admin\Controller\DateTimeController;
use HydraBooking\Admin\Controller\Helper;
use HydraBooking\DB\Booking;
use HydraBooking\DB\BookingMeta;
use HydraBooking\DB\Host;
use HydraBooking\DB\Meeting;
class GoogleCalendar {

	public $clientId;
	public $clientSecret;
	public $redirectUrl;

	private $accessToken;

	public $revokeUrl        = 'https://oauth2.googleapis.com/revoke';
	public $tokenUrl         = 'https://oauth2.googleapis.com/token';
	private $refreshTokenUrl = 'https://www.googleapis.com/oauth2/v3/token';
	public $authUrl          = 'https://accounts.google.com/o/oauth2/auth';

	public $calendarEvent 	= 'https://www.googleapis.com/calendar/v3/calendars/';

	public $authScope 		= 'https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/calendar.readonly https://www.googleapis.com/auth/calendar.events';



	public function __construct() {

		$this->setClientData(); 
 

		
	}

	private function get_activity_datetime() {
		$helper = new Helper();
		return wp_date( $helper->get_date_time_format_from_settings( 'M d, Y', 'h:i A' ) );
	}

	private function set_event_time_from_booking( &$event, $booking_data, $meeting_date = '' ) {
		if ( empty( $meeting_date ) ) {
			$meeting_date = isset( $booking_data->meeting_dates ) ? $booking_data->meeting_dates : '';
		}

		if ( empty( $meeting_date ) || empty( $booking_data->start_time ) || empty( $booking_data->end_time ) ) {
			return;
		}

		$start_time = strtotime( $booking_data->start_time );
		$end_time   = strtotime( $booking_data->end_time );

		$start_date = gmdate( 'Y-m-d', strtotime( $meeting_date ) ) . 'T' . gmdate( 'H:i:s', $start_time );
		$end_date   = gmdate( 'Y-m-d', strtotime( $meeting_date ) ) . 'T' . gmdate( 'H:i:s', $end_time );

		$event->start = array(
			'dateTime' => $start_date,
			'timeZone' => $booking_data->availability_time_zone,
		);

		$event->end = array(
			'dateTime' => $end_date,
			'timeZone' => $booking_data->availability_time_zone,
		);
	}

	// Update Google Calender
	public function checkConnectionStatus(){
		if($this->clientId != '' && $this->clientSecret != '' && $this->redirectUrl != ''){
			return true;
		}
		return false;
	}

	// Set Client Data
	public function setClientData() {
		// Get the Google Calendar Data
		$_tfhb_integration_settings = get_option( '_tfhb_integration_settings' );
		$google_calendar            = isset( $_tfhb_integration_settings['google_calendar'] ) ? $_tfhb_integration_settings['google_calendar'] : array();

		// Set the Client Data
		$this->clientId     = isset( $google_calendar['client_id'] ) ? $google_calendar['client_id'] : '';
		$this->clientSecret = isset( $google_calendar['secret_key'] ) ? $google_calendar['secret_key'] : '';
		$this->redirectUrl  = isset( $google_calendar['redirect_url'] ) ? $google_calendar['redirect_url'] : $this->setRredirectUrl();
	}

	public function setRredirectUrl() {
		// example : wp-json/hydra-booking/v1/integration/google-api
		return get_rest_url() . 'hydra-booking/v1/integration/google-api';
	}

	private function get_oauth_state_key( $state ) {
		return 'tfhb_google_oauth_' . md5( $state );
	}

	private function create_oauth_state( $user_id ) {
		$user_id = absint( $user_id );

		if ( ! $user_id || ! get_userdata( $user_id ) ) {
			return '';
		}

		$state = wp_generate_password( 32, false, false );

		set_transient(
			$this->get_oauth_state_key( $state ),
			array(
				'user_id'       => $user_id,
				'initiated_by'  => get_current_user_id(),
				'session_token' => wp_get_session_token(),
			),
			HOUR_IN_SECONDS
		);

		return $state;
	}

	private function get_oauth_state_data( $state ) {
		$state = sanitize_text_field( wp_unslash( $state ) );

		if ( empty( $state ) ) {
			return false;
		}

		$state_data = get_transient( $this->get_oauth_state_key( $state ) );

		if ( ! is_array( $state_data ) || empty( $state_data['user_id'] ) ) {
			return false;
		}

		$current_session_token = wp_get_session_token();

		if ( ! empty( $state_data['session_token'] ) && ! hash_equals( $state_data['session_token'], $current_session_token ) ) {
			return false;
		}

		return $state_data;
	}

	private function delete_oauth_state( $state ) {
		$state = sanitize_text_field( wp_unslash( $state ) );

		if ( ! empty( $state ) ) {
			delete_transient( $this->get_oauth_state_key( $state ) );
		}
	}
	// Set Access Token
	public function setAccessToken( $user_id ) {

		$_tfhb_host_integration_settings = is_array( get_user_meta( $user_id, '_tfhb_host_integration_settings', true ) ) ? get_user_meta( $user_id, '_tfhb_host_integration_settings', true ) : array();
		$accessToken                     = isset( $_tfhb_host_integration_settings['google_calendar']['tfhb_google_calendar']['access_token'] ) ? $_tfhb_host_integration_settings['google_calendar']['tfhb_google_calendar']['access_token'] : '';

		$this->accessToken = $accessToken;
	}

	public function create_endpoint() {
		register_rest_route(
			'hydra-booking/v1',
			'/integration/google-api',
			array(
				'methods'  => 'GET',
				'callback' => array( $this, 'GetAccessData' ),
				'permission_callback' =>  array( $this , 'permission_callback'),
			)
		);
	}
	public function permission_callback( $request ) {
		$state = $request instanceof \WP_REST_Request ? $request->get_param( 'state' ) : '';

		if ( false === $this->get_oauth_state_data( $state ) ) {
			return new \WP_Error(
				'rest_forbidden',
				__( 'Sorry, you are not allowed to do that.', 'hydra-booking' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	public function GetAccessData( $request ) {
		$code  = $request instanceof \WP_REST_Request ? $request->get_param( 'code' ) : ( isset( $_GET['code'] ) ? wp_unslash( $_GET['code'] ) : '' );
		$state = $request instanceof \WP_REST_Request ? $request->get_param( 'state' ) : ( isset( $_GET['state'] ) ? wp_unslash( $_GET['state'] ) : '' );
		$error = $request instanceof \WP_REST_Request ? $request->get_param( 'error' ) : ( isset( $_GET['error'] ) ? wp_unslash( $_GET['error'] ) : '' );

		$state_data = $this->get_oauth_state_data( $state );

		if ( false === $state_data ) {
			return new \WP_Error(
				'invalid_google_oauth_state',
				__( 'Invalid or expired Google authorization state.', 'hydra-booking' ),
				array( 'status' => 403 )
			);
		}

		$user_id      = absint( $state_data['user_id'] );
		$redirect_url = get_site_url() . '/wp-admin/admin.php?page=hydra-booking#/hosts/profile/' . $user_id . '/calendars';

		if ( ! empty( $error ) ) {
			$this->delete_oauth_state( $state );
			wp_safe_redirect( add_query_arg( 'google_calendar_error', sanitize_text_field( $error ), $redirect_url ) );
			exit;
		}

		if ( empty( $code ) ) {
			$this->delete_oauth_state( $state );
			return new \WP_Error(
				'missing_google_oauth_code',
				__( 'Missing Google authorization code.', 'hydra-booking' ),
				array( 'status' => 400 )
			);
		}

		try {
			$data = $this->GetAccessToken( sanitize_text_field( $code ) );

			if ( empty( $data['access_token'] ) || empty( $data['id_token'] ) ) {
				$this->delete_oauth_state( $state );
				return new \WP_Error(
					'google_oauth_token_error',
					__( 'Unable to validate the Google authorization response.', 'hydra-booking' ),
					array( 'status' => 400 )
				);
			}

			$email = $this->getEmailByIdToken( $data['id_token'] );

			// Get all calendar in the account
			$url      = 'https://www.googleapis.com/calendar/v3/users/me/calendarList';
			$response = wp_remote_get( $url, array( 'headers' => array( 'Authorization' => 'Bearer ' . $data['access_token'] ) ) );
			$body     = wp_remote_retrieve_body( $response );
			$body     = json_decode( $body, true );

			$data['email'] = sanitize_email( $email );
			$data['items'] = array();

			if ( isset( $body['items'] ) && is_array( $body['items'] ) ) {
				foreach ( $body['items'] as $calendar ) {
					if ( isset( $calendar['accessRole'] ) && ( 'owner' === $calendar['accessRole'] || 'writer' === $calendar['accessRole'] ) ) {
						$data['items'][] = array(
							'id'           => isset( $calendar['id'] ) ? $calendar['id'] : '',
							'title'        => isset( $calendar['summary'] ) ? $calendar['summary'] : '',
							'write_status' => 0,
						);
					}
				}
			}

			// remove the Id Token
			unset( $data['id_token'] );

			$_tfhb_host_integration_settings = is_array( get_user_meta( $user_id, '_tfhb_host_integration_settings', true ) ) ? get_user_meta( $user_id, '_tfhb_host_integration_settings', true ) : array();

			$_tfhb_host_integration_settings['google_calendar']['tfhb_google_calendar'] = $data;

			// save to user metadata
			update_user_meta( $user_id, '_tfhb_host_integration_settings', $_tfhb_host_integration_settings );
			$this->delete_oauth_state( $state );

			wp_safe_redirect( $redirect_url );
			exit;

		} catch ( \Exception $e ) {
			$this->delete_oauth_state( $state );
			return new \WP_Error(
				'google_oauth_error',
				esc_html( $e->getMessage() ),
				array( 'status' => 400 )
			);
		}
	}


	// if Access token is experired
	public function refreshToken( $host_id ) {
		$host     = new Host();
		$hostData = $host->get( $host_id );
		$user_id  = $hostData->user_id;

		$_tfhb_host_integration_settings = is_array( get_user_meta( $user_id, '_tfhb_host_integration_settings', true ) ) ? get_user_meta( $user_id, '_tfhb_host_integration_settings', true ) : array();
		$refreshToken                    = isset( $_tfhb_host_integration_settings['google_calendar']['tfhb_google_calendar']['refresh_token'] ) ? $_tfhb_host_integration_settings['google_calendar']['tfhb_google_calendar']['refresh_token'] : '';

		$url          = $this->refreshTokenUrl;
		$clientId     = $this->clientId;
		$clientSecret = $this->clientSecret;
		$post_fields  = array(
			'client_id'     => $clientId,
			'client_secret' => $clientSecret,
			'refresh_token' => $refreshToken,
			'grant_type'    => 'refresh_token',
		);
		// use Wp Remote Request
		$response = wp_remote_post(
			$url,
			array(
				'body' => $post_fields,
			)
		);
		$body     = wp_remote_retrieve_body( $response );
		$body     = json_decode( $body, true );

		if ( $body['access_token'] ) {

			$this->accessToken = $body['access_token'];
		} else {
			$this->setAccessToken( $user_id );
		}

		return $body;
	}

	public function GetAccessToken( $code ) {
		$url          = $this->tokenUrl;
		$clientId     = $this->clientId;
		$clientSecret = $this->clientSecret;
		$redirectUrl  = $this->redirectUrl;
		$post_fields  = array(
			'code'          => $code,
			'client_id'     => $clientId,
			'client_secret' => $clientSecret,
			'redirect_uri'  => $redirectUrl,
			'grant_type'    => 'authorization_code',
		);
		// use Wp Remote Request
		$response = wp_remote_post(
			$url,
			array(
				'body' => $post_fields,
			)
		);
		$body     = wp_remote_retrieve_body( $response );

		return json_decode( $body, true );
	}

	public function GetAccessTokenUrl( $user_id ) {
		$state = $this->create_oauth_state( $user_id );

		if ( empty( $state ) ) {
			return '';
		}

		return add_query_arg(
			array(
				'client_id'     => $this->clientId,
				'redirect_uri'  => $this->redirectUrl,
				'scope'         => $this->authScope,
				'response_type' => 'code',
				'access_type'   => 'offline',
				'prompt'        => 'consent',
				'state'         => $state,
			),
			$this->authUrl
		);
	}


	/**
	 * Get the email by id token
	 *
	 * @param $token
	 * @return mixed
	 */
	public function getEmailByIdToken( $id_token ) {
		$tokenParts   = explode( '.', $id_token );
		$tokenPayload = base64_decode( $tokenParts[1] );
		$jwtPayload   = json_decode( $tokenPayload, true );
		return $jwtPayload['email'];
	}


	public function generateAuthCode( $code ) {
		$body = array(
			'client_id'     => $this->clientId,
			'client_secret' => $this->clientSecret,
			'redirect_uri'  => $this->redirectUrl,
			'grant_type'    => 'authorization_code',
			'code'          => $code,
		);

		$type    = 'GET';
		$url     = $this->tokenUrl;
		$headers = array(
			'Content-Type'              => 'application/http',
			'Content-Transfer-Encoding' => 'binary',
			'MIME-Version'              => '1.0',
		);

		$args = array(
			'headers' => $headers,
			'method'  => $type,
			'timeout' => 20,
		);

		if ( $body ) {

			$url = add_query_arg( $body, $url );
		}

		$request = wp_remote_request( $url, $args );
	}



	// Insert Booking to Google Calendar
	public function InsertGoogleCalender($booking ) {
		
		$value = array();
	
		if ( ! isset( $booking->id ) ) {
			return;
		}
		


		$settings        = get_option( '_tfhb_integration_settings' );
		$google_calender = isset( $settings['google_calendar'] ) ? $settings['google_calendar'] : array();
		
		if ( isset( $google_calender['status'] ) && $google_calender['status'] == 0 ) {
			return $value;
		}
		if ( isset( $google_calender['connection_status'] ) && $google_calender['connection_status'] == 0 ) {
			return $value;
		}
 
		// set event data google meet shedule
		
		$meeting_dates = $booking->meeting_dates; // 2024-07-10,2024-07-17,2024-07-24,2024-07-31
		 
		if($booking->booking_type == 'one-to-group'){ 
			$event_title =  $booking->meeting_title;
		}else{
			$event_title = $booking->meeting_title . ' ( Meeting with ' . $booking->attendee_name .' )';
		}

		// Set the Access Token
		$this->refreshToken( $booking->host_id );

		$meeting_dates = explode( ',', $meeting_dates );
 
		$google_calendar_body = array(); 
 
		$attendees_data = array();
		$attendees_data[] = array('email' => $booking->host_email);
		$attendees_data[] = array('email' => $booking->email);  
		$availability_time_zone = $booking->availability_time_zone;

		// Check if meeting location is meet
		$meeting_loaction = json_decode( $booking->meeting_locations, true );
		$enable_meeting_location = false;
		// if in array location value is meet then set google meet using array filter
		$meeting_location = array_filter(
			$meeting_loaction,
			function ( $location ) {
				return $location['location'] == 'meet';
			}
		);

		$enable_meeting_location = count( $meeting_location ) > 0 ? true : false;
		
		$locations = !empty($booking->meeting_locations) ? $booking->meeting_locations : array();
        $booking_locations = json_decode($locations);
        $booking_locations_data ='';
        foreach ($booking_locations as $key => $value) { 
            $booking_locations_data .= ''.$value->location.', ';
        }
 
		$start_time    = strtotime( $booking->start_time ); // 03:45 AM
		$end_time      = strtotime( $booking->end_time ); // 04:30 AM
		foreach ( $meeting_dates as $meeting_date ) {
			// Co
			$start_date = gmdate( 'Y-m-d', strtotime( $meeting_date ) ) . 'T' . gmdate( 'H:i:s', $start_time );
			$end_date   = gmdate( 'Y-m-d', strtotime( $meeting_date ) ) . 'T' . gmdate( 'H:i:s', $end_time );


			// Meeting location google meeting
			$setData = array(
				'title'          => $event_title,
				'summary'        => $event_title,
				'location' => $booking_locations_data,
				'description'    => 'Description: ',
				'start'          => array(
					'dateTime' => $start_date,
					'timeZone' => $availability_time_zone,
				),
				'end'            => array(
					'dateTime' => $end_date,
					'timeZone' => $availability_time_zone,
				),
				'attendees'      => $attendees_data,
				'reminders'      => array(
					'useDefault' => false,
					'overrides'  => array(
						array(
							'method'  => 'email',
							'minutes' => 24 * 60,
						),
						array(
							'method'  => 'popup',
							'minutes' => 10,
						),
					),
				),
				'conferenceData' => array(
					'createRequest' => array(
						'requestId'             => 'sample123', // Provide a unique ID for the request
						'conferenceSolutionKey' => array(
							'type' => 'hangoutsMeet',
						),
					),
				),
			);
			if ( $enable_meeting_location == true ) {
				$setData['conferenceData'] = array(
					'createRequest' => array(
						'requestId'             => 'sample123', // Provide a unique ID for the request
						'conferenceSolutionKey' => array(
							'type' => 'hangoutsMeet',
						),
					),
				);
			}

			$host = new Host();
			$hostData = $host->get( $booking->host_id );
			$_tfhb_host_integration_settings = is_array( get_user_meta( $hostData->user_id, '_tfhb_host_integration_settings', true ) ) ? get_user_meta( $hostData->user_id, '_tfhb_host_integration_settings', true ) : array();
	
			$google_calendar                 = isset( $_tfhb_host_integration_settings['google_calendar'] ) ? $_tfhb_host_integration_settings['google_calendar'] : array();
			$calendarId                      = isset( $google_calendar['selected_calendar_id'] ) && !empty($google_calendar['selected_calendar_id']) ? $google_calendar['selected_calendar_id'] : $google_calendar['tfhb_google_calendar']['email'];
   
  
			if ( $calendarId ) {

				$url = $this->calendarEvent . $calendarId . '/events?sendUpdates=all';
				if ( $enable_meeting_location == true ) {
					$url .= '&conferenceDataVersion=1';


				}
				// set all events
				$response = wp_remote_post(
					$url,
					array(
						'headers'     => array(
							'Authorization' => 'Bearer ' . $this->accessToken,
						),
						'body'        => wp_json_encode( $setData ),
						'method'      => 'POST',
						'data_format' => 'body',
					)
				);

				$body = wp_remote_retrieve_body( $response );
				
				$google_calendar_body[ ] = json_decode( $body, true );
 
			}
		}

	
		
		$value = array(
			'google_calendar' => $google_calendar_body,
		);

		$booking_meta = array(
			'booking_id' => $booking->booking_id,
			'meta_key'   => 'booking_calendar',
			'value'      => wp_json_encode( $value, true ),
		);

		$BookingMeta = new BookingMeta(); 
		$insert = $BookingMeta->add( $booking_meta );

		$insert_id = $insert['insert_id'];
		if ( $insert_id === false ) {
			return false;
		} 
		

		$meet_link = '';
		foreach ( $google_calendar_body as $key => $mvalue ) {
			$hangoutLink = isset( $mvalue['hangoutLink'] ) ? $mvalue['hangoutLink'] : '';
			if ( $hangoutLink != '' ) {
				$meet_link .= $hangoutLink . ' | ';
			}
		} 
		
 
		
		$getBooking = new Booking();
		if($meet_link != '' && $enable_meeting_location == true ){
			$getBookingData = $getBooking->get( $booking->booking_id ); 
			$meeting_loaction  = json_decode($getBookingData->meeting_locations); 
			
			// remove the last pipe
			$meet_link = rtrim($meet_link, '|');

			if(isset($meeting_loaction->meet)){
				$meeting_loaction->meet->address = $meet_link;
			} 

			$booking->meeting_locations = $meeting_loaction;

		}
		$meeting_loaction =   $booking->meeting_locations; 
		$meeting_locations = is_array($meeting_loaction) ?  json_decode($meeting_loaction)  :  $meeting_loaction;
		 
		$updateData = array();
		$updateData['id']               = $booking->booking_id; 
		$updateData['meeting_locations'] = $meeting_locations; 

		$getBooking->update( $updateData );
 
  
		return true;
	}


	// Insert Calender After Booking Schedule
	public function insert_calender_after_booking_confirmed( $booking ) {
		 
		if($this->checkConnectionStatus() == false){
			return;
		}
		$BookingMeta = new BookingMeta();
		$get_booking_meta = $BookingMeta->getWithIdKey( $booking->booking_id, 'booking_calendar', 1 );  
		 
		if($get_booking_meta){
			$this->addAttendeeGoogleCalender($booking, $get_booking_meta);	 

			// Add activity after email sent
			$BookingMeta->add([
				'booking_id' => $booking->booking_id,
				'meta_key' => 'booking_activity',
				'value' => array( 
						'datetime' => $this->get_activity_datetime(), 
						'title' =>  'Updated Google Calendar Event',  // translate it from Vue
						'description' =>  'Attendee added to the google calendar', 
					)
				]
			);
		}else{
			
			// Update the Booking
			$this->InsertGoogleCalender($booking);

			// Add activity after email sent
			$BookingMeta->add([
				'booking_id' => $booking->booking_id,
				'meta_key' => 'booking_activity',
				'value' => array( 
						'datetime' => $this->get_activity_datetime(), 
						'title' =>  'Google Calendar Event Created',  // translate it from Vue
						'description' => 'Attendee added to the google calendar',
					)
				]
			);
		}
 
		return true;
	}
	/**
	 * Remove Attendee Google Calender
	 *
	 * @param $booking_calendar_id
	 * @param $booking_calendar_value
	 * @param $data
	 * @return mixed
	 */
	/**
	 * Delete all Google Calendar events stored for a booking and remove the meta record.
	 * Passing sendUpdates=all causes Google to email cancellation notices to all attendees.
	 *
	 * @param object $booking_meta  Row from BookingMeta (must have ->id and ->value).
	 * @param int    $host_id
	 * @return void
	 */
	private function delete_google_calendar_events( $booking_meta, $host_id ) {
		$host     = new Host();
		$hostData = $host->get( $host_id );

		if ( ! $hostData ) {
			return;
		}

		$_tfhb_host_integration_settings = is_array( get_user_meta( $hostData->user_id, '_tfhb_host_integration_settings', true ) ) ? get_user_meta( $hostData->user_id, '_tfhb_host_integration_settings', true ) : array();
		$google_calendar                 = isset( $_tfhb_host_integration_settings['google_calendar'] ) ? $_tfhb_host_integration_settings['google_calendar'] : array();
		$calendarId                      = isset( $google_calendar['selected_calendar_id'] ) && ! empty( $google_calendar['selected_calendar_id'] ) ? $google_calendar['selected_calendar_id'] : ( isset( $google_calendar['tfhb_google_calendar']['email'] ) ? $google_calendar['tfhb_google_calendar']['email'] : '' );

		if ( ! $calendarId ) {
			return;
		}

		$value  = json_decode( $booking_meta->value );
		$events = isset( $value->google_calendar ) ? $value->google_calendar : array();

		foreach ( $events as $event ) {
			$event_id = isset( $event->id ) ? $event->id : '';
			if ( empty( $event_id ) ) {
				continue;
			}

			// DELETE with sendUpdates=all → Google sends cancellation emails to all guests.
			wp_remote_request(
				$this->calendarEvent . $calendarId . '/events/' . $event_id . '?sendUpdates=all',
				array(
					'headers' => array( 'Authorization' => 'Bearer ' . $this->accessToken ),
					'method'  => 'DELETE',
				)
			);
		}

		// Remove the stored calendar meta so InsertGoogleCalender creates a fresh record.
		$BookingMeta = new BookingMeta();
		$BookingMeta->delete( $booking_meta->id );
	}

	public function remove_attendde_event_from_existing_booking( $old_booking_id, $attendee){ 
		
		 
		if($old_booking_id !=0 ){
			$booking_id = $old_booking_id; 
			//  Get Booking With Attendee
			$booking = new Booking();
			$booking_data = $booking->get( $booking_id ); 
			if ( ! $booking_data ) {
				return;
			}

			$is_same_booking_reschedule = ! empty( $attendee ) && isset( $attendee->booking_id ) && absint( $attendee->booking_id ) === absint( $old_booking_id );
			$this->refreshToken( $booking_data->host_id );
			$meeting_dates = ! empty( $booking_data->meeting_dates ) ? explode( ',', $booking_data->meeting_dates ) : array();
				// if is not array or not object json decode
			$locations = ! empty( $attendee ) && isset( $attendee->meeting_locations ) ? $attendee->meeting_locations : array(); 

			$_tfhb_integration_settings = get_option( '_tfhb_integration_settings' );
			 
			$BookingMeta = new BookingMeta();
			$booking_meta = $BookingMeta->getWithIdKey( $booking_data->id, 'booking_calendar', 1);

			// ----------------------------------------------------------------
			// Same-booking reschedule: cancel the old event (Google sends cancel
			// emails) then create a fresh event at the new time (Google sends
			// a new invite). No attendee-filter PUT is needed.
			// ----------------------------------------------------------------
			if ( $is_same_booking_reschedule ) {
				if ( $booking_meta ) {
					$this->delete_google_calendar_events( $booking_meta, $booking_data->host_id );
				}

				// InsertGoogleCalender reads meeting_dates/start_time/end_time from the
				// booking row which already holds the rescheduled values at this point.
				$this->InsertGoogleCalender( $attendee );

				$ActivityMeta = new BookingMeta();
				$ActivityMeta->add( array(
					'booking_id' => $attendee->booking_id,
					'meta_key'   => 'booking_activity',
					'value'      => array(
						'datetime'    => $this->get_activity_datetime(),
						'title'       => 'Updated Google Calendar Event',
						'description' => 'Google Calendar event cancelled and recreated at new time',
					),
				) );

				return;
			}
			// ----------------------------------------------------------------
			
			if($booking_meta){

				$events = json_decode( $booking_meta->value ); 
				$events = $events->google_calendar;
				
				$host = new Host();
				$hostData = $host->get( $booking_data->host_id ); 

				$google_calendar_body = array();
				foreach ( $events as $index => $event ) {
					$event_id = $event->id;
					$meeting_date = isset( $meeting_dates[ $index ] ) ? trim( $meeting_dates[ $index ] ) : ( isset( $meeting_dates[0] ) ? trim( $meeting_dates[0] ) : '' );
					$this->set_event_time_from_booking( $event, $booking_data, $meeting_date );

					
					$attendees_data = isset( $event->attendees ) && is_array( $event->attendees ) ? $event->attendees : array(); 
					
					// remove existing attendee
					if ( ! empty( $attendee ) && isset( $attendee->email ) ) {
						$attendees_data = array_filter($attendees_data, function($value) use ($attendee) {
							return isset( $value->email ) && $value->email != $attendee->email;
						});
					}
					
					$event->attendees = $attendees_data; 

					$_tfhb_host_integration_settings = is_array( get_user_meta( $hostData->user_id, '_tfhb_host_integration_settings', true ) ) ? get_user_meta( $hostData->user_id, '_tfhb_host_integration_settings', true ) : array();
					$google_calendar                 = isset( $_tfhb_host_integration_settings['google_calendar'] ) ? $_tfhb_host_integration_settings['google_calendar'] : array();
					$calendarId                      = isset( $google_calendar['selected_calendar_id'] ) && !empty($google_calendar['selected_calendar_id']) ? $google_calendar['selected_calendar_id'] : $google_calendar['tfhb_google_calendar']['email'];
   

					if ( $calendarId ) {

						$url      = $this->calendarEvent . $calendarId . '/events/' . $event_id .'?sendUpdates=all';
						$response = wp_remote_post(
							$url,
							array(
								'headers'     => array( 'Authorization' => 'Bearer ' . $this->accessToken ),
								'body'        => wp_json_encode( $event ),
								'method'      => 'PUT',
								'data_format' => 'body',
							)
						);
						$body     = wp_remote_retrieve_body( $response );

						$google_calendar_body[] = json_decode( $body, true ); 
					} 
				}  
				$value = array(
					'google_calendar' => $google_calendar_body,
				);
				$UpdateBookingMeta = new BookingMeta(); 
				
				// Update the Booking meta
				$booking_meta = array(
					'id'    => $booking_meta->id,
					'value' => wp_json_encode( $value ),
				);  
				$UpdateBookingMeta->update( $booking_meta ); 

				// Add activity after email sent
				$UpdateBookingMeta->add([
					'booking_id' => ! empty( $attendee ) && isset( $attendee->booking_id ) ? $attendee->booking_id : $booking_data->id,
					'meta_key' => 'booking_activity',
					'value' => array( 
							'datetime' => $this->get_activity_datetime(), 
							'title' =>  'Updated Google Calendar Event',  // translate it from Vue
							'description' =>  'Attendee removed from the google calendar',
						)
					]
				);
			} 
		} 
		if ( ! empty( $attendee ) && isset( $attendee->booking_id ) ) {
			$this->insert_calender_after_booking_confirmed($attendee);
		}
			 

	}

	// add new attendee existing Booking to Google Calendar
	public function addAttendeeGoogleCalender( $booking, $BookingMeta) {
		// Set the Access Token
		$this->refreshToken( $booking->host_id );
		$events =  json_decode($BookingMeta->value);
		$events = $events->google_calendar; 
		$meeting_dates = ! empty( $booking->meeting_dates ) ? explode( ',', $booking->meeting_dates ) : array();
		$host = new Host();
		$hostData = $host->get( $booking->host_id ); 

		$google_calendar_body = array();
		foreach ( $events as $index => $event ) {
			$event_id = $event->id;
			$meeting_date = isset( $meeting_dates[ $index ] ) ? trim( $meeting_dates[ $index ] ) : ( isset( $meeting_dates[0] ) ? trim( $meeting_dates[0] ) : '' );
			$this->set_event_time_from_booking( $event, $booking, $meeting_date );
 
			if ( ! isset( $event->attendees ) || ! is_array( $event->attendees ) ) {
				$event->attendees = array();
			}

			if ( ! empty( $booking->email ) ) {
				$existing_attendee_emails = array_map(
					function( $attendee ) {
						if ( is_object( $attendee ) && isset( $attendee->email ) ) {
							return $attendee->email;
						}

						if ( is_array( $attendee ) && isset( $attendee['email'] ) ) {
							return $attendee['email'];
						}

						return '';
					},
					$event->attendees
				);

				if ( ! in_array( $booking->email, $existing_attendee_emails, true ) ) {
					$event->attendees[] = array('email' => $booking->email);
				}
			}

			$_tfhb_host_integration_settings = is_array( get_user_meta( $hostData->user_id, '_tfhb_host_integration_settings', true ) ) ? get_user_meta( $hostData->user_id, '_tfhb_host_integration_settings', true ) : array();
			$google_calendar                 = isset( $_tfhb_host_integration_settings['google_calendar'] ) ? $_tfhb_host_integration_settings['google_calendar'] : array();
			$calendarId                      = isset( $google_calendar['selected_calendar_id'] ) && !empty($google_calendar['selected_calendar_id']) ? $google_calendar['selected_calendar_id'] : $google_calendar['tfhb_google_calendar']['email'];
   

			if ( $calendarId ) {

				$url      = $this->calendarEvent . $calendarId . '/events/' . $event_id .'?sendUpdates=all';
				$response = wp_remote_post(
					$url,
					array(
						'headers'     => array( 'Authorization' => 'Bearer ' . $this->accessToken ),
						'body'        => wp_json_encode( $event ),
						'method'      => 'PUT',
						'data_format' => 'body',
					)
				);
				$body     = wp_remote_retrieve_body( $response );

				$google_calendar_body[] = json_decode( $body, true ); 
			} 
		} 
		 
		$value = array(
			'google_calendar' => $google_calendar_body,
		);
		$UpdateBookingMeta = new BookingMeta(); 
		
		// Update the Booking meta
		$booking_meta = array(
			'id'    => $BookingMeta->id,
			'value' => wp_json_encode( $value ),
		);  
		$UpdateBookingMeta->update( $booking_meta );
		 
 
		 
	}

	/**
	 * Delete Google Calendar
	 *
	 * @param $data
	 * @param $booking
	 */
	public function deleteGoogleCalender( $data ) { 
		if($this->checkConnectionStatus() == false){
			return;
		}
		// Get Meeting Calendar Data
		$meeting = new Meeting();
		$booking = new Booking();
		$bookingMeta = new BookingMeta();
		$host = new Host();
		$hostData = $host->get( $data->host_id );
		$meetingData = $meeting->get( $data->meeting_id );
		$meeting_locations = json_decode( $meetingData->meeting_locations, true ); // [{"location":"meet","address":""}]

		//  if !in array location value is meet then  return false 
		$meeting_location = array_filter(
			$meeting_locations,
			function ( $location ) {
				return $location['location'] == 'meet';
			}
		);
		if ( count( $meeting_location ) == 0 ) {
			return false;
		}

		if('one-to-group' == $meetingData->meeting_type){

			$max_book_per_slot = isset( $meetingData->max_book_per_slot ) ? $meetingData->max_book_per_slot : 1;
			
			$check_booking = $booking->getCheckBooking( $data->meeting_id, $data->meeting_dates, $data->start_time, $data->end_time );
			
			

			// unset if check_booking has current booking data->id without loop and array maps or filter
			$check_booking = array_filter(
				$check_booking,
				function ( $data ) use ( $sdata ) {
					return $data->id !== $sdata->id;
				}
			); 
	

			// insert first booking
			if(count($check_booking) != 0){  
				$booking_ids = array_map(
					function ( $booking ) {
						return $booking->id;
					},
					$check_booking
				);
				
				
				$booking_calendar           = $bookingMeta->getFirstDataOfMultipleIds( $booking_ids, 'booking_calendar' );
				 
 
				if ( $booking_calendar ) { 
					 
					$booking_calendar_value = json_decode( $booking_calendar->value );
					

					$booking_calendar_value = $this->removeAttendeeGoogleCalender($booking_calendar->id, $booking_calendar_value, $data);
					// $booking_calendar_value = apply_filters( 'hydra_booking_calendar_add_new_attendee', $booking_calendar_value, $data );
	 
					return;

				}
			}
		}
		
		$bookingMeta = new BookingMeta(); 
		$booking_calendarData = $bookingMeta->getWithIdKey( $data->id, 'booking_calendar' , 1);
		

		// if booking calendar data is not found then return false
		if ( ! $booking_calendarData ) {
			return false;
		}
 

		// Set the Access Token
		$this->refreshToken( $data->host_id );

		
		$value = json_decode($booking_calendarData->value);
		$events = $value->google_calendar;
		 
		foreach($events as $event){
			$event_id = $event->id;

			$_tfhb_host_integration_settings = is_array( get_user_meta( $hostData->user_id, '_tfhb_host_integration_settings', true ) ) ? get_user_meta( $hostData->user_id, '_tfhb_host_integration_settings', true ) : array();
			$google_calendar                 = isset( $_tfhb_host_integration_settings['google_calendar'] ) ? $_tfhb_host_integration_settings['google_calendar'] : array();
			$calendarId                      = isset( $google_calendar['selected_calendar_id'] ) && !empty($google_calendar['selected_calendar_id']) ? $google_calendar['selected_calendar_id'] : $google_calendar['tfhb_google_calendar']['email'];
   

			if ( $calendarId ) {

				$url      = $this->calendarEvent . $calendarId . '/events/' . $event_id .'?sendUpdates=all';
				$response = wp_remote_request(
					$url,
					array(
						'headers' => array( 'Authorization' => 'Bearer ' . $this->accessToken ),
						'method'  => 'DELETE',
					)
				);
				$body     = wp_remote_retrieve_body( $response );
	

			} 
		}
		// unest google calendar form value
		unset($value->google_calendar);
		$booking_calendarData->value = json_encode($value, true);
		
		// $Update = $bookingMeta->update( array('id' => $booking_calendarData->id, 'value' => $booking_calendarData->value) );
		$delete = $bookingMeta->delete( $booking_calendarData->id );

		
		if ( $delete ) {
				// update meeting location
				$updateBooking = new Booking();
				$updateBookingData = $updateBooking->get( $data->id ); 
				$meeting_loaction  = json_decode($updateBookingData->meeting_locations); 
				$meeting_loaction->meet->address = 'Cancelled'; 


				$updateBooking->update( array('id' => $data->id, 'meeting_locations' => $meeting_loaction) );
				

			return true;
		} else {
			return false;
		}

	
	}


	// remove attendee existing Booking to Google Calendar
	public function removeAttendeeGoogleCalender($meta_data_id, $booking_calendar_value, $data){
		
		 
		// Set the Access Token
		$this->refreshToken( $data->host_id );
		$events = $booking_calendar_value->google_calendar;

		$google_calendar_body = array();
		foreach ( $events as $event ) {
			$event_id = $event->id;
			
			$attendees = $event->attendees;
			// if attendee email is not found then return false
			$attendee_email = array_filter(
				$attendees,
				function ( $attendee ) use ( $data ) {
					return $attendee->email == $data->email;
				}
			);

			if ( count( $attendee_email ) == 0 ) {
				 continue;
			}

			foreach($attendees as $key => $attendee){
				if($attendee->email == $data->email){
					unset($attendees[$key]);
				}
			}
			
			$event->attendees = $attendees;

			$_tfhb_host_integration_settings = is_array( get_user_meta( $hostData->user_id, '_tfhb_host_integration_settings', true ) ) ? get_user_meta( $hostData->user_id, '_tfhb_host_integration_settings', true ) : array();
			$google_calendar                 = isset( $_tfhb_host_integration_settings['google_calendar'] ) ? $_tfhb_host_integration_settings['google_calendar'] : array();
			$calendarId                      = isset( $google_calendar['selected_calendar_id'] ) && !empty($google_calendar['selected_calendar_id']) ? $google_calendar['selected_calendar_id'] : $google_calendar['tfhb_google_calendar']['email'];
   

			if ( $calendarId ) {

				$url      = $this->calendarEvent . $calendarId . '/events/' . $event_id .'?sendUpdates=all';
				$response = wp_remote_post(
					$url,
					array(
						'headers'     => array( 'Authorization' => 'Bearer ' . $this->accessToken ),
						'body'        => wp_json_encode( $event ),
						'method'      => 'PUT',
						'data_format' => 'body',
					)
				);
				$body     = wp_remote_retrieve_body( $response );

				$google_calendar_body[] = json_decode( $body, true );

			}

			
		} 
	
		$value = array(
			'google_calendar' => $google_calendar_body,
		);
		$BookingMeta = new BookingMeta(); 
		// Update the Booking meta
		$booking_meta = array(
			'id'    => $meta_data_id,
			'value' =>  wp_json_encode( $value, true ),
		);  
		$BookingMeta->update( $booking_meta );
		 
 
		// update Booking location 

		$booking = new Booking();

		$bookingGet = $booking->get( $data->id );
		$meeting_loaction  = json_decode($bookingGet->meeting_locations);
		$meeting_loaction->meet->address = 'Cancelled';
		$meeting_loaction = (array) $meeting_loaction;
		$meeting_locations = is_array($meeting_loaction) ? $meeting_loaction  : wp_json_decode($meeting_loaction);

		$update                     = array();
		$update['id']               = $data->id;
		$update['meeting_locations'] = $meeting_locations;

		$booking->update( $update );

		return true; 
	}

	/**
	 * Update Google Calendar edit existing Booking to Google Calendar
	 *
	 * @param $data
	 * @param $booking
	 */

	public function UpdateGoogleCalender( $old_booking_id, $data ) {

		if($this->checkConnectionStatus() == false){
			return;
		}

		// Get Meeting Calendar Data
		$meeting = new Meeting();
		$booking = new Booking();
		$bookingMeta = new BookingMeta();
		$meetingData = $meeting->get( $data->meeting_id );
		$meeting_locations = json_decode( $meetingData->meeting_locations, true ); // [{"location":"meet","address":""}]

		//  if !in array location value is meet then  return false 
		$meeting_location = array_filter(
			$meeting_locations,
			function ( $location ) {
				return $location['location'] == 'meet';
			}
		);
		if ( count( $meeting_location ) == 0 ) {
			return false;
		}

		if('one-to-group' == $meetingData->meeting_type){

			$max_book_per_slot = isset( $meetingData->max_book_per_slot ) ? $meetingData->max_book_per_slot : 1;
			
			$check_booking = $booking->getCheckBooking( $data->meeting_id, $data->meeting_dates, $data->start_time, $data->end_time );
			
			
			
			// unset if check_booking has current booking data->id without loop and array maps or filter
			$check_booking = array_filter(
				$check_booking,
				function ( $data ) use ( $sdata ) {
					return $data->id !== $sdata->id;
				}
			); 
	

			// insert first booking
			if(count($check_booking) != 0){  
				$booking_ids = array_map(
					function ( $booking ) {
						return $booking->id;
					},
					$check_booking
				);

				
				if(empty($data->meeting_calendar) ){
					return false;
				}
				
			
 
				$booking_calendar           = $bookingMeta->get( $data->meeting_calendar );
		
				if ( $booking_calendar ) { 
					 
				 
					 $this->rescheduleGoogleCalender($booking_calendar, $data);
					return;

				}
			}
		}
		
		$bookingMeta = new BookingMeta(); 
		$booking_calendarData = $bookingMeta->getWithIdKey( $data->id, 'booking_calendar', 1 );


		// if booking calendar data is not found then return false 
		$this->rescheduleGoogleCalender($booking_calendarData, $data);
	}

	public function rescheduleGoogleCalender($booking_calendarData, $data){
		
		if ( ! $booking_calendarData ) {
			return false;
		} 

		// Set the Access Token
		$this->refreshToken( $data->host_id );

		$bookingMeta = new BookingMeta();
		$value = json_decode($booking_calendarData->value);

		$events = $value->google_calendar;

		$_tfhb_host_integration_settings = is_array( get_user_meta( $hostData->user_id, '_tfhb_host_integration_settings', true ) ) ? get_user_meta( $hostData->user_id, '_tfhb_host_integration_settings', true ) : array();
		$google_calendar                 = isset( $_tfhb_host_integration_settings['google_calendar'] ) ? $_tfhb_host_integration_settings['google_calendar'] : array();
		$calendarId                      = isset( $google_calendar['selected_calendar_id'] ) && !empty($google_calendar['selected_calendar_id']) ? $google_calendar['selected_calendar_id'] : $google_calendar['tfhb_google_calendar']['email'];
   

		$google_calendar_body = array();
		$bookingDates = explode(',', $data->meeting_dates);
		foreach ($events as $key => $event){
			$event_id = $event->id;

			// update event time date and time zone everyting based on reshedule details
			$start_time    = strtotime( $data->start_time ); // 03:45 AM
			$end_time      = strtotime( $data->end_time ); // 04:30 AM
			$meeting_dates = $bookingDates[$key]; // 2024-07-10,2024-07-17,2024-07-24,2024-07-31
 
			$start_date = gmdate( 'Y-m-d', strtotime( $meeting_dates ) ) . 'T' . gmdate( 'H:i:s', $start_time );
			$end_date   = gmdate( 'Y-m-d', strtotime( $meeting_dates ) ) . 'T' . gmdate( 'H:i:s', $end_time );

			$event->start = array(
				'dateTime' => $start_date,
				'timeZone' => $data->attendee_time_zone,
			);
			$event->end = array(
				'dateTime' => $end_date,
				'timeZone' => $data->attendee_time_zone,
			);

			// change attendee email
			$attendees = $event->attendees;
			foreach($attendees as $key => $attendee){
				if($attendee->email == $data->email){
					$attendees[$key]->email = $data->email;
				}
			}


			
			if ( $calendarId ) {
	
				$url      = $this->calendarEvent . $calendarId . '/events/' . $event_id .'?sendUpdates=all';
				$response = wp_remote_post(
					$url,
					array(
						'headers'     => array( 'Authorization' => 'Bearer ' . $this->accessToken ),
						'body'        => wp_json_encode( $event ),
						'method'      => 'PUT',
						'data_format' => 'body',
					)
				);
				$body     = wp_remote_retrieve_body( $response );

				$google_calendar_body[] = json_decode( $body, true );
	
			}
		}
 
		// update booking calendar data
		$value = array(
			'google_calendar' => $google_calendar_body,
		);
 

		$Update = $bookingMeta->update( array('id' => $booking_calendarData->id, 'value' => $value) );

		if ( $Update ) {
			return true;
		} else {
			return false;
		}

	}
}
