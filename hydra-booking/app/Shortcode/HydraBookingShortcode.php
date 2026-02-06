<?php
namespace HydraBooking\App\Shortcode;

// use Classes
use HydraBooking\DB\Meeting;
use HydraBooking\Admin\Controller\DateTimeController;
use HydraBooking\Admin\Controller\Notification;
use HydraBooking\DB\Booking;
use HydraBooking\DB\Attendees;
use HydraBooking\DB\Host;
use HydraBooking\Services\Integrations\Woocommerce\WooBooking;
use HydraBooking\Services\Integrations\Zoom\ZoomServices;
use HydraBooking\DB\Transactions;
use HydraBooking\DB\BookingMeta;


class HydraBookingShortcode {
	public function __construct() {

		// Add Shortcode
		add_shortcode( 'hydra_booking', array( $this, 'hydra_booking_shortcode' ) );

		// Add Action
		add_action( 'hydra_booking/after_meeting_render', array( $this, 'after_meeting_render' ) );
		add_action( 'hydra_booking/before_meeting_render', array( $this, 'before_meeting_render' ) );

		// Already Booked Times
		add_action( 'wp_ajax_nopriv_tfhb_already_booked_times', array( $this, 'tfhb_already_booked_times_callback' ) );
		add_action( 'wp_ajax_tfhb_already_booked_times', array( $this, 'tfhb_already_booked_times_callback' ) );

		// Form Submit
		add_action( 'wp_ajax_nopriv_tfhb_meeting_form_submit', array( $this, 'tfhb_meeting_form_submit_callback' ) );
		add_action( 'wp_ajax_tfhb_meeting_form_submit', array( $this, 'tfhb_meeting_form_submit_callback' ) );

		// Booking Cancel
		add_action( 'wp_ajax_nopriv_tfhb_meeting_form_cencel', array( $this, 'tfhb_meeting_form_cencel_callback' ) );
		add_action( 'wp_ajax_tfhb_meeting_form_cencel', array( $this, 'tfhb_meeting_form_cencel_callback' ) );

	
		// Paypal Payment Confirmation
		add_action( 'wp_ajax_nopriv_tfhb_meeting_paypal_payment_confirmation', array( $this, 'tfhb_meeting_paypal_payment_confirmation_callback' ) );
		add_action( 'wp_ajax_tfhb_meeting_paypal_payment_confirmation', array( $this, 'tfhb_meeting_paypal_payment_confirmation_callback' ) );
 
		// Create Zoom Meeting
		
	}

	/**
	 * Generate a cryptographically secure token.
	 *
	 * @param int $bytes
	 *
	 * @return string
	 */
	private function generate_secure_token( $bytes = 16 ) {
		try {
			$token = \bin2hex( \random_bytes( $bytes ) );
		} catch ( \Exception $exception ) {
			$token = \wp_generate_password( $bytes * 2, false, false );
		}

		return $token;
	}

 

	public function hydra_booking_shortcode( $atts ) {

		// Country List form josn file

		if ( ! isset( $atts['id'] ) || $atts['id'] == 0 ) {
			return  __( 'Please provide a valid Meeting id', 'hydra-booking' );
		}

		// Attributes
		$atts = shortcode_atts(
			array(
				'id'   => 0,
				'hash' => '',
				'type' => 'create',
			),
			$atts,
			'hydra_booking'
		);

		$calendar_id = $atts['id'];

		// Get Meeting
		$meeting     = new Meeting();
		$MeetingData = $meeting->get( $calendar_id );

		if ( ! $MeetingData ) {
			return 'Invalid Meeting';
		}

		$meta_data        = get_post_meta( $MeetingData->post_id, '__tfhb_meeting_opt', true );
		$general_settings = get_option( '_tfhb_general_settings', true ) ? get_option( '_tfhb_general_settings', true ) : array();
	
		// Reschedule Booking
		$booking_data = array();

		if ( ! empty( $atts['hash'] ) && 'reschedule' == $atts['type'] ) {

			$Attendee = new Attendees();
			$attendeeBooking =  $Attendee->getAttendeeWithBooking( 
				array(
					array('hash', '=',get_query_var( 'hash' )),
				),
				1,
			);

			if ( ! $attendeeBooking ) {
				return  __( 'Invalid Booking', 'hydra-booking' );
			}

			$booking_data = $attendeeBooking;
		}
 
		// GetHost meta Data
		$host_id   = isset( $meta_data['host_id'] ) ? $meta_data['host_id'] : 0;
		

	
		$hostData =  new Host();
		$host_meta = (array) $hostData->getHostById( $host_id );  
		
		// Time Zone
		$DateTimeZone = new DateTimeController( 'UTC' );
		$time_zone    = $DateTimeZone->TimeZone();

		// Start Buffer
		ob_start();

		load_template(
			TFHB_PATH . '/app/Content/calendar.php',
			false,
			array(
				'meeting'      => $meta_data,
				'host'         => $host_meta,
				'time_zone'    => $time_zone,
				'booking_data' => $booking_data,
				'general_settings' => $general_settings,
				'atts' => $atts,
			)
		);
		// Return Buffer
		return ob_get_clean();
	}

	// Before Render
	public function before_meeting_render() {
		// Enqueue Styles
		if ( ! wp_style_is( 'tfhb-select2-style', 'enqueued' ) ) {
			wp_enqueue_style( 'tfhb-select2-style' );
		}
	}

	// After Render
	public function after_meeting_render( $data ) {
		if ( ! is_array( $data ) || empty( $data ) ) {
			return;
		}

		$id      = isset( $data['id'] ) ? $data['id'] : 0;
		$host_id = isset( $data['host_id'] ) ? $data['host_id'] : 0;
		$user_id = isset( $data['user_id'] ) ? $data['user_id'] : 0;

		// Check if id is not set
		if ( 0 === $id && 0 === $host_id ) {
			return;
		}

		
		if ( isset( $data['availability_type'] ) && 'settings' === $data['availability_type'] ) {
			$_tfhb_availability_settings = get_user_meta( $user_id, '_tfhb_host', true ); 
			
			if(isset($_tfhb_availability_settings['availability_type']) && $_tfhb_availability_settings['availability_type'] == 'settings'){
				$host_settings_availability_id = $_tfhb_availability_settings['availability_id'];
				$_tfhb_availability_settings =  get_option( '_tfhb_availability_settings' );

				if ( is_array($_tfhb_availability_settings)  ) { 
					$key = array_search($host_settings_availability_id, array_column($_tfhb_availability_settings, 'id'));
					//  _tfhb_availability_settings index id wich is match with host settings availability id
					if(isset($_tfhb_availability_settings[ $key ])){

						$availability_data = $_tfhb_availability_settings[ $key ];
					}else{
						$availability_data = isset( $data['availability_custom'] ) ? $data['availability_custom'] : array();
					} 
				} else {
					$availability_data = isset( $data['availability_custom'] ) ? $data['availability_custom'] : array();
				} 
			}elseif (isset($_tfhb_availability_settings['availability']) &&  in_array( $data['availability_id'], array_keys( $_tfhb_availability_settings['availability'] ) ) ) {
				
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
		$questions_type = isset( $data['questions_type'] ) ? $data['questions_type'] : array();
		$questions_form_type = isset( $data['questions_form_type'] ) ? $data['questions_form_type'] : array();
		$questions_form = isset( $data['questions_form'] ) ? $data['questions_form'] : array(); 

		// Duration
		$duration = isset( $data['duration'] ) && ! empty( $data['duration'] ) ? $data['duration'] : 30;

		$duration = isset( $data['custom_duration'] ) && ! empty( $data['custom_duration'] ) ? $data['custom_duration'] : $duration;

		// Buffer Time Before
		$buffer_time_before = isset( $data['buffer_time_before'] ) && ! empty( $data['buffer_time_before'] ) ? $data['buffer_time_before'] : 0;

		// Buffer Time After
		$buffer_time_after = isset( $data['buffer_time_after'] ) && ! empty( $data['buffer_time_after'] ) ? $data['buffer_time_after'] : 0;

		// Meeting Interval
		$meeting_interval = isset( $data['meeting_interval'] ) && ! empty( $data['meeting_interval'] ) ? $data['meeting_interval'] : 0;

		$payment_status = isset( $data['payment_status'] ) && ! empty( $data['payment_status'] ) ? $data['payment_status'] : 0;

	
		// Integration Settings
		$_tfhb_integration_settings = get_option( '_tfhb_integration_settings' );
		$tfhb_paypal = isset( $_tfhb_integration_settings['paypal'] ) ? $_tfhb_integration_settings['paypal'] : array();
		$tfhb_stripe = isset( $_tfhb_integration_settings['stripe'] ) ? $_tfhb_integration_settings['stripe'] : array();
	 

		if(isset($tfhb_paypal['status']) && $tfhb_paypal['status'] == 1 &&  ! wp_script_is( 'tfhb-paypal-script', 'enqueued' )){ 
			wp_enqueue_script( 'tfhb-paypal-sdk',  ); 
		}

		if(isset($tfhb_stripe['status']) && $tfhb_stripe['status'] == 1){ 
			wp_enqueue_script( 'tfhb-stripe-script',  ); 
		}
 

		// Enqueue Select2
		if ( ! wp_script_is( 'tfhb-select2-script', 'enqueued' ) ) {
			wp_enqueue_script( 'tfhb-select2-script' );
		}
		
		// Enqueue Scripts scripts 
		wp_enqueue_script( 'tfhb-app-script-app', TFHB_URL . 'assets/app/js/app.js', array( 'jquery', 'tfhb-app-script', 'wp-i18n' ), TFHB_VERSION, true );
		wp_set_script_translations( 'tfhb-app-script-app', 'hydra-booking'  );
		
		$data = array(
			'meeting_id'              => $id,
			'host_id'                 => $host_id,
			'calander_available_time_slot'                 => array(),
			'duration'                => $duration,
			'payment_status'          => $payment_status,
			'meeting_interval'        => $meeting_interval,
			'buffer_time_before'      => $buffer_time_before,
			'buffer_time_after'       => $buffer_time_after,
			'availability'            => $availability_data,
			'availability_range'      => $availability_range,
			'availability_range_type' => $availability_range_type,
			'questions_type' => $questions_type,
			'questions_form_type' => $questions_form_type,
			'questions_form' => $questions_form,

		);
		

		// Localize Script
		wp_localize_script(
			'tfhb-app-script-app',
			'tfhb_app_booking_' . $id,
			$data
		);
	}


	// Form Submit Callback
	public function tfhb_meeting_form_submit_callback() {

		// Checked Nonce validation
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'tfhb_nonce' ) ) {
			wp_send_json_error( array( 'message' => __('Nonce verification failed', 'hydra-booking') ) );
		}

		// Check if the request is POST
		if ( 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
			wp_send_json_error( array( 'message' => __('Invalid request method', 'hydra-booking') ) );
		}

		// Check if the request is not empty
		if ( empty( $_POST ) ) {
			wp_send_json_error( array( 'message' => __('Invalid request', 'hydra-booking') ) );
		}

		if ( $_POST['meeting_id'] == 0 ) {
			wp_send_json_error( array( 'message' => __('Invalid Meeting ID', 'hydra-booking') ) );
		}

		$data     = array();
		$attendee_data     = array();
		$response = array();

		$booking = new Booking();
		$attendees = new Attendees();

		// General Settings
		$general_settings = get_option( '_tfhb_general_settings', true ) ? get_option( '_tfhb_general_settings', true ) : array();
		
		

		// Generate Meeting Hash Based on start time and end time and Date And Meeting id + random number
		if ( isset( $_POST['booking_hash'] ) ) {

			$meeting_hash = sanitize_text_field( $_POST['booking_hash'] );

		} else {

			$meeting_hash = $this->generate_secure_token();

		}

		// sanitize the data
		$data['meeting_id'] = isset( $_POST['meeting_id'] ) ? sanitize_text_field( $_POST['meeting_id'] ) : 0;

		$data['meeting_dates']      = isset( $_POST['meeting_dates'] ) ? sanitize_text_field( $_POST['meeting_dates'] ) : '';
		$meeting     = new Meeting();
		$MeetingData = $meeting->get( $data['meeting_id'] );
 
	
		$meta_data = get_post_meta( $MeetingData->post_id, '__tfhb_meeting_opt', true );

		if($meta_data['meeting_type'] == 'one-to-group' && tfhb_is_pro_active() == false ){
			 
			wp_send_json_error( array( 'message' => esc_html(__('Please upgrade to pro version for group meeting', 'hydra-booking')) ) );
			wp_die();
		} 
		// data shuld a  '2024-12-06, 2024-12-06, 2024-12-06' 
		// get the first date
		$meeting_date = $data['meeting_dates'];
		$date_time = new DateTimeController( 'UTC' );
		$availability_data = $date_time->GetAvailabilityData($MeetingData);  
		$availability_time_zone = $availability_data['time_zone'];
		

		
		$start_time = isset( $_POST['meeting_time_start'] ) ? sanitize_text_field( $_POST['meeting_time_start'] ) : '';
		$end_time = isset( $_POST['meeting_time_end'] ) ? sanitize_text_field( $_POST['meeting_time_end'] ) : '';
	
		$start_time = $date_time->convert_time_based_on_timezone( $meeting_date, $start_time, $_POST['attendee_time_zone'], $availability_time_zone , '' );
		
		$end_time   = $date_time->convert_time_based_on_timezone($meeting_date, $end_time, $_POST['attendee_time_zone'], $availability_time_zone , '' );
	 
		$data['meeting_dates'] = $start_time->format('Y-m-d');
		 
		$start_time =  $start_time->format( 'h:i A' );
		$end_time =  $end_time->format( 'h:i A' );

		// Checking Hold Booking
		$hold_booking = new Booking();
		$get_hold_booking = $hold_booking->getHoldBooking( $data['meeting_id'], $data['meeting_dates'], $start_time, $end_time );
		if(!empty($get_hold_booking)){
			wp_send_json_error( array( 'message' => esc_html(__('This time slot is currently on hold. Please try again later or choose a different time.', 'hydra-booking')) ) );
		}
 
		$data['host_id']            = isset( $_POST['host_id'] ) ? sanitize_text_field( $_POST['host_id'] ) : 0;
		$data['attendee_id']        = isset( $_POST['attendee_id'] ) ? sanitize_text_field( $_POST['attendee_id'] ) : 0;
		$data['hash']               = $meeting_hash; 
	
		$data['availability_time_zone']      = isset( $availability_time_zone ) ? sanitize_text_field( $availability_time_zone ) : '';
		$data['start_time']         = isset( $start_time ) ? sanitize_text_field( $start_time ) : '';
		$data['end_time']           = isset( $end_time ) ? sanitize_text_field( $end_time ) : '';
		$data['slot_minutes']       = isset( $_POST['slot_minutes'] ) ? sanitize_text_field( $_POST['slot_minutes'] ) : '';
		$data['duration']           = isset( $_POST['duration'] ) ? sanitize_text_field( $_POST['duration'] ) : 0;
		

		// Attendee Data
		$attendee_data['hash'] =  $this->generate_secure_token();
		$attendee_data['meeting_id'] = isset( $data['meeting_id'] ) ? sanitize_text_field( $data['meeting_id'] ) : 0;
		$attendee_data['host_id']            = isset( $data['host_id'] ) ? sanitize_text_field( $data['host_id'] ) : 0;
		$attendee_data['attendee_time_zone'] = isset( $_POST['attendee_time_zone'] ) ? sanitize_text_field( $_POST['attendee_time_zone'] ) : 0;
		$attendee_data['attendee_name']      = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';
		$attendee_data['email']              = isset( $_POST['email'] ) ? sanitize_text_field( $_POST['email'] ) : '';
		$attendee_data['address']            = isset( $_POST['address'] ) ? sanitize_text_field( $_POST['address'] ) : '';
		$attendee_data['others_info']        = array();
		$questions                  = isset( $_POST['question'] ) ? $_POST['question'] : array();

		// Contact form fields
		if ( $meta_data['questions_type'] == 'existing' ) {

			if ( $meta_data['questions_form_type'] == 'wpcf7' ) {
				$questions = array_filter(
					$_POST,
					function ( $key ) {
						return strpos( $key, 'question_' ) === 0;
					},
					ARRAY_FILTER_USE_KEY
				);
			}

			if ( $meta_data['questions_form_type'] == 'fluent-forms' ) {

				$questions = array_filter(
					$_POST,
					function ( $key ) {
						return strpos( $key, 'question_' ) === 0;
					},
					ARRAY_FILTER_USE_KEY
				);

				if ( isset( $_POST['names'] ) && is_array( $_POST['names'] ) ) {
					$attendee_data['attendee_name'] = $_POST['names']['first_name'] . ' ' . $_POST['names']['last_name'];
				}
			}

			if ( $meta_data['questions_form_type'] == 'forminator' ) {

				$attendee_data['email'] = $_POST['email-1'];
				unset( $_POST['email-1'] );

				$attendee_names = array_filter(
					$_POST,
					function ( $key ) {
						return strpos( $key, 'name-1' ) === 0;
					},
					ARRAY_FILTER_USE_KEY
				);

				$attendee_data['attendee_name'] = '';
				foreach ( $attendee_names as $key => $name ) {
					$attendee_data['attendee_name'] .= $name . ' ';
					unset( $_POST[ $key ] );
				}

				$address = array_filter(
					$_POST,
					function ( $key ) {
						return strpos( $key, 'address-1' ) === 0;
					},
					ARRAY_FILTER_USE_KEY
				);

				foreach ( $address as $key => $name ) {
					$attendee_data['address'] .= $name . ' ';
					unset( $_POST[ $key ] );
				}
				$questions = $_POST;
				unset( $questions['_wp_http_referer'] );
				unset( $questions['action'] );
				unset( $questions['current_url'] );
				unset( $questions['form_id'] );
				unset( $questions['form_type'] );
				unset( $questions['forminator_nonce'] );
				unset( $questions['nonce'] );
				unset( $questions['page_id'] );
				unset( $questions['referer_url'] );
				unset( $questions['render_id'] );
				unset( $questions['nonce'] );
				unset( $questions['meeting_id'] );
				unset( $questions['host_id'] );
				unset( $questions['meeting_dates'] );
				unset( $questions['meeting_duration'] );
				unset( $questions['meeting_time_start'] );
				unset( $questions['meeting_time_end'] );
				unset( $questions['recurring_maximum'] );
				unset( $questions['attendee_time_zone'] );
				unset( $questions['payment_type'] );
				unset( $questions['meeting_price'] );
				unset( $questions['payment_amount'] );
				unset( $questions['stripe_public_key'] );
				unset( $questions['payment_currency'] );

			}
		}

		if ( isset( $questions ) && ! empty( $questions ) ) {
			foreach ( $questions as $key => $question ) {
				$attendee_data['others_info'][ $key ] = sanitize_text_field( $question );
			}
		}
		$attendee_data['country']    = isset( $_POST['country'] ) ? sanitize_text_field( $_POST['country'] ) : '';
		$attendee_data['ip_address'] = isset( $_POST['ip_address'] ) ? sanitize_text_field( $_POST['ip_address'] ) : '';
		$attendee_data['device']     = isset( $_POST['device'] ) ? sanitize_text_field( $_POST['device'] ) : '';


		// Recurring Meeting
		if ( isset( $meta_data['recurring_status'] ) && $meta_data['recurring_status'] == true ) {
			$meeting_dates          = isset( $_POST['meeting_dates'] ) ? sanitize_text_field( $_POST['meeting_dates'] ) : '';

			$data['meeting_dates'] = apply_filters( 'hydra_booking/calculate_recurring_meeting_dates', $meeting_dates, $meta_data );
 
		}

		// Meeting Location
		$data['meeting_locations'] = array();
		$meeting_location = is_array($meta_data['meeting_locations']) ? $meta_data['meeting_locations'] : array();
		if ( isset( $meeting_location ) && ! empty( $meeting_location ) ) {
			foreach ( $meeting_location as $key => $location ) {
				$location_address = $location['address'];
				if($location['location'] == 'In Person (Attendee Address)'){
					$location_address = $attendee_data['address'];
				}
				if($location['location'] == 'Attendee Phone Number'){
					$location_address = isset($attendee_data['others_info']['Phone']) ? $attendee_data['others_info']['Phone'] : '';
				}

				$data['meeting_locations'][ $location['location'] ] = array(
					'location' => sanitize_text_field( $location['location'] ),
					'address'  => sanitize_text_field( $location_address ),
				);
			}
		}
		
		$data['cancelled_by'] = '';
		$data['reason']       = isset( $_POST['reason'] ) ? sanitize_text_field( $_POST['reason'] ) : '';
		$data['booking_type'] = $meta_data['meeting_type'];

		// Payment Method
		if ( true == $meta_data['payment_status'] ) {

			$attendee_data['payment_method'] = $meta_data['payment_method'];
			$attendee_data['payment_status'] = 'pending';

		} else {

			$attendee_data['payment_method'] = 'free';
			$attendee_data['payment_status'] = 'completed';

		}

		$booking_status = 'pending';
		if(isset($general_settings['booking_status']) && $general_settings['booking_status'] == 1){
			$booking_status = 'confirmed';
		}
		if(!isset($general_settings['booking_status'])){
			$booking_status = 'confirmed';
		}

		if(!$attendee_data['payment_method'] == 'free' && $attendee_data['payment_status'] == 'pending'){
			$booking_status = 'pending';
		}
		if(true == $meta_data['payment_status'] && 'woo_payment'==$meta_data['payment_method'] && !empty($meta_data['payment_meta']['product_id'])){
			$booking_status = 'hold';
		}

		$data['status'] = $booking_status;
		$attendee_data['status'] = $booking_status;

		// Before Booking Hooks Action
		do_action( 'hydra_booking/before_booking_confirmation', $data );

		// Filter Hooks After Booking
		$data = apply_filters( 'hydra_booking/after_booking_confirmation_filters', $data );

		// GetHost meta Data
		$host_id   = isset( $meta_data['host_id'] ) ? $meta_data['host_id'] : 0;
		$host_meta = get_user_meta( $host_id, '_tfhb_host', true );

 
		
		// $check_booking = $booking->getCheckBooking( $data['meeting_id'], $data['meeting_dates'], $data['start_time'], $data['end_time'] );
 
		$where = array(
			array('meeting_id', '=', $data['meeting_id']),
			array('meeting_dates', '=', $data['meeting_dates']),
			array('start_time', '=', $data['start_time']),
			array('end_time', '=', $data['end_time']),
			array('status', '!=', 'canceled'),
		);
		$check_booking = $booking->getBookingWithAttendees( 
			$where,
			1,
			'DESC' 
		);  
		
		if ( 'one-to-group' == $meta_data['meeting_type'] ) {
			if(!empty($check_booking)){
				$attendee_data['booking_id'] = $check_booking->id;
				$max_book_per_slot = isset( $meta_data['max_book_per_slot'] ) ? $meta_data['max_book_per_slot'] : 1;
				$attendees = $check_booking->attendees; 
				if ( count($attendees) >= $max_book_per_slot ) {
					wp_send_json_error( array( 'message' => esc_html(__('Already Booked', 'hydra-booking')) ) );
				}
 
			}
			
		} elseif ( $check_booking  ) {
 
			wp_send_json_error( array( 'message' => esc_html(__('Already Booked', 'hydra-booking')) ) );
		}

	
		// Get booking Data using Hash
		if ( isset( $_POST['action_type'] ) && 'reschedule' == $_POST['action_type'] ) {
			
			// if general_settings['allowed_reschedule_before_meeting_start'] is available exp 100 then check the time before reschedule
			$this->tfhb_reschedule_booking( $data, $attendee_data,$meeting_hash, $meta_data,  $general_settings, $check_booking ); 
		}
		$this->tfhb_create_new_booking($data, $attendee_data, $meta_data, $MeetingData, $host_meta);



	}

	/**
	 * Create New Booking
	 * @param $data
	 * @return void
	 */
	public function tfhb_create_new_booking( $data, $attendee_data, $meta_data, $MeetingData, $host_meta  ) {
		// Get Booking Data
		$booking = new Booking();

		// Booking Frequency
		$current_user_booking = $booking->get( array( 'meeting_id' => $data['meeting_id'], 'meeting_dates' => $data['meeting_dates'] ) );
		
		 
		if ( $current_user_booking ) {
			$this->tfhb_checked_booking_frequency_limit( $current_user_booking, $meta_data,);
		}

		if(!isset($attendee_data['booking_id']) ){
			// Create a New Booking Into Post Type
			$meeting_post_id = $this->tfhb_create_custom_post_booking($data);



			$data['post_id'] = $meeting_post_id; // set post id into booking data
			$result          = $booking->add( $data );  // add booking data into booking table
			$attendee_data['booking_id'] = $result['insert_id'];
			if ( $result === false ) {
				wp_send_json_error( array( 'message' => esc_html(__('Booking Failed', 'hydra-booking')) ) );
			}

		}
		
		// Attendees
		$attendees = new Attendees(); 
		$add_attendee = $attendees->add( $attendee_data ); 
		if ( $add_attendee === false ) {
			wp_send_json_error( array( 'message' => esc_html(__('Fialed to add Attendee', 'hydra-booking')) ) );
		}
		$attendee_data['id'] = $add_attendee['insert_id'];
		


		// Woocommerce Payment Method
		if ( true == $meta_data['payment_status'] && 'woo_payment' == $meta_data['payment_method'] ) {
			// Add to cart
			$product_id = $meta_data['payment_meta']['product_id'];
			$data['booking_id'] = $attendee_data['booking_id'];
			$data['added_time'] = time();
			$woo_booking = new WooBooking();
			$woo_booking->add_to_cart( $product_id, $data, $attendee_data );
			$response['redirect'] = wc_get_checkout_url();
		}


		// After Booking Hooks Action  
		$Attendee = new Attendees();
		$attendeeBooking =  $Attendee->getAttendeeWithBooking( 
			array(
				array('id', '=',$attendee_data['id']),
			),
			1,
			'DESC'
		); 
 
		if($attendeeBooking->status == 'confirmed'){
			// Single Booking & Mail Notification, Google Calendar // Zoom Meeting
			do_action( 'hydra_booking/after_booking_confirmed', $attendeeBooking ); 
		}  
		if($attendeeBooking->status == 'pending'){  
			do_action( 'hydra_booking/after_booking_pending', $attendeeBooking );
		}
		$notification = new Notification();
		$notification->AddNotification($attendeeBooking);
		

		
		
  
		// Load Meeting Confirmation Template
		$confirmation_template = $this->tfhb_booking_confirmation( $attendee_data['id']);

		$response['message']               = 'Booking Successful';
		$response['action']                = 'create';
		
		if('paypal_payment' == $meta_data['payment_method'] || 'stripe_payment' == $meta_data['payment_method']){
			$response['data']                = array( 
				'hash' 	  => $data['hash'], 
				'booking_id' => $attendee_data['booking_id'],
				'attendee_id' => $attendee_data['id'],
				'booking' => $data,
				'attendee_data' => $attendee_data,
				'meeting' => $MeetingData,
			);
		}
		
		$response['confirmation_template'] = $confirmation_template;

		wp_send_json_success( $response );
		wp_die();
	}

	/* Checked Booking frequency limit
	 * @param $current_user_booking
	 * @param $meta_data
	 * @return void
	 */
	public function tfhb_checked_booking_frequency_limit($current_user_booking, $meta_data){
		$last_items_of_booking = end( $current_user_booking );

		$booking_frequency = isset( $meta_data['booking_frequency'] ) ? $meta_data['booking_frequency'] : array(); 
		if ( $booking_frequency != NULL ) {
			$booking_frequency = !is_array( $booking_frequency ) ? json_decode( $booking_frequency, true ) : $booking_frequency;
			$created_date = $last_items_of_booking->created_at; // 2024-07-02 14:26:29
			$current_date = gmdate( 'Y-m-d H:i:s' );

			$last_created_date = gmdate( 'Y-m-d', strtotime( $created_date ) );
			
			foreach ( $booking_frequency as $key => $value ) {
				$times  = isset( $value['times'] ) ? $value['times'] : 'days';
				$limit = isset( $value['limit'] ) ? $value['limit'] : 5;

				$booking_frequency_date = gmdate( 'Y-m-d', strtotime( $last_created_date . ' + ' . $limit . ' '.$times ) );
				$total_booking          = count(
					array_filter(
						$current_user_booking,
						function ( $booking ) use ( $booking_frequency_date, $last_created_date ) {
							$created_date = gmdate( 'Y-m-d', strtotime( $booking->created_at ) );
							// Check if the created date is between last_created_date and booking_frequency_date
							return strtotime( $last_created_date ) >= strtotime( $created_date ) || strtotime( $created_date ) <= strtotime( $booking_frequency_date );
						}
					)
				); 
				// if currentdate is greater than booking_frequency_date then you can book the meeting
				if ( strtotime( $current_date ) > strtotime( $booking_frequency_date ) ) {
					continue;
				}
				if ( $total_booking >= $limit ) {
					wp_send_json_error( array( 'message' => esc_html(__(' Meeting frequency limit reached. Try  another Date', 'hydra-booking')) ) );

				}
			}
		}
	}

	/* Create Custom Post Booking
	 * @param $data
	 * @return $id
	 */
	public function tfhb_create_custom_post_booking($data) {

		// Create a new booking
		$title = 'New booking Booking '; // default title

		// Create an array to store the post data for meeting the current row
		$meeting_post_data = array(
			'post_type'   => 'tfhb_booking',
			'post_title'  => esc_html( $title ),
			'post_status' => 'publish',
		);

		// Insert the post into the database
		$meeting_post_id = wp_insert_post( $meeting_post_data );
		update_post_meta( $meeting_post_id, '_tfhb_booking_opt', $data );

		return $meeting_post_id;
	}


	/* Reschedule Booking
	 * @param $data
	 * @return void
	 */
	public function tfhb_reschedule_booking( $data, $attendee_data, $meeting_hash, $meta_data,  $general_settings, $check_booking ) {
 
		// Booking Class
		$booking = new Booking(); 
		$Attendee = new Attendees();
		$attendeeBooking =  $Attendee->getAttendeeWithBooking( 
			array(
				array('hash', '=',$meeting_hash),
			),
			1,
			'DESC'
		);  
		if('rescheduled' == $attendeeBooking->status){
			wp_send_json_error( array( 'message' => esc_html(__('Booking is already rescheduled', 'hydra-booking')) ) );
		}
		if('canceled' == $attendeeBooking->status){
			wp_send_json_error( array( 'message' => esc_html(__('Booking is already Canceled', 'hydra-booking')) ) );
		}
		 
 
		
		$old_booking_id = 0;

		if($attendeeBooking){
			$old_booking_id = $attendeeBooking->booking_id;
		}
 
		
		if(!$attendeeBooking){
			wp_send_json_error( array( 'message' => esc_html(__('Invalid Booking ID', 'hydra-booking')) ) );
		}
		if($attendeeBooking->status == 'completed'){
			wp_send_json_error( array( 'message' => esc_html(__('Booking is already completed', 'hydra-booking')) ) );
		}
		if($attendeeBooking->status == 'cancelled'){
			wp_send_json_error( array( 'message' => esc_html(__('Booking is already cancelled', 'hydra-booking')) ) );
		}
 
		// Get Post Meta
		$booking_meta = get_post_meta( $attendeeBooking->post_id, '_tfhb_booking_opt', true );
		

		if ( isset( $general_settings['allowed_reschedule_before_meeting_start'] ) && ! empty( $general_settings['allowed_reschedule_before_meeting_start'] ) ) {
			$allowed_reschedule_before_meeting_start = $general_settings['allowed_reschedule_before_meeting_start']; // 100 minutes
			if ( isset( $general_settings['allowed_reschedule_before_meeting_start'] ) && ! empty( $general_settings['allowed_reschedule_before_meeting_start'] ) ) {
				$allowed_reschedule_before_meeting_start = $general_settings['allowed_reschedule_before_meeting_start']; // 100 minutes
				$DateTime                                = new DateTimeController( $attendeeBooking->attendee_time_zone );
				// Time format if has AM and PM into start time
				$time_format  = strpos( $attendeeBooking->start_time, 'AM' ) || strpos( $attendeeBooking->start_time, 'PM' ) ? '12' : '24';
				
	
				$current_time = strtotime( $DateTime->convert_time_based_on_timezone( '', gmdate( 'Y-m-d H:i:s' ), 'UTC', $attendeeBooking->attendee_time_zone, $time_format ) );
				
				$meeting_time = strtotime( $attendeeBooking->meeting_dates . ' ' . $attendeeBooking->start_time );
				$time_diff    = $meeting_time - $current_time;
				$time_diff    = $time_diff / 60; // convert to minutes

				if ( $time_diff < $allowed_reschedule_before_meeting_start ) {
					wp_send_json_error( array( 'message' => esc_html(__('You can not reschedule the meeting before ', 'hydra-booking')) . $allowed_reschedule_before_meeting_start . esc_html(__(' minutes', 'hydra-booking')) ) );
				}
			}
		}
		
		
		$attendee_update = array();
		if($check_booking){
			// update attende booking id
			$attendee_update['booking_id'] = $check_booking->id;
			// update booking id into attendee

		}else{
		
			if('one-to-one' == $attendeeBooking->booking_type){ 
				
				$updat_booking['id'] = $attendeeBooking->booking_id;
				$updat_booking['meeting_dates'] = $data['meeting_dates'];
				$updat_booking['start_time'] = $data['start_time'];
				$updat_booking['end_time'] = $data['end_time']; 
				// update booking
				$booking->update( $updat_booking );
			}else{
				// Create a New Booking Into Post Type
				$meeting_post_id = $this->tfhb_create_custom_post_booking($data);
				$data['post_id'] = $meeting_post_id; // set post id into booking data
				$result          = $booking->add( $data );  // add booking data into booking table
				$attendee_update['booking_id'] = $result['insert_id'];
				if ( $result === false ) {
					wp_send_json_error( array( 'message' => esc_html(__('Booking Failed', 'hydra-booking')) ) );
				} 
			} 

		}  
		$attendee_update['id'] = $attendeeBooking->id; 
		$attendee_update['status'] = 'rescheduled'; 
		$attendee_update['reason'] =  $data['reason'];
		
		$Attendee->update( $attendee_update );
		

	 
		$confirmation_template = $this->tfhb_booking_confirmation( $attendeeBooking->id);

		 
		 $attendeeBooking =  $Attendee->getAttendeeWithBooking( 
			array(
				array('id', '=', $attendeeBooking->id),
			),
			1,
			'DESC'
		); 
 
 
		do_action( 'hydra_booking/after_booking_schedule',  $old_booking_id, $attendeeBooking );

	 

		$response['message']               = esc_html(__('Rescheduled Successfully', 'hydra-booking'));
		$response['action']                = 'rescheduled';
		$response['confirmation_template'] = $confirmation_template;
		// $booking_meta, $MeetingData, $host_meta
		wp_send_json_success( $response );
		wp_die();
	}


	public function tfhb_booking_confirmation( $attendee_id) {
	
		$Attendee = new Attendees();
		$attendeeBooking =  $Attendee->getAttendeeWithBooking( 
			array(
				array('id', '=', $attendee_id),
			),
			1,
			'DESC'
		 ); 
		// Load Meeting Confirmation Template
		ob_start();

		load_template(
			TFHB_PATH . '/app/Content/Template/meeting-confirmation.php',
			false,
			array(
				'attendeeBooking' => $attendeeBooking, 
			)
		);

		$confirmation_template = ob_get_clean();

		return $confirmation_template;
	}


	

	// Already Booked Times Callback
	public function tfhb_already_booked_times_callback() {
		// Checked Nonce validation.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'tfhb_nonce' ) ) {
			wp_send_json_error( array( 'message' => esc_html(__('Nonce verification failed' , 'hydra-booking'))) );
		}

		// Check if the request is POST.
		if ( 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
			wp_send_json_error( array( 'message' => esc_html(__('Invalid request method', 'hydra-booking')) ) );
		}

		// Check if the request is not empty.
		if ( empty( $_POST ) ) {
			wp_send_json_error( array( 'message' => esc_html(__('Invalid request', 'hydra-booking')) ) );
		} 

		$meeting = new Meeting();
		$meetingData = $meeting->get( $_POST['meeting_id'] );
		$meeting_type =  $meetingData->meeting_type;
		if($meeting_type == 'one-to-group' && tfhb_is_pro_active() == false ){
			 
			wp_send_json_error( array( 'message' => esc_html(__('Please upgrade to pro version for group meeting', 'hydra-booking')) ) );
			wp_die();
		}

		$selected_date        = isset( $_POST['selected_date'] ) ? sanitize_text_field( $_POST['selected_date'] ) : '';
		$meeting_id           = isset( $_POST['meeting_id'] ) ? sanitize_text_field( $_POST['meeting_id'] ) : 0;
		$selected_time_format = isset( $_POST['time_format'] ) ? sanitize_text_field( $_POST['time_format'] ) : '12';
		$selected_time_zone   = isset( $_POST['time_zone'] ) ? sanitize_text_field( $_POST['time_zone'] ) : 'UTC';

		$booking = new Booking();
		$current_user_booking = $booking->get( array( 'meeting_id' => $meeting_id, 'meeting_dates' => $selected_date ) );
		if ( $current_user_booking ) {
			$meta_data = get_post_meta( $meetingData->post_id, '__tfhb_meeting_opt', true );
			
			$this->tfhb_checked_booking_frequency_limit( $current_user_booking, $meta_data,);
		}
		// get current date time to this month end in array
		$this_month_all_dates = array();
		$current_date = $selected_date;
		
		$end_date = date('Y-m-t', strtotime($selected_date)); 
		// now get all dates between current date and this month end
		$begin = new \DateTime($current_date);
		$end = new \DateTime($end_date);
		$end = $end->modify( '+1 day' );
		$interval = new \DateInterval('P1D');
		$daterange = new \DatePeriod($begin, $interval ,$end);
		foreach($daterange as $date){
			$this_month_all_dates[] = $date->format("Y-m-d");
		} 
		
		$all_month_data = array();
		$date_time = new DateTimeController( $selected_time_zone );
		foreach ( $this_month_all_dates as $date ) {
			$all_month_data[ $date ] = $date_time->getAvailableTimeData( $meeting_id, $date, $selected_time_zone, $selected_time_format );
		} 
	 
		if ( empty( $all_month_data ) ) {
			wp_send_json_error( array( 'message' => esc_html(__('No time slots are currently available.', 'hydra-booking')) ) );
		}
		wp_send_json_success( $all_month_data );
		wp_die();
	}


	// Booking Cancel Callback
	public function tfhb_meeting_form_cencel_callback() {
		// Check if the request is POST.
		if ( 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
			wp_send_json_error( array( 'message' => esc_html(__('Invalid request method' , 'hydra-booking'))) );
		}

		// Check if the request is not empty.
		if ( empty( $_POST ) ) {
			wp_send_json_error( array( 'message' => esc_html(__('Invalid request' , 'hydra-booking'))) );
		}

		$data     = array();
		$response = array();

		$hash = isset( $_POST['hash'] ) ? sanitize_text_field( $_POST['hash'] ) : '';
 
		// Checked Nonce validation.
		$nonce_valid = false;
		if ( isset( $_POST['nonce'] ) && ! empty( $hash ) ) {
			$nonce_valid = wp_verify_nonce( $_POST['nonce'], 'tfhb_cancel_' . $hash );
		}

		if ( ! $nonce_valid ) {
			wp_send_json_error( array( 'message' => esc_html(__('Nonce verification failed', 'hydra-booking')) ) );
		}

		$reason       = isset( $_POST['reason'] ) ? sanitize_text_field( $_POST['reason'] ) : '';
		
		$Attendee = new Attendees();
		$attendeeBooking =  $Attendee->getAttendeeWithBooking( 
			array(
				array('hash', '=',$hash),
			),
			1,
			'DESC'
		);   
 
		if ( ! $attendeeBooking || ! hash_equals( $attendeeBooking->hash, $hash ) ) {
			wp_send_json_error( array( 'message' => esc_html(__('Invalid Booking ID', 'hydra-booking')) ) );
		}


		$attendee_data = array(
			'id'           => $attendeeBooking->id,
			'reason'       => $reason,
			'status'       => 'canceled',
			'cancelled_by' => 'attendee',
		); 

		
		$Attendee->update( $attendee_data );


		// if booking type is one-to-one then Cancel booking status
		if('one-to-one' == $attendeeBooking->booking_type){
			$booking = new Booking();
			$booking->update( 
				array(
					'id' => $attendeeBooking->booking_id,
					'status' => 'canceled'
				) 
			);
		}

		// Before Booking After Cancel
		do_action( 'hydra_booking/after_booking_canceled', $attendeeBooking );

		$response['message'] = esc_html(__('Booking Cancelled Successfully', 'hydra-booking'));

		wp_send_json_success( $response );

		wp_die();
	}

	/**
	 * Get Booking Data
	 * @param $booking_id
	 * @return $booking
	 */
	public function tfhb_meeting_paypal_payment_confirmation_callback() {
		// Checked Nonce validation.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'tfhb_nonce' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Nonce verification failed', 'hydra-booking' ) ) );
		}

		// Check if the request is POST.
		if ( 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Invalid request method', 'hydra-booking' ) ) );
		}

		// Check if the request is not empty.
		if ( empty( $_POST ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Invalid request', 'hydra-booking' ) ) );
		}

		$payment_details = isset( $_POST['payment_details'] ) ? wp_unslash( $_POST['payment_details'] ) : array();
		$response_data   = isset( $_POST['responseData'] ) ? wp_unslash( $_POST['responseData'] ) : array();

		$payment_id = isset( $payment_details['id'] ) ? sanitize_text_field( $payment_details['id'] ) : '';
		$payer_id   = isset( $payment_details['payer']['payer_id'] ) ? sanitize_text_field( $payment_details['payer']['payer_id'] ) : '';

		$hash        = isset( $response_data['data']['hash'] ) ? sanitize_text_field( $response_data['data']['hash'] ) : '';
		$attendee_hash        = isset( $response_data['data']['attendee_data']['hash'] ) ? sanitize_text_field( $response_data['data']['attendee_data']['hash'] ) : '';
		$booking_id  = isset( $response_data['data']['booking_id'] ) ? absint( $response_data['data']['booking_id'] ) : 0;
		$attendee_id = isset( $response_data['data']['attendee_id'] ) ? absint( $response_data['data']['attendee_id'] ) : 0;
		$meeting_id  = isset( $response_data['data']['booking']['meeting_id'] ) ? absint( $response_data['data']['booking']['meeting_id'] ) : 0;
		$host_id     = isset( $response_data['data']['booking']['host_id'] ) ? absint( $response_data['data']['booking']['host_id'] ) : 0;

		$total    = isset( $payment_details['purchase_units'][0]['amount']['value'] ) ? sanitize_text_field( $payment_details['purchase_units'][0]['amount']['value'] ) : '';
		$currency = isset( $payment_details['purchase_units'][0]['amount']['currency_code'] ) ? sanitize_text_field( $payment_details['purchase_units'][0]['amount']['currency_code'] ) : '';

		if ( empty( $payment_id ) || empty( $payer_id ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Missing payment identifiers.', 'hydra-booking' ) ) );
		}

		if ( empty( $booking_id ) || empty( $attendee_id ) || empty( $attendee_hash ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Missing booking information.', 'hydra-booking' ) ) );
		}

		$attendee_model = new Attendees();
		$attendee       = $attendee_model->getAttendeeWithBooking(
			array(
				array( 'id', '=', $attendee_id ),
			),
			1,
			'DESC'
		); 

		if ( empty( $attendee ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Booking not found for the provided attendee.', 'hydra-booking' ) ) );
		}

		if ( (int) $attendee->booking_id !== $booking_id ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Booking mismatch detected.', 'hydra-booking' ) ) );
		}

		if ( empty( $attendee->hash ) || ! hash_equals( $attendee->hash, $attendee_hash ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Invalid booking reference.', 'hydra-booking' ) ) );
		}

		if ( empty( $attendee->payment_method ) || 'paypal_payment' !== $attendee->payment_method ) {
			wp_send_json_error( array( 'message' => esc_html__( 'PayPal is not the configured payment method for this booking.', 'hydra-booking' ) ) );
		}

		$current_payment_status = strtolower( $attendee->payment_status );
		if ( 'completed' === $current_payment_status ) {
			$response['message'] = esc_html__( 'Payment has already been confirmed.', 'hydra-booking' );
			wp_send_json_success( $response );
		}

		// Prefer data retrieved from the database over client input.
		$meeting_id = absint( $attendee->meeting_id );
		$host_id    = absint( $attendee->host_id );

		$meeting_model = new Meeting();
		$meeting       = $meeting_model->get( $meeting_id );

		$expected_total    = isset( $meeting->meeting_price ) ? (float) $meeting->meeting_price : (float) $total;
		$expected_currency = isset( $meeting->payment_currency ) ? strtoupper( $meeting->payment_currency ) : strtoupper( $currency );

		$_tfhb_integration_settings = get_option( '_tfhb_integration_settings', array() );
		$paypal_settings            = isset( $_tfhb_integration_settings['paypal'] ) ? $_tfhb_integration_settings['paypal'] : array();

		$paypal_enabled = ! empty( $paypal_settings ) && ! empty( $paypal_settings['status'] ) && (int) $paypal_settings['status'] === 1;
		$client_id      = isset( $paypal_settings['client_id'] ) ? trim( $paypal_settings['client_id'] ) : '';
		$secret_key     = isset( $paypal_settings['secret_key'] ) ? trim( $paypal_settings['secret_key'] ) : '';
		
		if ( ! $paypal_enabled || empty( $client_id ) || empty( $secret_key ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'PayPal integration is not configured.', 'hydra-booking' ) ) );
		}

		$environment = isset( $paypal_settings['environment'] ) && 'live' === strtolower( $paypal_settings['environment'] ) ? 'live' : 'sandbox';
		$api_base    = 'live' === $environment ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';

		// Step 1: Retrieve OAuth access token.
		$token_response = wp_remote_post(
			$api_base . '/v1/oauth2/token',
			array(
				'headers' => array(
					'Authorization' => 'Basic ' . base64_encode( $client_id . ':' . $secret_key ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
				),
				'body'      => array(
					'grant_type' => 'client_credentials',
				),
				'timeout'   => 20,
			)
		);
	
		if ( is_wp_error( $token_response ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Unable to communicate with PayPal. Please try again later.', 'hydra-booking' ) ) );
		}

		$token_code = (int) wp_remote_retrieve_response_code( $token_response );
		$token_body = json_decode( wp_remote_retrieve_body( $token_response ), true ); 
		if ( 200 !== $token_code || empty( $token_body['access_token'] ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Failed to authenticate with PayPal.', 'hydra-booking' ) ) );
		}

		$access_token = $token_body['access_token'];

		// Step 2: Retrieve order details.
		$order_response = wp_remote_get(
			$api_base . '/v2/checkout/orders/' . rawurlencode( $payment_id ),
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $access_token,
				),
				'timeout' => 20,
			)
		); 
		if ( is_wp_error( $order_response ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Unable to verify the PayPal payment.', 'hydra-booking' ) ) );
		}

		$order_code = (int) wp_remote_retrieve_response_code( $order_response );
		$order_body = json_decode( wp_remote_retrieve_body( $order_response ), true ); 
		if ( 200 !== $order_code || empty( $order_body ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Unexpected response received from PayPal.', 'hydra-booking' ) ) );
		}

		if ( empty( $order_body['status'] ) || 'COMPLETED' !== strtoupper( $order_body['status'] ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'PayPal has not marked this payment as completed.', 'hydra-booking' ) ) );
		}

		$order_payer_id = isset( $order_body['payer']['payer_id'] ) ? $order_body['payer']['payer_id'] : '';
		if ( empty( $order_payer_id ) || $order_payer_id !== $payer_id ) {
			wp_send_json_error( array( 'message' => esc_html__( 'PayPal payer verification failed.', 'hydra-booking' ) ) );
		}

		$order_reference = isset( $order_body['purchase_units'][0]['reference_id'] ) ? $order_body['purchase_units'][0]['reference_id'] : '';
		if ( ! empty( $order_reference ) && (int) $order_reference !== $attendee_id ) {
			wp_send_json_error( array( 'message' => esc_html__( 'PayPal order reference does not match the attendee.', 'hydra-booking' ) ) );
		}

		$capture_data      = isset( $order_body['purchase_units'][0]['payments']['captures'][0] ) ? $order_body['purchase_units'][0]['payments']['captures'][0] : array();
		$order_amount_raw  = isset( $capture_data['amount']['value'] ) ? $capture_data['amount']['value'] : ( isset( $order_body['purchase_units'][0]['amount']['value'] ) ? $order_body['purchase_units'][0]['amount']['value'] : '' );
		$order_currency    = isset( $capture_data['amount']['currency_code'] ) ? $capture_data['amount']['currency_code'] : ( isset( $order_body['purchase_units'][0]['amount']['currency_code'] ) ? $order_body['purchase_units'][0]['amount']['currency_code'] : '' );
		$capture_status    = isset( $capture_data['status'] ) ? strtoupper( $capture_data['status'] ) : '';

		if ( ! empty( $capture_status ) && 'COMPLETED' !== $capture_status ) {
			wp_send_json_error( array( 'message' => esc_html__( 'PayPal capture has not been completed.', 'hydra-booking' ) ) );
		}

		$order_amount    = (float) $order_amount_raw;
		$expected_amount = (float) $expected_total;

		if ( $expected_amount > 0 && abs( $order_amount - $expected_amount ) > 0.01 ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Paid amount does not match the booking total.', 'hydra-booking' ) ) );
		}

		if ( ! empty( $expected_currency ) && ! empty( $order_currency ) && strtoupper( $expected_currency ) !== strtoupper( $order_currency ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Currency mismatch detected for the payment.', 'hydra-booking' ) ) );
		}

		// Update attendee payment status.
		$attendee_update = array(
			'id'             => $attendee_id,
			'payment_status' => 'Completed',
			'status'         => 'confirmed',
		);

		$attendee_result = $attendee_model->update( $attendee_update );
		if ( false === $attendee_result ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Unable to update attendee payment status.', 'hydra-booking' ) ) );
		}

		// Update booking status to confirmed.
		$booking_model = new Booking();
		$booking_model->update(
			array(
				'id'     => $booking_id,
				'status' => 'confirmed',
			)
		);

		$transactions_model  = new Transactions();
		$existing_transaction = $transactions_model->get(
			array(
				array( 'booking_id', '=', $booking_id ),
				array( 'attendee_id', '=', $attendee_id ),
			),
			1
		);

		$transaction_payload = array(
			'booking_id'         => $booking_id,
			'attendee_id'        => $attendee_id,
			'meeting_id'         => $meeting_id,
			'host_id'            => $host_id,
			'customer_id'        => $attendee_id,
			'payment_method'     => 'paypal_payment',
			'total'              => $order_amount_raw ? sanitize_text_field( $order_amount_raw ) : sanitize_text_field( $total ),
			'transation_history' => array(
				'payment_id' => $payment_id,
				'payer_id'   => $payer_id,
				'order'      => $order_body,
			),
			'status'            => 'completed',
		);

		if ( $existing_transaction ) {
			$transaction_payload['id'] = $existing_transaction->id;
			$transactions_model->update( $transaction_payload );
		} else {
			$transactions_model->add( $transaction_payload );
		}

		// Retrieve the refreshed attendee record to pass along hooks.
		$updated_attendee = $attendee_model->getAttendeeWithBooking(
			array(
				array( 'id', '=', $attendee_id ),
			),
			1,
			'DESC'
		);

		// Trigger hooks.
		$attendee_hook_payload = array(
			'id'             => $attendee_id,
			'payment_status' => 'Completed',
			'status'         => 'confirmed',
		);

		do_action( 'hydra_booking/after_booking_payment_complete', $attendee_hook_payload );

		if ( $updated_attendee ) {
			do_action( 'hydra_booking/after_booking_confirmed', $updated_attendee );
		}

		// Return success message.
		$response['message'] = esc_html__( 'Payment Completed Successfully', 'hydra-booking' );
		wp_send_json_success( $response );
	}
}

?>
