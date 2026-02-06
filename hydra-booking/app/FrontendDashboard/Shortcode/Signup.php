<?php
namespace HydraBooking\FdDashboard\Shortcode;

// exit
if ( ! defined( 'ABSPATH' ) ) { exit; }

// Use Namespace
use HydraBooking\DB\Host;
use HydraBooking\DB\Availability;

/**
 * Signup Class
 * 
 * @author Sydur Rahman
 */
class Signup {

    /**
     * Constructor
     * 
     * @author Sydur Rahman 
     */
    public function __construct() { 
       
        // Add Shortcode
        add_shortcode( 'hydra_signup_form', array( $this, 'hydra_signup_form_shortcode' ) );

        // write ajax function 
        add_action( 'wp_ajax_nopriv_tfhb_registration', array( $this, 'tfhb_registration_callback' ) );
        
 
    }

    /**
     * Hydra Retistration From
     * 
     * @author Sydur Rahman
     * 
     */

     public function hydra_signup_form_shortcode() {

        // Enqueue Select2
		if ( ! wp_script_is( 'tfhb-app-registration', 'enqueued' ) ) {
			wp_enqueue_script( 'tfhb-app-registration' );
		}
        
        $frontend_dashboard_settings = get_option('_tfhb_frontend_dashboard_settings');
        $settings = !empty($frontend_dashboard_settings) ? $frontend_dashboard_settings : array();
        $login_page_id =  isset($settings['login']['login_page']) && !empty($settings['login']['login_page']) ? $settings['login']['login_page'] :  get_option( 'tfhb_login_page_id' );
        $get_login_page_url = get_permalink( $login_page_id );
        $tfhb_dashboard_page_id = get_option( 'tfhb_dashboard_page_id' );
        $signup_page_title = isset($settings['signup']['signup_page_title']) && !empty($settings['signup']['signup_page_title']) ? $settings['signup']['signup_page_title'] :  __('Sign up', 'hydra-booking');
        $signup_page_sub_title = isset($settings['signup']['signup_page_sub_title']) && !empty($settings['signup']['signup_page_sub_title']) ? $settings['signup']['signup_page_sub_title'] :  __('Please enter your details.', 'hydra-booking');
		// Start Buffer
		ob_start(); 

        if( is_user_logged_in() ) {
            ?>
            <div class="tfhb-frontend-from">
                <div class="tfhb-frontend-from__title">
                    <h3><?php echo esc_html(__('You are already logged in', 'hydra-booking')) ?></h3>
                    <!-- go to dashboard button -->

                    <br>
                    <a class="tfhb-from-btn" href="<?php echo get_permalink( $tfhb_dashboard_page_id ) ?>"><?php echo esc_html(__('Go to dashboard', 'hydra-booking')) ?></a>
                    
                </div>
            </div>
        <?php 
            return ob_get_clean();
        }  ?> 
        <div class="tfhb-frontend-from">
            <div class="tfhb-frontend-from__title">
                <h3><?php echo esc_html($signup_page_title) ?></h3>
                <p><?php echo esc_html($signup_page_sub_title) ?></p>
            </div>
            <form action="" id="tfhb-reg-from">
            <?php wp_nonce_field( 'tfhb_check_reg_nonce', 'tfhb_reg_nonce' ); ?>
                <div class="tfhb-frontend-from__field-wrap">
                    <div class="tfhb-frontend-from__field-wrap__inner">
                        <div class="tfhb-frontend-from__field-item">
                            <label for="tfhb_first_name"><?php echo esc_html(__('First Name', 'hydra-booking')) ?></label> 
                            <div class="tfhb-frontend-from__field-item__inner">
                                <span>
                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M9.99992 10.8333C12.3011 10.8333 14.1666 8.96785 14.1666 6.66667C14.1666 4.36548 12.3011 2.5 9.99992 2.5C7.69873 2.5 5.83325 4.36548 5.83325 6.66667C5.83325 8.96785 7.69873 10.8333 9.99992 10.8333Z" stroke="#273F2B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M16.6666 17.4987C16.6666 15.7306 15.9642 14.0349 14.714 12.7847C13.4637 11.5344 11.768 10.832 9.99992 10.832C8.23181 10.832 6.53612 11.5344 5.28587 12.7847C4.03563 14.0349 3.33325 15.7306 3.33325 17.4987" stroke="#273F2B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                <input type="text" name="tfhb_first_name" id="tfhb_first_name" placeholder="First Name">
                            </div>
                        </div>
                        <div class="tfhb-frontend-from__field-item">
                            <label for="tfhb_last_name"><?php echo esc_html(__('Last Name', 'hydra-booking')) ?></label> 
                            <div class="tfhb-frontend-from__field-item__inner">
                                <span>
                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M9.99992 10.8333C12.3011 10.8333 14.1666 8.96785 14.1666 6.66667C14.1666 4.36548 12.3011 2.5 9.99992 2.5C7.69873 2.5 5.83325 4.36548 5.83325 6.66667C5.83325 8.96785 7.69873 10.8333 9.99992 10.8333Z" stroke="#273F2B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M16.6666 17.4987C16.6666 15.7306 15.9642 14.0349 14.714 12.7847C13.4637 11.5344 11.768 10.832 9.99992 10.832C8.23181 10.832 6.53612 11.5344 5.28587 12.7847C4.03563 14.0349 3.33325 15.7306 3.33325 17.4987" stroke="#273F2B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                <input type="text" name="tfhb_last_name" id="tfhb_last_name" placeholder="First Name">
                            </div>
                        </div>
                    </div>

                    <div class="tfhb-frontend-from__field-item">
                        <label for="tfhb_username"><?php echo esc_html(__('Username', 'hydra-booking')) ?></label> 
                        <div class="tfhb-frontend-from__field-item__inner">
                            <span>
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M9.99992 10.8333C12.3011 10.8333 14.1666 8.96785 14.1666 6.66667C14.1666 4.36548 12.3011 2.5 9.99992 2.5C7.69873 2.5 5.83325 4.36548 5.83325 6.66667C5.83325 8.96785 7.69873 10.8333 9.99992 10.8333Z" stroke="#273F2B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M16.6666 17.4987C16.6666 15.7306 15.9642 14.0349 14.714 12.7847C13.4637 11.5344 11.768 10.832 9.99992 10.832C8.23181 10.832 6.53612 11.5344 5.28587 12.7847C4.03563 14.0349 3.33325 15.7306 3.33325 17.4987" stroke="#273F2B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <input type="text" name="tfhb_username" id="tfhb_username" placeholder="Type Username">
                        </div>
                    </div> 
                    <div class="tfhb-frontend-from__field-item">
                        <label for="tfhb_email"><?php echo esc_html(__('Email', 'hydra-booking')) ?></label> 
                        <div class="tfhb-frontend-from__field-item__inner">
                            <span>
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M16.6667 3.33203H3.33341C2.41294 3.33203 1.66675 4.07822 1.66675 4.9987V14.9987C1.66675 15.9192 2.41294 16.6654 3.33341 16.6654H16.6667C17.5872 16.6654 18.3334 15.9192 18.3334 14.9987V4.9987C18.3334 4.07822 17.5872 3.33203 16.6667 3.33203Z" stroke="#273F2B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M18.3334 5.83203L10.8584 10.582C10.6011 10.7432 10.3037 10.8287 10.0001 10.8287C9.69648 10.8287 9.39902 10.7432 9.14175 10.582L1.66675 5.83203" stroke="#273F2B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <input type="text" name="tfhb_email" id="tfhb_email" placeholder="Type your email">
                        </div>
                    </div> 
                    <div class="tfhb-frontend-from__field-item tfhb-password-field">
                        <label for="tfhb_password"><?php echo esc_html(__('Password', 'hydra-booking')) ?></label> 
                        <div class="tfhb-frontend-from__field-item__inner">
                            <span>
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <g clip-path="url(#clip0_6411_9196)">
                                    <path d="M1.66675 15.0019V17.5019C1.66675 18.0019 2.00008 18.3353 2.50008 18.3353H5.83341V15.8353H8.33341V13.3353H10.0001L11.1667 12.1686C12.3249 12.572 13.5858 12.5705 14.743 12.1642C15.9002 11.7579 16.8853 10.971 17.537 9.93203C18.1888 8.8931 18.4688 7.66373 18.331 6.44504C18.1933 5.22634 17.646 4.09047 16.7788 3.22323C15.9115 2.356 14.7757 1.80874 13.557 1.671C12.3383 1.53325 11.1089 1.81317 10.07 2.46496C9.03105 3.11675 8.24407 4.10182 7.83779 5.25902C7.4315 6.41623 7.42996 7.67706 7.83341 8.83526L1.66675 15.0019Z" stroke="#273F2B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M13.7499 6.66536C13.98 6.66536 14.1666 6.47882 14.1666 6.2487C14.1666 6.01858 13.98 5.83203 13.7499 5.83203C13.5198 5.83203 13.3333 6.01858 13.3333 6.2487C13.3333 6.47882 13.5198 6.66536 13.7499 6.66536Z" fill="#273F2B" stroke="#273F2B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </g>
                                <defs>
                                    <clipPath id="clip0_6411_9196">
                                    <rect width="20" height="20" fill="white"/>
                                    </clipPath>
                                </defs>
                            </svg>
                            </span>
                            <input type="password" name="tfhb_password" id="tfhb_password" placeholder="Type your password">
                            <span class="tfhb-frontend-from__field-item__inner__show-password tfhb-show-password">  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/></svg></span>
                        </div>
                    </div> 
                    <div class="tfhb-frontend-from__field-item tfhb-password-field">
                        <label for="tfhb_confirm_password"><?php echo esc_html(__('Confirm Password', 'hydra-booking')) ?></label> 
                        <div class="tfhb-frontend-from__field-item__inner">
                            <span>
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <g clip-path="url(#clip0_6411_9196)">
                                    <path d="M1.66675 15.0019V17.5019C1.66675 18.0019 2.00008 18.3353 2.50008 18.3353H5.83341V15.8353H8.33341V13.3353H10.0001L11.1667 12.1686C12.3249 12.572 13.5858 12.5705 14.743 12.1642C15.9002 11.7579 16.8853 10.971 17.537 9.93203C18.1888 8.8931 18.4688 7.66373 18.331 6.44504C18.1933 5.22634 17.646 4.09047 16.7788 3.22323C15.9115 2.356 14.7757 1.80874 13.557 1.671C12.3383 1.53325 11.1089 1.81317 10.07 2.46496C9.03105 3.11675 8.24407 4.10182 7.83779 5.25902C7.4315 6.41623 7.42996 7.67706 7.83341 8.83526L1.66675 15.0019Z" stroke="#273F2B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M13.7499 6.66536C13.98 6.66536 14.1666 6.47882 14.1666 6.2487C14.1666 6.01858 13.98 5.83203 13.7499 5.83203C13.5198 5.83203 13.3333 6.01858 13.3333 6.2487C13.3333 6.47882 13.5198 6.66536 13.7499 6.66536Z" fill="#273F2B" stroke="#273F2B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </g>
                                <defs>
                                    <clipPath id="clip0_6411_9196">
                                    <rect width="20" height="20" fill="white"/>
                                    </clipPath>
                                </defs>
                            </svg>
                            </span>
                            <input type="password" name="tfhb_confirm_password" id="tfhb_confirm_password" placeholder="Re-type your password">
                              <span class="tfhb-frontend-from__field-item__inner__show-password tfhb-show-password">  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/></svg></span>
                        </div>
                    </div> 
                    <div class="tfhb-frontend-from__field-item">
                        <button type="submit">
                            <span class="tfhb-submit-text"><?php echo esc_html(__('Sign up', 'hydra-booking')) ?></span>
                            <span class="tfhb-submit-icon">
                                <svg width="21" height="20" viewBox="0 0 21 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <g clip-path="url(#clip0_6411_13565)">
                                        <path d="M7.5 4.16797L13.3333 10.0013L7.5 15.8346" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </g>
                                    <defs>
                                        <clipPath id="clip0_6411_13565">
                                        <rect width="20" height="20" fill="white" transform="translate(0.5)"/>
                                        </clipPath>
                                    </defs>
                                </svg>
                            </span>
                        </button>
                    </div> 
                </div> 
            </form>
             <div class="tfhb-frontend-from__field-item tfhb-frontend-from__field-item--center">
                <p><?php echo esc_html(__('Already have an account?', 'hydra-booking')) ?><a href="<?php echo esc_url( $get_login_page_url ); ?>"> <?php echo esc_html(__('Login', 'hydra-booking')) ?></a></p> 
            </div>
        </div>
        <?php 


        return ob_get_clean();
     }

     /**
     * Generate random string for verification url
     */
    public function tfhb_generate_random_string( $stringLength ) {
        //specify characters to be used in generating random string, do not specify any characters that wordpress does not allow in the creation.
        $characters = "0123456789ABCDEFGHIJKLMNPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz_[]{}!@$%^*().,>=-;|:?";

        //get the total length of specified characters to be used in generating random string
        $charactersLength = strlen( $characters );

        //declare a string that we will use to create the random string
        $randomString = '';

        for ( $i = 0; $i < $stringLength; $i ++ ) {
            //generate random characters
            $randomCharacter = $characters[ wp_rand( 0, $charactersLength - 1 ) ];
            //add the random characters to the random string
            $randomString .= $randomCharacter;
        };

        //sanitize_user, just in case
        $sanRandomString = sanitize_user( $randomString );

        //check that random string contains Uppercase/Lowercase/Intergers/Special Char and that it is the correct length
        if ( ( preg_match( '([a-zA-Z].*[0-9]|[0-9].*[a-zA-Z].*[_\W])', $sanRandomString ) == 1 ) && ( strlen( $sanRandomString ) == $stringLength ) ) {
            //return the random string if it meets the complexity criteria
            return $sanRandomString;
        } else {
            // if the random string does not meet minimium criteria call function again
            return call_user_func( "generateRandomString", ( $stringLength ) );
        }
    }

     /**
     * tfhb_registration_callback
     *
     * @return void
     * @author Sydur Rahman
     */

     public function tfhb_registration_callback(){

        $response = [
            'success' => false,
        ];

        $field = [];
        foreach ( $_POST as $key => $value ) {
            $field[ $key ] = sanitize_text_field( $value );
        }

        $user_role = 'tfhb_host';

        $required_fields = array( 'tfhb_first_name', 'tfhb_last_name', 'tfhb_username', 'tfhb_email', 'tfhb_password', 'tfhb_confirm_password' );
        
        if ( ! isset( $field['tfhb_reg_nonce'] ) || ! wp_verify_nonce( $field['tfhb_reg_nonce'], 'tfhb_check_reg_nonce' ) ) {
            $response['message'] = esc_html__( 'Sorry, your nonce did not verify.', 'hydra-booking' );
        } else {
            foreach ( $required_fields as $required_field ) {
                if ( $required_field === 'tfhb_email' ) {
                    if ( empty( $field[ $required_field ] ) ) {
                        $response['fieldErrors'][ $required_field] = esc_html__( 'Please enter your email address', 'hydra-booking' );
                    } elseif ( ! is_email( $field[ $required_field ] ) ) {
                        $response['fieldErrors'][ $required_field] = esc_html__( 'Please enter a valid email address', 'hydra-booking' );
                    }
                } elseif ( $required_field === 'tfhb_password' ) {
                    if ( empty( $field[ $required_field ] ) ) {
                        $response['fieldErrors'][ $required_field] = esc_html__( 'Please enter your password', 'hydra-booking' );
                    } elseif ( ! preg_match( '@[A-Z]@', $field['tfhb_password'] ) ) {
                        $response['fieldErrors'][ $required_field] = esc_html__( 'Password must be include at least one uppercase letter', 'hydra-booking' );
                    } elseif ( ! preg_match( '@[0-9]@', $field['tfhb_password'] ) ) {
                        $response['fieldErrors'][ $required_field] = esc_html__( 'Password must be include at least one number', 'hydra-booking' );
                    } elseif ( ! preg_match( '@[^\w]@', $field['tfhb_password'] ) ) {
                        $response['fieldErrors'][ $required_field] = esc_html__( 'Password must be include at least one special character', 'hydra-booking' );
                    } elseif ( strlen( $field['tfhb_password'] ) < 8 ) {
                        $response['fieldErrors'][ $required_field] = esc_html__( 'Password must be at least 8 characters', 'hydra-booking' );
                    }
                } elseif ( $required_field === 'tfhb_confirm_password' ) {
                    if ( empty( $field[ $required_field ] ) ) {
                        $response['fieldErrors'][ $required_field] = esc_html__( 'Please re-enter your password', 'hydra-booking' );
                    } elseif ( $field['tfhb_password'] !== $field['tfhb_confirm_password'] ) {
                        $response['fieldErrors'][ $required_field] = esc_html__( 'Passwords doesn\'t match', 'hydra-booking' );
                    }
                } elseif ( empty( $field[ $required_field ] ) ) {
                    $response['fieldErrors'][ $required_field] = esc_html__( 'The field is required', 'hydra-booking' );
                }
            }
        }

       // Check if email and username already exist
       $user = get_user_by( 'email', $field['tfhb_email'] );
       if ( $user ) {
           $response['fieldErrors']['tfhb_email'] = esc_html__( 'Email already exist', 'hydra-booking' );
       } else {
           $user = get_user_by( 'login', $field['tfhb_username'] );
           if ( $user ) {
               $response['fieldErrors']['tfhb_username'] = esc_html__( 'Username already exist', 'hydra-booking' );
           }
       }

       if(!$response['fieldErrors']){
            $host = new Host();
            $userdata = array(
                'user_login' => sanitize_user( $field['tfhb_username'] ),
                'user_email' => sanitize_email( $field['tfhb_email'] ),
                'user_pass'  => esc_attr( $field['tfhb_password'] ),
                'role'       => $user_role,
            );

            $user_id = wp_insert_user( $userdata );
            if ( is_wp_error( $user_id ) ) {
                $response['message'] = $user_id->get_error_message();
            } else {
                $user = get_user_by( 'ID', $user_id );
                $settings = get_option('_tfhb_frontend_dashboard_settings');

                $enable_email_verification = isset($settings['signup']['enable_email_verification']) ? sanitize_text_field($settings['signup']['enable_email_verification']) : false;

                // update user first name and last name
                update_user_meta( $user->ID, 'first_name', $field['tfhb_first_name'] );

                update_user_meta( $user->ID, 'last_name', $field['tfhb_last_name'] );

                // update user activation code 
                
			    $code = $this->tfhb_generate_random_string( 32 );
                update_user_meta( $user->ID, 'tfhb_user_activation_code', $code );

             

                // $user->set_role( $user_role );
                // $response['success'] = true;

                // Check if user is already a host
                $hostCheck = $host->getHostByUserId( $user_id );
                if ( ! empty( $hostCheck ) ) {
                    $response['message'] = esc_html__( 'You are already a host', 'hydra-booking' );
                    
                }else{
                    // insert host
                    $data = array(
                        'user_id'        => $user->ID,
                        'first_name'     => get_user_meta( $user->ID, 'first_name', true ) != '' ? get_user_meta( $user->ID, 'first_name', true ) : $user->display_name,
                        'last_name'      => get_user_meta( $user->ID, 'last_name', true ) != '' ? get_user_meta( $user->ID, 'last_name', true ) : '',
                        'email'          => $user->user_email,
                        'phone_number'   => '',
                        'time_zone'      => '',
                        'about'          => '',
                        'avatar'         => '',
                        'featured_image' => '',
                        // 'status'         => 'deactivate',
                    );

                    if ($enable_email_verification == false){ 
                        update_user_meta( $user->ID, 'tfhb_user_is_activated', true );
                        $data['status'] = 'activate';
                    }else{ 
                        update_user_meta( $user->ID, 'tfhb_user_is_activated', false );
                        $data['status'] = 'deactivate';
                    }
            
                    // get Default Availability
                    $Availability = new Availability();
                    
                    // get default availability
                    $getAvailability = $Availability->get(
                        array(
                            'default_status' => true,
                        ),
                        false,
                        true,
                    );
                    if($getAvailability){
                         $data['availability_type'] = 'settings';
                         $data['availability_id'] = $getAvailability->id;
                    }  
            
            
                    // Insert Host
                    $hostInsert = $host->add( $data );
                   
                    if ( $hostInsert ) {
                        

                        if ($enable_email_verification == false){  
                            // Send activation email 
                            $this->tfhb_send_email_confirmation($data);

                            $response['message'] = esc_html__( 'Your account has been created successfully. You can login using your email and password.', 'hydra-booking' );
                        }else{ 
                            // Send activation email 
                            $this->tfhb_send_activation_code($data, $code);

                            $response['message'] = esc_html__( 'Your account has been created successfully. A confirmation email has been sent to your email address.', 'hydra-booking' );
                        }


                        unset( $data['user_id'] );
                        $data['host_id'] = $hostInsert['insert_id'];
                        // Update user Option
                        update_user_meta( $user_id, '_tfhb_host', $data );


                        $response['success'] = true;
                    }
                }

                
            }
       } 
        // return response
        wp_send_json( $response );

     }

    /**
     * Send Activation Code
     *
     * @param array $data
     * @param string $activation_code
     * @return void
     */
    public function tfhb_send_activation_code( $data, $code ) {

        $email = $data['email'];
        $name = $data['first_name'] . ' ' . $data['last_name'];
        $string = array( 'id' => $data['user_id'], 'code' => $code );
        $subject = esc_html__( 'Email Verification', 'hydra-booking' );
        $url = get_site_url() . '/?hydra-booking=email-verification&tfhb_verification=' . base64_encode( json_encode( $string ) );
        $message = '<p>' . esc_html__( 'Hi', 'hydra-booking' ) . ' ' . $name . '</p>';
        $message .= '<p>' . esc_html__( 'Please click the link below to activate your account:', 'hydra-booking' ) . '</p>';
        $message .= '<p><a target="_blank" href="' . $url . '">' . $url . '</a></p>';
        $message .= '<p>' . esc_html__( 'Thank you', 'hydra-booking' ) . '</p>';

        $headers = 'From: ' . get_bloginfo( 'name' ) . ' <' . get_bloginfo( 'admin_email' ) . '>' . "\r\n";
        $headers .= 'Content-Type: text/html; charset=UTF-8' . "\r\n";

        wp_mail( $email, $subject, $message, $headers );

    }
    /**
     * Send Email Confirmation 
     *
     * @param array $data
     * @param string $activation_code
     * @return void
     */
    public function tfhb_send_email_confirmation( $data ) {

       // send confirmation email 
       $email = $data['email'];
       $name = $data['first_name'] . ' ' . $data['last_name'];
        $subject = esc_html__( 'Your account has been activated', 'hydra-booking' );
        $message = '<p>' . esc_html__( 'Hi', 'hydra-booking' ) . ' ' . $name . '</p>';
        $message .= '<p>' . esc_html__( 'Your account has been successfully activated.', 'hydra-booking' ) . '</p>'; 

        $headers = 'From: ' . get_bloginfo( 'name' ) . ' <' . get_bloginfo( 'admin_email' ) . '>' . "\r\n";
        $headers .= 'Content-Type: text/html; charset=UTF-8' . "\r\n"; 

        wp_mail( $email, $subject, $message, $headers );

    }


 

}