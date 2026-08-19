<?php
namespace HydraBooking\App\Shortcode;

// exit
if ( ! defined( 'ABSPATH' ) ) { exit; }

// Use Namespace 
use HydraBooking\DB\Meeting;
use HydraBooking\DB\Host;
use HydraBooking\Admin\Controller\TransStrings;
/**
 * Signup Class
 * 
 * @author Sydur Rahman
 */
class ShortcodeBuilder { 

    /**
     * Constructor
     * 
     * @author Sydur Rahman 
     */
    public function __construct() { 
       
        // Add Shortcode Meeting Shortcode 
        add_shortcode( 'tfhb_meetings', array( $this, 'tfhb_meetings_callback' ) );

        // Host Shortcode
        add_shortcode('tfhb_hosts', array($this, 'tfhb_hosts_callback') );

        // Meeting categories 
        add_shortcode('tfhb_categories', array($this, 'tfhb_categories_callback') );

        
 
    }

    /*
     * Meeting Shortcode Callback
     * 
     * /
     * @author Sydur Rahman 
     * */

    public function tfhb_meetings_callback($atts) { 
        $atts = shortcode_atts([
            'title'     => '',
            'subtitle'  => '',
            'category'  => 'all', // Comma-separated category IDs
            'hosts'     => 'all', // Comma-separated host IDs
            'sort_by'   => 'id',
            'order_by'  => 'DESC',
            'limit'     => '10',
        ], $atts, 'tfhb_meetings');
        
        // Whitelisting
        $allowed_sort_by = ['id', 'title', 'created_at']; // customize as needed
        $allowed_order_by = ['ASC', 'DESC'];
        
        $sort_by = in_array($atts['sort_by'], $allowed_sort_by, true) ? $atts['sort_by'] : 'id';
        $order_by = in_array(strtoupper($atts['order_by']), $allowed_order_by, true) ? strtoupper($atts['order_by']) : 'DESC';
        
         // Validate sort_by
        if (!in_array($sort_by, $allowed_sort_by, true)) {
            return '<p class="tfhb-notice notice-error"><strong>' . esc_html__('Error:', 'hydra-booking') . '</strong> ' 
                . esc_html__('Invalid sorting parameter.', 'hydra-booking') . '<br>'
                . '' . esc_html__('Accepted sort_by values:', 'hydra-booking') . ' <code>' 
                . esc_html(implode(', ', $allowed_sort_by)) . '</code></p>';
        }

        // Validate order_by
        if (!in_array($order_by, $allowed_order_by, true)) {
            return '<p class="tfhb-notice notice-error"><strong>' . esc_html__('Error:', 'hydra-booking') . '</strong> ' 
                . esc_html__('Invalid order direction.', 'hydra-booking') . '<br>'
                . '' . esc_html__('Accepted order_by values:', 'hydra-booking') . ' <code>' 
                . esc_html(implode(', ', $allowed_order_by)) . '</code></p>';
        }

        if (!in_array($sort_by, $allowed_sort_by, true)) {
            $sort_by = 'id';
        }
        if (!in_array($order_by, $allowed_order_by, true)) {
            $order_by = 'DESC';
        }

        $limit = intval($atts['limit']);
        if ($limit <= 0) {
            $limit = 10;
        }
        
        // Build secure query
        $query = [];
        
        // meeting_category
        if ($atts['category'] !== 'all' && !empty($atts['category'])) {
            $category_ids = array_map('intval', explode(',', $atts['category']));
            $query[] = ['meeting_category', 'IN', $category_ids];
        }
        
        // meeting_host
        if ($atts['hosts'] !== 'all' && !empty($atts['hosts'])) {
            $host_ids = array_map('intval', explode(',', $atts['hosts']));
            $query[] = ['host_id', 'IN', $host_ids];
        }
        
        // Fetch meetings securely
        $meeting = new Meeting();
        $meetings = $meeting->getAll($query, $sort_by, $order_by, $limit);
        
        ob_start();
        ?>
        <div class="tfhb-meeting-list">
            <div class="tfhb-meeting-list__heading">

                <h2><?php echo esc_html( $atts['title'] );?></h2>
                <p><?php echo esc_html( $atts['subtitle'] );?></p>
            </div>
            <div class="tfhb-meeting-list__wrap">
                <?php 
                    if(count($meetings) > 0):
                        foreach ($meetings as $meeting) : 
                        // Get  all treams details based on trames id 
                        $meeting_category = $meeting->meeting_category; // meeting_category is a trems id 
                        $terms = get_term( $meeting_category ); 
                        $terms_archive_url = get_term_link($terms); 
                        $permalink = get_permalink($meeting->post_id);
                        // tfhb_print_r($terms);
                        $price = !empty($meeting->meeting_price) ? $meeting->meeting_price : esc_html(__('Free', 'hydra-booking'));
                ?>
                <div class="tfhb-meeting-list__wrap__items">
                   
                    <div class="tfhb-meeting-list__wrap__items__wrap">
                        <?php if($meeting->host_featured_image != ''): ?>
                        <div class="tfhb-meeting-list__wrap__items__wrap__img">
                            <img src="<?php echo esc_url($meeting->host_featured_image); ?>" alt="">
                        </div>
                        <?php endif; ?>
                        <div class="tfhb-meeting-list__wrap__items__wrap__content">
                            <h3>
                                <a href="<?php echo esc_url($permalink) ?>"><?php echo $meeting->title ? esc_html($meeting->title) : esc_html(__('No Title', 'hydra-booking')); ?></a>
                            </h3>
                            <!-- <p><?php echo esc_html($meeting->description) ?></p> -->
                            <div class="tfhb-meeting-list__wrap__items__wrap__content__tags"> 
                                <span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                    <?php echo esc_html($meeting->host_first_name) ?> <?php echo esc_html($meeting->host_last_name) ?>
                                </span> 
                                
                                <span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clock"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>    
                                    <?php echo esc_html(TransStrings::tfhbTranslateNumber($meeting->duration)) ?> <?php echo esc_html(__('minutes', 'hydra-booking')) ?>
                                </span>
                               
                                <span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-banknote"><rect width="20" height="12" x="2" y="6" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01M18 12h.01"/></svg> 
                                    <?php echo esc_html($price) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tfhb-meeting-list__wrap__items__actions tfhb-aling">
                        <a href="<?php echo esc_url($permalink) ?>" class="tfhb-btn secondary-btn"><?php echo esc_html(__('Select', 'hydra-booking')) ?></a>
                    </div>
                </div>

                <?php endforeach; else: ?>
                    <div class="tfhb-meeting-list__wrap__no-found">
                        <p><?php esc_html_e('No meetings found.', 'hydra-booking')?></p>
                    </div>
                <?php endif;?>

            </div>
        </div>
        <?php 
        $html = ob_get_clean();
        // tfhb_print_r($meetings);
 
        return $html;  // return the generated HTML for the shortcode

    }

    /**
     * Hosts Shortcode Generator
     * 
     * @author Sydur 
     * 
     * */

    public function tfhb_hosts_callback($atts){

        $atts = shortcode_atts([
            'title'        => '',
            'subtitle'    => '', 
            'hosts'        => 'all', // Comma-separated host IDs
            'sort_by'      => 'id',
            'order_by'     => 'DESC',
            'limit' => '10',
        ], $atts, 'tfhb_hosts');
 
        // Whitelist for sort_by and order_by
        $allowed_sort_columns = ['id', 'first_name', 'created_at']; // customize as needed
        $allowed_order_directions = ['ASC', 'DESC'];

        $sort_by = isset($atts['sort_by']) ? $atts['sort_by'] : 'id';
        $order_by = isset($atts['order_by']) ? strtoupper($atts['order_by']) : 'DESC';

        // Validate sort_by
        if (!in_array($sort_by, $allowed_sort_columns, true)) {
            return '<p class="tfhb-notice notice-error"><strong>' . esc_html__('Error:', 'hydra-booking') . '</strong> ' 
                . esc_html__('Invalid sorting parameter.', 'hydra-booking') . '<br>'
                . '' . esc_html__('Accepted sort_by values:', 'hydra-booking') . ' <code>' 
                . esc_html(implode(', ', $allowed_sort_columns)) . '</code></p>';
        }

        // Validate order_by
        if (!in_array($order_by, $allowed_order_directions, true)) {
            return '<p class="tfhb-notice notice-error"><strong>' . esc_html__('Error:', 'hydra-booking') . '</strong> ' 
                . esc_html__('Invalid order direction.', 'hydra-booking') . '<br>'
                . '' . esc_html__('Accepted order_by values:', 'hydra-booking') . ' <code>' 
                . esc_html(implode(', ', $allowed_order_directions)) . '</code></p>';
        }

        if (!in_array($sort_by, $allowed_sort_columns, true)) {
            $sort_by = 'id';
        }
        if (!in_array($order_by, $allowed_order_directions, true)) {
            $order_by = 'DESC';
        }

        // Prepare the query for hosts
        $host = new Host();
        $query = [];

        if ('all' != $atts['hosts'] && !empty($atts['hosts'])) {
            // Make sure hosts is a clean array of integers
            $host_ids = array_map('intval', explode(',', $atts['hosts']));
            $query[] = ['id', 'IN', $host_ids];
        }

        $limit = intval($atts['limit']) ?: 10;

        $hostData = $host->getAll($query, $sort_by, $order_by, $limit);

        ob_start();
        ?>
        <div class="tfhb-hosts-list">
            <div class="tfhb-hosts-list__heading">
                <h2><?php echo esc_html( $atts['title'] );?></h2>
                <p><?php echo esc_html( $atts['subtitle'] );?></p>
            </div>
            <div class="tfhb-hosts-list__wrap"> 
                <?php if(count($hostData) > 0):
                    foreach ($hostData as $host) : 
                        // get user data  
                        $nickname = get_user_meta($host->user_id, 'nickname', true); 
                        
                        $user_url = home_url('/tfhb-host/' . $nickname . '/');
       
                ?>
                <div class="tfhb-hosts-list__wrap__items"> 

                    <div class="tfhb-meeting-list__wrap__items__wrap">
                        <?php if($host->avatar): ?>
                        <div class="tfhb-meeting-list__wrap__items__wrap__img">
                            <img src="<?php echo esc_url($host->avatar)?>" alt="<?php echo esc_html($host->first_name.' '. $host->last_name)?>">
                        </div>
                        <?php endif; ?>
                        <div class="tfhb-meeting-list__wrap__items__wrap__content">
                            <h3></h3> 
                            <h3> <a href="<?php echo esc_url($user_url) ?>"><?php echo esc_html($host->first_name.' '. $host->last_name)?></a></h3>

                        </div>
                    </div>
                    
                    <div class="tfhb-meeting-list__wrap__items__actions tfhb-aling">
                        <a href="<?php echo esc_url(  $user_url ) ?>" class="tfhb-btn secondary-btn"><?php echo esc_html(__('Select', 'hydra-booking')) ?></a>
                    </div>

                    
                </div>

                <?php endforeach; else: ?>
                    <div class="tfhb-meeting-list__wrap__no-found">
                        <p><?php esc_html_e('No meetings found.', 'hydra-booking')?></p>
                    </div>
                <?php endif;?>

            </div>
        </div>
        <?php 
        $html = ob_get_clean(); 
 
        return $html;  // return the generated HTML for the shortcode


    }


    /**
     * Meeting Category Shortcode
     * 
     * @author Sydur Rahmanur <
     * 
     * */
    public function tfhb_categories_callback($atts){
        
        $atts = shortcode_atts([
            'title'     => '',
            'subtitle'  => '', 
            'sort_by'   => 'id', // Allowed: id or title
            'order_by'  => 'DESC',
            'limit'     => '10',
        ], $atts, 'tfhb_categories');
        
        // Whitelist acceptable values
        $allowed_sort_by = ['id', 'name', 'slug', 'count'];
        $allowed_order_by = ['ASC', 'DESC'];
        
        
        $sort_by = in_array($atts['sort_by'], $allowed_sort_by, true) ? $atts['sort_by'] : 'id';
        $order_by = in_array(strtoupper($atts['order_by']), $allowed_order_by, true) ? strtoupper($atts['order_by']) : 'DESC';

                 // Validate sort_by
        if (!in_array($sort_by, $allowed_sort_by, true)) {
            return '<p class="tfhb-notice notice-error"><strong>' . esc_html__('Error:', 'hydra-booking') . '</strong> ' 
                . esc_html__('Invalid sorting parameter.', 'hydra-booking') . '<br>'
                . '' . esc_html__('Accepted sort_by values:', 'hydra-booking') . ' <code>' 
                . esc_html(implode(', ', $allowed_sort_by)) . '</code></p>';
        }

        // Validate order_by
        if (!in_array($order_by, $allowed_order_by, true)) {
            return '<p class="tfhb-notice notice-error"><strong>' . esc_html__('Error:', 'hydra-booking') . '</strong> ' 
                . esc_html__('Invalid order direction.', 'hydra-booking') . '<br>'
                . '' . esc_html__('Accepted order_by values:', 'hydra-booking') . ' <code>' 
                . esc_html(implode(', ', $allowed_order_by)) . '</code></p>';
        }

        if (!in_array($sort_by, $allowed_sort_by, true)) {
            $sort_by = 'id';
        }
        if (!in_array($order_by, $allowed_order_by, true)) {
            $order_by = 'DESC';
        }

        $limit = intval($atts['limit']);
        if ($limit <= 0) $limit = 10;
        
        // Safe call to get_terms()
        $terms = get_terms([
            'taxonomy'   => 'meeting_category',
            'hide_empty' => false,
            'orderby'    => $sort_by,
            'order'      => $order_by,
            'number'     => $limit,
        ]);
        ob_start();
        ?>
        <div class="tfhb-category-list">
            <div class="tfhb-category-list__heading">
                <h2><?php echo esc_html( $atts['title'] );?></h2>
                <p><?php echo esc_html( $atts['subtitle'] );?></p>
            </div>
            <div class="tfhb-category-list__wrap"> 
                <?php if(count($terms) > 0):
                    foreach ($terms as $term) : 
                    // make  term link 
                    $terms_archive_url = get_term_link( $term, 'meeting_category' );
 
                ?>
                <div class="tfhb-category-list__wrap__items"> 
                    <div class="tfhb-meeting-list__wrap__items__wrap"> 
                        <div class="tfhb-meeting-list__wrap__items__wrap__content">
                            <h3> <a href="<?php echo esc_url($terms_archive_url) ?>"><?php echo esc_html($term->name) ?></a></h3>

                            <p><?php echo esc_html($term->description) ?></p>
                        </div>
                    </div>
                    
                    <div class="tfhb-meeting-list__wrap__items__actions tfhb-aling">
                        <a href="<?php echo esc_url($terms_archive_url) ?>" class="tfhb-btn secondary-btn"><?php echo esc_html(__('Select', 'hydra-booking')) ?></a>
                    </div>
                </div>

                <?php endforeach; else: ?>
                    <div class="tfhb-meeting-list__wrap__no-found">
                        <p><?php esc_html_e('No meetings found.', 'hydra-booking')?></p>
                    </div>
                <?php endif;?>

            </div>
        </div>
        <?php 
        $html = ob_get_clean();
 
        return $html;  // return the generated HTML for the shortcode


    }
 
 

}