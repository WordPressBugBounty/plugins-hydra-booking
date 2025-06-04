<?php
namespace HydraBooking\App; 
 
// exit
if ( ! defined( 'ABSPATH' ) ) { exit; }

use HydraBooking\Admin\Controller\TransStrings;

class Enqueue {
	public function __construct() {

		add_action( 'wp_enqueue_scripts', array( $this, 'tfhb_enqueue_scripts' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'tfhb_enqueue_scripts' ) );
	} 

    public function tfhb_enqueue_scripts() {
		wp_enqueue_style( 'tfhb-style', TFHB_URL . 'assets/app/css/style.css', '', TFHB_VERSION );
		wp_register_style( 'tfhb-select2-style', TFHB_URL . 'assets/lib/select2/select2.min.css', array(), TFHB_VERSION );

		// Global General Settings
		$general_settings = get_option( '_tfhb_general_settings', true ) ? get_option( '_tfhb_general_settings', true ) : array();

		$currency = ! empty( $general_settings['currency'] ) ? $general_settings['currency'] : 'USD';

		$_tfhb_appearance_settings = get_option( '_tfhb_appearance_settings' );

		// Integration Settings
		$_tfhb_integration_settings = get_option( '_tfhb_integration_settings' );
		$tfhb_paypal = isset( $_tfhb_integration_settings['paypal'] ) ? $_tfhb_integration_settings['paypal'] : array();
		 
		
		$tfhb_primary_color   = ! empty( $_tfhb_appearance_settings['primary_color'] ) ? $_tfhb_appearance_settings['primary_color'] : '#2E6B38';
		$tfhb_primary_hover   = ! empty( $_tfhb_appearance_settings['primary_hover'] ) ? $_tfhb_appearance_settings['primary_hover'] : '#4C9959';
		$tfhb_secondary_color = ! empty( $_tfhb_appearance_settings['secondary_color'] ) ? $_tfhb_appearance_settings['secondary_color'] : '#273F2B';
		$tfhb_secondary_hover = ! empty( $_tfhb_appearance_settings['secondary_hover'] ) ? $_tfhb_appearance_settings['secondary_hover'] : '#E1F2E4';
		$tfhb_text_title_color = ! empty( $_tfhb_appearance_settings['text_title_color'] ) ? $_tfhb_appearance_settings['text_title_color'] : '#141915';
		$tfhb_paragraph_color = ! empty( $_tfhb_appearance_settings['paragraph_color'] ) ? $_tfhb_appearance_settings['paragraph_color'] : '#273F2B';
		$tfhb_surface_primary = ! empty( $_tfhb_appearance_settings['surface_primary'] ) ? $_tfhb_appearance_settings['surface_primary'] : '#C0D8C4';
		$tfhb_surface_background = ! empty( $_tfhb_appearance_settings['surface_background'] ) ? $_tfhb_appearance_settings['surface_background'] : '#EEF6F0';
		$tfhb_theme_css       = "
        :root {
            --tfhb-primary-color: $tfhb_primary_color;
            --tfhb-primary-hover-color: $tfhb_primary_hover;
            --tfhb-secondary-color: $tfhb_secondary_color;
            --tfhb-secondary-hover-color: $tfhb_secondary_hover;
            --tfhb-paragraph-color: $tfhb_paragraph_color;
            --tfhb-text-title-color: $tfhb_text_title_color;
            --tfhb-surface-primary-color: $tfhb_surface_primary;
            --tfhb-surface-background-color: $tfhb_surface_background;
          }
        ";
		wp_add_inline_style( 'tfhb-style', $tfhb_theme_css ); 
		// register script
		wp_register_script( 'tfhb-stripe-script', '//checkout.stripe.com/checkout.js', array( 'jquery' ), '1.0.0' );
		if(isset($tfhb_paypal['status']) && $tfhb_paypal['status'] == 1){
			if($tfhb_paypal['environment'] == 'live'){
				$sdk_url = 'https://www.paypal.com/sdk/js?client-id='.esc_attr($tfhb_paypal['client_id']).'&currency='.esc_attr($currency).'';
			}else{ 
				$sdk_url = 'https://www.sandbox.paypal.com/sdk/js?client-id='.esc_attr($tfhb_paypal['client_id']).'&currency='.esc_attr($currency).'';
			}
			// if
			wp_register_script( 'tfhb-paypal-sdk', esc_url($sdk_url), array(), null, true );
		}
		wp_register_script( 'tfhb-paypal-script', '//paypalobjects.com/api/checkout.js', array( 'jquery' ), '1.0.0', true );
		wp_register_script( 'tfhb-select2-script', TFHB_URL . 'assets/lib/select2/select2.min.js', array( 'jquery', 'tfhb-app-script' ), TFHB_VERSION, true );
		wp_enqueue_script( 'tfhb-app-script', TFHB_URL . 'assets/app/js/main.js', array( 'jquery', 'wp-i18n' ), TFHB_VERSION, true );
		// pass data to script 

		// registration
		wp_register_script( 'tfhb-app-registration', TFHB_URL . 'assets/app/js/registration.js', array( 'jquery' ), TFHB_VERSION, true );
		
		// Login
		wp_register_script( 'tfhb-app-login', TFHB_URL . 'assets/app/js/login.js', array( 'jquery' ), TFHB_VERSION, true );
		
		
		wp_localize_script(
			'tfhb-app-script',
			'tfhb_app_booking',
			array(
				'ajax_url'         => admin_url( 'admin-ajax.php' ),
				'site_url'         => site_url(),
				'nonce'            => wp_create_nonce( 'tfhb_nonce' ),
				'general_settings' => $general_settings,
				'i18n' => TransStrings::calendarTransString(),
			)
		);
	}


}