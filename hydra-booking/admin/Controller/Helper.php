<?php
namespace HydraBooking\Admin\Controller;
// exit
if ( ! defined( 'ABSPATH' ) ) { exit; } 
 
class Helper {


	// constaract
	public function __construct() {
 
	}

    // Get Default Notification
    public function get_default_notification_template(){
        $notification = array(
            'host' => array(),
            'attendee' => array(),
            'telegram' => array(),
            'twilio' => array(),
            'slack' => array(),
        );

        // Get image URL dynamically
        $calendar_image_url = plugins_url('assets/images/calendar-days.png', dirname(__FILE__, 2));
        $user_image_url = plugins_url('assets/images/user.png', dirname(__FILE__, 2));
        $meeting_image_url = plugins_url('assets/images/Meeting.png', dirname(__FILE__, 2));
        $file_image_url = plugins_url('assets/images/file-text.png', dirname(__FILE__, 2));
        $location_image_url = plugins_url('assets/images/Location.png', dirname(__FILE__, 2));
        $mail_image_url = plugins_url('assets/images/mail.png', dirname(__FILE__, 2));
        $phone_image_url = plugins_url('assets/images/phone.png', dirname(__FILE__, 2));
        $google_calendar_image_url = plugins_url('assets/app/images/g-calendar.png', dirname(__FILE__, 2));
        $outlook_calendar_image_url = plugins_url('assets/app/images/outlook-icon.png', dirname(__FILE__, 2));
        $yahoo_calendar_image_url = plugins_url('assets/app/images/yahoo-icon.png', dirname(__FILE__, 2));
        $other_calendar_image_url = plugins_url('assets/app/images/other-calendar.png', dirname(__FILE__, 2));

        $add_to_calendar_content = '<div><a style="margin-right: 8px;" href="{{booking.add_to_calendar.google}}" target="_blank"><img style="height: 20px; width: 20px;" src="'.esc_url($google_calendar_image_url).'" alt="icon" /></a><a style="margin-right: 8px;" href="{{booking.add_to_calendar.outlook}}" target="_blank"><img style="height: 20px; width: 20px;" src="'.esc_url($outlook_calendar_image_url).'" alt="icon" /></a><a style="margin-right: 8px;" href="{{booking.add_to_calendar.yahoo}}" target="_blank"><img style="height: 20px; width: 20px;" src="'.esc_url($yahoo_calendar_image_url).'" alt="icon" /></a><a style="margin-right: 8px;" href="{{booking.add_to_calendar.other}}" target="_blank"><img style="height: 20px; width: 20px;" src="'.esc_url($other_calendar_image_url).'" alt="icon" /></a></div>';
        

        //  Host Notification
        $notification['host']['booking_confirmation'] = array(
            'status' => 1,
            'template' => 'default',
            'from' =>  '{{wp.admin_email}}',
            'subject' => 'New Booking between {{host.name}} & {{attendee.name}}',
            'body' =>  '
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;">
                    <tbody>
                        <tr>
                            <td bgcolor="#215732" style="padding: 16px 32px; text-align: left; border-radius: 8px 8px 0 0;">
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                    <tbody>
                                        <tr>
                                            <td style="vertical-align: middle;">
                                                <span style="color: #FFF; font-size: 22px; font-weight: 600; margin: 0;">HydraBooking</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 32px; width: 100%; max-width: 600px; margin: 0 auto;">
                    <tbody>
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;">
                                    <tbody>
                                        <tr>
                                            <td>
                                                <p style="font-weight: bold; margin: 0; font-size: 17px;">Hey {{attendee.name}},</p>
                                                <p style="font-weight: bold; margin: 8px 0 0 0; font-size: 17px;">A new booking with {{host.name}} was confirmed.</p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 32px; width: 100%; max-width: 600px; margin: 0 auto;">
                    <tbody>
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border: 2px dashed #C0D8C4; border-radius: 8px; padding: 24px; background: #fff;">
                                    <tbody>
                                        <tr>
                                            <td style="font-weight: bold; font-size: 16px;">Meeting Details</td>
                                        </tr>

                                        <tr>
                                            <td>
                                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px; width: 100%;">
                                                    <tbody>
                                                        <tr>
                                                            <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                                                <img src="'.esc_url($calendar_image_url).'" alt="data_time" style="float: left; margin-right: 8px;">
                                                                Date &amp; Time:
                                                            </td>
                                                            <td style="padding-left: 32px; font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                                                <strong>{{meeting.date}} - {{meeting.time}}</strong> <br>Host time: {{booking.start_date_time_for_host}} - {{booking.full_start_end_host_timezone}}
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>
                                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px; width: 100%;">
                                                    <tbody>
                                                        <tr>
                                                            <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                                                <img src="' . esc_url($user_image_url) . '" alt="host" style="float: left; margin-right: 8px;">
                                                                Host:
                                                            </td>
                                                            <td style="padding-left: 32px; font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                                                <strong>{{host.name}}</strong>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>
                                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px; width: 100%;">
                                                    <tbody>
                                                        <tr>
                                                            <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                                                <img src="' . esc_url($meeting_image_url) . '" alt="about" style="float: left; margin-right: 8px;">
                                                                About:
                                                            </td>
                                                            <td style="padding-left: 32px; font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                                                <strong>{{meeting.title}}</strong>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>
                                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px; width: 100%;">
                                                    <tbody>
                                                        <tr>
                                                            <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                                                <img src="' . esc_url($file_image_url) . '" alt="description" style="float: left; margin-right: 8px;">
                                                                Description:
                                                            </td>
                                                            <td style="padding-left: 32px; font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                                                {{meeting.content}}
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>
                                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px; width: 100%;">
                                                    <tbody>
                                                        <tr>
                                                            <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                                                <img src="' . esc_url($location_image_url) . '" alt="location" style="float: left; margin-right: 8px;">
                                                                Location:
                                                            </td>
                                                            <td style="padding-left: 32px; font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                                                <strong>{{booking.location_details_html}}</strong>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 32px; width: 100%; max-width: 600px; margin: 0 auto;">
                    <tbody>
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border: 2px dashed #C0D8C4; border-radius: 8px; padding: 24px; background: #fff;">
                                    <tbody>
                                        <tr>
                                            <td style="font-weight: bold; font-size: 16px;">Host Details</td>
                                        </tr>

                                        <tr>
                                            <td>
                                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px; width: 100%;">
                                                    <tbody>
                                                        <tr>
                                                            <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                                                <img src="' . esc_url($user_image_url) . '" alt="name" style="float: left; margin-right: 8px;">
                                                                Name:
                                                            </td>
                                                            <td style="padding-left: 32px; font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                                                <strong>{{host.name}}</strong>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>
                                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px; width: 100%;">
                                                    <tbody>
                                                        <tr>
                                                            <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                                                <img src="' . esc_url($mail_image_url) . '" alt="email" style="float: left; margin-right: 8px;">
                                                                Email:
                                                            </td>
                                                            <td style="padding-left: 32px; font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                                                <strong><a href="" style="text-decoration: none; color: #2E6B38;">{{host.email}}</a></strong>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>
                                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px; width: 100%;">
                                                    <tbody>
                                                        <tr>
                                                            <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                                                <img src="' . esc_url($phone_image_url) . '" alt="phone" style="float: left; margin-right: 8px;">
                                                                Phone:
                                                            </td>
                                                            <td style="padding-left: 32px; font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                                                <strong><a href="" style="text-decoration: none; color: #2E6B38;">{{host.phone}}</a></strong>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 0; width: 100%; max-width: 600px; margin: 0 auto;">
                    <tbody>
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="padding: 0 32px; width: 100%; max-width: 600px; margin: 0 auto;">
                                    <tbody>
                                        <tr>
                                            <td style="font-weight: bold; font-size: 17px; padding-bottom: 24px;" bgcolor="#fff">Instructions</td>
                                        </tr>
                                        <tr>
                                            <td style="font-size: 15px;">
                                                <ul>
                                                    <li>Please <strong>join the event five minutes before the event starts</strong> based on your time zone.</li>
                                                    <li>Ensure you have a good internet connection, a quality camera, and a quiet space.</li>
                                                </ul>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 0; width: 100%; max-width: 600px; margin: 0 auto;" class="tfhb-cancel-reschedule-btn">
                    <tbody>
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="border-top: 1px dashed #C0D8C4; border-bottom: 1px dashed #C0D8C4; padding: 0 32px; width: 100%; max-width: 600px; margin: 0 auto;">
                                    <tbody>
                                        <tr>
                                            <td style="font-size: 15px; padding: 24px 0 16px 0;">You can cancel or reschedule this event for any reason.</td>
                                        </tr>
                                        <tr>
                                            <td style="font-size: 15px; padding-bottom: 24px;">
                                                <a href="{{booking.cancel_link}}" class="tfhb-cancel-btn" style="padding: 8px 24px; border-radius: 8px; border: 1px solid #C0D8C4; background: #FFF; color: #273F2B; display: inline-block; text-decoration: none;">Cancel</a>
                                                <a href="{{booking.rescheduled_link}}" class="tfhb-reschedule-btn" style="padding: 8px 24px; border-radius: 8px; border: 1px solid #C0D8C4; background: #FFF; color: #273F2B; display: inline-block; margin-left: 16px; text-decoration: none;">Reschedule</a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding-bottom: 16px;width: 100%; max-width: 600px; margin: 0 auto;">
                    <tr>
                        <td>
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="border-bottom: 1px dashed ${emailBuilder.value[key].border_color}; padding: 0 32px; width: 100%; max-width: 600px; margin: 0 auto;">
                                <tr>
                                    <td style="font-size: 15px;padding: 8px 0 12px 0; text-align: center;">Add To Calendar</td>  
                                </tr> 
                                <tr>
                                    <td style="font-size: 15px;padding: 8px 0 8px 0; text-align: center;">
                                        <div><a style="margin-right: 8px;" href="{{booking.add_to_calendar.google}}" target="_blank"><img src="'.esc_url($google_calendar_image_url).'" alt="icon" /></a><a style="margin-right: 8px;" href="{{booking.add_to_calendar.outlook}}" target="_blank"><img src="'.esc_url($outlook_calendar_image_url).'" alt="icon" /></a><a style="margin-right: 8px;" href="{{booking.add_to_calendar.yahoo}}" target="_blank"><img src="'.esc_url($yahoo_calendar_image_url).'" alt="icon" /></a><a style="margin-right: 8px;" href="{{booking.add_to_calendar.other}}" target="_blank"><img src="'.esc_url($other_calendar_image_url).'" alt="icon" /></a></div>
                                    </td>  
                                </tr> 
                            </table>
                        </td>
                    </tr>
                </table>

                <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#121D13" style="padding: 16px 32px; border-radius: 0px 0px 8px 8px; width: 100%; max-width: 600px; margin: 0 auto;">
                    <tbody>
                        <tr>
                            <td align="left">
                                <span style="color: #FFF; font-size: 16.5px; font-weight: bold;">HydraBooking</span>
                                <p style="color: #FFF; font-size: 13px; margin: 8px 0 0 0;">The WordPress Plugin to <br>Supercharge Your Scheduling</p>
                            </td>
                            <td align="right" class="social" style="vertical-align: baseline;">
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                    <tbody>
                                        <tr>
                                            <td style="padding-bottom: 4px;">
                                                <a href="#" style="text-decoration: none; color: #FFF;">Facebook</a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding-bottom: 4px;">
                                                <a href="#" style="text-decoration: none; color: #FFF;">Twitter</a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding-bottom: 4px;">
                                                <a href="#" style="text-decoration: none; color: #FFF;">Youtube</a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>
            ',
            'builder' => array(
                array(
                    'id' => 'header',
                    'order' => 0,
                    'status' => 1,
                    'title' => 'Header',
                    'content' => '<span style="color: #FFF; font-size: 22px; font-weight: 600; margin: 0;">HydraBooking</span>',
                    'logo' => '',
                    'background' => '#215732'
                ),
                array(
                    'id' => 'gratitude',
                    'order' => 1,
                    'status' => 1,
                    'title' => 'Greetings',
                    'content' => '<p style="font-weight: bold;margin: 0; font-size: 17px;">Hey {{attendee.name}},</p><p style="font-weight: bold; margin: 8px 0 0 0; font-size: 17px;">A new booking with {{host.name}} was confirmed.</p>'
                ),
                array(
                    'id' => 'meeting_details',
                    'order' => 2,
                    'status' => 1,
                    'title' => 'Meeting Details',
                    'border_color' => '#C0D8C4',
                    'content' => array(
                        'data_time' => array(
                            'status' => 1,
                            'title' => 'Date & Time:',
                            'content' => '<strong>{{meeting.date}} - {{meeting.time}}</strong> <br>Host time: {{booking.start_date_time_for_host}} - {{booking.full_start_end_host_timezone}}'
                        ),
                        'host' => array(
                            'status' => 1,
                            'title' => 'Host:',
                            'content' => '<strong>{{host.name}}</strong>'
                        ),
                        'about' => array(
                            'status' => 1,
                            'title' => 'About:',
                            'content' => '<strong>{{meeting.title}}</strong>'
                        ),
                        'description' => array(
                            'status' => 1,
                            'title' => 'Description:',
                            'content' => '{{meeting.content}}'
                        ),
                        'location' => array(
                            'status' => 1,
                            'title' => 'Location:',
                            'content' => '<strong>{{booking.location_details_html}}</strong>'
                        ),
                    )
                ),
                array(
                    'id' => 'host_details',
                    'order' => 3,
                    'status' => 1,
                    'title' => 'Host Details',
                    'border_color' => '#C0D8C4',
                    'content' => array(
                        'name' => array(
                            'status' => 1,
                            'title' => 'Name:',
                            'content' => '<strong>{{host.name}}</strong>'
                        ),
                        'email' => array(
                            'status' => 1,
                            'title' => 'Email:',
                            'content' => '<strong><a href="" style="text-decoration: none; color: #2E6B38;">{{host.email}}</a></strong>'
                        ),
                        'phone' => array(
                            'status' => 1,
                            'title' => 'Phone:',
                            'content' => '<strong><a href="" style="text-decoration: none; color: #2E6B38;">{{host.phone}}</a></strong>'
                        ),
                    )
                ),
                array(
                    'id' => 'instructions',
                    'order' => 4,
                    'status' => 1,
                    'title' => 'Instructions',
                    'content' => '<ul><li>Please <strong>join the event five minutes before the event starts</strong> based on your time zone.</li><li>Ensure you have a good internet connection, a quality camera, and a quiet space.</li></ul>',
                ),
                array(
                    'id' => 'cancel_reschedule',
                    'order' => 5,
                    'status' => 1,
                    'title' => 'Buttons',
                    'border_color' => '#C0D8C4',
                    'content' => array(
                        'description' => array(
                            'status' => 1,
                            'content' => 'You can cancel or reschedule this event for any reason.'
                        ),
                        'cancel' => array(
                            'status' => 1,
                            'content' => '{{booking.cancel_link}}'
                        ),
                        'reschedule' => array(
                            'status' => 1,
                            'content' => '{{booking.rescheduled_link}}'
                        ),
                    )
                ),
                array(
                    'id' => 'add_to_calendar',
                    'order' => 6,
                    'status' => 1,
                    'title' => 'Add to Calendar',
                    'border_color' => '#C0D8C4',
                    'content' => array(
                        'description' => array(
                            'status' => 1,
                            'content' => 'Add to calendar'
                        ),
                        'list' => array(
                            'status' => 1,
                            'content' => $add_to_calendar_content
                        ), 
                    )
                ),
                array(
                    'id' => 'footer',
                    'order' => 7,
                    'status' => 1,
                    'title' => 'Footer',
                    'content' => array(
                        'description' => array(
                            'status' => 1,
                            'content' => '<span style="color: #FFF; font-size: 16.5px; font-weight: bold;">HydraBooking</span><p style="color: #FFF; font-size: 13px; margin: 8px 0 0 0;">The WordPress Plugin to <br>Supercharge Your Scheduling</p>'
                        ),
                        'social' => array(
                            'status' => 1,
                            'data' => array(
                                array(
                                    'title' => 'Facebook',
                                    'url' => '#'
                                ),
                                array(
                                    'title' => 'Twitter',
                                    'url' => '#'
                                ),
                                array(
                                    'title' => 'Youtube',
                                    'url' => '#'
                                ),
                            )
                        )
                    )
                ),
            )
            
        );
        $notification['host']['booking_cancel'] = array(
            'status' => 1,
            'template' => 'default',
            'from' => '{{wp.admin_email}}',
            'subject' => 'A booking was cancelled with {{attendee.name}}',
            'body' =>  '
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;">
                    <tbody>
                        <tr>
                            <td bgcolor="#215732" style="padding: 16px 32px; text-align: left; border-radius: 8px 8px 0 0;">
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                    <tbody>
                                        <tr>
                                            <td style="vertical-align: middle;">
                                                <span style="color: #FFF; font-size: 22px; font-weight: 600; margin: 0;">HydraBooking</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 32px; width: 100%; max-width: 600px; margin: 0 auto;">
                    <tbody>
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;">
                                    <tbody>
                                        <tr>
                                            <td>
                                                <p style="font-weight: bold; margin: 0; font-size: 17px;">Hey {{attendee.name}},</p>
                                                <p style="font-weight: bold; margin: 8px 0 0 0; font-size: 17px;">Booking Cancellation</p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 32px; width: 100%; max-width: 600px; margin: 0 auto;">
                    <tbody>
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border: 2px dashed #C0D8C4; border-radius: 8px; padding: 24px; background: #fff;">
                                    <tbody>
                                        <tr>
                                            <td style="font-weight: bold; font-size: 16px;">Meeting Details</td>
                                        </tr>

                                        <tr>
                                            <td>
                                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px; width: 100%;">
                                                    <tbody>
                                                        <tr>
                                                            <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                                                <img src="' . esc_url($calendar_image_url) . '" alt="data_time" style="float: left; margin-right: 8px;">
                                                                Date &amp; Time:
                                                            </td>
                                                            <td style="padding-left: 32px; font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                                                <strong>{{meeting.date}} - {{meeting.time}}</strong> <br>Host time: {{booking.start_date_time_for_host}} - {{booking.full_start_end_host_timezone}}
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>
                                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px; width: 100%;">
                                                    <tbody>
                                                        <tr>
                                                            <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                                                <img src="' . esc_url($user_image_url) . '" alt="host" style="float: left; margin-right: 8px;">
                                                                Host:
                                                            </td>
                                                            <td style="padding-left: 32px; font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                                                <strong>{{host.name}}</strong>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>
                                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px; width: 100%;">
                                                    <tbody>
                                                        <tr>
                                                            <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                                                <img src="' . esc_url($meeting_image_url) . '" alt="about" style="float: left; margin-right: 8px;">
                                                                About:
                                                            </td>
                                                            <td style="padding-left: 32px; font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                                                <strong>{{meeting.title}}</strong>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>
                                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px; width: 100%;">
                                                    <tbody>
                                                        <tr>
                                                            <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                                                <img src="' . esc_url($file_image_url) . '" alt="description" style="float: left; margin-right: 8px;">
                                                                Description:
                                                            </td>
                                                            <td style="padding-left: 32px; font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                                                {{meeting.content}}
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>
                                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px; width: 100%;">
                                                    <tbody>
                                                        <tr>
                                                            <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                                                <img src="' . esc_url($location_image_url) . '" alt="location" style="float: left; margin-right: 8px;">
                                                                Location:
                                                            </td>
                                                            <td style="padding-left: 32px; font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                                                <strong>{{booking.location_details_html}}</strong>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 32px; width: 100%; max-width: 600px; margin: 0 auto;">
                    <tbody>
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border: 2px dashed #C0D8C4; border-radius: 8px; padding: 24px; background: #fff;">
                                    <tbody>
                                        <tr>
                                            <td style="font-weight: bold; font-size: 16px;">Host Details</td>
                                        </tr>

                                        <tr>
                                            <td>
                                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px; width: 100%;">
                                                    <tbody>
                                                        <tr>
                                                            <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                                                <img src="' . esc_url($user_image_url) . '" alt="name" style="float: left; margin-right: 8px;">
                                                                Name:
                                                            </td>
                                                            <td style="padding-left: 32px; font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                                                <strong>{{host.name}}</strong>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>
                                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px; width: 100%;">
                                                    <tbody>
                                                        <tr>
                                                            <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                                                <img src="' . esc_url($mail_image_url) . '" alt="email" style="float: left; margin-right: 8px;">
                                                                Email:
                                                            </td>
                                                            <td style="padding-left: 32px; font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                                                <strong><a href="" style="text-decoration: none; color: #2E6B38;">{{host.email}}</a></strong>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td>
                                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px; width: 100%;">
                                                    <tbody>
                                                        <tr>
                                                            <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                                                <img src="' . esc_url($phone_image_url) . '" alt="phone" style="float: left; margin-right: 8px;">
                                                                Phone:
                                                            </td>
                                                            <td style="padding-left: 32px; font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                                                <strong><a href="" style="text-decoration: none; color: #2E6B38;">{{host.phone}}</a></strong>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 0; width: 100%; max-width: 600px; margin: 0 auto;">
                    <tbody>
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="padding: 0 32px; width: 100%; max-width: 600px; margin: 0 auto;">
                                    <tbody>
                                        <tr>
                                            <td style="font-weight: bold; font-size: 17px; padding-bottom: 24px;" bgcolor="#fff">Instructions</td>
                                        </tr>
                                        <tr>
                                            <td style="font-size: 15px;">
                                                <ul>
                                                    <li>Please <strong>join the event five minutes before the event starts</strong> based on your time zone.</li>
                                                    <li>Ensure you have a good internet connection, a quality camera, and a quiet space.</li>
                                                </ul>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 0; width: 100%; max-width: 600px; margin: 0 auto;" class="tfhb-cancel-reschedule-btn">
                    <tbody>
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="border-top: 1px dashed #C0D8C4; border-bottom: 1px dashed #C0D8C4; padding: 0 32px; width: 100%; max-width: 600px; margin: 0 auto;">
                                    <tbody>
                                        <tr>
                                            <td style="font-size: 15px; padding: 24px 0 16px 0;">You can cancel or reschedule this event for any reason.</td>
                                        </tr>
                                        <tr>
                                            <td style="font-size: 15px; padding-bottom: 24px;">
                                                <a href="{{booking.cancel_link}}" class="tfhb-cancel-btn" style="padding: 8px 24px; border-radius: 8px; border: 1px solid #C0D8C4; background: #FFF; color: #273F2B; display: inline-block; text-decoration: none;">Cancel</a>
                                                <a href="{{booking.rescheduled_link}}" class="tfhb-reschedule-btn" style="padding: 8px 24px; border-radius: 8px; border: 1px solid #C0D8C4; background: #FFF; color: #273F2B; display: inline-block; margin-left: 16px; text-decoration: none;">Reschedule</a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding-bottom: 16px;width: 100%; max-width: 600px; margin: 0 auto;" >
                    <tr>
                        <td>
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="border-bottom: 1px dashed ${emailBuilder.value[key].border_color}; padding: 0 32px; width: 100%; max-width: 600px; margin: 0 auto;">
                                <tr>
                                    <td style="font-size: 15px;padding: 8px 0 12px 0; text-align: center;">Add To Calendar</td>  
                                </tr> 
                                <tr>
                                    <td style="font-size: 15px;padding: 8px 0 8px 0; text-align: center;">
                                        <div><a style="margin-right: 8px;" href="{{booking.add_to_calendar.google}}" target="_blank"><img src="'.esc_url($google_calendar_image_url).'" alt="icon" /></a><a style="margin-right: 8px;" href="{{booking.add_to_calendar.outlook}}" target="_blank"><img src="'.esc_url($outlook_calendar_image_url).'" alt="icon" /></a><a style="margin-right: 8px;" href="{{booking.add_to_calendar.yahoo}}" target="_blank"><img src="'.esc_url($yahoo_calendar_image_url).'" alt="icon" /></a><a style="margin-right: 8px;" href="{{booking.add_to_calendar.other}}" target="_blank"><img src="'.esc_url($other_calendar_image_url).'" alt="icon" /></a></div>
                                    </td>  
                                </tr> 
                            </table>
                        </td>
                    </tr>
                </table>

                <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#121D13" style="padding: 16px 32px; border-radius: 0px 0px 8px 8px; width: 100%; max-width: 600px; margin: 0 auto;">
                    <tbody>
                        <tr>
                            <td align="left">
                                <span style="color: #FFF; font-size: 16.5px; font-weight: bold;">HydraBooking</span>
                                <p style="color: #FFF; font-size: 13px; margin: 8px 0 0 0;">The WordPress Plugin to <br>Supercharge Your Scheduling</p>
                            </td>
                            <td align="right" class="social" style="vertical-align: baseline;">
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                    <tbody>
                                        <tr>
                                            <td style="padding-bottom: 4px;">
                                                <a href="#" style="text-decoration: none; color: #FFF;">Facebook</a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding-bottom: 4px;">
                                                <a href="#" style="text-decoration: none; color: #FFF;">Twitter</a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding-bottom: 4px;">
                                                <a href="#" style="text-decoration: none; color: #FFF;">Youtube</a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>
            ',
            'builder' => array(
                array(
                    'id' => 'header',
                    'order' => 0,
                    'status' => 1,
                    'title' => 'Header',
                    'content' => '<span style="color: #FFF; font-size: 22px; font-weight: 600; margin: 0;">HydraBooking</span>',
                    'logo' => '',
                    'background' => '#215732'
                ),
                array(
                    'id' => 'gratitude',
                    'order' => 1,
                    'status' => 1,
                    'title' => 'Greetings',
                    'content' => '<p style="font-weight: bold;margin: 0; font-size: 17px;">Hey {{attendee.name}},</p><p style="font-weight: bold; margin: 8px 0 0 0; font-size: 17px;">Booking Cancellation</p>'
                ),
                array(
                    'id' => 'meeting_details',
                    'order' => 2,
                    'status' => 1,
                    'title' => 'Meeting Details',
                    'border_color' => '#C0D8C4',
                    'content' => array(
                        'data_time' => array(
                            'status' => 1,
                            'title' => 'Date & Time:',
                            'content' => '<strong>{{meeting.date}} - {{meeting.time}}</strong> <br>Host time: {{booking.start_date_time_for_host}} - {{booking.full_start_end_host_timezone}}'
                        ),
                        'host' => array(
                            'status' => 1,
                            'title' => 'Host:',
                            'content' => '<strong>{{host.name}}</strong>'
                        ),
                        'about' => array(
                            'status' => 1,
                            'title' => 'About:',
                            'content' => '<strong>{{meeting.title}}</strong>'
                        ),
                        'description' => array(
                            'status' => 1,
                            'title' => 'Description:',
                            'content' => '{{meeting.content}}'
                        ),
                        'location' => array(
                            'status' => 1,
                            'title' => 'Location:',
                            'content' => '<strong>{{booking.location_details_html}}</strong>'
                        ),
                    )
                ),
                array(
                    'id' => 'host_details',
                    'order' => 3,
                    'status' => 1,
                    'title' => 'Host Details',
                    'border_color' => '#C0D8C4',
                    'content' => array(
                        'name' => array(
                            'status' => 1,
                            'title' => 'Name:',
                            'content' => '<strong>{{host.name}}</strong>'
                        ),
                        'email' => array(
                            'status' => 1,
                            'title' => 'Email:',
                            'content' => '<strong><a href="" style="text-decoration: none; color: #2E6B38;">{{host.email}}</a></strong>'
                        ),
                        'phone' => array(
                            'status' => 1,
                            'title' => 'Phone:',
                            'content' => '<strong><a href="" style="text-decoration: none; color: #2E6B38;">{{host.phone}}</a></strong>'
                        ),
                    )
                ),
                array(
                    'id' => 'instructions',
                    'order' => 4,
                    'status' => 1,
                    'title' => 'Instructions',
                    'content' => '<ul><li>Please <strong>join the event five minutes before the event starts</strong> based on your time zone.</li><li>Ensure you have a good internet connection, a quality camera, and a quiet space.</li></ul>',
                ),
                array(
                    'id' => 'cancel_reschedule',
                    'order' => 5,
                    'status' => 1,
                    'title' => 'Buttons',
                    'border_color' => '#C0D8C4',
                    'content' => array(
                        'description' => array(
                            'status' => 1,
                            'content' => 'You can cancel or reschedule this event for any reason.'
                        ),
                        'cancel' => array(
                            'status' => 1,
                            'content' => '{{booking.cancel_link}}'
                        ),
                        'reschedule' => array(
                            'status' => 1,
                            'content' => '{{booking.rescheduled_link}}'
                        ),
                    )
                ),
                 array(
                    'id' => 'add_to_calendar',
                    'order' => 6,
                    'status' => 1,
                    'title' => 'Add to Calendar',
                    'border_color' => '#C0D8C4',
                    'content' => array(
                        'description' => array(
                            'status' => 1,
                            'content' => 'Add to calendar'
                        ),
                        'list' => array(
                            'status' => 1,
                            'content' => $add_to_calendar_content
                        ), 
                    )
                ),
                array(
                    'id' => 'footer',
                    'order' => 6,
                    'status' => 1,
                    'title' => 'Footer',
                    'content' => array(
                        'description' => array(
                            'status' => 1,
                            'content' => '<span style="color: #FFF; font-size: 16.5px; font-weight: bold;">HydraBooking</span><p style="color: #FFF; font-size: 13px; margin: 8px 0 0 0;">The WordPress Plugin to <br>Supercharge Your Scheduling</p>'
                        ),
                        'social' => array(
                            'status' => 1,
                            'data' => array(
                                array(
                                    'title' => 'Facebook',
                                    'url' => '#'
                                ),
                                array(
                                    'title' => 'Twitter',
                                    'url' => '#'
                                ),
                                array(
                                    'title' => 'Youtube',
                                    'url' => '#'
                                ),
                            )
                        )
                    )
                ),
            )
        );
        
        $notification['host']['booking_pending'] = array(
            'status' => 1,
            'template' => 'default',
            'from' =>  '{{wp.admin_email}}',
            'subject' => 'Pending Booking between {{host.name}} & {{attendee.name}}',
            'body' =>  ' 
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr>
                <td bgcolor="#215732" style="padding: 16px 32px; text-align: left; border-radius: 8px 8px 0 0;">
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                        <tbody><tr><td style="vertical-align: middle;">
                                    <span style="color: #FFF; font-size: 22px; font-weight: 600; margin: 0;">HydraBooking</span>
                                </td></tr>
                    </tbody></table>
                </td>
            </tr></tbody></table>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 32px;width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;">
            <tbody><tr><td><p style="font-weight: bold;margin: 0; font-size: 17px;">Hey {{attendee.name}},</p><p style="font-weight: bold; margin: 8px 0 0 0; font-size: 17px;">A new booking with {{host.name}} was pending.</p></td></tr> </tbody></table></td></tr></tbody></table>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 32px;width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;">
                <tbody><tr>
                    <td>
                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border: 2px dashed #C0D8C4; border-radius: 8px; padding: 24px; background: #fff;">
                            <tbody><tr><td style="font-weight: bold; font-size: 16px;">Meeting Details</td></tr>
            
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($calendar_image_url) . '" alt="data_time" style="float: left;margin-right: 8px;">
                                            Date &amp; Time:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{meeting.date}} - {{meeting.time}}</strong> <br>Host time: {{booking.start_date_time_for_host}} - {{booking.full_start_end_host_timezone}}
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($user_image_url) . '" alt="host" style="float: left;margin-right: 8px;">
                                            Host:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{host.name}}</strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($meeting_image_url) . '" alt="about" style="float: left;margin-right: 8px;">
                                            About:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{meeting.title}}</strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($file_image_url) . '" alt="description" style="float: left;margin-right: 8px;">
                                            Description:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            {{meeting.content}}
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($location_image_url) . '" alt="location" style="float: left;margin-right: 8px;">
                                            Location:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{booking.location_details_html}}</strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    </tbody></table></td></tr> </tbody></table></td></tr></tbody></table>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 32px;width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;">
                <tbody><tr>
                    <td>
                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border: 2px dashed #C0D8C4; border-radius: 8px; padding: 24px; background: #fff;">
                            <tbody><tr><td style="font-weight: bold; font-size: 16px;">Host Details</td></tr>
            
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($user_image_url) . '" alt="name" style="float: left;margin-right: 8px;">
                                            Name:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{host.name}}</strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($mail_image_url) . '" alt="email" style="float: left;margin-right: 8px;">
                                            Email:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong><a href="" style="text-decoration: none; color: #2E6B38;">{{host.email}}</a></strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($phone_image_url) . '" alt="phone" style="float: left;margin-right: 8px;">
                                            Phone:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong><a href="" style="text-decoration: none; color: #2E6B38;">{{host.phone}}</a></strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    </tbody></table></td></tr> </tbody></table></td></tr></tbody></table> <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 0;width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="padding: 0 32px; width: 100%; max-width: 600px; margin: 0 auto;">
                <tbody><tr>
                    <td style="font-weight: bold; font-size: 17px; padding-bottom: 24px;" bgcolor="#fff">Instructions</td>
                </tr>
                <tr>
                    <td style="font-size: 15px;"><ul><li>Please <strong>join the event five minutes before the event starts</strong> based on your time zone.</li><li>Ensure you have a good internet connection, a quality camera, and a quiet space.</li></ul></td>
                </tr></tbody></table></td></tr></tbody></table> <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 0;width: 100%; max-width: 600px; margin: 0 auto;" class="tfhb-cancel-reschedule-btn"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="border-top: 1px dashed #C0D8C4;border-bottom: 1px dashed #C0D8C4; padding: 0 32px; width: 100%; max-width: 600px; margin: 0 auto;"> <tbody><tr>
                        <td style="font-size: 15px;padding: 24px 0 16px 0;">You can cancel or reschedule this event for any reason.</td>
                    </tr><tr>
                    <td style="font-size: 15px; padding-bottom: 24px;"><a href="{{booking.cancel_link}}" class="tfhb-cancel-btn" style=" padding: 8px 24px; border-radius: 8px;border: 1px solid #C0D8C4;background: #FFF; color: #273F2B;display: inline-block;text-decoration: none;">Cancel</a><a href="{{booking.rescheduled_link}}" class="tfhb-reschedule-btn" style=" padding: 8px 24px; border-radius: 8px;border: 1px solid #C0D8C4;background: #FFF; color: #273F2B;display: inline-block; margin-left: 16px;text-decoration: none;">Reschedule</a></td></tr></tbody></table></td></tr></tbody></table>
                
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#121D13" style="padding: 16px 32px;border-radius: 0px 0px 8px 8px; width: 100%; max-width: 600px; margin: 0 auto;">
                        <tbody><tr><td align="left">
                                <span style="color: #FFF; font-size: 16.5px; font-weight: bold;">HydraBooking</span><p style="color: #FFF; font-size: 13px; margin: 8px 0 0 0;">The WordPress Plugin to <br>Supercharge Your Scheduling</p>
                            </td><td align="right" class="social" style="vertical-align: baseline;">
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0"><tbody><tr><td style="padding-bottom: 4px;">
                                            <a href="#" style="text-decoration: none; color: #FFF;">
                                                Facebook
                                            </a>
                                        </td></tr><tr><td style="padding-bottom: 4px;">
                                            <a href="#" style="text-decoration: none; color: #FFF;">
                                                Twitter
                                            </a>
                                        </td></tr><tr><td style="padding-bottom: 4px;">
                                            <a href="#" style="text-decoration: none; color: #FFF;">
                                                Youtube
                                            </a>
                                        </td></tr>
                                </tbody></table>
                            </td></tr>
                    </tbody></table>
            ',
            'builder' => array(
                array(
                    'id' => 'header',
                    'order' => 0,
                    'status' => 1,
                    'title' => 'Header',
                    'content' => '<span style="color: #FFF; font-size: 22px; font-weight: 600; margin: 0;">HydraBooking</span>',
                    'logo' => '',
                    'background' => '#215732'
                ),
                array(
                    'id' => 'gratitude',
                    'order' => 1,
                    'status' => 1,
                    'title' => 'Greetings',
                    'content' => '<p style="font-weight: bold;margin: 0; font-size: 17px;">Hey {{attendee.name}},</p><p style="font-weight: bold; margin: 8px 0 0 0; font-size: 17px;">A new booking with {{host.name}} was pending.</p>'
                ),
                array(
                    'id' => 'meeting_details',
                    'order' => 2,
                    'status' => 1,
                    'title' => 'Meeting Details',
                    'border_color' => '#C0D8C4',
                    'content' => array(
                        'data_time' => array(
                            'status' => 1,
                            'title' => 'Date & Time:',
                            'content' => '<strong>{{meeting.date}} - {{meeting.time}}</strong> <br>Host time: {{booking.start_date_time_for_host}} - {{booking.full_start_end_host_timezone}}'
                        ),
                        'host' => array(
                            'status' => 1,
                            'title' => 'Host:',
                            'content' => '<strong>{{host.name}}</strong>'
                        ),
                        'about' => array(
                            'status' => 1,
                            'title' => 'About:',
                            'content' => '<strong>{{meeting.title}}</strong>'
                        ),
                        'description' => array(
                            'status' => 1,
                            'title' => 'Description:',
                            'content' => '{{meeting.content}}'
                        ),
                        'location' => array(
                            'status' => 1,
                            'title' => 'Location:',
                            'content' => '<strong>{{booking.location_details_html}}</strong>'
                        ),
                    )
                ),
                array(
                    'id' => 'host_details',
                    'order' => 3,
                    'status' => 1,
                    'title' => 'Host Details',
                    'border_color' => '#C0D8C4',
                    'content' => array(
                        'name' => array(
                            'status' => 1,
                            'title' => 'Name:',
                            'content' => '<strong>{{host.name}}</strong>'
                        ),
                        'email' => array(
                            'status' => 1,
                            'title' => 'Email:',
                            'content' => '<strong><a href="" style="text-decoration: none; color: #2E6B38;">{{host.email}}</a></strong>'
                        ),
                        'phone' => array(
                            'status' => 1,
                            'title' => 'Phone:',
                            'content' => '<strong><a href="" style="text-decoration: none; color: #2E6B38;">{{host.phone}}</a></strong>'
                        ),
                    )
                ),
                array(
                    'id' => 'instructions',
                    'order' => 4,
                    'status' => 1,
                    'title' => 'Instructions',
                    'content' => '<ul><li>Please <strong>join the event five minutes before the event starts</strong> based on your time zone.</li><li>Ensure you have a good internet connection, a quality camera, and a quiet space.</li></ul>',
                ),
                array(
                    'id' => 'cancel_reschedule',
                    'order' => 5,
                    'status' => 1,
                    'title' => 'Buttons',
                    'border_color' => '#C0D8C4',
                    'content' => array(
                        'description' => array(
                            'status' => 1,
                            'content' => 'You can cancel or reschedule this event for any reason.'
                        ),
                        'cancel' => array(
                            'status' => 1,
                            'content' => '{{booking.cancel_link}}'
                        ),
                        'reschedule' => array(
                            'status' => 1,
                            'content' => '{{booking.rescheduled_link}}'
                        ),
                    )
                ),
                 array(
                    'id' => 'add_to_calendar',
                    'order' => 6,
                    'status' => 1,
                    'title' => 'Add to Calendar',
                    'border_color' => '#C0D8C4',
                    'content' => array(
                        'description' => array(
                            'status' => 1,
                            'content' => 'Add to calendar'
                        ),
                        'list' => array(
                            'status' => 1,
                            'content' => $add_to_calendar_content
                        ), 
                    )
                ),
                array(
                    'id' => 'footer',
                    'order' => 7,
                    'status' => 1,
                    'title' => 'Footer',
                    'content' => array(
                        'description' => array(
                            'status' => 1,
                            'content' => '<span style="color: #FFF; font-size: 16.5px; font-weight: bold;">HydraBooking</span><p style="color: #FFF; font-size: 13px; margin: 8px 0 0 0;">The WordPress Plugin to <br>Supercharge Your Scheduling</p>'
                        ),
                        'social' => array(
                            'status' => 1,
                            'data' => array(
                                array(
                                    'title' => 'Facebook',
                                    'url' => '#'
                                ),
                                array(
                                    'title' => 'Twitter',
                                    'url' => '#'
                                ),
                                array(
                                    'title' => 'Youtube',
                                    'url' => '#'
                                ),
                            )
                        )
                    )
                ),
            )
            
        );
        $notification['host']['booking_reschedule'] = array(
            'status' => 1,
            'template' => 'default',
            'from' => '{{wp.admin_email}}',
            'subject' => 'A booking was rescheduled with {{attendee.name}}',
            'body' =>  '
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr>
                <td bgcolor="#215732" style="padding: 16px 32px; text-align: left; border-radius: 8px 8px 0 0;">
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                        <tbody><tr><td style="vertical-align: middle;">
                                    <span style="color: #FFF; font-size: 22px; font-weight: 600; margin: 0;">HydraBooking</span>
                                </td></tr>
                    </tbody></table>
                </td>
            </tr></tbody></table>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 32px;width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;">
            <tbody><tr><td><p style="font-weight: bold;margin: 0; font-size: 17px;">Hey {{attendee.name}},</p><p style="font-weight: bold; margin: 8px 0 0 0; font-size: 17px;">Booking Rescheduled</p></td></tr> </tbody></table></td></tr></tbody></table>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 32px;width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;">
                <tbody><tr>
                    <td>
                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border: 2px dashed #C0D8C4; border-radius: 8px; padding: 24px; background: #fff;">
                            <tbody><tr><td style="font-weight: bold; font-size: 16px;">Meeting Details</td></tr>
            
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($calendar_image_url) . '" alt="data_time" style="float: left;margin-right: 8px;">
                                            Date &amp; Time:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{meeting.date}} - {{meeting.time}}</strong> <br>Host time: {{booking.start_date_time_for_host}} - {{booking.full_start_end_host_timezone}}
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($user_image_url) . '" alt="host" style="float: left;margin-right: 8px;">
                                            Host:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{host.name}}</strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($meeting_image_url) . '" alt="about" style="float: left;margin-right: 8px;">
                                            About:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{meeting.title}}</strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($file_image_url) . '" alt="description" style="float: left;margin-right: 8px;">
                                            Description:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            {{meeting.content}}
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($location_image_url) . '" alt="location" style="float: left;margin-right: 8px;">
                                            Location:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{booking.location_details_html}}</strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    </tbody></table></td></tr> </tbody></table></td></tr></tbody></table>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 32px;width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;">
                <tbody><tr>
                    <td>
                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border: 2px dashed #C0D8C4; border-radius: 8px; padding: 24px; background: #fff;">
                            <tbody><tr><td style="font-weight: bold; font-size: 16px;">Host Details</td></tr>
            
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($user_image_url) . '" alt="name" style="float: left;margin-right: 8px;">
                                            Name:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{host.name}}</strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($mail_image_url) . '" alt="email" style="float: left;margin-right: 8px;">
                                            Email:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong><a href="" style="text-decoration: none; color: #2E6B38;">{{host.email}}</a></strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($phone_image_url) . '" alt="phone" style="float: left;margin-right: 8px;">
                                            Phone:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong><a href="" style="text-decoration: none; color: #2E6B38;">{{host.phone}}</a></strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    </tbody></table></td></tr> </tbody></table></td></tr></tbody></table> <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 0;width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="padding: 0 32px; width: 100%; max-width: 600px; margin: 0 auto;">
                <tbody><tr>
                    <td style="font-weight: bold; font-size: 17px; padding-bottom: 24px;" bgcolor="#fff">Instructions</td>
                </tr>
                <tr>
                    <td style="font-size: 15px;"><ul><li>Please <strong>join the event five minutes before the event starts</strong> based on your time zone.</li><li>Ensure you have a good internet connection, a quality camera, and a quiet space.</li></ul></td>
                </tr></tbody></table></td></tr></tbody></table> <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 0;width: 100%; max-width: 600px; margin: 0 auto;" class="tfhb-cancel-reschedule-btn"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="border-top: 1px dashed #C0D8C4;border-bottom: 1px dashed #C0D8C4; padding: 0 32px; width: 100%; max-width: 600px; margin: 0 auto;"> <tbody><tr>
                        <td style="font-size: 15px;padding: 24px 0 16px 0;">You can cancel or reschedule this event for any reason.</td>
                    </tr><tr>
                    <td style="font-size: 15px; padding-bottom: 24px;"><a href="{{booking.cancel_link}}" class="tfhb-cancel-btn" style=" padding: 8px 24px; border-radius: 8px;border: 1px solid #C0D8C4;background: #FFF; color: #273F2B;display: inline-block;text-decoration: none;">Cancel</a><a href="{{booking.rescheduled_link}}" class="tfhb-reschedule-btn" style=" padding: 8px 24px; border-radius: 8px;border: 1px solid #C0D8C4;background: #FFF; color: #273F2B;display: inline-block; margin-left: 16px;text-decoration: none;">Reschedule</a></td></tr></tbody></table></td></tr></tbody></table>
                
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#121D13" style="padding: 16px 32px;border-radius: 0px 0px 8px 8px; width: 100%; max-width: 600px; margin: 0 auto;">
                        <tbody><tr><td align="left">
                                <span style="color: #FFF; font-size: 16.5px; font-weight: bold;">HydraBooking</span><p style="color: #FFF; font-size: 13px; margin: 8px 0 0 0;">The WordPress Plugin to <br>Supercharge Your Scheduling</p>
                            </td><td align="right" class="social" style="vertical-align: baseline;">
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0"><tbody><tr><td style="padding-bottom: 4px;">
                                            <a href="#" style="text-decoration: none; color: #FFF;">
                                                Facebook
                                            </a>
                                        </td></tr><tr><td style="padding-bottom: 4px;">
                                            <a href="#" style="text-decoration: none; color: #FFF;">
                                                Twitter
                                            </a>
                                        </td></tr><tr><td style="padding-bottom: 4px;">
                                            <a href="#" style="text-decoration: none; color: #FFF;">
                                                Youtube
                                            </a>
                                        </td></tr>
                                </tbody></table>
                            </td></tr>
                    </tbody></table>
            ',
            'builder' => array(
                array(
                    'id' => 'header',
                    'order' => 0,
                    'status' => 1,
                    'title' => 'Header',
                    'content' => '<span style="color: #FFF; font-size: 22px; font-weight: 600; margin: 0;">HydraBooking</span>',
                    'logo' => '',
                    'background' => '#215732'
                ),
                array(
                    'id' => 'gratitude',
                    'order' => 1,
                    'status' => 1,
                    'title' => 'Greetings',
                    'content' => '<p style="font-weight: bold;margin: 0; font-size: 17px;">Hey {{attendee.name}},</p><p style="font-weight: bold; margin: 8px 0 0 0; font-size: 17px;">Booking Rescheduled</p>'
                ),
                array(
                    'id' => 'meeting_details',
                    'order' => 2,
                    'status' => 1,
                    'title' => 'Meeting Details',
                    'border_color' => '#C0D8C4',
                    'content' => array(
                        'data_time' => array(
                            'status' => 1,
                            'title' => 'Date & Time:',
                            'content' => '<strong>{{meeting.date}} - {{meeting.time}}</strong> <br>Host time: {{booking.start_date_time_for_host}} - {{booking.full_start_end_host_timezone}}'
                        ),
                        'host' => array(
                            'status' => 1,
                            'title' => 'Host:',
                            'content' => '<strong>{{host.name}}</strong>'
                        ),
                        'about' => array(
                            'status' => 1,
                            'title' => 'About:',
                            'content' => '<strong>{{meeting.title}}</strong>'
                        ),
                        'description' => array(
                            'status' => 1,
                            'title' => 'Description:',
                            'content' => '{{meeting.content}}'
                        ),
                        'location' => array(
                            'status' => 1,
                            'title' => 'Location:',
                            'content' => '<strong>{{booking.location_details_html}}</strong>'
                        ),
                    )
                ),
                array(
                    'id' => 'host_details',
                    'order' => 3,
                    'status' => 1,
                    'title' => 'Host Details',
                    'border_color' => '#C0D8C4',
                    'content' => array(
                        'name' => array(
                            'status' => 1,
                            'title' => 'Name:',
                            'content' => '<strong>{{host.name}}</strong>'
                        ),
                        'email' => array(
                            'status' => 1,
                            'title' => 'Email:',
                            'content' => '<strong><a href="" style="text-decoration: none; color: #2E6B38;">{{host.email}}</a></strong>'
                        ),
                        'phone' => array(
                            'status' => 1,
                            'title' => 'Phone:',
                            'content' => '<strong><a href="" style="text-decoration: none; color: #2E6B38;">{{host.phone}}</a></strong>'
                        ),
                    )
                ),
                array(
                    'id' => 'instructions',
                    'order' => 4,
                    'status' => 1,
                    'title' => 'Instructions',
                    'content' => '<ul><li>Please <strong>join the event five minutes before the event starts</strong> based on your time zone.</li><li>Ensure you have a good internet connection, a quality camera, and a quiet space.</li></ul>',
                ),
                array(
                    'id' => 'cancel_reschedule',
                    'order' => 5,
                    'status' => 1,
                    'title' => 'Buttons',
                    'border_color' => '#C0D8C4',
                    'content' => array(
                        'description' => array(
                            'status' => 1,
                            'content' => 'You can cancel or reschedule this event for any reason.'
                        ),
                        'cancel' => array(
                            'status' => 1,
                            'content' => '{{booking.cancel_link}}'
                        ),
                        'reschedule' => array(
                            'status' => 1,
                            'content' => '{{booking.rescheduled_link}}'
                        ),
                    )
                ),
                 array(
                    'id' => 'add_to_calendar',
                    'order' => 6,
                    'status' => 1,
                    'title' => 'Add to Calendar',
                    'border_color' => '#C0D8C4',
                    'content' => array(
                        'description' => array(
                            'status' => 1,
                            'content' => 'Add to calendar'
                        ),
                        'list' => array(
                            'status' => 1,
                            'content' => '<div><a style="margin-right: 8px;" href="{{booking.add_to_calendar.google}}" target="_blank"><img src="'.esc_url($google_calendar_image_url).'" alt="icon" /></a><a style="margin-right: 8px;" href="{{booking.add_to_calendar.outlook}}" target="_blank"><img src="'.esc_url($calendar_image_url).'" alt="icon" /></a><a style="margin-right: 8px;" href="{{booking.add_to_calendar.yahoo}}" target="_blank"><img src="'.esc_url($calendar_image_url).'" alt="icon" /></a><a style="margin-right: 8px;" href="{{booking.add_to_calendar.other}}" target="_blank"><img src="'.esc_url($calendar_image_url).'" alt="icon" /></a></div>'
                        ), 
                    )
                ),
                array(
                    'id' => 'footer',
                    'order' => 7,
                    'status' => 1,
                    'title' => 'Footer',
                    'content' => array(
                        'description' => array(
                            'status' => 1,
                            'content' => '<span style="color: #FFF; font-size: 16.5px; font-weight: bold;">HydraBooking</span><p style="color: #FFF; font-size: 13px; margin: 8px 0 0 0;">The WordPress Plugin to <br>Supercharge Your Scheduling</p>'
                        ),
                        'social' => array(
                            'status' => 1,
                            'data' => array(
                                array(
                                    'title' => 'Facebook',
                                    'url' => '#'
                                ),
                                array(
                                    'title' => 'Twitter',
                                    'url' => '#'
                                ),
                                array(
                                    'title' => 'Youtube',
                                    'url' => '#'
                                ),
                            )
                        )
                    )
                ),
            )
        );
        $notification['host']['booking_reminder'] = array(
            'status' => 1,
            'template' => 'default',
            'from' => '{{wp.admin_email}}',
            'subject' => 'Meeting Reminder with {{host.name}} @ {{booking.start_date_time_for_host}}', 
            'body' =>  ' <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr>
                <td bgcolor="#215732" style="padding: 16px 32px; text-align: left; border-radius: 8px 8px 0 0;">
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                        <tbody><tr><td style="vertical-align: middle;">
                                    <span style="color: #FFF; font-size: 22px; font-weight: 600; margin: 0;">HydraBooking</span>
                                </td></tr>
                    </tbody></table>
                </td>
            </tr></tbody></table>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 32px;width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;">
            <tbody><tr><td><p style="font-weight: bold;margin: 0; font-size: 17px;">Hey {{attendee.name}},</p><p style="font-weight: bold; margin: 8px 0 0 0; font-size: 17px;">Reminder: Your meeting will start in {{booking.start_date_time_for_host}}</p></td></tr> </tbody></table></td></tr></tbody></table>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 32px;width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;">
                <tbody><tr>
                    <td>
                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border: 2px dashed #C0D8C4; border-radius: 8px; padding: 24px; background: #fff;">
                            <tbody><tr><td style="font-weight: bold; font-size: 16px;">Meeting Details</td></tr>
            
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($calendar_image_url) . '" alt="data_time" style="float: left;margin-right: 8px;">
                                            Date &amp; Time:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{meeting.date}} - {{meeting.time}}</strong> <br>Host time: {{booking.start_date_time_for_host}} - {{booking.full_start_end_host_timezone}}
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($user_image_url) . '" alt="host" style="float: left;margin-right: 8px;">
                                            Host:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{host.name}}</strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($meeting_image_url) . '" alt="about" style="float: left;margin-right: 8px;">
                                            About:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{meeting.title}}</strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($file_image_url) . '" alt="description" style="float: left;margin-right: 8px;">
                                            Description:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            {{meeting.content}}
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($location_image_url) . '" alt="location" style="float: left;margin-right: 8px;">
                                            Location:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{booking.location_details_html}}</strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    </tbody></table></td></tr> </tbody></table></td></tr></tbody></table>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 32px;width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;">
                <tbody><tr>
                    <td>
                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border: 2px dashed #C0D8C4; border-radius: 8px; padding: 24px; background: #fff;">
                            <tbody><tr><td style="font-weight: bold; font-size: 16px;">Host Details</td></tr>
            
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($user_image_url) . '" alt="name" style="float: left;margin-right: 8px;">
                                            Name:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{host.name}}</strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($mail_image_url) . '" alt="email" style="float: left;margin-right: 8px;">
                                            Email:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong><a href="" style="text-decoration: none; color: #2E6B38;">{{host.email}}</a></strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($phone_image_url) . '" alt="phone" style="float: left;margin-right: 8px;">
                                            Phone:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong><a href="" style="text-decoration: none; color: #2E6B38;">{{host.phone}}</a></strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    </tbody></table></td></tr> </tbody></table></td></tr></tbody></table> <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 0;width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="padding: 0 32px; width: 100%; max-width: 600px; margin: 0 auto;">
                <tbody><tr>
                    <td style="font-weight: bold; font-size: 17px; padding-bottom: 24px;" bgcolor="#fff">Instructions</td>
                </tr>
                <tr>
                    <td style="font-size: 15px;"><ul><li>Please <strong>join the event five minutes before the event starts</strong> based on your time zone.</li><li>Ensure you have a good internet connection, a quality camera, and a quiet space.</li></ul></td>
                </tr></tbody></table></td></tr></tbody></table> <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 0;width: 100%; max-width: 600px; margin: 0 auto;" class="tfhb-cancel-reschedule-btn"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="border-top: 1px dashed #C0D8C4;border-bottom: 1px dashed #C0D8C4; padding: 0 32px; width: 100%; max-width: 600px; margin: 0 auto;"> <tbody><tr>
                        <td style="font-size: 15px;padding: 24px 0 16px 0;">You can cancel or reschedule this event for any reason.</td>
                    </tr><tr>
                    <td style="font-size: 15px; padding-bottom: 24px;"><a href="{{booking.cancel_link}}" class="tfhb-cancel-btn" style=" padding: 8px 24px; border-radius: 8px;border: 1px solid #C0D8C4;background: #FFF; color: #273F2B;display: inline-block;text-decoration: none;">Cancel</a><a href="{{booking.rescheduled_link}}" class="tfhb-reschedule-btn" style=" padding: 8px 24px; border-radius: 8px;border: 1px solid #C0D8C4;background: #FFF; color: #273F2B;display: inline-block; margin-left: 16px;text-decoration: none;">Reschedule</a></td></tr></tbody></table></td></tr></tbody></table>
                
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#121D13" style="padding: 16px 32px;border-radius: 0px 0px 8px 8px; width: 100%; max-width: 600px; margin: 0 auto;">
                        <tbody><tr><td align="left">
                                <span style="color: #FFF; font-size: 16.5px; font-weight: bold;">HydraBooking</span><p style="color: #FFF; font-size: 13px; margin: 8px 0 0 0;">The WordPress Plugin to <br>Supercharge Your Scheduling</p>
                            </td><td align="right" class="social" style="vertical-align: baseline;">
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0"><tbody><tr><td style="padding-bottom: 4px;">
                                            <a href="#" style="text-decoration: none; color: #FFF;">
                                                Facebook
                                            </a>
                                        </td></tr><tr><td style="padding-bottom: 4px;">
                                            <a href="#" style="text-decoration: none; color: #FFF;">
                                                Twitter
                                            </a>
                                        </td></tr><tr><td style="padding-bottom: 4px;">
                                            <a href="#" style="text-decoration: none; color: #FFF;">
                                                Youtube
                                            </a>
                                        </td></tr>
                                </tbody></table>
                            </td></tr>
                    </tbody></table>
            ',
            'builder' => array(
                array(
                    'id' => 'header',
                    'order' => 0,
                    'status' => 1,
                    'title' => 'Header',
                    'content' => '<span style="color: #FFF; font-size: 22px; font-weight: 600; margin: 0;">HydraBooking</span>',
                    'logo' => '',
                    'background' => '#215732'
                ),
                array(
                    'id' => 'gratitude',
                    'order' => 1,
                    'status' => 1,
                    'title' => 'Greetings',
                    'content' => '<p style="font-weight: bold;margin: 0; font-size: 17px;">Hey {{attendee.name}},</p><p style="font-weight: bold; margin: 8px 0 0 0; font-size: 17px;">Reminder: Your meeting will start in {{booking.start_date_time_for_host}}</p>'
                ),
                array(
                    'id' => 'meeting_details',
                    'order' => 2,
                    'status' => 1,
                    'title' => 'Meeting Details',
                    'border_color' => '#C0D8C4',
                    'content' => array(
                        'data_time' => array(
                            'status' => 1,
                            'title' => 'Date & Time:',
                            'content' => '<strong>{{meeting.date}} - {{meeting.time}}</strong> <br>Host time: {{booking.start_date_time_for_host}} - {{booking.full_start_end_host_timezone}}'
                        ),
                        'host' => array(
                            'status' => 1,
                            'title' => 'Host:',
                            'content' => '<strong>{{host.name}}</strong>'
                        ),
                        'about' => array(
                            'status' => 1,
                            'title' => 'About:',
                            'content' => '<strong>{{meeting.title}}</strong>'
                        ),
                        'description' => array(
                            'status' => 1,
                            'title' => 'Description:',
                            'content' => '{{meeting.content}}'
                        ),
                        'location' => array(
                            'status' => 1,
                            'title' => 'Location:',
                            'content' => '<strong>{{booking.location_details_html}}</strong>'
                        ),
                    )
                ),
                array(
                    'id' => 'host_details',
                    'order' => 3,
                    'status' => 1,
                    'title' => 'Host Details',
                    'border_color' => '#C0D8C4',
                    'content' => array(
                        'name' => array(
                            'status' => 1,
                            'title' => 'Name:',
                            'content' => '<strong>{{host.name}}</strong>'
                        ),
                        'email' => array(
                            'status' => 1,
                            'title' => 'Email:',
                            'content' => '<strong><a href="" style="text-decoration: none; color: #2E6B38;">{{host.email}}</a></strong>'
                        ),
                        'phone' => array(
                            'status' => 1,
                            'title' => 'Phone:',
                            'content' => '<strong><a href="" style="text-decoration: none; color: #2E6B38;">{{host.phone}}</a></strong>'
                        ),
                    )
                ),
                array(
                    'id' => 'instructions',
                    'order' => 4,
                    'status' => 1,
                    'title' => 'Instructions',
                    'content' => '<ul><li>Please <strong>join the event five minutes before the event starts</strong> based on your time zone.</li><li>Ensure you have a good internet connection, a quality camera, and a quiet space.</li></ul>',
                ),
                array(
                    'id' => 'cancel_reschedule',
                    'order' => 5,
                    'status' => 1,
                    'title' => 'Buttons',
                    'border_color' => '#C0D8C4',
                    'content' => array(
                        'description' => array(
                            'status' => 1,
                            'content' => 'You can cancel or reschedule this event for any reason.'
                        ),
                        'cancel' => array(
                            'status' => 1,
                            'content' => '{{booking.cancel_link}}'
                        ),
                        'reschedule' => array(
                            'status' => 1,
                            'content' => '{{booking.rescheduled_link}}'
                        ),
                    )
                ),
                 array(
                    'id' => 'add_to_calendar',
                    'order' => 6,
                    'status' => 1,
                    'title' => 'Add to Calendar',
                    'border_color' => '#C0D8C4',
                    'content' => array(
                        'description' => array(
                            'status' => 1,
                            'content' => 'Add to calendar'
                        ),
                        'list' => array(
                            'status' => 1,
                            'content' => $add_to_calendar_content
                        ), 
                    )
                ),
                array(
                    'id' => 'footer',
                    'order' => 7,
                    'status' => 1,
                    'title' => 'Footer',
                    'content' => array(
                        'description' => array(
                            'status' => 1,
                            'content' => '<span style="color: #FFF; font-size: 16.5px; font-weight: bold;">HydraBooking</span><p style="color: #FFF; font-size: 13px; margin: 8px 0 0 0;">The WordPress Plugin to <br>Supercharge Your Scheduling</p>'
                        ),
                        'social' => array(
                            'status' => 1,
                            'data' => array(
                                array(
                                    'title' => 'Facebook',
                                    'url' => '#'
                                ),
                                array(
                                    'title' => 'Twitter',
                                    'url' => '#'
                                ),
                                array(
                                    'title' => 'Youtube',
                                    'url' => '#'
                                ),
                            )
                        )
                    )
                ),
            )
        );

        // Attendee Notification
        $notification['attendee']['booking_confirmation'] = array(
            'status' => 1,
            'template' => 'default',
            'from' => '{{wp.admin_email}}',
            'subject' => 'Booking Confirmation between {{host.name}} & {{attendee.name}}',
            'body' =>  '<table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;">
                <tbody>
                    <tr>
                        <td bgcolor="#215732" style="padding: 16px 32px; text-align: left; border-radius: 8px 8px 0 0;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                <tbody>
                                    <tr>
                                        <td style="vertical-align: middle;">
                                            <span style="color: #FFF; font-size: 22px; font-weight: 600; margin: 0;">HydraBooking</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 32px; width: 100%; max-width: 600px; margin: 0 auto;">
                <tbody>
                    <tr>
                        <td>
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;">
                                <tbody>
                                    <tr>
                                        <td>
                                            <p style="font-weight: bold; margin: 0; font-size: 17px;">Hey {{attendee.name}},</p>
                                            <p style="font-weight: bold; margin: 8px 0 0 0; font-size: 17px;">Your booking has been scheduled</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 32px;width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;">
                <tbody><tr>
                    <td>
                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border: 2px dashed #C0D8C4; border-radius: 8px; padding: 24px; background: #fff;">
                            <tbody><tr><td style="font-weight: bold; font-size: 16px;">Meeting Details</td></tr>
            
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($calendar_image_url) . '" alt="data_time" style="float: left;margin-right: 8px;">
                                            Date &amp; Time:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{meeting.date}} - {{meeting.time}}</strong> <br>Host time: {{booking.start_date_time_for_host}} - {{booking.full_start_end_host_timezone}}
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($user_image_url) . '" alt="host" style="float: left;margin-right: 8px;">
                                            Host:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{host.name}}</strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($meeting_image_url) . '" alt="about" style="float: left;margin-right: 8px;">
                                            About:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{meeting.title}}</strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($file_image_url) . '" alt="description" style="float: left;margin-right: 8px;">
                                            Description:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            {{meeting.content}}
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($location_image_url) . '" alt="location" style="float: left;margin-right: 8px;">
                                            Location:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{booking.location_details_html}}</strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    </tbody></table></td></tr> </tbody></table></td></tr></tbody></table>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 32px;width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;">
                <tbody><tr>
                    <td>
                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border: 2px dashed #C0D8C4; border-radius: 8px; padding: 24px; background: #fff;">
                            <tbody><tr><td style="font-weight: bold; font-size: 16px;">Host Details</td></tr>
            
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($user_image_url) . '" alt="name" style="float: left;margin-right: 8px;">
                                            Name:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{host.name}}</strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($mail_image_url) . '" alt="email" style="float: left;margin-right: 8px;">
                                            Email:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong><a href="" style="text-decoration: none; color: #2E6B38;">{{host.email}}</a></strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($phone_image_url) . '" alt="phone" style="float: left;margin-right: 8px;">
                                            Phone:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong><a href="" style="text-decoration: none; color: #2E6B38;">{{host.phone}}</a></strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    </tbody></table></td></tr> </tbody></table></td></tr></tbody></table> <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 0;width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="padding: 0 32px; width: 100%; max-width: 600px; margin: 0 auto;">
                <tbody><tr>
                    <td style="font-weight: bold; font-size: 17px; padding-bottom: 24px;" bgcolor="#fff">Instructions</td>
                </tr>
                <tr>
                    <td style="font-size: 15px;"><ul><li>Please <strong>join the event five minutes before the event starts</strong> based on your time zone.</li><li>Ensure you have a good internet connection, a quality camera, and a quiet space.</li></ul></td>
                </tr></tbody></table></td></tr></tbody></table> <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 0;width: 100%; max-width: 600px; margin: 0 auto;" class="tfhb-cancel-reschedule-btn"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="border-top: 1px dashed #C0D8C4;border-bottom: 1px dashed #C0D8C4; padding: 0 32px; width: 100%; max-width: 600px; margin: 0 auto;"> <tbody><tr>
                        <td style="font-size: 15px;padding: 24px 0 16px 0;">You can cancel or reschedule this event for any reason.</td>
                    </tr><tr>
                    <td style="font-size: 15px; padding-bottom: 24px;"><a href="{{booking.cancel_link}}" class="tfhb-cancel-btn" style=" padding: 8px 24px; border-radius: 8px;border: 1px solid #C0D8C4;background: #FFF; color: #273F2B;display: inline-block;text-decoration: none;">Cancel</a><a href="{{booking.rescheduled_link}}" class="tfhb-reschedule-btn" style=" padding: 8px 24px; border-radius: 8px;border: 1px solid #C0D8C4;background: #FFF; color: #273F2B;display: inline-block; margin-left: 16px;text-decoration: none;">Reschedule</a></td></tr></tbody></table></td></tr></tbody></table>
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding-bottom: 16px;width: 100%; max-width: 600px; margin: 0 auto;"  >
                    <tr>
                        <td>
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="border-bottom: 1px dashed #C0D8C4; padding: 0 32px; width: 100%; max-width: 600px; margin: 0 auto;">
                                <tr>
                                    <td style="font-size: 15px;padding: 8px 0 12px 0; text-align: center;">Add To Calendar</td>  
                                </tr> 
                                <tr>
                                    <td style="font-size: 15px;padding: 8px 0 8px 0; text-align: center;">
                                        <div><a style="margin-right: 8px;" href="{{booking.add_to_calendar.google}}" target="_blank"><img src="'.esc_url($google_calendar_image_url).'" alt="icon" /></a><a style="margin-right: 8px;" href="{{booking.add_to_calendar.outlook}}" target="_blank"><img src="'.esc_url($outlook_calendar_image_url).'" alt="icon" /></a><a style="margin-right: 8px;" href="{{booking.add_to_calendar.yahoo}}" target="_blank"><img src="'.esc_url($yahoo_calendar_image_url).'" alt="icon" /></a><a style="margin-right: 8px;" href="{{booking.add_to_calendar.other}}" target="_blank"><img src="'.esc_url($other_calendar_image_url).'" alt="icon" /></a></div>
                                    </td>  
                                </tr> 
                            </table>
                        </td>
                    </tr>
                </table>
                
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#121D13" style="padding: 16px 32px;border-radius: 0px 0px 8px 8px; width: 100%; max-width: 600px; margin: 0 auto;">
                        <tbody><tr><td align="left">
                                <span style="color: #FFF; font-size: 16.5px; font-weight: bold;">HydraBooking</span><p style="color: #FFF; font-size: 13px; margin: 8px 0 0 0;">The WordPress Plugin to <br>Supercharge Your Scheduling</p>
                            </td><td align="right" class="social" style="vertical-align: baseline;">
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0"><tbody><tr><td style="padding-bottom: 4px;">
                                            <a href="#" style="text-decoration: none; color: #FFF;">
                                                Facebook
                                            </a>
                                        </td></tr><tr><td style="padding-bottom: 4px;">
                                            <a href="#" style="text-decoration: none; color: #FFF;">
                                                Twitter
                                            </a>
                                        </td></tr><tr><td style="padding-bottom: 4px;">
                                            <a href="#" style="text-decoration: none; color: #FFF;">
                                                Youtube
                                            </a>
                                        </td></tr>
                                </tbody></table>
                            </td></tr>
                    </tbody></table>
            ',
            'builder' => array(
                array(
                    'id' => 'header',
                    'order' => 0,
                    'status' => 1,
                    'title' => 'Header',
                    'content' => '<span style="color: #FFF; font-size: 22px; font-weight: 600; margin: 0;">HydraBooking</span>',
                    'logo' => '',
                    'background' => '#215732'
                ),
                array(
                    'id' => 'gratitude',
                    'order' => 1,
                    'status' => 1,
                    'title' => 'Greetings',
                    'content' => '<p style="font-weight: bold;margin: 0; font-size: 17px;">Hey {{attendee.name}},</p><p style="font-weight: bold; margin: 8px 0 0 0; font-size: 17px;">Your booking has been scheduled</p>'
                ),
                array(
                    'id' => 'meeting_details',
                    'order' => 2,
                    'status' => 1,
                    'title' => 'Meeting Details',
                    'border_color' => '#C0D8C4',
                    'content' => array(
                        'data_time' => array(
                            'status' => 1,
                            'title' => 'Date & Time:',
                            'content' => '<strong>{{meeting.date}} - {{meeting.time}}</strong> <br>Host time: {{booking.start_date_time_for_host}} - {{booking.full_start_end_host_timezone}}'
                        ),
                        'host' => array(
                            'status' => 1,
                            'title' => 'Host:',
                            'content' => '<strong>{{host.name}}</strong>'
                        ),
                        'about' => array(
                            'status' => 1,
                            'title' => 'About:',
                            'content' => '<strong>{{meeting.title}}</strong>'
                        ),
                        'description' => array(
                            'status' => 1,
                            'title' => 'Description:',
                            'content' => '{{meeting.content}}'
                        ),
                        'location' => array(
                            'status' => 1,
                            'title' => 'Location:',
                            'content' => '<strong>{{booking.location_details_html}}</strong>'
                        ),
                    )
                ),
                array(
                    'id' => 'host_details',
                    'order' => 3,
                    'status' => 1,
                    'title' => 'Host Details',
                    'border_color' => '#C0D8C4',
                    'content' => array(
                        'name' => array(
                            'status' => 1,
                            'title' => 'Name:',
                            'content' => '<strong>{{host.name}}</strong>'
                        ),
                        'email' => array(
                            'status' => 1,
                            'title' => 'Email:',
                            'content' => '<strong><a href="" style="text-decoration: none; color: #2E6B38;">{{host.email}}</a></strong>'
                        ),
                        'phone' => array(
                            'status' => 1,
                            'title' => 'Phone:',
                            'content' => '<strong><a href="" style="text-decoration: none; color: #2E6B38;">{{host.phone}}</a></strong>'
                        ),
                    )
                ),
                array(
                    'id' => 'instructions',
                    'order' => 4,
                    'status' => 1,
                    'title' => 'Instructions',
                    'content' => '<ul><li>Please <strong>join the event five minutes before the event starts</strong> based on your time zone.</li><li>Ensure you have a good internet connection, a quality camera, and a quiet space.</li></ul>',
                ),
                array(
                    'id' => 'cancel_reschedule',
                    'order' => 5,
                    'status' => 1,
                    'title' => 'Buttons',
                    'border_color' => '#C0D8C4',
                    'content' => array(
                        'description' => array(
                            'status' => 1,
                            'content' => 'You can cancel or reschedule this event for any reason.'
                        ),
                        'cancel' => array(
                            'status' => 1,
                            'content' => '{{booking.cancel_link}}'
                        ),
                        'reschedule' => array(
                            'status' => 1,
                            'content' => '{{booking.rescheduled_link}}'
                        ),
                    )
                ),
                array(
                    'id' => 'add_to_calendar',
                    'order' => 6,
                    'status' => 1,
                    'title' => 'Add to Calendar',
                    'border_color' => '#C0D8C4',
                    'content' => array(
                        'description' => array(
                            'status' => 1,
                            'content' => 'Add to calendar'
                        ),
                        'list' => array(
                            'status' => 1,
                            'content' => $add_to_calendar_content
                        ), 
                    )
                ),
                array(
                    'id' => 'footer',
                    'order' => 7,
                    'status' => 1,
                    'title' => 'Footer',
                    'content' => array(
                        'description' => array(
                            'status' => 1,
                            'content' => '<span style="color: #FFF; font-size: 16.5px; font-weight: bold;">HydraBooking</span><p style="color: #FFF; font-size: 13px; margin: 8px 0 0 0;">The WordPress Plugin to <br>Supercharge Your Scheduling</p>'
                        ),
                        'social' => array(
                            'status' => 1,
                            'data' => array(
                                array(
                                    'title' => 'Facebook',
                                    'url' => '#'
                                ),
                                array(
                                    'title' => 'Twitter',
                                    'url' => '#'
                                ),
                                array(
                                    'title' => 'Youtube',
                                    'url' => '#'
                                ),
                            )
                        )
                    )
                ),
            )

        );
        $notification['attendee']['booking_pending'] = array(
            'status' => 1,
            'template' => 'default',
            'from' => '{{wp.admin_email}}',
            'subject' => 'Pending Booking with {{host.name}}',
            'body' =>  '
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr>
                <td bgcolor="#215732" style="padding: 16px 32px; text-align: left; border-radius: 8px 8px 0 0;">
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                        <tbody><tr><td style="vertical-align: middle;">
                                    <span style="color: #FFF; font-size: 22px; font-weight: 600; margin: 0;">HydraBooking</span>
                                </td></tr>
                    </tbody></table>
                </td>
            </tr></tbody></table>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 32px;width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;">
            <tbody><tr><td><p style="font-weight: bold;margin: 0; font-size: 17px;">Hey {{attendee.name}},</p><p style="font-weight: bold; margin: 8px 0 0 0; font-size: 17px;">Your Booking is Pending Approval</p></td></tr> </tbody></table></td></tr></tbody></table>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 32px;width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;">
                <tbody><tr>
                    <td>
                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border: 2px dashed #C0D8C4; border-radius: 8px; padding: 24px; background: #fff;">
                            <tbody><tr><td style="font-weight: bold; font-size: 16px;">Meeting Details</td></tr>
            
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($calendar_image_url) . '" alt="data_time" style="float: left;margin-right: 8px;">
                                            Date &amp; Time:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{meeting.date}} - {{meeting.time}}</strong> <br>Host time: {{booking.start_date_time_for_host}} - {{booking.full_start_end_host_timezone}}
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($user_image_url) . '" alt="host" style="float: left;margin-right: 8px;">
                                            Host:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{host.name}}</strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($meeting_image_url) . '" alt="about" style="float: left;margin-right: 8px;">
                                            About:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{meeting.title}}</strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($file_image_url) . '" alt="description" style="float: left;margin-right: 8px;">
                                            Description:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            {{meeting.content}}
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($location_image_url) . '" alt="location" style="float: left;margin-right: 8px;">
                                            Location:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{booking.location_details_html}}</strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    </tbody></table></td></tr> </tbody></table></td></tr></tbody></table>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 32px;width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;">
                <tbody><tr>
                    <td>
                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border: 2px dashed #C0D8C4; border-radius: 8px; padding: 24px; background: #fff;">
                            <tbody><tr><td style="font-weight: bold; font-size: 16px;">Host Details</td></tr>
            
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($user_image_url) . '" alt="name" style="float: left;margin-right: 8px;">
                                            Name:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{host.name}}</strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($mail_image_url) . '" alt="email" style="float: left;margin-right: 8px;">
                                            Email:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong><a href="" style="text-decoration: none; color: #2E6B38;">{{host.email}}</a></strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($phone_image_url) . '" alt="phone" style="float: left;margin-right: 8px;">
                                            Phone:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong><a href="" style="text-decoration: none; color: #2E6B38;">{{host.phone}}</a></strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    </tbody></table></td></tr> </tbody></table></td></tr></tbody></table> <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 0;width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="padding: 0 32px; width: 100%; max-width: 600px; margin: 0 auto;">
                <tbody><tr>
                    <td style="font-weight: bold; font-size: 17px; padding-bottom: 24px;" bgcolor="#fff">Instructions</td>
                </tr>
                <tr>
                    <td style="font-size: 15px;"><ul><li>Please <strong>join the event five minutes before the event starts</strong> based on your time zone.</li><li>Ensure you have a good internet connection, a quality camera, and a quiet space.</li></ul></td>
                </tr></tbody></table></td></tr></tbody></table> <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 0;width: 100%; max-width: 600px; margin: 0 auto;" class="tfhb-cancel-reschedule-btn"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="border-top: 1px dashed #C0D8C4;border-bottom: 1px dashed #C0D8C4; padding: 0 32px; width: 100%; max-width: 600px; margin: 0 auto;"> <tbody><tr>
                        <td style="font-size: 15px;padding: 24px 0 16px 0;">You can cancel or reschedule this event for any reason.</td>
                    </tr><tr>
                    <td style="font-size: 15px; padding-bottom: 24px;"><a href="{{booking.cancel_link}}" class="tfhb-cancel-btn" style=" padding: 8px 24px; border-radius: 8px;border: 1px solid #C0D8C4;background: #FFF; color: #273F2B;display: inline-block;text-decoration: none;">Cancel</a><a href="{{booking.rescheduled_link}}" class="tfhb-reschedule-btn" style=" padding: 8px 24px; border-radius: 8px;border: 1px solid #C0D8C4;background: #FFF; color: #273F2B;display: inline-block; margin-left: 16px;text-decoration: none;">Reschedule</a></td></tr></tbody></table></td></tr></tbody></table>
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding-bottom: 16px;width: 100%; max-width: 600px; margin: 0 auto;">
                    <tr>
                        <td>
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="border-bottom: 1px dashed #C0D8C4; padding: 0 32px; width: 100%; max-width: 600px; margin: 0 auto;">
                                <tr>
                                    <td style="font-size: 15px;padding: 8px 0 12px 0; text-align: center;">Add To Calendar</td>  
                                </tr> 
                                <tr>
                                    <td style="font-size: 15px;padding: 8px 0 8px 0; text-align: center;">
                                        <div><a style="margin-right: 8px;" href="{{booking.add_to_calendar.google}}" target="_blank"><img src="'.esc_url($google_calendar_image_url).'" alt="icon" /></a><a style="margin-right: 8px;" href="{{booking.add_to_calendar.outlook}}" target="_blank"><img src="'.esc_url($outlook_calendar_image_url).'" alt="icon" /></a><a style="margin-right: 8px;" href="{{booking.add_to_calendar.yahoo}}" target="_blank"><img src="'.esc_url($yahoo_calendar_image_url).'" alt="icon" /></a><a style="margin-right: 8px;" href="{{booking.add_to_calendar.other}}" target="_blank"><img src="'.esc_url($other_calendar_image_url).'" alt="icon" /></a></div>
                                    </td>  
                                </tr> 
                            </table>
                        </td>
                    </tr>
                </table>
                
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#121D13" style="padding: 16px 32px;border-radius: 0px 0px 8px 8px; width: 100%; max-width: 600px; margin: 0 auto;">
                        <tbody><tr><td align="left">
                                <span style="color: #FFF; font-size: 16.5px; font-weight: bold;">HydraBooking</span><p style="color: #FFF; font-size: 13px; margin: 8px 0 0 0;">The WordPress Plugin to <br>Supercharge Your Scheduling</p>
                            </td><td align="right" class="social" style="vertical-align: baseline;">
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0"><tbody><tr><td style="padding-bottom: 4px;">
                                            <a href="#" style="text-decoration: none; color: #FFF;">
                                                Facebook
                                            </a>
                                        </td></tr><tr><td style="padding-bottom: 4px;">
                                            <a href="#" style="text-decoration: none; color: #FFF;">
                                                Twitter
                                            </a>
                                        </td></tr><tr><td style="padding-bottom: 4px;">
                                            <a href="#" style="text-decoration: none; color: #FFF;">
                                                Youtube
                                            </a>
                                        </td></tr>
                                </tbody></table>
                            </td></tr>
                    </tbody></table>
            ',
            'builder' => array(
                array(
                    'id' => 'header',
                    'order' => 0,
                    'status' => 1,
                    'title' => 'Header',
                    'content' => '<span style="color: #FFF; font-size: 22px; font-weight: 600; margin: 0;">HydraBooking</span>',
                    'logo' => '',
                    'background' => '#215732'
                ),
                array(
                    'id' => 'gratitude',
                    'order' => 1,
                    'status' => 1,
                    'title' => 'Greetings',
                    'content' => '<p style="font-weight: bold;margin: 0; font-size: 17px;">Hey {{attendee.name}},</p><p style="font-weight: bold; margin: 8px 0 0 0; font-size: 17px;">Your Booking is Pending Approval</p>'
                ),
                array(
                    'id' => 'meeting_details',
                    'order' => 2,
                    'status' => 1,
                    'title' => 'Meeting Details',
                    'border_color' => '#C0D8C4',
                    'content' => array(
                        'data_time' => array(
                            'status' => 1,
                            'title' => 'Date & Time:',
                            'content' => '<strong>{{meeting.date}} - {{meeting.time}}</strong> <br>Host time: {{booking.start_date_time_for_host}} - {{booking.full_start_end_host_timezone}}'
                        ),
                        'host' => array(
                            'status' => 1,
                            'title' => 'Host:',
                            'content' => '<strong>{{host.name}}</strong>'
                        ),
                        'about' => array(
                            'status' => 1,
                            'title' => 'About:',
                            'content' => '<strong>{{meeting.title}}</strong>'
                        ),
                        'description' => array(
                            'status' => 1,
                            'title' => 'Description:',
                            'content' => '{{meeting.content}}'
                        ),
                        'location' => array(
                            'status' => 1,
                            'title' => 'Location:',
                            'content' => '<strong>{{booking.location_details_html}}</strong>'
                        ),
                    )
                ),
                array(
                    'id' => 'host_details',
                    'order' => 3,
                    'status' => 1,
                    'title' => 'Host Details',
                    'border_color' => '#C0D8C4',
                    'content' => array(
                        'name' => array(
                            'status' => 1,
                            'title' => 'Name:',
                            'content' => '<strong>{{host.name}}</strong>'
                        ),
                        'email' => array(
                            'status' => 1,
                            'title' => 'Email:',
                            'content' => '<strong><a href="" style="text-decoration: none; color: #2E6B38;">{{host.email}}</a></strong>'
                        ),
                        'phone' => array(
                            'status' => 1,
                            'title' => 'Phone:',
                            'content' => '<strong><a href="" style="text-decoration: none; color: #2E6B38;">{{host.phone}}</a></strong>'
                        ),
                    )
                ),
                array(
                    'id' => 'instructions',
                    'order' => 4,
                    'status' => 1,
                    'title' => 'Instructions',
                    'content' => '<ul><li>Please <strong>join the event five minutes before the event starts</strong> based on your time zone.</li><li>Ensure you have a good internet connection, a quality camera, and a quiet space.</li></ul>',
                ),
                array(
                    'id' => 'cancel_reschedule',
                    'order' => 5,
                    'status' => 1,
                    'title' => 'Buttons',
                    'border_color' => '#C0D8C4',
                    'content' => array(
                        'description' => array(
                            'status' => 1,
                            'content' => 'You can cancel or reschedule this event for any reason.'
                        ),
                        'cancel' => array(
                            'status' => 1,
                            'content' => '{{booking.cancel_link}}'
                        ),
                        'reschedule' => array(
                            'status' => 1,
                            'content' => '{{booking.rescheduled_link}}'
                        ),
                    )
                ),
                array(
                    'id' => 'add_to_calendar',
                    'order' => 6,
                    'status' => 1,
                    'title' => 'Add to Calendar',
                    'border_color' => '#C0D8C4',
                    'content' => array(
                        'description' => array(
                            'status' => 1,
                            'content' => 'Add to calendar'
                        ),
                        'list' => array(
                            'status' => 1,
                            'content' => $add_to_calendar_content
                        ), 
                    )
                ),
                array(
                    'id' => 'footer',
                    'order' => 7,
                    'status' => 1,
                    'title' => 'Footer',
                    'content' => array(
                        'description' => array(
                            'status' => 1,
                            'content' => '<span style="color: #FFF; font-size: 16.5px; font-weight: bold;">HydraBooking</span><p style="color: #FFF; font-size: 13px; margin: 8px 0 0 0;">The WordPress Plugin to <br>Supercharge Your Scheduling</p>'
                        ),
                        'social' => array(
                            'status' => 1,
                            'data' => array(
                                array(
                                    'title' => 'Facebook',
                                    'url' => '#'
                                ),
                                array(
                                    'title' => 'Twitter',
                                    'url' => '#'
                                ),
                                array(
                                    'title' => 'Youtube',
                                    'url' => '#'
                                ),
                            )
                        )
                    )
                ),
            )

        );
        $notification['attendee']['booking_cancel'] = array(
            'status' => 1,
            'template' => 'default',
            'from' => '{{wp.admin_email}}', 
            'subject' => 'A booking was cancelled with {{host.name}}',
            'body' =>  ' <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr>
                <td bgcolor="#215732" style="padding: 16px 32px; text-align: left; border-radius: 8px 8px 0 0;">
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                        <tbody><tr><td style="vertical-align: middle;">
                                    <span style="color: #FFF; font-size: 22px; font-weight: 600; margin: 0;">HydraBooking</span>
                                </td></tr>
                    </tbody></table>
                </td>
            </tr></tbody></table>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 32px;width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;">
            <tbody><tr><td><p style="font-weight: bold;margin: 0; font-size: 17px;">Hey {{attendee.name}},</p><p style="font-weight: bold; margin: 8px 0 0 0; font-size: 17px;">Booking Cancellation</p></td></tr> </tbody></table></td></tr></tbody></table>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 32px;width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;">
                <tbody><tr>
                    <td>
                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border: 2px dashed #C0D8C4; border-radius: 8px; padding: 24px; background: #fff;">
                            <tbody><tr><td style="font-weight: bold; font-size: 16px;">Meeting Details</td></tr>
            
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($calendar_image_url) . '" alt="data_time" style="float: left;margin-right: 8px;">
                                            Date &amp; Time:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{meeting.date}} - {{meeting.time}}</strong> <br>Host time: {{booking.start_date_time_for_host}} - {{booking.full_start_end_host_timezone}}
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($user_image_url) . '" alt="host" style="float: left;margin-right: 8px;">
                                            Host:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{host.name}}</strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($meeting_image_url) . '" alt="about" style="float: left;margin-right: 8px;">
                                            About:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{meeting.title}}</strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($file_image_url) . '" alt="description" style="float: left;margin-right: 8px;">
                                            Description:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            {{meeting.content}}
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($location_image_url) . '" alt="location" style="float: left;margin-right: 8px;">
                                            Location:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{booking.location_details_html}}</strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    </tbody></table></td></tr> </tbody></table></td></tr></tbody></table>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 32px;width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;">
                <tbody><tr>
                    <td>
                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border: 2px dashed #C0D8C4; border-radius: 8px; padding: 24px; background: #fff;">
                            <tbody><tr><td style="font-weight: bold; font-size: 16px;">Host Details</td></tr>
            
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($user_image_url) . '" alt="name" style="float: left;margin-right: 8px;">
                                            Name:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{host.name}}</strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($mail_image_url) . '" alt="email" style="float: left;margin-right: 8px;">
                                            Email:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong><a href="" style="text-decoration: none; color: #2E6B38;">{{host.email}}</a></strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($phone_image_url) . '" alt="phone" style="float: left;margin-right: 8px;">
                                            Phone:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong><a href="" style="text-decoration: none; color: #2E6B38;">{{host.phone}}</a></strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    </tbody></table></td></tr> </tbody></table></td></tr></tbody></table> <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 0;width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="padding: 0 32px; width: 100%; max-width: 600px; margin: 0 auto;">
                <tbody><tr>
                    <td style="font-weight: bold; font-size: 17px; padding-bottom: 24px;" bgcolor="#fff">Instructions</td>
                </tr>
                <tr>
                    <td style="font-size: 15px;"><ul><li>Please <strong>join the event five minutes before the event starts</strong> based on your time zone.</li><li>Ensure you have a good internet connection, a quality camera, and a quiet space.</li></ul></td>
                </tr></tbody></table></td></tr></tbody></table> <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 0;width: 100%; max-width: 600px; margin: 0 auto;" class="tfhb-cancel-reschedule-btn"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="border-top: 1px dashed #C0D8C4;border-bottom: 1px dashed #C0D8C4; padding: 0 32px; width: 100%; max-width: 600px; margin: 0 auto;"> <tbody><tr>
                        <td style="font-size: 15px;padding: 24px 0 16px 0;">You can cancel or reschedule this event for any reason.</td>
                    </tr><tr>
                    <td style="font-size: 15px; padding-bottom: 24px;"><a href="{{booking.cancel_link}}" class="tfhb-cancel-btn" style=" padding: 8px 24px; border-radius: 8px;border: 1px solid #C0D8C4;background: #FFF; color: #273F2B;display: inline-block;text-decoration: none;">Cancel</a><a href="{{booking.rescheduled_link}}" class="tfhb-reschedule-btn" style=" padding: 8px 24px; border-radius: 8px;border: 1px solid #C0D8C4;background: #FFF; color: #273F2B;display: inline-block; margin-left: 16px;text-decoration: none;">Reschedule</a></td></tr></tbody></table></td></tr></tbody></table>
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding-bottom: 16px;width: 100%; max-width: 600px; margin: 0 auto;" >
                    <tr>
                        <td>
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="border-bottom: 1px dashed #C0D8C4; padding: 0 32px; width: 100%; max-width: 600px; margin: 0 auto;">
                                <tr>
                                    <td style="font-size: 15px;padding: 8px 0 12px 0; text-align: center;">Add To Calendar</td>  
                                </tr> 
                                <tr>
                                    <td style="font-size: 15px;padding: 8px 0 8px 0; text-align: center;">
                                        <div><a style="margin-right: 8px;" href="{{booking.add_to_calendar.google}}" target="_blank"><img src="'.esc_url($google_calendar_image_url).'" alt="icon" /></a><a style="margin-right: 8px;" href="{{booking.add_to_calendar.outlook}}" target="_blank"><img src="'.esc_url($outlook_calendar_image_url).'" alt="icon" /></a><a style="margin-right: 8px;" href="{{booking.add_to_calendar.yahoo}}" target="_blank"><img src="'.esc_url($yahoo_calendar_image_url).'" alt="icon" /></a><a style="margin-right: 8px;" href="{{booking.add_to_calendar.other}}" target="_blank"><img src="'.esc_url($other_calendar_image_url).'" alt="icon" /></a></div>
                                    </td>  
                                </tr> 
                            </table>
                        </td>
                    </tr>
                </table>
                
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#121D13" style="padding: 16px 32px;border-radius: 0px 0px 8px 8px; width: 100%; max-width: 600px; margin: 0 auto;">
                        <tbody><tr><td align="left">
                                <span style="color: #FFF; font-size: 16.5px; font-weight: bold;">HydraBooking</span><p style="color: #FFF; font-size: 13px; margin: 8px 0 0 0;">The WordPress Plugin to <br>Supercharge Your Scheduling</p>
                            </td><td align="right" class="social" style="vertical-align: baseline;">
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0"><tbody><tr><td style="padding-bottom: 4px;">
                                            <a href="#" style="text-decoration: none; color: #FFF;">
                                                Facebook
                                            </a>
                                        </td></tr><tr><td style="padding-bottom: 4px;">
                                            <a href="#" style="text-decoration: none; color: #FFF;">
                                                Twitter
                                            </a>
                                        </td></tr><tr><td style="padding-bottom: 4px;">
                                            <a href="#" style="text-decoration: none; color: #FFF;">
                                                Youtube
                                            </a>
                                        </td></tr>
                                </tbody></table>
                            </td></tr>
                    </tbody></table>
            ',
            'builder' => array(
                array(
                    'id' => 'header',
                    'order' => 0,
                    'status' => 1,
                    'title' => 'Header',
                    'content' => '<span style="color: #FFF; font-size: 22px; font-weight: 600; margin: 0;">HydraBooking</span>',
                    'logo' => '',
                    'background' => '#215732'
                ),
                array(
                    'id' => 'gratitude',
                    'order' => 1,
                    'status' => 1,
                    'title' => 'Greetings',
                    'content' => '<p style="font-weight: bold;margin: 0; font-size: 17px;">Hey {{attendee.name}},</p><p style="font-weight: bold; margin: 8px 0 0 0; font-size: 17px;">Booking Cancellation</p>'
                ),
                array(
                    'id' => 'meeting_details',
                    'order' => 2,
                    'status' => 1,
                    'title' => 'Meeting Details',
                    'border_color' => '#C0D8C4',
                    'content' => array(
                        'data_time' => array(
                            'status' => 1,
                            'title' => 'Date & Time:',
                            'content' => '<strong>{{meeting.date}} - {{meeting.time}}</strong> <br>Host time: {{booking.start_date_time_for_host}} - {{booking.full_start_end_host_timezone}}'
                        ),
                        'host' => array(
                            'status' => 1,
                            'title' => 'Host:',
                            'content' => '<strong>{{host.name}}</strong>'
                        ),
                        'about' => array(
                            'status' => 1,
                            'title' => 'About:',
                            'content' => '<strong>{{meeting.title}}</strong>'
                        ),
                        'description' => array(
                            'status' => 1,
                            'title' => 'Description:',
                            'content' => '{{meeting.content}}'
                        ),
                        'location' => array(
                            'status' => 1,
                            'title' => 'Location:',
                            'content' => '<strong>{{booking.location_details_html}}</strong>'
                        ),
                    )
                ),
                array(
                    'id' => 'host_details',
                    'order' => 3,
                    'status' => 1,
                    'title' => 'Host Details',
                    'border_color' => '#C0D8C4',
                    'content' => array(
                        'name' => array(
                            'status' => 1,
                            'title' => 'Name:',
                            'content' => '<strong>{{host.name}}</strong>'
                        ),
                        'email' => array(
                            'status' => 1,
                            'title' => 'Email:',
                            'content' => '<strong><a href="" style="text-decoration: none; color: #2E6B38;">{{host.email}}</a></strong>'
                        ),
                        'phone' => array(
                            'status' => 1,
                            'title' => 'Phone:',
                            'content' => '<strong><a href="" style="text-decoration: none; color: #2E6B38;">{{host.phone}}</a></strong>'
                        ),
                    )
                ),
                array(
                    'id' => 'instructions',
                    'order' => 4,
                    'status' => 1,
                    'title' => 'Instructions',
                    'content' => '<ul><li>Please <strong>join the event five minutes before the event starts</strong> based on your time zone.</li><li>Ensure you have a good internet connection, a quality camera, and a quiet space.</li></ul>',
                ),
                array(
                    'id' => 'cancel_reschedule',
                    'order' => 5,
                    'status' => 1,
                    'title' => 'Buttons',
                    'border_color' => '#C0D8C4',
                    'content' => array(
                        'description' => array(
                            'status' => 1,
                            'content' => 'You can cancel or reschedule this event for any reason.'
                        ),
                        'cancel' => array(
                            'status' => 1,
                            'content' => '{{booking.cancel_link}}'
                        ),
                        'reschedule' => array(
                            'status' => 1,
                            'content' => '{{booking.rescheduled_link}}'
                        ),
                    )
                ),
                array(
                    'id' => 'add_to_calendar',
                    'order' => 6,
                    'status' => 1,
                    'title' => 'Add to Calendar',
                    'border_color' => '#C0D8C4',
                    'content' => array(
                        'description' => array(
                            'status' => 1,
                            'content' => 'Add to calendar'
                        ),
                        'list' => array(
                            'status' => 1,
                            'content' => $add_to_calendar_content
                        ), 
                    )
                ),
                array(
                    'id' => 'footer',
                    'order' => 7,
                    'status' => 1,
                    'title' => 'Footer',
                    'content' => array(
                        'description' => array(
                            'status' => 1,
                            'content' => '<span style="color: #FFF; font-size: 16.5px; font-weight: bold;">HydraBooking</span><p style="color: #FFF; font-size: 13px; margin: 8px 0 0 0;">The WordPress Plugin to <br>Supercharge Your Scheduling</p>'
                        ),
                        'social' => array(
                            'status' => 1,
                            'data' => array(
                                array(
                                    'title' => 'Facebook',
                                    'url' => '#'
                                ),
                                array(
                                    'title' => 'Twitter',
                                    'url' => '#'
                                ),
                                array(
                                    'title' => 'Youtube',
                                    'url' => '#'
                                ),
                            )
                        )
                    )
                ),
            )


        );
        $notification['attendee']['booking_reschedule'] = array(
            'status' => 1,
            'template' => 'default',
            'from' => '{{wp.admin_email}}', 
            'subject' => 'Your booking was rescheduled with {{host.name}}',
            'body' =>  ' 
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr>
                <td bgcolor="#215732" style="padding: 16px 32px; text-align: left; border-radius: 8px 8px 0 0;">
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                        <tbody><tr><td style="vertical-align: middle;">
                                    <span style="color: #FFF; font-size: 22px; font-weight: 600; margin: 0;">HydraBooking</span>
                                </td></tr>
                    </tbody></table>
                </td>
            </tr></tbody></table>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 32px;width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;">
            <tbody><tr><td><p style="font-weight: bold;margin: 0; font-size: 17px;">Hey {{attendee.name}},</p><p style="font-weight: bold; margin: 8px 0 0 0; font-size: 17px;">Booking Rescheduled</p></td></tr> </tbody></table></td></tr></tbody></table>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 32px;width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;">
                <tbody><tr>
                    <td>
                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border: 2px dashed #C0D8C4; border-radius: 8px; padding: 24px; background: #fff;">
                            <tbody><tr><td style="font-weight: bold; font-size: 16px;">Meeting Details</td></tr>
            
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($calendar_image_url) . '" alt="data_time" style="float: left;margin-right: 8px;">
                                            Date &amp; Time:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{meeting.date}} - {{meeting.time}}</strong> <br>Host time: {{booking.start_date_time_for_host}} - {{booking.full_start_end_host_timezone}}
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($user_image_url) . '" alt="host" style="float: left;margin-right: 8px;">
                                            Host:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{host.name}}</strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($meeting_image_url) . '" alt="about" style="float: left;margin-right: 8px;">
                                            About:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{meeting.title}}</strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($file_image_url) . '" alt="description" style="float: left;margin-right: 8px;">
                                            Description:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            {{meeting.content}}
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($location_image_url) . '" alt="location" style="float: left;margin-right: 8px;">
                                            Location:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{booking.location_details_html}}</strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    </tbody></table></td></tr> </tbody></table></td></tr></tbody></table>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 32px;width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;">
                <tbody><tr>
                    <td>
                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border: 2px dashed #C0D8C4; border-radius: 8px; padding: 24px; background: #fff;">
                            <tbody><tr><td style="font-weight: bold; font-size: 16px;">Host Details</td></tr>
            
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($user_image_url) . '" alt="name" style="float: left;margin-right: 8px;">
                                            Name:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{host.name}}</strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($mail_image_url) . '" alt="email" style="float: left;margin-right: 8px;">
                                            Email:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong><a href="" style="text-decoration: none; color: #2E6B38;">{{host.email}}</a></strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($phone_image_url) . '" alt="phone" style="float: left;margin-right: 8px;">
                                            Phone:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong><a href="" style="text-decoration: none; color: #2E6B38;">{{host.phone}}</a></strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    </tbody></table></td></tr> </tbody></table></td></tr></tbody></table> <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 0;width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="padding: 0 32px; width: 100%; max-width: 600px; margin: 0 auto;">
                <tbody><tr>
                    <td style="font-weight: bold; font-size: 17px; padding-bottom: 24px;" bgcolor="#fff">Instructions</td>
                </tr>
                <tr>
                    <td style="font-size: 15px;"><ul><li>Please <strong>join the event five minutes before the event starts</strong> based on your time zone.</li><li>Ensure you have a good internet connection, a quality camera, and a quiet space.</li></ul></td>
                </tr></tbody></table></td></tr></tbody></table> <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 0;width: 100%; max-width: 600px; margin: 0 auto;" class="tfhb-cancel-reschedule-btn"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="border-top: 1px dashed #C0D8C4;border-bottom: 1px dashed #C0D8C4; padding: 0 32px; width: 100%; max-width: 600px; margin: 0 auto;"> <tbody><tr>
                        <td style="font-size: 15px;padding: 24px 0 16px 0;">You can cancel or reschedule this event for any reason.</td>
                    </tr><tr>
                    <td style="font-size: 15px; padding-bottom: 24px;"><a href="{{booking.cancel_link}}" class="tfhb-cancel-btn" style=" padding: 8px 24px; border-radius: 8px;border: 1px solid #C0D8C4;background: #FFF; color: #273F2B;display: inline-block;text-decoration: none;">Cancel</a><a href="{{booking.rescheduled_link}}" class="tfhb-reschedule-btn" style=" padding: 8px 24px; border-radius: 8px;border: 1px solid #C0D8C4;background: #FFF; color: #273F2B;display: inline-block; margin-left: 16px;text-decoration: none;">Reschedule</a></td></tr></tbody></table></td></tr></tbody></table>
                
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#121D13" style="padding: 16px 32px;border-radius: 0px 0px 8px 8px; width: 100%; max-width: 600px; margin: 0 auto;">
                        <tbody><tr><td align="left">
                                <span style="color: #FFF; font-size: 16.5px; font-weight: bold;">HydraBooking</span><p style="color: #FFF; font-size: 13px; margin: 8px 0 0 0;">The WordPress Plugin to <br>Supercharge Your Scheduling</p>
                            </td><td align="right" class="social" style="vertical-align: baseline;">
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0"><tbody><tr><td style="padding-bottom: 4px;">
                                            <a href="#" style="text-decoration: none; color: #FFF;">
                                                Facebook
                                            </a>
                                        </td></tr><tr><td style="padding-bottom: 4px;">
                                            <a href="#" style="text-decoration: none; color: #FFF;">
                                                Twitter
                                            </a>
                                        </td></tr><tr><td style="padding-bottom: 4px;">
                                            <a href="#" style="text-decoration: none; color: #FFF;">
                                                Youtube
                                            </a>
                                        </td></tr>
                                </tbody></table>
                            </td></tr>
                    </tbody></table>
            ',
            'builder' => array(
                array(
                    'id' => 'header',
                    'order' => 0,
                    'status' => 1,
                    'title' => 'Header',
                    'content' => '<span style="color: #FFF; font-size: 22px; font-weight: 600; margin: 0;">HydraBooking</span>',
                    'logo' => '',
                    'background' => '#215732'
                ),
                array(
                    'id' => 'gratitude',
                    'order' => 1,
                    'status' => 1,
                    'title' => 'Greetings',
                    'content' => '<p style="font-weight: bold;margin: 0; font-size: 17px;">Hey {{attendee.name}},</p><p style="font-weight: bold; margin: 8px 0 0 0; font-size: 17px;">Booking Rescheduled</p>'
                ),
                array(
                    'id' => 'meeting_details',
                    'order' => 2,
                    'status' => 1,
                    'title' => 'Meeting Details',
                    'border_color' => '#C0D8C4',
                    'content' => array(
                        'data_time' => array(
                            'status' => 1,
                            'title' => 'Date & Time:',
                            'content' => '<strong>{{meeting.date}} - {{meeting.time}}</strong> <br>Host time: {{booking.start_date_time_for_host}} - {{booking.full_start_end_host_timezone}}'
                        ),
                        'host' => array(
                            'status' => 1,
                            'title' => 'Host:',
                            'content' => '<strong>{{host.name}}</strong>'
                        ),
                        'about' => array(
                            'status' => 1,
                            'title' => 'About:',
                            'content' => '<strong>{{meeting.title}}</strong>'
                        ),
                        'description' => array(
                            'status' => 1,
                            'title' => 'Description:',
                            'content' => '{{meeting.content}}'
                        ),
                        'location' => array(
                            'status' => 1,
                            'title' => 'Location:',
                            'content' => '<strong>{{booking.location_details_html}}</strong>'
                        ),
                    )
                ),
                array(
                    'id' => 'host_details',
                    'order' => 3,
                    'status' => 1,
                    'title' => 'Host Details',
                    'border_color' => '#C0D8C4',
                    'content' => array(
                        'name' => array(
                            'status' => 1,
                            'title' => 'Name:',
                            'content' => '<strong>{{host.name}}</strong>'
                        ),
                        'email' => array(
                            'status' => 1,
                            'title' => 'Email:',
                            'content' => '<strong><a href="" style="text-decoration: none; color: #2E6B38;">{{host.email}}</a></strong>'
                        ),
                        'phone' => array(
                            'status' => 1,
                            'title' => 'Phone:',
                            'content' => '<strong><a href="" style="text-decoration: none; color: #2E6B38;">{{host.phone}}</a></strong>'
                        ),
                    )
                ),
                array(
                    'id' => 'instructions',
                    'order' => 4,
                    'status' => 1,
                    'title' => 'Instructions',
                    'content' => '<ul><li>Please <strong>join the event five minutes before the event starts</strong> based on your time zone.</li><li>Ensure you have a good internet connection, a quality camera, and a quiet space.</li></ul>',
                ),
                array(
                    'id' => 'cancel_reschedule',
                    'order' => 5,
                    'status' => 1,
                    'title' => 'Buttons',
                    'border_color' => '#C0D8C4',
                    'content' => array(
                        'description' => array(
                            'status' => 1,
                            'content' => 'You can cancel or reschedule this event for any reason.'
                        ),
                        'cancel' => array(
                            'status' => 1,
                            'content' => '{{booking.cancel_link}}'
                        ),
                        'reschedule' => array(
                            'status' => 1,
                            'content' => '{{booking.rescheduled_link}}'
                        ),
                    )
                ),
                array(
                    'id' => 'add_to_calendar',
                    'order' => 6,
                    'status' => 1,
                    'title' => 'Add to Calendar',
                    'border_color' => '#C0D8C4',
                    'content' => array(
                        'description' => array(
                            'status' => 1,
                            'content' => 'Add to calendar'
                        ),
                        'list' => array(
                            'status' => 1,
                            'content' => $add_to_calendar_content
                        ), 
                    )
                ),
                array(
                    'id' => 'footer',
                    'order' => 7,
                    'status' => 1,
                    'title' => 'Footer',
                    'content' => array(
                        'description' => array(
                            'status' => 1,
                            'content' => '<span style="color: #FFF; font-size: 16.5px; font-weight: bold;">HydraBooking</span><p style="color: #FFF; font-size: 13px; margin: 8px 0 0 0;">The WordPress Plugin to <br>Supercharge Your Scheduling</p>'
                        ),
                        'social' => array(
                            'status' => 1,
                            'data' => array(
                                array(
                                    'title' => 'Facebook',
                                    'url' => '#'
                                ),
                                array(
                                    'title' => 'Twitter',
                                    'url' => '#'
                                ),
                                array(
                                    'title' => 'Youtube',
                                    'url' => '#'
                                ),
                            )
                        )
                    )
                ),
            )
        );
        $notification['attendee']['booking_reminder'] = array(
            'status' => 1,
            'template' => 'default',
            'from' => '{{wp.admin_email}}', 
            'subject' => 'Meeting Reminder with {{host.name}} @ {{booking.start_date_time_for_attendee}}', 
            'body' =>  ' <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr>
                <td bgcolor="#215732" style="padding: 16px 32px; text-align: left; border-radius: 8px 8px 0 0;">
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                        <tbody><tr><td style="vertical-align: middle;">
                                    <span style="color: #FFF; font-size: 22px; font-weight: 600; margin: 0;">HydraBooking</span>
                                </td></tr>
                    </tbody></table>
                </td>
            </tr></tbody></table>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 32px;width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;">
            <tbody><tr><td><p style="font-weight: bold;margin: 0; font-size: 17px;">Hey {{attendee.name}},</p><p style="font-weight: bold; margin: 8px 0 0 0; font-size: 17px;">Reminder: Your meeting will start in {{booking.start_date_time_for_attendee}}</p></td></tr> </tbody></table></td></tr></tbody></table>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 32px;width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;">
                <tbody><tr>
                    <td>
                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border: 2px dashed #C0D8C4; border-radius: 8px; padding: 24px; background: #fff;">
                            <tbody><tr><td style="font-weight: bold; font-size: 16px;">Meeting Details</td></tr>
            
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($calendar_image_url) . '" alt="data_time" style="float: left;margin-right: 8px;">
                                            Date &amp; Time:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{meeting.date}} - {{meeting.time}}</strong> <br>Host time: {{booking.start_date_time_for_host}} - {{booking.full_start_end_host_timezone}}
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($user_image_url) . '" alt="host" style="float: left;margin-right: 8px;">
                                            Host:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{host.name}}</strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($meeting_image_url) . '" alt="about" style="float: left;margin-right: 8px;">
                                            About:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{meeting.title}}</strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($file_image_url) . '" alt="description" style="float: left;margin-right: 8px;">
                                            Description:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            {{meeting.content}}
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($location_image_url) . '" alt="location" style="float: left;margin-right: 8px;">
                                            Location:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{booking.location_details_html}}</strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    </tbody></table></td></tr> </tbody></table></td></tr></tbody></table>
            <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 32px;width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%; max-width: 600px; margin: 0 auto;">
                <tbody><tr>
                    <td>
                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border: 2px dashed #C0D8C4; border-radius: 8px; padding: 24px; background: #fff;">
                            <tbody><tr><td style="font-weight: bold; font-size: 16px;">Host Details</td></tr>
            
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($user_image_url) . '" alt="name" style="float: left;margin-right: 8px;">
                                            Name:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong>{{host.name}}</strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($mail_image_url) . '" alt="email" style="float: left;margin-right: 8px;">
                                            Email:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong><a href="" style="text-decoration: none; color: #2E6B38;">{{host.email}}</a></strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    
                        <tr>
                            <td>
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top: 24px;">
                                    <tbody><tr>
                                        <td style="vertical-align: top; font-size: 15px; width: 120px; min-width: 120px;">
                                            <img src="' . esc_url($phone_image_url) . '" alt="phone" style="float: left;margin-right: 8px;">
                                            Phone:
                                        </td>
                                        <td style="padding-left: 32px;font-size: 15px; line-height: 24px; word-wrap: anywhere;">
                                            <strong><a href="" style="text-decoration: none; color: #2E6B38;">{{host.phone}}</a></strong>
                                        </td>
                                    </tr>
                                </tbody></table>
                            </td>
                        </tr>
                    </tbody></table></td></tr> </tbody></table></td></tr></tbody></table> <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 0;width: 100%; max-width: 600px; margin: 0 auto;"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="padding: 0 32px; width: 100%; max-width: 600px; margin: 0 auto;">
                <tbody><tr>
                    <td style="font-weight: bold; font-size: 17px; padding-bottom: 24px;" bgcolor="#fff">Instructions</td>
                </tr>
                <tr>
                    <td style="font-size: 15px;"><ul><li>Please <strong>join the event five minutes before the event starts</strong> based on your time zone.</li><li>Ensure you have a good internet connection, a quality camera, and a quiet space.</li></ul></td>
                </tr></tbody></table></td></tr></tbody></table> <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" style="padding: 16px 0;width: 100%; max-width: 600px; margin: 0 auto;" class="tfhb-cancel-reschedule-btn"><tbody><tr><td><table role="presentation" cellspacing="0" cellpadding="0" border="0" style="border-top: 1px dashed #C0D8C4;border-bottom: 1px dashed #C0D8C4; padding: 0 32px; width: 100%; max-width: 600px; margin: 0 auto;"> <tbody><tr>
                        <td style="font-size: 15px;padding: 24px 0 16px 0;">You can cancel or reschedule this event for any reason.</td>
                    </tr><tr>
                    <td style="font-size: 15px; padding-bottom: 24px;"><a href="{{booking.cancel_link}}" class="tfhb-cancel-btn" style=" padding: 8px 24px; border-radius: 8px;border: 1px solid #C0D8C4;background: #FFF; color: #273F2B;display: inline-block;text-decoration: none;">Cancel</a><a href="{{booking.rescheduled_link}}" class="tfhb-reschedule-btn" style=" padding: 8px 24px; border-radius: 8px;border: 1px solid #C0D8C4;background: #FFF; color: #273F2B;display: inline-block; margin-left: 16px;text-decoration: none;">Reschedule</a></td></tr></tbody></table></td></tr></tbody></table>
                
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" bgcolor="#121D13" style="padding: 16px 32px;border-radius: 0px 0px 8px 8px; width: 100%; max-width: 600px; margin: 0 auto;">
                        <tbody><tr><td align="left">
                                <span style="color: #FFF; font-size: 16.5px; font-weight: bold;">HydraBooking</span><p style="color: #FFF; font-size: 13px; margin: 8px 0 0 0;">The WordPress Plugin to <br>Supercharge Your Scheduling</p>
                            </td><td align="right" class="social" style="vertical-align: baseline;">
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0"><tbody><tr><td style="padding-bottom: 4px;">
                                            <a href="#" style="text-decoration: none; color: #FFF;">
                                                Facebook
                                            </a>
                                        </td></tr><tr><td style="padding-bottom: 4px;">
                                            <a href="#" style="text-decoration: none; color: #FFF;">
                                                Twitter
                                            </a>
                                        </td></tr><tr><td style="padding-bottom: 4px;">
                                            <a href="#" style="text-decoration: none; color: #FFF;">
                                                Youtube
                                            </a>
                                        </td></tr>
                                </tbody></table>
                            </td></tr>
                    </tbody></table>
            ',
            'builder' => array(
                array(
                    'id' => 'header',
                    'order' => 0,
                    'status' => 1,
                    'title' => 'Header',
                    'content' => '<span style="color: #FFF; font-size: 22px; font-weight: 600; margin: 0;">HydraBooking</span>',
                    'logo' => '',
                    'background' => '#215732'
                ),
                array(
                    'id' => 'gratitude',
                    'order' => 1,
                    'status' => 1,
                    'title' => 'Greetings',
                    'content' => '<p style="font-weight: bold;margin: 0; font-size: 17px;">Hey {{attendee.name}},</p><p style="font-weight: bold; margin: 8px 0 0 0; font-size: 17px;">Reminder: Your meeting will start in {{booking.start_date_time_for_attendee}}</p>'
                ),
                array(
                    'id' => 'meeting_details',
                    'order' => 2,
                    'status' => 1,
                    'title' => 'Meeting Details',
                    'border_color' => '#C0D8C4',
                    'content' => array(
                        'data_time' => array(
                            'status' => 1,
                            'title' => 'Date & Time:',
                            'content' => '<strong>{{meeting.date}} - {{meeting.time}}</strong> <br>Host time: {{booking.start_date_time_for_host}} - {{booking.full_start_end_host_timezone}}'
                        ),
                        'host' => array(
                            'status' => 1,
                            'title' => 'Host:',
                            'content' => '<strong>{{host.name}}</strong>'
                        ),
                        'about' => array(
                            'status' => 1,
                            'title' => 'About:',
                            'content' => '<strong>{{meeting.title}}</strong>'
                        ),
                        'description' => array(
                            'status' => 1,
                            'title' => 'Description:',
                            'content' => '{{meeting.content}}'
                        ),
                        'location' => array(
                            'status' => 1,
                            'title' => 'Location:',
                            'content' => '<strong>{{booking.location_details_html}}</strong>'
                        ),
                    )
                ),
                array(
                    'id' => 'host_details',
                    'order' => 3,
                    'status' => 1,
                    'title' => 'Host Details',
                    'border_color' => '#C0D8C4',
                    'content' => array(
                        'name' => array(
                            'status' => 1,
                            'title' => 'Name:',
                            'content' => '<strong>{{host.name}}</strong>'
                        ),
                        'email' => array(
                            'status' => 1,
                            'title' => 'Email:',
                            'content' => '<strong><a href="" style="text-decoration: none; color: #2E6B38;">{{host.email}}</a></strong>'
                        ),
                        'phone' => array(
                            'status' => 1,
                            'title' => 'Phone:',
                            'content' => '<strong><a href="" style="text-decoration: none; color: #2E6B38;">{{host.phone}}</a></strong>'
                        ),
                    )
                ),
                array(
                    'id' => 'instructions',
                    'order' => 4,
                    'status' => 1,
                    'title' => 'Instructions',
                    'content' => '<ul><li>Please <strong>join the event five minutes before the event starts</strong> based on your time zone.</li><li>Ensure you have a good internet connection, a quality camera, and a quiet space.</li></ul>',
                ),
                array(
                    'id' => 'cancel_reschedule',
                    'order' => 5,
                    'status' => 1,
                    'title' => 'Buttons',
                    'border_color' => '#C0D8C4',
                    'content' => array(
                        'description' => array(
                            'status' => 1,
                            'content' => 'You can cancel or reschedule this event for any reason.'
                        ),
                        'cancel' => array(
                            'status' => 1,
                            'content' => '{{booking.cancel_link}}'
                        ),
                        'reschedule' => array(
                            'status' => 1,
                            'content' => '{{booking.rescheduled_link}}'
                        ),
                    )
                ),
                array(
                    'id' => 'add_to_calendar',
                    'order' => 6,
                    'status' => 1,
                    'title' => 'Add to Calendar',
                    'border_color' => '#C0D8C4',
                    'content' => array(
                        'description' => array(
                            'status' => 1,
                            'content' => 'Add to calendar'
                        ),
                        'list' => array(
                            'status' => 1,
                            'content' => $add_to_calendar_content
                        ), 
                    )
                ),
                array(
                    'id' => 'footer',
                    'order' => 6,
                    'status' => 1,
                    'title' => 'Footer',
                    'content' => array(
                        'description' => array(
                            'status' => 1,
                            'content' => '<span style="color: #FFF; font-size: 16.5px; font-weight: bold;">HydraBooking</span><p style="color: #FFF; font-size: 13px; margin: 8px 0 0 0;">The WordPress Plugin to <br>Supercharge Your Scheduling</p>'
                        ),
                        'social' => array(
                            'status' => 1,
                            'data' => array(
                                array(
                                    'title' => 'Facebook',
                                    'url' => '#'
                                ),
                                array(
                                    'title' => 'Twitter',
                                    'url' => '#'
                                ),
                                array(
                                    'title' => 'Youtube',
                                    'url' => '#'
                                ),
                            )
                        )
                    )
                ),
            )
        );

        // Telegram Notification
        $notification['telegram']['booking_confirmation'] = array(
            'status' => 0,
            'builder' => '',
            'body' => '
                <h3>A new Booking has been scheduled</h3> <hr> 
                <h4>Meeting Details</h4>
                <p> {{meeting.title}} with {{attendee.name}}</p> 
                <p> Date: {{meeting.date}} </p>'
        );
        $notification['telegram']['booking_cancel'] = array(
            'status' => 0,
            'builder' => '',
            'body' => '
                <h3>Booking Cancellation</h3> <hr> 
                <h4>Meeting Details</h4>
                <p> {{meeting.title}} with {{attendee.name}}</p> 
                <p> Date: {{meeting.date}} </p>'
        );
        $notification['telegram']['booking_reschedule'] = array(
            'status' => 0,
            'builder' => '',
            'body' => '
                <h3>Booking Rescheduled</h3> <hr> 
                <h4>Meeting Details</h4>
                <p> {{meeting.title}} with {{attendee.name}}</p> 
                <p> Date: {{meeting.date}} </p>'
        );

        // Twilio Notification
        $notification['twilio']['booking_confirmation'] = array(
            'status' => 0,
            'builder' => '',
            'body' => '
                <h3>A new Booking has been scheduled</h3> <hr> 
                <h4>Meeting Details</h4>
                <p> {{meeting.title}} with {{attendee.name}}</p> 
                <p> Date: {{meeting.date}} </p>'
        );
        $notification['twilio']['booking_cancel'] = array(
            'status' => 0,
            'builder' => '',
            'body' => '
                <h3>Booking Cancellation</h3> <hr> 
                <h4>Meeting Details</h4>
                <p> {{meeting.title}} with {{attendee.name}}</p> 
                <p> Date: {{meeting.date}} </p>'
        );
        $notification['twilio']['booking_reschedule'] = array(
            'status' => 0,
            'builder' => '',
            'body' => '
                <h3>Booking Rescheduled</h3> <hr> 
                <h4>Meeting Details</h4>
                <p> {{meeting.title}} with {{attendee.name}}</p> 
                <p> Date: {{meeting.date}} </p>'
        );
        
        // Slack Notification
        $notification['slack']['booking_confirmation'] = array(
            'status' => 0,
            'builder' => '',
            'body' => '
                <h3>A new Booking has been scheduled</h3> <hr> 
                <h4>Meeting Details</h4>
                <p> {{meeting.title}} with {{attendee.name}}</p> 
                <p> Date: {{meeting.date}} </p>'
        );
        $notification['slack']['booking_cancel'] = array(
            'status' => 0,
            'builder' => '',
            'body' => '
                <h3>Booking Cancellation</h3> <hr> 
                <h4>Meeting Details</h4>
                <p> {{meeting.title}} with {{attendee.name}}</p> 
                <p> Date: {{meeting.date}} </p>'
        );
        $notification['slack']['booking_reschedule'] = array(
            'status' => 0,
            'builder' => '',
            'body' => '
                <h3>Booking Rescheduled</h3> <hr> 
                <h4>Meeting Details</h4>
                <p> {{meeting.title}} with {{attendee.name}}</p> 
                <p> Date: {{meeting.date}} </p>'
        );

        // $notification = $this->append_add_to_calendar_default_section(
        //     $notification,
        //     $google_calendar_image_url,
        //     $outlook_calendar_image_url,
        //     $yahoo_calendar_image_url,
        //     $other_calendar_image_url
        // );

        return $notification;
        
    }
 


}
