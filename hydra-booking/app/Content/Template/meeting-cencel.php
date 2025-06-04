<?php

defined( 'ABSPATH' ) || exit;

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://hydrabooking.com
 * @since      1.0.0
 *
 * @Template Template for Meeting Cencel
 *
 * @package    HydraBooking
 * @subpackage HydraBooking/app
 */
get_header();

global $wp_query;


 
$data    = isset( $args['attendeeBooking'] ) ? $args['attendeeBooking'] : array(); 

// tfhb_print_r($data);
?>
<div class=" tfhb-booking-cencel tfhb-meeting-<?php echo esc_attr( $data->meeting_id ); ?>" data-calendar="<?php echo esc_attr( $data->meeting_id ); ?>">
	<form method="post" action="" class="tfhb-meeting-cencel-form ajax-submit"  enctype="multipart/form-data">
		<div class="tfhb-meeting-card tfhb-p-16">
			<div class="tfhb-meeting-confirmation">  

				<div class="tfhb-confirmation-seccess"> 
					<h3><?php echo esc_html( __( 'Your meeting has been', 'hydra-booking' ) ); ?> <?php echo esc_html( $data->status ); ?></h3>
					<p><?php echo esc_html( __( 'Please check your email for more information. Now you can reschedule or cancel booking from here.', 'hydra-booking' ) ); ?></p>
				</div>

				<div class="tfhb-meeting-hostinfo"> 
					<ul>
						<li class="tfhb-flexbox tfhb-gap-8">
							<div class="tfhb-icon">
								<img src="<?php echo esc_url(TFHB_URL . 'assets/app/images/user.svg'); ?>" alt="User">
							</div>
							<?php echo ! empty( $data->host_first_name ) ? '' . esc_html( $data->host_first_name ) . '  ' . esc_html( $data->host_last_name ) . '' : ''; ?>
							<span><?php echo esc_html( __( 'Host', 'hydra-booking' ) ); ?></span>
						</li>
						<li class="tfhb-flexbox tfhb-gap-8">
							<div class="tfhb-icon">
								<img src="<?php echo esc_url(TFHB_URL . 'assets/app/images/Meeting.svg'); ?>" alt="Meeting">
							</div>
							<!--date stored in this format  2024-05-24  9:00pm-9:45pm, Saturday, April 25 -->
							<?php

							$meeting_dates = explode( ',', $data->meeting_dates );

							$date_strings = '';
							foreach ( $meeting_dates as $key => $date ) {

								$date_strings .= gmdate( 'l, F j', strtotime( $date ) );
								$date_strings .= ', ';
							}

								echo ! empty( $data->start_time ) ? '' . esc_html( $data->start_time ) . ' - ' . esc_html( $data->end_time ) . ' ' . esc_html( $date_strings ) . '' : ''
							?>
						</li>
						<li class="tfhb-flexbox tfhb-gap-8">
							<div class="tfhb-icon">
								<img src="<?php echo esc_url(TFHB_URL . 'assets/app/images/location.svg'); ?>" alt="location">
							</div>
							<!-- Asia/Dhaka  -->
							<?php echo ! empty( $data->attendee_time_zone ) ? '' . esc_html( $data->attendee_time_zone ) . '' : ''; ?>

						</li>

						<!-- Meeting location -->
						<?php
						if ( ! empty( $data->meeting_locations ) ) {
							$locations = json_decode( $data->meeting_locations );
							foreach ( $locations as $key => $location ) {
								if ( empty( $location->location ) ) {
									continue;
								}
								echo '<li class="tfhb-flexbox tfhb-gap-8">
                                            <div class="tfhb-icon">
                                                <img src="' . esc_url( TFHB_URL . 'assets/app/images/location.svg' ) . '" alt="location">   
                                            </div> 
                                            ' . esc_html( $location->location ) . '
                                        </li>';
							}
						}
						?>
					</ul>
				</div>
 
				<?php if ( $data->status == 'canceled' ) : ?>
					<div class="tfhb-notice notice-error" > 
						<span><?php echo esc_html_( 'This meeting has been cancelled by the ', 'hydra-booking' ) . esc_attr($data->cancelled_by) . '.'; ?></span>
					</div>
				<?php else : ?>
				<div class="hidden-field"> 
					<input type="hidden" id="attendee_hash" name="attendee_hash" value="<?php echo esc_attr($data->hash); ?>"> 
				</div>  
				<div class="tfhb-forms" >
					<div  class="tfhb-single-form">
						<br>
						<label for="attendee_name"> <?php echo esc_html__( 'Reason for Cancellation', 'hydra-booking' ); ?> </label>
						<br>

						<textarea name="reason" required id="reason"></textarea>
						<input type="hidden" name="hash" value="<?php echo esc_attr($data->hash) ?>">
						<br>
						<br>
					</div> 

					<div class="tfhb-confirmation-button tfhb-flexbox tfhb-gap-8">
				   
						<button class="tfhb-flexbox tfhb-gap-8 tfhb-booking-submit">
							<?php echo esc_attr( 'Cancel Booking' ); ?>
							<img src="<?php echo esc_url(TFHB_URL . 'assets/app/images/arrow-right.svg'); ?>" alt="arrow"> 
						</button>
					</div>

				</div>
				<?php endif; ?>
				
			</div>
		</div>
	</form>
	
</div>
<?php



get_footer();

