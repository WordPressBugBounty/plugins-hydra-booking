<?php
namespace HydraBooking\Admin\Controller;
// exit
if ( ! defined( 'ABSPATH' ) ) { exit; }
// Use DB 
use HydraBooking\Admin\Controller\RouteController;
use HydraBooking\DB\Booking;
use HydraBooking\DB\Host;
use HydraBooking\Admin\Controller\DateTimeController;
use HydraBooking\DB\Meeting;

 
class ImportExport {


	// constaract
	public function __construct() {
		
 
	}

	public function create_endpoint() {
		// // tfhb_print_r('hello world');
		// register_rest_route(
		// 	'hydra-booking/v1',
		// 	'/settings/import-export',
		// 	array(
		// 		'methods'  => 'GET',
		// 		'callback' => array( $this, 'GetImportExportData' ),
		// 		'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
		// 	)
		// );
		// register_rest_route(
		// 	'hydra-booking/v1',
		// 	'/settings/import-export/export-all-data',
		// 	array(
		// 		'methods'  => 'POST',
		// 		'callback' => array( $this, 'ExportAllData' ),
		// 		'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
		// 	)
		// );  
		// // import export host
		// register_rest_route(
		// 	'hydra-booking/v1',
		// 	'/settings/import-export/export-hosts',
		// 	array(
		// 		'methods'  => 'GET',
		// 		'callback' => array( $this, 'ExportHosts' ),
		// 		'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
		// 	)
		// );

		// register_rest_route(
		// 	'hydra-booking/v1',
		// 	'/settings/import-export/import-host',
		// 	array(
		// 		'methods'  => 'POST',
		// 		'callback' => array( $this, 'ImportHost' ),
		// 		'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
		// 	)
		// );
		// // Import Booking
		// register_rest_route(
		// 	'hydra-booking/v1',
		// 	'/settings/import-export/import-booking',
		// 	array(
		// 		'methods'  => 'POST',
		// 		'callback' => array( $this, 'ImportBooking' ),
		// 		'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
		// 	)
		// );
		// // Import Booking
		// register_rest_route(
		// 	'hydra-booking/v1',
		// 	'/settings/import-export/import-all-data',
		// 	array(
		// 		'methods'  => 'POST',
		// 		'callback' => array( $this, 'ImportAllData' ),
		// 		'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
		// 	)
		// );
	}

	public function GetImportExportData() {
		
		// Get booking Data
		$booking  = new Booking();
		$bookings = $booking->getColumns(); 
		// Meeting 
		$meeting = new Meeting();
		$meetings = $meeting->getColumns();
		// Host
		$host = new Host();
		$hosts = $host->getColumns();

		// Meeting List 
		$meeting_data = $this->getMeetingList();
		$meeting_list = [];
		foreach($meeting_data as $key => $value){
			$name = '#'.$value->id.' '.$value->title;
			if($value->title == ''){
				$name = '#'.$value->id.' No Title';
			}
			$meeting_list[] = array(
				'name'  => $name,
				'value' => $value->id,
			);
		} 
	 
		$data = array(
			'status'         => true,
			'booking_column' => $bookings,
			'meeting_column' => $meetings,
			'host_column'    => $hosts,
			'meeting_list'    => $meeting_list,
		);
		return rest_ensure_response( $data );
	}

	// Export all data to Json fromat
	public function ExportAllData(){
		$request = json_decode( file_get_contents( 'php://input' ), true );
		$select_export    = isset( $request['select_export'] ) ? $request['select_export'] : array();
		$type    = isset( $request['type'] ) ? $request['type'] : array();
		$file_name = 'hdyra-settings';
		// check if current user has permission to export all data
		if ( ! current_user_can( 'tfhb_manage_options' ) ) {
			return rest_ensure_response( array(
				'status'  => false,
				'message' => __( 'You do not have permission to export all data.', 'hydra-booking' ),
			) );
		}
		// if $select_export is empty 
		if ( empty($select_export) ) {
			return rest_ensure_response( array(
				'status'  => false,
				'message' =>  __( 'Please select at least one option to export.', 'hydra-booking' ),
			) );
		}
		$export_array = [];
		if(in_array('Settings', $select_export)){ 
			$_tfhb_general_settings = get_option( '_tfhb_general_settings' ); 
			$_tfhb_availability_settings = get_option( '_tfhb_availability_settings' );
			$_tfhb_integration_settings = get_option( '_tfhb_integration_settings' );
			$_tfhb_notification_settings = get_option( '_tfhb_notification_settings' );
			$_tfhb_hosts_settings = get_option( '_tfhb_hosts_settings' );
			$_tfhb_appearance_settings = get_option( '_tfhb_appearance_settings' );
			$export_array['settings']['_tfhb_general_settings'] = $_tfhb_general_settings;
			$export_array['settings'][ '_tfhb_availability_settings'] = $_tfhb_availability_settings;
			$export_array['settings'][ '_tfhb_integration_settings'] = $_tfhb_integration_settings;
			$export_array['settings'][ '_tfhb_notification_settings'] = $_tfhb_notification_settings;
			$export_array['settings'][ '_tfhb_hosts_settings'] = $_tfhb_hosts_settings;
			$export_array['settings'][ '_tfhb_appearance_settings'] = $_tfhb_appearance_settings; 
		}
		if(in_array('Hosts', $select_export)){
			$host = new Host(); 
			$host_data = (array) $host->get(); 
			$host_data = json_decode(json_encode($host_data), true);
			
			foreach($host_data as $key => $value){
				// get host user data
				$_tfhb_host = get_user_meta( $value['user_id'], '_tfhb_host', true );
				
				$_tfhb_host_integration_settings = get_user_meta( $value['user_id'], '_tfhb_host_integration_settings', true );
				
				// added host user data 
				$host_data[$key]['_tfhb_host'] = $_tfhb_host;
				$host_data[$key]['_tfhb_host_integration_settings'] = $_tfhb_host_integration_settings;

			} 
			$export_array['tfhb_hosts'] =  $host_data;
		}
		if(in_array('Meetings', $select_export)){
			$meeting = new Meeting(); 
			// get data object to array 
			$meeting_data = $meeting->getMeetings();
			$meeting_data = json_decode(json_encode($meeting_data), true);
			$export_array['tfhb_meetings'] =  $meeting_data;
		}
		if(in_array('Bookings', $select_export)){
			$booking = new Booking();
			// get booking with attendees
			$bookings_data = $booking->getBookingWithAttendees(); 
			foreach($bookings_data as $key => $value){
				// unset some data
				unset($bookings_data[$key]->title);
				unset($bookings_data[$key]->meeting_price);
				unset($bookings_data[$key]->payment_currency);
				unset($bookings_data[$key]->meeting_payment_status);
				unset($bookings_data[$key]->meeting_type);
				unset($bookings_data[$key]->host_first_name);
				unset($bookings_data[$key]->host_last_name);
				unset($bookings_data[$key]->host_email);
				unset($bookings_data[$key]->host_time_zone);
			    
			}
			
			$bookings_data = json_decode(json_encode($bookings_data), true);
		 
			$export_array['tfhb_bookings'] =  $bookings_data;
		} 
	 
	 

		// return data as json format 
		// Return response
		$data = array(
			'status'    => true,
			'data'      =>  json_encode($export_array),
			'file_name' => $file_name.'.json',
			'message'   =>  __('Hosts Data Exported Successfully!', 'hydra-booking'),
		);
		return rest_ensure_response( $data );
		
	}

	// Import All Data from Json format
	public function ImportAllData(){
		$request = json_decode( file_get_contents( 'php://input' ), true );
		$import_data    = isset( $request['import_data'] ) ? $request['import_data'] : array();
		$select_import = isset( $request['select_import'] ) ? $request['select_import'] : array();  
		$is_overwrite_host = isset( $request['is_overwrite_host'] ) ? $request['is_overwrite_host'] : true; 
		$is_create_new_user = isset( $request['is_create_new_user'] ) ? $request['is_create_new_user'] : true; 
		$is_overwrite_meeting = isset( $request['is_overwrite_meeting'] ) ? $request['is_overwrite_meeting'] : true; 
		$is_overwrite_booking = isset( $request['is_overwrite_booking'] ) ? $request['is_overwrite_booking'] : true; 
		$is_default_meeting = isset( $request['is_default_meeting'] ) ? $request['is_default_meeting'] : true; 
		$default_meeting_id = isset( $request['default_meeting_id'] ) ? $request['default_meeting_id'] : 0; 

		$import_logs = [];
		// if currect user dosent have permission to import all data
		if ( !current_user_can( 'tfhb_manage_options' ) ) {
			return rest_ensure_response( array(
				'status'  => false,
				'message' =>  __( 'You do not have permission to import all data.', 'hydra-booking' ),
			) );
		}
		// if $select_import is empty
		if ( empty( $select_import ) ) {
			return rest_ensure_response( array(
				'status'  => false,
				'message' =>  __( 'Please select at least one option to import.', 'hydra-booking' ),
			) );
		}
		// if $import_data is empty
		if ( empty( $import_data ) ) {
			return rest_ensure_response( array(
				'status'  => false,
				'message' =>  __( 'Invalid data provided.', 'hydra-booking' ),
			) );
		}
		// Select general settingss
		if(isset($select_import['settings']) && $select_import['settings'] == true){
			$settings = $import_data['settings'];
			// update option settings
			update_option( '_tfhb_general_settings', $settings['_tfhb_general_settings'] );
			update_option( '_tfhb_availability_settings', $settings['_tfhb_availability_settings'] );
			update_option( '_tfhb_integration_settings', $settings['_tfhb_integration_settings'] );
			update_option( '_tfhb_notification_settings', $settings['_tfhb_notification_settings'] );
			update_option( '_tfhb_hosts_settings', $settings['_tfhb_hosts_settings'] );
			update_option( '_tfhb_appearance_settings', $settings['_tfhb_appearance_settings'] );
			$import_logs['settings'] =  __( 'Settings imported successfully.', 'hydra-booking' );
		}

		// Import Host 
		if(isset($select_import['tfhb_hosts']) && $select_import['tfhb_hosts'] == true){
			$hosts_data = $import_data['tfhb_hosts'];
			// tfhb_print_r($hosts_data);

			$host = new Host();
			foreach( $hosts_data as $key => $value){
				$user_id = $value['user_id'];
				// Check if user is not available in the row using id 
				if($user_id !='' ){ 
					$check_user = get_user_by( 'id', $user_id ); 
					
						if(empty($check_user)){
							// create new user
							// check user by email
							$check_user = get_user_by( 'email', $value['email'] );
							if(empty($check_user)){
								if($is_create_new_user == true){
								// create new user
									$user_id = wp_create_user( $value['email'], 'password', $value['email'] );
									$new_row['user_id'] = $user_id;

								}else{
									continue;
								}
							}else{
								$new_row['user_id'] = $check_user->ID;
							} 
						}else{
							$new_row['user_id'] = $check_user->ID;
						}
					
				}else{
					$user_id = wp_create_user( $value['email'], 'password', $value['email'] );
					$value['user_id'] = $user_id;
				}
				$_tfhb_host = $value['_tfhb_host'];
				$_tfhb_host_integration_settings = $value['_tfhb_host_integration_settings'];
				// unset 
				unset($value['_tfhb_host']);
				unset($value['_tfhb_host_integration_settings']);
 
				if($is_overwrite_host == true){
					$hostData = $host->get( $value['id'] );
					if(!empty($hostData)){   
	
						unset($value['id']);
						$host->add($value);
	 
					}else{ 
						$host->update($value);
					}
				}else{  
 
					unset($value['id']);
					$host->add($value);
				} 
				// update user meta
				if($user_id !='' ){
					update_user_meta( $user_id, '_tfhb_host', $_tfhb_host, true );
					update_user_meta( $user_id, '_tfhb_host_integration_settings', $_tfhb_host_integration_settings, true );
				}
			}
			$import_logs['hosts']=  __( 'Host imported successfully.', 'hydra-booking' );
			 
		} 

		// Import Meeting Data
		if(isset($select_import['tfhb_meetings']) && $select_import['tfhb_meetings'] == true){
			$hosts_data = $import_data['tfhb_meetings'];
			// tfhb_print_r($hosts_data);

			$meeting = new Meeting();
			$host = new Host();
			$host_id = $host->getHostByUserId( get_current_user_id() );
			foreach( $hosts_data as $key => $value){
				// Check if host is not available in the row
				if($value['host_id'] !='' ){
					$check_host = $host->getHostById($value['host_id']);
					if(empty($check_host)){
						$value['host_id'] = $host_id;
						$value['user_id'] = get_current_user_id();
					}else{
						$value['user_id'] = $check_host->user_id;
					}
				}else{
					$value['host_id'] = $host_id;
					$value['user_id'] = get_current_user_id();
				} 
				
				if($is_overwrite_host == true){
					$meeting->update($value);
				}else{
					// unset id 
					unset($value['id']);
					$meeting->add($value);
				}  
				if($is_overwrite_meeting == true){
					$meetingData = $meeting->get( $value['id'] );
					if(!empty($meetingData)){   
	
						unset($value['id']);
						$meeting->add($value);
	 
					}else{ 
						$meeting->update($value);
					}
				}else{  
 
					unset($value['id']);
					$host->add($value);
				} 
			}
			
			$import_logs['meeting']=  __( 'Host imported successfully.', 'hydra-booking' );
			 
		}


		// Import Booking Data
		if(isset($select_import['tfhb_bookings']) && $select_import['tfhb_bookings'] == true){
			$bookinsg_data = $import_data['tfhb_bookings'];
			// tfhb_print_r($hosts_data);

			$booking = new Booking();
			$host = new Host();
			$meeting = new meeting();
			$host_id = $host->getHostByUserId( get_current_user_id() );
			foreach( $bookinsg_data as $key => $value){ 

				// if meeting is not available in the row
				if($is_default_meeting == true){
					
					// check meeting is exisist or not 
					$meeting = new Meeting();
					$meetingData = !empty($value['meeting_id']) ||  $value['meeting_id'] != null ? $meeting->getWithID( $value['meeting_id'] ) : '';
					if(empty($meetingData)){ 
						$default_meeting = $meeting->getWithID( $default_meeting_id );	
						$value['meeting_id'] = $default_meeting_id;
						$value['host_id'] = $default_meeting->host_id;
					}
				}
				$attendees = $value['attendees'];
				unset($value['attendees']);
				// if current meeting id is exist and overwrite is true 
				
				if($is_overwrite_booking == true){
					$bookingData = $booking->get( $value['id'] );
					if(!empty($bookingData)){   

						unset($value['id']);
						$booking->add($value);

					}else{ 
						$booking->update($value);
					}
				}else{  
					unset($value['id']);
					$booking->add($value);
				}
					 
			}
			
			$import_logs['booking'] =  __( 'Booking imported successfully.', 'hydra-booking' );
				
		}

		// import meeting


		// tfhb_print_r($select_import['settings']);
		// return;
		// return success message
		$data = array(
			'status'  => true,
			'import_logs' => $import_logs,
			'message' =>  __( 'Data imported successfully.', 'hydra-booking' ),
		);
		return rest_ensure_response( $data );
	}

 
	// Export booking Data
	public function ImportHost() {
		$request = json_decode( file_get_contents( 'php://input' ), true );
		$data    = isset( $request['data'] ) ? $request['data'] : array();
		$columns = isset( $request['column'] ) ? $request['column'] : array();
		$is_overwrite = isset( $request['is_overwrite'] ) ? $request['is_overwrite'] : false;
		$is_create_new_user = isset( $request['is_create_new_user'] ) ? $request['is_create_new_user'] : false;
		 // current user host id 
		$host = new Host();
		$host_id = $host->getHostByUserId( get_current_user_id() );
	
		if ( empty( $data ) || empty( $columns ) ) {
			return rest_ensure_response( array(
				'status'  => false,
				'message' => __( 'Invalid data provided.', 'hydra-booking' ),
			) );
		} 
		$header = $data[0];
		$data_rows = array_slice($data, 1);
		
		// Map header to index
		$header_map = array_flip($header);
	 
		$host = new Host();
		foreach ($data_rows as $row) {
			$new_row = []; 
			$data = []; 
			if (empty(array_filter($row))) {
				continue;
			}
			// tfhb_print_r($row);
			foreach ($columns as $key => $column) {
				if($column == '' || ( $key == 'id' && $is_overwrite == false)){
					continue; // skip empty column
				}
				if (isset($header_map[$column])) {
					$data[] = $row[$header_map[$column]];
					$new_row[$key] = $row[$header_map[$column]];
				} else {
					$data[] = null; // or empty string
					$new_row[$key] = null; // or empty string
				}
 
			}  
			$user_id = $new_row['user_id'];
			// Check if user is not available in the row using id 
			if($user_id !='' ){ 
				$check_user = get_user_by( 'id', $user_id ); 
				
					if(empty($check_user)){
						// create new user
						// check user by email
						$check_user = get_user_by( 'email', $new_row['email'] );
						if(empty($check_user)){
							if($is_create_new_user == true){
							// create new user
								$user_id = wp_create_user( $new_row['email'], 'password', $new_row['email'] );
								$new_row['user_id'] = $user_id;

							}else{
								continue;
							}
						}else{
							$new_row['user_id'] = $check_user->ID;
						} 
					}else{
						$new_row['user_id'] = $check_user->ID;
					}
				
			 }else{
				$user_id = wp_create_user( $new_row['email'], 'password', $new_row['email'] );
				$new_row['user_id'] = $user_id;
			 }

			if($is_overwrite == true){
				$host->update($new_row);
			}else{
				unset($new_row['id']);
				$host->add($new_row);
			}   
		}


		$data = array(
			'status'  => true,
			'data'    => true,
			'message' =>  __( 'Host Imported Successfully', 'hydra-booking' ),
		);
		return rest_ensure_response( $data );
 
	}
	
	// Import booking Data
	public function ImportBooking() {
		$request = json_decode( file_get_contents( 'php://input' ), true );
		$data    = isset( $request['data'] ) ? $request['data'] : array();
		$columns = isset( $request['columns'] ) ? $request['columns'] : array();
		$is_overwrite = isset( $request['is_overwrite'] ) ? $request['is_overwrite'] : true;
		$is_default_meeting = isset( $request['is_default_meeting'] ) ? $request['is_default_meeting'] : true;
		$default_meeting_id = isset( $request['default_meeting_id'] ) ? $request['default_meeting_id'] : true;

		// current user host id 
		$host = new Host();
		$host_id = $host->getHostByUserId( get_current_user_id() );
	  
		if ( empty( $data ) || empty( $columns ) ) { 
			return rest_ensure_response( array(
				'status'  => false,
				'message' => __( 'Invalid data provided.', 'hydra-booking' ),
			) );
		}

		$booking = new Booking();
		$header = $data[0];
		$data_rows = array_slice($data, 1);
		
		// Map header to index
		$header_map = array_flip($header);
	 
		foreach ($data_rows as $row) { 
			$new_row = []; 
			$data = []; 
			if (empty(array_filter($row))) {
				continue;
			}
			// tfhb_print_r($row);
			foreach ($columns as $key => $column) {
				if($column == '' ){
					continue; // skip empty column
				}
				if (isset($header_map[$column])) {
					$data[] = $row[$header_map[$column]];
					$new_row[$key] = $row[$header_map[$column]];
				} else {
					$data[] = null; // or empty string
					$new_row[$key] = null; // or empty string
				} 
			}  


			// if meeting is not available in the row
			if($is_default_meeting == true){
				// check meeting is exisist or not 
				$meeting = new Meeting();
				$meetingData = $meeting->getWithID( $new_row['meeting_id'] );
				if(empty($meetingData)){ 
					$default_meeting = $meeting->getWithID( $default_meeting_id );	
					$new_row['meeting_id'] = $default_meeting_id;
					$new_row['host_id'] = $default_meeting->host_id;
				}
			}
			
			 
			$attendees = $new_row['attendees'];
			unset($new_row['attendees']);
			// if current meeting id is exist and overwrite is true 
		
			if($is_overwrite == true){
				$bookingData = $booking->get( $new_row['id'] );
				if(!empty($bookingData)){   

					unset($new_row['id']);
					$booking->add($new_row);
 
				}else{ 
					$booking->update($new_row);
				}
			}else{  
				unset($new_row['id']);
				$booking->add($new_row);
			}
			
		 
		}
		 
		$data = array(
			'status'  => true,
			'data'    => true,
			'message' =>  __( 'Booking Data Imported Successfully', 'hydra-booking' ),
		);
		return rest_ensure_response( $data );
	}

 

	/**
	 * Export Hosts Data 
	 * @since 1.1.0
	 * @author Sydur Rahman
	 * */
	public function ExportHosts(){
		$host = new Host();
		$hostsLists = $host->get(); 
		$file_name = 'hydra-hosts-data';
		// check if current has manage option caps
		if ( ! current_user_can( 'tfhb_manage_options' ) ) {
			$data = array(
				'status'  => false,
				'message' =>  __('You do not have permission to export hosts data!', 'hydra-booking' ),
			);
			return rest_ensure_response( $data );
		} 
		$data_array  = array();
		$data_column = array();
		foreach ( $hostsLists as $key => $book ) {
			
			if ( $key == 0 ) {
				foreach ( $book as $c_key => $c_value ) {
					$data_column[] = $c_key;
				}
			}
			$book->attendees = json_encode($book->attendees); 
			$data_array[] = (array) $book;
		} 

		ob_start();
		$file = fopen( 'php://output', 'w' );
		fputcsv( $file, $data_column );

		foreach ( $data_array as $booking ) {
			fputcsv( $file, $booking );
		}

		fclose( $file );
		$data = ob_get_clean();
		// Return response
		$data = array(
			'status'    => true,
			'data'      => $data,
			'file_name' => $file_name.'.csv',
			'message'   =>  __('Hosts Data Exported Successfully!', 'hydra-booking'),
		);
		return rest_ensure_response( $data );
	}


	public function getMeetingList() {
		$current_user = wp_get_current_user();
		// get user role
		$current_user_role = ! empty( $current_user->roles[0] ) ? $current_user->roles[0] : '';
		$current_user_id   = $current_user->ID;

		// Meeting Lists
		$meeting = new Meeting();

		if ( ! empty( $current_user_role ) && 'administrator' == $current_user_role ) {
			$MeetingsList = $meeting->get();
		} 

		if ( ! empty( $current_user_role ) && 'tfhb_host' == $current_user_role ) {
			$MeetingsList = $meeting->get( null, null, $current_user_id );
		}

		// add meeting permalink key into the meeting list using post id using array map
		$MeetingsList = array_map(
			function ( $meeting ) {
				$meeting->permalink = get_permalink( $meeting->post_id );
				return $meeting;
			},
			$MeetingsList
		);
		return $MeetingsList;
	}
}
