<?php

namespace HydraBooking\PostType\Booking;

defined( 'ABSPATH' ) || exit;

class Booking_CPT extends \HydraBooking\PostType\Post_Type {

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

	/**
	 * Initialize custom post type
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		$booking_args = array(
			'name'          => 'Booking',
			'singular_name' => 'Booking',
			'slug'          => 'tfhb_booking',
			'menu_icon'     => 'dashicons-admin-home',
			'supports'      => apply_filters( 'tfhb_booking_supports', array( 'title', 'editor', 'thumbnail', 'comments', 'author' ) ),
			'capability'    => 'post',
			'rewrite_slug'  => 'tfhb_booking',
			'show_ui'       => true,
			'show_in_menu'  => false,
		);

		$tax_args = array();

		parent::__construct( $booking_args, $tax_args );
	}
}
