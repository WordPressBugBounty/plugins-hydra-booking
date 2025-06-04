<?php

namespace HydraBooking\Services\Integrations\AppleCalendar;

use Sabre\DAV\Client;
// use Sabre\VObject\Component\VCalendar;
class AppleCalendar {
	private $client;
	private $baseUri = 'https://caldav.icloud.com';

	public function __construct( $appleId, $appSpecificPassword ) {
		$this->client = new Client(
			array(
				'baseUri'  => $this->baseUri,
				'userName' => $appleId,
				'password' => $appSpecificPassword,
			)
		);
		 
		$this->addEvent();
	}

	public function getPrincipalUrl() {
		$response = $this->client->propFind(
			'/.well-known/caldav',
			array(
				'{DAV:}current-user-principal',
			),
			0
		);

		return $response['{DAV:}current-user-principal'][0]['value'] ?? null;
	}

	public function getCalendars() {
		$principalUrl = $this->getPrincipalUrl(); // 21642915044/principal/
		$principalUrl = '/principal/'; // 21642915044/principal/

		if ( ! $principalUrl ) {
			throw new Exception( 'Could not find principal URL' );
		}

		$response = $this->client->propFind(
			$principalUrl,
			array(
				'{urn:ietf:params:xml:ns:caldav}calendar-home-set',

			),
			0
		);

		$calendarHomeSet = $response['{urn:ietf:params:xml:ns:caldav}calendar-home-set'][0]['value'] ?? null;

		if ( ! $calendarHomeSet ) {
			throw new Exception( 'Could not find calendar home set' );
		}
		// Get only calendar display names like home and work no need to get other calendars
		$response = $this->client->propFind(
			$calendarHomeSet,
			array(
				'{DAV:}displayname',
				'{DAV:}id',
				'{DAV:}resourcetype',
				'{urn:ietf:params:xml:ns:caldav}calendar',
			// '{urn:ietf:params:xml:ns:caldav}calendar-description',
			// '{urn:ietf:params:xml:ns:caldav}calendar-timezone',
			// '{urn:ietf:params:xml:ns:caldav}supported-calendar-component-set',
			// '{urn:ietf:params:xml:ns:caldav}supported-calendar-data',
			// '{urn:ietf:params:xml:ns:caldav}max-resource-size',
			// '{urn:ietf:params:xml:ns:caldav}min-date-time',
			// '{urn:ietf:params:xml:ns:caldav}max-date-time',
			// '{urn:ietf:params:xml:ns:caldav}max-instances',
			// '{urn:ietf:params:xml:ns:caldav}max-attendees-per-instance',
			// '{urn:ietf:params:xml:ns:caldav}calendar-free-busy-set',
			// '{urn:ietf:params:xml:ns:caldav}schedule-calendar-transp',
			// '{urn:ietf:params:xml:ns:caldav}calendar-user-address-set',
			// '{urn:ietf:params:xml:ns:caldav}schedule-default-calendar-URL',
			// '{urn:ietf:params:xml:ns:caldav}schedule-inbox-URL',
			// '{urn:ietf:params:xml:ns:caldav}schedule-outbox-URL',
			// '{urn:ietf:params:xml:ns:caldav}calendar-proxy-read-for',
			),
			1
		);
		echo '<pre>';
		print_r( $response );
		echo '</pre>';
		exit;
		$calendars = array();
		foreach ( $response as $url => $props ) {
			$calendars[] = array(
				'id'    => $url,
				'title' => 'humm',
			);
		}
		echo '<pre>';
		print_r( $calendars );
		echo '</pre>';
		exit;

		return $calendars;
	}

	public function addEvent() {
		// calendar id like /21642915044/calendars/3CB94D5E-3A93-4CA9-A46F-F09844B3909B/
		$calendarUrl = '/21642915044/calendars/457E4798-C315-4843-B343-1B7B0078A955/';
		$summary     = 'Meeting with John';
		$description = 'Discuss the new project';
		$location    = 'Office';

		$eventId   = uniqid();
		$eventData = "BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:$eventId
DTSTAMP:" . gmdate( 'Ymd\THis\Z' ) . "
DTSTART;TZID=America/New_York:20240630T100000  
DTEND;TZID=America/New_York:20240630T110000 
SUMMARY:$summary
DESCRIPTION:$description
LOCATION:$location
END:VEVENT
END:VCALENDAR";

		try {
			// /calendars/YOUR-ICLOUD-USER-ID/CALENDAR-ID/
			$url = $calendarUrl . $eventId . '.ics';
			echo esc_url($url);
			exit;
			$data = $this->client->request(
				'PUT',
				$calendarUrl . $eventId . '.ics',
				$eventData,
				array(
					'Content-Type'  => 'text/calendar',
					'If-None-Match' => '*',
				)
			);
			echo "Event added successfully.\n";
			echo '<pre>';
			print_r( $data );
			echo '</pre>';
		} catch ( Exception $e ) {
			echo 'Error: ', esc_attr($e->getMessage()), "\n";
		}
	}

	public function editEvent( $calendarUrl, $eventId, $startDateTime, $endDateTime, $summary, $description, $location ) {
		try {
			$events = $this->client->propFind(
				$calendarUrl,
				array(
					'{DAV:}getetag',
					'{urn:ietf:params:xml:ns:caldav}calendar-data',
				),
				1
			);

			foreach ( $events as $url => $props ) {
				if ( strpos( $url, $eventId ) !== false ) {
					$vcalendar           = VCalendar::parse( $props['{urn:ietf:params:xml:ns:caldav}calendar-data'] );
					$vevent              = $vcalendar->VEVENT;
					$vevent->DTSTART     = new \DateTime( $startDateTime );
					$vevent->DTEND       = new \DateTime( $endDateTime );
					$vevent->SUMMARY     = $summary;
					$vevent->DESCRIPTION = $description;
					$vevent->LOCATION    = $location;

					$this->client->request( 'PUT', $url, $vcalendar->serialize() );
					echo "Event updated successfully.\n";
					break;
				}
			}
		} catch ( Exception $e ) {
			echo 'Error: ', esc_attr($e->getMessage()), "\n";
		}
	}
}
