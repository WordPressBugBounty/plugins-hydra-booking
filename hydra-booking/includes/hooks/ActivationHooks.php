<?php
namespace HydraBooking\Hooks;

class ActivationHooks {

	public function __construct() {

		register_activation_hook( TFHB_PATH . 'hydra-booking.php', array( $this, 'tfhb_activate' ) );
	}

	public function tfhb_activate() {

		global $wp_rewrite;

		// if permalink strucutrre is  Day and name then change it to postname
		$permalink_structure = get_option( 'permalink_structure' );
		if ( '/%year%/%monthnum%/%day%/%postname%/' === $permalink_structure ) {
			$wp_rewrite->set_permalink_structure( '/%postname%/' );
			$wp_rewrite->flush_rules();
		}else{
			$wp_rewrite->flush_rules(); 
		}

		// Create a New host Role
		$this->tfhb_create_host_role();

		// Add Capabilities to the role
		$this->tfhb_add_capabilities_to_role();

		// Tfhb Options Activation hooks
		$this->tfhb_options_activation_hooks();

		// update option 
		update_option('tfhb_update_status', TFHB_VERSION);
	}

	public function tfhb_create_host_role() {

		if ( get_role( 'tfhb_host' ) ) {
			return;
		}
			add_role(
				'tfhb_host',
				'Hydra Host',
				array(
					'read'                            => true, // true allows this capability 
					'upload_files'                    => true, // true allows this capability 
					// Custom Capabilities
					'tfhb_manage_options'             => true, // true allows this capability.
					'tfhb_manage_dashboard'           => true, // true allows this capability.
					'tfhb_manage_meetings'            => true, // true allows this capability.
					'tfhb_manage_booking'             => true, // true allows this capability.
					'tfhb_manage_settings'            => false, // true allows this capability.
					'tfhb_manage_hosts'               => true, // true allows this capability.
					'tfhb_manage_custom_availability' => true, // true allows this capability.
					'tfhb_manage_integrations'        => true, // true allows this capability.
				)
			);
	}

	// Add Capabilities to the role
	public function tfhb_add_capabilities_to_role() {
		// administrator
		$role = get_role( 'administrator' );
		$role->add_cap( 'tfhb_manage_options' );
		$role->add_cap( 'tfhb_manage_dashboard' );
		$role->add_cap( 'tfhb_manage_meetings' );
		$role->add_cap( 'tfhb_manage_booking' );
		$role->add_cap( 'tfhb_manage_settings' );
		$role->add_cap( 'tfhb_manage_hosts' );
		$role->add_cap( 'tfhb_manage_custom_availability' );
		$role->add_cap( 'tfhb_manage_integrations' );
	}


	// Tfhb Options Activation hooks
	public function tfhb_options_activation_hooks() {

		// setup default options

		// Activation date
		update_option( 'tfhb_hydra_activation_date', time() );
	}
}
