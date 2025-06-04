<?php
namespace HydraBooking\Admin;

use HydraBooking\Admin\Controller\AdminMenu; 
use HydraBooking\Admin\Controller\Notification;
use HydraBooking\Admin\Controller\UpdateController;
use HydraBooking\Services\Integrations\Zoom\ZoomServices;
use HydraBooking\Migration\Migration;
use HydraBooking\Admin\Controller\NoticeController;
use HydraBooking\Admin\Controller\licenseController;
use HydraBooking\License\HydraBooking; 
// Load Migrator
use HydraBooking\DB\Migrator;

	// exit
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

class Admin {

	// constaract
	public function __construct() { 
		// run migrator
		new Migrator();
	

		// admin menu
		new AdminMenu();
 

		// update controller
		new UpdateController();
		
		// notice controller
		new NoticeController();

		// Notification controller
		// new Notification();

		// activation hooks
		register_activation_hook( TFHB_URL, array( $this, 'activate' ) );

		Migration::instance();

		// license controller
        new  HydraBooking();
		new licenseController();
		

		add_action( 'admin_init', array( $this, 'tfhb_hydra_activation_redirect' ) );

		// Update Existing User Role
		add_action( 'admin_init', array( $this, 'plugins_update_v_1_0_10' ) );
	}

	public function activate() {
		// $Migrator = new Migrator();
		new Migrator();
	}

	public function tfhb_hydra_activation_redirect() {
		if ( ! get_option( 'tfhb_hydra_quick_setup' ) ) {

			update_option( 'tfhb_hydra_quick_setup', 1 );
			wp_redirect( admin_url( 'admin.php?page=hydra-booking#/setup-wizard' ) );

			// exit;
		}
	}

	public function plugins_update_v_1_0_10(){

 
		if( TFHB_VERSION == '1.0.10' && get_option( 'tfhb_update_status' ) != '1.0.10' ) {
			$role = get_role( 'tfhb_host' );
			// remove capabilities
			$role->remove_cap( 'edit_posts' );
			$role->remove_cap( 'edit_pages' );
			$role->remove_cap( 'edit_others_posts' );
			$role->remove_cap( 'create_posts' );
			$role->remove_cap( 'manage_categories' );
			$role->remove_cap( 'publish_posts' );
			$role->remove_cap( 'edit_themes' );
			$role->remove_cap( 'install_plugins' );
			$role->remove_cap( 'update_plugin' );
			$role->remove_cap( 'update_core' );
			$role->remove_cap( 'manage_options' );

			// Tfhb Update Status
			update_option( 'tfhb_update_status', '1.0.10' );
		} 
	}
}
