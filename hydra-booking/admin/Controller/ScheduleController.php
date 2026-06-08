<?php

namespace HydraBooking\Admin\Controller;

// exit
if (! defined('ABSPATH')) {
	exit;
}


use HydraBooking\DB\Booking;
use HydraBooking\DB\Attendees;
use HydraBooking\DB\Host;
use HydraBooking\Admin\Controller\DateTimeController;
use HydraBooking\Services\Integrations\GoogleCalendar\GoogleCalendar;

class ScheduleController
{

	// constaract
	public function __construct()
	{
		$this->tfhb_create_cron_job();

		add_action('tfhb_after_booking_completed_schedule', array($this, 'tfhb_after_booking_completed_schedule_callback'));

		add_action('tfhb_cart_auto_remover_schedule', array($this, 'tfhb_cart_auto_remover_schedule_callback'));

		add_action('tfhb_calendar_sync_schedule', array($this, 'tfhb_calendar_sync_schedule_callback'), 10, 1);
	}
	public function tfhb_create_cron_job()
	{

		add_filter('cron_schedules', array($this, 'tfhb_custom_cron_job_schedule'));
		// Schedule Cron Job Before Booking Completed specific time
		if (! wp_next_scheduled('tfhb_after_booking_completed_schedule')) {
			// Create custom interverl for 60 minutes
			wp_schedule_event(time(), 'tfhb_after_booking_status', 'tfhb_after_booking_completed_schedule');
		}

		add_filter('cron_schedules', array($this, 'tfhb_custom_cart_cron_job_schedule'));
		// Schedule Cron Job Before Booking Completed specific time
		if (! wp_next_scheduled('tfhb_cart_auto_remover_schedule')) {
			// Create custom interverl for 60 minutes
			wp_schedule_event(time(), 'tfhb_cart_remover', 'tfhb_cart_auto_remover_schedule');
		}

		add_filter('cron_schedules', array($this, 'tfhb_google_calendar_cron_schedule'));
		if (! wp_next_scheduled('tfhb_calendar_sync_schedule')) {
			wp_schedule_event(time(), 'tfhb_calendar_sync_status', 'tfhb_calendar_sync_schedule');
		}
	}

	public function tfhb_custom_cron_job_schedule($schedules)
	{

		$general_settings = get_option('_tfhb_general_settings', true) ? get_option('_tfhb_general_settings', true) : array();
		$every_minute     = ! empty($general_settings['after_booking_completed']) ? $general_settings['after_booking_completed'] : 60; // minutes

		$every_minute = $every_minute * 60;

		if (isset($schedules['tfhb_after_booking_status'])) {
			// Remove the old schedule

			unset($schedules['tfhb_after_booking_status']);
			// Convert into seconds

			$schedules['tfhb_after_booking_status'] = array(
				'interval' => $every_minute,
				'display'  => __('Hydra After booking Completed', 'hydra-booking'),
			);
		} else {

			$schedules['tfhb_after_booking_status'] = array(
				'interval' => $every_minute,
				'display'  => __('Hydra After booking Completed', 'hydra-booking'),
			);
		}

		return $schedules;
	}

	function tfhb_after_booking_completed_schedule_callback()
	{

		$general_settings        = get_option('_tfhb_general_settings', true) ? get_option('_tfhb_general_settings', true) : array();



		$after_booking_completed = isset($general_settings['after_booking_completed']) ? $general_settings['after_booking_completed'] : 60; // minutes
		// Get all bookings current date before 60 minutes of Current Time and status is confirmed
		$time = gmdate('H:i:s', strtotime('-' . $after_booking_completed . ' minutes', strtotime(gmdate('H:i:s'))));
		// meeting_dates 2024-06-06
		$meeting_dates = gmdate('Y-m-d', strtotime(gmdate('Y-m-d')));
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

		foreach ($bookings as $key => $value) {

			if (empty($value->availability_time_zone)) {
				continue;
			}
			$DateTime = new DateTimeController($value->availability_time_zone);
			// Time format if has AM and PM into start time
			$time_format              = strpos($value->start_time, 'AM') || strpos($value->start_time, 'PM') ? '12' : '24';
			$before_booking_completed = $DateTime->convert_time_based_on_timezone('', gmdate('Y-m-d H:i:s', strtotime('-' . $after_booking_completed . ' minutes')), 'UTC', $value->availability_time_zone, $time_format);


			if ($value->end_time < $before_booking_completed) {

				$booking_meta            = get_post_meta($value->id, '_tfhb_booking_opt', true);
				$$booking_meta['status'] = 'completed';
				update_post_meta($value->id, '_tfhb_booking_opt', $booking_meta);
				$update = array(
					'id'     => $value->id,
					'status' => 'completed',
				);
				$booking->update($update);
			}
		}

		return true;
	}

	public function tfhb_custom_cart_cron_job_schedule($schedules)
	{

		$general_settings = get_option('_tfhb_general_settings', true) ? get_option('_tfhb_general_settings', true) : array();
		$every_minute     = ! empty($general_settings['after_cart_expire']) ? $general_settings['after_cart_expire'] : 60; // minutes

		$every_minute = $every_minute * 60;

		if (isset($schedules['tfhb_cart_remover'])) {
			// Remove the old schedule

			unset($schedules['tfhb_cart_remover']);
			// Convert into seconds

			$schedules['tfhb_cart_remover'] = array(
				'interval' => $every_minute,
				'display'  => __('Hydra Cart Remover after Expire', 'hydra-booking'),
			);
		} else {

			$schedules['tfhb_cart_remover'] = array(
				'interval' => $every_minute,
				'display'  => __('Hydra Cart Remover after Expire', 'hydra-booking'),
			);
		}

		return $schedules;
	}
	function tfhb_cart_auto_remover_schedule_callback()
	{

		// Get all WooCommerce sessions
		global $wpdb;
		$table = $wpdb->prefix . 'woocommerce_sessions';
		$sessions = $wpdb->get_results("SELECT session_key, session_value FROM $table");

		$general_settings = get_option('_tfhb_general_settings', true) ? get_option('_tfhb_general_settings', true) : array();
		$every_minute     = ! empty($general_settings['after_cart_expire']) ? $general_settings['after_cart_expire'] : 60; // minutes

		$every_minute = intval($every_minute * 60);

		foreach ($sessions as $session) {
			$session_data = maybe_unserialize($session->session_value);

			if (! empty($session_data['cart'])) {
				$cart = maybe_unserialize($session_data['cart']);

				foreach ($cart as $key => $item) {
					$added_time = !empty($item['tfhb_order_meta']['added_time']) ? intval($item['tfhb_order_meta']['added_time']) : 0;
					if (!empty($added_time) && (time() - $added_time) > $every_minute) {

						// Update Booking
						if (!empty($item['tfhb_order_meta']['booking_id'])) {
							$booking = new Booking();
							$booking_id = $item['tfhb_order_meta']['booking_id'];
							$updat_booking['id'] = $booking_id;
							$updat_booking['status'] = 'canceled';
							$booking->update($updat_booking);
						}

						// Attendees update
						if (!empty($item['tfhb_order_meta']['attendee_id'])) {
							$Attendees = new Attendees();
							$attendee_id = $item['tfhb_order_meta']['attendee_id'];
							$updat_attendee['id'] = $attendee_id;
							$updat_attendee['status'] = 'canceled';
							$Attendees->update($updat_attendee);
						}

						unset($cart[$key]);
					}
				}

				// Save updated session
				$session_data['cart'] = $cart;
				$wpdb->update(
					$table,
					['session_value' => maybe_serialize($session_data)],
					['session_key' => $session->session_key]
				);
			}
		}
	}

	// Update Booking Cron Job
	public function tfhb_after_booking_completed_schedule_update()
	{
		// Make custom Shedule intervel
		add_filter('cron_schedules', array($this, 'tfhb_custom_cron_job_schedule'));

		wp_clear_scheduled_hook('tfhb_after_booking_completed_schedule');
		// Create custom interverl for 60 minutes

		wp_schedule_event(time(), 'tfhb_after_booking_status', 'tfhb_after_booking_completed_schedule');


		// Make custom Shedule intervel
		add_filter('cron_schedules', array($this, 'tfhb_custom_cart_cron_job_schedule'));

		wp_clear_scheduled_hook('tfhb_cart_auto_remover_schedule');
		// Create custom interverl for 60 minutes

		wp_schedule_event(time(), 'tfhb_cart_remover', 'tfhb_cart_auto_remover_schedule');

		add_filter('cron_schedules', array($this, 'tfhb_google_calendar_cron_schedule'));
		wp_clear_scheduled_hook('tfhb_calendar_sync_schedule');
		wp_schedule_event(time(), 'tfhb_calendar_sync_status', 'tfhb_calendar_sync_schedule');
	}

	public function tfhb_google_calendar_cron_schedule($schedules)
	{
		$schedules['tfhb_calendar_sync_status'] = array(
			'interval' => 15 * 60, // 15 minutes check
			'display'  => __('Hydra Google Calendar Sync Check', 'hydra-booking'),
		);
		$schedules['tfhb_sync_interval_5'] = array('interval' => 5 * 60, 'display' => __('Hydra Sync Every 5 Min', 'hydra-booking'));
		$schedules['tfhb_sync_interval_10'] = array('interval' => 10 * 60, 'display' => __('Hydra Sync Every 10 Min', 'hydra-booking'));
		$schedules['tfhb_sync_interval_15'] = array('interval' => 15 * 60, 'display' => __('Hydra Sync Every 15 Min', 'hydra-booking'));
		$schedules['tfhb_sync_interval_30'] = array('interval' => 30 * 60, 'display' => __('Hydra Sync Every 30 Min', 'hydra-booking'));
		$schedules['tfhb_sync_interval_60'] = array('interval' => 60 * 60, 'display' => __('Hydra Sync Every 1 Hour', 'hydra-booking'));
		$schedules['tfhb_sync_interval_360'] = array('interval' => 360 * 60, 'display' => __('Hydra Sync Every 6 Hours', 'hydra-booking'));
		$schedules['tfhb_sync_interval_720'] = array('interval' => 720 * 60, 'display' => __('Hydra Sync Every 12 Hours', 'hydra-booking'));
		$schedules['tfhb_sync_interval_1440'] = array('interval' => 1440 * 60, 'display' => __('Hydra Sync Every 24 Hours', 'hydra-booking'));
		return $schedules;
	}

	public function tfhb_calendar_sync_schedule_callback($host_id = null)
	{
		$google_calendar_service = new GoogleCalendar();

		// Host Specific Sync
		if (!empty($host_id)) {
			$google_calendar_service->syncHostGoogleCalendar($host_id);
			return true;
		}

		// Fallback for all hosts (if legacy scheduled event runs)
		$host  = new Host();
		$hosts = $host->getAll();
		if (empty($hosts) || ! is_array($hosts)) {
			return true;
		}

		foreach ($hosts as $single_host) {
			if (empty($single_host->user_id) || empty($single_host->id)) {
				continue;
			}
			$user_id = $single_host->user_id;
			$_tfhb_host_integration_settings = is_array(get_user_meta($user_id, '_tfhb_host_integration_settings', true)) ? get_user_meta($user_id, '_tfhb_host_integration_settings', true) : array();
			$host_gc                         = isset($_tfhb_host_integration_settings['google_calendar']) ? $_tfhb_host_integration_settings['google_calendar'] : array();

			if (empty($host_gc['status']) || empty($host_gc['connection_status']) || empty($host_gc['two_way_sync'])) {
				continue;
			}

			$sync_interval  = isset($host_gc['sync_interval']) ? (int) $host_gc['sync_interval'] : 15;
			$last_sync_time = isset($host_gc['last_sync_time']) ? (int) $host_gc['last_sync_time'] : 0;

			if ((time() - $last_sync_time) >= ($sync_interval * 60)) {
				$google_calendar_service->syncHostGoogleCalendar($single_host->id);
			}
		}

		return true;
	}
}
