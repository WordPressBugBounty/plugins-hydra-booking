<?php

namespace HydraBooking\DB;

class Meta {
	// phpcs:disable WordPress.DB.DirectDatabaseQuery
	public $table = 'tfhb_meta';


	public function __construct() {
	}

		/**
		 * Run the database migration.
		 */
	public function migrate() {

		global $wpdb;

		$table_name = $wpdb->prefix . $this->table;

		$charset_collate = $wpdb->get_charset_collate();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			$sql = "CREATE TABLE $table_name (
                id INT(11) NOT NULL AUTO_INCREMENT, 
                object_id INT(11) NOT NULL, 
                object_type VARCHAR(255) NOT NULL,  
                meta_key VARCHAR(255) NOT NULL,  
                value LONGTEXT NULL,
                created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
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

		// insert availability // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.SlowDBQuery.slow_db_query_meta_key
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

		// Update availability
 // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.SlowDBQuery.slow_db_query_meta_key
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
	 * Get all  availability Data.
	 */
	public function get( $where = null, $limit = false ) {
		global $wpdb;
		$table_name = $wpdb->prefix . $this->table;
		$sql        = "SELECT * FROM $table_name";
		$data = [];
		if ( $where != null ) {
			foreach ( $where as $key => $condition ) {
				$field =  $condition[0]; 
				$operator = $condition[1];
				$value = $condition[2];  
				if($key == 0){
					$sql .= " WHERE $field $operator %s";
                    $data[] = $value;
				}else{
                    $sql .= " AND $field $operator %s";
                    $data[] = $value;
                }

			}

			 

		}  
		$sql .= ' ORDER BY id DESC';
		// limit
		if ( $limit != false ) {
			$sql .= " LIMIT $limit";
		}   
		if(!empty($data)) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
			$query = $wpdb->prepare($sql, $data);
		} else {
			$query = $sql; // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		}
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$data = $wpdb->get_results( $query ); 
		return $data;
	}

	// delete
	public function delete( $id ) {
		global $wpdb;
	}
}
