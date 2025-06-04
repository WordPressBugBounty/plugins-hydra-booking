

<?php 
// exit
if ( ! defined( 'ABSPATH' ) ) { exit; } 

use HydraBooking\DB\Host;
$page = get_query_var( 'hydra-booking' );
$tfhb_verification = get_query_var( 'tfhb_verification' );
if($page != "email-verification" || !$tfhb_verification) {
    return ;
}
 
$data = base64_decode($tfhb_verification);
 
$data = json_decode($data, true);

$user_id = $data ? $data['id'] : '';
$data_code = $data ? $data['code'] : '';
$saved_code = !empty($user_id) ? get_user_meta($user_id, 'tfhb_user_activation_code', true) : '';
$tfhb_user_is_activated = !empty($user_id) ? get_user_meta($user_id, 'tfhb_user_is_activated', true) : '';



$frontend_dashboard_settings = get_option('_tfhb_frontend_dashboard_settings');
$settings = !empty($frontend_dashboard_settings) ? $frontend_dashboard_settings : array();
$login_page_id =  isset($settings['login']['login_page']) && !empty($settings['login']['login_page']) ? $settings['login']['login_page'] :  get_option( 'tfhb_login_page_id' );
$tfhb_dashboard_page_id = isset($settings['login']['after_login_redirect']) && !empty($settings['login']['after_login_redirect']) ? $settings['login']['after_login_redirect'] :  get_option( 'tfhb_dashboard_page_id' );
   
get_header();   

 
    ?>
    <div class="tfhb-frontend-from">
        <div class="tfhb-frontend-from__title">
            <?php
                /**
                 * Verification
                 */
                if(is_user_logged_in()){

                 ?>
                   <h3><?php echo esc_html(__('You are already logged in', 'hydra-booking')) ?></h3>
                    <!-- go to dashboard button -->
                    <br>
                    <a class="tfhb-from-btn" href="<?php echo get_permalink( $tfhb_dashboard_page_id ) ?>"><?php echo esc_html(__('Go to dashboard', 'hydra-booking')) ?></a>
                    
                 <?php 
                    
                    
                }
                elseif(!empty($saved_code) && $saved_code == $data_code) { 
                    $host  = new Host();
                    $getHost = $host->getHostByUserId($user_id);
                    if($getHost) { 
                        printf( esc_html__( '%1$sYour email has been successfully verified! %2$sLogin here%3$s', 'hydra-booking' ), '<h3>', '</h3><br><a class="tfhb-from-btn" href="' .esc_url(get_permalink( $login_page_id )). '">', '</a>' );
                        update_user_meta($user_id, 'tfhb_user_is_activated', 1);
                        delete_user_meta($user_id, 'tfhb_user_activation_code');
                        // updatehost status
                        $data = [
                            'id' => $getHost->id,
                            'status' => 'activate'
                        ];
                        $host->update($data);

                        // send confirmation email 
                        $email = $getHost->email;
                        $name = $getHost->first_name . ' ' . $getHost->last_name;
                        $subject = esc_html__( 'Your account has been activated', 'hydra-booking' );
                        $message = '<p>' . esc_html__( 'Hi', 'hydra-booking' ) . ' ' . $name . '</p>';
                        $message .= '<p>' . esc_html__( 'Your account has been successfully activated.', 'hydra-booking' ) . '</p>'; 

                        $headers = 'From: ' . get_bloginfo( 'name' ) . ' <' . get_bloginfo( 'admin_email' ) . '>' . "\r\n";
                        $headers .= 'Content-Type: text/html; charset=UTF-8' . "\r\n";

                        wp_mail( $email, $subject, $message, $headers );
                    }

                } elseif (!empty($saved_code) && $saved_code != $data_code) {

                    printf( esc_html__( '%1$s Invalid email verification url! %2$sResend email%3$', 'hydra-booking' ), '<h3>', '<br><a class="tfhb-from-btn" href="" class="resend-email-verification" data-id="' .esc_attr($user_id). '">', '</a></h3>' );

                } elseif (empty($saved_code) && $tfhb_user_is_activated == '1') {

                    printf( esc_html__( '%1$sYour email is already verified. %2$sLogin here%3$s', 'hydra-booking' ), '<h3>', '</h3> <br><a class="tfhb-from-btn" href="' .esc_url(get_permalink( $login_page_id )). '">', '</a>' );
                    
                } elseif (empty($saved_code) && $tfhb_user_is_activated == '0') {

                    printf( esc_html__( '%1$s No email verification url found! %2$sResend email%3$', 'hydra-booking' ), '<h3>', '<br><a class="tfhb-from-btn" href="" class="resend-email-verification" data-id="' .esc_attr($user_id). '">', '</a></h3>' );
                    
                } elseif (empty($saved_code) && empty($tfhb_user_is_activated)) {

                    printf( esc_html__( '%1$s Invalid or not found email verification url! %2$s', 'hydra-booking' ), '<h3>', '</h3>' );
                    
                } else {

                    printf( esc_html__( '%1$s Verification failed! %2$s', 'hydra-booking' ), '<h3>', '</h3>' );
                    
                }
        
            ?>
            
        </div>
    </div>
    <?php  
get_footer(); 

