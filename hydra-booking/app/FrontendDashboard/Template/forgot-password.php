<?php 
// exit

if ( ! defined( 'ABSPATH' ) ) { exit; }

$page = get_query_var( 'hydra-booking' );
$tfhb_verification = get_query_var( 'tfhb_verification' );
 
$data = base64_decode($tfhb_verification);
$data = json_decode($data, true);

$frontend_dashboard_settings = get_option('_tfhb_frontend_dashboard_settings');
$settings = !empty($frontend_dashboard_settings) ? $frontend_dashboard_settings : array();
$login_page_id =  isset($settings['login']['login_page']) && !empty($settings['login']['login_page']) ? $settings['login']['login_page'] :  get_option( 'tfhb_login_page_id' );
 
$registration_page_id = isset($settings['signup']['registration_page']) && !empty($settings['signup']['registration_page']) ? $settings['signup']['registration_page'] :  get_option( 'tfhb_register_page_id' );
$tfhb_dashboard_page_id = isset($settings['login']['after_login_redirect']) && !empty($settings['login']['after_login_redirect']) ? $settings['login']['after_login_redirect'] :  get_option( 'tfhb_dashboard_page_id' );
                   
get_header(); 
 

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
} elseif($page && $tfhb_verification) {
    
    // check verification code get_password_reset_key( $user )
    $user = get_user_by( 'email', $data['email'] );

    $user_id = $user->ID;
    $code = $data['code']; 
    $check = check_password_reset_key($code, $user->user_login  );
    
   
    // if code is valid then display password reset form
    if ( is_wp_error( $check ) ) {
        ?>
        <div class="tfhb-frontend-from">
            <div class="tfhb-frontend-from__title">
                <h3 style="text-align: center;"><?php echo $check->get_error_message(); ?></h3>
                <br>
                <!-- go to dashboard button -->
                <div class="tfhb-frontend-from__field-item tfhb-frontend-from__field-item--center">
                    <p><a href="<?php echo esc_url( get_permalink( $login_page_id ) ); ?>"><?php echo esc_html(__('Login', 'hydra-booking')) ?></a>
                        |
                    <a href="<?php echo esc_url( get_permalink( $registration_page_id ) ) ?>"><?php echo esc_html(__('Sign up', 'hydra-booking')) ?> </a></p>
                    <p></p>
                        
                </div>
                
            </div>
        </div>
        <?php
        
    }else{

        ?>
            <div class="tfhb-frontend-from">
            <div class="tfhb-frontend-from__title">
                <h3><?php echo esc_html(__('Reset Password ?', 'hydra-booking')) ?></h3>
                <p><?php echo esc_html(__('Please enter your details.', 'hydra-booking')) ?></p>
            </div>
            <form action="" id="tfhb-reset-password-from">
            <?php wp_nonce_field( 'tfhb_check_reset_password_nonce', 'tfhb_reset_password_nonce' ); ?>
            <input type="hidden" name="code" value="<?php echo esc_attr($tfhb_verification) ?>">
                <div class="tfhb-frontend-from__field-wrap">

                    <div class="tfhb-frontend-from__field-item">
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
                        </div>
                    </div>

                    <div class="tfhb-frontend-from__field-item">
                        <label for="tfhb_confirm_password"><?php echo esc_html(__('Confirm Password',  'hydra-booking')) ?></label> 
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
                        </div>
                    </div>

                    <div class="tfhb-frontend-from__field-item">
                        <button type="submit">
                            <span><?php echo esc_html(__('Reset Password', 'hydra-booking')) ?></span>
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
                    <div class="tfhb-frontend-from__field-item tfhb-frontend-from__field-item--center">
                        <p><a href="<?php echo esc_url( get_permalink( $login_page_id ) ); ?>"><?php echo esc_html(__('Login', 'hydra-booking')) ?></a>
                            |
                        <a href="<?php echo esc_url( get_permalink( $registration_page_id ) ) ?>"><?php echo esc_html(__('Sign up', 'hydra-booking')) ?> </a></p>
                        <p></p>
                            
                    </div>
                    
                </div>
            </form>
        </div>
    <?php
    } 
}else{
?>
<div class="tfhb-frontend-from">
    <div class="tfhb-frontend-from__title">
        <h3><?php echo esc_html(__('Forgot Password ?', 'hydra-booking')) ?></h3>
        <p><?php echo  esc_html(__('Please enter your details.', 'hydra-booking')) ?></p>
    </div>
    <form action="" id="tfhb-forgot-password-from">
        <?php wp_nonce_field( 'tfhb_check_forgot_nonce', 'tfhb_forgot_nonce' ); ?>
        <div class="tfhb-frontend-from__field-wrap">
            

            <div class="tfhb-frontend-from__field-item">
                <label for="tfhb_forgot_user"><?php echo  esc_html(__('Username or Email', 'hydra-booking')) ?></label> 
                <div class="tfhb-frontend-from__field-item__inner">
                    <span>
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9.99992 10.8333C12.3011 10.8333 14.1666 8.96785 14.1666 6.66667C14.1666 4.36548 12.3011 2.5 9.99992 2.5C7.69873 2.5 5.83325 4.36548 5.83325 6.66667C5.83325 8.96785 7.69873 10.8333 9.99992 10.8333Z" stroke="#273F2B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M16.6666 17.4987C16.6666 15.7306 15.9642 14.0349 14.714 12.7847C13.4637 11.5344 11.768 10.832 9.99992 10.832C8.23181 10.832 6.53612 11.5344 5.28587 12.7847C4.03563 14.0349 3.33325 15.7306 3.33325 17.4987" stroke="#273F2B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <input type="text" name="tfhb_forgot_user" id="tfhb_forgot_user" placeholder="Enter Username or Email">
                </div>
            </div> 

            <div class="tfhb-frontend-from__field-item">
                <button type="submit">
                    <span><?php echo esc_html(__('Reset my Password', 'hydra-booking')) ?></span>
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

            <div class="tfhb-frontend-from__field-item tfhb-frontend-from__field-item--center">
                <p><a href="<?php echo esc_url( get_permalink( $login_page_id ) ); ?>"><?php echo esc_html(__('Login', 'hydra-booking')) ?></a>
                    |
                <a href="<?php echo esc_url( get_permalink( $registration_page_id ) ) ?>"><?php echo esc_html(__('Sign up', 'hydra-booking')) ?> </a></p>
                <p></p>
                       
            </div>
            
        </div>
    </form>
</div>
<?php 
}
get_footer(); 
