<?php
namespace HydraBooking\DB;

class Host {

	public $table = 'tfhb_hosts';
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
                user_id INT(11) NOT NULL, 
                first_name VARCHAR(100) NOT NULL,  
                last_name VARCHAR(100) NOT NULL,  
                email VARCHAR(100) NOT NULL,  
                phone_number VARCHAR(20) NOT NULL,
                about text NOT NULL,
                avatar VARCHAR(255) NOT NULL, 
                featured_image VARCHAR(255) NOT NULL, 
                time_zone VARCHAR(255) NOT NULL, 
                availability_type VARCHAR(11) NULL, 
                availability_id INT(11) NULL, 
                others_information text NULL, 
                status VARCHAR(11) NOT NULL,
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
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}tfhb_hosts" );
	}

	/**
	 * Create the database availability.
	 */
	public function add( $request ) {

		global $wpdb;

		$table_name = $wpdb->prefix . $this->table;

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

		if( isset($request['others_information']) && is_array($request['others_information'])){ 
			$request['others_information'] = wp_json_encode( $request['others_information'] );
		}

		$id = $request['id'];
		unset( $request['id'] );

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
				'status'    => true,
				'update_id' => $id,
			);
		}
	}
	/**
	 * Get all  availability Data.
	 */
	public function get( $where = null, $filterData = ''  ) {

		global $wpdb;

		$table_name = $wpdb->prefix . $this->table;

		if ( is_array( $where ) ) {
			$sql = "SELECT * FROM $table_name WHERE ";
			$i   = 0;
			$prepare_values = array();
			
			// Whitelist allowed field names
			$allowed_fields = array( 'id', 'user_id', 'email', 'status', 'availability_type', 'availability_id' );
			
			foreach ( $where as $k => $v ) {
				// Sanitize field name - whitelist only
				if ( ! in_array( $k, $allowed_fields, true ) ) {
					continue;
				}
				
				if ( $i == 0 ) {
					$sql .= " $k = %s";
				} else {
					$sql .= " AND $k = %s";
				}
				$prepare_values[] = $v;
				++$i;
			}
			
			if ( ! empty( $prepare_values ) ) {
				$data = $wpdb->get_results( $wpdb->prepare( $sql, $prepare_values ) );
			} else {
				$data = array();
			}
		} elseif ( $where != null ) {
			$data = $wpdb->get_row(
				$wpdb->prepare( "SELECT * FROM {$wpdb->prefix}tfhb_hosts WHERE id = %d",$where )
			);
		} elseif ( ! empty( $filterData['name'] ) ) {
			// Corrected SQL query for searching by name
			$data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}tfhb_hosts WHERE concat(first_name, last_name) LIKE %s", '%' . $filterData['name'] . '%' ) );

		} else {
			$data = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}tfhb_hosts");
		}
		// Get all data

		return $data;
	}

	// Get Host By User ID
	public function getHostByUserId( $user_id ) {
		global $wpdb;

		$table_name = $wpdb->prefix . $this->table;

		$data = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM $table_name WHERE user_id = %d", $user_id )
		);

		return $data;
	}

	// Get Host By ID
	public function getHostById( $id ) {
		global $wpdb;

		$table_name = $wpdb->prefix . $this->table;  
		$data = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $id )
		);

		return $data;
	}
 
	// get column 
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
			'name'  =>'_tfhb_host',
			'value' => '_tfhb_host',
		);
		$columns[] = array(
			'name'  => '_tfhb_host_integration_settings',
			'value' => '_tfhb_host_integration_settings',
		);
		return $columns;
	}

 
	/**
	 * Get all  Hosts
	 */
	public function getAll($where = null, $sort_by = 'id', $order_by = 'DESC', $limit = false){
		global $wpdb;
 
		$table_name = $wpdb->prefix . $this->table;
		$sql        = "SELECT *  FROM $table_name";	 
		$data = [];
 
			if($where != null || !empty($where)) { 
				foreach ($where as $key => $condition) {
					$field =  $condition[0]; 
					if(strpos($field, '.') === false){
						$field = $condition[0];
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

		// Sanitize sort_by - whitelist allowed columns
		$allowed_sort_fields = array( 'id', 'user_id', 'first_name', 'last_name', 'email', 'status', 'created_at', 'updated_at' );
		if ( ! in_array( $sort_by, $allowed_sort_fields, true ) ) {
			$sort_by = 'id';
		}

		// Sanitize order_by - whitelist allowed values
		$allowed_order = array( 'ASC', 'DESC' );
		$order_by = strtoupper( $order_by );
		if ( ! in_array( $order_by, $allowed_order, true ) ) {
			$order_by = 'DESC';
		}
		
		// Sort and order
		$sql .= " ORDER BY {$sort_by} {$order_by}";

		// Sanitize limit - ensure it's a positive integer or false
		if ($limit !== false) {
			$limit = absint( $limit );
			if ( $limit > 0 ) {
				$sql .= " LIMIT %d";
				$data[] = $limit;
			}
		}

		if(!empty($data)) {
			// Prepare and execute
			$query = $wpdb->prepare($sql, ...$data);
		} else {
            $query = $sql;
        }
		$data = $wpdb->get_results($query);  
 

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
