<?php
namespace HydraBooking\DB;

class Meeting {

	public $table = 'tfhb_meetings';
	public function __construct() {
	}

	/**
	 * Run the database migration.
	 */
	public function migrate() {

		global $wpdb;

		$table_name = $wpdb->prefix . $this->table;

		$charset_collate = $wpdb->get_charset_collate();

		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) { // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$sql = "CREATE TABLE $table_name (
                id INT(11) NOT NULL AUTO_INCREMENT, 
                slug VARCHAR(255) NULL,
                host_id INT(11) NULL,
                user_id INT(11) NOT NULL,
                post_id INT(11) NOT NULL,
                title VARCHAR(255) NULL,
                description LONGTEXT NULL,
                meeting_type VARCHAR(255) NOT NULL,
                duration VARCHAR(20) NULL,
                custom_duration VARCHAR(20) NULL,
                meeting_locations LONGTEXT NULL,
                meeting_category VARCHAR(20) NULL,
                availability_range_type VARCHAR(20) NULL,
                availability_range LONGTEXT NULL, 
                availability_type VARCHAR(20) NULL,
                availability_id VARCHAR(11) NULL,
                availability_custom LONGTEXT NULL, 
                buffer_time_before VARCHAR(20) NULL,
                buffer_time_after VARCHAR(20) NULL,
                booking_frequency LONGTEXT NULL,
                meeting_interval VARCHAR(20) NULL,
                recurring_status VARCHAR(20) NULL,
                recurring_repeat LONGTEXT NULL,
                recurring_maximum VARCHAR(20) NULL,
                attendee_can_cancel VARCHAR(20) NULL,
                attendee_can_reschedule VARCHAR(20) NULL,
                questions_type VARCHAR(20) NULL,
                questions_form_type LONGTEXT NULL,
                questions_form LONGTEXT NULL,
                questions LONGTEXT NULL, 
                notification LONGTEXT NULL, 
                payment_status VARCHAR(20) NULL, 
                payment_method VARCHAR(20) NULL, 
                payment_currency VARCHAR(20) NULL, 
                meeting_price VARCHAR(20) NULL, 
                payment_meta LONGTEXT NULL, 
                webhook LONGTEXT NULL, 
                integrations LONGTEXT NULL, 
                max_book_per_slot VARCHAR(20) NULL, 
                is_display_max_book_slot VARCHAR(20) NULL, 
                status VARCHAR(20) NULL, 
                created_by VARCHAR(20) NOT NULL, 
                updated_by VARCHAR(20) NOT NULL, 
                created_at DATE NOT NULL, 
                updated_at DATE NOT NULL, 
                PRIMARY KEY (id)
            ) $charset_collate";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
		}
	}

	/**
	 * Rollback the database migration.
	 */
	public function rollback() {
		global $wpdb;
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}tfhb_meetings" );
	}

	/**
	 * Create the database meeting.
	 */
	public function add( $request ) {

		global $wpdb;


		$table_name = $wpdb->prefix . $this->table;
		// insert meeting
		$result = $wpdb->insert(
			$table_name,
			$request
		);

		
		 
		if ( $result === false ) {
			return false;
		} else {
			
			return array(
				'status'    => true,
				'insert_id' => $wpdb->insert_id,
			);
		}

		
	}
	/**
	 * Update the database meeting.
	 */
	public function update( $request ) {

		global $wpdb;

		$table_name = $wpdb->prefix . $this->table;

		$id = $request['id'];
		unset( $request['id'] );

		// encode json in array data
		if ( isset($request['meeting_locations']) && is_array($request['meeting_locations']) ) {
			$request['meeting_locations'] = wp_json_encode( $request['meeting_locations'] );
		}
		if ( isset($request['availability_range'] ) && is_array($request['availability_range'] )) {
			$request['availability_range'] = wp_json_encode( $request['availability_range'] );
		}
		if ( isset( $request['availability_custom']) && is_array( $request['availability_custom']) ) {
			$request['availability_custom'] = wp_json_encode( $request['availability_custom'] );
		}
		if ( isset($request['booking_frequency']) && is_array($request['booking_frequency']) ) {
			$request['booking_frequency'] = wp_json_encode( $request['booking_frequency'] );
		}
		if ( isset($request['recurring_repeat']) && is_array($request['recurring_repeat']) ) {
			$request['recurring_repeat'] = wp_json_encode( $request['recurring_repeat'] );
		}
		if ( isset($request['questions'] ) && is_array($request['questions'] )) { 
			$request['questions'] = wp_json_encode( $request['questions'] );
		}
		if ( isset($request['notification']) && is_array($request['notification']) ) {
			$request['notification'] = wp_json_encode( $request['notification'] );
		}
		if ( isset($request['payment_meta']) && is_array($request['payment_meta']) ) {
			$request['payment_meta'] = wp_json_encode( $request['payment_meta'] );
		}

		// Update meeting
		$result = $wpdb->update(
			$table_name,
			$request,
			array( 'id' => $id )
		);

		if ( $result === false ) {
			return false;
		} else {
			return array(
				'status'    => true,
				'update_id' => $wpdb->insert_id,
			);
		}
	}
	/**
	 * Get all  meeting Data.
	 */
	public function get( $id = null, $filterData = null, $user_id = null ) {

		global $wpdb;

		$table_name = $wpdb->prefix . $this->table;
		$host_table = $wpdb->prefix . 'tfhb_hosts';
		$booking_table = $wpdb->prefix . 'tfhb_bookings';
		if ( $id ) {
			$data = $wpdb->get_row(
				$wpdb->prepare( "SELECT $table_name.*, COUNT($booking_table.id) as total_booking, $host_table.first_name as host_first_name,  $host_table.last_name as host_last_name FROM $table_name
				LEFT JOIN $booking_table ON $table_name.id = $booking_table.meeting_id
				LEFT JOIN $host_table ON $table_name.host_id = $host_table.id 
				WHERE $table_name.id = %s GROUP BY $table_name.id", $id )
			);
		} elseif ( ! empty( $filterData['title'] ) || ! empty( $filterData['fhosts'] ) || ! empty( $filterData['user_id'] ) || ! empty( $filterData['fcategory'] ) || ( ! empty( $filterData['startDate'] ) && ! empty( $filterData['endDate'] ) ) ) {
			$sql = "SELECT $table_name.*, COUNT($booking_table.id) as total_booking, $host_table.first_name as host_first_name,  $host_table.last_name as host_last_name FROM $table_name LEFT JOIN $booking_table ON $table_name.id = $booking_table.meeting_id LEFT JOIN $host_table ON $table_name.host_id = $host_table.id WHERE";

			$prepare_values = array();
			$has_condition = false;

			if ( ! empty( $filterData['title'] ) ) {
				$title = '%' . $wpdb->esc_like( sanitize_text_field( $filterData['title'] ) ) . '%';
				$sql  .= " $table_name.title LIKE %s";
				$prepare_values[] = $title;
				$has_condition = true;
			}

			if ( isset( $filterData['fhosts'] ) && is_array( $filterData['fhosts'] ) ) {
				$host_ids = array_map( 'absint', $filterData['fhosts'] );
				$sql     .= $has_condition ? ' AND' : '';
				$placeholders = implode( ',', array_fill( 0, count( $host_ids ), '%d' ) );
				$sql     .= " $table_name.host_id IN ($placeholders)";
				$prepare_values = array_merge( $prepare_values, $host_ids );
				$has_condition = true;
			}

			if ( isset( $filterData['fcategory'] ) && is_array( $filterData['fcategory'] ) ) {
				$category_ids = array_map( 'absint', $filterData['fcategory'] );
				$sql         .= $has_condition ? ' AND' : '';
				$placeholders = implode( ',', array_fill( 0, count( $category_ids ), '%d' ) );
				$sql         .= " $table_name.meeting_category IN ($placeholders)";
				$prepare_values = array_merge( $prepare_values, $category_ids );
				$has_condition = true;
			}
			if ( isset( $filterData['user_id'] ) ) { 
				$sql         .= $has_condition ? ' AND' : '';
				$sql         .= " $table_name.user_id = %d";
				$prepare_values[] = absint( $filterData['user_id'] );
				$has_condition = true;
			}

			$sql .= " GROUP BY $table_name.id" ;  

			$sql .= " ORDER BY $table_name.id DESC"; 

			if ( ! empty( $prepare_values ) ) {
				$data = $wpdb->get_results( $wpdb->prepare( $sql, $prepare_values ) );
			} else {
				$data = $wpdb->get_results( $sql );
			}

		 
		} elseif ( ! empty( $user_id ) ) {
			$data = $wpdb->get_results(
				$wpdb->prepare( "SELECT $table_name.*, COUNT($booking_table.id) as total_booking, $host_table.first_name as host_first_name,  $host_table.last_name as host_last_name FROM $table_name
				LEFT JOIN $booking_table ON $table_name.id = $booking_table.meeting_id LEFT JOIN $host_table ON $table_name.host_id = $host_table.id WHERE $table_name.user_id = %s GROUP BY $table_name.id  ORDER BY $table_name.id DESC", $user_id ) 
			);
		} else {  
			$data = $wpdb->get_results(
				"SELECT $table_name.*, COUNT($booking_table.id) as total_booking, $host_table.first_name as host_first_name,  $host_table.last_name as host_last_name  FROM $table_name
				LEFT JOIN $booking_table ON $table_name.id = $booking_table.meeting_id
				LEFT JOIN $host_table ON $table_name.host_id = $host_table.id
				GROUP BY $table_name.id  ORDER BY $table_name.id DESC
				"
			);
		}

		// if any data has json data decode that data

		// Get all data

		return $data;
	}

	// Get Booking with all attendees in one query
	public function getMeetings($where = null, $limit = null, $orderBy = null) {

		global $wpdb;

		$table_name = $wpdb->prefix . $this->table;
		// $attendee_table = $wpdb->prefix . 'tfhb_attendees';
		// $meeting_table = $wpdb->prefix . 'tfhb_meetings';
		// $host_table    = $wpdb->prefix . 'tfhb_hosts';

		// echo $where;
		// Define the SQL query
		$sql = "SELECT * FROM  {$table_name} ";

			$data = [];
			if($where != null) {
				
				foreach ($where as $condition) {
					$field =  $condition[0]; 
					// if(strpos($field, '.') === false){
					// 	$field = 'booking.'.$condition[0];
					// } 

					$operator = $condition[1];
					$value = $condition[2]; 
					if($operator == 'BETWEEN'){  
						$sql .= " AND $field $operator %s AND %s";
						$data[] = $value[0];
						$data[] = $value[1]; 
					}elseif($operator == 'IN'){   
						// value is array 
						$in = implode(',', array_fill(0, count($value), '%s')); 
						$sql .= " AND $field $operator ($in)";
						$data = array_merge($data, $value);
					}elseif($operator == 'LIKE'){   
						// if operator is like 
						$like_conditions[] = "$field $operator %s";
						$data[] = $value; 
					}else{

						$sql .= " AND $field $operator %s";
						$data[] = $value;
					}
				} 
			} 

			// Add grouped `LIKE` conditions
			if (!empty($like_conditions)) {
				$sql .= " AND (" . implode(' OR ', $like_conditions) . ")";
			}
			
			$sql .= "GROUP BY id ";
			
			// Sanitize orderBy - whitelist allowed values
			$allowed_order = array( 'ASC', 'DESC' );
			$orderBy = strtoupper( $orderBy );
			if ( ! in_array( $orderBy, $allowed_order, true ) ) {
				$orderBy = 'DESC';
			}
			
			$sql .= " ORDER BY id $orderBy";

			// Sanitize limit - ensure it's a positive integer
			$limit = $limit !== null ? absint( $limit ) : null;
			if($limit !== null && $limit > 1) {
				$sql .= " LIMIT %d";
				$data[] = $limit;
			}   
	
			
			// Prepare the SQL query 
			$query = $wpdb->prepare($sql, $data);
		
			// Get the results
			if($limit == 1) {
				$results = $wpdb->get_row($query); 
				
			} else {
				$results = $wpdb->get_results($query);
			} 

			
			
	
			// echo $wpdb->last_query;
			
		// Return the results
		return $results;


	}

	/**
	 * Get all  meeting Data.
	 */
	public function getAll($where = null, $sort_by = 'id', $order_by = 'DESC', $limit = false){
		global $wpdb;

		$host_table    = $wpdb->prefix . 'tfhb_hosts';
		$table_name = $wpdb->prefix . $this->table;
		$sql        = "SELECT tfhb_meetings.*, 
					host.first_name AS host_first_name,
					host.last_name AS host_last_name,
					host.email AS host_email,
					host.time_zone AS host_time_zone,
					host.featured_image AS host_featured_image
					FROM $table_name As tfhb_meetings LEFT JOIN {$host_table} AS host  ON host.id = tfhb_meetings.host_id ";	 
		$data = [];
 
			if($where != null) { 
				foreach ($where as $key => $condition) {
					$field =  $condition[0]; 
					if(strpos($field, '.') === false){
						$field = 'tfhb_meetings.'.$condition[0];
					} 

					$operator = $condition[1];
					$value = $condition[2]; 
					if($key == 0){
						$sql .= " WHERE ";
					}else{
						$sql .= " AND ";
					}
					if($operator == 'BETWEEN'){  
						$sql .= " $field $operator %s AND %s";
						$data[] = $value[0];
						$data[] = $value[1]; 
					}elseif($operator == 'IN'){   
						// value is array 
						$values_array = is_array($value) ? $value : explode(',', $value);
						$in = implode(',', array_fill(0, count($values_array), '%d')); // Numeric values
 
						$sql .= " $field $operator ($in)";
						$data = array_merge($data, array_map('intval', $values_array));
					}elseif($operator == 'LIKE'){   
						// if operator is like 
						$like_conditions[] = "$field $operator %s";
						$data[] = $value; 
					}else{

						$sql .= " $field $operator %s";
						$data[] = $value;
					}
				} 
			} 

		
		// Sort and order
		$sql .= " ORDER BY tfhb_meetings.{$sort_by} {$order_by}";

		// Limit
		if ($limit !== false) {
			$sql .= " LIMIT $limit";
		}

		// Prepare and execute
		if(!empty($data)){

			$query = $wpdb->prepare($sql, ...$data);
		}else{
			$query = $sql;
		}
		$data = $wpdb->get_results($query);  
 

		return $data;

	}

	/**
	 * Get with ID
	 */

	 public function getWithID( $id ) {
		global $wpdb;

		$table_name = $wpdb->prefix . $this->table; 

		$data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %s", $id ) );

		return $data;
	 }


	/**
	 * Get all  meeting Data. with total booking count also host id
	 * 
	 */
	public function getWithBookingCount( $id = null, $filterData = null, $user_id = null ) {

		global $wpdb;

		
		$table_name = $wpdb->prefix . $this->table;
		$host_table = $wpdb->prefix . 'tfhb_hosts'; 
		$booking_table = $wpdb->prefix . 'tfhb_bookings';

		$sql = "SELECT $table_name.*, COUNT($booking_table.id) as total_booking
				FROM $table_name  
				LEFT JOIN $booking_table ON $table_name.id = $booking_table.meeting_id";

		if($user_id) {
			$sql .= $wpdb->prepare( " WHERE $table_name.user_id = %s", $user_id );
		}
		$sql .= " GROUP BY $table_name.id" ;

		$data = $wpdb->get_results( $wpdb->prepare( $sql )); 
		return $data;
	}
	
	// Get Only column list as array
	public function getColumns() {
		global $wpdb;
		$table_name = $wpdb->prefix . $this->table;
		$sql        = "SHOW COLUMNS FROM $table_name";
		$data       = $wpdb->get_results( $sql );
		$columns    = array();

	 
		foreach ( $data as $key => $value ) {
			// if ( $value->Field == 'id' ) {
			// 	continue;
			// }  
			$columns[] = array(
				'name'  => $value->Field,
				'value' => $value->Field,
			);
		}
		return $columns;
	}

	
	public function importMeeting( $data ){
		global $wpdb;
		
		$table_name = $wpdb->prefix . $this->table;
		// Define column names (ensure these match your database table structure)
		 
		$columns = $data[0]; 
		unset($data[0]);  
		// Build the SQL query
		$values = [];
		foreach ($data as $row) { 
			$escaped_values = array_map([$wpdb, 'prepare'], array_values($row));
			$values[] = '(' . implode(',', $escaped_values) . ')';
		} 
		$sql = "
			INSERT INTO $table_name (" . implode(',', $columns) . ")
			VALUES " . implode(', ', $values);

			echo $sql;
			exit;
		// Execute the query
		$wpdb->query($sql); 
		
	}


	// delete
	public function delete( $id ) {
		global $wpdb;

		$table_name = $wpdb->prefix . $this->table;
		$result     = $wpdb->delete( $table_name, array( 'id' => $id ) );
		if ( $result === false ) {
			return false;
		} else {
			return array(
				'status'    => true,
				'delete_id' => $id,
			);
		}
	}
}
