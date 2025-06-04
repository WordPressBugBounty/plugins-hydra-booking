<?php  
// exit
if ( ! defined( 'ABSPATH' ) ) { exit; }


    /**
     *  Include Files
     *  Require all the files in the includes folder
     */

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