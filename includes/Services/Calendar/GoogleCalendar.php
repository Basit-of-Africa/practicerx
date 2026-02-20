<?php
namespace PracticeRx\Services\Calendar;

use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class GoogleCalendar {

    /**
     * Create an event in Google Calendar for the given appointment.
     * This is a scaffold: it requires google/apiclient and OAuth tokens to be configured.
     * Returns true on success or WP_Error on failure.
     */
    /**
     * Create an event in Google Calendar for the given appointment.
     *
     * @param object|array $appointment Object or array with start_time, end_time, notes, title, id
     * @param string $calendar_id Calendar ID (default 'primary')
     * @param array $attendees Array of attendee emails
     * @return \Google_Service_Calendar_Event|\WP_Error
     */
    public static function create_event( $appointment, $calendar_id = 'primary', $attendees = array(), $practitioner_id = 0 ) {
        // Use GoogleOAuth helper to get a configured client (with tokens loaded)
        $client = GoogleOAuth::get_client( $practitioner_id );
        if ( is_wp_error( $client ) ) {
            return $client;
        }

        // Ensure token is valid / refreshed (supports practitioner-specific tokens)
        $refresh = GoogleOAuth::refresh_token_if_needed( $practitioner_id );
        if ( is_wp_error( $refresh ) ) {
            return $refresh;
        }

        try {
            $service = new \Google_Service_Calendar( $client );

            // Build event
            $event = new \Google_Service_Calendar_Event();

            $summary = isset( $appointment->title ) ? $appointment->title : sprintf( 'Appointment with %s', isset( $appointment->patient_id ) ? $appointment->patient_id : '' );
            $description = isset( $appointment->notes ) ? $appointment->notes : '';

            $start_dt = new \Google_Service_Calendar_EventDateTime();
            $start_dt->setDateTime( date( 'c', strtotime( $appointment->start_time ) ) );
            $start_dt->setTimeZone( get_option( 'timezone_string' ) ?: wp_timezone_string() );

            $end_dt = new \Google_Service_Calendar_EventDateTime();
            $end_dt->setDateTime( date( 'c', strtotime( $appointment->end_time ) ) );
            $end_dt->setTimeZone( get_option( 'timezone_string' ) ?: wp_timezone_string() );

            $event->setSummary( $summary );
            $event->setDescription( $description );
            $event->setStart( $start_dt );
            $event->setEnd( $end_dt );

            // If appointment has meeting link, add as location
            if ( ! empty( $appointment->meeting_link ) ) {
                $event->setLocation( $appointment->meeting_link );
            }

            // Attach attendees if provided
            if ( ! empty( $attendees ) && is_array( $attendees ) ) {
                $att_objs = array();
                foreach ( $attendees as $email ) {
                    if ( is_email( $email ) ) {
                        $att = new \Google_Service_Calendar_EventAttendee();
                        $att->setEmail( $email );
                        $att_objs[] = $att;
                    }
                }
                if ( ! empty( $att_objs ) ) {
                    $event->setAttendees( $att_objs );
                }
            }

            // Insert into specified calendar (supports per-practitioner calendars)
            $params = array();
            // Request Google to send invites/updates to attendees
            $params['sendUpdates'] = 'all';

            $created = $service->events->insert( $calendar_id ?: 'primary', $event, $params );

            if ( $created && ! empty( $created->id ) ) {
                // Optionally store calendar event id on appointment record
                if ( method_exists( '\PracticeRx\\Models\\Appointment', 'update' ) ) {
                    try {
                        \PracticeRx\Models\Appointment::update( $appointment->id, array( 'meeting_event_id' => $created->id ) );
                    } catch ( \Exception $e ) {
                        // ignore
                    }
                }

                return $created;
            }

            return new WP_Error( 'google_event_failed', __( 'Failed to create Google Calendar event', 'practicerx' ) );

        } catch ( \Exception $e ) {
            return new WP_Error( 'google_exception', $e->getMessage() );
        }
    }
}
