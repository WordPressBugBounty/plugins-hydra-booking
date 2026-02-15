<?php
namespace HydraBooking\Hooks;

class ActivationHooks {

	public function __construct() {

		register_activation_hook( TFHB_PATH . 'hydra-booking.php', array( $this, 'tfhb_activate' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( TFHB_PATH . 'hydra-booking.php' ), array( $this, 'tfhb_plugin_action_links' ) );
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

	/**
	 * Add plugin action links
	 *
	 * @param array $links Existing plugin action links.
	 * @return array Modified plugin action links.
	 */
	public function tfhb_plugin_action_links( $links ) {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			admin_url( 'admin.php?page=hydra-booking#/settings/general' ),
			__( 'Settings', 'hydra-booking' )
		);

		$docs_link = sprintf(
			'<a href="%s" target="_blank">%s</a>',
			'https://themefic.com/docs/hydrabooking/',
			__( 'Docs', 'hydra-booking' )
		);

		// Only show "GO PRO" link if pro version is not active
		if ( ! is_plugin_active( 'hydra-booking-pro/hydra-booking-pro.php' ) ) {
			$pro_link = sprintf(
				'<a href="%s" style="color: #b32d2e; font-weight: bold;" target="_blank">%s</a>',
				'https://hydrabooking.com/pricing/',
				__( 'GET PRO', 'hydra-booking' )
			);
			array_unshift( $links, $settings_link, $pro_link, $docs_link );
		} else {
			array_unshift( $links, $settings_link, $docs_link );
		}

		return $links;
	}
}
