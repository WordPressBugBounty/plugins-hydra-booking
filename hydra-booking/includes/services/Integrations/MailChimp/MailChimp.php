<?php
namespace HydraBooking\Services\Integrations\MailChimp;

use HydraBooking\DB\Meeting;
class MailChimp {

	public function __construct() {
		add_action( 'hydra_booking/after_booking_completed', array( $this, 'integrationsBookingToCompleted' ), 10, 1 );
		add_action( 'hydra_booking/after_booking_canceled', array( $this, 'integrationsBookingToCanceled' ), 10, 1 );
		add_action( 'hydra_booking/after_booking_confirmed', array( $this, 'integrationsBookingToConfirmed' ), 10, 1 );
	}

	// If booking Completed
	public function integrationsBookingToCompleted( $attendee ) {

		// Get Meeting
		$meeting     = new Meeting();
		$MeetingData = $meeting->get( $attendee->meeting_id );

		$integrationsdata = ! empty( $MeetingData->integrations ) ? json_decode( $MeetingData->integrations, true ) : array();
	
		if ( ! empty( $integrationsdata ) ) {
			foreach ( $integrationsdata as $hook ) {
				// integrations
				if ( ! empty( $hook['webhook'] ) && 'Mailchimp' == $hook['webhook'] && ! empty( $hook['events'] ) && in_array( 'Booking Completed', $hook['events'] ) && ! empty( $hook['status'] ) ) {
					$this->tfhb_mailchimp_callback( $attendee, $hook, $MeetingData->host_id );
				}
			}
		}
	}

	// If booking Cancel
	public function integrationsBookingToCanceled( $attendee ) {

		// Get Meeting
		$meeting     = new Meeting();
		$MeetingData = $meeting->get( $attendee->meeting_id );

		$integrationsdata = ! empty( $MeetingData->integrations ) ? json_decode( $MeetingData->integrations, true ) : array();
	
		if ( ! empty( $integrationsdata ) ) {
			foreach ( $integrationsdata as $hook ) {
				// integrations
				if ( ! empty( $hook['webhook'] ) && 'Mailchimp' == $hook['webhook'] && ! empty( $hook['events'] ) && in_array( 'Booking Canceled', $hook['events'] ) && ! empty( $hook['status'] ) ) {
					$this->tfhb_mailchimp_callback( $attendee, $hook, $MeetingData->host_id );
				}
			}
		}
	}

	// If booking confirmed
	public function integrationsBookingToConfirmed( $attendee ) {
		
		// Get Meeting
		$meeting     = new Meeting();
		$MeetingData = $meeting->get( $attendee->meeting_id );

		$integrationsdata = ! empty( $MeetingData->integrations ) ? json_decode( $MeetingData->integrations, true ) : array();
	
		if ( ! empty( $integrationsdata ) ) {
			foreach ( $integrationsdata as $hook ) {
				// integrations
				if ( ! empty( $hook['webhook'] ) && 'Mailchimp' == $hook['webhook'] && ! empty( $hook['events'] ) && in_array( 'Booking Confirmed', $hook['events'] ) && ! empty( $hook['status'] ) ) {
					$this->tfhb_mailchimp_callback( $attendee, $hook, $MeetingData->host_id );
				}
			}
		}
	}

	// Mailchimp Callback
	function tfhb_mailchimp_callback( $attendee, $hook, $host ) {
	
	
		$_tfhb_host_integration_settings = ! empty( $host ) ? get_user_meta( $host, '_tfhb_host_integration_settings', true ) : '';

		$_tfhb_integration_settings = get_option( '_tfhb_integration_settings' );
		$api_key                    = ! empty( $_tfhb_integration_settings['mailchimp']['key'] ) ? $_tfhb_integration_settings['mailchimp']['key'] : '';
		$api_key                    = ! empty( $_tfhb_host_integration_settings['mailchimp']['key'] ) ? $_tfhb_host_integration_settings['mailchimp']['key'] : $api_key;

		if ( $api_key != '' ) {

			$response = $this->set_config( $api_key, 'ping' );
			
			$response = json_decode( $response );
			if ( isset( $response->health_status ) ) { // Display success message
				$response = $this->add_members( $api_key, $attendee, $hook );
			} else {
				$this->mailchimlConnection = false;
			}
		}

		// Admin Option
		$body_request = isset( $hook['bodys'] ) ? $hook['bodys'] : '';

		// Define the data to send in the POST request
		$header_data = array();
		$body_data   = array();
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

	/* Add members to mailchimp */
	public function add_members( $api_key, $attendee, $hook ) {
		
		$subscriber_email = ! empty( $attendee->email ) ? $attendee->email : '';

		if ( $api_key != '' && $subscriber_email != '' ) {
			$server_prefix    = explode( '-', $api_key );
			$server_prefix    = $server_prefix[1];
			$subscriber_fname = ! empty( $attendee->attendee_name ) ? $attendee->attendee_name : '';
			$subscriber_lname = ! empty( $attendee->attendee_last_name ) ? $attendee->attendee_last_name : '';

			$extra_fields = ! empty( $hook['bodys'] ) ? $hook['bodys'] : array();

			$extra_merge_fields = '';
			foreach ( $extra_fields as $extra_field ) {
				$field_name          = $extra_field['type'];
				if($field_name!='tfhb_ct'){
					$field_value         = isset( $attendee->$field_name ) ? $attendee->$field_name : ''; // Check if the property exists
				}else{
					$field_value = $extra_field['value'];
				}
				if(!empty($field_value)){
					$extra_merge_fields .= '"' . $extra_field['name'] . '": "' . $field_value . '",';
				}
			}
			$extra_merge_fields = trim( $extra_merge_fields, ',' );

			if ( $extra_merge_fields != '' ) {
				$extra_merge_fields = ',' . $extra_merge_fields;
			}

			 
			$url = "https://$server_prefix.api.mailchimp.com/3.0/lists/" . $hook['audience'] . '/members';

			$headers = array(
				'Authorization' => 'Bearer ' . $api_key,
				'Content-Type'  => 'application/json',
			);

			// Mailchimp data
			$data = array(
				'email_address' => sanitize_email($subscriber_email),
				'status'        => 'subscribed',
				'merge_fields'  => array(
					'FNAME' => sanitize_text_field($subscriber_fname),
					'LNAME' => sanitize_text_field($subscriber_lname),
				),
				'vip'      => false,
				'location' => array(
					'latitude'  => 0,
					'longitude' => 0,
				),
			);

			// Include any additional merge fields if applicable
			if (!empty($extra_merge_fields)) {
				$extra_fields = json_decode($extra_merge_fields, true);
				if (is_array($extra_fields)) {
					$data['merge_fields'] = array_merge($data['merge_fields'], $extra_fields);
				}
			}

			$args = array(
				'headers' => $headers,
				'body'    => wp_json_encode($data),
				'method'  => 'POST',
				'timeout' => 45,
			);

			// Disable SSL verification for debugging (not recommended for production)
			add_filter('https_ssl_verify', '__return_false');
			$response = wp_remote_post($url, $args);
			$response_body = wp_remote_retrieve_body($response); 
			// json_decode the response
			$response_body = json_decode($response_body);
 
			if (isset($response_body->status) && !$response_body->status == 400) { 
				return true;
			} else {
				return false;
			}
		}
	}
}
