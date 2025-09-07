<?php
namespace HydraBooking\Hooks;

class Mailer {

 
	public static function send( $to, $subject, $body, $headers = array(), $attachments = array() ) {
 
		
		// Clean the subject line - remove HTML tags and decode entities
		$subject = strip_tags($subject);
		$subject = html_entity_decode($subject, ENT_QUOTES, 'UTF-8');
		
		// Ensure proper encoding
		$body = mb_convert_encoding($body, 'UTF-8', 'auto');
		
		// Set proper headers
		$headers[] = 'Content-Type: text/html; charset=UTF-8';
		$headers[] = 'Content-Transfer-Encoding: 8bit';
		
		// Send email
		$result = wp_mail($to, $subject, $body, $headers, $attachments);
		 
		
		return $result;
	}
}
