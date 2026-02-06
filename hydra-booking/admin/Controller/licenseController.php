<?php
namespace HydraBooking\Admin\Controller;
// exit
if ( ! defined( 'ABSPATH' ) ) { exit; }


// Use HydraBooking 
use HydraBooking\License\HydraBooking; 
use HydraBooking\License\HydraBookingBase; 
 

class licenseController {
    private static $instance = null; // Holds the single instance
    private static $cached_result = null;

	// constaract
	public function __construct() {
        // if HydraBooking is not exicuted 
        

	}

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

	public function create_endpoint() {
		register_rest_route(
			'hydra-booking/v1',
			'/settings/license',
			array(
				'methods'  => 'GET',
				'callback' => array( $this, 'GetLicenseData' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
			)
		); 
        register_rest_route(
			'hydra-booking/v1',
			'/settings/license/update',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'UpdateLicenseData' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
			)
		); 

        register_rest_route(
			'hydra-booking/v1',
			'/settings/license/deactivate',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'DeactiveLicense' ),
				'permission_callback' =>  array(new RouteController() , 'tfhb_manage_options_permission'),
			)
		); 
	}


    public function GetLicenseData(){

        // Checked current user can manage option
		if (  ! current_user_can( 'manage_options' ) ) {
			// woocommerce payment
			wp_send_json_error( array( 
                'status' => false,
                'message' => ' You do not have permission to access this data.'
            ) );
		}
		

        $main_lic_key="HydraBooking_lic_Key";
	    $lic_key_name =HydraBookingBase::get_lic_key_param($main_lic_key);
        $license_key=get_option($lic_key_name,"");
        $license_email=get_option("HydraBooking_lic_email","");

        // $HydraBooking = new HydraBooking();
        $response = get_option('HydraBooking_lic_response_obj', false); 
       
         
        if(false == $response){
            wp_send_json_error( array( 
                'status' => false,
                'message' => 'Invalid License Key'
            ) );
        }

        wp_send_json_success( array( 
            'status' => true,
            'message' => 'License Data',
            'data' => $response,
            'license_key' => $this->decryptKey($license_key, $license_email),
            'license_email' => $license_email,
        ) );

    }

    /**
     * Update License Data
     *
     * @param \WP_REST_Request $request
     * @return void
     */
    public function UpdateLicenseData(){
        $request = json_decode( file_get_contents( 'php://input' ), true );
        $license_key = !empty($request['license_key'])?sanitize_text_field(wp_unslash($request['license_key'])):"";
        $license_email = !empty($request['license_email'])?sanitize_email(wp_unslash($request['license_email'])):"";
        $license_key = $this->encryptKey($license_key, $license_email);
        $main_lic_key="HydraBooking_lic_Key";

      
      
	    $lic_key_name = HydraBookingBase::get_lic_key_param($main_lic_key);  
        update_option($lic_key_name,$license_key) || add_option($lic_key_name,$license_key);
        update_option($main_lic_key,$license_key) || add_option($main_lic_key,$license_key);
        update_option("HydraBooking_lic_email",$license_email) || add_option("HydraBooking_lic_email",$license_email);
        update_option('_site_transient_update_plugins',''); 
        $HydraBooking = new HydraBooking();
     
        $HydraBooking->response_obj;  
        $response = get_option('HydraBooking_lic_response_obj', false); 
       
        if(false == $response){ 
            delete_option($lic_key_name);
            delete_option("HydraBooking_lic_email");

            
            wp_send_json_error( array( 
                'status' => false,
                'message' => 'Invalid License Key'
            ) );
        }
	 
        wp_send_json_success( array( 
            'status' => true,
            'message' => 'License Updated Successfully',
            'data' => $response,
            'license_key' => $license_key,
            'license_email' => $license_email,
        ) );
   
        
    }

    /**
     * Deactive License
     *
     * @param \WP_REST_Request $request
     * @return void
     */

    public function DeactiveLicense(){
        $message="";
	    $main_lic_key="HydraBooking_lic_Key";
	    $lic_key_name =HydraBookingBase::get_lic_key_param($main_lic_key);
        if(HydraBookingBase::remove_license_key(TFHB_BASE_FILE,$message)){
            update_option($lic_key_name,"") || add_option($lic_key_name,"");
            update_option($main_lic_key,"") || add_option($main_lic_key,"");
            update_option('_site_transient_update_plugins','');
        }

        wp_send_json_success( array( 
            'status' => true,
            'message' => 'License Deactivated Successfully',
            'data' => array(
                'is_valid' => false,
            ),
        ) );
    }

    /**
     * Check License
     *
     * @return void
     */
    public function check_license() {
        // Ensure the license check runs only once per request
        if (self::$cached_result !== null) {
            return self::$cached_result; // Return cached result if already checked
        }
    
        // Initialize necessary classes
        // $HydraBooking = new HydraBooking();
        
        // Default response structure
        self::$cached_result = [
            'is_valid' => false,
            'license_type' => false, // Default to 'free'
        ];
    
        $response = get_option('HydraBooking_lic_response_obj', false);  
        

        // tfhb_print_r($response);
        if (false !=  $response ) {
            // if expire_date	is over then return  //2026-03-04 08:21:06
            $exp_date = strtotime($response->expire_date);
            $current_date = strtotime(current_time('mysql'));

            if ($response->expire_date!='No expiry' && $exp_date <= $current_date) {
                return self::$cached_result;
            }
             


            self::$cached_result['is_valid'] = !empty($response->is_valid) ? $response->is_valid : false;
            
            // Determine license type based on 'license_title'
            if (!empty($response->license_title) && stripos($response->license_title, 'free') !== false) {
                self::$cached_result['license_type'] = 'free';
            } else {
                if ( is_plugin_active( 'hydra-booking-pro/hydra-booking-pro.php'  ) ) {
                    self::$cached_result['license_type'] = 'pro';
                }else{
                    self::$cached_result['license_type'] = false; 
                }
            }
        } 
        return self::$cached_result;
    }

    // Function to encrypt the key
    public function encryptKey($plainText, $email) {
        $iv = openssl_random_pseudo_bytes(16); // 16 bytes for AES-256-CBC
        $encrypted = openssl_encrypt($plainText, 'AES-256-CBC', $email, 0, $iv);
        // Combine encrypted text and IV with '::' separator
        return base64_encode($encrypted . '::' . $iv);
    }
    // Function to decrypt the key
    public function decryptKey($encryptedText, $email) {
        $data = base64_decode($encryptedText);
        list($encrypted, $iv) = explode('::', $data, 2);
        return openssl_decrypt($encrypted, 'AES-256-CBC', $email, 0, $iv);
    }
    
    
}