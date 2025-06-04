<?php 
namespace HydraBooking\License;

// Use HydraBookingBase 
use HydraBooking\License\HydraBookingBase; 
// exit
if ( ! defined( 'ABSPATH' ) ) { exit; }
class HydraBooking {
    public $plugin_file=__FILE__;
    public $response_obj;
    public $license_message;
    public $show_message=false;
    public $slug="hydra-booking-pro";
    public $plugin_version='';
    public $text_domain='';
    function __construct() {
        // add_action( 'admin_print_styles', [ $this, 'set_admin_style' ] );
        
        $this->set_plugin_data();
	    $main_lic_key="HydraBooking_lic_Key";
	    $lic_key_name =HydraBookingBase::get_lic_key_param($main_lic_key);
        $license_key=get_option($lic_key_name,"");
        if(empty($license_key)){
	        $license_key=get_option($main_lic_key,"");
	        if(!empty($license_key)){
	            update_option($lic_key_name,$license_key) || add_option($lic_key_name,$license_key);
		        update_option($main_lic_key,$license_key);
            }
        }
        $lice_email=get_option( "HydraBooking_lic_email","");

        if(!empty($license_key)){
            // if key is not encripted then encript it
            $decoded = base64_decode($license_key, true);

            if($decoded === false){
                $license_key=$this->encryptKey($license_key,$lice_email);
                update_option($main_lic_key,$license_key) || add_option($main_lic_key,$license_key);
                update_option($lic_key_name,$license_key) || add_option($lic_key_name,$license_key);
            }

            // decrypt key
            $license_key=$this->decryptKey($license_key,$lice_email);

        }
        

        HydraBookingBase::add_on_delete(function(){
           update_option("HydraBooking_lic_Key","");
        });
        if(HydraBookingBase::check_wp_plugin($license_key,$lice_email,$this->license_message,$this->response_obj,TFHB_BASE_FILE)){
            // add_action( 'admin_menu', [$this,'active_admin_menu'],99999);
            // add_action( 'admin_post_HydraBooking_el_deactivate_license', [ $this, 'action_deactivate_license' ] );
            //$this->licenselMessage=$this->mess;
            //***Write you plugin's code here***
            // tfhb_print_r( $this->response_obj);
            $responsed = $this->response_obj;
            // delete_option("HydraBooking_lic_response_obj");
            update_option("HydraBooking_lic_response_obj",$responsed);

        }else{
            // if(!empty($license_key) && !empty($this->license_message)){
            //    $this->show_message=true;
            // }
            // update_option($license_key,"") || add_option($license_key,"");
            // add_action( 'admin_post_HydraBooking_el_activate_license', [ $this, 'action_activate_license' ] );
            // add_action( 'admin_menu', [$this,'inactive_menu']);
            update_option("HydraBooking_lic_response_obj",false);
        }
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
    public function set_plugin_data(){
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		if ( function_exists( 'get_plugin_data' ) ) {
			$data = get_plugin_data( $this->plugin_file );
			if ( isset( $data['Version'] ) ) {
				$this->plugin_version = $data['Version'];
			}
			if ( isset( $data['TextDomain'] ) ) {
				$this->text_domain = $data['TextDomain'];
			}
		}
    }
	private static function &get_server_array() {
		return $_SERVER;
	}
	private static function get_raw_domain(){
		if(function_exists("site_url")){
			return site_url();
		}
		if ( defined( "WPINC" ) && function_exists( "get_bloginfo" ) ) {
			return get_bloginfo( 'url' );
		} else {
			$server = self::get_server_array();
			if ( ! empty( $server['HTTP_HOST'] ) && ! empty( $server['SCRIPT_NAME'] ) ) {
				$base_url  = ( ( isset( $server['HTTPS'] ) && $server['HTTPS'] == 'on' ) ? 'https' : 'http' );
				$base_url .= '://' . $server['HTTP_HOST'];
				$base_url .= str_replace( basename( $server['SCRIPT_NAME'] ), '', $server['SCRIPT_NAME'] );
				
				return $base_url;
			}
		}
		return '';
	}
	private static function get_raw_wp(){
		$domain=self::get_raw_domain();
		return preg_replace("(^https?://)", "", $domain );
	}
	public static function get_lic_key_param($key){
		$raw_url=self::get_raw_wp();
		return $key."_s".hash('crc32b',$raw_url."vtpbdapps");
	}
	public function set_admin_style() {
        wp_register_style( "HydraBookingLic", plugins_url("_lic_style.css",$this->plugin_file),10,time());
        wp_enqueue_style( "HydraBookingLic" );
    }
	public function active_admin_menu(){
        
		add_menu_page (  "HydraBooking", "Hydra Booking", "activate_plugins", $this->slug, [$this,"activated"], " dashicons-star-filled ");
		//add_submenu_page(  $this->slug, "HydraBooking License", "License Info", "activate_plugins",  $this->slug."_license", [$this,"activated"] );

    }
	public function inactive_menu() {
        add_menu_page( "HydraBooking", "Hydra Booking", 'activate_plugins', $this->slug,  [$this,"license_form"], " dashicons-star-filled " );

    }
    function action_activate_license($el_license_key, $el_license_email) {
    
        check_admin_referer( 'el-license' );
        $license_key=!empty($el_license_key)?sanitize_text_field(wp_unslash($el_license_key)):"";
        $license_email=!empty($el_license_email)?sanitize_email(wp_unslash($el_license_email)):"";
	    $main_lic_key="HydraBooking_lic_Key";
	    $lic_key_name = HydraBookingBase::get_lic_key_param($main_lic_key); 
         
        update_option($lic_key_name,$license_key) || add_option($lic_key_name,$license_key);
        update_option("HydraBooking_lic_email",$license_email) || add_option("HydraBooking_lic_email",$license_email);
        update_option('_site_transient_update_plugins','');
        wp_safe_redirect(admin_url( 'admin.php?page='.$this->slug));
    }
    function action_deactivate_license() {
        check_admin_referer( 'el-license' );
        $message="";
	    $main_lic_key="HydraBooking_lic_Key";
	    $lic_key_name =HydraBookingBase::get_lic_key_param($main_lic_key);
        if(HydraBookingBase::remove_license_key(__FILE__,$message)){
            update_option($lic_key_name,"") || add_option($lic_key_name,"");
            update_option('_site_transient_update_plugins','');
        }
        wp_safe_redirect(admin_url( 'admin.php?page='.$this->slug));
    }
    function activated(){
        ?>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <input type="hidden" name="action" value="HydraBooking_el_deactivate_license"/>
            <div class="el-license-container">
                <h3 class="el-license-title"><i class="dashicons-before dashicons-star-filled"></i> <?php esc_html_e("Hydra Booking License Info","hydra-booking");?> </h3>
                <hr>
                <ul class="el-license-info">
                <li>
                    <div>
                        <span class="el-license-info-title"><?php esc_html_e("Status","hydra-booking");?></span>

                        <?php if ( $this->response_obj->is_valid ) : ?>
                            <span class="el-license-valid"><?php esc_html_e("Valid","hydra-booking");?></span>
                        <?php else : ?>
                            <span class="el-license-valid"><?php esc_html_e("Invalid","hydra-booking");?></span>
                        <?php endif; ?>
                    </div>
                </li>

                <li>
                    <div>
                        <span class="el-license-info-title"><?php esc_html_e("License Type","hydra-booking");?></span>
                        <?php echo esc_html($this->response_obj->license_title,"hydra-booking"); ?>
                    </div>
                </li>

               <li>
                   <div>
                       <span class="el-license-info-title"><?php esc_html_e("License Expired on","hydra-booking");?></span>
                       <?php echo esc_html($this->response_obj->expire_date,"hydra-booking");
                       if(!empty($this->response_obj->expire_renew_link)){
                           ?>
                           <a target="_blank" class="el-blue-btn" href="<?php echo esc_url($this->response_obj->expire_renew_link); ?>">Renew</a>
                           <?php
                       }
                       ?>
                   </div>
               </li>

               <li>
                   <div>
                       <span class="el-license-info-title"><?php esc_html_e("Support Expired on","hydra-booking");?></span>
                       <?php
                           echo esc_html($this->response_obj->support_end,"hydra-booking");;
                        if(!empty($this->response_obj->support_renew_link)){
                            ?>
                               <a target="_blank" class="el-blue-btn" href="<?php echo esc_url($this->response_obj->support_renew_link); ?>">Renew</a>
                            <?php
                        }
                       ?>
                   </div>
               </li>
                <li>
                    <div>
                        <span class="el-license-info-title"><?php esc_html_e("Your License Key","hydra-booking");?></span>
                        <span class="el-license-key"><?php echo esc_attr( substr($this->response_obj->license_key,0,9)."XXXXXXXX-XXXXXXXX".substr($this->response_obj->license_key,-9) ); ?></span>
                    </div>
                </li>
                </ul>
                <div class="el-license-active-btn">
                    <?php wp_nonce_field( 'el-license' ); ?>
                    <?php submit_button('Deactivate'); ?>
                </div>
            </div>
        </form>
    <?php
    }

    function license_form() {
        ?>
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <input type="hidden" name="action" value="HydraBooking_el_activate_license"/>
        <div class="el-license-container">
            <h3 class="el-license-title"><i class="dashicons-before dashicons-star-filled"></i> <?php esc_html_e("Hydra Booking Licensing","hydra-booking");?></h3>
            <hr>
            <?php
            if(!empty($this->show_message) && !empty($this->license_message)){
                ?>
                <div class="notice notice-error is-dismissible">
                    <p><?php echo esc_html($this->license_message,"hydra-booking"); ?></p>
                </div>
                <?php
            }
            ?>
            <p><?php esc_html_e("Enter your license key here, to activate the product, and get full feature updates and premium support.","hydra-booking");?></p>
<ol>
    <li><?php esc_html_e("Write your licnese key details","hydra-booking");?></li>
    <li><?php esc_html_e("How buyer will get this license key?","hydra-booking");?></li>
    <li><?php esc_html_e("Describe other info about licensing if required","hydra-booking");?></li>
    <li>. ...</li>
</ol>
            <div class="el-license-field">
                <label for="el_license_key"><?php echo esc_html("License code","hydra-booking");?></label>
                <input type="text" class="regular-text code" name="el_license_key" size="50" placeholder="xxxxxxxx-xxxxxxxx-xxxxxxxx-xxxxxxxx" required="required">
            </div>
            <div class="el-license-field">
                <label for="el_license_key"><?php echo esc_html("Email Address","hydra-booking");?></label>
                <?php
                    $purchase_email   = get_option( "HydraBooking_lic_email", get_bloginfo( 'admin_email' ));
                ?>
                <input type="text" class="regular-text code" name="el_license_email" size="50" value="<?php echo esc_html($purchase_email); ?>" placeholder="" required="required">
                <div><small><?php echo esc_html("We will send update news of this product by this email address, don't worry, we hate spam","hydra-booking");?></small></div>
            </div>
            <div class="el-license-active-btn">
                <?php wp_nonce_field( 'el-license' ); ?>
                <?php submit_button('Activate'); ?>
            </div>
        </div>
    </form>
        <?php
    }
}