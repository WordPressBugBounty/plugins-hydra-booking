<?php

namespace HydraBooking\PostType\Meeting;

defined( 'ABSPATH' ) || exit;

class Meeting_CPT extends \HydraBooking\PostType\Post_Type {

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
		$meeting_args = array(
			'name'          => 'Meeting',
			'singular_name' => 'Meeting',
			'slug'          => 'tfhb_meeting',
			'menu_icon'     => 'dashicons-admin-home',
			'supports'      => apply_filters( 'tfhb_meeting_supports', array( 'title', 'editor', 'thumbnail', 'comments', 'author' ) ),
			'capability'    => 'post',
			'rewrite_slug'  => 'tfhb-meeting',
			'show_ui'       => true,
			'show_in_menu'  => false,
		);

		$tax_args = array(
			array(
				'name'          => 'Categories',
				'singular_name' => 'Category',
				'hierarchical'  => true,
				'taxonomy'      => 'meeting_category',
				// 'rewrite_slug'  => apply_filters( 'meeting_category_slug', 'tfhb-meeting' ),
				'rewrite_slug'  => 'tfhb-meeting-category',
				'show_ui'       => true,
				'public'       => true,
				'show_in_menu'  => true,
			),
		);

		parent::__construct( $meeting_args, $tax_args );
	}
}
