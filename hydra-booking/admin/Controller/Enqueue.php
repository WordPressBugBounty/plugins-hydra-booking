<?php
namespace HydraBooking\Admin\Controller;

// exit
if ( ! defined( 'ABSPATH' ) ) { exit; }

use HydraBooking\Admin\Controller\TransStrings;
use HydraBooking\Admin\Controller\AuthController;
use HydraBooking\Admin\Controller\licenseController;

/**
 * Enqueue Class
 * 
 * @package HydraBooking\Admin\Controller
 * @since 1.0.0
 * 
 * @author Sydur Rahman
 */ 
class Enqueue {

	// constaract
	public function __construct() { 
		
		
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) ); 
		add_action( 'wp_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_filter( 'script_loader_tag', array( $this, 'thb_loadScriptAsModule' ), 10, 3 );
	}
	public function thb_loadScriptAsModule( $tag, $handle, $src ) {
		if ( 'tfhb-admin-core' !== $handle ) {
			return $tag;
		}
		$tag = '<script type="module" src="' . esc_url( $src ) . '"></script>';
		return $tag;
	}
	public function admin_enqueue_scripts() {
		
		wp_enqueue_script( 'tfhb-admin-script', TFHB_URL . 'assets/admin/js/main.js', array( 'jquery' ), time(), true );
		wp_localize_script(
			'tfhb-admin-script',
			'tfhb_admin_notice',
			array(
				'_nonce'           => wp_create_nonce( 'wp_notice' ),
				'ajax_url'             => admin_url( 'admin-ajax.php' ),
			)
		);

		$front_end_dashboard = false;
		// if is admin page
		if ( is_admin() ) {
			if ( ! isset( $_GET['page'] ) || 'hydra-booking' !== $_GET['page'] ) { 
				return;
			}
		}
	
		// if its load in frontend then get page template
		if ( ! is_admin() ) {
			// get current page and page template
			$current_page = get_queried_object(); 
			if($current_page && isset($current_page->ID)){
				$page_template = get_page_template_slug( $current_page->ID );
				if ( 'tfhb-frontend-dashboard.php' !== $page_template ) {
					return;
				}
				$front_end_dashboard = true;
			}else{
				return;
			}
			
		}

		$user      = new AuthController();
		$user_auth = array(
			'id'   => $user->userID(),
			'host_id'  => $user->userHostID(),
			'role' => $user->userRole(),
			'caps' => $user->userAllCaps(),
		);

		// enqueue styles
		wp_enqueue_style( 'tfhb-admin-style', TFHB_URL . 'assets/admin/css/tfhb-admin-style.css', array(), null );
 
		if(defined('TFHB_DEV_MODE') && TFHB_DEV_MODE === true){
			wp_enqueue_script( 'tfhb-admin-core', apply_filters('tfhb_admin_core_script', 'http://localhost:5173/src/main.js'), array(), time(), true ); 

		} else {
			
			//  Build the core script
			wp_enqueue_script('tfhb-admin-core',  apply_filters('tfhb_admin_core_script', TFHB_URL .'build/assets/tfhb-admin-app-script.js'), [], time(), true); 
			wp_enqueue_style('tfhb-admin-style-core',  apply_filters('tfhb_admin_core_style', TFHB_URL .'build/assets/tfhb-admin-app.css'), [], time(), 'all');
	
		}
		
	

		// Localize the script
		 
		$embed_script_link = esc_html('<script src="' .TFHB_URL . 'assets/app/js/widget.js"></script>');
		$trans_string = array_merge(TransStrings::getTransStrings(), TransStrings::calendarTransString());
		$license = LicenseController::getInstance()->check_license();
	
		wp_localize_script(
			'tfhb-admin-core',
			'tfhb_core_apps',
			array(
				// 'url' => TFHB_URL,
				'rest_nonce'           => wp_create_nonce( 'wp_rest' ),
				'tfhb_license_type' =>  $license['license_type'],
                'tfhb_is_valid'  =>  $license['is_valid'],
				'admin_url'            => site_url(),
				'rest_route'           => get_rest_url(),
				'embed_script_link'    => esc_html( $embed_script_link ),
				'ajax_url'             => admin_url( 'admin-ajax.php' ),
				'front_end_dashboard'  => $front_end_dashboard,
				'tfhb_url'             => TFHB_URL,
				'tfhb_hydra_admin_url' => admin_url( 'admin.php?page=hydra-booking#/' ),
				'user'                 => $user_auth, 
				'trans'				   => $trans_string,
			)
		); 

		if($front_end_dashboard == true){
			$settings = !empty(get_option('_tfhb_frontend_dashboard_settings')) ? get_option('_tfhb_frontend_dashboard_settings') : array();
			$primery_default  = isset($settings['general']['primery_default']) ? $settings['general']['primery_default'] : '#2E6B38'; 
			$primery_hover  = isset($settings['general']['primery_hover']) ? $settings['general']['primery_hover'] : '#4C9959'; 
			$secondary_default  = isset($settings['general']['secondary_default']) ? $settings['general']['secondary_default'] : '#273F2B'; 
			$secondary_hover  = isset($settings['general']['secondary_hover']) ? $settings['general']['secondary_hover'] : '#E1F2E4'; 
			$text_title  = isset($settings['general']['text_title']) ? $settings['general']['text_title'] : '#141915'; 
			$text_paragraph  = isset($settings['general']['text_paragraph']) ? $settings['general']['text_paragraph'] : '#273F2B';  
			$surface_primary  = isset($settings['general']['surface_primary']) ? $settings['general']['surface_primary'] : '#F9FBF9';  
			$surface_background  = isset($settings['general']['surface_background']) ? $settings['general']['surface_background'] : '#C0D8C4';  
			$surface_border  = isset($settings['general']['surface_border']) ? $settings['general']['surface_border'] : '#C0D8C4';  
			$surface_border_hover  = isset($settings['general']['surface_border_hover']) ? $settings['general']['surface_border_hover'] : '#211319';  
			$surface_input_field  = isset($settings['general']['surface_input_field']) ? $settings['general']['surface_input_field'] : '#56765B';  
			$custom_css = "
				:root {
					--tfhb-admin-primary-default: $primery_default; 
					--tfhb-admin-primary-hover: $primery_hover; 
					--tfhb-admin-secondary-default: $secondary_default; 
					--tfhb-admin-secondary-hover: $secondary_hover; 
					--tfhb-admin-text-title: $text_title; 
					--tfhb-admin-text-paragraph: $text_paragraph; 
					--tfhb-admin-surface-primary: $surface_primary; 
					--tfhb-admin-surface-background: $surface_background; 
					--tfhb-admin-surface_border: $surface_border; 
					--tfhb-admin-surface-border-hover: $surface_border_hover; 
					--tfhb-admin-surface-input-field: $surface_input_field; 
				} 
			";
			wp_add_inline_style('tfhb-admin-style', $custom_css);
		}

		if ( function_exists( 'wp_enqueue_media' ) ) {
			wp_enqueue_media();
		}
	}
}
