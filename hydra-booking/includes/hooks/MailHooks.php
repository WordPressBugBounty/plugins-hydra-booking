<?php
namespace HydraBooking\Hooks;

// Use
use HydraBooking\DB\Meeting;
use HydraBooking\DB\Attendees;
use HydraBooking\DB\Host;
use HydraBooking\DB\BookingMeta;
use HydraBooking\Admin\Controller\DateTimeController; 


class MailHooks {
	// Approved
	// Pending
	// Re-schedule
	// Canceled
 
	public function __construct() {
		add_action( 'hydra_booking/after_booking_confirmed', array( $this, 'pushBookingToConfirmed' ), 20, 1 ); 
		add_action( 'hydra_booking/after_booking_pending', array( $this, 'pushBookingToPending' ), 20, 1 );
		add_action( 'hydra_booking/after_booking_canceled', array( $this, 'pushBookingToCanceled' ), 20, 1 );
		add_action( 'hydra_booking/after_booking_schedule', array( $this, 'pushBookingToscheduled' ), 20, 2 );
		add_action( 'hydra_booking/send_booking_reminder', array( $this, 'send_booking_reminder' ), 20, 1 );

		// Send Mail Booking with All attendees
		add_action( 'hydra_booking/send_booking_with_all_attendees_confirmed', array( $this, 'send_booking_with_all_attendees_confirmed' ), 10, 1 );

		// Send Mail Booking with All attendees Pending
		add_action( 'hydra_booking/send_booking_with_all_attendees_pending', array( $this, 'send_booking_with_all_attendees_pending' ), 10, 1 );

		// Send Mail Booking with All attendees Canceled
		add_action( 'hydra_booking/send_booking_with_all_attendees_canceled', array( $this, 'send_booking_with_all_attendees_canceled' ), 10, 1 );

		// Send Mail Booking with All attendees Schedule
		add_action( 'hydra_booking/send_booking_with_all_attendees_schedule', array( $this, 'send_booking_with_all_attendees_schedule' ), 10, 1 );
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
		$bookingMeta                 = new BookingMeta();
		$Meeting_meta                = $this->getMeetingData( $attendees->meeting_id );
		$_tfhb_notification_settings = ! empty( $Meeting_meta['notification'] ) ? $Meeting_meta['notification'] : '';
		$hostData                    = $this->getHostData( $attendees->host_id );  

		
		if ( ! empty( $_tfhb_notification_settings ) ) {

			// Host Confirmation Email, If Settings Enable for Host Confirmation
			if ( ! empty( $_tfhb_notification_settings['host']['booking_confirmation']['status'] ) ) {
				
				
				// From Email
				$replyTo = ! empty( $_tfhb_notification_settings['host']['booking_confirmation']['form'] ) ? $_tfhb_notification_settings['host']['booking_confirmation']['form'] : get_option( 'admin_email' );

				// Email Subject
				$subject = ! empty( $_tfhb_notification_settings['host']['booking_confirmation']['subject'] ) ? $_tfhb_notification_settings['host']['booking_confirmation']['subject'] : 'Booking Confirmation';

				// Replace Shortcode to Values
				$subject = $this->replace_mail_tags( $subject, $attendees->id );
				
				
				// Setting Body
				$mailbody = ! empty( $_tfhb_notification_settings['host']['booking_confirmation']['body'] ) ? $_tfhb_notification_settings['host']['booking_confirmation']['body'] : ''; 
 	 
				// Replace Shortcode to Values
				$finalbody = $this->replace_mail_tags( $mailbody, $attendees->id );
		 
			
				// Result after Shortcode replce
				$body = wp_kses_post( $this->email_body_open() . $finalbody . $this->email_body_close() );


				// Host Email
				$mailto = ! empty( $hostData->email ) ? $hostData->email : '';

				$headers = array(
					'Reply-To: ' . $replyTo,
				);

				
				Mailer::send( $mailto, $subject, $body, $headers );

				// Add activity after email sent
				$bookingMeta->add([
					'booking_id' => $attendees->booking_id,
					'meta_key' => 'booking_activity',
					'value' => array( 
							'datetime' => date('M d, Y, h:i A'), 
							'title' => 'Confirmation Email Sent', // translate it from Vue
							'description' => 'Confirmation Email Sent to Host',  // translate it from Vue
						)
					]
				);
			}

			// Attendee Confirmation Email, If Settings Enable for Attendee Confirmation
			if ( ! empty( $_tfhb_notification_settings['attendee']['booking_confirmation']['status'] ) ) {
				
				
				// From Email
				$replyTo = ! empty( $_tfhb_notification_settings['attendee']['booking_confirmation']['form'] ) ? $_tfhb_notification_settings['attendee']['booking_confirmation']['form'] : get_option( 'admin_email' );

				// Email Subject
				$subject = ! empty( $_tfhb_notification_settings['attendee']['booking_confirmation']['subject'] ) ? $_tfhb_notification_settings['attendee']['booking_confirmation']['subject'] : 'Booking Confirmation';

				// Replace Shortcode to Values
				$subject = $this->replace_mail_tags( $subject, $attendees->id );
				
				
				// Setting Body
				$mailbody = ! empty( $_tfhb_notification_settings['attendee']['booking_confirmation']['body'] ) ? $_tfhb_notification_settings['attendee']['booking_confirmation']['body'] : ''; 

				
				// Replace Shortcode to Values
				$finalbody = $this->replace_mail_tags( $mailbody, $attendees->id );
			
				// Result after Shortcode replce
				$body = wp_kses_post( $this->email_body_open() . $finalbody . $this->email_body_close() );
 
				// Host Email
				$mailto = ! empty( $attendees->email ) ? $attendees->email : '';

				$headers = array(
					'Reply-To: ' . $replyTo,
				);

				
				Mailer::send( $mailto, $subject, $body, $headers );


				// Add activity after email sent
				$bookingMeta->add([
					'booking_id' => $attendees->booking_id,
					'meta_key' => 'booking_activity',
					'value' => array(
							 
							'datetime' => date('M d, Y, h:i A'), 
							'title' =>  'Confirmation Email Sent', // translate it from Vue
							'description' => 'Confirmation Email Sent to Attendee', // translate it from Vue
						)
					]
				);
			}
		}
	}


	// If booking Status is Pending
	public function pushBookingToPending( $attendees ) {

		$bookingMeta                 = new BookingMeta();
		$Meeting_meta                = $this->getMeetingData( $attendees->meeting_id );
		$_tfhb_notification_settings = ! empty( $Meeting_meta['notification'] ) ? $Meeting_meta['notification'] : '';
		$hostData                    = $this->getHostData( $attendees->host_id );  

		if ( ! empty( $_tfhb_notification_settings ) ) {

			// Host Pending Email, If Settings Enable for Host Pending
			if ( ! empty( $_tfhb_notification_settings['host']['booking_pending']['status'] ) ) {
				
				// From Email
				$replyTo = ! empty( $_tfhb_notification_settings['host']['booking_pending']['form'] ) ? $_tfhb_notification_settings['host']['booking_pending']['form'] : get_option( 'admin_email' );

				// Email Subject
				$subject = ! empty( $_tfhb_notification_settings['host']['booking_pending']['subject'] ) ? $_tfhb_notification_settings['host']['booking_pending']['subject'] : 'Booking Pending';

				// Replace Shortcode to Values
				$subject = $this->replace_mail_tags( $subject, $attendees->id );
				
				// Setting Body
				$mailbody = ! empty( $_tfhb_notification_settings['host']['booking_pending']['body'] ) ? $_tfhb_notification_settings['host']['booking_pending']['body'] : '';

				// Replace Shortcode to Values
				$finalbody = $this->replace_mail_tags( $mailbody, $attendees->id );

				// Result after Shortcode replce
				$body = wp_kses_post( $this->email_body_open() . $finalbody . $this->email_body_close() );

				// Host Email
				$mailto = ! empty( $hostData->email ) ? $hostData->email : '';

				$headers = array(
					'Reply-To: ' . $replyTo,
				);

				Mailer::send( $mailto, $subject, $body, $headers );

				// Add activity after email sent
				$bookingMeta->add([
					'booking_id' => $attendees->booking_id,
					'meta_key' => 'booking_activity',
					'value' => array(
							 
							'datetime' => date('M d, Y, h:i A'),  
							'title' => 'Pending Email Sent', // translate it from Vue
							'description' => 'Pending Email Sent to Host', // translate it from Vue
						)
					]
				);
			}

			// Attendee Pending Email, If Settings Enable for Attendee Pending
			if ( ! empty( $_tfhb_notification_settings['attendee']['booking_pending']['status'] ) ) {
				// From Email
				$replyTo = ! empty( $_tfhb_notification_settings['attendee']['booking_pending']['form'] ) ? $_tfhb_notification_settings['attendee']['booking_pending']['form'] : get_option( 'admin_email' );

				// Email Subject
				$subject = ! empty( $_tfhb_notification_settings['attendee']['booking_pending']['subject'] ) ? $_tfhb_notification_settings['attendee']['booking_pending']['subject'] : 'Booking Pending';

				// Replace Shortcode to Values
				$subject = $this->replace_mail_tags( $subject, $attendees->id );


				// Setting Body
				$mailbody = ! empty( $_tfhb_notification_settings['attendee']['booking_pending']['body'] ) ? $_tfhb_notification_settings['attendee']['booking_pending']['body'] : '';

				// Replace Shortcode to Values
				$finalbody = $this->replace_mail_tags( $mailbody, $attendees->id );

				// Result after Shortcode replce
				$body = wp_kses_post( $this->email_body_open() . $finalbody . $this->email_body_close() );

				// Attendee Email
				$mailto = ! empty( $attendees->email ) ? $attendees->email : '';

				$headers = array(
					'Reply-To: ' . $replyTo,
				);

				Mailer::send( $mailto, $subject, $body, $headers );

				// Add activity after email sent
				$bookingMeta->add([
					'booking_id' => $attendees->booking_id,
					'meta_key' => 'booking_activity',
					'value' => array(
							 
							'datetime' => date('M d, Y, h:i A'),   
							'title' => 'Pending Email Sent', // translate it from Vue
							'description' => 'Pending Email Sent to Attendee', // translate it from Vue
						)
					]
				);
			}
		}
	}

	// If booking Status is Cancel
	public function pushBookingToCanceled( $attendees ) {
		$bookingMeta                 = new BookingMeta();
		$Meeting_meta                = $this->getMeetingData( $attendees->meeting_id );
		$_tfhb_notification_settings = ! empty( $Meeting_meta['notification'] ) ? $Meeting_meta['notification'] : '';
		$hostData                    = $this->getHostData( $attendees->host_id );

		
		if ( ! empty( $_tfhb_notification_settings ) ) {

			// Host Canceled Email, If Settings Enable for Host Canceled
			if ( ! empty( $_tfhb_notification_settings['host']['booking_cancel']['status'] ) ) {

				// From Email
				$replyTo = ! empty( $_tfhb_notification_settings['host']['booking_cancel']['form'] ) ? $_tfhb_notification_settings['host']['booking_cancel']['form'] : get_option( 'admin_email' );

				// Email Subject
				$subject = ! empty( $_tfhb_notification_settings['host']['booking_cancel']['subject'] ) ? $_tfhb_notification_settings['host']['booking_cancel']['subject'] : 'Booking Canceled';

				// Replace Shortcode to Values
				$subject = $this->replace_mail_tags( $subject, $attendees->id );

				// Setting Body
				$mailbody = ! empty( $_tfhb_notification_settings['host']['booking_cancel']['body'] ) ? $_tfhb_notification_settings['host']['booking_cancel']['body'] : '';

				// Replace Shortcode to Values
				$finalbody = $this->replace_mail_tags( $mailbody, $attendees->id );

				// Result after Shortcode replce
				$body = wp_kses_post( $this->email_body_open() . $finalbody . $this->email_body_close() );

				// Host Email
				$mailto = ! empty( $hostData->email ) ? $hostData->email : '';

				$headers = array(
					'Reply-To: ' . $replyTo,
				);
				Mailer::send( $mailto, $subject, $body, $headers );

				// Add activity after email sent
				$bookingMeta->add([
					'booking_id' => $attendees->booking_id,
					'meta_key' => 'booking_activity',
					'value' => array(
							 
							'datetime' => date('M d, Y, h:i A'),    
							'title' => 'Canceled Email Sent', // translate it from Vue
							'description' => 'Canceled Email Sent to Host', // translate it from Vue
						)
					]
				);
			}

			// Attendee Canceled Email, If Settings Enable for Attendee Canceled
			if ( ! empty( $_tfhb_notification_settings['attendee']['booking_cancel']['status'] ) ) {
				// From Email
				$replyTo = ! empty( $_tfhb_notification_settings['attendee']['booking_cancel']['form'] ) ? $_tfhb_notification_settings['attendee']['booking_cancel']['form'] : get_option( 'admin_email' );

				// Email Subject
				$subject = ! empty( $_tfhb_notification_settings['attendee']['booking_cancel']['subject'] ) ? $_tfhb_notification_settings['attendee']['booking_cancel']['subject'] : 'Booking Canceled';

				// Replace Shortcode to Values
				$subject = $this->replace_mail_tags( $subject, $attendees->id );

				// Setting Body
				$mailbody = ! empty( $_tfhb_notification_settings['attendee']['booking_cancel']['body'] ) ? $_tfhb_notification_settings['attendee']['booking_cancel']['body'] : '';

				// Replace Shortcode to Values
				$finalbody = $this->replace_mail_tags( $mailbody, $attendees->id );

				// Result after Shortcode replce
				$body = wp_kses_post( $this->email_body_open() . $finalbody . $this->email_body_close() );

				// Attendee Email
				$mailto = ! empty( $attendees->email ) ? $attendees->email : '';

				$headers = array(
					'Reply-To: ' . $replyTo,
				);

				Mailer::send( $mailto, $subject, $body, $headers );

				// Add activity after email sent
				$bookingMeta->add([
					'booking_id' => $attendees->booking_id,
					'meta_key' => 'booking_activity',
					'value' => array(
							 
							'datetime' => date('M d, Y, h:i A'),
							'title' => 'Canceled Email Sent', // translate it from Vue
							'description' => 'Canceled Email Sent to Attendee', // translate it from Vue
						)
					]
				);
			}
		}
	}

	// If booking Status is ReSchedule
	public function pushBookingToscheduled( $old_booking_id,  $attendees ) { 
		 
		$bookingMeta                 = new BookingMeta();
		$Meeting_meta                = $this->getMeetingData( $attendees->meeting_id );
		$_tfhb_notification_settings = ! empty( $Meeting_meta['notification'] ) ? $Meeting_meta['notification'] : '';
		$hostData                    = $this->getHostData( $attendees->host_id );

		if ( ! empty( $_tfhb_notification_settings ) ) {

			// Host ReSchedule Email, If Settings Enable for Host ReSchedule
			if ( ! empty( $_tfhb_notification_settings['host']['booking_reschedule']['status'] ) ) {
				// From Email
				$replyTo = ! empty( $_tfhb_notification_settings['host']['booking_reschedule']['form'] ) ? $_tfhb_notification_settings['host']['booking_reschedule']['form'] : get_option( 'admin_email' );
 
				// Email Subject
				$subject = ! empty( $_tfhb_notification_settings['host']['booking_reschedule']['subject'] ) ? $_tfhb_notification_settings['host']['booking_reschedule']['subject'] : 'Booking ReSchedule';
				
				// Replace Shortcode to Values
				$subject = $this->replace_mail_tags( $subject, $attendees->id );


				// Setting Body
				$mailbody = ! empty( $_tfhb_notification_settings['host']['booking_reschedule']['body'] ) ? $_tfhb_notification_settings['host']['booking_reschedule']['body'] : '';

				// Replace Shortcode to Values
				$finalbody = $this->replace_mail_tags( $mailbody, $attendees->id );

				// Result after Shortcode replce
				$body = wp_kses_post( $this->email_body_open() . $finalbody . $this->email_body_close() );

				// Host Email
				$mailto = ! empty( $hostData->host_email ) ? $hostData->host_email : '';

				$headers = array(
					'Reply-To: ' . $replyTo,
				);

				Mailer::send( $mailto, $subject, $body, $headers );

				// Add activity after email sent
				$bookingMeta->add([
					'booking_id' => $attendees->booking_id,
					'meta_key' => 'booking_activity',
					'value' => array( 
							'datetime' => date('M d, Y, h:i A'),
							'title' => 'ReSchedule Email Sent', // translate it from Vue
							'description' => 'ReSchedule Email Sent to Host', // translate it from Vue
						)
					]
				);
			}

			// Attendee ReSchedule Email, If Settings Enable for Attendee ReSchedule
			if ( ! empty( $_tfhb_notification_settings['attendee']['booking_reschedule']['status'] ) ) {
				// From Email
				$replyTo = ! empty( $_tfhb_notification_settings['attendee']['booking_reschedule']['form'] ) ? $_tfhb_notification_settings['attendee']['booking_reschedule']['form'] : get_option( 'admin_email' );

				// Email Subject
				$subject = ! empty( $_tfhb_notification_settings['attendee']['booking_reschedule']['subject'] ) ? $_tfhb_notification_settings['attendee']['booking_reschedule']['subject'] : 'Booking ReSchedule';

				// Replace Shortcode to Values
				$subject = $this->replace_mail_tags( $subject, $attendees->id );

				// Setting Body
				$mailbody = ! empty( $_tfhb_notification_settings['attendee']['booking_reschedule']['body'] ) ? $_tfhb_notification_settings['attendee']['booking_reschedule']['body'] : '';

				// Replace Shortcode to Values
				$finalbody = $this->replace_mail_tags( $mailbody, $attendees->id );

				// Result after Shortcode replce
				$body = wp_kses_post( $this->email_body_open() . $finalbody . $this->email_body_close() );

				// Attendee Email
				$mailto = ! empty( $attendees->email ) ? $attendees->email : '';

				$headers = array(
					'Reply-To: ' . $replyTo,
				);

				Mailer::send( $mailto, $subject, $body, $headers );

				// Add activity after email sent
				$bookingMeta->add([
					'booking_id' => $attendees->booking_id,
					'meta_key' => 'booking_activity',
					'value' => array( 
							'datetime' => date('M d, Y, h:i A'),
							'title' => 'ReSchedule Email Sent', // translate it from Vue
							'description' => 'ReSchedule Email Sent to Attendee', // translate it from Vue
						)
					]
				);
			}
		}
	}


		// If booking Status is ReSchedule
	public function send_booking_reminder( $booking ) {
		$bookingMeta                 = new BookingMeta();
		$Meeting_meta                = $this->getMeetingData( $booking->meeting_id );
		$_tfhb_notification_settings = ! empty( $Meeting_meta['notification'] ) ? $Meeting_meta['notification'] : '';
		$hostData                    = $this->getHostData( $booking->host_id );


		if ( ! empty( $_tfhb_notification_settings ) ) {
			$attendees = $booking->attendees;
	 
			// Attendee ReSchedule Email, If Settings Enable for Attendee ReSchedule
			if ( ! empty( $_tfhb_notification_settings['attendee']['booking_reminder']['status'] ) ) {
				foreach($attendees as $key => $attendee_data){
					// From Email
					$replyTo = ! empty( $_tfhb_notification_settings['attendee']['booking_reminder']['form'] ) ? $_tfhb_notification_settings['attendee']['booking_reminder']['form'] : get_option( 'admin_email' );

					// Email Subject
					$subject = ! empty( $_tfhb_notification_settings['attendee']['booking_reminder']['subject'] ) ? $_tfhb_notification_settings['attendee']['booking_reminder']['subject'] : 'Booking ReSchedule';

					// Replace Shortcode to Values
					$subject = $this->replace_mail_tags( $subject, $attendee_data->id );

					// Setting Body
					$mailbody = ! empty( $_tfhb_notification_settings['attendee']['booking_reminder']['body'] ) ? $_tfhb_notification_settings['attendee']['booking_reminder']['body'] : '';

					// Replace Shortcode to Values
					$finalbody = $this->replace_mail_tags( $mailbody, $attendee_data->id );

					// Result after Shortcode replce
					$body = wp_kses_post( $this->email_body_open() . $finalbody . $this->email_body_close() );

					// Attendee Email
					$mailto = ! empty( $attendee_data->email ) ? $attendee_data->email : ''; 
					$headers = array(
						'Reply-To: ' . $replyTo,
					);

					Mailer::send( $mailto, $subject, $body, $headers );

						// Add activity after email sent
					$bookingMeta->add([
						'booking_id' => $attendees->booking_id,
						'meta_key' => 'booking_activity',
						'value' => array( 
								'datetime' => date('M d, Y, h:i A'), 
								'title' => 'Reminder Email Sent', // translate it from Vue
								'description' => 'Reminder Email Sent to Attendee', // translate it from Vue
							)
						]
					);
				}
			}
		}
	}

	/**
	 * Send Mail Booking with All attendees
	 */
	public function send_booking_with_all_attendees_confirmed( $booking ) { 
		$bookingMeta                 = new BookingMeta();
		$Meeting_meta                = $this->getMeetingData( $booking->meeting_id );
		$_tfhb_notification_settings = ! empty( $Meeting_meta['notification'] ) ? $Meeting_meta['notification'] : '';
		$hostData                    = $this->getHostData( $booking->host_id );
		 
		$attendees = $booking->attendees;
 
		if ( ! empty( $_tfhb_notification_settings ) ) { 
			// Attendee ReSchedule Email, If Settings Enable for Attendee ReSchedule
			if ( ! empty( $_tfhb_notification_settings['attendee']['booking_confirmation']['status'] ) ) {
				foreach($attendees as $key => $attendee_data){
					// From Email
					$replyTo = ! empty( $_tfhb_notification_settings['attendee']['booking_confirmation']['form'] ) ? $_tfhb_notification_settings['attendee']['booking_confirmation']['form'] : get_option( 'admin_email' );

					// Email Subject
					$subject = ! empty( $_tfhb_notification_settings['attendee']['booking_confirmation']['subject'] ) ? $_tfhb_notification_settings['attendee']['booking_confirmation']['subject'] : 'Booking ReSchedule';

					// Replace Shortcode to Values
					$subject = $this->replace_mail_tags( $subject, $attendee_data->id );

					// Setting Body
					$mailbody = ! empty( $_tfhb_notification_settings['attendee']['booking_confirmation']['body'] ) ? $_tfhb_notification_settings['attendee']['booking_confirmation']['body'] : '';

					// Replace Shortcode to Values
					$finalbody = $this->replace_mail_tags( $mailbody, $attendee_data->id );

					// Result after Shortcode replce
					$body = wp_kses_post( $this->email_body_open() . $finalbody . $this->email_body_close() );

					// Attendee Email
					$mailto = ! empty( $attendee_data->email ) ? $attendee_data->email : ''; 
					$headers = array(
						'Reply-To: ' . $replyTo,
					);
 
					Mailer::send( $mailto, $subject, $body, $headers );

					
				}
				// Add activity after email sent
				$bookingMeta->add([
					'booking_id' => $booking->id,
					'meta_key' => 'booking_activity',
					'value' => array( 
							'datetime' => date('M d, Y, h:i A'),  
							'title' => 'Booking Has Been Confirmed', // translate it from Vue
							'description' => 'Confirmation Email Sent to Attendee', // translate it from Vue
						)
					]
				);
			}
		}
	}
	/**
	 * Send Mail Booking with All attendees Pending
	 */
	public function send_booking_with_all_attendees_pending( $booking ) {
		$bookingMeta                 = new BookingMeta();
		$Meeting_meta                = $this->getMeetingData( $booking->meeting_id );
		$_tfhb_notification_settings = ! empty( $Meeting_meta['notification'] ) ? $Meeting_meta['notification'] : '';
		$hostData                    = $this->getHostData( $booking->host_id );
		 
		$attendees = $booking->attendees; 

		if ( ! empty( $_tfhb_notification_settings ) ) { 
			// Attendee ReSchedule Email, If Settings Enable for Attendee ReSchedule
			if ( ! empty( $_tfhb_notification_settings['attendee']['booking_pending']['status'] ) ) {
				foreach($attendees as $key => $attendee_data){
					// From Email
					$replyTo = ! empty( $_tfhb_notification_settings['attendee']['booking_pending']['form'] ) ? $_tfhb_notification_settings['attendee']['booking_pending']['form'] : get_option( 'admin_email' );

					// Email Subject
					$subject = ! empty( $_tfhb_notification_settings['attendee']['booking_pending']['subject'] ) ? $_tfhb_notification_settings['attendee']['booking_pending']['subject'] : 'Booking ReSchedule';

					// Replace Shortcode to Values
					$subject = $this->replace_mail_tags( $subject, $attendee_data->id );

					// Setting Body
					$mailbody = ! empty( $_tfhb_notification_settings['attendee']['booking_pending']['body'] ) ? $_tfhb_notification_settings['attendee']['booking_pending']['body'] : '';

					// Replace Shortcode to Values
					$finalbody = $this->replace_mail_tags( $mailbody, $attendee_data->id );

					// Result after Shortcode replce
					$body = wp_kses_post( $this->email_body_open() . $finalbody . $this->email_body_close() );

					// Attendee Email
					$mailto = ! empty( $attendee_data->email ) ? $attendee_data->email : ''; 
					$headers = array(
						'Reply-To: ' . $replyTo,
					);
 
					Mailer::send( $mailto, $subject, $body, $headers );

				
				}
				// Add activity after email sent
				$bookingMeta->add([
					'booking_id' => $booking->id,
					'meta_key' => 'booking_activity',
					'value' => array( 
							'datetime' => date('M d, Y, h:i A'),   
							'title' => 'Booking Has Been Pending', // translate it from Vue
							'description' => 'Pending Email Sent to Attendee', // translate it from Vue
						)
					]
				);
			
			}
		}
	}
	/**
	 * Send Mail Booking with All attendees Canceled
	 */
	public function send_booking_with_all_attendees_canceled( $booking ) {
		$bookingMeta                 = new BookingMeta();
		$Meeting_meta                = $this->getMeetingData( $booking->meeting_id );
		$_tfhb_notification_settings = ! empty( $Meeting_meta['notification'] ) ? $Meeting_meta['notification'] : '';
		$hostData                    = $this->getHostData( $booking->host_id );
		 
		$attendees = $booking->attendees;
 
		if ( ! empty( $_tfhb_notification_settings ) ) { 
			// Attendee ReSchedule Email, If Settings Enable for Attendee ReSchedule
			if ( ! empty( $_tfhb_notification_settings['attendee']['booking_cancel']['status'] ) ) {
				foreach($attendees as $key => $attendee_data){
					// From Email
					$replyTo = ! empty( $_tfhb_notification_settings['attendee']['booking_cancel']['form'] ) ? $_tfhb_notification_settings['attendee']['booking_cancel']['form'] : get_option( 'admin_email' );

					// Email Subject
					$subject = ! empty( $_tfhb_notification_settings['attendee']['booking_cancel']['subject'] ) ? $_tfhb_notification_settings['attendee']['booking_cancel']['subject'] : 'Booking ReSchedule';

					// Replace Shortcode to Values
					$subject = $this->replace_mail_tags( $subject, $attendee_data->id );

					// Setting Body
					$mailbody = ! empty( $_tfhb_notification_settings['attendee']['booking_cancel']['body'] ) ? $_tfhb_notification_settings['attendee']['booking_cancel']['body'] : '';

					// Replace Shortcode to Values
					$finalbody = $this->replace_mail_tags( $mailbody, $attendee_data->id );
				
					// Result after Shortcode replce
					$body = wp_kses_post( $this->email_body_open() . $finalbody . $this->email_body_close() );

					// Attendee Email
					$mailto = ! empty( $attendee_data->email ) ? $attendee_data->email : ''; 
					$headers = array(
						'Reply-To: ' . $replyTo,
					);
 
					Mailer::send( $mailto, $subject, $body, $headers );

					
				}
				// Add activity after email sent
				$bookingMeta->add([
					'booking_id' => $booking->id,
					'meta_key' => 'booking_activity',
					'value' => array( 
							'datetime' => date('M d, Y, h:i A'),    
							'title' => 'Booking Has Been Canceled', // translate it from Vue
							'description' => 'Canceled Email Sent to Attendee', // translate it from Vue
						)
					]
				);
				
			}
		}
	}

	/**
	 * Send Mail Booking with All attendees Schedule
	 */
	public function send_booking_with_all_attendees_schedule( $booking ) {
		$bookingMeta                 = new BookingMeta();
		$Meeting_meta                = $this->getMeetingData( $booking->meeting_id );
		$_tfhb_notification_settings = ! empty( $Meeting_meta['notification'] ) ? $Meeting_meta['notification'] : '';
		$hostData                    = $this->getHostData( $booking->host_id );
		 
		$attendees = $booking->attendees;
 

		if ( ! empty( $_tfhb_notification_settings ) ) { 
			// Attendee ReSchedule Email, If Settings Enable for Attendee ReSchedule
			if ( ! empty( $_tfhb_notification_settings['attendee']['booking_reschedule']['status'] ) ) {
				foreach($attendees as $key => $attendee_data){
					// From Email
					$replyTo = ! empty( $_tfhb_notification_settings['attendee']['booking_reschedule']['form'] ) ? $_tfhb_notification_settings['attendee']['booking_reschedule']['form'] : get_option( 'admin_email' );

					// Email Subject
					$subject = ! empty( $_tfhb_notification_settings['attendee']['booking_reschedule']['subject'] ) ? $_tfhb_notification_settings['attendee']['booking_reschedule']['subject'] : 'Booking ReSchedule';

					// Replace Shortcode to Values
					$subject = $this->replace_mail_tags( $subject, $attendee_data->id );

					// Setting Body
					$mailbody = ! empty( $_tfhb_notification_settings['attendee']['booking_reschedule']['body'] ) ? $_tfhb_notification_settings['attendee']['booking_reschedule']['body'] : '';

					// Replace Shortcode to Values
					$finalbody = $this->replace_mail_tags( $mailbody, $attendee_data->id );

					// Result after Shortcode replce
					$body = wp_kses_post( $this->email_body_open() . $finalbody . $this->email_body_close() );

					// Attendee Email
					$mailto = ! empty( $attendee_data->email ) ? $attendee_data->email : ''; 
					$headers = array(
						'Reply-To: ' . $replyTo,
					);
 
					Mailer::send( $mailto, $subject, $body, $headers );

					
				}

				// Add activity after email sent
				$bookingMeta->add([
					'booking_id' => $booking->id,
					'meta_key' => 'booking_activity',
					'value' => array( 
							'datetime' => date('M d, Y, h:i A'),     
							'title' => 'Booking Has Been Rescheduled', // translate it from Vue
							'description' => 'ReSchedule Email Sent to Attendee', // translate it from Vue
						)
					]
				);
				
			}
		}
	}

	/**
	 * email body open markup
	 */
	public function email_body_open() {
		// email body open
		$email_body_open = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1"><link rel="preconnect" href="https://fonts.googleapis.com"></head><body style="margin: 0; padding: 0; background-color: #E1F2E4;">';
		return $email_body_open;
	}

	/**
	 * email body close markup
	 */
	public function email_body_close() {
		// email body close
		$email_body_close = '</body></html>';
		return $email_body_close;
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


		$google_calendar_link  = '#';
		$outlook_calendar_link = '#';
		$yahoo_calendar_link   = '#';
		$other_calendar_link   = '#';

		// Query-arg based add-to-calendar links
		if ( ! empty( $attendeeBooking->hash ) && ! empty( $attendeeBooking->meeting_id ) ) {
			$google_base_hash = [
				'attendee_id' => $attendeeBooking->id,
				'type' => 'google', // This can be used to identify the calendar type in the add-to-calendar handler
			];  
			$google_base_hash = base64_encode( wp_json_encode( $google_base_hash ) );  
			$google_calendar_link  =home_url( '?hydra-add-to-calendar=' . $google_base_hash );

			// outlook base hash
			$outlook_base_hash = [
				'attendee_id' => $attendeeBooking->id,
				'type' => 'outlook', // This can be used to identify the calendar type in the add-to-calendar handler
			];
			$outlook_base_hash = base64_encode( wp_json_encode( $outlook_base_hash ) );
			$outlook_calendar_link = home_url( '?hydra-add-to-calendar=' . $outlook_base_hash );

			// yahoo base hash
			$yahoo_base_hash = [
				'attendee_id' => $attendeeBooking->id,
				'type' => 'yahoo', // This can be used to identify the calendar type in the add-to-calendar handler
			];
			$yahoo_base_hash = base64_encode( wp_json_encode( $yahoo_base_hash ) );
			$yahoo_calendar_link = home_url( '?hydra-add-to-calendar=' . $yahoo_base_hash );
			 
			// other calendar base hash
			$other_base_hash = [
				'attendee_id' => $attendeeBooking->id,
				'type' => 'other', // This can be used to identify the calendar type in the add-to-calendar handler
			];
			$other_base_hash = base64_encode( wp_json_encode( $other_base_hash ) );
			$other_calendar_link = home_url( '?hydra-add-to-calendar=' . $other_base_hash );
		}
 
  
			// $google_calendar_link = 'https://calendar.google.com/calendar/r/eventedit?dates=20260223T122000/20260223T130000&text=Maiores aut sed rati Between admin  and Lillith Mccarty&details';
			// $outlook_calendar_link = '#';
			// $yahoo_calendar_link = '#';
			// $other_calendar_link = '#';
		 
		// Meeting Location Check
		$meeting_locations =  !is_array($attendeeBooking->meeting_locations) ?  json_decode( $attendeeBooking->meeting_locations ) : $attendeeBooking->meeting_locations;
		$locations         = array();
		if ( is_array( $meeting_locations ) ) {
			foreach ( $meeting_locations as $location ) {
				if ( isset( $location->location ) ) {
					$locations[] = $location->location;
				}
			}
		}
		 

		$replacements = array(
			'{{meeting.title}}'    => ! empty( $attendeeBooking->meeting_title ) ? $attendeeBooking->meeting_title : '',
			'{{meeting.content}}'  => ! empty( $attendeeBooking->meeting_content ) ? $attendeeBooking->meeting_content : '',
			'{{meeting.date}}'     => ! empty( $attendeeBooking->meeting_dates ) ? $attendeeBooking->meeting_dates : '',
			'{{meeting.location}}' => implode( ', ', $locations ),
			'{{meeting.duration}}' => $attendeeBooking->duration,
			'{{meeting.time}}'     => $attendeeBooking->start_time . '-' . $attendeeBooking->end_time,
			'{{host.name}}'        => $attendeeBooking->host_first_name . ' ' . $attendeeBooking->host_last_name,
			'{{host.email}}'       => ! empty( $attendeeBooking->host_email ) ? $attendeeBooking->host_email : '',
			'{{host.phone}}'       => ! empty( $attendeeBooking->host_phone ) ? $attendeeBooking->host_phone : '',
			'{{attendee.name}}'    => ! empty( $attendeeBooking->attendee_name ) ? $attendeeBooking->attendee_name : '',
			'{{attendee.email}}'   => ! empty( $attendeeBooking->email ) ? $attendeeBooking->email : '', 
			'{{booking.add_to_calendar.google}}'   => ! empty( $google_calendar_link ) ? htmlspecialchars($google_calendar_link, ENT_QUOTES, 'UTF-8') : '#', 
			'{{booking.add_to_calendar.outlook}}'   => ! empty( $outlook_calendar_link ) ? htmlspecialchars($outlook_calendar_link, ENT_QUOTES, 'UTF-8') : '#', 
			'{{booking.add_to_calendar.yahoo}}'   => ! empty( $yahoo_calendar_link ) ? htmlspecialchars($yahoo_calendar_link, ENT_QUOTES, 'UTF-8') : '#', 
			'{{booking.add_to_calendar.other}}'   => ! empty( $other_calendar_link ) ? htmlspecialchars($other_calendar_link, ENT_QUOTES, 'UTF-8') : '#',
	
		);
		// tfhb_print_r($replacements);
		
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
		if( $attendeeBooking->attendee_can_reschedule == 1){ 
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
		$template = str_replace( $tags, $values, $template );

		if ($attendeeBooking->attendee_can_cancel != 1 && $attendeeBooking->attendee_can_reschedule != 1) {
			libxml_use_internal_errors(true); // Suppress warnings for invalid HTML
			$dom = new \DOMDocument();
			$dom->loadHTML(mb_convert_encoding($template, 'HTML-ENTITIES', 'UTF-8'));

			$xpath = new \DOMXPath($dom);
			foreach ($xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' tfhb-cancel-reschedule-btn ')]") as $node) {
				$node->parentNode->removeChild($node);
			}

			$template = $dom->saveHTML($dom->getElementsByTagName('body')->item(0));
			$template = preg_replace('/^<body>|<\/body>$/', '', $template); // Strip body tags
		} elseif ($attendeeBooking->attendee_can_cancel == 1 && $attendeeBooking->attendee_can_reschedule != 1) {
			// Remove only cancel button
			libxml_use_internal_errors(true);
			$dom = new \DOMDocument();
			$dom->loadHTML(mb_convert_encoding($template, 'HTML-ENTITIES', 'UTF-8'));

			$xpath = new \DOMXPath($dom);
			foreach ($xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' tfhb-reschedule-btn ')]") as $node) {
				$node->parentNode->removeChild($node);
			}

			$template = $dom->saveHTML($dom->getElementsByTagName('body')->item(0));
			$template = preg_replace('/^<body>|<\/body>$/', '', $template);
		} elseif ($attendeeBooking->attendee_can_cancel != 1 && $attendeeBooking->attendee_can_reschedule == 1) {
			// Remove only cancel button
			libxml_use_internal_errors(true);
			$dom = new \DOMDocument();
			$dom->loadHTML(mb_convert_encoding($template, 'HTML-ENTITIES', 'UTF-8'));

			$xpath = new \DOMXPath($dom);
			foreach ($xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' tfhb-cancel-btn ')]") as $node) {
				$node->parentNode->removeChild($node);
			}

			$template = $dom->saveHTML($dom->getElementsByTagName('body')->item(0));
			$template = preg_replace('/^<body>|<\/body>$/', '', $template);
		}

		return $template;

	}
}
