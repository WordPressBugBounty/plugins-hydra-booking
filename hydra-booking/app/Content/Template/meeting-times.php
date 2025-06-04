<?php
defined( 'ABSPATH' ) || exit;

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://hydrabooking.com
 * @since      1.0.0
 *
 * @Template Template for Meeting Times
 *
 * @package    HydraBooking
 * @subpackage HydraBooking/app
 */

 use HydraBooking\Admin\Controller\DateTimeController;

$meeting          = isset( $args['meeting'] ) ? $args['meeting'] : array();
$host 		   = isset( $args['host'] ) ? $args['host'] : array();
$general_settings = isset( $args['general_settings'] ) ? $args['general_settings'] : array();
$time_format      = isset( $general_settings['time_format'] ) && ! empty( $general_settings['time_format'] ) ? $general_settings['time_format'] : '12';



// Selecte suld be current date 
$selected_date        = gmdate( 'Y-m-d' );
$meeting_id           = isset($meeting['id']) ? $meeting['id'] : 0;

$selected_timezone =  isset($meeting['availability_custom']['time_zone']) && !empty($meeting['availability_custom']['time_zone']) ? $meeting['availability_custom']['time_zone'] : 'UTC';
if ( 'settings' === $meeting['availability_type'] ) {
	$_tfhb_availability_settings = get_user_meta( $meeting['user_id'], '_tfhb_host', true );
	if(isset($_tfhb_availability_settings['availability_type']) && $_tfhb_availability_settings['availability_type'] === 'settings' ){
		// Get Global Settings
		$_tfhb_availability_settings_global = get_option( '_tfhb_availability_settings' ); 
		
		$key = array_search( $meeting['availability_id'], array_column( $_tfhb_availability_settings_global, 'id' ) );
		
		if ( in_array( $key, array_keys( $_tfhb_availability_settings_global ) ) ) {
		
			$selected_timezone = $_tfhb_availability_settings_global[ $key ]['time_zone']; 
		}

	}elseif (isset($_tfhb_availability_settings['availability']) &&  in_array( $meeting['availability_id'], array_keys( $_tfhb_availability_settings['availability'] ) ) ) {
		 
		$selected_timezone = $_tfhb_availability_settings['availability'][ $meeting['availability_id'] ]['time_zone'];
	}
	
}

// $selected_timezone = isset( $booking_data->attendee_time_zone ) ? $booking_data->attendee_time_zone : $selected_timezone;

$date_time = new DateTimeController( $selected_timezone );
$data      = $date_time->getAvailableTimeData( $meeting_id, $selected_date, $selected_timezone, $time_format );


?> 
<div class="tfhb-meeting-times">

	<?php
		// Hook for before Times
		do_action( 'hydra_booking/before_meeting_time' );

	?>
	<div class="tfhb-timezone-tabs-warp">
		<div class="tfhb-timezone-tabs">
			<ul>
				<li class="<?php echo $time_format == '12' ? 'active' : ''; ?>">
					<label for="tfhb_time_format_12" for=""><?php echo esc_html( __( '12h', 'hydra-booking' ) ); ?>
						<input id="tfhb_time_format_12" type="radio" <?php echo $time_format == '12' ? 'checked' : ''; ?>  name="tfhb_time_format" value="12">
					</label> 
				</li>
				<li class="<?php echo $time_format == '24' ? 'active' : ''; ?>">
					<label for="tfhb_time_format_24" for=""><?php echo esc_html( __( '24h', 'hydra-booking' ) ); ?>
						<input id="tfhb_time_format_24" type="radio" <?php echo $time_format == '24' ? 'checked' : ''; ?> name="tfhb_time_format" value="24">
					</label>
				</li>
			</ul>
		</div>
	</div>
	<h3 class="tfhb-select-date"> </h3>

	<div class="tfhb-available-times tfhb-scrollbar">
		<ul>
			<?php
			if ( ! empty( $data ) ) {
				foreach ( $data as $time ) {
					?>
					<li class="tfhb-flexbox"> <span class="time" data-time-start="<?php echo esc_attr($time['start']) ?>" data-time-end="<?php echo esc_attr($time['end']) ?>"><?php echo esc_attr($time['start']) ?></span> </li>
					<?php
				}
			}else{
				?>
					<li class="tfhb-flexbox"><?php echo esc_html( __( 'No time slots are currently available.', 'hydra-booking' ) ); ?> </li>
				<?php
			}
			?>
			
		</ul>
	</div>

	<?php
		// Hook for After Times
		do_action( 'hydra_booking/after_meeting_time' );

	?>
</div>
