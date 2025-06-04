<?php
namespace HydraBooking\Services\Integrations\Telegram;

use HydraBooking\DB\Meeting;
use HydraBooking\DB\Attendees;
use HydraBooking\DB\Host;
use HydraBooking\DB\BookingMeta;
use HydraBooking\Admin\Controller\DateTimeController;

class Telegram {

	public function __construct( ) {
		add_action( 'hydra_booking/after_booking_confirmed', array( $this, 'pushBookingToConfirmed' ), 20, 1 );
		add_action( 'hydra_booking/after_booking_canceled', array( $this, 'pushBookingToCanceled' ), 20, 1 );
		add_action( 'hydra_booking/after_booking_schedule', array( $this, 'pushBookingToscheduled' ), 20, 2 );
	}

    // Get Meeting Data
	public function getMeetingData( $meeting_id ) {
		$meeting      = new Meeting();
		$meeting_data = $meeting->get( $meeting_id );
		return get_post_meta( $meeting_data->post_id, '__tfhb_meeting_opt', true );
	}

	// Get Host Data
	public function getHostData( $host_id ) {
		$host      = new Host();
		$host_data = $host->getHostById(  $host_id );
		return $host_data;
	}

	// If booking Status is Complted
	public function pushBookingToConfirmed( $attendees ) {

		$Meeting_meta                = $this->getMeetingData( $attendees->meeting_id );
		$_tfhb_notification_settings = ! empty( $Meeting_meta['notification'] ) ? $Meeting_meta['notification'] : '';
		if ( ! empty( $_tfhb_notification_settings ) ) {
			if(!empty($_tfhb_notification_settings['telegram']['booking_confirmation']['status']) && !empty($_tfhb_notification_settings['telegram']['booking_confirmation']['body'])){
				$telegram_data = $this->tfhb_telegram_callback($_tfhb_notification_settings['telegram']['booking_confirmation']['body'], $attendees);
			}
		}
       
	}

	// If booking Status is Cancel
	public function pushBookingToCanceled( $attendees ) {

		$Meeting_meta                = $this->getMeetingData( $attendees->meeting_id );
		$_tfhb_notification_settings = ! empty( $Meeting_meta['notification'] ) ? $Meeting_meta['notification'] : '';

		if ( ! empty( $_tfhb_notification_settings ) ) {
			if(!empty($_tfhb_notification_settings['telegram']['booking_cancel']['status']) && !empty($_tfhb_notification_settings['telegram']['booking_cancel']['body'])){
				$telegram_data = $this->tfhb_telegram_callback($_tfhb_notification_settings['telegram']['booking_cancel']['body'], $attendees);
			}
		}

	}

	// If booking Status is ReSchedule
	public function pushBookingToscheduled( $old_booking_id, $attendees ) {
		
		$Meeting_meta                = $this->getMeetingData( $attendees->meeting_id );
		$_tfhb_notification_settings = ! empty( $Meeting_meta['notification'] ) ? $Meeting_meta['notification'] : '';

		if ( ! empty( $_tfhb_notification_settings ) ) {
			if(!empty($_tfhb_notification_settings['telegram']['booking_reschedule']['status']) && !empty($_tfhb_notification_settings['telegram']['booking_reschedule']['body'])){
				$telegram_data = $this->tfhb_telegram_callback($_tfhb_notification_settings['telegram']['booking_reschedule']['body'], $attendees);
			}
		}

	}

    function tfhb_telegram_callback($body, $attendees) {

		$_tfhb_host_integration_settings = is_array( get_user_meta( $attendees->host_id, '_tfhb_host_integration_settings', true ) ) ? get_user_meta( $attendees->host_id, '_tfhb_host_integration_settings', true ) : array();
        $_tfhb_integration_settings = !empty(get_option( '_tfhb_integration_settings' )) && get_option( '_tfhb_integration_settings' ) != false ? get_option( '_tfhb_integration_settings' ) : array();
        
		if(!empty($_tfhb_host_integration_settings['telegram']) && !empty($_tfhb_host_integration_settings['telegram']['status']) && !empty($_tfhb_host_integration_settings['telegram']['bot_token'])){
			$telegram_status = !empty($_tfhb_host_integration_settings['telegram']['status']) ? $_tfhb_host_integration_settings['telegram']['status'] : '';
			$telegram_bot_token = !empty($_tfhb_host_integration_settings['telegram']['bot_token']) ? $_tfhb_host_integration_settings['telegram']['bot_token'] : '';
			$telegram_chat_id = !empty($_tfhb_host_integration_settings['telegram']['chat_id']) ? $_tfhb_host_integration_settings['telegram']['chat_id'] : '';
		}else{
			$telegram_status = !empty($_tfhb_integration_settings['telegram']['status']) ? $_tfhb_integration_settings['telegram']['status'] : '';
			$telegram_bot_token = !empty($_tfhb_integration_settings['telegram']['bot_token']) ? $_tfhb_integration_settings['telegram']['bot_token'] : '';
			$telegram_chat_id = !empty($_tfhb_integration_settings['telegram']['chat_id']) ? $_tfhb_integration_settings['telegram']['chat_id'] : '';
		}

        if(!empty($telegram_status) && !empty($telegram_bot_token) && !empty($telegram_chat_id)){

            $mailbody = $this->replace_mail_tags($body, $attendees->id);
			$html = preg_replace('/<\s*(br|hr)\s*\/?>/i', "\n", $mailbody);
			$html = preg_replace('/<\/?(p|div|h[1-6])[^>]*>/i', "\n", $html);
			$text = strip_tags($html);
			$text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5);
			$lines = array_filter(array_map('trim', explode("\n", $text)));
			$mailbody = implode("\n", $lines);
			
			$mailbody = $this->escape_markdown_v2($mailbody);

            $api_url = "https://api.telegram.org/bot$telegram_bot_token/sendMessage";
			$args = array(
				'chat_id' => $telegram_chat_id,
				'text' => $mailbody,
                'parse_mode' => 'MarkdownV2'
			);

			$response = wp_remote_post( $api_url, array(
				'body' => json_encode( $args ),
				'headers' => array( 'Content-Type' => 'application/json' ),
			) );

            return $response;


			if ( is_wp_error( $response ) ) {
				error_log( 'Telegram API request failed: ' . $response->get_error_message() );
			}
        }
    }

	// Escape MarkdownV2 reserved characters
	public function escape_markdown_v2($text) {
		$special_chars = ['_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!'];
		foreach ($special_chars as $char) {
			$text = str_replace($char, '\\' . $char, $text);
		}
		return $text;
	}
    /**
	 * Replace all available mail tags
	 */
	public function replace_mail_tags( $template, $attendee_id ) {
		
		$Attendee = new Attendees();
		$attendeeBooking =  $Attendee->getAttendeeWithBooking( 
			array(
				array('id', '=',$attendee_id),
			),
			1,
			'DESC'
		); 
		 
		// Meeting Location Check
		$meeting_locations =  !is_array($attendeeBooking->meeting_locations) ?  json_decode( $attendeeBooking->meeting_locations ) : $attendeeBooking->meeting_locations;
		$locations         = array();
		
		if ( is_array( $meeting_locations ) ) {
			foreach ( $meeting_locations as $location ) {
				if ( isset( $location->location ) ) {
					$locations[] = $location->location . (!empty($location->address) ? ' - ' . $location->address : '');
				}
			}
		}

		if ( is_object($meeting_locations) ) {
			foreach ( $meeting_locations as $key => $locationObj ) {
				if ( isset($locationObj->location) ) {
					$locations[] = $locationObj->location . (!empty($locationObj->address) ? ' - ' . $locationObj->address : '');
				}
			}
		}

		$replacements = array(
			'{{meeting.title}}'    => ! empty( $attendeeBooking->meeting_title ) ? $attendeeBooking->meeting_title : '',
			'{{meeting.content}}'    => ! empty( $attendeeBooking->meeting_content ) ? $attendeeBooking->meeting_content : '',
			'{{meeting.date}}'     => ! empty( $attendeeBooking->meeting_dates ) ? $attendeeBooking->meeting_dates : '',
			'{{meeting.location}}' => implode( ', ', $locations ),
			'{{meeting.duration}}' => $attendeeBooking->duration,
			'{{meeting.time}}'     => $attendeeBooking->start_time . '-' . $attendeeBooking->end_time,
			'{{host.name}}'        => $attendeeBooking->host_first_name . ' ' . $attendeeBooking->host_last_name,
			'{{host.email}}'       => ! empty( $attendeeBooking->host_email ) ? $attendeeBooking->host_email : '',
			'{{host.phone}}'       => ! empty( $attendeeBooking->host_phone ) ? $attendeeBooking->host_phone : '',
			'{{attendee.name}}'    => ! empty( $attendeeBooking->attendee_name ) ? $attendeeBooking->attendee_name : '',
			'{{attendee.email}}'   => ! empty( $attendeeBooking->attendee_email ) ? $attendeeBooking->attendee_email : '', 

		);
		
		// Additional Data
		if( !empty($attendeeBooking->others_info) && $attendeeBooking->others_info != NULL ){
			$additional_data = json_decode($attendeeBooking->others_info);
			$others_info_html = '<ul>';
			foreach ($additional_data as $key => $value) {
				$others_info_html .= '<li>'.$key.' : '.$value.'</li>'; 
			}
			$others_info_html .= '</ul>';
			$replacements['{{attendee.additional_data}}'] = $others_info_html;
		}
		 
		// reason
		if( !empty($attendeeBooking->reason) && $attendeeBooking->reason != NULL ){
			$replacements['{{booking.cancel_reason}}'] = $attendeeBooking->reason;
			$replacements['{{booking.rescheduled_reason}}'] = $attendeeBooking->reason;
		}
		
		
		if($attendeeBooking->attendee_can_cancel == 1){ 
		
			$cancel_link = home_url( '?hydra-booking=booking&hash=' . $attendeeBooking->hash . '&meetingId=' . $attendeeBooking->meeting_id  . '&type=cancel' );
			$replacements['{{booking.cancel_link}}'] = $cancel_link;
		}
		if( $attendeeBooking->attendee_can_cancel == 1){ 
			$rescheduled_link = home_url( '?hydra-booking=booking&hash=' . $attendeeBooking->hash . '&meetingId=' . $attendeeBooking->meeting_id . '&type=reschedule' );
			$replacements['{{booking.rescheduled_link}}'] = $rescheduled_link;
		}
		// Full start end time with timezone for attendee 
		$replacements['{{booking.full_start_end_attendee_timezone}}'] = $attendeeBooking->start_time.' - '.$attendeeBooking->end_time.' ('.$attendeeBooking->attendee_time_zone.')';
		$replacements['{{booking.start_date_time_for_attendee}}'] = $attendeeBooking->start_time. ' ('.$attendeeBooking->attendee_time_zone.')';
		
	
		// Full start end time with timezone for host
		$dateTime = new DateTimeController( 'UTC' );
		$metting_dates = explode(',', $attendeeBooking->meeting_dates);
		if($attendeeBooking->availability_time_zone != ''){
			$full_start_end_host_timezone = $dateTime->convert_full_start_end_host_timezone_with_date( $attendeeBooking->start_time, $attendeeBooking->end_time, $attendeeBooking->attendee_time_zone, $attendeeBooking->availability_time_zone,  $metting_dates[0], 'full' );  
			$replacements['{{booking.full_start_end_host_timezone}}'] = $full_start_end_host_timezone;

			$start_date_time_for_host = $dateTime->convert_full_start_end_host_timezone_with_date( $attendeeBooking->start_time, $attendeeBooking->end_time, $attendeeBooking->attendee_time_zone, $attendeeBooking->availability_time_zone,  $metting_dates[0], 'start' );
			$replacements['{{booking.start_date_time_for_host}}'] =  $start_date_time_for_host;
		}else{
			$replacements['{{booking.full_start_end_host_timezone}}'] = $attendeeBooking->start_time.' - '.$attendeeBooking->end_time.' ('.$attendeeBooking->attendee_time_zone.')';

			$replacements['{{booking.start_date_time_for_host}}'] = $attendeeBooking->start_time. ' ('.$attendeeBooking->attendee_time_zone.')';
		}
 
		if( !empty($attendeeBooking->meeting_locations) && $attendeeBooking->meeting_locations != NULL  ){
			$booking_locations = json_decode($attendeeBooking->meeting_locations); 
			
			$booking_locations_html = '';
			foreach ($booking_locations as $key => $value) { 
				if($key == 'zoom'){
					$link = $value->address->link;
					$password = $value->address->password;  
					$booking_locations_html .= '<b>'.$value->location.' :</b> <a href="'.esc_url($link).'" target="_blank">Join Meeting</a> <br> <b>Password :</b> '.esc_html($password).'<br>';
				}else{
					$booking_locations_html .= '<b>'.$value->location.' :</b> '.$value->address.'<br>'; 
				}
			}

			$replacements['{{booking.location_details_html}}'] = $booking_locations_html;
		}  
		$tags   = array_keys( $replacements );
		$values = array_values( $replacements ); 
		return str_replace( $tags, $values, $template );
	}

}
