<?php
namespace HydraBooking\DB;

class Booking {

	public $table = 'tfhb_bookings';
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
                meeting_id INT(11) NULL,
                host_id INT(11) NULL,
                attendee_id INT(11) NULL, 
                post_id INT(11) NULL, 
                hash text NULL,   
                meeting_dates VARCHAR(255) NULL, 
                availability_time_zone LONGTEXT NULL, 
                start_time VARCHAR(20) NULL,
                end_time VARCHAR(20) NULL,
                slot_minutes LONGTEXT NULL, 
                duration LONGTEXT NULL,    
                meeting_locations LONGTEXT NOT NULL,
                meeting_calendar LONGTEXT NULL,
                cancelled_by VARCHAR(255) NULL,
                status VARCHAR(50) NOT NULL, 
                reason VARCHAR(255) NULL, 
                booking_type VARCHAR(20) NULL, 
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

	// 

	/**
	 * Create the database Booking.
	 */
	public function add( $request ) {
	

		global $wpdb;

		$table_name = $wpdb->prefix . $this->table;

		// json encode meeting locations
		// $request['others_info']       = wp_json_encode( $request['others_info'] );
		if(isset($request['meeting_locations'])) { 
			$request['meeting_locations'] = is_array($request['meeting_locations']) || is_object($request['meeting_locations']) ? wp_json_encode( $request['meeting_locations']  ) : $request['meeting_locations']; 
			 
		} 
	
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

		
		// if(isset($request['others_info'])){ 
		// 	$request['others_info']       = wp_json_encode( $request['others_info'] );
		// } 
		if(isset($request['meeting_locations'])) { 
			$request['meeting_locations'] = is_array($request['meeting_locations']) || is_object($request['meeting_locations']) ? wp_json_encode( $request['meeting_locations']  ) : $request['meeting_locations']; 
			 
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
				'update_id' => $wpdb->insert_id,
			);
		}
	}
	/**
	 * Get all  Booking Data.
	 */
	public function get( $where = null, $join = false, $FirstOrFaill = false, $custom = false, $orderBy = null, $limit = null, $user_id = null ) {
		global $wpdb;

		$table_name    = $wpdb->prefix . $this->table;
		$meeting_table = $wpdb->prefix . 'tfhb_meetings';
		$host_table    = $wpdb->prefix . 'tfhb_hosts';

		if ( is_array( $where ) && $join == false ) {
			$sql = "SELECT * FROM $table_name WHERE ";
			$i   = 0;
			foreach ( $where as $k => $v ) {
				if ( $i == 0 ) {
					if ( $k == 'meeting_dates' ) {
						$sql .= " FIND_IN_SET('$v', $k)";
						continue;
					}
					$sql .= " $k = '$v'";

				} else {
					if ( $k == 'meeting_dates' ) {
						$sql .= " AND FIND_IN_SET('$v', $k)";
						continue;
					}
					$sql .= " AND $k = '$v'";
				}
				++$i;
			}
			// Add Order by if exist
			$sql .= $orderBy != null ? " ORDER BY $orderBy" : ' ORDER BY id DESC';

			// Add Limit if exist
			$sql .= $limit != null ? " LIMIT $limit" : '';
 
			if ( $FirstOrFaill == true ) {
				// only get first item
				$data = $wpdb->get_row(
					$sql
				);
			} else {
				$data = $wpdb->get_results(
					$sql 
				);
			}

			// echo $sql;
		} elseif ( $where != null && $join != true ) {
			if ( $custom == true ) {
				$sql = "SELECT * FROM $table_name WHERE $where";
				if ( ! empty( $where ) ) {
					$sql .= $user_id != null ? " AND $table_name.host_id = $user_id" : '';
				}
				$data = $wpdb->get_results(
					$wpdb->prepare( $sql )
				);
			} else {
				$sql  = "
                    SELECT $table_name.*, 
                    $host_table.email AS host_email,
                    $meeting_table.post_id,
                    $meeting_table.title AS meeting_title,
                    $meeting_table.meeting_locations AS meeting_location,
                    $meeting_table.duration AS meeting_duration,
                    $meeting_table.buffer_time_before,
                    $meeting_table.buffer_time_after
                    FROM $table_name
                    INNER JOIN $host_table ON $table_name.host_id = $host_table.id
                    INNER JOIN $meeting_table ON $table_name.meeting_id = $meeting_table.id
                    WHERE $table_name.id = %d
                ";
				$data = $wpdb->get_row( $wpdb->prepare( $sql, $where ) );
			}
		} else {
			if ( $join == true ) {
				$sql = "SELECT 
                $table_name.id,
                $table_name.host_id,
                $table_name.meeting_id,   
                $table_name.meeting_dates,
                $table_name.availability_time_zone,
                $table_name.start_time,
                $table_name.end_time,
                $table_name.status AS booking_status,
                $table_name.created_at AS booking_created_at,
                $meeting_table.host_id,
                $meeting_table.title,
                $meeting_table.duration,
                $table_name.meeting_locations,
                $meeting_table.meeting_price,
                $meeting_table.payment_currency,
                $meeting_table.payment_status AS meeting_payment_status,
                $meeting_table.meeting_type,
                $host_table.first_name AS host_first_name,
                $host_table.last_name AS host_last_name,
                $host_table.email AS host_email,
                $host_table.time_zone AS host_time_zone
                FROM $table_name 
                INNER JOIN $meeting_table
                ON $table_name.meeting_id=$meeting_table.id
                INNER JOIN $host_table
                ON $meeting_table.host_id=$host_table.id";
			} else {
				$sql = "SELECT * FROM $table_name";

			}
			// userwise
			if ( empty( $where ) ) {
				$sql .= $user_id != null ? " WHERE $table_name.host_id = $user_id" : '';
			}
			// custom where
			$sql .= $custom != null ? " WHERE $where" : '';

			if ( ! empty( $where ) ) {
				$sql .= $user_id != null ? " AND $table_name.host_id = $user_id" : '';
			}
			// Add Order by if exist
			$sql .= $orderBy != null ? " ORDER BY $orderBy" : ' ORDER BY id DESC';

			// Add Limit if exist
			$sql .= $limit != null ? " LIMIT $limit" : '';

			$data = $wpdb->get_results( $sql );
		}

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

	// Get Booking with all attendees in one query
	public function getBookingWithAttendees($where = null, $limit = null, $orderBy = null) {
 
		global $wpdb;

		$table_name = $wpdb->prefix . $this->table;
		$attendee_table = $wpdb->prefix . 'tfhb_attendees';
		$meeting_table = $wpdb->prefix . 'tfhb_meetings';
		$host_table    = $wpdb->prefix . 'tfhb_hosts';

		// echo $where;
		// Define the SQL query
		$sql = "SELECT booking.*, 
			COALESCE(
				JSON_ARRAYAGG(
					JSON_OBJECT(
						'id', attendee.id,
						'booking_id', attendee.booking_id,
						'meeting_id', attendee.meeting_id,
						'hash', attendee.hash,
						'attendee_time_zone', attendee.attendee_time_zone,
						'attendee_name', attendee.attendee_name,
						'email', attendee.email,
						'address', attendee.address,
						'others_info', attendee.others_info,
						'country', attendee.country,
						'ip_address', attendee.ip_address,
						'device', attendee.device,
						'cancelled_by', attendee.cancelled_by,
						'status', attendee.status,
						'reason', attendee.reason,
						'payment_method', attendee.payment_method,
						'payment_status', attendee.payment_status,
						'created_at', attendee.created_at
					)
				), 
				JSON_ARRAY()
			) AS attendees,
			meeting.host_id,
			meeting.title,
			meeting.duration, 
			meeting.meeting_price,
			meeting.payment_currency,
			meeting.payment_status AS meeting_payment_status,
			meeting.meeting_type,
			host.first_name AS host_first_name,
			host.last_name AS host_last_name,
			host.email AS host_email,
			host.time_zone AS host_time_zone
			FROM  {$table_name} AS booking
			LEFT JOIN  {$attendee_table} AS attendee  ON booking.id = attendee.booking_id
			INNER JOIN {$meeting_table} As meeting ON meeting.id = booking.meeting_id 
			INNER JOIN {$host_table} AS host  ON host.id = booking.host_id ";

			$data = [];
			if($where != null) { 
				foreach ($where as $key => $condition) {
					if ($key === 'OR' && is_array($condition)) {
						$or_sql = [];
						foreach ($condition as $or_condition) {
							$field = $or_condition[0];
							$operator = $or_condition[1];
							$value = $or_condition[2];
			
							if (strpos($field, '.') === false) {
								$field = 'booking.' . $field;
							}
			
							if ($operator === 'LIKE') {
								$or_sql[] = "$field $operator %s";
								$data[] = $value;
							} else {
								$or_sql[] = "$field $operator %s";
								$data[] = $value;
							}
						}
						if (!empty($or_sql)) {
							$sql .= " AND (" . implode(' OR ', $or_sql) . ")";
						}
					} else {
					    $field =  $condition[0]; 
						if(strpos($field, '.') === false){
							$field = 'booking.'.$condition[0];
						} 

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
			} 

			 // Add grouped `LIKE` conditions
			 if (!empty($like_conditions)) {
				$sql .= " AND (" . implode(' OR ', $like_conditions) . ")";
			}
			
			$sql .= "GROUP BY booking.id ";
			
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

			
			if($results){
				// if its not row
				if($limit > 1 ||  $limit == null) {
					foreach ($results as $key => $result) {
						$attendees = json_decode($result->attendees);
						$results[$key]->attendees = $attendees;
					}
				} 
				if($limit == 1 && $limit != null) {
					$attendees = json_decode($results->attendees);
					$results->attendees = $attendees;
				}
			} 
 
			// echo $wpdb->last_query;
			
		// Return the results
		return $results;


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
                b.address,
                b.meeting_dates,
                b.start_time,
                b.end_time,
                b.status AS booking_status, 
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

		$data = $wpdb->get_row(
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
			// if ( $value->Field == 'id' ) {
			// 	continue;
			// }  
			$columns[] = array(
				'name'  => $value->Field,
				'value' => $value->Field,
			);
		}
		$columns[] = array(
			'name'  => 'attendees',
			'value' => 'attendees',
		);
		return $columns;
	}

	public function importBooking( $data ){
		global $wpdb;
		
		$table_name = $wpdb->prefix . $this->table;
		// Define column names (ensure these match your database table structure)
		 
		$columns = array_keys($data[0]);
		unset($columns['id']); 
		// Build the SQL query
		$values = [];
		foreach ($data as $row) {
			$escaped_values = array_map([$wpdb, 'prepare'], array_values($row));
			$values[] = '(' . implode(',', $escaped_values) . ')';
		}

		$sql = "
			INSERT INTO $table_name (" . implode(',', $columns) . ")
			VALUES " . implode(', ', $values);

		 
		// Execute the query
		$wpdb->query($sql);
	}

	// public function importBooking( $data ) {
	// 	global $wpdb;
	// 	$table_name = $wpdb->prefix . $this->table;
	// 	$columns    = $this->getColumns();
	// 	$columns    = array_column( $columns, 'name' );
	// 	$columns    = implode( ',', $columns );
	// 	$sql        = "INSERT INTO $table_name ($columns) VALUES ";
	// 	$i          = 0;
	// 	// remove the first row and get the columns
	// 	unset( $data[0] );
	// 	// also remove the first item of the
	// 	foreach ( $data as $key => $value ) {
	// 		if ( $value[0] == '' ) {
	// 			continue;
	// 		}
	// 		if ( $i == 0 ) {
	// 			$sql .= '(' . implode(
	// 				',',
	// 				array_map(
	// 					function ( $v ) {
	// 						return "'" . $v . "'";
	// 					},
	// 					$value
	// 				)
	// 			) . ')';
	// 		} else {
	// 			$sql .= ',(' . implode(
	// 				',',
	// 				array_map(
	// 					function ( $v ) {
	// 						return "'" . $v . "'";
	// 					},
	// 					$value
	// 				)
	// 			) . ')';
	// 		}
	// 		++$i;
	// 	}
	// 	// echo $sql;
	// 	// exit;
	// 	$wpdb->query( $sql );
	// }
}
