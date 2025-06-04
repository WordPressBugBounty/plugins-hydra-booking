 
<?php
// exit
if ( ! defined( 'ABSPATH' ) ) { exit; }
use HydraBooking\DB\Meeting;
use HydraBooking\Admin\Controller\TransStrings;
$page_id = get_queried_object_id();
$meeting = new Meeting();
$query = array( );
// meeting_category  
$query[] = array('meeting_category', '=', $page_id); 
$meetings = $meeting->getAll( $query, 'id', 'DESC');


get_header();
?>
<!-- Loade theme class -->

<div class="tfhb-meeting-archive">
    <div class="tfhb-category-list">
        <div class="tfhb-category-list__heading">
            <h2><?php echo esc_html( __('Category: ', 'hydra-booking') );?> <?php  single_cat_title(); ?></h2>
          
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
                    $price = !empty($meeting->meeting_price) ? $meeting->meeting_price : esc_html(__('Free', 'hydra_booking'));
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
                            <a href="<?php echo esc_url($permalink) ?>"><?php echo esc_html($meeting->title) ?></a>
                        </h3>
                        <!-- <p><?php echo esc_html($meeting->description) ?></p> -->
                        <div class="tfhb-meeting-list__wrap__items__wrap__content__tags"> 
                            <span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                <?php echo esc_html($meeting->host_first_name) ?> <?php echo esc_html($meeting->host_last_name) ?>
                            </span> 
                            <!-- <span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-tags"><path d="m15 5 6.3 6.3a2.4 2.4 0 0 1 0 3.4L17 19"/><path d="M9.586 5.586A2 2 0 0 0 8.172 5H3a1 1 0 0 0-1 1v5.172a2 2 0 0 0 .586 1.414L8.29 18.29a2.426 2.426 0 0 0 3.42 0l3.58-3.58a2.426 2.426 0 0 0 0-3.42z"/><circle cx="6.5" cy="9.5" r=".5" fill="currentColor"/></svg>
                                <a href="<?php // echo esc_url($terms_archive_url); ?>"><?php echo esc_html($terms->name) ?>  </a>  
                            </span>  -->
                            <span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clock"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>    
                                   <?php echo esc_html(TransStrings::tfhbTranslateNumber($meeting->duration)) ?> <?php echo esc_html__('minutes', 'hydra_booking')?>
                            </span>
                            <!-- <span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-presentation"><path d="M2 3h20"/><path d="M21 3v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V3"/><path d="m7 21 5-5 5 5"/></svg> 
                                <?php // echo esc_html($meeting->meeting_type) ?>
                            </span> -->
                            <span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-banknote"><rect width="20" height="12" x="2" y="6" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01M18 12h.01"/></svg> 
                                  <?php echo esc_html(TransStrings::tfhbTranslateNumber($meeting->price)) ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="tfhb-meeting-list__wrap__items__actions tfhb-aling">
                    <a href="<?php echo esc_url($permalink) ?>" class="tfhb-btn secondary-btn"><?php echo esc_html__('Select', 'hydra_booking')?></a>
                </div>
            </div>

            <?php endforeach; else: ?>
                <div class="tfhb-meeting-list__wrap__no-found">
                    <p><?php esc_html__('No meetings found.', 'hydra_booking')?></p>
                </div>
            <?php endif;?>

        </div>
    </div>
</div>


<?php get_footer(); ?>
