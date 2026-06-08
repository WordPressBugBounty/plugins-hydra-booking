<?php

namespace HydraBooking\Admin\Controller;

if (! defined('ABSPATH')) {
    exit;
}

use HydraBooking\DB\Meeting;
use HydraBooking\DB\Attendees;

class iCalendarController
{
    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('init', array($this, 'init'));
        add_action('init', array($this, 'add_rewrite_rules'));
        add_filter('query_vars', array($this, 'register_query_vars'));
        add_filter('template_include', array($this, 'tfhb_single_page_template'));
    }

    /**
     * Add custom rewrite rules for iCalendar
     */
    public function add_rewrite_rules()
    {
        add_rewrite_rule(
            '^icalendar/([^/]+)/?([^/]+)?/?$',
            'index.php?hydra-booking=icalendar&secret=$matches[1]&hash=$matches[2]',
            'top'
        );
    }

    /**
     * Register REST API routes
     */
    public function create_endpoint()
    {
        register_rest_route(
            'hydra-booking/v1',
            '/settings/icalendar',
            array(
                'methods'  => 'GET',
                'callback' => array($this, 'GetICalSettings'),
                'permission_callback' => function () {
                    return current_user_can('tfhb_manage_settings');
                },
            )
        );
        register_rest_route(
            'hydra-booking/v1',
            '/settings/icalendar/reset',
            array(
                'methods'  => 'POST',
                'callback' => array($this, 'ResetICalSecret'),
                'permission_callback' => function () {
                    return current_user_can('tfhb_manage_settings');
                },
            )
        );
        register_rest_route(
            'hydra-booking/v1',
            '/settings/icalendar/meetings',
            array(
                'methods'  => 'GET',
                'callback' => array($this, 'GetMeetings'),
                'permission_callback' => function () {
                    return current_user_can('tfhb_manage_settings');
                },
            )
        );
        register_rest_route(
            'hydra-booking/v1',
            '/settings/icalendar/generate-meeting-url',
            array(
                'methods'  => 'POST',
                'callback' => array($this, 'GenerateMeetingICalUrl'),
                'permission_callback' => function () {
                    return current_user_can('tfhb_manage_settings');
                },
            )
        );
    }

    /**
     * Get all meetings for dropdown
     */
    public function GetMeetings()
    {
        $MeetingDB = new Meeting();
        $meetings = $MeetingDB->getAll();
        
        $data = array();
        if (!empty($meetings)) {
            foreach ($meetings as $meeting) {
                $hash = get_post_meta($meeting->post_id, '_tfhb_ical_hash', true);
                $data[] = array(
                    'id'    => $meeting->id,
                    'title' => $meeting->title,
                    'hash'  => $hash,
                    'url'   => $hash ? $this->generate_ical_url_by_hash($hash) : ''
                );
            }
        }
        
        return rest_ensure_response(array(
            'status'   => true,
            'meetings' => $data,
        ));
    }

    /**
     * Generate and save meeting specific iCal URL
     */
    public function GenerateMeetingICalUrl($request)
    {
        $meeting_id = $request->get_param('meeting_id');
        
        if (!$meeting_id) {
            return rest_ensure_response(array('status' => false, 'message' => __('Invalid Meeting ID', 'hydra-booking')));
        }
        
        $MeetingDB = new Meeting();
        $meeting = $MeetingDB->getAll(array(array('id', '=', $meeting_id)));
        
        if (empty($meeting)) {
            return rest_ensure_response(array('status' => false, 'message' => __('Meeting not found', 'hydra-booking')));
        }
        
        $meeting = $meeting[0];
        $hash = get_post_meta($meeting->post_id, '_tfhb_ical_hash', true);
        
        if (!$hash) {
            $hash = $this->encrypt_meeting_id($meeting->id);
            update_post_meta($meeting->post_id, '_tfhb_ical_hash', $hash);
        }
        
        return rest_ensure_response(array(
            'status' => true,
            'hash'   => $hash,
            'url'    => $this->generate_ical_url_by_hash($hash),
        ));
    }

    /**
     * Helper to generate URL by hash
     */
    private function generate_ical_url_by_hash($hash)
    {
        $args = array(
            'hydra-booking' => 'icalendar',
            'secret'        => $this->get_site_secret(),
            'hash'          => $hash
        );

        return add_query_arg($args, home_url('/'));
    }

    /**
     * Reset the iCal secret token
     */
    public function ResetICalSecret()
    {
        // To truly "reset" based on website specific info, we can just clear the option
        // and let get_site_secret regenerate it. 
        // However, if the generation logic is deterministic (like hashing home_url), 
        // resetting it won't change it unless we add a random salt component.

        // Let's store a random salt to allow resetting even if the site info is the same.
        $random_salt = wp_generate_password(16, false);
        update_option('_tfhb_ical_reset_salt', $random_salt);
        delete_option('_tfhb_ical_secret');

        $new_secret = $this->get_site_secret();

        return rest_ensure_response(array(
            'status' => true,
            'message' => __('Secret token has been reset.', 'hydra-booking'),
            'url'    => $this->generate_ical_url(),
            'secret' => $new_secret,
        ));
    }

    /**
     * Get iCal settings for frontend
     */
    public function GetICalSettings()
    {
        return rest_ensure_response(array(
            'status' => true,
            'url'    => $this->generate_ical_url(),
            'secret' => $this->get_site_secret(),
        ));
    }

    /**
     * Register additional query variables
     */
    public function register_query_vars($vars)
    {
        $vars[] = 'secret';
        return $vars;
    }

    /**
     * Initialize the controller
     */
    public function init()
    {
        if (get_query_var('hydra-booking') === 'icalendar') {
        }
    }

    public function tfhb_single_page_template($template)
    {

        if (get_query_var('hydra-booking') === 'icalendar') {
            return  $this->handle_ical_request();
        }

        return $template;
    }

    /**
     * Handle the incoming iCal request
     */
    private function handle_ical_request()
    {
        // Handle both 'secret' and 'secrect' (as misspelled in previous request)
        $secret = isset($_GET['secret']) ? sanitize_text_field($_GET['secret']) : get_query_var('secret');
        if (empty($secret) && isset($_GET['secrect'])) {
            $secret = sanitize_text_field($_GET['secrect']);
        }

        if (!$this->validate_secret($secret)) {
            wp_die(__('Invalid Secret', 'hydra-booking'), __('Unauthorized', 'hydra-booking'), array('response' => 403));
        }

        $hash = isset($_GET['hash']) ? sanitize_text_field($_GET['hash']) : get_query_var('hash');

        $meeting_id = 0;

        if (!empty($hash)) {
            $meeting_id = $this->decrypt_meeting_id($hash);
            if (!$meeting_id) {
                wp_die(__('Invalid Meeting Hash', 'hydra-booking'), __('Error', 'hydra-booking'), array('response' => 400));
            }
        }

        echo $this->generate_ical($meeting_id);
        exit;
    }

    /**
     * Validate the site-specific secret
     */
    private function validate_secret($secret)
    {
        if (empty($secret)) {
            return false;
        }
        return $secret === $this->get_site_secret();
    }

    /**
     * Get or generate the site-specific secret
     */
    public function get_site_secret()
    {
        $stored_secret = get_option('_tfhb_ical_secret');
        if (!$stored_secret) {
            // Generate a stable, site-specific secret based on website URL, AUTH_SALT, and a reset salt
            $reset_salt = get_option('_tfhb_ical_reset_salt', '');
            $site_identifier = home_url() . AUTH_SALT . $reset_salt;
            $stored_secret = hash('sha256', $site_identifier);
            update_option('_tfhb_ical_secret', $stored_secret);
        }
        return $stored_secret;
    }

    /**
     * Decrypt the meeting ID from the hash
     */
    private function decrypt_meeting_id($hash)
    {
        if (empty($hash) || ! function_exists('openssl_decrypt')) {
            return false;
        }

        // Convert URL-safe Base64 back to standard Base64
        $hash = str_replace(['-', '_'], ['+', '/'], $hash);

        // Add padding if necessary
        $padding = strlen($hash) % 4;
        if ($padding > 0) {
            $hash .= str_repeat('=', 4 - $padding);
        }

        $decoded = base64_decode($hash, true);
        if ($decoded === false || strlen($decoded) <= 16) {
            return false;
        }

        $key = substr(hash('sha256', AUTH_SALT . SECURE_AUTH_SALT, true), 0, 32);
        $iv  = substr($decoded, 0, 16);
        $encrypted = substr($decoded, 16);
        $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);

        return is_numeric($decrypted) ? (int)$decrypted : false;
    }

    /**
     * Encrypt a meeting ID
     */
    private function encrypt_meeting_id($id)
    {
        if (empty($id) || ! function_exists('openssl_encrypt')) {
            return false;
        }



        $key = substr(hash('sha256', AUTH_SALT . SECURE_AUTH_SALT, true), 0, 32);
        $iv  = openssl_random_pseudo_bytes(16);
        $encrypted = openssl_encrypt((string)$id, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);

        if ($encrypted === false) {
            return false;
        }

        $base64 = base64_encode($iv . $encrypted);
        // Make Base64 URL-safe by replacing + and / and removing padding =
        return str_replace(['+', '/', '='], ['-', '_', ''], $base64);
    }

    /**
     * Generate the iCalendar URL
     */
    public function generate_ical_url($meeting_id = 0)
    {
        $args = array(
            'hydra-booking' => 'icalendar',
            'secret'        => $this->get_site_secret(),
        );

        if ($meeting_id) {
            $hash = $this->encrypt_meeting_id($meeting_id);

            if ($hash) {
                $args['hash'] = $hash;
            }
        }

        return add_query_arg($args, home_url('/'));
    }

    /**
     * Fetch data and output ICS
     */
    private function generate_ical($meeting_id = 0)
    {
        $MeetingDB = new Meeting();
        $AttendeeDB = new Attendees();

        $where = array();
        if ($meeting_id) {
            $where[] = array('id', '=', $meeting_id);
        }

        // Get meetings with host info
        $meetings = $MeetingDB->getAll($where);

        if (empty($meetings)) {
            $this->output_empty_ical();
            exit;
        }

        // Prepare ICS content
        $ical = "BEGIN:VCALENDAR\r\n";
        $ical .= "VERSION:2.0\r\n";
        $ical .= "PRODID:-//HydraBooking//iCalendar//EN\r\n";
        $ical .= "CALSCALE:GREGORIAN\r\n";
        $ical .= "METHOD:PUBLISH\r\n";
        $ical .= "X-WR-CALNAME:" . get_bloginfo('name') . " Bookings\r\n";

        foreach ($meetings as $meeting) {
            // Fetch bookings for this meeting
            // We need to get attendees that are associated with this meeting
            $attendees = $AttendeeDB->getAttendeeWithBooking(array(
                array('meeting_id', '=', $meeting->id),
                array('status', '!=', 'canceled')
            ));

            if (empty($attendees)) {
                continue;
            }

            foreach ($attendees as $attendee) {
                if (empty($attendee->meeting_dates) || empty($attendee->start_time)) {
                    continue;
                }

                // Data Structure like Google Calendar
                $booking_type = isset($attendee->booking_type) ? $attendee->booking_type : '';
                $meeting_title = isset($attendee->meeting_title) ? $attendee->meeting_title : $meeting->title;
                $attendee_name = isset($attendee->attendee_name) ? $attendee->attendee_name : '';
                
                if ($booking_type == 'one-to-group') {
                    $event_title = $meeting_title;
                } else {
                    $event_title = $meeting_title . ' ( Meeting with ' . $attendee_name . ' )';
                }

                // Meeting location formatting (like Google Calendar)
                $locations = !empty($attendee->meeting_locations) ? $attendee->meeting_locations : array();
                $booking_locations = is_string($locations) ? json_decode($locations) : $locations;
                $booking_locations_data = '';
                if (!empty($booking_locations)) {
                    foreach ($booking_locations as $key => $value) {
                        $loc_name = isset($value->location) ? $value->location : (isset($value['location']) ? $value['location'] : '');
                        if (!empty($loc_name)) {
                            $booking_locations_data .= $loc_name . ', ';
                        }
                    }
                }
                $booking_locations_data = rtrim($booking_locations_data, ', ');

                $meeting_dates = explode(',', $attendee->meeting_dates);
                foreach ($meeting_dates as $date) {
                    $dtStart = $this->formatToUTC($date, $attendee->start_time, $attendee->availability_time_zone);
                    $dtEnd = $this->formatToUTC($date, $attendee->end_time, $attendee->availability_time_zone);

                    if (!$dtStart || !$dtEnd) continue;

                    $ical .= "BEGIN:VEVENT\r\n";
                    $ical .= "UID:" . $attendee->id . "-" . md5($date . $attendee->start_time) . "@hydrabooking.com\r\n";
                    $ical .= "DTSTAMP:" . gmdate("Ymd\THis\Z") . "\r\n";
                    $ical .= "DTSTART:$dtStart\r\n";
                    $ical .= "DTEND:$dtEnd\r\n";
                    $ical .= "SUMMARY:" . $event_title . "\r\n";
                    $ical .= "DESCRIPTION:Booking for " . $meeting_title . "\\nAttendee: " . $attendee_name . " (" . $attendee->email . ")\\nStatus: " . $attendee->status . "\r\n";

                    if (!empty($booking_locations_data)) {
                        $ical .= "LOCATION:" . $booking_locations_data . "\r\n";
                    }

                    $ical .= "END:VEVENT\r\n";
                }
            }
        }

        $ical .= "END:VCALENDAR\r\n";

        // Output Headers
        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: inline');
        echo $ical;
        exit;
    }

    /**
     * Output an empty but valid iCal if no data found
     */
    private function output_empty_ical()
    {
        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: inline');
        echo "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//HydraBooking//iCalendar//EN\r\nMETHOD:PUBLISH\r\nEND:VCALENDAR\r\n";
    }

    /**
     * Convert date and time to UTC format (Helper)
     */
    private function formatToUTC($date, $time, $timezone)
    {
        if (empty($timezone)) {
            $timezone = wp_timezone_string();
        }

        $format = (stripos($time, 'AM') !== false || stripos($time, 'PM') !== false) ? 'Y-m-d h:i A' : 'Y-m-d H:i';

        try {
            $datetime = \DateTime::createFromFormat($format, trim($date) . " " . trim($time), new \DateTimeZone($timezone));
            if ($datetime === false) {
                return false;
            }
            $datetime->setTimezone(new \DateTimeZone("UTC"));
            return $datetime->format("Ymd\THis\Z");
        } catch (\Exception $e) {
            return false;
        }
    }
}
