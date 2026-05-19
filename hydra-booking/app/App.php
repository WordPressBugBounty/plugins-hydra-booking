<?php
namespace HydraBooking\App;
// exit
if ( ! defined( 'ABSPATH' ) ) { exit; }

// Use Classes
use HydraBooking\App\Shortcode\HydraBookingShortcode;
use HydraBooking\App\Shortcode\ShortcodeBuilder;
use HydraBooking\App\Content\Archive;
use HydraBooking\FdDashboard\FrontendDashboard;
use HydraBooking\App\Enqueue;
use HydraBooking\App\BookingLocation;
use HydraBooking\Services\Integrations\Woocommerce\WooBooking;
use HydraBooking\Services\Integrations\BookingBookmarks\BookingBookmarks;
use HydraBooking\DB\Booking;
use HydraBooking\DB\Attendees;

class App {
	public function __construct() {

		$this->init();
	}

	public function init() {

		// Load Enqueue Class
		new Enqueue();

		// Load Shortcode Class
		new HydraBookingShortcode();

		// Load meeting shortcode Class
		new ShortcodeBuilder();

 

		new FrontendDashboard();
		// use this class
		new Archive();


		add_filter( 'query_vars', array( $this, 'tfhb_single_query_vars' ) );

		add_filter( 'template_include', array( $this, 'tfhb_single_page_template' ) );

	 

		add_rewrite_rule(
			'^meeting/([0-9]+)/?$',
			'index.php?hydra-booking=meeting&meeting-id=$matches[1]&type=$matches[2]',
			'top'
		);
		// Create Rewrite Rule for Reschedule hydra-booking.local/?hydra-booking=booking&hash=2121&type=reschedule
		add_rewrite_rule(
			'^booking/([0-9]+)/?$',
			'index.php?hydra-booking=booking&hash=$matches[1]&meeting-id=$matches[2]&type=$matches[3]',
			'top'
		);

		// Rewrite rool for add to calendar 
		add_rewrite_rule(
			'^booking/([0-9]+)/?$',
			'index.php?hydra-booking=add-to-calendar&hash=$matches[1]&type=download_ics',
			'top'
		);

		add_action( 'pre_get_posts', array( $this, 'tfhb_remove_posttype_request' ) );
		add_filter( 'single_template', array( $this, 'tfhb_single_meeting_template' ) );
		
		add_filter( 'post_type_link',  array( $this,'tfhb_meeting_permalink'), 10, 2 );

		// SEO: exclude meeting URLs when URL generation is disabled.
		add_filter( 'wp_sitemaps_post_types',                      array( $this, 'tfhb_exclude_from_wp_sitemap' ) );
		add_filter( 'wpseo_sitemap_exclude_post_type',             array( $this, 'tfhb_yoast_exclude_sitemap' ), 10, 2 );
		add_filter( 'rank_math/sitemap/exclude_post_type',         array( $this, 'tfhb_rankmath_exclude_sitemap' ), 10, 2 );
		add_action( 'wp',                                          array( $this, 'tfhb_send_noindex_header' ) );
	
	 }

	/**
	 * Remove tfhb_meeting from WordPress core XML sitemap when disabled.
	 */
	public function tfhb_exclude_from_wp_sitemap( $post_types ) {
		if ( ! $this->tfhb_is_url_generation_enabled() ) {
			unset( $post_types['tfhb_meeting'] );
		}
		return $post_types;
	}

	/**
	 * Exclude from Yoast SEO sitemap when disabled.
	 */
	public function tfhb_yoast_exclude_sitemap( $excluded, $post_type ) {
		if ( 'tfhb_meeting' === $post_type && ! $this->tfhb_is_url_generation_enabled() ) {
			return true;
		}
		return $excluded;
	}

	/**
	 * Exclude from RankMath sitemap when disabled.
	 */
	public function tfhb_rankmath_exclude_sitemap( $excluded, $post_type ) {
		if ( 'tfhb_meeting' === $post_type && ! $this->tfhb_is_url_generation_enabled() ) {
			return true;
		}
		return $excluded;
	}

	/**
	 * Send X-Robots-Tag: noindex header for meeting post type when disabled
	 * and the current user is not an admin.
	 */
	public function tfhb_send_noindex_header() {
		if ( $this->tfhb_is_url_generation_enabled() ) {
			return;
		}
		if ( ! is_singular( 'tfhb_meeting' ) && ! is_post_type_archive( 'tfhb_meeting' ) ) {
			return;
		}
		if ( current_user_can( 'manage_options' ) ) {
			return;
		}
		header( 'X-Robots-Tag: noindex, nofollow', true );
	}

	public function tfhb_meeting_permalink( $permalink, $post ) {
		$permalink_structure = get_option( 'permalink_structure' );
		if ( !empty($permalink_structure) && $post->post_type === 'tfhb_meeting' && '/%postname%/' == $permalink_structure) {
			return home_url( '/' . $post->post_name . '/' );
		}
		return $permalink;
	}
	public function tfhb_single_meeting_template( $single_template ) {
		global $post;

		/**
		 * Single Meeting
		 *
		 * single-meeting.php
		 */
		if ( 'tfhb_meeting' === $post->post_type ) {
			if ( ! $this->tfhb_is_url_generation_enabled() ) {
				// Allow admins to load the template only for a genuine preview request.
				if ( is_preview() && current_user_can( 'manage_options' ) ) {
					return TFHB_PATH . '/app/Content/Template/single-meeting.php';
				}
				// All other requests (including admins browsing the public URL) → 404.
				global $wp_query;
				$wp_query->set_404();
				status_header( 404 );
				return get_404_template();
			}
			return TFHB_PATH . '/app/Content/Template/single-meeting.php';
		}

		return $single_template;
	}

	/**
	 * Check whether meeting URL generation is enabled.
	 *
	 * Returns true by default in the free version.
	 * Pro plugin hooks into 'tfhb_is_url_generation_enabled' to override.
	 *
	 * @return bool
	 */
	public function tfhb_is_url_generation_enabled() {
		return (bool) apply_filters( 'tfhb_is_url_generation_enabled', true );
	}

	public function tfhb_remove_posttype_request( $query ) {
		// Only noop the main query.
		if ( ! $query->is_main_query() ) {
			return;
		}

		// Preview requests include an extra `preview` query var, making count = 3.
		// Handle them before the strict count guard so admin preview always resolves
		// the meeting post even when public URL generation is disabled.
		if ( ! empty( $query->query['name'] ) && ! empty( $query->query['preview'] ) ) {
			$query->set( 'post_type', array( 'post', 'page', 'tfhb_meeting' ) );
			return;
		}

		// Only noop our very specific rewrite rule match.
		if ( 2 != count( $query->query ) || ! isset( $query->query['page'] ) ) {
			return;
		}

		// 'name' will be set if post permalinks are just post_name, otherwise the page rule will match.
		if ( ! empty( $query->query['name'] ) ) {
			$post_types = array(
				'post', // important to  not break your standard posts
				'page', // important to  not break your standard pages
			);

			// Only include tfhb_meeting in slug-based queries when public URL generation is enabled.
			if ( $this->tfhb_is_url_generation_enabled() ) {
				$post_types[] = 'tfhb_meeting';
			}

			$query->set( 'post_type', $post_types );
		}
	}

	public function tfhb_single_query_vars( $query_vars ) {
		$query_vars[] = 'hydra-booking'; 
		$query_vars[] = 'hydra-add-to-calendar'; 
		$query_vars[] = 'username';
		$query_vars[] = 'meeting';
		$query_vars[] = 'meeting-id';
		$query_vars[] = 'meetingId';
		$query_vars[] = 'hash';
		$query_vars[] = 'type';
		return $query_vars;
	}

	public function tfhb_single_page_template( $template ) {
		
		if ( get_query_var( 'hydra-booking' ) === 'meeting' && get_query_var( 'meetingId' )) {
			if ( ! $this->tfhb_is_url_generation_enabled() ) {
				return $template;
			}

			$custom_template = load_template( TFHB_PATH . '/app/Content/Template/embed.php', true );
			return $custom_template;
		}
		if ( get_query_var( 'hydra-booking' ) === 'meeting' && get_query_var( 'meeting' ) ) {
			if ( ! $this->tfhb_is_url_generation_enabled() ) {
				return $template;
			}
			$custom_template = load_template( TFHB_PATH . '/app/Content/Template/single-meeting.php', false );
			return $custom_template;
		} 
		if ( get_query_var( 'hydra-add-to-calendar' )) {
			
			
			$Bookmark = new BookingBookmarks();
			$getBookmark = $Bookmark->sendBookmarkFormEmail(get_query_var( 'hydra-add-to-calendar' ));

		} 

 
		// Reschedule Page
		if ( get_query_var( 'hydra-booking' ) === 'booking' && get_query_var( 'hash' ) && get_query_var( 'type' ) === 'reschedule' ) {
			$custom_template = load_template( TFHB_PATH . '/app/Content/Template/reschedule.php', false );
			return $custom_template;

		} 
		// Cenceled And Confirmation Page and download ics
		if (( 
			get_query_var( 'hydra-booking' ) === 'booking' && get_query_var( 'hash' ) && get_query_var( 'type' ) === 'cancel' ) // Cenceled  Page
			|| ( get_query_var( 'hydra-booking' ) === 'booking' && get_query_var( 'hash' ) && get_query_var( 'type' ) === 'confirmation' ) // Confirmation  Page
			|| ( get_query_var( 'hydra-booking' ) === 'booking' && get_query_var( 'hash' ) && get_query_var( 'type' ) === 'download_ics' ) // Download Ics
		) {
			 
			if ( ! wp_script_is( 'tfhb-app-script', 'enqueued' ) ) {
				wp_enqueue_script( 'tfhb-app-script' );
			}
		 
			
			$Attendee = new Attendees();
			$attendeeBooking =  $Attendee->getAttendeeWithBooking( 
				array(
					array('hash', '=', get_query_var( 'hash' )),
				),
				1,
				'DESC'
			);  
			if ( ! $attendeeBooking ) {
				return $template;
			} 
			if('confirmation' == get_query_var( 'type' )){
				$custom_template = load_template(
					TFHB_PATH . '/app/Content/Template/meeting-confirmation.php',
					false,
					array(
						'attendeeBooking'         => $attendeeBooking, 
						'confirmation_page'         => true, 
					)
				);
			}
			if('cancel' == get_query_var( 'type' )){
				$custom_template = load_template(
					TFHB_PATH . '/app/Content/Template/meeting-cencel.php',
					false,
					array(
						'attendeeBooking'         => $attendeeBooking, 
					)
				);
			}
			if('download_ics' == get_query_var( 'type' )){
				$bookmark = new BookingBookmarks();
				$bookmark->generateBookingICS($attendeeBooking);
				return $template;
			}
			
			return $custom_template;

		}
		return $template;
	}

	 
}
