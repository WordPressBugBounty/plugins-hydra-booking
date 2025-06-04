<?php
namespace HydraBooking\Hooks;


	// exit
if ( ! defined( 'ABSPATH' ) ) {
	exit; }
use HydraBooking\Services\Integrations\Zoom\ZoomServices;
use HydraBooking\Services\Integrations\GoogleCalendar\GoogleCalendar;
use HydraBooking\DB\Booking;
class BookingLocation {
	public function __construct() {
		add_action( 'hydra_booking/after_booking_confirmed', array( $this, 'addLocation_after_booking_confirmed' ),  11, 1); 
		// add_action( 'hydra_booking/after_booking_confirmed', array( $this, 'addLocation_after_booking_confirmed' ), 10, 1 );
		// add_action( 'hydra_booking/after_booking_canceled', array( $this, 'pushBookingToCanceled' ), 10, 1 );
		add_action( 'hydra_booking/after_booking_schedule', array( $this, 'pushBookingToscheduled' ), 10, 2 );
		// add_action( 'hydra_booking/send_booking_reminder', array( $this, 'send_booking_reminder' ), 10, 1 );
	
	}

	/**
	 * Add Location to Booking
	 * 
	 * @param int $booking_id
	 * @author Sydur Rahman
	 * @return void
	 */
 

	public function addLocation_after_booking_confirmed( $attendee ) {
		// tfhb_print_r( $attendee );
		$booking_id = $attendee->booking_id;
	
		// if is not array or not object json decode
		$locations = !empty($attendee->meeting_locations) ? $attendee->meeting_locations : array();
	
		$locations = !is_array($locations) && !is_object($locations) ? json_decode($locations) : $locations;
		 

		$_tfhb_integration_settings = get_option( '_tfhb_integration_settings' ); 
 
		foreach($locations as $key => $location){ 
			if($key == 'zoom'){ // Booking Location is Zoom 
				if ( ! empty( $_tfhb_integration_settings['zoom_meeting'] ) && ! empty( $_tfhb_integration_settings['zoom_meeting']['connection_status'] ) && $_tfhb_integration_settings['zoom_meeting']['status'] == true ) {
					$zoom = new ZoomServices();
					$address = $zoom->tfhb_create_zoom_meeting($attendee);  
					if($address){
						$locations->zoom->address = $address;
					}
				}
			}
 
		}  
		

		$update_data = array(
			'id' => $booking_id,
			'meeting_locations' => $locations

		); 

		$updateBooking = new Booking();
		$updateBooking->update($update_data); 




	}

	/**
	 * Push Booking to Scheduled
	 * 
	 * @param int $booking_id
	 * @param array $booking
	 * @return void
	 */

	 public function pushBookingToscheduled( $old_booking_id, $attendee ) { 
		
		if($old_booking_id !=0 ){
			$booking_id = $old_booking_id; 
			//  Get Booking With Attendee
			$booking = new Booking();
			$booking_data = $booking->get( $booking_id ); 
				// if is not array or not object json decode
			$locations = !is_array($attendee->meeting_locations) ? json_decode($attendee->meeting_locations) : $attendee->meeting_locations;
		 
			$_tfhb_integration_settings = get_option( '_tfhb_integration_settings' );
			foreach($locations as $key => $location){ 
				if($key == 'zoom'){ // Booking Location is Zoom
					if ( ! empty( $_tfhb_integration_settings['zoom_meeting'] ) && ! empty( $_tfhb_integration_settings['zoom_meeting']['connection_status'] ) && $_tfhb_integration_settings['zoom_meeting']['status'] == true ) {
						$zoom = new ZoomServices();
						$address = $zoom->remove_attendde_location_from_existing_booking($booking_data, $attendee);  
						if($address){
							$locations->zoom->address = $address;
						}
						// Create new Zoom Meeting
					}
				}
			}
			

		}
		$this->addLocation_after_booking_confirmed( $attendee );

	 }

	 

}