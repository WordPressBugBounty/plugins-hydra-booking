<?php
namespace HydraBooking\Hooks;

// exit
if ( ! defined( 'ABSPATH' ) ) { exit; }
use HydraBooking\Services\Integrations\GoogleCalendar\GoogleCalendar;
use HydraBooking\Services\Integrations\Zoom\ZoomServices;
use HydraBooking\Admin\Controller\HostsController; 
use HydraBooking\Services\Integrations\Woocommerce\WooBooking;

class ActionHooks {

	public function __construct() { 
		
		// Google Calendar
		$_tfhb_integration_settings = get_option( '_tfhb_integration_settings' ); 
		$google_calendar            = isset( $_tfhb_integration_settings['google_calendar'] ) ? $_tfhb_integration_settings['google_calendar'] : array();
		$zoom_meeting            = isset( $_tfhb_integration_settings['zoom_meeting'] ) ? $_tfhb_integration_settings['zoom_meeting'] : array();
 
		if(!empty($google_calendar) && $google_calendar['status'] == true){
			add_action( 'hydra_booking/after_booking_confirmed', array( new GoogleCalendar(), 'insert_calender_after_booking_confirmed' ), 11, 2 ); 
			add_action( 'hydra_booking/after_booking_canceled', array( new GoogleCalendar(), 'deleteGoogleCalender' ), 11, 2 );
			add_action( 'hydra_booking/after_booking_schedule', array( new GoogleCalendar(), 'remove_attendde_event_from_existing_booking' ), 11, 2 );
		}

		// if(!empty($zoom_meeting) && $zoom_meeting['status'] == true){
		// 	add_action( 'hydra_booking/after_booking_completed', array( new ZoomServices(), 'tfhb_create_zoom_meeting' ), 10, 2 ); 
		// 	add_action( 'hydra_booking/after_booking_canceled', array( new ZoomServices(), 'tfhb_cancel_zoom_meeting' ), 10, 2 ); 
		// 	add_action( 'hydra_booking/after_booking_schedule', array( new ZoomServices(), 'tfhb_reshedule_zoom_meeting' ), 10, 2 ); 
		// }


		// add_action( 'hydra_booking/after_booking_completed', array( new ZoomServices(), 'tfhb_create_zoom_meeting' ), 10, 2 ); 

		// host update email 
		add_action('profile_update', array(new HostsController(), 'update_host_email'), 10, 2);


		// woocommerce order status change  

		$woo_payment = isset( $_tfhb_integration_settings['woo_payment'] ) ? $_tfhb_integration_settings['woo_payment'] : array();

		if(isset($woo_payment['status']) && $woo_payment['status'] == true){  
		 
			// Show custom data in order details.
			add_action( 'woocommerce_checkout_create_order_line_item', array( new WooBooking(), 'tfhb_booking_custom_order_data' ), 10, 4 );

			// add booking_id to order meta
			add_action( 'woocommerce_checkout_order_processed', array( new WooBooking(), 'tfhb_add_booking_data_checkout_order_processed' ), 10, 4 );

			add_action( 'woocommerce_thankyou', array( new WooBooking(), 'tfhb_woocommerce_thankyou' ) );

			add_action( 'woocommerce_store_api_checkout_order_processed', array( new WooBooking(), 'tfhb_add_booking_data_checkout_order_processed_block_checkout' ) );

		}
		
	}

 
}
