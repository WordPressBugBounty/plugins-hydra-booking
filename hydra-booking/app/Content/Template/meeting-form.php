<?php
defined( 'ABSPATH' ) || exit;

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://hydrabooking.com
 * @since      1.0.0
 *
 * @Template Template for Form
 *
 * @package    HydraBooking
 * @subpackage HydraBooking/app
 */

use HydraBooking\Admin\Controller\TransStrings;
$meeting             = isset( $args['meeting'] ) ? $args['meeting'] : array();
$questions           = isset( $meeting['questions'] ) ? $meeting['questions'] : array();
$questions_type      = isset( $meeting['questions_type'] ) ? $meeting['questions_type'] : 'custom';
$questions_form_type = isset( $meeting['questions_form_type'] ) ? $meeting['questions_form_type'] : '';
$questions_form      = isset( $meeting['questions_form'] ) ? $meeting['questions_form'] : '';
$booking_data        = isset( $args['booking_data'] ) ? $args['booking_data'] : array();

// Integration Settings
$_tfhb_integration_settings = get_option( '_tfhb_integration_settings' );
$tfhb_paypal = isset( $_tfhb_integration_settings['paypal'] ) ? $_tfhb_integration_settings['paypal'] : array(); 
$tfhb_stripe = isset( $_tfhb_integration_settings['stripe'] ) ? $_tfhb_integration_settings['stripe'] : array(); 

?> 
<div class="tfhb-meeting-booking-form" style="display:none">
	<?php
		// Hook for Before Form
		do_action( 'hydra_booking/before_meeting_form' );

	?>

	<div class="tfhb-back-btn tfhb-flexbox tfhb-gap-8">
		<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
		<path d="M9.99935 15.8334L4.16602 10L9.99935 4.16669" stroke="#F62881" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
		<path d="M15.8327 10H4.16602" stroke="#F62881" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
		</svg>
		<h3><?php echo esc_html__( 'Details', 'hydra-booking' ); ?></h3>
	</div>
	<div class="tfhb-notice notice-error" style="display:none;"> 
	</div>

	<div class="tfhb-forms tfhb-flexbox">
		
		<?php

		if ( $questions_type != 'custom' ) {
			if ( $questions_form_type == 'wpcf7' ) {
				echo do_shortcode( '[contact-form-7 id="' . $questions_form . '"]' );

			} elseif ( $questions_form_type == 'fluent-forms' ) {
					echo do_shortcode( '[fluentform id="' . $questions_form . '"]' );
			} elseif ( $questions_form_type == 'forminator' ) {
				echo do_shortcode( '[forminator_form id="' . $questions_form . '"]' );
			} elseif ( $questions_form_type == 'forminator' ) {
					echo do_shortcode( '[forminator_form id="' . $questions_form . '"]' );
			}
			
			if(isset($tfhb_paypal['status']) && $tfhb_paypal['status'] == 1 ):
			?> 
				<div class="tfhb-paypal-button-container"></div>
			<?php
				endif;
				if(isset($tfhb_stripe['status']) && $tfhb_stripe['status'] == 1 ):
			?>
			<div class="tfhb-stripe-button-container"></div>
			<?php
			endif;

		} else {
			echo '<form  method="post" action="" class="tfhb-meeting-form ajax-submit"  enctype="multipart/form-data">';
			if ( is_array( $questions ) && ! empty( $questions ) ) {
				$disable = ! empty( $booking_data ) ? 'disabled' : '';
				$others_info = isset( $booking_data->others_info ) ? $booking_data->others_info : '';
				$others_info = !is_array($others_info) ? json_decode( $others_info, true ) : $others_info; 
				foreach ( $questions as $key => $question ) :  
					if(isset($question['enable']) && $question['enable'] == 0){ 
						continue;
					}

					// this is a temporay fix it will be removed in version 2.0.0 or higher version 
					if(!isset($question['name']) || empty($question['name'])){
						$baseName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $question['label']));
						$question['name']  = $baseName; 
					}
					// ******** end of fix

					$name = 1 >= $key ? $question['name'] : 'question[' . $question['name'] . ']'; 
					$placehoder = isset( $question['placeholder'] )? $question['placeholder'] : ''; 
					$label = ucfirst($question['label']);  

					if( $question['name'] == 'Address'){
						$name = 'address';
					}
					
					if ( $name == 'email' ) {
						$value = ! empty( $booking_data ) ? $booking_data->email : '';
					} elseif ( $name == 'name' ) {
						$value = ! empty( $booking_data ) ? $booking_data->attendee_name : '';
					} elseif ( $name == 'Address' || $name == 'address'  ) {
						$value = ! empty( $booking_data ) ? $booking_data->address : '';
					}elseif ( isset( $others_info[ $question['name'] ] ) ) { 
						$value = $others_info[ $question['name']];
					} else {
							$value = '';
						}
				
					
					if ( empty( $question['type'] ) ) {
						continue;
					}

					if($question['type'] == 'phone'){
						$question['type'] ='tel';
					}
 
					$required_star = $question['required'] == 1 ? '*' : '';
					$required      = $question['required'] == 1 ? 'required' : '';

					echo '<div class="tfhb-single-form">
                                <label for="' . esc_attr($name) . '">' . esc_attr(TransStrings::tfhbTranslate($label)) . ' ' . esc_attr($required_star) . '</label>';
					if ( $question['type'] == 'select' ) {

						echo '<select name="' . esc_attr($name) . '" id="' . esc_attr($name) . '" ' . esc_attr($disable) . ' ' . esc_attr($required) . '>';
						foreach ( $question['options'] as $option ) {
							echo '<option value="' . esc_attr($option) . '">' . esc_attr($option) . '</option>';
						}
						echo '</select>';

					} elseif ( $question['type'] == 'textarea' ) {

						echo '<textarea name="' . esc_attr($name) . '" id="' . esc_attr($name) . '" ' . esc_attr($disable) . ' ' . esc_attr($required) . ' placeholder="' . esc_attr(TransStrings::tfhbTranslate($placehoder)) . '">' . esc_html($value) . '</textarea>';

					} elseif ( $question['type'] == 'checkbox' ) { 
						echo '<div class="tfhb-checkbox-group">';
						foreach ( $question['options'] as $key => $option ) { 
							echo '<label class="tfhb-field-'. esc_attr($question['type']) .'" for="' . esc_attr($name)  .'_'.$key.'">
                                            <input name="' . esc_attr($name) . '" value="'.esc_attr($option).'"  id="' . esc_attr($name)  .'_'.$key.'"  type="' . esc_attr($question['type']) . '" ' . esc_attr($disable) . ' >
                                            <span class="checkmark"></span> ' . esc_attr($option) . '
                                        </label>';
						}
						echo '</div>';
						

					}elseif ( $question['type'] == 'radio' ) { 
						echo '<div class="tfhb-radio-group">';
						foreach ( $question['options'] as $key => $option ) {  
							echo '<label  class="tfhb-field-'. esc_attr($question['type']) .'" for="' . esc_attr($name) .'_'.$key.'">
										<input name="' . esc_attr($name) . '" value="'.esc_attr($option).'"  id="' . esc_attr($name)  .'_'.$key.'"  type="' . esc_attr($question['type']) . '" ' . esc_attr($disable) . ' ' . esc_attr($required) . '>
										<span class="checkmark"></span> ' . esc_attr($option) . '
									</label>';
						}
						echo '</div>';
						

					}  else {

						echo '<input name="' . esc_attr($name) . '" id="' . esc_attr($name) . '"  value="' . esc_attr($value) . '" type="' . esc_attr($question['type']) . '" ' . esc_attr($required) . ' ' . esc_attr($disable) . ' placeholder="' . esc_attr(TransStrings::tfhbTranslate($placehoder)) . '">';
					}
							echo '</div>';

					endforeach;
			}



			?>
			
			<?php if ( ! empty( $booking_data ) ) : ?>
				
				<div class="tfhb-forms">
					<div  class="tfhb-single-form">
						<label for="attendee_name"> <?php echo esc_html__( 'Reason for Reschedule', 'hydra-booking' ); ?> </label>
						<br>

						<textarea name="reason" required id="reason"></textarea>
					</div> 
				</div> 
			<?php endif;
			
		
			?> 
				<div class="tfhb-confirmation-button tfhb-mt-32">
					<button class="tfhb-flexbox tfhb-gap-8 tfhb-booking-submit">
					<?php echo ! empty( $booking_data ) ? 'Reschedule' : 'Confirm'; ?>  
						<img src="<?php echo esc_url(TFHB_URL . 'assets/app/images/arrow-right.svg'); ?>" alt="arrow"> 
					</button>
				</div>
			<?php
				if(isset($tfhb_paypal['status']) && $tfhb_paypal['status'] == 1 ):
			?> 
				<div class="tfhb-paypal-button-container"></div>
			<?php
				endif;
				if(isset($tfhb_stripe['status']) && $tfhb_stripe['status'] == 1 ):
			?>
			<div class="tfhb-stripe-button-container"></div>
			<?php
			endif;
			echo '</form>';
		}
		?>
 
	</div>

	<?php
		// Hook for After confirmation
		do_action( 'hydra_booking/after_meeting_form' );

	?>
</div>
