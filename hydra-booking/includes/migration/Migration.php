<?php
namespace HydraBooking\Migration;

	// exit
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

	use HydraBooking\Migration\ThirdParty\ThirdParty;

class Migration {

	private static $instance;

	/**
	 * @return static
	 */
	public static function instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct() {
		ThirdParty::instance();
	}
}

// call the class
