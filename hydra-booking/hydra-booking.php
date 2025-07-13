<?php
/**
 * Plugin Name: Hydra Booking - All in One Appointment Booking System with Automated Appointment Scheduling.
 * Plugin URI: https://hydrabooking.com/
 * Description: Appointment Booking Plugin with Automated Scheduling - Apple/Outlook/ Google Calendar, WooCommerce, Zoom, Fluent Forms, Zapier, Mailchimp & CRM Integration.
 * Version: 1.1.18
 * Tested up to: 6.8
 * Author: Themefic
 * Author URI: https://themefic.com/
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: hydra-booking
 * Domain Path: /languages
 */

// don't load directly
defined( 'ABSPATH' ) || exit;
require_once ABSPATH . 'wp-admin/includes/plugin.php';

use HydraBooking\Admin\Controller\Enqueue;
class THB_INIT {
	// CONSTARACT
	public function __construct() {
		// DEFINE PATH
    
		define( 'TFHB_PATH', plugin_dir_path( __FILE__ ) );
		define( 'TFHB_URL', plugin_dir_url( __FILE__ ) );

		define( 'TFHB_VERSION', '1.1.18' );
		define( 'TFHB_BASE_FILE', __FILE__);


		// Load Vendor Auto Load
		if ( file_exists( TFHB_PATH . '/vendor/autoload.php' ) ) {
			require_once TFHB_PATH . '/vendor/autoload.php';
		}

		// Helper Function
		// Load Vendor Auto Load
		if ( file_exists( TFHB_PATH . '/includes/Includes.php' ) ) {

			require_once TFHB_PATH . '/includes/Includes.php';
		}
		
	
		add_action( 'init', array( $this, 'init' ) ); 
		add_action( 'current_screen', array( $this, 'tfhb_get_plugin_screen' ) );
		
		add_action('plugins_loaded', array($this, 'tfhb_load_textdomain'));
 
	}


	function tfhb_load_textdomain() {
		load_plugin_textdomain('hydra-booking', false, dirname(plugin_basename(__FILE__)) . '/languages/');
	}

 

	public function init() {

		
		//Register text domain
		load_plugin_textdomain( 'hydra-booking', false, basename( dirname( __FILE__ ) ) . '/languages' );

		// Load Appsero Tracker
		$this->tfhb_appsero_init_tracker_hydra_booking();

		new HydraBooking\Admin\Controller\ScheduleController();

		// Post Type
		new HydraBooking\PostType\Meeting\Meeting_CPT();
		new HydraBooking\PostType\Booking\Booking_CPT();

		// enqueue
		new Enqueue();

		// Create a New host Role
		new HydraBooking\Admin\Controller\RouteController(); 
		if ( is_admin() ) {
			// Load Admin Class
			new HydraBooking\Admin\Admin();
		}

		// Load App Class
		new HydraBooking\App\App();
	}




	public function tfhb_get_plugin_screen() {
		$current_screen = get_current_screen();
		if ( isset( $_GET['page'] ) && $_GET['page'] === 'hydra-booking' ) {
			// remove admin notice
			add_action( 'in_admin_header', array( $this, 'tfhb_hide_notices' ), 99 );
		}
	}

	public function tfhb_hide_notices() {
		remove_all_actions( 'user_admin_notices' );
		remove_all_actions( 'admin_notices' );
	}


	/**
	 * Initialize the plugin tracker
	 *
	 * @return void
	 */
	function tfhb_appsero_init_tracker_hydra_booking() {

		if ( ! class_exists( 'Appsero\Client' ) ) {
			require_once __DIR__ . '/appsero/src/Client.php';
		}

		$client = new Appsero\Client( '685ed86d-9a98-46e2-9f07-79206f5fd69b', 'Hydra Booking &#8211; All-in-One Appointment Management Solution', __FILE__ );
		$notice = sprintf( $client->__trans( 'Want to help make <strong>%1$s</strong> even more awesome? Allow %1$s to collect non-sensitive diagnostic data and usage information. I agree to get Important Product Updates & Discount related information on my email from  %1$s (I can unsubscribe anytime).' ), $client->name );
		$client->insights()->notice( $notice );
		// Active insights
		$client->insights()->init();

	}


}



new THB_INIT();
