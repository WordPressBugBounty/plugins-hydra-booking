<?php
namespace HydraBooking\Hooks;

class Mailer {

 
	public static function send( $to, $subject, $body, $headers = array(), $attachments = array() ) {
 
		
		// Clean the subject line - remove HTML tags and decode entities
		$subject = strip_tags($subject);
		$subject = html_entity_decode($subject, ENT_QUOTES, 'UTF-8');

		// Normalize headers to array (some callers pass a string)
		if ( is_string( $headers ) ) {
			$headers = preg_split( '/\r\n|\r|\n/', $headers );
		}
		$headers = array_values( array_filter( (array) $headers ) );

		// Normalize attachments
		$attachments = is_array( $attachments ) ? $attachments : array_filter( array( $attachments ) );
		
		// Ensure proper encoding
		$body = mb_convert_encoding($body, 'UTF-8', 'auto');
		
		// Set proper headers
		$has_content_type = false;
		foreach ( $headers as $header ) {
			if ( stripos( $header, 'Content-Type:' ) === 0 ) {
				$has_content_type = true;
				break;
			}
		}
		if ( ! $has_content_type ) {
			$headers[] = 'Content-Type: text/html; charset=UTF-8';
		}
		
		// Send email
		$result = wp_mail($to, $subject, $body, $headers, $attachments);
		 
		
		return $result;
	}

	public static function mail_body_template( $data = array() ) {
		$defaults = array(
			'recipient_name' => 'Web Admin',
			'title'          => "A new enquiry has been submitted through Hydra Booking.",
			'subtitle'       => '',
			'body_content'   => '',
			'footer_text'    => 'This is an automated notification from Hydra Booking.',
			'brand_name'     => '',
			'logo_url'       => '',
		);

		$data = wp_parse_args( $data, $defaults );

		$recipient_name = esc_html( $data['recipient_name'] );
		$title          = esc_html( $data['title'] );
		$subtitle       = esc_html( $data['subtitle'] );
		$footer_text    = esc_html( $data['footer_text'] );
		$brand_name     = esc_html( $data['brand_name'] );
		$logo_url       = esc_url( $data['logo_url'] );

		$body_content_raw = isset( $data['body_content'] ) ? (string) $data['body_content'] : '';
		$allowed_html     = wp_kses_allowed_html( 'post' );

		if ( ! isset( $allowed_html['a'] ) ) {
			$allowed_html['a'] = array();
		}
		$allowed_html['a']['style']  = true;
		$allowed_html['a']['target'] = true;

		if ( ! isset( $allowed_html['p'] ) ) {
			$allowed_html['p'] = array();
		}
		$allowed_html['p']['style'] = true;

		$body_content = wp_kses( $body_content_raw, $allowed_html );
		if ( trim( wp_strip_all_tags( $body_content ) ) === trim( $body_content ) ) {
			$body_content = nl2br( esc_html( $body_content ) );
		}

		$logo_html = ! empty( $logo_url )
			? '<img src="' . $logo_url . '" alt="' . $brand_name . '" style="max-width:190px; height:auto; display:block; margin:0 auto 20px;" />'
			: '<h2 style="margin:0 0 20px; text-align:center; font-size:30px; line-height:1.2; color:#1f2937;">' . $brand_name . '</h2>';

		$body = '
		<!doctype html>
		<html>
		<head>
			<meta charset="UTF-8" />
			<meta name="viewport" content="width=device-width, initial-scale=1.0" />
			<title>' . esc_html__( 'Enquiry Notification', 'hydra-booking' ) . '</title>
		</head>
		<body style="margin:0; padding:40px 16px; background:#eef0f5; font-family:Arial, Helvetica, sans-serif; color:#111827;">
			<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="max-width:760px; margin:0 auto; background:#f8f9fc; border-radius:10px;">
				<tr>
					<td style="padding:30px;">
						<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background:#ffffff; border-radius:8px;">
							<tr>
								<td style="padding:28px 28px 20px;">
									' . $logo_html . '
									<p style="margin:0 0 6px; font-size:16px; line-height:1.35; color:#111827;"><strong>' . $recipient_name . ',</strong></p>
									<h5 style="margin:0; font-size:16px; line-height:1.55; color:#374151;">' . $title . '</h5>
									'.(!empty($subtitle) ? '<p style="margin:0; font-size:16px; line-height:1.55; color:#374151;">' . $subtitle . '</p>' : '').'

									' . ( ! empty( $body_content ) ? '<div style="margin:20px 0; padding:15px; background:#f1f5f9; border-left:4px solid #3b82f6; font-size:15px; line-height:1.5; color:#111827;">' . $body_content . '</div>' : '' ) . '
									
									<p style="margin:26px 0 0; text-align:center; font-size:14px; color:#6b7280;">' . $footer_text . '</p>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</body>
		</html>';

		return $body;
	}
}
