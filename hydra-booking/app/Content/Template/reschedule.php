<?php
get_header(); 
use HydraBooking\DB\Meeting;
use HydraBooking\Admin\Controller\AuthController;

global $wp_query;



if ( isset( $wp_query->query_vars['hydra-booking'] ) ) {
	$meeting_id = intval( $wp_query->query_vars['meetingId'] );
	$hash       = esc_attr( $wp_query->query_vars['hash'] );
	$type       = esc_attr( $wp_query->query_vars['type'] );

	$meeting = new Meeting();
	$meeting = $meeting->get( $meeting_id );
	$attendee_can_reschedule = isset( $meeting->attendee_can_reschedule ) ? $meeting->attendee_can_reschedule : false;

// Get Current User 
	$auth = new AuthController();
	$userRole =  $auth->userRole();
	$user_id =  $auth->userID();
	 

	if($attendee_can_reschedule == false ){
		if(!in_array('administrator', haystack: $userRole) || (!in_array('tfhb_host', haystack: $userRole) && $user_id != $meeting->user_id)){
			echo '<div class="tfhb-single-meeting-section">';
			echo '<h2>'.__('You are not allowed to reschedule this meeting', 'hydra-booking').'</h2>';
			echo '</div>';
			return;
		}
	}
 

	//
	if ( ! empty( $meeting_id ) ) { ?>
	<div class="tfhb-single-meeting-section">
		<?php echo do_shortcode( '[hydra_booking id=' . $meeting_id . ' hash=' . $hash . ' type=' . $type . ' ]' ); ?>
	</div>
		<?php
	}
}

get_footer();
