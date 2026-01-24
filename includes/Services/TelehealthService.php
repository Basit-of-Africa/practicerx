<?php
/**
 * Telehealth Service
 *
 * Video consultation integration (Zoom & Twilio Video)
 *
 * @package PracticeRx
 */

namespace PracticeRx\Services;

use PracticeRx\Models\TelehealthSession;
use PracticeRx\Models\Appointment;
use PracticeRx\Models\Client;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TelehealthService {
	
	/**
	 * Supported providers
	 */
	const PROVIDER_ZOOM = 'zoom';
	const PROVIDER_TWILIO = 'twilio';
	
	/**
	 * Create telehealth session
	 *
	 * @param array $data Session data
	 * @return int|false Session ID or false on failure
	 */
	public static function create_session( $data ) {
		$provider = $data['provider'] ?? self::PROVIDER_ZOOM;
		
		// Create meeting based on provider
		$meeting_data = self::create_meeting( $provider, $data );
		
		if ( ! $meeting_data ) {
			return false;
		}
		
		// Store session
		$session_data = array(
			'appointment_id'   => $data['appointment_id'] ?? null,
			'client_id'        => $data['client_id'],
			'practitioner_id'  => $data['practitioner_id'],
			'provider'         => $provider,
			'meeting_id'       => $meeting_data['meeting_id'],
			'meeting_url'      => $meeting_data['join_url'],
			'meeting_password' => $meeting_data['password'] ?? '',
			'host_url'         => $meeting_data['start_url'] ?? '',
			'start_time'       => $data['start_time'],
			'duration'         => $data['duration'] ?? 60,
			'status'           => 'scheduled',
			'provider_data'    => wp_json_encode( $meeting_data ),
		);
		
		$session_id = TelehealthSession::create( $session_data );
		
		// Send notification to client
		if ( $session_id ) {
			self::send_session_notification( $session_id );
		}
		
		return $session_id;
	}
	
	/**
	 * Create meeting with provider
	 *
	 * @param string $provider Provider name
	 * @param array $data Meeting data
	 * @return array|false Meeting details or false
	 */
	private static function create_meeting( $provider, $data ) {
		switch ( $provider ) {
			case self::PROVIDER_ZOOM:
				return self::create_zoom_meeting( $data );
			case self::PROVIDER_TWILIO:
				return self::create_twilio_room( $data );
			default:
				return false;
		}
	}
	
	/**
	 * Create Zoom meeting
	 *
	 * @param array $data Meeting data
	 * @return array|false
	 */
	private static function create_zoom_meeting( $data ) {
		$api_key = get_option( 'ppms_zoom_api_key' );
		$api_secret = get_option( 'ppms_zoom_api_secret' );
		
		if ( empty( $api_key ) || empty( $api_secret ) ) {
			return false;
		}
		
		// Generate JWT token
		$token = self::generate_zoom_jwt( $api_key, $api_secret );
		
		// Create meeting via Zoom API
		$response = wp_remote_post( 'https://api.zoom.us/v2/users/me/meetings', array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $token,
				'Content-Type'  => 'application/json',
			),
			'body'    => wp_json_encode( array(
				'topic'      => $data['topic'] ?? 'Telehealth Consultation',
				'type'       => 2, // Scheduled meeting
				'start_time' => date( 'Y-m-d\TH:i:s', strtotime( $data['start_time'] ) ),
				'duration'   => $data['duration'] ?? 60,
				'timezone'   => wp_timezone_string(),
				'settings'   => array(
					'host_video'        => true,
					'participant_video' => true,
					'join_before_host'  => false,
					'mute_upon_entry'   => false,
					'watermark'         => false,
					'audio'             => 'both',
					'auto_recording'    => 'cloud',
				),
			) ),
		) );
		
		if ( is_wp_error( $response ) ) {
			return false;
		}
		
		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		
		if ( empty( $body['id'] ) ) {
			return false;
		}
		
		return array(
			'meeting_id' => $body['id'],
			'join_url'   => $body['join_url'],
			'start_url'  => $body['start_url'],
			'password'   => $body['password'] ?? '',
		);
	}
	
	/**
	 * Create Twilio video room
	 *
	 * @param array $data Room data
	 * @return array|false
	 */
	private static function create_twilio_room( $data ) {
		$account_sid = get_option( 'ppms_twilio_account_sid' );
		$auth_token = get_option( 'ppms_twilio_auth_token' );
		
		if ( empty( $account_sid ) || empty( $auth_token ) ) {
			return false;
		}
		
		$room_name = 'session_' . time() . '_' . wp_rand( 1000, 9999 );
		
		// Create room via Twilio API
		$response = wp_remote_post( "https://video.twilio.com/v1/Rooms", array(
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( $account_sid . ':' . $auth_token ),
			),
			'body'    => array(
				'UniqueName' => $room_name,
				'Type'       => 'group',
				'MaxParticipants' => 2,
			),
		) );
		
		if ( is_wp_error( $response ) ) {
			return false;
		}
		
		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		
		if ( empty( $body['sid'] ) ) {
			return false;
		}
		
		// Generate access tokens for host and client
		$host_token = self::generate_twilio_token( $account_sid, $auth_token, $room_name, 'practitioner_' . $data['practitioner_id'] );
		$client_token = self::generate_twilio_token( $account_sid, $auth_token, $room_name, 'client_' . $data['client_id'] );
		
		return array(
			'meeting_id'   => $body['sid'],
			'join_url'     => site_url( '/telehealth/' . $room_name . '?token=' . $client_token ),
			'start_url'    => site_url( '/telehealth/' . $room_name . '?token=' . $host_token ),
			'room_name'    => $room_name,
			'host_token'   => $host_token,
			'client_token' => $client_token,
		);
	}
	
	/**
	 * Generate Zoom JWT token
	 *
	 * @param string $api_key API Key
	 * @param string $api_secret API Secret
	 * @return string
	 */
	private static function generate_zoom_jwt( $api_key, $api_secret ) {
		// Simplified JWT generation - in production use proper JWT library
		$header = array( 'alg' => 'HS256', 'typ' => 'JWT' );
		$payload = array(
			'iss' => $api_key,
			'exp' => time() + 3600,
		);
		
		$header_encoded = rtrim( strtr( base64_encode( wp_json_encode( $header ) ), '+/', '-_' ), '=' );
		$payload_encoded = rtrim( strtr( base64_encode( wp_json_encode( $payload ) ), '+/', '-_' ), '=' );
		
		$signature = hash_hmac( 'sha256', $header_encoded . '.' . $payload_encoded, $api_secret, true );
		$signature_encoded = rtrim( strtr( base64_encode( $signature ), '+/', '-_' ), '=' );
		
		return $header_encoded . '.' . $payload_encoded . '.' . $signature_encoded;
	}
	
	/**
	 * Generate Twilio access token (simplified)
	 *
	 * @param string $account_sid Account SID
	 * @param string $auth_token Auth token
	 * @param string $room_name Room name
	 * @param string $identity User identity
	 * @return string
	 */
	private static function generate_twilio_token( $account_sid, $auth_token, $room_name, $identity ) {
		// In production, use Twilio PHP SDK for proper token generation
		// This is a placeholder implementation
		return base64_encode( $account_sid . ':' . $room_name . ':' . $identity );
	}
	
	/**
	 * Send session notification to client
	 *
	 * @param int $session_id Session ID
	 * @return bool
	 */
	private static function send_session_notification( $session_id ) {
		$session = TelehealthSession::get( $session_id );
		if ( ! $session ) {
			return false;
		}
		
		$client = Client::get( $session->client_id );
		if ( ! $client ) {
			return false;
		}
		
		// Send email notification
		$subject = __( 'Telehealth Session Scheduled', 'practicerx' );
		$message = sprintf(
			__( 'Your telehealth session is scheduled for %s. Join URL: %s', 'practicerx' ),
			date( 'F j, Y g:i A', strtotime( $session->start_time ) ),
			$session->meeting_url
		);
		
		$user = get_user_by( 'id', $client->user_id );
		if ( $user ) {
			wp_mail( $user->user_email, $subject, $message );
		}
		
		return true;
	}
	
	/**
	 * End session and get recording
	 *
	 * @param int $session_id Session ID
	 * @return bool
	 */
	public static function end_session( $session_id ) {
		$session = TelehealthSession::get( $session_id );
		if ( ! $session ) {
			return false;
		}
		
		// Update session status
		TelehealthSession::update( $session_id, array(
			'status' => 'completed',
		) );
		
		// Retrieve recording based on provider
		// This would be implemented per provider's API
		
		return true;
	}
}
