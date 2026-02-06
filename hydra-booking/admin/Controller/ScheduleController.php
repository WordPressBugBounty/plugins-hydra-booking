<?php
namespace HydraBooking\Admin\Controller;

// exit
if ( ! defined( 'ABSPATH' ) ) { exit; }


use HydraBooking\DB\Booking;
use HydraBooking\DB\Attendees;
use HydraBooking\Admin\Controller\DateTimeController;

class ScheduleController {

	// constaract
	public function __construct() {
		$this->tfhb_create_cron_job();

		add_action( 'tfhb_after_booking_completed_schedule', array( $this, 'tfhb_after_booking_completed_schedule_callback' ) );

		add_action( 'tfhb_cart_auto_remover_schedule', array( $this, 'tfhb_cart_auto_remover_schedule_callback' ) );
		
	}
	public function tfhb_create_cron_job() {

		add_filter( 'cron_schedules', array( $this, 'tfhb_custom_cron_job_schedule' ) );
		// Schedule Cron Job Before Booking Completed specific time
		if ( ! wp_next_scheduled( 'tfhb_after_booking_completed_schedule' ) ) {
			// Create custom interverl for 60 minutes
			wp_schedule_event( time(), 'tfhb_after_booking_status', 'tfhb_after_booking_completed_schedule' );
		}

		add_filter( 'cron_schedules', array( $this, 'tfhb_custom_cart_cron_job_schedule' ) );
		// Schedule Cron Job Before Booking Completed specific time
		if ( ! wp_next_scheduled( 'tfhb_cart_auto_remover_schedule' ) ) {
			// Create custom interverl for 60 minutes
			wp_schedule_event( time(), 'tfhb_cart_remover', 'tfhb_cart_auto_remover_schedule' );
		}
	}

	public function tfhb_custom_cron_job_schedule( $schedules ) {

		$general_settings = get_option( '_tfhb_general_settings', true ) ? get_option( '_tfhb_general_settings', true ) : array();
		$every_minute     = ! empty( $general_settings['after_booking_completed'] ) ? $general_settings['after_booking_completed'] : 60; // minutes

		$every_minute = $every_minute * 60;

		if ( isset( $schedules['tfhb_after_booking_status'] ) ) {
			// Remove the old schedule

			unset( $schedules['tfhb_after_booking_status'] );
			// Convert into seconds

			$schedules['tfhb_after_booking_status'] = array(
				'interval' => $every_minute,
				'display'  => __( 'Hydra After booking Completed', 'hydra-booking' ),
			);
		} else {

			$schedules['tfhb_after_booking_status'] = array(
				'interval' => $every_minute,
				'display'  => __( 'Hydra After booking Completed', 'hydra-booking' ),
			);
		}

		return $schedules;
	}

	function tfhb_after_booking_completed_schedule_callback() {

		$general_settings        = get_option( '_tfhb_general_settings', true ) ? get_option( '_tfhb_general_settings', true ) : array();


		
		$after_booking_completed = isset( $general_settings['after_booking_completed'] ) ? $general_settings['after_booking_completed'] : 60; // minutes
		// Get all bookings current date before 60 minutes of Current Time and status is confirmed
		$time = gmdate( 'H:i:s', strtotime( '-' . $after_booking_completed . ' minutes', strtotime( gmdate( 'H:i:s' ) ) ) );
		// meeting_dates 2024-06-06
		$meeting_dates = gmdate( 'Y-m-d', strtotime( gmdate( 'Y-m-d' ) ) );
		$booking       = new Booking();
		 
		$where = array(
			array('status', '=', 'confirmed'), 
			array('meeting_dates', '=', $meeting_dates),
		);
		 $bookings = $booking->getBookingWithAttendees(  
			$where,
			 null,
			'DESC',
		); 
	
		foreach ( $bookings as $key => $value ) {
		
			if(empty($value->availability_time_zone)){ 
				continue;
			}
			$DateTime = new DateTimeController( $value->availability_time_zone );
					// Time format if has AM and PM into start time
			$time_format              = strpos( $value->start_time, 'AM' ) || strpos( $value->start_time, 'PM' ) ? '12' : '24';
			$before_booking_completed = $DateTime->convert_time_based_on_timezone( '', gmdate( 'Y-m-d H:i:s', strtotime( '-' . $after_booking_completed . ' minutes' ) ), 'UTC', $value->availability_time_zone, $time_format );
 

			if ( $value->end_time < $before_booking_completed ) {

				$booking_meta            = get_post_meta( $value->id, '_tfhb_booking_opt', true );
				$$booking_meta['status'] = 'completed';
				update_post_meta( $value->id, '_tfhb_booking_opt', $booking_meta );
				$update = array(
					'id'     => $value->id,
					'status' => 'completed',
				);
				$booking->update( $update );
			}
		}

		return true;
	}

	public function tfhb_custom_cart_cron_job_schedule( $schedules ) {

		$general_settings = get_option( '_tfhb_general_settings', true ) ? get_option( '_tfhb_general_settings', true ) : array();
		$every_minute     = ! empty( $general_settings['after_cart_expire'] ) ? $general_settings['after_cart_expire'] : 60; // minutes

		$every_minute = $every_minute * 60;

		if ( isset( $schedules['tfhb_cart_remover'] ) ) {
			// Remove the old schedule

			unset( $schedules['tfhb_cart_remover'] );
			// Convert into seconds

			$schedules['tfhb_cart_remover'] = array(
				'interval' => $every_minute,
				'display'  => __( 'Hydra Cart Remover after Expire', 'hydra-booking' ),
			);
		} else {

			$schedules['tfhb_cart_remover'] = array(
				'interval' => $every_minute,
				'display'  => __( 'Hydra Cart Remover after Expire', 'hydra-booking' ),
			);
		}

		return $schedules;
	}
	function tfhb_cart_auto_remover_schedule_callback() {

		// Get all WooCommerce sessions
		global $wpdb;
		$table = $wpdb->prefix . 'woocommerce_sessions';
		$sessions = $wpdb->get_results("SELECT session_key, session_value FROM $table");
	
		$general_settings = get_option( '_tfhb_general_settings', true ) ? get_option( '_tfhb_general_settings', true ) : array();
		$every_minute     = ! empty( $general_settings['after_cart_expire'] ) ? $general_settings['after_cart_expire'] : 60; // minutes

		$every_minute = intval($every_minute * 60);

		foreach ($sessions as $session) {
			$session_data = maybe_unserialize($session->session_value);
	
			if (! empty($session_data['cart'])) {
				$cart = maybe_unserialize($session_data['cart']);

				foreach ($cart as $key => $item) {
					$added_time = !empty($item['tfhb_order_meta']['added_time']) ? intval($item['tfhb_order_meta']['added_time']) : 0;
					if ( !empty($added_time) && (time() - $added_time) > $every_minute ) {

						// Update Booking
						if( !empty($item['tfhb_order_meta']['booking_id']) ){
							$booking = new Booking();
							$booking_id = $item['tfhb_order_meta']['booking_id'];
							$updat_booking['id'] = $booking_id;
							$updat_booking['status'] = 'canceled';
							$booking->update( $updat_booking );
						}

						// Attendees update
						if( !empty($item['tfhb_order_meta']['attendee_id']) ){
							$Attendees = new Attendees();
							$attendee_id = $item['tfhb_order_meta']['attendee_id'];
							$updat_attendee['id'] = $attendee_id;
							$updat_attendee['status'] = 'canceled';
							$Attendees->update( $updat_attendee );
						}
	
						unset($cart[$key]);
					}
				}
	
				// Save updated session
				$session_data['cart'] = $cart;
				$wpdb->update(
					$table,
					[ 'session_value' => maybe_serialize($session_data) ],
					[ 'session_key' => $session->session_key ]
				);
			}
		}

	}

	// Update Booking Cron Job
	public function tfhb_after_booking_completed_schedule_update() {
		// Make custom Shedule intervel
		add_filter( 'cron_schedules', array( $this, 'tfhb_custom_cron_job_schedule' ) );

		wp_clear_scheduled_hook( 'tfhb_after_booking_completed_schedule' );
		// Create custom interverl for 60 minutes

		wp_schedule_event( time(), 'tfhb_after_booking_status', 'tfhb_after_booking_completed_schedule' );


		// Make custom Shedule intervel
		add_filter( 'cron_schedules', array( $this, 'tfhb_custom_cart_cron_job_schedule' ) );

		wp_clear_scheduled_hook( 'tfhb_cart_auto_remover_schedule' );
		// Create custom interverl for 60 minutes

		wp_schedule_event( time(), 'tfhb_cart_remover', 'tfhb_cart_auto_remover_schedule' );
	}
}
