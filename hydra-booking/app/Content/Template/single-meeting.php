<?php defined( 'ABSPATH' ) || exit; ?>
<?php get_header('tfhb-meeting' ); ?>

<?php 
$meeting_id = get_post_meta( get_the_ID(), '__tfhb_meeting_id', true ); 
 

if ( ! empty( $meeting_id ) ) { ?>
<div class="tfhb-single-meeting-section">
	<?php echo do_shortcode( '[hydra_booking id=' . $meeting_id . ']' ); ?>
</div>

<?php } ?>

<?php get_footer( 'tfhb-meeting' ); ?>