<?php

/**
 * Plugin Name: Hydra Booking — Appointment Scheduling & Booking Calendar
 * Plugin URI: https://hydrabooking.com/
 * Description: Appointment Booking Plugin with Automated Scheduling - Apple/Outlook/ Google Calendar, WooCommerce, Zoom, Fluent Forms, Zapier, Mailchimp & CRM Integration.
 * Version: 1.2.5
 * Tested up to: 7.0
 * Author: Themefic
 * Author URI: https://themefic.com/
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: hydra-booking
 * Domain Path: /languages
 */

// don't load directly 
defined('ABSPATH') || exit;

use HydraBooking\Admin\Controller\Enqueue;

class THB_INIT
{
	// CONSTARACT
	public function __construct()
	{
		// DEFINE PATH

		define('TFHB_PATH', plugin_dir_path(__FILE__));
		define('TFHB_URL', plugin_dir_url(__FILE__));

		define('TFHB_VERSION', '1.2.5');
		define('TFHB_BASE_FILE', __FILE__);
		define('TFHB_DEV_MODE', false); // Set true to enable dev mode


		// Load Vendor Auto Load
		if (file_exists(TFHB_PATH . '/vendor/autoload.php')) {
			require_once TFHB_PATH . '/vendor/autoload.php';
		}

		// Helper Functions
		if (file_exists(TFHB_PATH . '/includes/Includes.php')) {

			require_once TFHB_PATH . '/includes/Includes.php';
		}


		add_action('init', array($this, 'init'));
		add_action('current_screen', array($this, 'tfhb_get_plugin_screen'));

		add_action('plugins_loaded', array($this, 'tfhb_load_textdomain'));
	}


	function tfhb_load_textdomain()
	{
		// phpcs:ignore PluginCheck.CodeAnalysis.DiscouragedFunctions.load_plugin_textdomainFound
		load_plugin_textdomain('hydra-booking', false, dirname(plugin_basename(__FILE__)) . '/languages/');
	}


	public function init()
	{


		//Register text domain
		// phpcs:ignore PluginCheck.CodeAnalysis.DiscouragedFunctions.load_plugin_textdomainFound
		load_plugin_textdomain('hydra-booking', false, basename(dirname(__FILE__)) . '/languages');


		new HydraBooking\Admin\Controller\ScheduleController();

		// Post Type
		new HydraBooking\PostType\Meeting\Meeting_CPT();
		new HydraBooking\PostType\Booking\Booking_CPT();

		// enqueue
		new Enqueue();

		// Create a New host Role
		new HydraBooking\Admin\Controller\RouteController();
		if (is_admin()) {
			// Load Admin Class
			new HydraBooking\Admin\Admin();
		}

		// Load iCalendar Controller
		new HydraBooking\Admin\Controller\iCalendarController();

		// load Promo Notice
		new HydraBooking\Admin\Controller\PromoNotice();
		new HydraBooking\Admin\Controller\DashboardWidget();

		// Load App Class
		new HydraBooking\App\App();
	}




	public function tfhb_get_plugin_screen()
	{
		$current_screen = get_current_screen();
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if (isset($_GET['page']) && $_GET['page'] === 'hydra-booking') {
			// remove admin notice
			add_action('in_admin_header', array($this, 'tfhb_hide_notices'), 99);
		}
	}

	public function tfhb_hide_notices()
	{
		remove_all_actions('user_admin_notices');
		remove_all_actions('admin_notices');
	}
}



new THB_INIT();
