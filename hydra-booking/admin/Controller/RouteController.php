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
use HydraBooking\Admin\Controller\Notification;
use HydraBooking\Admin\Controller\FrontendDashboard;
use HydraBooking\Admin\Controller\licenseController;
use HydraBooking\Admin\Controller\iCalendarController;


// Use DB
use HydraBooking\DB\Availability;

// exit
if (! defined('ABSPATH')) {
	exit;
}

class RouteController
{

	// constaract
	public function __construct()
	{
		$this->create(new SettingsController(), 'create_endpoint');
		$this->create(new HostsController(), 'create_endpoint');
		$this->create(new MeetingController(), 'create_endpoint');
		$this->create(new BookingController(), 'create_endpoint');
		$this->create(new AuthController(), 'create_endpoint');
		$this->create(new GoogleCalendar(), 'create_endpoint');
		$this->create(new DashboardController(), 'create_endpoint');
		$this->create(new SetupWizard(), 'create_endpoint');
		$this->create(new Notification(), 'create_endpoint');
		$this->create(new FrontendDashboard(), 'create_endpoint');
		$this->create(new licenseController(), 'create_endpoint');
		$this->create(new iCalendarController(), 'create_endpoint');
	}

	public function create($class, $function)
	{
		add_action('rest_api_init', array($class, $function));
	}

	public function tfhb_manage_options_permission()
	{
		if (current_user_can('manage_options')) {
			return true;
		}
		return current_user_can('tfhb_manage_settings');
	}
	public function tfhb_manage_integrations_permission()
	{
		return current_user_can('tfhb_manage_integrations');
	}
	public function tfhb_manage_hosts_permission()
	{
		return current_user_can('tfhb_manage_hosts');
	}
	public function tfhb_manage_custom_availability_permission()
	{
		return current_user_can('tfhb_manage_custom_availability');
	}
	public function tfhb_manage_settings_permission()
	{
		// manage_options is core WordPress and only real administrators hold it, so it's a
		// reliable bypass regardless of what the tfhb_host role's own capability is set to.
		if (current_user_can('manage_options')) {
			return true;
		}
		return current_user_can('tfhb_manage_settings');
	}
	public function tfhb_manage_admin_only_permission()
	{
		return current_user_can('manage_options');
	}
}
