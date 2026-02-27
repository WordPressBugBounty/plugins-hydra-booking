<?php
namespace HydraBooking\Services\Integrations\BookingBookmarks;
// exit
if ( ! defined( 'ABSPATH' ) ) { exit; }


use HydraBooking\DB\Attendees;
use HydraBooking\Services\Integrations\BookingBookmarks\BookingBookmarks; 
/**
 * 
 * BookingBookmarks
 */
class BookingBookmarks { 

	public function __construct( ) { 

	}

    public function GetBookingConfirmationUrl ($data){ 

        $confirmation = add_query_arg(
            array(
                'hydra-booking' => 'booking',
                'hash'          => $data->hash,
                'meetingId'    => $data->meeting_id,
                'type'          => 'confirmation',
            ),
            home_url()
        );
        return $confirmation;
    }

    public function GetBookingIcsUrl ($data){
        $confirmation = add_query_arg(
            array(
                'hydra-booking' => 'booking',
                'hash'          => $data->hash,
                'meetingId'    => $data->meeting_id,
                'type'          => 'download_ics',
            ),
            home_url()
        );
        return $confirmation;
    }
    public function getMeetingBookmarks($data ){ 
        $bookingTitle = $data->meeting_title . ' Between ' . $data->host_first_name . ' ' . $data->host_last_name . ' and ' . $data->attendee_name;
        $location = '';
        if (!empty($data->meeting_locations)) {
            $location_data = json_decode($data->meeting_locations, true);
        
            foreach ($location_data as $key => $value) {
                $location .= $value['location'] ." - ". $value['address'] ; 
                if ($key < count($location_data) - 1) {
                    $location .= ", ";
                }

            }  
        }
         
        $availability_time_zone = $data->availability_time_zone; // Example: "America/New_York"

        // Convert to required format with the correct timezone
        $dtStart = new \DateTime($data->start_time, new \DateTimeZone($availability_time_zone));
        $dtEnd = new \DateTime($data->end_time, new \DateTimeZone($availability_time_zone));
        $details = '<p>'.esc_html($data->meeting_title).'</p>'; 

        // Format for Google Calendar (Including Timezone)
        $start_time_google = $dtStart->format("Ymd\THis");
        $end_time_google = $dtEnd->format("Ymd\THis"); 
        // Google Calendar Link with Timezone
        $bookmarks['google'] = [
            'title' => __('Google Calendar', 'fluent-booking'),
            'url'   => add_query_arg([
                'dates'    => $start_time_google . '/' . $end_time_google,
                'text'     => $bookingTitle,
                'details'  => $details,
                'location' => $location,
                'ctz'      => $availability_time_zone
            ], 'https://calendar.google.com/calendar/r/eventedit'),

            'icon' => esc_url(TFHB_URL . 'assets/app/images/google-calendar.svg'),
        ];

        // Format for Outlook (ISO 8601 format with time zone)
        $start_time_outlook = $dtStart->format("Y-m-d\TH:i:s"); 
        $end_time_outlook = $dtEnd->format("Y-m-d\TH:i:s");
        // Outlook Calendar Link
        $bookmarks['outlook'] = [
            'title' => __('Outlook', 'fluent-booking'),
            'url'   => add_query_arg([
                'startdt'  => $start_time_outlook,
                'enddt'    => $end_time_outlook,
                'subject'  => $bookingTitle,
                'path'     => '/calendar/action/compose',
                'body'     =>  $details,
                'rru'      => 'addevent',
                'location' => $location,
            ], 'https://outlook.live.com/calendar/0/deeplink/compose'),
            'icon'  => esc_url(TFHB_URL . 'assets/app/images/outlook-calendar.svg'), 
        ];

        // // Microsoft Office 365 Calendar Link
        // $bookmarks['msoffice'] = [
        //     'title' => __('Microsoft Office', 'fluent-booking'),
        //     'url'   => add_query_arg([
        //         'startdt'  => $start_time_outlook,
        //         'enddt'    => $end_time_outlook,
        //         'subject'  => $bookingTitle,
        //         'path'     => '/calendar/action/compose',
        //         'body'     => $data->meeting_title,
        //         'rru'      => 'addevent',
        //         'location' => $location,
        //     ], 'https://outlook.office.com/calendar/0/deeplink/compose'),
        //     'icon'  => $assetsUrl . 'images/msoffice.svg'
        // ];


        // Format start time for Yahoo (UTC format with 'Z')
        $start_time_yahoo = $dtStart->format("Ymd\THis\Z");

        // Calculate duration in minutes
        $duration = $dtStart->diff($dtEnd);
        $duration_minutes = ($duration->h * 60) + $duration->i; // Convert hours to minutes
        // Format duration for Yahoo Calendar (HHMM format)
        $formatted_duration = str_pad(floor($duration_minutes / 60), 2, '0', STR_PAD_LEFT) . str_pad($duration_minutes % 60, 2, '0', STR_PAD_LEFT);

     

        // Yahoo Calendar Link with dynamic duration
        $bookmarks['yahoo'] = [
            'title' => __('Yahoo Calendar', 'fluent-booking'),
            'url'   => add_query_arg([
                'v'        => 60,
                'view'     => 'd',
                'type'     => 20,
                'title'    => $bookingTitle,
                'st'       => $start_time_yahoo,
                'dur'      => $formatted_duration,
                'desc'     => $details,
                'in_loc'   => $location,
            ], 'http://calendar.yahoo.com/'),
            'icon' => esc_url(TFHB_URL . 'assets/app/images/yahoo-calendar.svg'), 
        ];
        $bookmarks['other']    = [
            'title' => __('Other Calendar', 'fluent-booking'), 
            'url'   => $this->GetBookingIcsUrl($data), 
            'icon' => esc_url(TFHB_URL . 'assets/app/images/other-calendar.svg'), 
        ];
        
        return $bookmarks;

    }

    public  function generateBookingICS($data)
    {
        // Convert time to UTC format for ICS
        $start = new \DateTime("{$data->meeting_date} {$data->start_time}", new \DateTimeZone($data->availability_time_zone));
        $end = new \DateTime("{$data->meeting_date} {$data->end_time}", new \DateTimeZone($data->availability_time_zone));
        $start->setTimezone(new \DateTimeZone('UTC'));
        $end->setTimezone(new \DateTimeZone('UTC'));

        // ICS File Content
        $ics_content = "BEGIN:VCALENDAR\r\n";
        $ics_content .= "VERSION:2.0\r\n";
        $ics_content .= "PRODID:-//YourPlugin//BookingSystem//EN\r\n";
        $ics_content .= "METHOD:REQUEST\r\n";
        $ics_content .= "BEGIN:VEVENT\r\n";
        $ics_content .= "UID:" . md5($data->hash) . "\r\n";
        $ics_content .= "SUMMARY:" . $data->meeting_title . "\r\n";
        $ics_content .= "DESCRIPTION:Meeting with " . $data->attendee_name . "\r\n";
        $ics_content .= "DTSTART:" . $start->format('Ymd\THis\Z') . "\r\n";
        $ics_content .= "DTEND:" . $end->format('Ymd\THis\Z') . "\r\n";
        // Decode meeting locations
        $locations = json_decode($data->meeting_locations, true);
        if (!empty($locations)) {
            $locationString = [];
            foreach ($locations as $key => $location) {
                $locationString[] = "{$location['location']} - {$location['address']}";
            }
            $ics_content .= "LOCATION:" . implode(", ", $locationString) . "\r\n";
        }
        // $ics_content .= "LOCATION:" . $location . "\r\n";
        $ics_content .= "ORGANIZER;CN=\"" . $data->host_first_name . "\":mailto:" . $data->host_email . "\r\n";
        $ics_content .= "ATTENDEE;CN=\"" . $data->attendee_name . "\";ROLE=REQ-PARTICIPANT;RSVP=TRUE;PARTSTAT=ACCEPTED:mailto:" . $data->email . "\r\n";
        $ics_content .= "END:VEVENT\r\n";
        $ics_content .= "END:VCALENDAR\r\n";

        // Send Headers
        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="booking-event.ics"');
        echo $ics_content;
        exit;
    }
 

    // generate full booking ics 
    public function generateFullBookingICS($data)
    {  
        // iCal header
        // Start iCal file
        $ical = "BEGIN:VCALENDAR\r\n";
        $ical .= "VERSION:2.0\r\n";
        $ical .= "PRODID:-//Your Company//Meeting Scheduler//EN\r\n";
        $ical .= "CALSCALE:GREGORIAN\r\n";
        $ical .= "METHOD:PUBLISH\r\n";
        foreach ($data as $meeting) { 
           
            if($meeting->meeting_dates == '' || $meeting->start_time == ''){
                continue;
            }
            $meeting_dates = explode( ',', $meeting->meeting_dates );
            foreach ( $meeting_dates as $meeting_date ) {
                $uid = uniqid();
                $time_zone = !empty($meeting->availability_time_zone) ? $meeting->availability_time_zone : $meeting->host_time_zone;
                $dtStart = $this->formatToUTC($meeting_date, $meeting->start_time, $time_zone);
                $dtEnd = $this->formatToUTC($meeting_date, $meeting->end_time, $time_zone);
            
                $ical .= "BEGIN:VEVENT\r\n";
                $ical .= "UID:$uid\r\n";
                $ical .= "DTSTAMP:" . gmdate("Ymd\THis\Z") . "\r\n";
                $ical .= "DTSTART:$dtStart\r\n";
                $ical .= "DTEND:$dtEnd\r\n";
                $ical .= "SUMMARY:" . $meeting->title . "\r\n";
                $ical .= "STATUS:" . strtoupper($meeting->status) . "\r\n";

                // Decode meeting locations
                $locations = json_decode($meeting->meeting_locations, true);
                if (!empty($locations)) {
                    $locationString = [];
                    foreach ($locations as $key => $location) {
                        $locationString[] = "{$location['location']} - {$location['address']}";
                    }
                    $ical .= "LOCATION:" . implode(", ", $locationString) . "\r\n";
                }
                
                // Add attendees
                if (!empty($meeting->attendees)) {
                    foreach ($meeting->attendees as $attendee) {
                        $ical .= "ATTENDEE;CN={$attendee->attendee_name}:mailto:{$attendee->email}\r\n";
                    }
                }
            
                $ical .= "END:VEVENT\r\n";
            }
            
        }
            
        // iCal footer
        $ical .= "END:VCALENDAR\r\n";

        return $ical;
    }

    // Get booking ICS URL for the current user
    public function generateSingleBookingICS($meeting){ 
        // Start iCal file
        $ical = "BEGIN:VCALENDAR\r\n";
        $ical .= "VERSION:2.0\r\n";
        $ical .= "PRODID:-//Your Company//Meeting Scheduler//EN\r\n";
        $ical .= "CALSCALE:GREGORIAN\r\n";
        $ical .= "METHOD:PUBLISH\r\n"; 
        $meeting_dates = explode( ',', $meeting->meeting_dates );
        foreach ( $meeting_dates as $meeting_date ) {
            $uid = uniqid();
        
            $dtStart = $this->formatToUTC($meeting_date, $meeting->start_time, $meeting->availability_time_zone);
            $dtEnd = $this->formatToUTC($meeting_date, $meeting->end_time, $meeting->availability_time_zone);
        
            $ical .= "BEGIN:VEVENT\r\n";
            $ical .= "UID:$uid\r\n";
            $ical .= "DTSTAMP:" . gmdate("Ymd\THis\Z") . "\r\n";
            $ical .= "DTSTART:$dtStart\r\n";
            $ical .= "DTEND:$dtEnd\r\n";
            $ical .= "SUMMARY:" . $meeting->title . "\r\n";
            $ical .= "STATUS:" . strtoupper($meeting->status) . "\r\n";
    
            // Decode meeting locations
            $locations = json_decode($meeting->meeting_locations, true);
            if (!empty($locations)) {
                $locationString = [];
                foreach ($locations as $key => $location) {
                    $locationString[] = "{$location['location']} - {$location['address']}";
                }
                $ical .= "LOCATION:" . implode(", ", $locationString) . "\r\n";
            }
            
            // Add attendees
            if (!empty($meeting->attendees)) {
                foreach ($meeting->attendees as $attendee) {
                    $ical .= "ATTENDEE;CN={$attendee->attendee_name}:mailto:{$attendee->email}\r\n";
                }
            }
        
            $ical .= "END:VEVENT\r\n"; 
                

        }
       
        // iCal footer
        $ical .= "END:VCALENDAR\r\n";

        return $ical;
    }

     // Convert date and time to UTC format
	 public function formatToUTC($date, $time, $timezone){
        if (empty($timezone)) {
            $timezone = 'UTC';
        }

        // Determine the format based on the presence of AM/PM
        $format = (stripos($time, 'AM') !== false || stripos($time, 'PM') !== false) ? 'Y-m-d h:i A' : 'Y-m-d H:i';

        // Create DateTime object with detected format
        $datetime = \DateTime::createFromFormat($format, "$date $time", new \DateTimeZone($timezone));

        if ($datetime === false) {
            return false; // Return false if date parsing fails
        }

        // Convert to UTC
        $datetime->setTimezone(new \DateTimeZone("UTC"));

        return $datetime->format("Ymd\THis\Z");
    }

    // sent bookmark add to calender link in email notification
    public function sendBookmarkFormEmail($hash){ 
        // allowed types: download_ics, confirmation, cancel
   
        // decode hash to get booking data
        // encoded format: $hash = base64_encode( wp_json_encode( $hash ) );
        $decoded_hash = base64_decode($hash, true); 
        if ($decoded_hash === false) {
            return false; // Invalid base64 string
        }
        $booking_data = json_decode($decoded_hash, true);
        $type = $booking_data['type'] ?? '';
        $allowed_types = ['google', 'outlook', 'yahoo', 'other'];
        if ( !in_array($type, $allowed_types) ) {
            return false;
        }

        if (json_last_error() !== JSON_ERROR_NONE) {
            return false; // Invalid JSON
        }
        $Attendee = new Attendees();
		$attendeeBooking =  $Attendee->getAttendeeWithBooking( 
			array(
				array('id', '=',$booking_data['attendee_id']),
			),
			1,
			'DESC'
		); 
        if(!$attendeeBooking){
            return false;
        }
        // Get bookmarks
        $bookmarks = $this->getMeetingBookmarks($attendeeBooking);

        if (empty($bookmarks[$type]['url'])) {
            return false;
        }

        wp_redirect($bookmarks[$type]['url']);
        exit;
    }

 
}
