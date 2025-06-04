<?php
namespace HydraBooking\PostType;

	// exit
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

class Post_Type {

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

	protected $post_args;
	protected $tax_args;

	public function __construct( $post_args = array(), $tax_args = array() ) {

		$this->post_args = $post_args;
		$this->tax_args  = $tax_args;
		$this->tfhb_post_type_register();
		$this->tfhb_post_type_taxonomy_register();
	}

	public function tfhb_post_type_register() {
		$post_args = $this->post_args;
		$labels    = array(
			'name'                  => $post_args['name'],
			'singular_name'         => $post_args['singular_name'],
			'add_new'               => esc_html__( 'Add New', 'hydra-booking' ),
			/* translators: %s: post type singular name */
			'add_new_item'             => sprintf( esc_html__( 'Add New %s', 'hydra-booking' ), $post_args['singular_name'] ),
			/* translators: %s: post type singular name */
			'edit_item'             => sprintf( esc_html__( 'Edit %s', 'hydra-booking' ), $post_args['singular_name'] ),
			/* translators: %s: post type singular name */
			'new_item'              => sprintf( esc_html__( 'New %s', 'hydra-booking' ), $post_args['singular_name'] ),
			/* translators: %s: post type name */
			'all_items'             => sprintf( esc_html__( 'All %s', 'hydra-booking' ), $post_args['name'] ),
			/* translators: %s: post type singular name */
			'view_item'             => sprintf( esc_html__( 'View %s', 'hydra-booking' ), $post_args['singular_name'] ),
			/* translators: %s: post type name */
			'view_items'            => sprintf( esc_html__( 'View %s', 'hydra-booking' ), $post_args['name'] ),
			/* translators: %s: post type name */
			'search_items'          => sprintf( esc_html__( 'Search %s', 'hydra-booking' ), $post_args['name'] ),
			/* translators: %s: post type singular name */
			'not_found'             => sprintf( esc_html__( 'No %s found', 'hydra-booking' ), $post_args['singular_name'] ),
			/* translators: %s: post type singular name */
			'not_found_in_trash'    => sprintf( esc_html__( 'No %s found in Trash', 'hydra-booking' ), $post_args['singular_name'] ),
			'parent_item_colon'     => '',
			'menu_name'             => $post_args['name'],
			/* translators: %s: post type singular name */
			'featured_image'        => sprintf( esc_html__( '%s Image', 'hydra-booking' ), $post_args['singular_name'] ),
			/* translators: %s: post type singular name */
			'set_featured_image'    => sprintf( esc_html__( 'Set %s Image', 'hydra-booking' ), $post_args['singular_name'] ),
			/* translators: %s: post type singular name */
			'remove_featured_image' => sprintf( esc_html__( 'Remove %s Image', 'hydra-booking' ), $post_args['singular_name'] ),
			/* translators: %s: post type singular name */
			'use_featured_image'    => sprintf( esc_html__( 'Use as %s Image', 'hydra-booking' ), $post_args['singular_name'] ),
			/* translators: %s: post type singular name */
			'attributes'            => sprintf( esc_html__( '%s Attributes', 'hydra-booking' ), $post_args['singular_name'] ),
			/* translators: %s: post type name */
			'filter_items_list'     => sprintf( esc_html__( 'Filter %s list', 'hydra-booking' ), $post_args['name'] ),
			/* translators: %s: post type singular name */
			'items_list_navigation' => sprintf( esc_html__( '%s list navigation', 'hydra-booking' ), $post_args['singular_name'] ),
			/* translators: %s: post type name */
			'items_list'            => sprintf( esc_html__( '%s list', 'hydra-booking' ), $post_args['name'] )
		);

		$labels = apply_filters( $post_args['slug'] . '_labels', $labels );

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'show_in_rest'       => true,
			'publicly_queryable' => true,
			'show_ui'            => $post_args['show_ui'],
			'show_in_menu'       => $post_args['show_in_menu'],
			'query_var'          => true,
			'menu_icon'          => $post_args['menu_icon'],
			'rewrite'            => array(
				'slug'       => $post_args['rewrite_slug'],
				'with_front' => false,
			),
			'capability_type'    => $post_args['capability'],
			'has_archive'        => true,
			'hierarchical'       => false,
			'supports'           => $post_args['supports'],
		);

		$args = apply_filters( $post_args['slug'] . '_args', $args );
		register_post_type( $post_args['slug'], $args );
	}

	public function tfhb_post_type_taxonomy_register() {

		foreach ( $this->tax_args as $tax_args ) {

			$tax_labels = array(
				'name'                       => $tax_args['name'],
				'singular_name'              => $tax_args['singular_name'],
				'menu_name'                  => $tax_args['name'],
				/* translators: %s: taxonomy name */
				'all_items'                  => sprintf( esc_html__( 'All %s', 'hydra-booking' ), $tax_args['name'] ),
				/* translators: %s: taxonomy singular name */
				'edit_item'                  => sprintf( esc_html__( 'Edit %s', 'hydra-booking' ), $tax_args['singular_name'] ),
				/* translators: %s: taxonomy singular name */
				'view_item'                  => sprintf( esc_html__( 'View %s', 'hydra-booking' ), $tax_args['singular_name'] ),
				/* translators: %s: taxonomy singular name */
				'update_item'                => sprintf( esc_html__( 'Update %s name', 'hydra-booking' ), strtolower( $tax_args['singular_name'] ) ),
				/* translators: %s: taxonomy singular name */
				'add_new_item'               => sprintf( esc_html__( 'Add New %s', 'hydra-booking' ), strtolower( $tax_args['singular_name'] ) ),
				/* translators: %s: taxonomy singular name */
				'new_item_name'              => sprintf( esc_html__( 'New %s name', 'hydra-booking' ), strtolower( $tax_args['singular_name'] ) ),
				/* translators: %s: taxonomy singular name */
				'parent_item'                => sprintf( esc_html__( 'Parent %s', 'hydra-booking' ), $tax_args['singular_name'] ),
				/* translators: %s: taxonomy singular name */
				'parent_item_colon'          => sprintf( esc_html__( 'Parent : %s', 'hydra-booking' ), $tax_args['singular_name'] ),
				/* translators: %s: taxonomy singular name */
				'search_items'               => sprintf( esc_html__( 'Search %s', 'hydra-booking' ), $tax_args['singular_name'] ),
				/* translators: %s: taxonomy singular name */
				'popular_items'              => sprintf( esc_html__( 'Popular %s', 'hydra-booking' ), $tax_args['singular_name'] ),
				/* translators: %s: taxonomy singular name */
				'separate_items_with_commas' => sprintf( esc_html__( 'Separate %s with commas', 'hydra-booking' ), strtolower( $tax_args['singular_name'] ) ),
				/* translators: %s: taxonomy singular name */
				'add_or_remove_items'        => sprintf( esc_html__( 'Add or remove %s', 'hydra-booking' ), strtolower( $tax_args['singular_name'] ) ),
				/* translators: %s: taxonomy singular name */
				'choose_from_most_used'      => sprintf( esc_html__( 'Choose from the most used %s', 'hydra-booking' ), strtolower( $tax_args['singular_name'] ) ),
				/* translators: %s: taxonomy singular name */
				'not_found'                  => sprintf( esc_html__( 'No %s found.', 'hydra-booking' ), strtolower( $tax_args['singular_name'] ) ),
				/* translators: %s: taxonomy singular name */
				'no_terms'                   => sprintf( esc_html__( 'No %s', 'hydra-booking' ), strtolower( $tax_args['singular_name'] ) ),
				/* translators: %s: taxonomy singular name */
				'items_list_navigation'      => sprintf( esc_html__( '%s list navigation', 'hydra-booking' ), $tax_args['singular_name'] ),
				/* translators: %s: taxonomy name */
				'items_list'                 => sprintf( esc_html__( '%s list', 'hydra-booking' ), $tax_args['name'] ),
				/* translators: %s: taxonomy singular name */
				'back_to_items'              => sprintf( esc_html__( 'Back to %s', 'hydra-booking' ), strtolower( $tax_args['singular_name'] ) )
			);
			$tax_labels = apply_filters( 'tfhb_' . $tax_args['taxonomy'] . '_labels', $tax_labels );

			$tfhb_tax_args = array(
				'labels'                => $tax_labels,
				'public'                => true,
				'publicly_queryable'    => true,
				'hierarchical'          => true,
				'show_ui'               => $tax_args['show_ui'],
				'show_in_menu'          => $tax_args['show_in_menu'],
				'show_in_nav_menus'     => true,
				'query_var'             => true,
				'rewrite'               => array(
					'slug'       => $tax_args['rewrite_slug'],
					'with_front' => false,
					'hierarchical' => true,
				),
				'show_admin_column'     => true,
				'show_in_rest'          => true,
				'rest_base'             => $tax_args['taxonomy'],
				'rest_controller_class' => 'WP_REST_Terms_Controller',
				'show_in_quick_edit'    => true, 
			);
			$tfhb_tax_args = apply_filters( 'tfhb_' . $tax_args['taxonomy'] . '_args', $tfhb_tax_args );

			register_taxonomy( $tax_args['taxonomy'], $this->post_args['slug'], $tfhb_tax_args );
		}
	}
}
