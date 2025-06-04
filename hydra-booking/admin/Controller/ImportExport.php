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
		register_rest_route(
			'hydra-booking/v1',
			'/settings/import-export',
			array(
				'methods'  => 'GET',
				'callback' => array( $this, 'GetImportExportData' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
			)
		);
		register_rest_route(
			'hydra-booking/v1',
			'/settings/import-export/export-meeting-csv',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'ExportMeeting' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
			)
		);

		register_rest_route(
			'hydra-booking/v1',
			'/import-export/import-meeting',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'ImportMeeting' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
			)
		);
		// Import Booking
		register_rest_route(
			'hydra-booking/v1',
			'/settings/import-export/import-booking',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'ImportBooking' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
			)
		);
	}

	public function GetImportExportData() {
		// Get booking Data
		$booking  = new Booking();
		$bookings = $booking->getColumns();

		$data = array(
			'status'         => true,
			'booking_column' => $bookings,
		);
		return rest_ensure_response( $data );
	}

	// Export booking Data
	public function ImportBooking() {
		$request = json_decode( file_get_contents( 'php://input' ), true );
		$data    = isset( $request['data'] ) ? $request['data'] : array();
		$columns = isset( $request['columns'] ) ? $request['columns'] : array();

		// rearrange data first array value based on columns
		$firstData = $data[0];
		$newData   = array();
		foreach ( $columns as $key => $column ) {
			// if column name is match with first data value update that frist data value form column value
			// get the first data key
			$firstDataKey = array_search( $column, $firstData );
			if ( $firstDataKey !== false ) {
				$firstData[ $firstDataKey ] = $data[0][ $key ];
			}
		}
		$data[0] = $newData;
		 

		$booking = new Booking();
		$booking->importBooking( $data );
		 
		$data = array(
			'status'  => true,
			'data'    => true,
			'message' =>  __( 'Booking Data Imported Successfully', 'hydra-booking' ),
		);
		return rest_ensure_response( $data );
	}
}
