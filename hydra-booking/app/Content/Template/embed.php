 
<?php 
defined( 'ABSPATH' ) || exit;

/**
 * Embed Template
 * 
 * @link       https://hydrabooking.com
 */

 wp_head();
 
 global $wp_query;
if ( isset( $wp_query->query_vars['hydra-booking'] ) ) {
	$meeting_id = intval( $wp_query->query_vars['meetingId'] ); 
	if ( ! empty( $meeting_id ) ) {
		?>
		<div class="tfhb-single-meeting-section tfhb-meeting-embed-section">
			<?php echo do_shortcode( '[hydra_booking id=' . $meeting_id . ']' ); ?>
		</div>
		<?php
	}
}
wp_footer();
?>
 