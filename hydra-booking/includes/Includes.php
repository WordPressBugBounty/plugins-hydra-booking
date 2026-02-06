<?php  
// exit
if ( ! defined( 'ABSPATH' ) ) { exit; }


    /**
     *  Include Files
     *  Require all the files in the includes folder
     */

     function tfhb_utm_generator( $url, $utm_params = array() ) {
        $host_url = parse_url( get_site_url(), PHP_URL_HOST );
        $utm_params = array_merge( array(
            'utm_source'   => 'tfhb_' . $host_url,
            'utm_medium'   => 'plugin',
            'utm_campaign' => 'tfhb_plugin_installation',
        ), $utm_params );
    
        $query_string = http_build_query( $utm_params );
        return esc_url( $url . ( strpos( $url, '?' ) === false ? '?' : '&' ) . $query_string );
    }
    
    

    // Helper Functions file
    if ( file_exists( TFHB_PATH . '/includes/helper/helper-functions.php' ) ) {

        require_once TFHB_PATH . '/includes/helper/helper-functions.php';
    }

    /**
     *  Class Include
     *  Require Hooks files
     */

    // Activation Hooks
    new HydraBooking\Hooks\ActivationHooks();

    // Deactivation Hooks
    new HydraBooking\Hooks\DeactivationHooks();


    // Action Hooks
    new HydraBooking\Hooks\ActionHooks();

    // Mail Hooks
    new HydraBooking\Hooks\MailHooks();

    // Filter Hooks
    new HydraBooking\Hooks\FilterHooks();

    // Booking Location
    new HydraBooking\Hooks\BookingLocation();


    /**
     *  Class Include
     *  Load Integrations Class
     */

    // Integrations
    new HydraBooking\Services\Integrations\MailChimp\MailChimp();
    new HydraBooking\Services\Integrations\Telegram\Telegram();

    
?>