<?php
namespace HydraBooking\DB;

class Attendees {

	public $table = 'tfhb_attendees';
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
                booking_id INT(11) NULL,
                meeting_id INT(11) NULL,
                host_id INT(11) NULL,  
                hash text NULL,  
                attendee_time_zone VARCHAR(20) NULL,     
                attendee_name VARCHAR(50) NULL, 
                email VARCHAR(100) NOT NULL, 
                address LONGTEXT NULL,
                others_info LONGTEXT NULL,
                country VARCHAR(20) NULL,
                ip_address VARCHAR(50) NULL, 
                device VARCHAR(50) NULL,   
                cancelled_by VARCHAR(255) NULL,
                status VARCHAR(50) NOT NULL, 
                reason VARCHAR(255) NULL,  
                payment_method VARCHAR(20) NOT NULL,
                payment_status VARCHAR(20) NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, 
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
		$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}tfhb_bookings");
	}

	/**
	 * Create the database Booking.
	 */
	public function add( $request ) {

		global $wpdb;

		$table_name = $wpdb->prefix . $this->table;

		// json encode meeting locations
		$request['others_info']       = wp_json_encode( $request['others_info'] );  

		// insert Booking
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
	 * Update the database Booking.
	 */
	public function update( $request ) {

		global $wpdb;

		$table_name = $wpdb->prefix . $this->table;

		$id = $request['id'];
		unset( $request['id'] );

		
		if(isset($request['others_info'])){ 
			$request['others_info']       = wp_json_encode( $request['others_info'] );
		} 
		 
	
		// Update Booking
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
				'update_id' =>  $id,
			);
		}
	}
	/**
	 * Get Attendees Data with booking
	 */
	public function getAttendeeWithBooking( $where = null, $limit = null, $orderBy = null) {
		
		global $wpdb;

		$table_name = $wpdb->prefix . $this->table;
		$booking_table = $wpdb->prefix . 'tfhb_bookings';  
		$meeting_table = $wpdb->prefix . 'tfhb_meetings';  
		$host_table =  $wpdb->prefix . 'tfhb_hosts';
		$sql = "SELECT attendees.*,
			booking.post_id as post_id,
			booking.meeting_dates,
			booking.start_time,
			booking.end_time,
			booking.availability_time_zone, 
			booking.meeting_locations as meeting_locations,
			booking.booking_type as booking_type,
			meeting.title as meeting_title, 
			meeting.description as meeting_content,
			meeting.attendee_can_cancel as attendee_can_cancel, 
			meeting.attendee_can_reschedule as attendee_can_reschedule, 
			meeting.availability_custom as availability_custom, 
			meeting.availability_id as availability_id, 
			meeting.availability_type as availability_type, 
			meeting.duration as duration,
			meeting.buffer_time_before as buffer_time_before,
			meeting.buffer_time_after as buffer_time_after,
			meeting.meeting_interval as meeting_interval,
			host.first_name as host_first_name,
			host.last_name as host_last_name,
			host.email as host_email,
			host.phone_number as host_phone
			FROM $table_name as attendees
			LEFT JOIN $booking_table as booking ON attendees.booking_id = booking.id
			LEFT JOIN $meeting_table as meeting ON booking.meeting_id = meeting.id
			LEFT JOIN $host_table as host ON attendees.host_id = host.id ";
			
			$data = [];
			if($where != null) {
				
				foreach ($where as $condition) {
					$field = 'attendees.'.$condition[0];
					$operator = $condition[1];
					$value = $condition[2]; 
					// first time where clause and others are and 
					if($condition === reset($where)) {
						$sql .= " WHERE $field $operator %s";
					} else {
						$sql .= " AND $field $operator %s";
					}
					$data[] = $value;

				} 
			} 

			

			// $sql .= " GROUP BY booking.id ";
			
			if($orderBy != null) {
				$sql .= " ORDER BY booking.id $orderBy";
			} else {
				$sql .= " ORDER BY booking.id DESC";
			}

			if($limit != null && $limit > 1) {
				$sql .= " LIMIT $limit";
			}   
 
			
			// Prepare the SQL query 
			$query = $wpdb->prepare($sql, $data);
			
			// Get the results
			if($limit == 1) {
				$results = $wpdb->get_row($query); 
				
			} else {
				$results = $wpdb->get_results($query);
			}  
 
		return $results;
	}
	

	//  Count available dates attendees based on booking id
	public function countAvailableAttendees($booking_id) {
		global $wpdb;

		$table_name = $wpdb->prefix . $this->table;

		$sql = "SELECT COUNT(*) as total FROM $table_name WHERE booking_id = %d "; 
		$data = $wpdb->get_row(
			$wpdb->prepare( $sql, $booking_id )
		);
		return $data;

	}

	// 


	/**
	 * Get Booking ID and Dates 
	 * 
	 */
	public function getByMeetingIdDates($meeting_id, $dates) {
		global $wpdb;

		$table_name = $wpdb->prefix . $this->table;
 
		$sql = "SELECT * FROM $table_name WHERE meeting_id = %d AND meeting_dates = %s"; 
		$sql .= " AND status != 'canceled'";
		$data = $wpdb->get_results(
			$wpdb->prepare( $sql, $meeting_id, $dates )
		);
		return $data;

	}



	public function getFilter( $filterData = '' ) {

		global $wpdb;

		$table_name    = $wpdb->prefix . $this->table;
		$meeting_table = $wpdb->prefix . 'tfhb_meetings';
		$host_table    = $wpdb->prefix . 'tfhb_hosts';

		$query = "SELECT 
                b.id,
                b.host_id,
                b.meeting_id,
                b.attendee_name,
                b.email AS attendee_email,
                b.attendee_time_zone AS attendee_time_zone,
                b.address,
                b.meeting_dates,
                b.start_time,
                b.end_time,
                b.status AS booking_status,
                b.payment_status AS payment_status,
                b.created_at AS booking_created_at,
                m.host_id AS meeting_host_id,
                m.title,
                m.duration,
                b.meeting_locations,
                m.meeting_price,
                m.payment_currency,
                m.payment_status AS meeting_payment_status,
                m.meeting_type,
                h.first_name AS host_first_name,
                h.last_name AS host_last_name,
                h.email AS host_email,
                h.time_zone AS host_time_zone
              FROM $table_name AS b
              LEFT JOIN $meeting_table AS m ON b.meeting_id = m.id
              LEFT JOIN $host_table AS h ON b.host_id = h.id
              WHERE 1=1";

		// Apply filter to match meeting title or host name
		if ( ! empty( $filterData['name'] ) ) {
		$title = '%' . $wpdb->esc_like( $filterData['name'] ) . '%';
		$query .= $wpdb->prepare(" AND (m.title LIKE %s OR h.first_name LIKE %s OR h.last_name LIKE %s)", $title, $title, $title);
		}

		return $wpdb->get_results( $query );

	}

	public function getMeetingByBooking( $booking_id = '' ) {

		global $wpdb;
	
		$table_name    = $wpdb->prefix . $this->table;
		$meeting_table = $wpdb->prefix . 'tfhb_meetings';
	
		$query = $wpdb->prepare(
			"SELECT b.*, m.*
			 FROM $table_name AS b
			 LEFT JOIN $meeting_table AS m ON b.meeting_id = m.id
			 WHERE b.id = %d",
			$booking_id
		);
	
		return $wpdb->get_row( $query );
	}

	//getCheckBooking
	public function getCheckBooking( $meeting_id, $meeting_dates, $start_time, $end_time ) {

		// get all bookings order by id desc

		global $wpdb;

		$table_name = $wpdb->prefix . $this->table;

		$sql = "SELECT * FROM $table_name WHERE meeting_id = %d AND meeting_dates = %s AND start_time = %s AND end_time = %s";

		// stats != canceled
		$sql .= " AND status != 'canceled'";

		$data = $wpdb->get_results(
			$wpdb->prepare( $sql, $meeting_id, $meeting_dates, $start_time, $end_time )
		);

		return $data;
		 
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
	// export data
	public function export( $where = null, $join = false, $FirstOrFaill = false ) {
		global $wpdb;
		$table_name = $wpdb->prefix . $this->table;
		$sql        = "SELECT * FROM $table_name";
		if ( $where != null ) {
			foreach ( $where as $key => $value ) {
				$sql .= ' WHERE ' . $value['column'] . ' ' . $value['operator'] . ' ' . $value['value'] . '';

			}
		} else {
			$sql .= ' ORDER BY id DESC';
		}
		$data = $wpdb->get_results( $sql );
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
			if ( $value->Field == 'id' ) {
				continue;
			}
			$columns[ $key ] = array(
				'name'  => $value->Field,
				'value' => $value->Field,
			);
		}
		return $columns;
	}

	public function importBooking( $data ) {
		global $wpdb;
		$table_name = $wpdb->prefix . $this->table;
		$columns    = $this->getColumns();
		$columns    = array_column( $columns, 'name' );
		$columns    = implode( ',', $columns );
		$sql        = "INSERT INTO $table_name ($columns) VALUES ";
		$i          = 0;
		// remove the first row and get the columns
		unset( $data[0] );
		// also remove the first item of the
		foreach ( $data as $key => $value ) {
			if ( $value[0] == '' ) {
				continue;
			}
			if ( $i == 0 ) {
				$sql .= '(' . implode(
					',',
					array_map(
						function ( $v ) {
							return "'" . $v . "'";
						},
						$value
					)
				) . ')';
			} else {
				$sql .= ',(' . implode(
					',',
					array_map(
						function ( $v ) {
							return "'" . $v . "'";
						},
						$value
					)
				) . ')';
			}
			++$i;
		}
		// echo $sql;
		// exit;
		$wpdb->query( $sql );
	}
}
