<?php
namespace HydraBooking\Services\Integrations\Woocommerce;
use HydraBooking\Admin\Controller\Notification;

// don't load directly
defined( 'ABSPATH' ) || exit;


use HydraBooking\DB\Booking;
use HydraBooking\DB\Attendees;
use HydraBooking\DB\Transactions;
use HydraBooking\DB\Meeting;

/**
 *
 *
 * Extend WooCommerce Product data
 *
 * @since 1.0.0
 */
class WooBooking {

	public function __construct() {
	}

	public function add_to_cart( $product_id, $data, $attendee_data ) { 
		$product                                      = wc_get_product( $product_id );
		$order_meta                                   = array();
		$order_meta['tfhb_order_meta']['booking_id']  = $data['booking_id'];
		$order_meta['tfhb_order_meta']['attendee_id']  = $attendee_data['id'];
		$order_meta['tfhb_order_meta']['added_time']  = time();
		$order_meta['tfhb_order_meta']['Appointment'] = $data['meeting_dates'] . ' ' . $data['start_time'] . ' - ' . $data['end_time'] . ' ( ' . $attendee_data['attendee_time_zone'] . ' )';
		$cart = WC()->cart;
		$cart->add_to_cart( $product_id, 1, 0, array(), $order_meta );

		return true;
	}

	// display booking_id  into checkout page
	public function tfhb_woocommerce_get_item_data( $item_data, $cart_item ) { 
		if ( ! empty( $cart_item['tfhb_order_meta']['booking_id'] ) ) {
			$item_data[] = array(
				'key'   => esc_html( __( 'Appointment ', 'hydra-booking' ) ),
				'value' => $cart_item['tfhb_order_meta']['Appointment'],
			);
		}
		return $item_data;
	}

	public function woocommerce_cart_loaded_from_session_callback($cart) {
		$general_settings = get_option( '_tfhb_general_settings', true ) ? get_option( '_tfhb_general_settings', true ) : array();
		$expire_minutes = !empty($general_settings['after_cart_expire']) ? intval($general_settings['after_cart_expire']) : 60;
		$expire_seconds = $expire_minutes * 60;
	
		foreach ($cart->get_cart() as $key => $item) {
			$added_time = isset($item['tfhb_order_meta']['added_time']) ? intval($item['tfhb_order_meta']['added_time']) : 0;
			if ($added_time && (time() - $added_time) > $expire_seconds) {
	
				// Update Booking
				if (!empty($item['tfhb_order_meta']['booking_id'])) {
					$booking = new Booking();
					$booking->update([
						'id'     => $item['tfhb_order_meta']['booking_id'],
						'status' => 'canceled',
					]);
				}
	
				// Update Attendee
				if (!empty($item['tfhb_order_meta']['attendee_id'])) {
					$Attendees = new Attendees();
					$Attendees->update([
						'id'     => $item['tfhb_order_meta']['attendee_id'],
						'status' => 'canceled',
					]);
				}
	
				$cart->remove_cart_item($key);
			}
		}
	}

	public function woocommerce_remove_cart_item_callback($cart_item_key, $cart) {
		$cart_item = $cart->get_cart_item($cart_item_key);
		if (!$cart_item) return;
	
		// Update Booking
		if (!empty($cart_item['tfhb_order_meta']['booking_id'])) {
			$booking = new Booking();
			$booking->update([
				'id'     => $cart_item['tfhb_order_meta']['booking_id'],
				'status' => 'canceled',
			]);
		}
	
		// Update Attendee
		if (!empty($cart_item['tfhb_order_meta']['attendee_id'])) {
			$Attendees = new Attendees();
			$Attendees->update([
				'id'     => $cart_item['tfhb_order_meta']['attendee_id'],
				'status' => 'canceled',
			]);
		}
	
	}
	
	
	// update order meta data
	public function tfhb_booking_custom_order_data( $item, $cart_item_key, $values, $order ) {

		// Assigning data into variables.
		$booking_id  = ! empty( $values['tfhb_order_meta']['booking_id'] ) ? $values['tfhb_order_meta']['booking_id'] : '';
		$attendee_id  = ! empty( $values['tfhb_order_meta']['attendee_id'] ) ? $values['tfhb_order_meta']['attendee_id'] : '';
		$appointment = ! empty( $values['tfhb_order_meta']['Appointment'] ) ? $values['tfhb_order_meta']['Appointment'] : '';
		$added_time  = ! empty( $values['tfhb_order_meta']['added_time'] ) ? $values['tfhb_order_meta']['added_time'] : '';

		if ( $booking_id ) {
			$item->update_meta_data( '_tfhb_booking_id', $booking_id, true );
		}
		if ( $attendee_id ) {
			$item->update_meta_data( '_tfhb_attendee_id', $attendee_id, true );
		}

		if ( $appointment ) {
			$item->update_meta_data( 'tfhb_appointment', $appointment, true );
		}
		if ( $appointment ) {
			$item->update_meta_data( '_added_time', $added_time, true );
		}
	}

	// Add order id to the hotel room meta field
	public function tfhb_add_booking_data_checkout_order_processed( $order_id, $posted_data, $order ) {

		
		$order = wc_get_order( $order_id );
		$items = $order->get_items(); 
		 
		foreach ( $items as $item_id => $item ) { 
			if ( ! empty( $item->get_meta( '_tfhb_booking_id' ) ) ) {
				// Item Sales Price
				$items_price = $item->get_total();

				
				$booking_id  = $item->get_meta( '_tfhb_booking_id' );
				$attendee_id = $item->get_meta( '_tfhb_attendee_id' );
				$appointment = $item->get_meta( 'tfhb_appointment' );
				$order->update_meta_data(
					'tfhb_order_meta',
					array(
						'booking_id'  => $booking_id,
						'attendee_id'  => $attendee_id,
						'Appointment' => $appointment,
					)
				);

				// Update Booking based on General Status
				$booking = new Booking();
				$general_settings = get_option( '_tfhb_general_settings', true ) ? get_option( '_tfhb_general_settings', true ) : array();
				$updat_booking['id'] = $booking_id;
				$updat_booking['status'] = 'pending';
				if(isset($general_settings['booking_status']) && $general_settings['booking_status'] == 1){
					$updat_booking['status'] = 'confirmed';
				}
				if(!isset($general_settings['booking_status'])){
					$updat_booking['status'] = 'confirmed';
				}
				$booking->update( $updat_booking );
				
				// Update Transaction ID Data 

				$Attendees = new Attendees();
				// Attendees update
				$updat_attendee['id'] = $attendee_id;
				$updat_attendee['status'] = 'pending';
				if(isset($general_settings['booking_status']) && $general_settings['booking_status'] == 1){
					$updat_attendee['status'] = 'confirmed';
				}
				if(!isset($general_settings['booking_status'])){
					$updat_attendee['status'] = 'confirmed';
				}
				$Attendees->update( $updat_attendee );

				$attendeeBooking =  $Attendees->getAttendeeWithBooking( 
					array(
						array('id', '=',$attendee_id),
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

				$get_attendee = $Attendees->getAttendeeWithBooking( $attendee_id  ); 
								
				$transactions = new Transactions();
				$transation_history = array(
					'wc_order_id' => $order_id, 
					'item_id' => $item_id, 
				);
				// add transaction
				$transactionData = array(
					'booking_id' 	   => $booking_id,
					'attendee_id' 	   => $attendee_id,
					'meeting_id' 	   => $get_attendee->meeting_id,
					'host_id' 	   => $get_attendee->host_id,
					'customer_id' 	   => $attendee_id,
					'payment_method' 	   => $get_attendee->payment_method,
					'total' 	   => $items_price,
					'status' 	   => $order->get_status(),
					'transation_history' => json_encode($transation_history, true),
				); 

		

				// add transaction
				$transactions->add( $transactionData );  


			}
		}
	}

	/**
	 * Block Checkout Transaction
	 */
	public function tfhb_add_booking_data_checkout_order_processed_block_checkout($order){
		$order_id = $order->get_id();
		$items = $order->get_items(); 
		 
		foreach ( $items as $item_id => $item ) { 
			if ( ! empty( $item->get_meta( '_tfhb_booking_id' ) ) ) {
				// Item Sales Price
				$items_price = $item->get_total();

				
				$booking_id  = $item->get_meta( '_tfhb_booking_id' );
				$attendee_id = $item->get_meta( '_tfhb_attendee_id' );
				$appointment = $item->get_meta( 'tfhb_appointment' );
				$order->update_meta_data(
					'tfhb_order_meta',
					array(
						'booking_id'  => $booking_id,
						'attendee_id'  => $attendee_id,
						'Appointment' => $appointment,
					)
				);

				// Update Booking based on General Status
				$booking = new Booking();
				$general_settings = get_option( '_tfhb_general_settings', true ) ? get_option( '_tfhb_general_settings', true ) : array();
				$updat_booking['id'] = $booking_id;
				$updat_booking['status'] = 'pending';
				if(isset($general_settings['booking_status']) && $general_settings['booking_status'] == 1){
					$updat_booking['status'] = 'confirmed';
				}
				if(!isset($general_settings['booking_status'])){
					$updat_booking['status'] = 'confirmed';
				}
				$booking->update( $updat_booking );
				
				// Update Transaction ID Data 

				$Attendees = new Attendees();
				// Attendees update
				$updat_attendee['id'] = $attendee_id;
				$updat_attendee['status'] = 'pending';
				if(isset($general_settings['booking_status']) && $general_settings['booking_status'] == 1){
					$updat_attendee['status'] = 'confirmed';
				}
				if(!isset($general_settings['booking_status'])){
					$updat_attendee['status'] = 'confirmed';
				}
				$Attendees->update( $updat_attendee );

				$attendeeBooking =  $Attendees->getAttendeeWithBooking( 
					array(
						array('id', '=',$attendee_id),
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

				
				// Update Transaction ID Data 
				$get_attendee = $Attendees->getAttendeeWithBooking( $attendee_id  ); 
								
				$transactions = new Transactions();
				$transation_history = array(
					'wc_order_id' => $order_id, 
					'item_id' => $item_id, 
				);
				// add transaction
				$transactionData = array(
					'booking_id' 	   => $booking_id,
					'attendee_id' 	   => $attendee_id,
					'meeting_id' 	   => $get_attendee->meeting_id,
					'host_id' 	   => $get_attendee->host_id,
					'customer_id' 	   => $attendee_id,
					'payment_method' 	   => $get_attendee->payment_method,
					'total' 	   => $items_price,
					'status' 	   => $order->get_status(),
					'transation_history' => json_encode($transation_history, true),
				); 

		

				// add transaction
				$transactions->add( $transactionData );  


			}
		}
	}

	public function tfhb_woocommerce_thankyou( $id ) {
		$order = wc_get_order( $id );

		// check if order is not empty
		if ( ! $order ) {
			return;
		}

		$items = $order->get_items();
		foreach ( $items as $item_id => $item ) {

			if ( ! empty( $item->get_meta( '_tfhb_booking_id' ) ) ) { 

				$booking_id = $item->get_meta( '_tfhb_booking_id' );
				$attendee_id = $item->get_meta( '_tfhb_attendee_id' );
				$Attendees    = new Attendees();
				$updateData = array(
					'id'             => $attendee_id,  
					'payment_status' => $order->get_status(),
				);
				// update booking

				// update booking
				$Attendees->update( $updateData );

			}
		}
	}


	public function getAllProductList() {
		// Check if WooCommerce is active
		if ( ! class_exists( 'WooCommerce' ) ) {
			return array();
		}
		$args        = array(
			'post_type'      => 'product',
			'posts_per_page' => -1,
		);
		$products    = new \WP_Query( $args );
		$currency 	=  get_woocommerce_currency();
		$productList = array();
		if ( $products->have_posts() ) {
			while ( $products->have_posts() ) {
				$products->the_post();
				$productList[] = array(
					'value' => get_the_ID(),
					'name'  => '[#' . get_the_ID() . '] - ' . get_the_title() . ' - ' . get_post_meta( get_the_ID(), '_price', true ) .' ( '.$currency.' )',
				);
			}
		}
		return $productList;
	}
}
