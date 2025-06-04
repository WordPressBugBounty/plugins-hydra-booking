<?php
namespace HydraBooking\Migration\ThirdParty\FluentBooking;

	// exit
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

	use HydraBooking\DB\Availability;
	use HydraBooking\DB\Meeting;
	use HydraBooking\DB\Host;
	use HydraBooking\DB\Booking;

class Migrator {

	private static $instance;

	/**
	 * @return static
	 */
	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct() {
		$this->migrate();
	}

	public function migrate() {
		// $this->migrateSettings();
		// $this->migrateAvailability();
		// $this->migrateMeeting();
		// $this->migrateHost();
		// $this->migrateBooking();
	}

	// Migrate Settings
	public function migrateSettings() {

		global $wpdb;

		// General Settings
		$fluent_settings = get_option( '_fluent_booking_settings' );
		$hydra_settings  = get_option( '_tfhb_general_settings' );

		$hydra_settings['time_zone']       = isset( $fluent_settings['time_zone'] ) ? $fluent_settings['time_zone'] : $hydra_settings['time_zone'];
		$hydra_settings['time_format']     = isset( $fluent_settings['time_format'] ) ? $fluent_settings['time_format'] : $hydra_settings['time_format'];
		$hydra_settings['week_start_from'] = isset( $fluent_settings['administration']['start_day'] ) ? $fluent_settings['administration']['start_day'] : $hydra_settings['week_start_from'];
		$hydra_settings['country']         = isset( $fluent_settings['administration']['default_country'] ) ? $fluent_settings['administration']['default_country'] : $hydra_settings['country'];

		$hydra_settings['after_booking_completed'] = isset( $fluent_settings['administration']['auto_cancel_timing'] ) ? $fluent_settings['administration']['auto_cancel_timing'] : $hydra_settings['after_booking_completed'];

		update_option( '_tfhb_general_settings', $hydra_settings );

		// Integration Settings
		$tfhb_integration_settings = get_option( '_tfhb_integration_settings' );
		$fcal_google               = get_option( '_fcal_google_calendar_client_details' );

		// Google Calendar
		$tfhb_integration_settings['google_calendar']['client_id']  = isset( $fcal_google['client_id'] ) ? $fcal_google['client_id'] : $tfhb_integration_settings['google_calendar']['client_id'];
		$tfhb_integration_settings['google_calendar']['secret_key'] = isset( $fcal_google['secret_key'] ) ? $fcal_google['secret_key'] : $tfhb_integration_settings['google_calendar']['secret_key'];

		// Zoom Integration
		// Get Wpdb prefix
		$prefix = $wpdb->prefix;
		// custom query with prepare statement
		$zoom_integration = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}fcal_meta WHERE object_type = %s AND object_id = %s  AND  `key` = %s",
				'user_meta',
				1,
				'zoom_credentials'
			)
		);

		$zoom_integration_value                                      = maybe_unserialize( $zoom_integration->value );
		$tfhb_integration_settings['zoom_meeting']['account_id']     = isset( $zoom_integration_value['account_id'] ) ? $zoom_integration_value['account_id'] . '2' : $tfhb_integration_settings['zoom_meeting']['account_id'];
		$tfhb_integration_settings['zoom_meeting']['app_client_id']  = isset( $zoom_integration_value['client_id'] ) ? $zoom_integration_value['client_id'] : $tfhb_integration_settings['zoom_meeting']['app_client_id'];
		$tfhb_integration_settings['zoom_meeting']['app_secret_key'] = isset( $zoom_integration_value['client_secret'] ) ? $zoom_integration_value['client_secret'] : $tfhb_integration_settings['zoom_meeting']['app_secret_key'];
		$tfhb_integration_settings['zoom_meeting']['connection_status'] = ! empty( $zoom_integration_value['account_id'] ) ? 1 : $zoom_integration_value['account_id'];

		// update Integration settings
		update_option( '_tfhb_integration_settings', $tfhb_integration_settings );
	}

	// Migration Availability
	public function migrateAvailability() {

		global $wpdb;
		$_tfhb_availability_settings = get_option( '_tfhb_availability_settings' );
		// Get Wpdb prefix
		$prefix = $wpdb->prefix;

		$days = array( 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' );

		// Get all availability
		$availabilities = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}fcal_meta WHERE object_type = %s",
				'availability',
			)
		);

		foreach ( $availabilities as $key => $value ) {

			$data = maybe_unserialize( $value->value );

			// $availability['id'] = isset($value->id) ? sanitize_text_field($value->id) : '';
			$availability['host']      = isset( $value->object_id ) ? sanitize_text_field( $value->object_id ) : '';
			$availability['title']     = isset( $data->title ) ? sanitize_text_field( $data->title ) : 'No title';
			$availability['time_zone'] = isset( $data['timezone'] ) ? sanitize_text_field( $data['timezone'] ) : '';
			$availability['override']  = '';
			$count                     = 0;
			foreach ( $data['weekly_schedules'] as $dkey => $dvalue ) {

				$day = $dkey; // mon
				$day = array_filter(
					$days,
					function ( $day_data ) use ( $day ) {
						return substr( $day_data, 0, 3 ) === $day;
					}
				);
				$day = array_values( $day );
				$availability['time_slots'][ $count ]['day'] = $day[0];
				// if day first 3 char is match with any $days array value string then get the days expale: monday dunamicly
				// $availability['time_slots'][$count]['day'] =
				$availability['time_slots'][ $count ]['status'] = $dvalue['enabled'] ? 1 : 0;
				$availability['time_slots'][ $count ]['times']  = $dvalue['slots'];

				++$count;
			}

			$availability['date_status'] = '';

			// // Date slots
			$date_count = 0;
			foreach ( $data['date_overrides'] as $date_key => $date_value ) {
				$availability['date_slots'][ $date_count ] = array(
					'date'   => $date_key,
					'status' => 0,
					'times'  => $date_value,

				);

				++$date_count;
			}

			$availability['status'] = 'active';
			// Insert Availability
			$InsertAvailability = new Availability();
			$insert             = $InsertAvailability->add( $availability );

			$_tfhb_host_info                   = get_user_meta( $value->object_id, '_tfhb_host', true );
			$_tfhb_host_info['availability'][] = $availability;
			update_user_meta( $value->object_id, '_tfhb_host', $_tfhb_host_info );

		}
	}

	// Migrate Meeting
	public function migrateMeeting() {

		global $wpdb;

		// Get all booking
		$fluent_calender = $wpdb->get_results(
			"SELECT * FROM {$wpdb->prefix}fcal_calendars"
		);

		// Get all booking
		$meeting = $wpdb->get_results(
			"SELECT * FROM {$wpdb->prefix}tfhb_meetings"
		);

		foreach ( $fluent_calender as $key => $value ) {
			// Convert object to array
			$value = (array) $value;

			// Get all booking
			$fluent_calender_event = (array) $wpdb->get_row(
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}fcal_calendar_events where calendar_id = %s",
					$value['id']
				)
			);
			$settings              = maybe_unserialize( $fluent_calender_event['settings'] );
			$location_settings     = maybe_unserialize( $fluent_calender_event['location_settings'] );
			$location              = array();
			foreach ( $location_settings as $lkey => $lvalue ) {
				$location[ $lvalue['title'] ] = array(
					'location' => $lvalue['title'],
					'address'  => $lvalue['display_on_booking'],
				);
			}

			// Get Current User
			$current_user = wp_get_current_user();
			// get user id
			$current_user_id = $current_user->ID;

			// Create an array to store the post data for meeting the current row
			$meeting_post_data = array(
				'post_type'   => 'tfhb_meeting',
				'post_title'  => $value['title'],
				'post_status' => 'publish',
				'post_author' => $current_user_id,
			);
			$meeting_post_id   = wp_insert_post( $meeting_post_data );

				// Update Meeting
			$data         = array(
				// 'id'                        => $value['id'],
				'user_id'                 => $value['user_id'],
				'title'                   => isset( $value['title'] ) ? sanitize_text_field( $value['title'] ) : '',
				'host_id'                 => isset( $value['calendar_id'] ) ? sanitize_key( $value['calendar_id'] ) : '',
				'description'             => isset( $value['description'] ) ? sanitize_text_field( $value['description'] ) : '',
				'meeting_type'            => isset( $fluent_calender_event['event_type'] ) ? sanitize_text_field( $fluent_calender_event['event_type'] ) : '',
				'post_id'                 => $meeting_post_id,
				'duration'                => isset( $fluent_calender_event['duration'] ) ? sanitize_text_field( $fluent_calender_event['duration'] ) : '',
				'custom_duration'         => isset( $value['custom_duration'] ) ? sanitize_text_field( $value['custom_duration'] ) : '',
				'meeting_locations'       => isset( $location ) ? $location : '',
				'meeting_category'        => isset( $value['meeting_category'] ) ? sanitize_text_field( $value['meeting_category'] ) : '',
				'availability_type'       => isset( $value['availability_type'] ) ? sanitize_text_field( $value['availability_type'] ) : '',
				'availability_range_type' => isset( $settings['range_type'] ) ? sanitize_text_field( $settings['range_type'] ) : '',
				'availability_range'      => isset( $settings['range_days'] ) ? $settings['range_days'] : '',
				'availability_id'         => isset( $fluent_calender_event['availability_id'] ) ? sanitize_text_field( $fluent_calender_event['availability_id'] ) : '',
				'availability_custom'     => isset( $value['availability_custom'] ) ? $value['availability_custom'] : '',
				'buffer_time_before'      => isset( $settings['buffer_time_before'] ) ? sanitize_text_field( $settings['buffer_time_before'] ) : '',
				'buffer_time_after'       => isset( $settings['buffer_time_after'] ) ? sanitize_text_field( $settings['buffer_time_after'] ) : '',
				'booking_frequency'       => isset( $settings['booking_frequency'] ) ? $settings['booking_frequency'] : '',
				'meeting_interval'        => isset( $value['meeting_interval'] ) ? sanitize_text_field( $value['meeting_interval'] ) : '',
				'recurring_status'        => isset( $value['recurring_status'] ) ? sanitize_text_field( $value['recurring_status'] ) : '',
				'recurring_repeat'        => isset( $value['recurring_repeat'] ) ? $value['recurring_repeat'] : '',
				'recurring_maximum'       => isset( $value['recurring_maximum'] ) ? sanitize_text_field( $value['recurring_maximum'] ) : '',
				'attendee_can_cancel'     => isset( $settings['can_not_cancel']['enabled'] ) ? sanitize_text_field( $settings['can_not_cancel']['enabled'] ) : '',
				'attendee_can_reschedule' => isset( $settings['can_not_reschedule']['enabled'] ) ? sanitize_text_field( $settings['can_not_reschedule']['enabled'] ) : '',
				'questions_type'          => isset( $value['questions_type'] ) ? sanitize_text_field( $value['questions_type'] ) : '',
				'questions'               => isset( $value['questions'] ) ? $value['questions'] : '',
				'notification'            => isset( $value['notification'] ) ? $value['notification'] : '',
				'payment_status'          => isset( $fluent_calender_event['type'] ) ? sanitize_text_field( $fluent_calender_event['type'] ) : '',
				'meeting_price'           => isset( $value['meeting_price'] ) ? sanitize_text_field( $value['meeting_price'] ) : '',
				'payment_currency'        => isset( $value['payment_currency'] ) ? sanitize_text_field( $value['payment_currency'] ) : '',
				'payment_method'          => isset( $value['payment_method'] ) ? sanitize_text_field( $value['payment_method'] ) : '',
				'payment_meta'            => isset( $value['payment_meta'] ) ? $value['payment_meta'] : '',
				'updated_at'              => gmdate( 'Y-m-d' ),
				'updated_by'              => $current_user_id,
			);
			$data['slug'] = get_post_field( 'post_name', $meeting_post_id );
			// Check if user is already a meeting
			$meeting = new Meeting();
			// Insert meeting
			$meetingInsert = $meeting->add( $data );

			$meetings_id = $meetingInsert['insert_id'];

			// Meetings Id into Post Meta
			update_post_meta( $meeting_post_id, '__tfhb_meeting_id', $meetings_id );
		}
	}

	// Migrate Host
	public function migrateHost() {
		global $wpdb;

		// Get all booking
		$fluent_calender = $wpdb->get_results(
			"SELECT * FROM {$wpdb->prefix}fcal_calendars"
		);
		foreach ( $fluent_calender as $key => $value ) {

			$user_id              = $value->user_id;
			$fluent_calender_meta = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}fcal_meta where object_type = %s AND object_id = %s",
					'Calendar',
					$value->id
				)
			);
			$wp_tfhb_bookings     = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM {$wpdb->prefix}fcal_meta where object_type = %s AND object_id = %s",
					'Calendar',
					$value->id
				)
			);

			$profile_photo_url  = '';
			$featured_image_url = '';

			foreach ( $fluent_calender_meta as $meta ) {
				if ( $meta->key == 'profile_photo_url' ) {
					$profile_photo_url = $meta->value;
				}
				if ( $meta->key == 'featured_image_url' ) {
					$featured_image_url = $meta->value;
				}
			}

			// Get user Data
			$user = get_user_by( 'id', $user_id );

			if ( empty( $user ) ) {
				continue;
			}

			$host      = new Host();
			$hostCheck = $host->get( array( 'user_id' => $user_id ) );
			if ( ! empty( $hostCheck ) ) {
				continue;
			}

			$data = array(
				'user_id'        => $user->ID,
				'first_name'     => get_user_meta( $user->ID, 'first_name', true ) != '' ? get_user_meta( $user->ID, 'first_name', true ) : $user->display_name,
				'last_name'      => get_user_meta( $user->ID, 'last_name', true ) != '' ? get_user_meta( $user->ID, 'last_name', true ) : '',
				'email'          => $user->user_email,
				'phone_number'   => '',
				'time_zone'      => $value->author_timezone,
				'about'          => $value->description,
				'avatar'         => $profile_photo_url,
				'featured_image' => $featured_image_url,
				'status'         => 1,
			);

			// Insert Host
			$hostInsert = $host->add( $data );

			unset( $data['user_id'] );
			$data['host_id'] = $hostInsert['insert_id'];

			// Update user Option
			update_user_meta( $user_id, '_tfhb_host', $data );

			// Get all user

		}

		exit;
	}

	// Migrate Booking
	public function migrateBooking() {
		global $wpdb;

		$fluent_booking = $wpdb->get_results(
			"SELECT * FROM {$wpdb->prefix}fcal_bookings"
		);
		$booking        = new Booking();
		$tfhb_booking   = $booking->get( 7 );
		foreach ( $fluent_booking as $key => $value ) {
			// 2024-04-30 04:30:00 . ge only 2024-04-30 form here
			$meeting_dates = explode( ' ', $value->start_time );
			$meeting_dates = $meeting_dates[0];

			$data = array(
				'meeting_id'         => $value->event_id,
				'host_id'            => $value->calendar_id,
				'attendee_id'        => $value->person_user_id,
				'post_id'            => '',
				'hash'               => $value->hash,
				'order_id'           => '',
				'attendee_time_zone' => $value->person_time_zone,
				'meeting_dates'      => $meeting_dates,
				'start_time'         => $value->start_time,
				'end_time'           => $value->end_time,
				'slot_minutes'       => $value->event_id,
				'duration'           => $value->slot_minutes,
				'attendee_name'      => $value->first_name . ' ' . $value->last_name,
				'email'              => $value->email,
				'address'            => $value->event_id,
				'others_info'        => $value->other_info,
				'country'            => $value->country,
				'ip_address'         => $value->ip_address,
				'device'             => $value->device,
				'meeting_locations'  => $value->location_details,
				'meeting_calendar'   => $value->event_id,
				'cancelled_by'       => $value->cancelled_by,
				'status'             => $value->status,
				'reason'             => $value->event_id,
				'booking_type'       => $value->booking_type,
				'payment_method'     => $value->booking_type,
				'payment_status'     => $value->booking_type,
				'created_at'         => $value->created_at,
				'updated_at'         => $value->updated_at,

			);

			$booking->add( $data );
		}
	}
}

// call the class
