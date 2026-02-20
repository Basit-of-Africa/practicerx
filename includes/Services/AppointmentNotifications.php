<?php
namespace PracticeRx\Services;

use PracticeRx\Models\Appointment;
use PracticeRx\Models\Client;
use PracticeRx\Models\Practitioner;
use PracticeRx\Services\EmailTemplates\AppointmentTemplate;
use PracticeRx\Services\EmailTemplates\Engine as TemplateEngine;
use PracticeRx\Services\SMS\TwilioService;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AppointmentNotifications {

    public static function init() {
        add_action( 'ppms_after_appointment_created', array( __CLASS__, 'on_appointment_created' ), 10, 2 );
    }

    public static function on_appointment_created( $appointment_id, $data ) {
        $appointment = Appointment::get( $appointment_id );
        if ( ! $appointment ) {
            return;
        }

        $client = null;
        if ( ! empty( $appointment->patient_id ) ) {
            $client = Client::get( $appointment->patient_id );
        }

        $practitioner = null;
        if ( ! empty( $appointment->practitioner_id ) ) {
            $practitioner = Practitioner::get_with_user( $appointment->practitioner_id );
        }

        // Create Google Calendar event (if configured) using per-practitioner calendar mapping
        $gc_result = null;
        if ( class_exists( '\\PracticeRx\\Services\\Calendar\\GoogleCalendar' ) ) {
            try {
                $calendar_map = get_option( 'ppms_practitioner_calendars', array() );
                $calendar_id = null;
                if ( $practitioner && ! empty( $practitioner->id ) && ! empty( $calendar_map[ $practitioner->id ] ) ) {
                    $calendar_id = $calendar_map[ $practitioner->id ];
                }

                $attendees = array();
                if ( $client && ! empty( $client->email ) ) {
                    $attendees[] = $client->email;
                }
                // include practitioner user email as attendee if available
                if ( $practitioner && ! empty( $practitioner->user_email ) ) {
                    $attendees[] = $practitioner->user_email;
                }

                $pract_id = $practitioner && ! empty( $practitioner->id ) ? $practitioner->id : 0;
                $gc_result = \PracticeRx\Services\Calendar\GoogleCalendar::create_event( $appointment, $calendar_id ?: 'primary', $attendees, $pract_id );
                if ( ! is_wp_error( $gc_result ) && ! empty( $gc_result->htmlLink ) ) {
                    // If Google returned a public link, attach it to appointment
                    $meeting_link = $gc_result->hangoutLink ?? $gc_result->htmlLink ?? '';
                    if ( $meeting_link ) {
                        try {
                            \PracticeRx\Models\Appointment::update( $appointment->id, array( 'meeting_link' => $meeting_link ) );
                            $appointment->meeting_link = $meeting_link;
                        } catch ( \Exception $e ) {
                            // ignore
                        }
                    }
                }
            } catch ( \Exception $e ) {
                if ( function_exists( 'ppms_log' ) ) {
                    ppms_log( 'Google Calendar error: ' . $e->getMessage(), 'calendar' );
                }
            }
        }

        // Build templated email bodies and calendar attachments
        $client_subject = AppointmentTemplate::render_subject( $appointment, $client, $practitioner, 'client' );
        $pract_subject  = AppointmentTemplate::render_subject( $appointment, $client, $practitioner, 'practitioner' );

        $google_link = AppointmentTemplate::google_calendar_url( $appointment, $client, $practitioner );
        $extra_links = array( __( 'Google Calendar', 'practicerx' ) => $google_link );

        // Allow admin-configured templates via Engine fallback
        $templates = get_option( 'ppms_email_templates', array() );
        $client_tpl = ! empty( $templates['appointment_client'] ) ? $templates['appointment_client'] : AppointmentTemplate::render_body_html( $appointment, $client, $practitioner, $extra_links );
        $pract_tpl  = ! empty( $templates['appointment_practitioner'] ) ? $templates['appointment_practitioner'] : AppointmentTemplate::render_body_html( $appointment, $client, $practitioner, $extra_links );

        $context = array(
            'appointment' => $appointment,
            'client' => $client,
            'practitioner' => $practitioner,
            'google_link' => $google_link,
        );

        $client_body = TemplateEngine::render( $client_tpl, $context );
        $pract_body  = TemplateEngine::render( $pract_tpl, $context );

        $headers = array( 'Content-Type: text/html; charset=UTF-8' );

        // Generate ICS and attach if possible
        $ics_path = AppointmentTemplate::generate_ics( $appointment, $client, $practitioner );
        $attachments = array();
        if ( $ics_path ) {
            $attachments[] = $ics_path;
        }

        // Send email to client
        if ( $client && ! empty( $client->email ) ) {
            wp_mail( $client->email, $client_subject, $client_body, $headers, $attachments );
        }

        // Send email to practitioner (use user email if available)
        if ( $practitioner && ! empty( $practitioner->user_email ) ) {
            wp_mail( $practitioner->user_email, $pract_subject, $pract_body, $headers, $attachments );
        }

        // Cleanup temporary ICS file
        if ( ! empty( $ics_path ) && file_exists( $ics_path ) ) {
            @unlink( $ics_path );
        }

        // Send optional SMS notifications via Twilio when enabled
        $client_phone = $client && ! empty( $client->phone ) ? $client->phone : '';
        if ( $client_phone ) {
            $sms_body = sprintf( __( 'Appointment with %s on %s', 'practicerx' ), ( $practitioner ? AppointmentTemplate::get_practitioner_name( $practitioner ) : __( 'Practitioner', 'practicerx' ) ), $appointment->start_time );
            $sms_result = TwilioService::send_sms( $client_phone, $sms_body );
            if ( is_wp_error( $sms_result ) ) {
                // log but don't fail
                if ( function_exists( 'ppms_log' ) ) {
                    ppms_log( 'Twilio SMS error: ' . $sms_result->get_error_message(), 'sms' );
                }
            }
        }
    }
}

