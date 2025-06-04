<?php
namespace HydraBooking\Admin\Controller;
// exit
if ( ! defined( 'ABSPATH' ) ) { exit; }

// Use Namespace
use HydraBooking\Admin\Controller\RouteController;
// Use DB
use HydraBooking\DB\Host;
use HydraBooking\DB\Availability;
use HydraBooking\DB\Meeting;
use HydraBooking\DB\Booking;
use HydraBooking\DB\Transactions;
use HydraBooking\Admin\Controller\DateTimeController;

// exit
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

class DashboardController {

	public function __construct() { 
		
	}
	public function create_endpoint() {
		register_rest_route(
			'hydra-booking/v1',
			'/dashboard',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'getDashboardsData' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
			)
		);

		register_rest_route(
			'hydra-booking/v1',
			'/dashboard/statistics',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'getDashboardsStatisticsData' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
			)
		);
 
	}


	// get dashboard data
	public function getDashboardsData() {

		$request   = json_decode( file_get_contents( 'php://input' ), true );
		$days      = $request['days']; // exp 1
		$from_date = $request['from_date']; // exp 2021-09-01
		$to_date   = $request['to_date']; // exp 2021-09-01

		// calculate Days based on form day and to day
		$days = $from_date != null && $to_date != null ? ( strtotime( $to_date ) - strtotime( $from_date ) ) / ( 60 * 60 * 24 ) : $days; // exp 1

		// how to get current date start time 12:00:00 and end time 23:59:59
		$current_date = $to_date != null ? gmdate( 'Y-m-d 23:59:59', strtotime( $to_date ) ) : gmdate( 'Y-m-d 23:59:59' ); // exp 2021-09-01

		// $current_date  = gmdate('Y-m-d H:i:s'); // exp 2021-09-01
		$previous_date        = $days != 1 ? gmdate( 'Y-m-d 00:00:00', strtotime( '-' . $days . ' days' ) ) : gmdate( 'Y-m-d 00:00:00' ); // exp 2021-09-01
		$previous_date        = $from_date != null ? gmdate( 'Y-m-d 00:00:00', strtotime( $from_date ) ) : $previous_date; // exp 2021-09-01
		$previous_date_before = $days != 1 ? gmdate( 'Y-m-d 00:00:00', strtotime( '-' . ( $days * 2 ) . ' days' ) ) : gmdate( 'Y-m-d 00:00:00', strtotime( '-1 days' ) ); // exp 2021-09-01

		// Get booking
		$booking = new Booking();

		$current_user = wp_get_current_user();
		// get user role
		$current_user_role = ! empty( $current_user->roles[0] ) ? $current_user->roles[0] : '';
		$current_user_id   = $current_user->ID;
		$host              = new Host();
		$HostData          = $host->getHostByUserId( $current_user_id );

		$bookings_where = array(
			array('created_at', 'BETWEEN',  array($previous_date, $current_date)), 
		);
		if(!empty($current_user_role) && 'tfhb_host' == $current_user_role){
			$bookings_where[] = array('host_id', '=', $HostData->id);
		}
		$bookings = $booking->getBookingWithAttendees( 
			$bookings_where,
			null,
			'DESC' 
		); 

		 

		// Previous Date Booking
		$previous_date_bookings_where = array(
			array('created_at', 'BETWEEN',  array($previous_date_before, $previous_date)), 
		);
		if(!empty($current_user_role) && 'tfhb_host' == $current_user_role){
			$previous_date_bookings_where[] = array('host_id', '=', $HostData->id);
		}
		$previous_date_bookings = $booking->getBookingWithAttendees( 
			$previous_date_bookings_where,
			null,
			'DESC' 
		); 
 

		// Upcoming Booking
		$upcoming_booking_where = array(
			array('meeting_dates', '>=', $current_date), 
		);
		if(!empty($current_user_role) && 'tfhb_host' == $current_user_role){
			$upcoming_booking_where[] = array('host_id', '=', $HostData->id);
		}
		$upcoming_booking = $booking->getBookingWithAttendees( 
			$upcoming_booking_where,
			5,
			'DESC' 
		); 


		 
		// Recent Booking
		$recent_booking_where = array();
		if(!empty($current_user_role) && 'tfhb_host' == $current_user_role){
			$recent_booking_where[] = array('host_id', '=', $HostData->id);
		}
		$recent_booking = $booking->getBookingWithAttendees( 
			$recent_booking_where,
			5,
			'DESC' 
		); 
		// count total Booking and collect percentage
		$total_bookings['total']      = count( $bookings );
		$total_bookings_previous      = count( $previous_date_bookings );
		$total_bookings['percentage'] = $total_bookings_previous != 0 ? 100 * ( $total_bookings['total'] - $total_bookings_previous ) / $total_bookings_previous : 100;
		// make only 2 decimal after dots exp 10.00
		$total_bookings['percentage'] = number_format( $total_bookings['percentage'], 0 );
		$total_bookings['percentage'] = $total_bookings['percentage'] == 100 ? 0 : $total_bookings['percentage'];
		$total_bookings['growth']     = $total_bookings['percentage'] < 0 ? 'decrease' : 'increase';

		// total cancelled Booking and collect percentage
		
		$cancelled['total']      = count(
			array_filter(
				$bookings,
				function ( $booking ) {
					return $booking->status == 'canceled';
				}
			)
		);
		$cancelled_previous      = count(
			array_filter(
				$previous_date_bookings,
				function ( $booking ) {
					return $booking->status == 'canceled';
				}
			)
		);
		$cancelled['percentage'] = $cancelled_previous != 0 ? 100 * ( $cancelled['total'] - $cancelled_previous ) / $cancelled_previous : 100;
		// make only 2 decimal after dots exp 10.00
		$cancelled['percentage'] = number_format( $cancelled['percentage'], 0 );
		$cancelled['growth']     = $cancelled['percentage'] < 0 ? 'decrease' : 'increase';

		// count wich status is completed for Bookings array
		$completed['total']      = count(
			array_filter(
				$bookings,
				function ( $booking ) {
					return $booking->status == 'completed';
				}
			)
		);
		$completed_previous      = count(
			array_filter(
				$previous_date_bookings,
				function ( $booking ) {
					return $booking->status == 'completed';
				}
			)
		);
		$completed['percentage'] = $completed_previous != 0 ? 100 * ( $completed['total'] - $completed_previous ) / $completed_previous : 100;
		// make only 2 decimal after dots exp 10.00
		$completed['percentage'] = number_format( $completed['percentage'], 0 );
		$completed['growth']     = $completed['percentage'] < 0 ? 'decrease' : 'increase';

		// Total Earning	
		$transactions = new Transactions();
		$earning      = $transactions->totalEarning($previous_date,  $current_date, ! empty( $current_user_role ) && 'tfhb_host' == $current_user_role ? $HostData->id : false); 

		$previous_earning = $transactions->totalEarning($previous_date_before,  $previous_date, ! empty( $current_user_role ) && 'tfhb_host' == $current_user_role ? $HostData->id : false);
		// tfhb_print_r($earning);
		$total_earning['total']      = !empty($earning) ? $earning : 0;
		// make only 2 decimal after dots exp 10.00
		$total_earning['total'] = number_format( $total_earning['total'], 2 );
		$total_earning_previous      = $previous_earning;
		$total_earning['percentage'] = $total_earning_previous != 0 ? 100 * ( $total_earning['total'] - $total_earning_previous ) / $total_earning_previous : 100;
		// make only 2 decimal after dots exp 10.00
		$total_earning['percentage'] = number_format( $total_earning['percentage'], 0 );
		$total_earning['growth']     = $total_earning['percentage'] < 0 ? 'decrease' : 'increase';

		$data = array(
			'status'                   => true,
			'total_bookings'           => $total_bookings,
			'total_cancelled_bookings' => $cancelled,
			'total_completed_bookings' => $completed,
			'upcoming_booking'         => $upcoming_booking,
			'recent_booking'           => $recent_booking,
			'total_earning'            => $total_earning,
			'days'                     => $days,
		);

		return rest_ensure_response( $data );
	}
	// get dashboard data
	public function getDashboardsStatisticsData() {

		$request       = json_decode( file_get_contents( 'php://input' ), true );
		$days          = $request['statistics_days']; // exp 2021-09-01
		$current_date  = gmdate( 'Y-m-d 23:59:59' ); // exp 2021-09-01
		$previous_date = gmdate( 'Y-m-d 00:00:00', strtotime( '-' . $days . ' days' ) ); // exp 2021-09-01

		$current_user = wp_get_current_user();
		// get user role
		$current_user_role = ! empty( $current_user->roles[0] ) ? $current_user->roles[0] : '';
		$current_user_id   = $current_user->ID;
		$host              = new Host();
		$HostData          = $host->getHostByUserId( $current_user_id );

		$booking                          = new Booking();
		$statistics['total_bookings']     = array();
		$statistics['cancelled_bookings'] = array();
		$statistics['completed_bookings'] = array();

		$statistics = array();
		if ( $days == 7 ) {
			// store label as date
			for ( $i = 0; $i < 7; $i++ ) {
				$statistics['label'][] = gmdate( 'd M, y', strtotime( '-' . $i . ' days' ) );
			}
			$statistics['label'] = array_reverse( $statistics['label'] );
		}
		if ( $days == 30 ) { // This Month every Days
			// store label as date
			// Count First how many days in this month
			$days_in_month = cal_days_in_month( CAL_GREGORIAN, gmdate( 'm' ), gmdate( 'Y' ) );
			$currentMonth  = gmdate( 'm' );
			$currentYear   = gmdate( 'Y' );
			// Get Current month

			for ( $day = 1; $day <= $days_in_month; $day++ ) {
				$statistics['label'][] = gmdate( 'd M, y', strtotime( "$currentYear-$currentMonth-$day" ) );
			}
		}
		if ( $days == 3 ) {  // last 3 Months
			// store label as Month Name
			for ( $i = 0; $i <= 2; $i++ ) {
				$statistics['label'][] = gmdate( 'F', strtotime( '-' . $i . ' months' ) );
			}
			$statistics['label'] = array_reverse( $statistics['label'] );

		}
		if ( $days == 12 ) {  // This year as month
			// store label as Month Name
			$statistics['label'] = array( 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December' );
		} else {
			// $statistics['label'] = array_reverse($statistics['label']);
		}

		$dateTime = new DateTimeController('UTC');
		foreach ( $statistics['label'] as $key => $value ) { 
	 
			// $date = $value;
			// $next_date = $key != 0 ? $statistics['label'][$key - 1] : $current_date;
			if ( $days == 12 || $days == 3 ) {
				$date      = gmdate( 'Y-m-d', strtotime( 'first day of ' . $value ) );
				$next_date = gmdate( 'Y-m-d', strtotime( 'last day of ' . $value ) );
			}
			if ( $days == 30 || $days == 7 ) { // value is a date exp 2021-09-01

				$value = $dateTime->convertDateTimeFormat( $value, 'd M, y', 'Y-m-d' ); 
				$date      =  $value;
				$next_date =  $value;
			} 
			// Get booking
			$bookings_where = array(
				array('created_at', 'BETWEEN',  array($date . ' 00:00:00', $next_date . ' 23:59:59')), 
			);
			if(!empty($current_user_role) && 'tfhb_host' == $current_user_role){
				$bookings_where[] = array('host_id', '=', $HostData->id);
			}
			$bookings = $booking->getBookingWithAttendees( 
				$bookings_where,
				null,
				'DESC' 
			); 
	
			$statistics['total_bookings'][]     = count( $bookings );
			$statistics['cancelled_bookings'][] = count(
				array_filter(
					$bookings,
					function ( $booking ) {
						return $booking->status == 'canceled';
					}
				)
			);
			$statistics['completed_bookings'][] = count(
				array_filter(
					$bookings,
					function ( $booking ) {
						return $booking->status == 'completed';
					}
				)
			);
		}

		$data = array(
			'status'     => true,
			'statistics' => $statistics, 
		);

		return rest_ensure_response( $data );
	}

 
}
