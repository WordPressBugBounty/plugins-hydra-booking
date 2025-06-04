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
      }


       
            
 
}
