<?php
namespace HydraBooking\Migration\ThirdParty;

	// exit
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

	use HydraBooking\Migration\ThirdParty\FluentBooking\Migrator;
class ThirdParty {

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
		Migrator::instance();
	}
}

// call the class
