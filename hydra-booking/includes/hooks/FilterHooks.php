<?php
namespace HydraBooking\Hooks;

use HydraBooking\Admin\Controller\AuthController; 
use HydraBooking\Services\Integrations\Woocommerce\WooBooking;

class FilterHooks {

	public function __construct() {
                $_tfhb_integration_settings = get_option( '_tfhb_integration_settings' ); 
                // Restrict unverified user
                add_filter( 'authenticate', array( new AuthController(), 'tfhb_restrict_unverified_user' ), 10, 3 );
                
                // WooCommerce Order Hooks. 
                $woo_payment = isset( $_tfhb_integration_settings['woo_payment'] ) ? $_tfhb_integration_settings['woo_payment'] : array();
                
                if(isset($woo_payment['status']) && $woo_payment['status'] == true){  
                        // display booking_id  into checkout page
                        add_filter( 'woocommerce_get_item_data', array( new WooBooking(), 'tfhb_woocommerce_get_item_data' ), 10, 2 );
                }
		
                // Redirect Host after login if woocommerce is active
                add_filter('woocommerce_prevent_admin_access', array( new AuthController(),  'tfhb_woocommerce_prevent_admin_access' ), 10, 3);

                // Restrict the Media Library so a user who can't edit others' content
                // (e.g. a Hydra Host) only ever sees their own uploads, not every
                // host/user's files. Admins/editors (edit_others_posts) are unaffected.
                add_filter( 'ajax_query_attachments_args', array( $this, 'tfhb_restrict_media_library_query' ) );
                add_filter( 'rest_attachment_query', array( $this, 'tfhb_restrict_media_library_rest_query' ), 10, 2 );
                add_action( 'pre_get_posts', array( $this, 'tfhb_restrict_media_library_admin_query' ) );
      }

        // Restrict the wp.media() modal (used by the Hydra Booking / Frontend
        // Dashboard image upload fields) to the current user's own attachments.
        public function tfhb_restrict_media_library_query( $query ) {
                if ( ! current_user_can( 'edit_others_posts' ) ) {
                        $query['author'] = get_current_user_id();
                }
                return $query;
        }

        // Restrict the wp/v2/media REST endpoint the same way, in case the
        // Frontend Dashboard lists or queries media directly via REST.
        public function tfhb_restrict_media_library_rest_query( $args, $request ) {
                if ( ! current_user_can( 'edit_others_posts' ) ) {
                        $args['author'] = get_current_user_id();
                }
                return $args;
        }

        // Restrict the wp-admin Media Library screen (Media > Library) the same
        // way, since Hydra Hosts have the 'upload_files' capability and can
        // otherwise browse it directly.
        public function tfhb_restrict_media_library_admin_query( $query ) {
                if ( ! is_admin() || ! $query->is_main_query() ) {
                        return;
                }
                if ( 'attachment' !== $query->get( 'post_type' ) ) {
                        return;
                }
                if ( ! current_user_can( 'edit_others_posts' ) ) {
                        $query->set( 'author', get_current_user_id() );
                }
        }


       
            
 
}
