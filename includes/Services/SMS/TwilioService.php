<?php
namespace PracticeRx\Services\SMS;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TwilioService {

    public static function send_sms( $to, $body ) {
        $enabled = get_option( 'ppms_twilio_enabled', 0 );
        if ( ! $enabled ) {
            return new \WP_Error( 'twilio_disabled', __( 'Twilio SMS is disabled in settings.', 'practicerx' ) );
        }

        $sid = get_option( 'ppms_twilio_account_sid' );
        $token = get_option( 'ppms_twilio_auth_token' );
        $from = get_option( 'ppms_twilio_from_number' );

        if ( empty( $sid ) || empty( $token ) || empty( $from ) ) {
            return new \WP_Error( 'twilio_credentials_missing', __( 'Twilio credentials are not configured.', 'practicerx' ) );
        }

        $uri = sprintf( 'https://api.twilio.com/2010-04-01/Accounts/%s/Messages.json', rawurlencode( $sid ) );

        $args = array(
            'body' => array(
                'From' => $from,
                'To'   => $to,
                'Body' => $body,
            ),
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode( $sid . ':' . $token ),
            ),
            'timeout' => 15,
        );

        $response = wp_remote_post( $uri, $args );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body = wp_remote_retrieve_body( $response );

        if ( $code >= 200 && $code < 300 ) {
            return true;
        }

        return new \WP_Error( 'twilio_error', sprintf( __( 'Twilio API error: %s', 'practicerx' ), $body ) );
    }
}
