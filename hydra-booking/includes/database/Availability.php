<?php
namespace HydraBooking\DB;

class Availability {
	// phpcs:disable WordPress.DB.DirectDatabaseQuery
	public $table = 'tfhb_availability';
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
                host INT(11) NULL,
                title VARCHAR(100) NOT NULL,
                time_zone VARCHAR(50) NULL, 
                override VARCHAR(255) NULL,
                time_slots LONGTEXT NULL, 
                date_status LONGTEXT NULL, 
                date_slots LONGTEXT NULL, 
                status VARCHAR(20) NULL,
				default_status INT(11) NULL, 
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
		global $wpdb; // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}tfhb_availability");
	}

	/**
	 * Create the database availability.
	 */
	public function add( $request ) {

		global $wpdb;

		$table_name            = $wpdb->prefix . $this->table;
		$request['time_slots'] = maybe_serialize( $request['time_slots'] );
		if(isset($request['date_status'])){
			$request['date_status'] = maybe_serialize( $request['date_status'] );
		} 
 // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.SlowDBQuery.slow_db_query_meta_key
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
		unset( $request['id'] ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.SlowDBQuery.slow_db_query_meta_key
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
	public function get( $where = null, $join = false, $FirstOrFaill = false, $limit = false ) {

		global $wpdb;
		$table_name = $wpdb->prefix . $this->table;
 // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		if ( $where  != null && !is_array($where) ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
			$data = $wpdb->get_row(
				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $where )
			);
		}elseif ( $where  != null && is_array($where) ) {
			$sql = "SELECT * FROM {$table_name} WHERE ";
			$i   = 0;
			$values = [];
			foreach ( $where as $k => $v ) {
				if ( $i == 0 ) {
					$sql .= " $k = %s";
				} else {
					$sql .= " AND $k = %s";
				}
				$values[] = $v;
				++$i; // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			}

			if($FirstOrFaill == true){
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				$data = $wpdb->get_row(
					// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
					$wpdb->prepare( $sql, $values )
				);
			}else{
				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
				$data = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.SlowDBQuery.slow_db_query_meta_key
					// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
					$wpdb->prepare( $sql, $values )
				);
			}
			
		}
		 else {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$data = $wpdb->get_results("SELECT * FROM {$table_name}");
		}

		// Get all data
 // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.SlowDBQuery.slow_db_query_meta_key
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
}
