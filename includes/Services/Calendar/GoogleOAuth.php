<?php
namespace PracticeRx\Services\Calendar;

use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class GoogleOAuth {

    const OPTION_TOKEN = 'ppms_google_tokens';

    public static function get_client( $practitioner_id = 0 ) {
        if ( ! class_exists( '\\Google_Client' ) ) {
            return new WP_Error( 'google_client_missing', __( 'google/apiclient is not installed. Run `composer require google/apiclient` in the plugin folder.', 'practicerx' ) );
        }
        $client_id = get_option( 'ppms_google_client_id' );
        $client_secret = get_option( 'ppms_google_client_secret' );
        $redirect = get_option( 'ppms_google_redirect_uri' );

        $client = new \Google_Client();
        $client->setClientId( $client_id );
        $client->setClientSecret( $client_secret );
        $client->setRedirectUri( $redirect ?: admin_url( 'admin-post.php?action=ppms_google_oauth_callback' ) );
        $client->setAccessType( 'offline' );
        $client->setPrompt( 'consent' );
        $client->addScope( 'https://www.googleapis.com/auth/calendar.events' );

        // Load saved tokens (global or practitioner-specific)
        if ( $practitioner_id ) {
            $all = get_option( 'ppms_practitioner_tokens', array() );
            $tokens = isset( $all[ $practitioner_id ] ) ? $all[ $practitioner_id ] : null;
        } else {
            $tokens = get_option( self::OPTION_TOKEN );
        }

        if ( ! empty( $tokens ) && isset( $tokens['access_token'] ) ) {
            $client->setAccessToken( $tokens );
        }

        return $client;
    }

    public static function get_auth_url( $practitioner_id = 0 ) {
        $client = self::get_client( $practitioner_id );
        if ( is_wp_error( $client ) ) {
            return $client;
        }

        // include practitioner id in state so callback can associate tokens
        if ( $practitioner_id ) {
            $client->setState( 'practitioner:' . intval( $practitioner_id ) );
        }

        return $client->createAuthUrl();
    }

    public static function handle_callback( $code, $practitioner_id = 0 ) {
        $client = self::get_client( $practitioner_id );
        if ( is_wp_error( $client ) ) {
            return $client;
        }

        try {
            $token = $client->fetchAccessTokenWithAuthCode( $code );
        } catch ( \Exception $e ) {
            return new WP_Error( 'google_fetch_error', $e->getMessage() );
        }

        if ( isset( $token['error'] ) ) {
            return new WP_Error( 'google_token_error', maybe_serialize( $token ) );
        }

        // store token with expiration time (global or practitioner-specific)
        if ( $practitioner_id ) {
            $all = get_option( 'ppms_practitioner_tokens', array() );
            $all[ $practitioner_id ] = $token;
            update_option( 'ppms_practitioner_tokens', $all );
        } else {
            update_option( self::OPTION_TOKEN, $token );
        }

        return true;
    }

    public static function refresh_token_if_needed( $practitioner_id = 0 ) {
        $client = self::get_client( $practitioner_id );
        if ( is_wp_error( $client ) ) {
            return $client;
        }

        if ( $client->isAccessTokenExpired() ) {
            if ( $practitioner_id ) {
                $all = get_option( 'ppms_practitioner_tokens', array() );
                $tokens = isset( $all[ $practitioner_id ] ) ? $all[ $practitioner_id ] : null;
                if ( ! empty( $tokens['refresh_token'] ) ) {
                    $new = $client->fetchAccessTokenWithRefreshToken( $tokens['refresh_token'] );
                    if ( isset( $new['access_token'] ) ) {
                        $all[ $practitioner_id ] = array_merge( $tokens, $new );
                        update_option( 'ppms_practitioner_tokens', $all );
                        return true;
                    }
                    return new WP_Error( 'google_refresh_failed', __( 'Failed to refresh Google access token.', 'practicerx' ) );
                }
            } else {
                $tokens = get_option( self::OPTION_TOKEN );
                if ( ! empty( $tokens['refresh_token'] ) ) {
                    $new = $client->fetchAccessTokenWithRefreshToken( $tokens['refresh_token'] );
                    if ( isset( $new['access_token'] ) ) {
                        update_option( self::OPTION_TOKEN, array_merge( $tokens, $new ) );
                        return true;
                    }
                    return new WP_Error( 'google_refresh_failed', __( 'Failed to refresh Google access token.', 'practicerx' ) );
                }
            }
        }

        return true;
    }

    public static function disconnect( $practitioner_id = 0 ) {
        if ( $practitioner_id ) {
            $all = get_option( 'ppms_practitioner_tokens', array() );
            if ( isset( $all[ $practitioner_id ] ) ) {
                unset( $all[ $practitioner_id ] );
                update_option( 'ppms_practitioner_tokens', $all );
            }
            return true;
        }

        delete_option( self::OPTION_TOKEN );
        return true;
    }
}
