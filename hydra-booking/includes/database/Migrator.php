<?php
namespace HydraBooking\DB;

use HydraBooking\DB\Availability;
use HydraBooking\DB\Attendees;
use HydraBooking\DB\Host;
use HydraBooking\DB\Booking;
use HydraBooking\DB\Meeting;
use HydraBooking\DB\Transactions;
use HydraBooking\DB\Meta;
use HydraBooking\DB\BookingMeta;

class Migrator {

	public function __construct() {
		$this->migrate();
	}

	/**
	 * Run the database migration.
	 */
	public function migrate() {
		// availability migration
		$Availability = new Availability();
		$Availability->migrate();

		// Availability migration
		$Attendees = new Attendees();
        $Attendees->migrate();

		// Host migration
		$Host = new Host();
		$Host->migrate();

		// Booking migration
		$Booking = new Booking();
		$Booking->migrate();

		// Meeting migration
		$Meeting = new Meeting();
		$Meeting->migrate();

		// Transactions migration
		$Transactions = new Transactions();
		$Transactions->migrate();

		// Meta migration
		$Meta = new Meta();
		$Meta->migrate();

		// BookingMeta migration
		$BookingMeta = new BookingMeta();
		$BookingMeta->migrate();
		
	}

	/**
	 * Rollback the database migration.
	 */
	public function rollback() {
		// $this->availability();
	}




	/**
	 * Availability migration.
	 */
	private function availability() {
	}
}
