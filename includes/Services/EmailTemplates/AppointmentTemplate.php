<?php
namespace PracticeRx\Services\EmailTemplates;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AppointmentTemplate {

    public static function render_subject( $appointment, $client = null, $practitioner = null, $for = 'client' ) {
        if ( 'practitioner' === $for ) {
            return sprintf( __( 'New appointment with %s', 'practicerx' ), self::get_client_name( $client ) );
        }

        return __( 'Appointment Confirmation', 'practicerx' );
    }

    public static function render_body_html( $appointment, $client = null, $practitioner = null, $extra_links = array() ) {
        $start = ! empty( $appointment->start_time ) ? $appointment->start_time : '';
        $end = ! empty( $appointment->end_time ) ? $appointment->end_time : '';
        $meeting = ! empty( $appointment->meeting_link ) ? $appointment->meeting_link : '';

        $client_name = self::get_client_name( $client );
        $pract_name = self::get_practitioner_name( $practitioner );

        $body  = '<div style="font-family:Arial, Helvetica, sans-serif; font-size:14px; color:#222;">';
        $body .= sprintf( '<p>%s</p>', esc_html__( 'Your appointment has been scheduled.', 'practicerx' ) );
        $body .= sprintf( '<p><strong>%s:</strong> %s</p>', esc_html__( 'Practitioner', 'practicerx' ), esc_html( $pract_name ) );
        $body .= sprintf( '<p><strong>%s:</strong> %s - %s</p>', esc_html__( 'Time', 'practicerx' ), esc_html( $start ), esc_html( $end ) );
        if ( $meeting ) {
            $body .= sprintf( '<p><strong>%s:</strong> <a href="%s">%s</a></p>', esc_html__( 'Meeting Link', 'practicerx' ), esc_url( $meeting ), esc_html__( 'Join meeting', 'practicerx' ) );
        }

        if ( ! empty( $extra_links ) ) {
            $body .= '<p>' . esc_html__( 'Add to your calendar:', 'practicerx' ) . '</p><ul>';
            foreach ( $extra_links as $label => $url ) {
                $body .= sprintf( '<li><a href="%s">%s</a></li>', esc_url( $url ), esc_html( $label ) );
            }
            $body .= '</ul>';
        }

        $body .= '</div>';
        return $body;
    }

    public static function get_client_name( $client ) {
        if ( ! $client ) {
            return __( 'Client', 'practicerx' );
        }
        return trim( ( isset( $client->first_name ) ? $client->first_name : '' ) . ' ' . ( isset( $client->last_name ) ? $client->last_name : '' ) );
    }

    public static function get_practitioner_name( $practitioner ) {
        if ( ! $practitioner ) {
            return __( 'Practitioner', 'practicerx' );
        }
        if ( ! empty( $practitioner->display_name ) ) {
            return $practitioner->display_name;
        }
        return trim( ( isset( $practitioner->first_name ) ? $practitioner->first_name : '' ) . ' ' . ( isset( $practitioner->last_name ) ? $practitioner->last_name : '' ) );
    }

    /**
     * Generate ICS file content and write to a temporary file. Returns file path.
     * Caller should unlink the file after sending.
     */
    public static function generate_ics( $appointment, $client = null, $practitioner = null ) {
        $start_ts = strtotime( $appointment->start_time );
        $end_ts   = strtotime( $appointment->end_time );

        $dtstart = gmdate( 'Ymd\THis\Z', $start_ts );
        $dtend   = gmdate( 'Ymd\THis\Z', $end_ts );

        $uid = uniqid( 'ppms_' );
        $summary = sprintf( '%s - %s', self::get_practitioner_name( $practitioner ), self::get_client_name( $client ) );
        $description = strip_tags( sprintf( '%s %s', isset( $appointment->notes ) ? $appointment->notes : '', isset( $appointment->meeting_link ) ? $appointment->meeting_link : '' ) );

        $ics = "BEGIN:VCALENDAR\r\n";
        $ics .= "VERSION:2.0\r\n";
        $ics .= "PRODID:-//PracticeRx//EN\r\n";
        $ics .= "CALSCALE:GREGORIAN\r\n";
        $ics .= "BEGIN:VEVENT\r\n";
        $ics .= "UID:{$uid}\r\n";
        $ics .= "DTSTAMP:" . gmdate( 'Ymd\THis\Z' ) . "\r\n";
        $ics .= "DTSTART:{$dtstart}\r\n";
        $ics .= "DTEND:{$dtend}\r\n";
        $ics .= "SUMMARY:" . addcslashes( $summary, "\n,;" ) . "\r\n";
        $ics .= "DESCRIPTION:" . addcslashes( $description, "\n,;" ) . "\r\n";
        $ics .= "END:VEVENT\r\n";
        $ics .= "END:VCALENDAR\r\n";

        $tmp = tempnam( sys_get_temp_dir(), 'ppms_');
        if ( ! $tmp ) {
            return false;
        }

        // ensure .ics extension
        $ics_path = $tmp . '.ics';
        file_put_contents( $ics_path, $ics );

        return $ics_path;
    }

    public static function google_calendar_url( $appointment, $client = null, $practitioner = null ) {
        $start_ts = strtotime( $appointment->start_time );
        $end_ts   = strtotime( $appointment->end_time );

        $dtstart = gmdate( 'Ymd\THis\Z', $start_ts );
        $dtend   = gmdate( 'Ymd\THis\Z', $end_ts );

        $text = rawurlencode( sprintf( '%s - %s', self::get_practitioner_name( $practitioner ), self::get_client_name( $client ) ) );
        $details = rawurlencode( isset( $appointment->notes ) ? $appointment->notes : '' );
        $location = rawurlencode( isset( $appointment->meeting_link ) ? $appointment->meeting_link : '' );

        $url = sprintf( 'https://www.google.com/calendar/render?action=TEMPLATE&text=%s&dates=%s/%s&details=%s&location=%s', $text, $dtstart, $dtend, $details, $location );

        return $url;
    }
}
