<?php
namespace HydraBooking\Admin\Controller;
// exit
if ( ! defined( 'ABSPATH' ) ) { exit; }

// Use Namespace
use HydraBooking\Admin\Controller\RouteController;

// Use DB
use HydraBooking\DB\Booking;
use HydraBooking\DB\Attendees;
use HydraBooking\DB\Host;
use HydraBooking\Admin\Controller\DateTimeController;
use HydraBooking\DB\Meeting;
use HydraBooking\DB\Transactions;
use HydraBooking\DB\BookingMeta;
use HydraBooking\Services\Integrations\BookingBookmarks\BookingBookmarks;

class BookingController {


	// constaract
	public function __construct() {
	}

	public function init() {
	}

	public function create_endpoint() {
		register_rest_route(
			'hydra-booking/v1',
			'/booking/lists',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'getBookingsData' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
			)
		);
		register_rest_route(
			'hydra-booking/v1',
			'/booking/get-availability-dates',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'getAvailabilityDates' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
			)
		);
		register_rest_route(
			'hydra-booking/v1',
			'/booking/get-availability-time-slot',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'getAvailabilityTimeSlot' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
			)
		);
		register_rest_route(
			'hydra-booking/v1',
			'/booking/re-book-meeting',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'ReBookCencelMeeting' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
			)
		);
		register_rest_route(
			'hydra-booking/v1',
			'/booking/create',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'CreateBooking' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
			)
		);
		register_rest_route(
			'hydra-booking/v1',
			'/booking/delete',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'DeleteBooking' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
			)
		);
		// Get Single Booking based on id
		register_rest_route(
			'hydra-booking/v1',
			'/booking/(?P<id>[0-9]+)',
			array(
				'methods'  => 'GET',
				'callback' => array( $this, 'getBookingData' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
			)
		);
		// Get Single Booking based on id
		register_rest_route(
			'hydra-booking/v1',
			'/booking/details/(?P<id>[0-9]+)',
			array(
				'methods'  => 'GET',
				'callback' => array( $this, 'getBookingDetails' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
			)
		);
		// Change Attendee email Attendee.
		register_rest_route(
			'hydra-booking/v1',
			'/booking/change-booking-status',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'changeBookingDetailsStatus' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
			)
		);
		// Cancel Booking Attendee.
		register_rest_route(
			'hydra-booking/v1',
			'/booking/cancel-booking-attendee',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'cancelBookingAttendee' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
			)
		);

		register_rest_route(
			'hydra-booking/v1',
			'/booking/update',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'updateBooking' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
			)
		);
		register_rest_route(
			'hydra-booking/v1',
			'/booking/bulk-update',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'updateBulkStatus' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
			)
		);

		// booking reminder email.
		register_rest_route(
			'hydra-booking/v1',
			'/booking/send-reminder',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'sendReminderEmail' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
			)
		);
		// booking reminder email Attendee.
		register_rest_route(
			'hydra-booking/v1',
			'/booking/send-attendee-reminder',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'sendReminderAttendeeEmail' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
			)
		);
		// Change Attendee email Attendee.
		register_rest_route(
			'hydra-booking/v1',
			'/booking/change-attendee-status',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'changeAttendeeStatus' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
			)
		);

		// update booking internal notes
		register_rest_route(
			'hydra-booking/v1',
			'/booking/update-internal-note',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'updateInternalNotes' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
			)
		);
		

		// Pre Booking Data
		register_rest_route(
			'hydra-booking/v1',
			'/booking/pre',
			array(
				'methods'  => 'GET',
				'callback' => array( $this, 'getPreBookingsData' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
			)
		);
		register_rest_route(
			'hydra-booking/v1',
			'/booking/meeting',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'getpreMeetingData' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
			)
		);
		register_rest_route(
			'hydra-booking/v1',
			'/booking/availabletime',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'getAvailableTimeData' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
			)
		);

		// Export Booking Data as csv
		register_rest_route(
			'hydra-booking/v1',
			'/booking/export-as',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'exportBookingDataAs' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_integrations_permission'),
			)
		);

		// Filter Booking
		register_rest_route(
			'hydra-booking/v1',
			'/booking/filter',
			array(
				'methods'  => 'GET',
				'callback' => array( $this, 'filterBookings' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
				'args'     => array(
					'title' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);
	}


	// Booking List
	public function getBookingsData() {
	
		$request = json_decode( file_get_contents( 'php://input' ), true ); 
		$filter_data = isset( $request['filter_data'] ) ? $request['filter_data'] : '';

		$filter_type = isset( $filter_data['filter_type'] ) ? $filter_data['filter_type'] : '';
		$filter_search = isset( $filter_data['filter_search'] ) ? $filter_data['filter_search'] : '';
		$host_ids = isset( $filter_data['host_ids'] ) ? $filter_data['host_ids'] : '';
		$meeting_ids = isset( $filter_data['meeting_ids'] ) ? $filter_data['meeting_ids'] : '';
		$status = isset( $filter_data['status'] ) ? $filter_data['status'] : '';
		$date_range = isset( $filter_data['date_range'] ) ? $filter_data['date_range'] : ''; 

		$current_user = wp_get_current_user();
		// get user role
		$current_user_role = ! empty( $current_user->roles[0] ) ? $current_user->roles[0] : '';
		$current_user_id   = $current_user->ID;

		// Booking Lists
		$booking = new Booking();
		$where = array();

		if( ! empty( $filter_type ) && $filter_type == 'upcoming' && empty( $date_range['from']) ){ 
			$where[] = array('meeting_dates', '>=', date('Y-m-d'));
		}elseif( ! empty( $filter_type ) && $filter_type == 'completed' &&  empty($status) ){   
			$where[] = array('status', '=', 'completed');
		}elseif( ! empty( $filter_type ) && $filter_type == 'latest' ){  
			// based on created date 
			$where[] = array('created_at', '>=', date('Y-m-d', strtotime('-7 days')));
		}elseif( ! empty( $filter_type ) && $filter_type == 'filter' ){  
			// filter by host
			if( ! empty( $host_ids ) ){ 
				$where[] = array('host_id', 'IN', $host_ids);
			}

			// filter by meeting
			if( ! empty( $meeting_ids ) ){ 
				$where[] = array('meeting_id', 'IN', $meeting_ids);
			}

			// filter by status
			if( ! empty( $status ) ){ 
				$where[] = array('status', 'IN', $status);
			}

			// filter by date range
			if( ! empty( $date_range['from'] ) ){   

				$where[] = array('meeting_dates', '>=', date('Y-m-d', strtotime($date_range['from'])));
				$where[] = array('meeting_dates', '<=', date('Y-m-d', strtotime($date_range['to'])));
			}
		}elseif( ! empty( $filter_type ) && $filter_type == 'search' && ! empty( $filter_search ) ){  
			// based on created date  
			$where[] = array('meeting.title', 'LIKE', '%'.$filter_search.'%');
			$where[] = array('attendee.attendee_name', 'LIKE', '%'.$filter_search.'%');
		}else{
			// Get all order by desc 
		}
		


		
		if ( 'administrator' != $current_user_role && 'tfhb_host' != $current_user_role ) {
			return rest_ensure_response(
				array(
					'status'  => false,
					'message' => __('You are not allowed to access this page', 'hydra-booking'),
				)
			);
			
		}
		
		if ( ! empty( $current_user_role ) && 'tfhb_host' == $current_user_role ) {
			$host     = new Host();
			$HostData = $host->getHostByUserId( $current_user_id );
			$where[] = array('host_id', '=', $HostData->id); 
		}
		$bookingsList = $booking->getBookingWithAttendees(  
			$where,
			null,
			'DESC',
		); 
		  

		$extractedBookings = array_map(
			function ( $booking ) {
				return array(
					'id'            => $booking->id,
					'title'         => $booking->title,
					'meeting_dates' => $booking->meeting_dates,
					'start_time'    => $booking->start_time,
					'end_time'      => $booking->end_time,
					'status'        => $booking->status,
					'host_id'       => $booking->host_id,
				);
			},
			$bookingsList
		);

		$booking_array = array();
		foreach ( $extractedBookings as $book ) {

			// Convert start and end times to 24-hour format
			$start_time_24hr = gmdate( 'H:i', strtotime( $book['start_time'] ) );
			$end_time_24hr   = gmdate( 'H:i', strtotime( $book['end_time'] ) );

			$dates = explode( ',', $book['meeting_dates'] );
			$first_date = $dates[0];

			$booking_array[] = array(
				'booking_id'   => $book['id'],
				'title'        => $book['title'],
				'start'        => $first_date . 'T' . $start_time_24hr,
				'end'          => $first_date . 'T' . $end_time_24hr,
				'status'       => $book['status'],
				'booking_date' => $first_date,
				'booking_time' => $book['start_time'] . ' - ' . $book['end_time'],
				'host_id'      => $book['host_id'],
			);
		}
		// make list based on date
		$booking_list = array_values(array_reduce($bookingsList , function($carry, $item) {
			$dates = explode(',', $item->meeting_dates);
			
			foreach ($dates as $kye => $date) {
				
				$carry[$date]['date'] = $date;
				$carry[$date]['bookings'][] = $item;
			}
			return $carry;
		}, [])); 
		if( ! empty( $filter_type )  ){
			// Current date
			$currentDateTime = new \DateTime( date( 'Y-m-d' ) );
			
			if($filter_type == 'upcoming'){
				// Filter out dates that are before the current date
				$filteredData = array_filter($booking_list, function ($item) use ($currentDateTime) {
					$date = new \DateTime($item['date']);
					return $date >= $currentDateTime;
				});
				// Sort the data by date in ascending order
				usort($filteredData, function ($a, $b) {
					return strcmp($a['date'], $b['date']);
				});
				
			}else{

				$filteredData = $booking_list;
			} 
			// Sort bookings within each date by time
			foreach ($filteredData as &$dateData) { 

				usort($dateData['bookings'], function ($a, $b) {
					$timeA = \DateTime::createFromFormat('h:i A', $a->start_time);
					$timeB = \DateTime::createFromFormat('h:i A', $b->start_time);
					return $timeA <=> $timeB;
				});

				if( ! empty( $filter_type ) && $filter_type == 'upcoming' ){ 
					// current date is today make it today
					if($dateData['date'] == date('Y-m-d')){
						$dateData['date'] = 'Today';
					}
					// current date is tomorrow make it tomorrow
					if($dateData['date'] == date('Y-m-d', strtotime('+1 day'))){
						$dateData['date'] = 'Tomorrow';
					}
				}
				
			}
			$booking_list = $filteredData;
		} 
		// Time Zone
		$DateTimeZone           = new DateTimeController( 'UTC' );
		$time_zone              = $DateTimeZone->TimeZone();
		// Return response
		$data = array(
			'status'           	=> true,
			'bookings'         	=> $booking_list,
			'booking_calendar' 	=> $booking_array,
			'time_zone' 		=> $time_zone,
			'message'          	=> 'Booking Data Successfully Retrieve!',
		);
		return rest_ensure_response( $data );
	}

	// Get getAvailabilityTimeSlot
	public function getAvailabilityDates(){
		// current user can manage booking 
		// get current user
		if ( ! current_user_can( 'tfhb_manage_booking' ) ) {
			return rest_ensure_response( array(
				'status'  => false,
				'message' => __('You are not allowed to access this page', 'hydra-booking'),
			) );

		}

		$request = json_decode( file_get_contents( 'php://input' ), true );  
		$meeting_id = isset( $request['meeting_id'] ) ? $request['meeting_id'] : '';
		$selected_time_zone = isset( $request['selected_time_zone'] ) ? $request['selected_time_zone'] : '';
		$date_time = new DateTimeController( $selected_time_zone );
		$meeting = new Meeting();
		$meeting_data = $meeting->get( $meeting_id );  
		$meeting_data = (array) $meeting_data;
		$user_id = $meeting_data['user_id'];
		
		// $AvailabilityData = $date_time->GetAvailabilityData( $meeting_data ); 
		if ( isset( $meeting_data['availability_type'] ) && 'settings' === $meeting_data['availability_type'] ) {
			$_tfhb_availability_settings = get_user_meta( $user_id, '_tfhb_host', true ); 
			
			if(isset($_tfhb_availability_settings['availability_type']) && $_tfhb_availability_settings['availability_type'] == 'settings'){
				$host_settings_availability_id = $_tfhb_availability_settings['availability_id'];
				$_tfhb_availability_settings =  get_option( '_tfhb_availability_settings' );

				if ( is_array($_tfhb_availability_settings)  ) { 
					$key = array_search($host_settings_availability_id, array_column($_tfhb_availability_settings, 'id'));
					//  _tfhb_availability_settings index id wich is match with host settings availability id
					if(isset($_tfhb_availability_settings[ $key ])){

						$AvailabilityData = $_tfhb_availability_settings[ $key ];
					}else{
						$AvailabilityData = isset( $meeting_data['availability_custom'] ) ? $meeting_data['availability_custom'] : array();
					} 
				} else {
					$AvailabilityData = isset( $meeting_data['availability_custom'] ) ? $meeting_data['availability_custom'] : array();
				} 
			}elseif (isset($_tfhb_availability_settings['availability']) &&  in_array( $meeting_data['availability_id'], array_keys( $_tfhb_availability_settings['availability'] ) ) ) {
				
				$AvailabilityData = $_tfhb_availability_settings['availability'][ $meeting_data['availability_id'] ];
				
				
			} else {
				$AvailabilityData = isset( $meeting_data['availability_custom'] ) ? $meeting_data['availability_custom'] : array();
			}
		} else {

			$AvailabilityData = isset( $meeting_data['availability_custom'] ) ? $meeting_data['availability_custom'] : array();
		}
		$AvailabilityData = !is_array( $AvailabilityData ) && !empty( $AvailabilityData ) ? json_decode( $AvailabilityData, true )  : $AvailabilityData;
	 

		// Map weekdays to numeric keys
		$dayMap = [
			'Sunday' => 0,
			'Monday' => 1,
			'Tuesday' => 2,
			'Wednesday' => 3,
			'Thursday' => 4,
			'Friday' => 5,
			'Saturday' => 6
		];

		// 2. Extract disabled days (status != 1)
		$disabled_days = [];
		foreach ($AvailabilityData['time_slots'] as $slot) {
			if ($slot['status'] != 1) {
				$dayName = $slot['day'];
				if (isset($dayMap[$dayName])) {
					$disabled_days[] = $dayMap[$dayName];
				}
			}
		}

		// 3. Extract disabled specific dates (available == 1)
		$disabled_dates = [];
		foreach ($AvailabilityData['date_slots'] as $dateSlot) {
			if ($dateSlot['available'] == 1) {
				$dates = explode(',', $dateSlot['date']);
				foreach ($dates as $date) {
					$disabled_dates[] = trim($date);
				}
			}
		}
	 

		$data = array(
			'status'    => true,
			'disabled_days' => $disabled_days, 
			'disabled_dates' => $disabled_dates, 
		);
		return rest_ensure_response( $data );
	 
	}
	// Get getAvailabilityTimeSlot
	public function getAvailabilityTimeSlot(){
		if ( ! current_user_can( 'tfhb_manage_booking' ) ) {
			return rest_ensure_response( array(
				'status'  => false,
				'message' => __('You are not allowed to access this page', 'hydra-booking'),
			) );

		} 
		$request = json_decode( file_get_contents( 'php://input' ), true ); 
		$select_date = isset( $request['select_date'] ) ? $request['select_date'] : '';
		$meeting_id = isset( $request['meeting_id'] ) ? $request['meeting_id'] : '';
		$selected_time_zone = isset( $request['selected_time_zone'] ) ? $request['selected_time_zone'] : '';
		$date_time = new DateTimeController( $selected_time_zone );
		$all_month_data = $date_time->getAvailableTimeData( $meeting_id, $select_date, $selected_time_zone, '12' );
	 
		$time_slot = [];
		foreach ( $all_month_data as $key => $value ) {
			$time_slot[] = array(
				'value' => json_encode($value),
				'name'  => $value['start'] .' - '. $value['end'] ,
			);
		}

		$data = array(
			'status'    => true,
			'time_slot' => $time_slot, 
		);
		return rest_ensure_response( $data );
	 
	}

	// Rebook Cencel Meeting 
	public function ReBookCencelMeeting(){

		if ( ! current_user_can( 'tfhb_manage_booking' ) ) {
			return rest_ensure_response( array(
				'status'  => false,
				'message' => __('You are not allowed to access this page', 'hydra-booking'),
			) );

		}
		$request = json_decode( file_get_contents( 'php://input' ), true ); 
		$select_date = isset( $request['select_date'] ) ? $request['select_date'] : '';
		$booking_id = isset( $request['booking_id'] ) ? $request['booking_id'] : '';
		$meeting_id = isset( $request['meeting_id'] ) ? $request['meeting_id'] : '';
		$availability_time_zone = isset( $request['availability_time_zone'] ) ? $request['availability_time_zone'] : '';
		$select_date = isset( $request['select_date'] ) ? $request['select_date'] : '';
		$select_time_slot = isset( $request['select_time_slot'] ) ? $request['select_time_slot'] : '';
		$select_status = isset( $request['select_status'] ) ? $request['select_status'] : '';
		$select_time_slot = json_decode( $select_time_slot, true ); 
		$start_time = $select_time_slot['start'];
		$end_time = $select_time_slot['end'];

		// get booking data with attendees 
		$booking = new Booking();
		$where = array(
			array('id', '=', $booking_id),
		);
		 $single_booking = $booking->getBookingWithAttendees(  
			$where,
			1,
			'DESC',
		);
		// if booking is not empty and not exist 
		if( ! empty( $single_booking ) && $single_booking->id != $booking_id ){
			// return error message
			$data = array(
				'status'    => false,
				'message' => 'Booking is not exist', 
			);
			return rest_ensure_response( $data );
		}  
		$booking->update(
			array(
				'id' => $booking_id,
				'meeting_dates' => $select_date,
				'start_time' => $start_time,
				'end_time' => $end_time,
				'availability_time_zone' => $availability_time_zone,
				'status' => $select_status, 
			)
		);

		// 
		// return witn success message
		$data = array(
			'status'    => true,
			'message' => 'Booking is updated successfully', 
		);
		return rest_ensure_response( $data );

 

	}

	// Pre Booking Data
	public function getPreBookingsData() {
		$DateTimeZone = new DateTimeController( 'UTC' );
		$time_zone    = $DateTimeZone->TimeZone();

		$meeting      = new Meeting();
		$MeetingsList = $meeting->get();

		$meeting_array = array();
		foreach ( $MeetingsList as $single ) {
			$meeting_array[] = array(
				'name'  => $single->title,
				'value' => '' . $single->id . '',
			);
		}

		

		$data = array(
			'status'    => true,
			'time_zone' => $time_zone,
			'meetings'  => $meeting_array,
		);
		return rest_ensure_response( $data );
	}

	// Pre Meeting Data
	public function getpreMeetingData() {
		$request    = json_decode( file_get_contents( 'php://input' ), true );
		$meeting_id = isset( $request['meeting_id'] ) ? $request['meeting_id'] : '';

		// Single Meeting Data
		$meeting      = new Meeting();
		$MeetingsData = $meeting->get( $meeting_id );

		// Meeting Location
		$meeting_locations = ! empty( $MeetingsData->meeting_locations ) ? json_decode( $MeetingsData->meeting_locations ) : array( '' );

		$meeting_location_array = array();
		if ( ! empty( $meeting_locations ) ) {
			foreach ( $meeting_locations as $single ) {
				if ( $single->location ) {
					$meeting_location_array[] = array(
						'name'  => $single->location,
						'value' => '' . $single->location . '',
					);
				}
			}
		}

		// Host List
		$host     = new Host();
		$HostData = $host->get( $MeetingsData->host_id );

		$meeting_host_array = array();
		if ( $HostData->first_name ) {
			$meeting_host_array[] = array(
				'name'  => $HostData->first_name . ' ' . $HostData->last_name,
				'value' => '' . $HostData->id . '',
			);
		}

		// Meeting Information
		$data = get_post_meta( $MeetingsData->post_id, '__tfhb_meeting_opt', true );

		if ( isset( $data['availability_type'] ) && 'settings' === $data['availability_type'] ) {
			$_tfhb_availability_settings = get_user_meta( $MeetingsData->host_id, '_tfhb_host', true );
			if ( in_array( $data['availability_id'], array_keys( $_tfhb_availability_settings['availability'] ) ) ) {
				$availability_data = $_tfhb_availability_settings['availability'][ $data['availability_id'] ];
			} else {
				$availability_data = isset( $data['availability_custom'] ) ? $data['availability_custom'] : array();
			}
		} else {
			$availability_data = isset( $data['availability_custom'] ) ? $data['availability_custom'] : array();
		}

		// Availability Range
		$availability_range      = isset( $data['availability_range'] ) ? $data['availability_range'] : array();
		$availability_range_type = isset( $data['availability_range_type'] ) ? $data['availability_range_type'] : array();

		// Duration
		$duration = isset( $data['duration'] ) && ! empty( $data['duration'] ) ? $data['duration'] : 30;

		$duration = isset( $data['custom_duration'] ) && ! empty( $data['custom_duration'] ) ? $data['custom_duration'] : $duration;

		// Buffer Time Before
		$buffer_time_before = isset( $data['buffer_time_before'] ) && ! empty( $data['buffer_time_before'] ) ? $data['buffer_time_before'] : 0;

		// Buffer Time After
		$buffer_time_after = isset( $data['buffer_time_after'] ) && ! empty( $data['buffer_time_after'] ) ? $data['buffer_time_after'] : 0;

		// Meeting Interval
		$meeting_interval = isset( $data['meeting_interval'] ) && ! empty( $data['meeting_interval'] ) ? $data['meeting_interval'] : 0;

		// Disable Dates
		$disabled_dates = array();
		if ( $availability_data['date_slots'] != '' ) {
			$date_slots = $availability_data['date_slots'];
			foreach ( $date_slots as $single ) {
				if ( $single['available'] == true ) {
					// string to array
					$dates = explode( ',', $single['date'] );
					foreach ( $dates as $date ) {
						$disabled_dates[] = $date;
					}
				}
			}
		}
		// Disable Unavailable days
		$unavailable_days = isset( $availability_data['time_slots'] ) ? $availability_data['time_slots'] : array();

		// day array based on js date key value
		$days = array( 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' );

		$unavailable_days_array = array();

		foreach ( $unavailable_days as $single ) {
			$unavailable_days_array[ $single['day'] ] = $single['status'] == false ? array_search( $single['day'], $days ) : 8;
		}

		// flatpickr configuration only for date not time
		$config = array(
			// 'enableTime' => false,
			'dateFormat'   => 'Y-m-d',
			'minDate'      => 'today',
			'defaultDate'  => 'today',
			'disable'      => $disabled_dates,
			'disable_days' => $unavailable_days_array,
		);

		if ( $availability_range_type != 'indefinitely' ) {
			$config['maxDate'] = $availability_range['end'];
		}

		$data = array(
			'status'         => true,
			'locations'      => $meeting_location_array,
			'hosts'          => $meeting_host_array,
			'available_slot' => $unavailable_days_array,
			'flatpickr_date' => $config,
		);
		return rest_ensure_response( $data );
	}

	// Pre Meeting Data
	public function getAvailableTimeData() {
		$request    = json_decode( file_get_contents( 'php://input' ), true );
		$meeting_id = isset( $request['meeting_id'] ) ? $request['meeting_id'] : '';

		$selected_date = isset( $request['date'] ) ? sanitize_text_field( $request['date'] ) : '';

		$selected_time_zone = isset( $request['time_zone'] ) ? sanitize_text_field( $request['time_zone'] ) : 'UTC';

		$selected_time_format = '12';

		$date_time = new DateTimeController( $selected_time_zone );
		$data      = $date_time->getAvailableTimeData( $meeting_id, $selected_date, $selected_time_zone, $selected_time_format );

		return rest_ensure_response(
			array(
				'status'          => true,
				'time_slots_data' => $data,
			)
		);
	}

	// Booking Filter
	public function filterBookings( $request ) {
		$filterData = $request->get_param( 'filterData' );

		// Booking Lists
		$booking      = new Booking();
		$bookingsList = $booking->getFilter( $filterData );
		
		$extractedBookings = array_map(
			function ( $booking ) {
				return array(
					'id'            => $booking->id,
					'title'         => $booking->title,
					'meeting_dates' => $booking->meeting_dates,
					'start_time'    => $booking->start_time,
					'end_time'      => $booking->end_time,
					'status'        => $booking->booking_status,
					'host_id'       => $booking->host_id,
				);
			},
			$bookingsList
		);

		$booking_array = array();
		foreach ( $extractedBookings as $book ) {

			// Convert start and end times to 24-hour format
			$start_time_24hr = gmdate( 'H:i', strtotime( $book['start_time'] ) );
			$end_time_24hr   = gmdate( 'H:i', strtotime( $book['end_time'] ) );

			$dates = explode( ',', $book['meeting_dates'] );
			$first_date = $dates[0];

			$booking_array[] = array(
				'booking_id'   => $book['id'],
				'title'        => $book['title'],
				'start'        => $first_date . 'T' . $start_time_24hr,
				'end'          => $first_date . 'T' . $end_time_24hr,
				'status'       => $book['status'],
				'booking_date' => $first_date,
				'booking_time' => $book['start_time'] . ' - ' . $book['end_time'],
				'host_id'      => $book['host_id'],
			);
		}

		// Return response
		$data = array(
			'status'           => true,
			'bookings'         => $bookingsList,
			'booking_calendar' => $booking_array,
			'message'          =>  __( 'Booking Data Successfully Retrieve!', 'hydra-booking' ),
		);
		return rest_ensure_response( $data );
	}

	// Create Booking
	public function CreateBooking() {
		$request = json_decode( file_get_contents( 'php://input' ), true );

		// Check if user is already a booking
		$booking = new Booking();

		if ( ! empty( $request['id'] ) ) {
			$data = array(
				'id'                 => isset( $request['id'] ) ? $request['id'] : '',
				'meeting_id'         => isset( $request['meeting'] ) ? $request['meeting'] : '',
				'attendee_name'      => isset( $request['name'] ) ? $request['name'] : '',
				'email'              => isset( $request['email'] ) ? $request['email'] : '',
				'attendee_time_zone' => isset( $request['time_zone'] ) ? $request['time_zone'] : '',
				'start_time'         => isset( $request['time']['start'] ) ? $request['time']['start'] : '',
				'end_time'           => isset( $request['time']['end'] ) ? $request['time']['end'] : '',
				'meeting_dates'      => isset( $request['date'] ) ? $request['date'] : '',
				'status'             => isset( $request['status'] ) ? $request['status'] : '',
			);

			// Booking Update
			$bookingUpdate = $booking->update( $data );
		} else {
			$data = array(
				'meeting_id'         => isset( $request['meeting'] ) ? $request['meeting'] : '',
				'attendee_name'      => isset( $request['name'] ) ? $request['name'] : '',
				'email'              => isset( $request['email'] ) ? $request['email'] : '',
				'attendee_time_zone' => isset( $request['time_zone'] ) ? $request['time_zone'] : '',
				'host_id'            => isset( $request['host'] ) ? $request['host'] : '',
				'meeting_dates'      => isset( $request['date'] ) ? $request['date'] : '',
				'start_time'         => isset( $request['time']['start'] ) ? $request['time']['start'] : '',
				'end_time'           => isset( $request['time']['end'] ) ? $request['time']['end'] : '',
				'status'             => isset( $request['status'] ) ? $request['status'] : '',
				'payment_method'     => 'backend',
				'payment_status'     => 'pending',
			);

			// Insert booking
			$bookingInsert = $booking->add( $data );
			if ( ! $bookingInsert['status'] ) {
				return rest_ensure_response(
					array(
						'status'  => false,
						'message' => __('Error while creating Booking', 'hydra-booking'),
					)
				);
			}
		}

		$single_booking_meta = $booking->get(
			array( 'id' => $request['id'] ),
			false,
		);

		if ( 'approved' == $request['status'] ) {
			do_action( 'hydra_booking/after_booking_completed', $single_booking_meta );
		}

		if ( 'canceled' == $request['status'] ) { 
			do_action( 'hydra_booking/after_booking_canceled', $single_booking_meta );
		}

		if ( 'schedule' == $request['status'] ) {
			do_action( 'hydra_booking/after_booking_schedule', $single_booking_meta );
		}


		// booking Lists
		$booking_List = $booking->get();
		// Return response
		$data = array(
			'status'  => true,
			'booking' => $booking_List,
			'message' => ! empty( $request['id'] ) ? __('Booking Updated Successfully', 'hydra-booking') : __('Booking Created Successfully', 'hydra-booking'),
		);

		return rest_ensure_response( $data );
	}

	// Delete Booking
	public function DeleteBooking() {

		$request       = json_decode( file_get_contents( 'php://input' ), true );
		$booking_id    = $request['id'];
		$booking_owner = $request['host'];
	
		if ( empty( $booking_id ) || $booking_id == 0 ) {
			return rest_ensure_response(
				array(
					'status'  => false,
					'message' => __('Invalid Booking', 'hydra-booking'),
				)
			);
		}
		// Delete Booking
		$booking       = new Booking();
		$bookingDelete = $booking->delete( $booking_id );
		$current_user  = get_userdata( $booking_owner );
		// get user role
		$current_user_role = ! empty( $current_user->roles[0] ) ? $current_user->roles[0] : '';
		$current_user_id   = $current_user->ID;

		if ( ! empty( $current_user_role ) && 'administrator' == $current_user_role ) {
			$bookingsList = $booking->get( null, true );
		}
		if ( ! empty( $current_user_role ) && 'tfhb_host' == $current_user_role ) {
			$host         = new Host();
			$HostData     = $host->getHostByUserId( $current_user_id );
			$bookingsList = $booking->get( null, true, false, false, false, false, $HostData->host_id );

		}
		
		$extractedBookings = array_map(
			function ( $booking ) {
				return array(
					'id'            => $booking->id,
					'title'         => $booking->title,
					'meeting_dates' => $booking->meeting_dates,
					'start_time'    => $booking->start_time,
					'end_time'      => $booking->end_time,
					'status'        => $booking->booking_status,
					'host_id'       => $booking->host_id,
				);
			},
			$bookingsList
		);

		$booking_array = array();
		foreach ( $extractedBookings as $book ) {
			// Convert start and end times to 24-hour format
			$start_time_24hr = gmdate( 'H:i', strtotime( $book['start_time'] ) );
			$end_time_24hr   = gmdate( 'H:i', strtotime( $book['end_time'] ) );
		 


			$dates = explode( ',', $book['meeting_dates'] );
			$first_date = $dates[0];
			
			$booking_array[] = array(
				'booking_id'   => $book['id'],
				'title'        => $book['title'],
				'start'        => $first_date . 'T' . $start_time_24hr,
				'end'          => $first_date . 'T' . $end_time_24hr,
				'status'       => $book['status'],
				'booking_date' => $first_date,
				'booking_time' => $book['start_time'] . ' - ' . $book['end_time'],
				'host_id'      => $book['host_id'],
			);
		}

		// Return response
		$data = array(
			'status'           => true,
			'bookings'         => $bookingsList,
			'booking_calendar' => $booking_array,
			'message'          =>  __('Booking Data Successfully Deleted!', 'hydra-booking'),
		);
		return rest_ensure_response( $data );
	}

	// Send Reminder Email
	public function sendReminderEmail(){
		$request = json_decode( file_get_contents( 'php://input' ), true ); 

		$booking_id =  isset( $request['booking_id'] ) ? $request['booking_id'] : 0;
		if( empty( $booking_id ) && $booking_id == 0 ){
			return rest_ensure_response(
				array(
					'status'  => false,
					'message' => __('Invalid Booking', 'hydra-booking'),
				)
			);
		}

		// Get Booking Data
		$booking = new Booking();  
		$Attendee =  new Attendees();
		$where = array(
			array('id', '=', $booking_id),
		);
		 $single_booking = $booking->getBookingWithAttendees(  
			$where,
			1,
			'DESC',
		);  

		if( empty( $single_booking ) ){
			return rest_ensure_response(
				array(
					'status'  => false,
					'message' =>  __('Invalid Booking', 'hydra-booking'),
				)
			);
		} 

		if( 'confirmed' != $single_booking->status ){
			return rest_ensure_response(
				array(
					'status'  => false,
					'message' =>  __('This Booking is not Confirmed', 'hydra-booking'),
				)
			);
		}

		do_action( 'hydra_booking/send_booking_reminder', $single_booking );


		// Return response
		$data = array(
			'status'  => true,
			'message' =>  __('Reminder Email Sent Successfully!', 'hydra-booking'),
		);
		return rest_ensure_response( $data );
	}

	// Send Reminder Email
	public function sendReminderAttendeeEmail(){
		$request = json_decode( file_get_contents( 'php://input' ), true ); 

		$attendee_id =  isset( $request['attendee_id'] ) ? $request['attendee_id'] : 0;
		if( empty( $attendee_id ) && $attendee_id == 0 ){
			return rest_ensure_response(
				array(
					'status'  => false,
					'message' =>  __('Invalid Attendee', 'hydra-booking'),
				)
			);
		}

	
		// Get Booking Data
		$Attendee =  new Attendees();
		$attendeeBooking =  $Attendee->getAttendeeWithBooking( 
			array(
				array('id', '=', $attendee_id),
			),
			1,
			'DESC'
		 );  
		if( empty( $attendeeBooking ) ){
			return rest_ensure_response(
				array(
					'status'  => false,
					'message' =>  __('Invalid Attendee', 'hydra-booking'),
				)
			);
		}

		if( 'confirmed' != $attendeeBooking->status ){
			return rest_ensure_response(
				array(
					'status'  => false,
					'message' =>  __('This Booking is not Confirmed', 'hydra-booking'),
				)
			);
		}

		do_action( 'hydra_booking/send_booking_reminder', $attendeeBooking );

		// Return response
		$data = array(
			'status'  => true,
			'message' =>  __('Reminder Email Sent Successfully!', 'hydra-booking'),
		);
		return rest_ensure_response( $data );
	}

	// Send Attendee Status
	public function changeAttendeeStatus($data = []){
		if( ! empty( $data ) ){
			$request = $data;
		}else{
			$request = json_decode( file_get_contents( 'php://input' ), true ); 
		} 
		$attendee_id =  isset( $request['attendee_id'] ) ? $request['attendee_id'] : 0;
		$status =  isset( $request['status'] ) ? $request['status'] : '';
		
		if( empty( $attendee_id ) && $attendee_id == 0 ){
			return rest_ensure_response(
				array(
					'status'  => false,
					'message' =>  __('Invalid Attendee', 'hydra-booking'),
				)
			);
		}

		// Get Booking Data
		$Attendee =  new Attendees();
		$attendeeBooking =  $Attendee->getAttendeeWithBooking( 
			array(
				array('id', '=', $attendee_id),
			),
			1,
			'DESC'
		 );


		if( empty( $attendeeBooking ) ){
			return rest_ensure_response(
				array(
					'status'  => false,
					'message' => __('Invalid Attendee', 'hydra-booking'),
				)
			);
		}
		if($attendeeBooking->status == $status){
			return rest_ensure_response(
				array(
					'status'  => false,
					'message' =>  __('Attendee Status Already Updated!', 'hydra-booking'),
				)
			);
		}

		$attendee_update = array();
		$status = strtolower( $status );
		$attendee_update['status'] = $status; 
		$attendee_update['id'] = $attendee_id;

		$attendeeUpdate = $Attendee->update( $attendee_update ); 
		
		if ( 'canceled' == $status ) { 
			
			do_action( 'hydra_booking/after_booking_canceled', $attendeeBooking );
		}
		if ( 'confirmed' == $status ) { 
			
			do_action( 'hydra_booking/after_booking_confirmed', $attendeeBooking );
		}
		if ( 'pending' == $status ) { 
			
			do_action( 'hydra_booking/after_booking_pending', $attendeeBooking );
		}
		

		if( $attendeeUpdate ){ 
			// Return response
			$data = array(
				'status'  => true,
				'message' =>  __('Attendee Status Updated Successfully!', 'hydra-booking'), 
			);
			return rest_ensure_response( $data );
		}
	}

	/*
	 * Update booking internal note
	 *
	 * @param $request
	 *
	 * @return mixed
	 * @since 1.0.16
	 * 
	 */

	 public function updateInternalNotes(){
		$bookingMeta = new BookingMeta();
		$request = json_decode( file_get_contents( 'php://input' ), true ); 
		$booking_id =  isset( $request['booking_id'] ) ? $request['booking_id'] : 0;
		$internal_note =  isset( $request['internal_note'] ) ? $request['internal_note'] : '';

		if( empty( $booking_id ) && $booking_id == 0 ){
			return rest_ensure_response(
				array(
					'status'  => false,
					'message' =>  __('Invalid Booking', 'hydra-booking'),
				)
			);
		}
		$get_internal_note = $bookingMeta->getWithIdKey( $booking_id, 'internal_note', 1 );
		if($get_internal_note){
			// update
			$bookingMeta->update( 
				array(
					'id' => $get_internal_note->id, 
					'value' => $internal_note,
				)
			 );
			 
			 $data = array(
				'status'  => true,
				'message' =>  __('Internal Note Updated Successfully!', 'hydra-booking'),
			);
			return rest_ensure_response( $data );
		}else{
			// insert
			$bookingMeta->add( 
				array(
					'booking_id' => $booking_id,
					'meta_key' => 'internal_note',
					'value' => $internal_note,
				)
			 );
			 
			 $data = array(
				'status'  => true,
				'message' =>  __('Internal Note Added Successfully!', 'hydra-booking'),
			);
			return rest_ensure_response( $data );
		}

	 }




	// Get Single Booking
	public function getBookingData( $request ) {
		$booking_id = $request['id']; 
		// Check if user is already a booking
		$booking = new Booking();
		// Insert booking
		$singlebooking = $booking->get(
			array( 'id' => $booking_id ),
			false,
			true
		);

		$meeting_id   = $singlebooking->meeting_id;
		$meeting      = new Meeting();
		$MeetingsData = $meeting->get( $meeting_id );

		$selected_date = $singlebooking->meeting_dates;

		$selected_time_zone = $singlebooking->attendee_time_zone;

		$selected_time_format = '12';
		// Meeting Information
		$data = get_post_meta( $MeetingsData->post_id, '__tfhb_meeting_opt', true );

		if ( isset( $data['availability_type'] ) && 'settings' === $data['availability_type'] ) {
			$_tfhb_availability_settings = get_user_meta( $MeetingsData->host_id, '_tfhb_host', true );
			if ( isset($_tfhb_availability_settings['availability']) && in_array( $data['availability_id'], array_keys( $_tfhb_availability_settings['availability'] ) ) ) {
				$availability_data = $_tfhb_availability_settings['availability'][ $data['availability_id'] ];
			} else {
				$availability_data = isset( $data['availability_custom'] ) ? $data['availability_custom'] : array();
			}
		} else {
			$availability_data = isset( $data['availability_custom'] ) ? $data['availability_custom'] : array();
		}

		// Disable Unavailable days
		$time_slots = isset( $availability_data['time_slots'] ) ? $availability_data['time_slots'] : array();

		// Duration
		$duration = isset( $data['duration'] ) && ! empty( $data['duration'] ) ? $data['duration'] : 30;

		$duration = isset( $data['custom_duration'] ) && ! empty( $data['custom_duration'] ) ? $data['custom_duration'] : $duration;

		// Buffer Time Before
		$buffer_time_before = isset( $data['buffer_time_before'] ) && ! empty( $data['buffer_time_before'] ) ? $data['buffer_time_before'] : 0;

		// Buffer Time After
		$buffer_time_after = isset( $data['buffer_time_after'] ) && ! empty( $data['buffer_time_after'] ) ? $data['buffer_time_after'] : 0;

		// Meeting Interval
		$meeting_interval = isset( $data['meeting_interval'] ) && ! empty( $data['meeting_interval'] ) ? $data['meeting_interval'] : 0;

		// Disable Dates

		// Get All Booking Data.
		$bookings = $booking->get( array( 'meeting_dates' => $selected_date ) );
		// $date_time = new DateTimeController( $selected_time_zone );
		$date_time = new DateTimeController( $selected_time_zone );
		$data_time = $date_time->getAvailableTimeData( $meeting_id, $selected_date, $selected_time_zone, $selected_time_format );

	 

		$singlebooking->times = array(
			'start' => $singlebooking->start_time,
			'end'   => $singlebooking->end_time,
		);
		// Return response
		$data = array(
			'status'  => true,
			'booking' => $singlebooking,
			'times'   => $data_time,
			'message' =>  __('Booking Data Successfully Retrieve!', 'hydra-booking'),
		);
		return rest_ensure_response( $data );
	}

	// Get booking Details Data getBookingDetails

	public function getBookingDetailsData($booking_id){
		$booking = new Booking();
		
		$where = array(
			array('id', '=', $booking_id),
		);
		 $bookingsList = $booking->getBookingWithAttendees(  
			$where,
			1,  
		); 
		
		if( empty( $bookingsList ) ){
			return [];
		}
 
		$attendeesData = $bookingsList->attendees;
		$transactions = new Transactions();
		foreach ($attendeesData as $key => $attendee) {

			// json decode others info
			if($attendeesData[$key]->others_info){
				$attendeesData[$key]->others_info = json_decode($attendeesData[$key]->others_info);
			}

			$where = array(
				array('attendee_id', '=', $attendee->id),
			);
			$transaction = $transactions->get( $where, 1 );
			if($transaction != null || !empty($transaction)){ 
				$transaction->transation_history = json_decode($transaction->transation_history);

				$attendeesData[$key]->transaction =  $transaction;
			}

		}

		return  $bookingsList;
	}

	/**
	 * Get Booking Details
	 *
	 * @param $request
	 *
	 * @return mixed
	 * @since 1.0.16
	 * @Author: Sydur Rahman
	 * 
	 */
	public function getBookingDetails( $request ) {
		$booking_id = $request['id']; 

		if(empty($booking_id)){
			return rest_ensure_response(
				array(
					'status'  => false,
					'message' =>  __('Invalid Booking', 'hydra-booking'),
				)
			);
		}
		
		$bookingsList = $this->getBookingDetailsData($booking_id);
		$bookingMeta = new BookingMeta();
		$booking_activity = $bookingMeta->getWithIdKey ( $booking_id, 'booking_activity', null); 
		$get_internal_note = $bookingMeta->getWithIdKey ( $booking_id, 'internal_note', 1);  
 
		$internal_note = isset($get_internal_note->value) ? $get_internal_note->value : '';

		if( empty( $bookingsList ) ){
			return rest_ensure_response(
				array(
					'status'  => false,
					'message' =>  __('Invalid Booking', 'hydra-booking'),
				)
			);
		}


		 $data = array(
			'status'  => true,
			'booking' => $bookingsList,
			'booking_activity' => $booking_activity,
			'internal_note' => $internal_note,
			'message' =>  __('Booking Data Successfully Retrieve!', 'hydra-booking'),
		);
		return rest_ensure_response( $data );
	}


		/**
	 * Export Booking Data as CSV
	 *
	 * @param $request
	 *
	 * @return mixed
	 * @since 1.0.16
	 * @author Sydur Rahman 
	 * 
	 */

	 public function changeBookingDetailsStatus( $request ) {
		$booking_id = $request['booking_id'];
		$status     = $request['status'];
		if ( empty( $booking_id ) || $booking_id == 0 ) {
			return rest_ensure_response(
				array(
					'status'  => false,
					'message' => __('Invalid Booking', 'hydra-booking'),
				)
			);
		}
		 

		// Check if user is already a booking
		$booking = new Booking();  
		// Update Booking Status
		$bookingUpdate = $booking->update( array( 'id' => $booking_id, 'status' => $status ) );

		if( $bookingUpdate ){ 

			if ( 'completed' == $status ) {
				// do_action( 'hydra_booking/after_booking_completed', $single_booking_meta );
				$bookingMeta = new BookingMeta();
				$bookingMeta->add([
					'booking_id' => $booking_id,
					'meta_key' => 'booking_activity',
					'value' => array(
							
							'datetime' => date('M d, Y, h:i A'),
							'title' =>  'Booking has been completed',
							'description' => '',
						)
					]
				);
			}
 
		}
		$bookingsList = $this->getBookingDetailsData($booking_id);
 
		$data = array(
			'status'  => true,
			'booking' => $bookingsList,
			'message' => __('Booking Status Updated Successfully!', 'hydra-booking'),
		);
		return rest_ensure_response( $data );
	}

	/**
	 * Export Booking Data as CSV
	 *
	 * @param $request
	 *
	 * @return mixed
	 * @since 1.0.16
	 * @author Sydur Rahman
	 * 
	 * 
	 */

	 public function  cancelBookingAttendee( $request ) {

		$attendee_id = $request['id'];
		$booking_id = $request['booking_id'];
		$status     =  $request['status'];
		$cancel_reason =  $request['cancel_reason']; 
		if ( empty( $attendee_id ) || $attendee_id == 0 ) {
			return rest_ensure_response(
				array(
					'status'  => false,
					'message' => __('Invalid Attendee', 'hydra-booking'),
				)
			);
		}

		$Attendee = new Attendees();

		 $update_data = array(

			'id' => $attendee_id,
			'status' => $status,
			'reason' => $cancel_reason,
			'cancelled_by' => 'host',
		);

		$attendeeUpdate = $Attendee->update( $update_data );

		if( $attendeeUpdate ){ 
			
			$bookingsList = $this->getBookingDetailsData($booking_id);
			$data = array(
				'status'  => true,
				'booking' => $bookingsList,
				'message' => __('Attendee Status Updated Successfully!', 'hydra-booking'),
			);

			$attendeeBooking =  $Attendee->getAttendeeWithBooking( 
				array(
					array('id', '=', $attendee_id),
				),
				1,
				'DESC'
			 ); 
			 // Add activity 
			$bookingMeta = new BookingMeta();
			$bookingMeta->add([
				'booking_id' => $attendeeBooking->booking_id,
				'meta_key' => 'booking_activity',
				'value' => array(
						 
						'datetime' => date('M d, Y, h:i A'),
						'title' => esc_html(__(  'A attendee has been canceled by host', 'hydra-booking')),
						'description' => $cancel_reason,
					)
				]
			);
			if ( 'canceled' == $status ) {
				do_action( 'hydra_booking/after_booking_canceled', $attendeeBooking );
			}


			return rest_ensure_response( $data );
		}else{
			return rest_ensure_response(
				array(
					'status'  => false,
					'message' => __('Error while updating Attendee Status', 'hydra-booking'),
				)
			);
		}

	 }


	// Update Booking Information
	public function updateBooking() {
		
		$request       = json_decode( file_get_contents( 'php://input' ), true );
		$booking_id    = $request['id'];
		$booking_owner = $request['host'];

		if ( empty( $booking_id ) || $booking_id == 0 ) {
			return rest_ensure_response(
				array(
					'status'  => false,
					'message' =>  __('Invalid Booking', 'hydra-booking'),
				)
			);
		}

		$data = array(
			'id'     => $request['id'],
			'status' => isset( $request['status'] ) ? sanitize_text_field( $request['status'] ) : '',
		);
 
		$booking = new Booking();
		// Booking Update
		 $booking->update( $data );
 
 
		$booking = new Booking();
 
		// Single Booking
		 
		$where = array(
			array('id', '=', $request['id']),
		);
		 $single_booking_meta = $booking->getBookingWithAttendees(  
			$where,
			1,
			'DESC',
		); 


 
		// Update Attendee Status based on booking status

		if( $single_booking_meta->booking_type  == 'one-to-one'){
			$attendees = $single_booking_meta->attendees;
			$AttendeeDB = new Attendees();
			foreach( $attendees as $attendee ){ 
				// Need to apply $this->changeAttendeeStatus() function 
				$this->changeAttendeeStatus( 
					array(
						'attendee_id' => $attendee->id,
						'status' => $request['status'],
					)
				);

			}
		}else{
					
			if ( 'confirmed' == $request['status'] ) {
				do_action( 'hydra_booking/send_booking_with_all_attendees_confirmed', $single_booking_meta );
			}

			if ( 'pending' == $request['status'] ) {
				do_action( 'hydra_booking/send_booking_with_all_attendees_pending', $single_booking_meta );
			}
			if ( 'canceled' == $request['status'] ) { 
				do_action( 'hydra_booking/send_booking_with_all_attendees_canceled', $single_booking_meta );
			}

			if ( 'schedule' == $request['status'] ) {
				do_action( 'hydra_booking/send_booking_with_all_attendees_schedule', $single_booking_meta );
			}
		}
	
		
	

		// Return response
		$data = array(
			'status'           => true, 
			'message'          =>  __('Booking Updated Successfully!', 'hydra-booking'),
		);
		return rest_ensure_response( $data );
	}

	// Change attendee Status

	// Update Booking Bulk Option
	public function updateBulkStatus() {
		$request       = json_decode( file_get_contents( 'php://input' ), true );
		$status    = $request['status'];
		$items    = $request['items'];
		$booking_owner = !empty($request['host']) ? $request['host'] : '';


		$booking = new Booking();
		if($status == 'delete'){
			if(!empty($items)){
				foreach($items as $item){
					$bookingDelete = $booking->delete( $item );
				}
			}

		}else{
			if(!empty($items)){
				foreach($items as $item){
					$data = array(
						'id'     => $item,
						'status' => isset( $status ) ? sanitize_text_field( $status ) : '',
					);
	
					// Booking Update
					$bookingUpdate = $booking->update( $data );
				}
			}
		}
		

		if(!empty($booking_owner)){
			$current_user = get_userdata( $booking_owner );
			// get user role
			$current_user_role = ! empty( $current_user->roles[0] ) ? $current_user->roles[0] : '';
			$current_user_id   = $current_user->ID;
		}

		if ( ! empty( $current_user_role ) && 'tfhb_host' == $current_user_role ) {
			$host         = new Host();
			$HostData     = $host->getHostByUserId( $current_user_id );
			$bookingsList = $booking->get( null, true, false, false, false, false, $HostData->user_id );

		}else{
			$bookingsList = $booking->get( null, true );
		}

		$extractedBookings = array_map(
			function ( $booking ) {
				return array(
					'id'            => $booking->id,
					'title'         => $booking->title,
					'meeting_dates' => $booking->meeting_dates,
					'start_time'    => $booking->start_time,
					'end_time'      => $booking->end_time,
					'status'        => $booking->booking_status,
					'host_id'       => $booking->host_id,
				);
			},
			$bookingsList
		);

		$booking_array = array();
		foreach ( $extractedBookings as $book ) {
			// Convert start and end times to 24-hour format
			$start_time_24hr = gmdate( 'H:i', strtotime( $book['start_time'] ) );
			$end_time_24hr   = gmdate( 'H:i', strtotime( $book['end_time'] ) );

			$dates = explode( ',', $book['meeting_dates'] );
			$first_date = $dates[0];
			$booking_array[] = array(
				'booking_id'   => $book['id'],
				'title'        => $book['title'],
				'start'        => $first_date . 'T' . $start_time_24hr,
				'end'          => $first_date . 'T' . $end_time_24hr,
				'status'       => $book['status'],
				'booking_date' => $first_date,
				'booking_time' => $book['start_time'] . ' - ' . $book['end_time'],
				'host_id'      => $book['host_id'],
			);
		}

		// Return response
		$data = array(
			'status'           => true,
			'booking'          => $bookingsList,
			'booking_calendar' => $booking_array, 
		);
		if($status == 'delete'){
			$data['message'] =  __('Booking Deleted Successfully!', 'hydra-booking');
		}else{
			$data['message'] =  __('Booking Updated Successfully!', 'hydra-booking');
		}
		return rest_ensure_response( $data );
	}

	// Export Booking Data as CSV.
	public function exportBookingDataAs() {
		$request = json_decode( file_get_contents( 'php://input' ), true );
		// 2024-07-03 23:48:25
		$time         = '00:00:00';
		$current_time = '23:59:59';
		// Get Current Date baded on time
		$current_date  = gmdate( 'Y-m-d H:i:s', strtotime( $current_time ) );
		$previous_date = gmdate( 'Y-m-d H:i:s', strtotime( '-1 day', strtotime( $current_date ) ) );

		$booking = new Booking();
		if ( ! empty( $request['date_range'] == 'custom' ) ) {
			// in this format 2024-07-03 23:48:25 form 2024-07-03 request['start_date'] variable
			$current_date  = gmdate( 'Y-m-d H:i:s', strtotime( $request['end_date'] ) );
			$previous_date = gmdate( 'Y-m-d H:i:s', strtotime( $request['start_date'] ) );

		} elseif ( $request['date_range'] == 'today' ) {
			$current_date  = gmdate( 'Y-m-d H:i:s', strtotime( $current_time ) );
			$previous_date = gmdate( 'Y-m-d H:i:s', strtotime( '-1 day', strtotime( $current_date ) ) );
		} elseif ( $request['date_range'] == 'weeks' ) {
			$current_date  = gmdate( 'Y-m-d H:i:s', strtotime( $current_time ) );
			$previous_date = gmdate( 'Y-m-d H:i:s', strtotime( '-7 day', strtotime( $current_date ) ) );
		} elseif ( $request['date_range'] == 'months' ) {  // current month
			// This month end date
			$current_date  = gmdate( 'Y-m-d H:i:s', strtotime( 'last day of this month', strtotime( $current_time ) ) );
			$previous_date = gmdate( 'Y-m-d H:i:s', strtotime( 'first day of last month', strtotime( $current_time ) ) );
		} elseif ( $request['date_range'] == 'years' ) {  // current year
			// This year end date
			$current_date  = gmdate( 'Y-m-d H:i:s', strtotime( 'last day of this year', strtotime( $current_time ) ) );
			$previous_date = gmdate( 'Y-m-d H:i:s', strtotime( 'first day of last year', strtotime( $current_time ) ) );
		}
		if ( $request['date_range'] == 'all' ) {
			$file_name = 'booking-data';

		} else {
			$file_name = 'booking-data-' . gmdate( 'Y-m-d', strtotime( $previous_date ) ) . '-' . gmdate( 'Y-m-d', strtotime( $current_date ) ) . '';

		}

		$current_user = wp_get_current_user();
		// get user role
		$current_user_role = ! empty( $current_user->roles[0] ) ? $current_user->roles[0] : '';
		$current_user_id   = $current_user->ID; 
			$host     = new Host();
			$HostData = $host->getHostByUserId( $current_user_id ); 
	 
		$where = array();
		if($current_user_role != 'administrator'){
			$where[] = array('host_id', '=', $HostData->id);
		} 
		if ( $request['date_range'] == 'all' ) {
			
			$bookingsList = $booking->getBookingWithAttendees($where);
		} else { 
			$where[] = array('created_at', 'BETWEEN', [$previous_date, $current_date]);
			 $bookingsList = $booking->getBookingWithAttendees(  
				$where,
				NULL,
				'DESC',
			);  
		}
 
		
		if('CSV' == $request['type']){
			$booking_array  = array();
			$booking_column = array();
			foreach ( $bookingsList as $key => $book ) {
				
				// unset some data
				unset($book->title);
				unset($book->meeting_price);
				unset($book->payment_currency);
				unset($book->meeting_payment_status);
				unset($book->meeting_type);
				unset($book->host_first_name);
				unset($book->host_last_name);
				unset($book->host_email);
				unset($book->host_time_zone);
				
				if ( $key == 0 ) {
					foreach ( $book as $c_key => $c_value ) {
						$booking_column[] = $c_key;
					}
				}
				$book->attendees = json_encode($book->attendees); 
				
				$booking_array[] = (array) $book;
			} 

			ob_start();
			$file = fopen( 'php://output', 'w' );
			fputcsv( $file, $booking_column );

			foreach ( $booking_array as $booking ) {
				fputcsv( $file, $booking );
			}

			fclose( $file );
			$data = ob_get_clean();
			// Return response
			$data = array(
				'status'    => true,
				'data'      => $data,
				'file_name' => $file_name.'.csv',
				'message'   =>  __('Booking Data Exported Successfully!', 'hydra-booking'),
			);
			return rest_ensure_response( $data );
		}elseif('iCal' == $request['type']){
			// Set the correct headers for .ics file
			$BookingBookmarks = new BookingBookmarks();
			$ical = $BookingBookmarks->generateFullBookingICS($bookingsList);
			
			 
			// Return response
			$data = array(
				'status'    => true,
				'data'      => $ical,
				'file_name' => $file_name.'.ics',
				'message'   =>  __('Booking Data Exported Successfully!', 'hydra-booking'),
			);
			return rest_ensure_response( $data );

		}

		
	}

	
}