<?php

namespace HydraBooking\DB;

class BookingMeta {
	public $table = 'tfhb_booking_meta';


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
                meta_key VARCHAR(255) NULL,  
                value LONGTEXT NULL,
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
	}

	/**
	 * Create the database availability.
	 */
	public function add( $request ) {

		global $wpdb;
		$table_name = $wpdb->prefix . $this->table;

		if($request['booking_id'] == null) {
			return false;
		}
		if($request['value'] && is_array($request['value'])) {
			$request['value'] = json_encode($request['value']);
		}

		// insert availability
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
	 * Update the database availability.
	 */
	public function update( $request ) {

		global $wpdb;

		$table_name = $wpdb->prefix . $this->table;

		$id = $request['id'];
		unset( $request['id'] );

		if($request['value'] && is_array($request['value'])) {
			$request['value'] = json_encode($request['value']);
		}
		// Update availability
		$result = $wpdb->update(
			$table_name,
			$request,
			array( 'id' => $id )
		);

		if ( $result === false ) {
			return false;
		} else {
			return array(
				'status' => true,
			);
		}
	}
	/**
	 * Get all  availability Data.
	 */
	public function get( $id ) {

		global $wpdb;

		$table_name = $wpdb->prefix . $this->table;

		$data = $wpdb->get_row(
			$wpdb->prepare("SELECT * FROM {$wpdb->prefix}tfhb_booking_meta WHERE id = %d", $id)
		);

		return $data;
 
	}

	/**
	 * getWithIdKey
	 */
	public function getWithIdKey($id, $key, $limit = null) { 

		// example 
	 

		global $wpdb;

		$table_name = $wpdb->prefix . $this->table;

		if($limit > 1) { 
			$data = $wpdb->get_results(
				$wpdb->prepare("SELECT * FROM {$wpdb->prefix}tfhb_booking_meta WHERE booking_id = %d AND meta_key = %s  LIMIT %d", $id, $key, $limit)
			);
		} else {
			if($limit == 1) {
				$data = $wpdb->get_row(
					$wpdb->prepare("SELECT * FROM {$wpdb->prefix}tfhb_booking_meta WHERE booking_id = %d AND meta_key = %s ORDER BY id DESC", $id, $key)
				);
			} else {
				$data = $wpdb->get_results(
					$wpdb->prepare("SELECT * FROM {$wpdb->prefix}tfhb_booking_meta WHERE booking_id = %d AND meta_key = %s ORDER BY id DESC", $id, $key)
				);
			} 
		} 

		return $data;
	}

	/**
	 * fet first data of multiple ids 
	 * 
	 */
	public function getFirstDataOfMultipleIds($ids, $key) {
		global $wpdb;
 
		
		$table_name = $wpdb->prefix . $this->table;

		// Prepare placeholders for each ID in the IN clause
		$placeholders = implode(',', array_fill(0, count($ids), '%d'));
	
		// Construct the SQL query with placeholders
		$sql = "SELECT * FROM {$wpdb->prefix}tfhb_booking_meta WHERE booking_id IN ($placeholders) AND meta_key = %s";
	
		// Merge $ids and $key into a single array of arguments
		$params = array_merge($ids, [$key]);
	
		// Use call_user_func_array to dynamically apply $params to $wpdb->prepare
		$query = call_user_func_array([$wpdb, 'prepare'], array_merge([$sql], $params));
	
		// Fetch the first matching row
		$data = $wpdb->get_row($query); 
		if($data) {
			return $data;
		} else {
			return false;
		}
	}
	

		/**
		 * Get all  availability Data.
		 */
	public function getFirstOrFail( $where = null ) {

		global $wpdb;

		$table_name = $wpdb->prefix . $this->table;

		if ( $where != null && is_array( $where ) ) {
			$sql = "SELECT * FROM $table_name WHERE ";
			$i   = 0;
			foreach ( $where as $key => $value ) {
				if ( $i == 0 ) {
					$sql .= " $key = $value";
				} else {
					$sql .= " AND $key = $value";
				}
				++$i;
			}
			$data = $wpdb->get_row(
				$wpdb->prepare( $sql )
			);
		} else {
			$data = $wpdb->get_row(
				$wpdb->prepare("SELECT * FROM {$wpdb->prefix}tfhb_booking_meta WHERE id = %d", $where)
			);

		}

		return $data;
	}




	// delete
	public function delete( $id ) {
		global $wpdb;

		$table_name = $wpdb->prefix . $this->table;

		$result = $wpdb->delete(
			$table_name,
			array( 'id' => $id )
		);

		if ( $result === false ) {
			return false;
		} else {
			return array(
				'status' => true,
			);
		}
	}
}
