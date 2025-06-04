<?php
namespace HydraBooking\Admin\Controller;
 
use HydraBooking\Admin\Controller\SettingsController;
use HydraBooking\Admin\Controller\HostsController;
use HydraBooking\Admin\Controller\MeetingController;
use HydraBooking\Admin\Controller\BookingController;
use HydraBooking\Admin\Controller\AuthController;
use HydraBooking\Admin\Controller\DashboardController;
use HydraBooking\Services\Integrations\GoogleCalendar\GoogleCalendar;
use HydraBooking\Admin\Controller\SetupWizard;
use HydraBooking\Admin\Controller\ImportExport;
use HydraBooking\Admin\Controller\Notification;
use HydraBooking\Admin\Controller\FrontendDashboard;
use HydraBooking\Admin\Controller\licenseController;


// Use DB
use HydraBooking\DB\Availability;

// exit
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

class RouteController {

	// constaract
	public function __construct() { 
		$this->create( new SettingsController(), 'create_endpoint' );
		$this->create( new HostsController(), 'create_endpoint' );
		$this->create( new MeetingController(), 'create_endpoint' );
		$this->create( new BookingController(), 'create_endpoint' );
		$this->create( new AuthController(), 'create_endpoint' );
		$this->create( new GoogleCalendar(), 'create_endpoint' );
		$this->create( new DashboardController(), 'create_endpoint' );
		$this->create( new SetupWizard(), 'create_endpoint' );
		$this->create( new ImportExport(), 'create_endpoint' );
		$this->create( new Notification(), 'create_endpoint' );
		$this->create( new FrontendDashboard(), 'create_endpoint' );
		$this->create( new licenseController(), 'create_endpoint' ); 
	}

	public function create( $class, $function ) {
		add_action( 'rest_api_init', array( $class, $function ) );
	}

	public function permission_callback( \WP_REST_Request $request ) {
		// get header data form request "capability'
		$capability = $request->get_header( 'capability' );
		 
		// check current user have capability
		return current_user_can( $capability );
	}

	public function tfhb_manage_options_permission(){
		return current_user_can( 'tfhb_manage_options' );
	}
	public function tfhb_manage_integrations_permission(){
		return current_user_can( 'tfhb_manage_integrations' );
	}
	public function tfhb_manage_hosts_permission(){
		return current_user_can( 'tfhb_manage_hosts' );
	}
	public function tfhb_manage_custom_availability_permission(){
		return current_user_can( 'tfhb_manage_custom_availability' );
	}
}
