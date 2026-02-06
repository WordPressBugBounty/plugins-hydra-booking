<?php
namespace HydraBooking\Admin\Controller;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PromoNotice {

    private $api_url = 'https://api.themefic.com/';
    private $args = array();
    private $responsed = false; 
    private $tfhb_promo_option = false; 
    private $error_message = ''; 
    private static $instance = null;

    private $months = [
        'January',  
        'February',
        'March',
        'April',
        'May',
        'June',
        'July',
        'August',
        'September',
        'October',
        'November',
        'December'
    ];
    private $plugins_existes = ['ins', 'tf', 'beaf', 'uacf7', 'uawpf'];
    
    /**
	 * Singleton instance
	 * @since 1.0.0
	 */
	public static function instance() {
		if ( self::$instance == null ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

    public function __construct() {
        
        if(in_array(date('F'), $this->months)){  

            $tfhb_promo__schedule_start_from = !empty(get_option( 'tfhb_promo__schedule_start_from' )) ? get_option( 'tfhb_promo__schedule_start_from' ) : 0;

            if($tfhb_promo__schedule_start_from == 0){
                // delete option
                delete_option('tfhb_promo__schedule_option');

            }elseif($tfhb_promo__schedule_start_from  != 0 && $tfhb_promo__schedule_start_from > time()){
                return;
            }  
            
            add_filter('cron_schedules', array($this, 'tfhb_custom_cron_interval'));
        
            if (!wp_next_scheduled('tfhb_promo__schedule')) {
                wp_schedule_event(time(), 'every_day', 'tfhb_promo__schedule');
            }
            
            add_action('tfhb_promo__schedule', array($this, 'tfhb_promo__schedule_callback'));
             
            if(get_option( 'tfhb_promo__schedule_option' )){
                $this->tfhb_promo_option = get_option( 'tfhb_promo__schedule_option' );
            }

            $tf_existes = get_option( 'tf_promo_notice_exists' );
            $dashboard_banner = isset($this->tfhb_promo_option['dashboard_banner']) ? $this->tfhb_promo_option['dashboard_banner'] : '';

            // Admin Notice  
            if( ! in_array($tf_existes, $this->plugins_existes) && is_array($dashboard_banner) && strtotime($dashboard_banner['end_date']) > time() && strtotime($dashboard_banner['start_date']) < time() && $dashboard_banner['enable_status'] == true){
            exit;
                add_action( 'admin_notices', array( $this, 'tf_promo_dashboard_admin_notice' ) );
                add_action( 'wp_ajax_tf_black_friday_notice_dismiss_callback', array($this, 'tf_black_friday_notice_dismiss_callback') );
            }
            
            // side Notice 
            $service_banner = isset($this->tfhb_promo_option['service_banner']) ? $this->tfhb_promo_option['service_banner'] : array();
            $promo_banner = isset($this->tfhb_promo_option['promo_banner']) ? $this->tfhb_promo_option['promo_banner'] : array();

            $current_day = date('l'); 
            if(isset($service_banner['enable_status']) && $service_banner['enable_status'] == true && in_array($current_day, $service_banner['display_days'])){ 
             
                $start_date = isset($service_banner['start_date']) ? $service_banner['start_date'] : '';
                $end_date = isset($service_banner['end_date']) ? $service_banner['end_date'] : '';
                $enable_side = isset($service_banner['enable_status']) ? $service_banner['enable_status'] : false;
            }else{  
                $start_date = isset($promo_banner['start_date']) ? $promo_banner['start_date'] : '';
                $end_date = isset($promo_banner['end_date']) ? $promo_banner['end_date'] : '';
                $enable_side = isset($promo_banner['enable_status']) ? $promo_banner['enable_status'] : false;
            }
            if(is_array($this->tfhb_promo_option) && strtotime($end_date) > time() && strtotime($start_date) < time()  && $enable_side == true){ 
                
                add_action( 'tfhb_sidebar_promo_banner', array( $this, 'tfhb_promo_side_notice_callback' ) );
                add_action( 'wp_ajax_tfhb_promo_side_notice_dismiss_callback', array($this, 'tfhb_promo_side_notice_dismiss_callback') ); 

            } 

            $tf_widget_exists = get_option('tf_promo_widget_exists');
            $dashboard_widget = isset($this->tfhb_promo_option['dashboard_widget']) ? $this->tfhb_promo_option['dashboard_widget'] : [];
            if (
                !in_array($tf_widget_exists, $this->plugins_existes) && 
                isset($dashboard_widget['enable_status']) && 
                $dashboard_widget['enable_status'] == true
            ) {
                // Mark that one Themefic widget already exists
                update_option('tf_promo_widget_exists', 'hydra');

                add_action('admin_init', [$this, 'init_dashboard_notice_widget']);
                add_action('wp_ajax_tfhb_dashboard_widget_dismiss', [$this, 'ajax_dashboard_widget_dismiss']);
            }

            register_deactivation_hook( TFHB_PATH . 'hydra-booking.php', array($this, 'tfhb_promo_notice_deactivation_hook') );
        }

        
       
    }

    public function init_dashboard_notice_widget() {

		// Ensure we’re only loading it on Dashboard
		add_action('wp_dashboard_setup', [$this, 'register_dashboard_notice_widget']);
	}
	
	public function register_dashboard_notice_widget() {

        $dashboard_banner = isset($this->tfhb_promo_option['dashboard_widget'])
        ? $this->tfhb_promo_option['dashboard_widget']
        : [];

        // Use API title if available, otherwise fallback
        $widget_title = !empty($dashboard_banner['title'])
        ? esc_html($dashboard_banner['title'])
        : __('Themefic Deals & Services', 'hydra-booking');


		wp_add_dashboard_widget(
			'tfhb_promo_dashboard_widget',
			$widget_title,
			[$this, 'render_dashboard_notice_widget'],
            null, null, 'side', 'high'
		);
	}

    public function render_dashboard_notice_widget() {
        $dashboard_widget = isset($this->tfhb_promo_option['dashboard_widget']) ? $this->tfhb_promo_option['dashboard_widget'] : [];

        if (empty($dashboard_widget) || empty($dashboard_widget['enable_status'])) {
            echo '<p>' . esc_html__('No active widget promotion.', 'hydra-booking') . '</p>';
            return;
        }

        $highlight = isset($dashboard_widget['highlight']) ? $dashboard_widget['highlight'] : [];
        $links     = isset($dashboard_widget['links']) ? $dashboard_widget['links'] : [];
        $footer    = isset($dashboard_widget['footer']) ? $dashboard_widget['footer'] : [];

        ?>
        <div class="tfhb-dashboard-widget" style="position:relative;">
            <?php if (!empty($dashboard_widget['dismiss_status'])) : ?>
                <button type="button" class="notice-dismiss tfhb-dashboard-dismiss" style="position:absolute; top:10px; right:10px;"></button>
            <?php endif; ?>

            <?php if (!empty($highlight)) : ?>
                <div class="highlight">
                    <?php if (!empty($highlight['before_image'])) : ?>
                        <img class="before-img" src="<?php echo esc_url($highlight['before_image']); ?>" style="max-width:100%; height:auto;" alt="">
                    <?php endif; ?>
                    <div class="content">
                        <?php if (!empty($highlight['title'])) : ?>
                        <p style="font-weight:600; margin:5px 0;"><?php echo esc_html($highlight['title']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($highlight['button_text']) && !empty($highlight['button_url'])) : ?>
                            <a href="<?php echo esc_url($highlight['button_url']); ?>" target="_blank" class="button button-primary"><?php echo esc_html($highlight['button_text']); ?></a>
                        <?php endif; ?>
                    </div>
                     <?php if (!empty($highlight['after_image'])) : ?>
                        <img class="after-img" src="<?php echo esc_url($highlight['after_image']); ?>" style="max-width:100%; height:auto;" alt="">
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($links)) : ?>
                <ul>
                    <?php foreach ($links as $link) : ?>
                        <li>
                            <a href="<?php echo esc_url($link['url']); ?>" target="_blank">
                                <?php if (!empty($link['tag'])) echo ' <span class="new-tag">' . esc_html($link['tag']) . '</span>'; ?>
                                <?php echo esc_html($link['text']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <?php if (!empty($footer)) : ?>
                <div class="footer" style="display:flex; justify-content:space-between; margin-top:15px; font-size:13px;">
                    <?php if (!empty($footer['left'])) : ?>
                        <a href="<?php echo esc_url($footer['left']['url']); ?>" target="_blank"><?php echo esc_html($footer['left']['text']); ?>
                        <svg width="13" height="13" viewBox="0 0 13 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12.5 0H6.66667V1.25H10.3667L5.39167 6.225L6.275 7.10833L11.25 2.13333V5.83333H12.5V0ZM1.66667 0.833333C1.22464 0.833333 0.800716 1.00893 0.488155 1.32149C0.175595 1.63405 0 2.05797 0 2.5V10.8333C0 11.2754 0.175595 11.6993 0.488155 12.0118C0.800716 12.3244 1.22464 12.5 1.66667 12.5H10C10.442 12.5 10.8659 12.3244 11.1785 12.0118C11.4911 11.6993 11.6667 11.2754 11.6667 10.8333V8.33333H10.4167V10.8333C10.4167 10.9438 10.3728 11.0498 10.2946 11.128C10.2165 11.2061 10.1105 11.25 10 11.25H1.66667C1.55616 11.25 1.45018 11.2061 1.37204 11.128C1.2939 11.0498 1.25 10.9438 1.25 10.8333V2.5C1.25 2.38949 1.2939 2.28351 1.37204 2.20537C1.45018 2.12723 1.55616 2.08333 1.66667 2.08333H4.16667V0.833333H1.66667Z" fill="#2271B1"/>
                        </svg>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($footer['right'])) : ?>
                        <a href="<?php echo esc_url($footer['right']['url']); ?>" target="_blank"><?php echo esc_html($footer['right']['text']); ?>
                        <svg width="13" height="13" viewBox="0 0 13 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12.5 0H6.66667V1.25H10.3667L5.39167 6.225L6.275 7.10833L11.25 2.13333V5.83333H12.5V0ZM1.66667 0.833333C1.22464 0.833333 0.800716 1.00893 0.488155 1.32149C0.175595 1.63405 0 2.05797 0 2.5V10.8333C0 11.2754 0.175595 11.6993 0.488155 12.0118C0.800716 12.3244 1.22464 12.5 1.66667 12.5H10C10.442 12.5 10.8659 12.3244 11.1785 12.0118C11.4911 11.6993 11.6667 11.2754 11.6667 10.8333V8.33333H10.4167V10.8333C10.4167 10.9438 10.3728 11.0498 10.2946 11.128C10.2165 11.2061 10.1105 11.25 10 11.25H1.66667C1.55616 11.25 1.45018 11.2061 1.37204 11.128C1.2939 11.0498 1.25 10.9438 1.25 10.8333V2.5C1.25 2.38949 1.2939 2.28351 1.37204 2.20537C1.45018 2.12723 1.55616 2.08333 1.66667 2.08333H4.16667V0.833333H1.66667Z" fill="#2271B1"/>
                        </svg>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <style>
            .tfhb-dashboard-widget {
                background: #fff;
                border-radius: 4px;
                padding: 0;
                font-family: Arial, sans-serif;
                font-size: 13px;
                color: #23282d;
            }

            .tfhb-dashboard-widget .header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                background: #f8f9fa;
                padding: 14px;
                border-bottom: 1px solid #ddd;
            }

            .tfhb-dashboard-widget .highlight {
                display: flex;
                align-items: center;
                justify-content: space-between;
                background-color: #fff;
                border-bottom:1px solid #ccd0d4; 
                padding:12px 0px; 
                margin-bottom:8px; 
                text-align:left;
                gap: 10px;
                padding-top: 0px;
            }

            .tfhb-dashboard-widget .highlight .before-img {
                width: 58px;
                height: 58px;
                flex: 0 0 58px;
            }
            .tfhb-dashboard-widget .highlight .after-img {
                width: 100px;
                height: 60px;
            }
            .tfhb-dashboard-widget .highlight .content {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                flex-direction: column;
                flex: 1;
                width: 100%;
            }
            .tfhb-dashboard-widget .highlight .content p{
                color: #1D2327;
                font-family: "Roboto", sans-serif;
                font-size: 13px;
                font-weight: 500;
                line-height: 19.6px;
            }
            .tfhb-dashboard-widget .highlight .content .button{
                height: 30px;
                color: #FFF;
                font-family: "Roboto", sans-serif;
                font-size: 13px;
                font-weight: 500;
                border-radius: 3px;
                background: #2271B1;
            }

            .tfhb-dashboard-widget ul li a {
                color: #2271B1;
                font-family: "Roboto", sans-serif;
                font-size: 13px;
                font-weight: 400;
                line-height: 120%;
            }

            .tfhb-dashboard-widget .new-tag {
                padding: 3px 6px;
                border-radius: 3px;
                background-color: #0A875A;
                font-family: "Roboto", sans-serif;
                font-size: 10.5px;
                font-weight: 500;
                line-height: 12.6px;
                line-height: 12.6px;
                color: #fff;
                text-transform: uppercase;
            }

            .tfhb-dashboard-widget .footer {
                display: flex;
                justify-content: space-between;
                align-items: center;
                border-top: 1px solid #ddd;
                padding: 10px 0px;
                background: #fff;
            }

            .tfhb-dashboard-widget .footer a {
                text-decoration: none;
                font-weight: 500;
                color: #2271B1;
                font-family: "Roboto", sans-serif;
                font-size: 13px;
                font-weight: 400;
                line-height: 15.6px;
            }

            .tfhb-dashboard-widget .footer a svg {
                padding-left: 4px;
            }

        </style>

        <script>
        jQuery(document).ready(function($){
            $(document).on('click', '.tfhb-dashboard-dismiss', function(){
                $(this).closest('.tfhb-dashboard-widget').fadeOut(300);
                $.post(ajaxurl, { action: 'tfhb_dashboard_widget_dismiss' });
            });
        });
        </script>
        <?php
    }



    public function ajax_dashboard_widget_dismiss() {
        // Dismiss control - 7 days
		update_option('tfhb_dashboard_widget_dismissed', time() + (86400 * 7));
		wp_die();
	}

    public function tfhb_get_api_response(){
        $query_params = array(
            'plugin' => 'hydra', 
        );
        $response = wp_remote_post($this->api_url, array(
            'body'    => json_encode($query_params),
            'headers' => array('Content-Type' => 'application/json'),
        )); 

        if (is_wp_error($response)) {
            // Handle API request error
            $this->responsed = false;
            $this->error_message = esc_html($response->get_error_message());
 
        } else {
            // API request successful, handle the response content
            $data = wp_remote_retrieve_body($response);
           
            $this->responsed = json_decode($data, true); 

            $tfhb_promo__schedule_option = get_option( 'tfhb_promo__schedule_option' ); 
            if(isset($tfhb_promo__schedule_option['notice_name']) && $tfhb_promo__schedule_option['notice_name'] != $this->responsed['notice_name']){ 
                // Unset the cookie variable in the current script
                update_option( 'tf_dismiss_admin_notice', 1);
                update_option( 'tfhb_dismiss_post_notice', 1); 
                update_option( 'tfhb_promo__schedule_start_from', time() + 43200);
            }elseif(empty($tfhb_promo__schedule_option)){
                update_option( 'tfhb_promo__schedule_start_from', time() + 43200);
            }
            update_option( 'tfhb_promo__schedule_option', $this->responsed);
            
        } 
    }

    // Define the custom interval
    public function tfhb_custom_cron_interval($schedules) {
        $schedules['every_day'] = array(
            'interval' => 86400, // Every 24 hours
            // 'interval' => 5, // Every 24 hours
            'display' => __('Every 24 hours')
        );
        return $schedules;
    }

    public function tfhb_promo__schedule_callback() {  

        $this->tfhb_get_api_response();

    }
 

    /**
     * Black Friday Deals 2023
     */
    
    public function tf_promo_dashboard_admin_notice(){ 
        
        $dashboard_banner = isset($this->tfhb_promo_option['dashboard_banner']) ? $this->tfhb_promo_option['dashboard_banner'] : '';
        $image_url = isset($dashboard_banner['banner_url']) ? esc_url($dashboard_banner['banner_url']) : '';
        $deal_link = isset($dashboard_banner['redirect_url']) ? esc_url($dashboard_banner['redirect_url']) : ''; 

        $tf_dismiss_admin_notice = get_option( 'tf_dismiss_admin_notice' );
        $get_current_screen = get_current_screen();  
        if(($tf_dismiss_admin_notice == 1  || time() >  $tf_dismiss_admin_notice ) && $get_current_screen->base == 'dashboard'   ){ 
          
         // if very fist time then set the dismiss for our other plugins
           update_option( 'tf_promo_notice_exists', 'hydra' );
           
           ?>
            <style> 
                .tf_black_friday_20222_admin_notice a:focus {
                    box-shadow: none;
                } 
                .tf_black_friday_20222_admin_notice {
                    padding: 7px;
                    position: relative;
                    z-index: 10;
                    max-width: 825px;
                } 
                .tf_black_friday_20222_admin_notice button:before {
                    color: #fff !important;
                }
                .tf_black_friday_20222_admin_notice button:hover::before {
                    color: #d63638 !important;
                }
            </style>
            <div class="notice notice-success tf_black_friday_20222_admin_notice"> 
                <a href="<?php echo esc_attr( $deal_link ); ?>" style="display: block; line-height: 0;" target="_blank" >
                    <img  style="width: 100%;" src="<?php echo esc_attr($image_url) ?>" alt="">
                </a> 
                <?php if( isset($dashboard_banner['dismiss_status']) && $dashboard_banner['dismiss_status'] == true): ?>
                <button type="button" class="notice-dismiss tf_black_friday_notice_dismiss"><span class="screen-reader-text"><?php echo __('Dismiss this notice.', 'hydra-booking' ) ?></span></button>
                <?php  endif; ?>
            </div>
            <script>
                jQuery(document).ready(function($) {
                    $(document).on('click', '.tf_black_friday_notice_dismiss', function( event ) {
                        jQuery('.tf_black_friday_20222_admin_notice').css('display', 'none')
                        data = {
                            action : 'tf_black_friday_notice_dismiss_callback',
                        };

                        $.ajax({
                            url: ajaxurl,
                            type: 'post',
                            data: data,
                            success: function (data) { ;
                            },
                            error: function (data) { 
                            }
                        });
                    });
                });
            </script>
        
        <?php 
        }
        
    } 


    public function tf_black_friday_notice_dismiss_callback() {  

        $tfhb_promo_option = get_option( 'tfhb_promo__schedule_option' );
	    $restart = isset($tfhb_promo_option['dashboard_banner']['restart']) && $tfhb_promo_option['dashboard_banner']['restart'] != false ? $tfhb_promo_option['dashboard_banner']['restart'] : false;
        if($restart == false){
            update_option( 'tf_dismiss_admin_notice', strtotime($tfhb_promo_option['end_date']) ); 
        }else{
            update_option( 'tf_dismiss_admin_notice', time() + (86400 * $restart) );  
        } 
		wp_die();
	}




    public function tfhb_promo_side_notice_callback(){
        $service_banner = isset($this->tfhb_promo_option['service_banner']) ? $this->tfhb_promo_option['service_banner'] : array();
        $promo_banner = isset($this->tfhb_promo_option['promo_banner']) ? $this->tfhb_promo_option['promo_banner'] : array();
        

        $current_day = date('l'); 
        if( isset($service_banner['enable_status']) && $service_banner['enable_status'] == true && in_array($current_day, $service_banner['display_days'])){ 
           
            $image_url = esc_url($service_banner['banner_url']);
            $deal_link = esc_url($service_banner['redirect_url']);  
            $dismiss_status = $service_banner['dismiss_status'];
        }else{
            $image_url = esc_url($promo_banner['banner_url']);
            $deal_link = esc_url($promo_banner['redirect_url']); 
            $dismiss_status = $promo_banner['dismiss_status'];  
        }  
        $tfhb_dismiss_post_notice = get_option( 'tfhb_dismiss_post_notice' );
        ?> 
         <?php if($tfhb_dismiss_post_notice == 1  || time() >  $tfhb_dismiss_post_notice ): 
            
            ?>
           
            <div class="tfhb_promo_side_preview" style="text-align: center; overflow: hidden; margin-top: 15px; position: relative;">
                <a href="<?php echo esc_attr($deal_link); ?>" target="_blank" >
                    <img  style="width: 100%;" src="<?php echo esc_attr($image_url); ?>" alt="">
                </a>  
                <?php if( isset($dismiss_status) && $dismiss_status == true): ?>
                    <button type="button" class="notice-dismiss tfhb_promo_side_notice_dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
                <?php  endif; ?>
                
            </div>
            <script>
                jQuery(document).ready(function($) {
                    $(document).on('click', '.tfhb_promo_side_notice_dismiss', function( event ) { 
                        jQuery('.tfhb_promo_side_preview').css('display', 'none')
                        data = {
                            action : 'tfhb_promo_side_notice_dismiss_callback', 
                        };

                        $.ajax({
                            url: ajaxurl,
                            type: 'post',
                            data: data,
                            success: function (data) { ;
                            },
                            error: function (data) { 
                            }
                        });
                    });
                });
            </script>
            <?php 
         endif; ?>
        <?php
	}

    public  function tfhb_promo_side_notice_dismiss_callback() {   
        $tfhb_promo_option = get_option( 'tfhb_promo__schedule_option' );
        $start_date = isset($tfhb_promo_option['start_date']) ? strtotime($tfhb_promo_option['start_date']) : time();
        $restart = isset($tfhb_promo_option['side_restart']) && $tfhb_promo_option['side_restart'] != false ? $tfhb_promo_option['side_restart'] : 5;
        update_option( 'tfhb_dismiss_post_notice', time() + (86400 * $restart) );  
        wp_die();
    }

     // Deactivation Hook
     public function tfhb_promo_notice_deactivation_hook() {
        wp_clear_scheduled_hook('tfhb_promo__schedule'); 

        delete_option('tfhb_promo__schedule_option');
        delete_option('tf_promo_notice_exists');
        delete_option('tfhb_promo__schedule_start_from');
        delete_option('tf_promo_widget_exists');
    }
 
}