<?php
namespace HydraBooking\Admin\Controller;

use HydraBooking\Admin\Controller\TransStrings;
use HydraBooking\Admin\Controller\AuthController;
use HydraBooking\DB\Attendees;
use HydraBooking\DB\Booking;

	// exit
if ( ! defined( 'ABSPATH' ) ) { exit; }

class UpdateController {

    public $version  = TFHB_VERSION; 

	// constaract
	public function __construct() {  

        // update version 1.0.12 to 1.0.13.
        $this->update_1_0_13();

        // Remove it after few releases
        $this->tfhb_check_and_add_upload_cap();
 
		 
	} 

    /**
     * Update Database table structure
     * update version 1.0.8 to 1.0.9
     * @author Sydur Rahman
     * @since 1.0.9
     */

     public function update_1_0_13() { 

        $tfhb_update_status = get_option('tfhb_update_status', false); 
          
        if( $this->version == '1.0.14' && $tfhb_update_status != '1.0.14' ) { 

            // Add column in transaction table
            global $wpdb;
            $table_name = $wpdb->prefix . 'tfhb_transactions';
            // add column in one query
            if( $wpdb->get_var("SHOW COLUMNS FROM $table_name LIKE 'booking_id'") != 'attendees_id' ) {
                $wpdb->query("ALTER TABLE $table_name ADD attendee_id INT(11) NOT NULL AFTER id");
            }
            // tfhb_print_r('Update 1.0.5 to 1.0.6');
            $Attendees = new Attendees();
            $Attendees->migrate();

            // get all booking data
            $Booking_data = new Booking(); 
            $bookings = $Booking_data->get(); 
            // add data to attendees table
            foreach( $bookings as $key => $booking ) {  
                $booking_id = $booking->id;
                $check_booking = $Booking_data->get(
					array(
						'meeting_id'    => $booking->meeting_id,
						'meeting_dates' => $booking->meeting_dates,
						'start_time'    => $booking->start_time,
						'end_time'      => $booking->end_time,
					)
				); 
                if( !empty($check_booking) ) { 
                    if(count($check_booking) > 1) {
                        
                        $get_last_booking = end( $check_booking);
                        
                        if( $get_last_booking->id != $booking->id ) { 

                            $booking_id = $get_last_booking->id;
                            $Booking_data->delete($booking->id);
                        }

                        // $booking->delete($booking->id);
                    }
                }
                

                $data = array(
                    'booking_id' => $booking_id,
                    'meeting_id' => $booking->meeting_id,
                    'host_id' => $booking->host_id,
                    'hash' => $booking->hash,
                    'attendee_time_zone' => $booking->attendee_time_zone,
                    'attendee_name' => $booking->attendee_name,
                    'email' => $booking->email,
                    'address' => $booking->address,
                    'others_info' => json_decode($booking->others_info),
                    'country' => $booking->country,
                    'ip_address' => $booking->ip_address,
                    'device' => $booking->device,
                    'cancelled_by' => $booking->cancelled_by,
                    'status' => $booking->status,
                    'reason' => $booking->reason,
                    'payment_method' => $booking->payment_method,
                    'payment_status' => $booking->payment_status,
                    'created_at' => $booking->created_at,
                    'updated_at' => $booking->updated_at,
                );

                $Attendees->add($data);

                if( !empty($check_booking) ) { 
                    if(count($check_booking) > 1) {
                        
                        $get_last_booking = end( $check_booking);
                        
                        if( $get_last_booking->id != $booking->id ) { 
 
                            $Booking_data->delete($booking->id);
                        }
 
                    }
                }
 

            }  

            // Delete Column from booking table
            global $wpdb;
            $table_name = $wpdb->prefix . 'tfhb_bookings';
            // drop column in one query
            $wpdb->query("ALTER TABLE $table_name 
                DROP COLUMN order_id, 
                DROP COLUMN attendee_time_zone, 
                DROP COLUMN attendee_name, 
                DROP COLUMN email, 
                DROP COLUMN address, 
                DROP COLUMN others_info, 
                DROP COLUMN country, 
                DROP COLUMN ip_address, 
                DROP COLUMN device, 
                DROP COLUMN meeting_calendar,
                DROP COLUMN payment_method,
                DROP COLUMN payment_status,
                -- add column
                ADD COLUMN availability_time_zone  VARCHAR(255) NOT NULL AFTER meeting_dates
                ",
                
            );

           
            // update version
            update_option('tfhb_update_status', '1.0.14');

            
            
        }
        
     }

    //  update Upload Files Capabilities
     public function tfhb_check_and_add_upload_cap() {
        $role_name = 'tfhb_host'; // Change this to the role you want to modify
        $role = get_role($role_name);
    
        if ($role && !$role->has_cap('upload_files')) {
            $role->add_cap('upload_files');
        }
    }

}
