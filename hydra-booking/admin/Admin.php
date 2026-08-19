<?php

namespace HydraBooking\Admin;

use HydraBooking\Admin\Controller\AdminMenu;
use HydraBooking\Admin\Controller\Notification;
use HydraBooking\Admin\Controller\UpdateController;
use HydraBooking\Services\Integrations\Zoom\ZoomServices;
use HydraBooking\Hooks\Mailer;
use HydraBooking\Migration\Migration;
use HydraBooking\Admin\Controller\NoticeController;
use HydraBooking\Admin\Controller\licenseController;
use HydraBooking\License\HydraBooking;
// Load Migrator
use HydraBooking\DB\Migrator;

// exit
if (! defined('ABSPATH')) {
	exit;
}

class Admin
{

	// constaract
	public function __construct()
	{
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
		register_activation_hook(TFHB_URL, array($this, 'activate'));

		Migration::instance();

		// license controller
		new  HydraBooking();
		new licenseController();


		add_action('admin_init', array($this, 'tfhb_hydra_activation_redirect'));

		// Update Existing User Role
		add_action('admin_init', array($this, 'plugins_update_v_1_0_10'));

		// 
		// add dome in admin footer based one page template
		add_action('admin_footer', array($this, 'add_admin_footer_content'));


		add_action('wp_ajax_tfhb_hydra_manage_plugin', array($this, 'tfhb_hydra_manage_plugin'));

		// send demo mail
		// add_action('admin_init', array( $this, 'tfhb_send_demo_mail' ) );

	}

	public function tfhb_send_demo_mail()
	{

		$to = get_option('admin_email');
		$subject = 'Hydra Booking - Test Email';
		$body = Mailer::mail_body_template([
			'recipient_name' => 'Dear Admin',
			'title'          => esc_html__('Your account has been activated', 'hydra-booking'),
			'subtitle'       => esc_html__('Your account has been successfully activated.', 'hydra-booking'),
			'brand_name'     => get_bloginfo('name'),
			/* translators: %s: Site name */
			'footer_text'    => sprintf(esc_html__('This is an automated email from %s, please do not reply.', 'hydra-booking'), get_bloginfo('name')),
		]);
		$headers = array('Content-Type: text/html; charset=UTF-8');

		Mailer::send($to, $subject, $body, $headers);
	}

	public function add_admin_footer_content()
	{
		if (! function_exists('get_current_screen')) {
			return;
		}

		$screen = get_current_screen();

		if (empty($screen) || 'toplevel_page_hydra-booking' !== $screen->id) {
			return;
		}
	}


	public function tfhb_hydra_manage_plugin()
	{
		check_ajax_referer('wp_rest', 'security');

		if (!current_user_can('install_plugins')) {
			wp_send_json_error(__('You do not have permission to perform this action.', 'hydra-booking'));
		}

		$plugin_slug = isset($_POST['plugin_slug']) ? sanitize_text_field(wp_unslash($_POST['plugin_slug'])) : '';
		$plugin_filename = isset($_POST['plugin_filename']) ? sanitize_text_field(wp_unslash($_POST['plugin_filename'])) : '';
		$plugin_action = isset($_POST['plugin_action']) ? sanitize_text_field(wp_unslash($_POST['plugin_action'])) : '';

		if (!$plugin_slug || !$plugin_action) {
			wp_send_json_error(__('Invalid request.', 'hydra-booking'));
		}

		include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		if ($plugin_action === 'install') {
			$api = plugins_api('plugin_information', ['slug' => $plugin_slug]);

			if (is_wp_error($api)) {
				wp_send_json_error($api->get_error_message());
			}

			$upgrader = new \Plugin_Upgrader(new \WP_Ajax_Upgrader_Skin());
			$install_result = $upgrader->install($api->download_link);

			if (is_wp_error($install_result)) {
				wp_send_json_error($install_result->get_error_message());
			}

			wp_send_json_success(['message' => __('Installed successfully.', 'hydra-booking')]);
		}

		if ($plugin_action === 'activate') {
			$plugin_path = WP_PLUGIN_DIR . '/' . $plugin_slug . '/' . $plugin_filename . '.php';

			if (!file_exists($plugin_path)) {
				wp_send_json_error(__('Plugin file not found.', 'hydra-booking'));
			}

			$activate_result = activate_plugin($plugin_path);

			if (is_wp_error($activate_result)) {
				wp_send_json_error($activate_result->get_error_message());
			}

			wp_send_json_success(['message' => __('Activated successfully.', 'hydra-booking')]);
		}

		wp_send_json_error(__('Invalid action.', 'hydra-booking'));
	}


	public function activate()
	{
		// $Migrator = new Migrator();
		new Migrator();
	}

	public function tfhb_hydra_activation_redirect()
	{
		if (wp_doing_ajax()) {
			return;
		}

		if (! get_option('tfhb_hydra_quick_setup')) {

			update_option('tfhb_hydra_quick_setup', 1);
			wp_safe_redirect(admin_url('admin.php?page=hydra-booking#/setup-wizard'));
			exit;
		}
	}

	public function plugins_update_v_1_0_10()
	{


		if (TFHB_VERSION == '1.0.10' && get_option('tfhb_update_status') != '1.0.10') {
			$role = get_role('tfhb_host');
			// remove capabilities
			$role->remove_cap('edit_posts');
			$role->remove_cap('edit_pages');
			$role->remove_cap('edit_others_posts');
			$role->remove_cap('create_posts');
			$role->remove_cap('manage_categories');
			$role->remove_cap('publish_posts');
			$role->remove_cap('edit_themes');
			$role->remove_cap('install_plugins');
			$role->remove_cap('update_plugin');
			$role->remove_cap('update_core');
			$role->remove_cap('manage_options');

			// Tfhb Update Status
			update_option('tfhb_update_status', '1.0.10');
		}
	}
}
