<?php
namespace PracticeRx\Services;

use PracticeRx\Models\Client;

/**
 * Class SMSService
 *
 * Handles SMS notifications via Twilio or other providers.
 */
class SMSService {

	/**
	 * SMS provider.
	 *
	 * @var string
	 */
	private $provider;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->provider = ppms_get_option( 'sms_provider', 'twilio' );
	}

	/**
	 * Send SMS to client.
	 *
	 * @param int    $client_id Client ID.
	 * @param string $message Message text.
	 * @return array|\WP_Error
	 */
	public function send_to_client( $client_id, $message ) {
		$client = Client::get( $client_id );
		if ( ! $client || empty( $client->phone ) ) {
			return new \WP_Error( 'invalid_recipient', __( 'Client not found or no phone number', 'practicerx' ) );
		}

		return $this->send( $client->phone, $message, $client_id, 'client' );
	}

	/**
	 * Send SMS to phone number.
	 *
	 * @param string $phone_number Phone number.
	 * @param string $message Message text.
	 * @param int    $recipient_id Optional recipient ID.
	 * @param string $recipient_type Recipient type (client, practitioner).
	 * @return array|\WP_Error
	 */
	public function send( $phone_number, $message, $recipient_id = null, $recipient_type = 'client' ) {
		// Validate phone number
		$phone_number = $this->format_phone_number( $phone_number );
		if ( empty( $phone_number ) ) {
			return new \WP_Error( 'invalid_phone', __( 'Invalid phone number', 'practicerx' ) );
		}

		// Apply filter to message
		$message = apply_filters( 'ppms_sms_message', $message, $recipient_id, $recipient_type );

		// Log the SMS attempt
		$log_id = $this->log_sms( array(
			'recipient_id'   => $recipient_id,
			'recipient_type' => $recipient_type,
			'phone_number'   => $phone_number,
			'message'        => $message,
			'provider'       => $this->provider,
			'status'         => 'pending',
			'sent_by'        => get_current_user_id(),
		) );

		// Send via provider
		$result = $this->send_via_provider( $phone_number, $message );

		if ( is_wp_error( $result ) ) {
			$this->update_sms_log( $log_id, array(
				'status'        => 'failed',
				'error_message' => $result->get_error_message(),
			) );
			return $result;
		}

		// Update log with success
		$this->update_sms_log( $log_id, array(
			'status'             => 'sent',
			'provider_message_id' => $result['message_id'] ?? '',
			'sent_at'            => current_time( 'mysql' ),
		) );

		do_action( 'ppms_sms_sent', $log_id, $phone_number, $message );

		return array(
			'success'    => true,
			'log_id'     => $log_id,
			'message_id' => $result['message_id'] ?? '',
		);
	}

	/**
	 * Send SMS via provider (Twilio, etc).
	 *
	 * @param string $phone_number Phone number.
	 * @param string $message Message text.
	 * @return array|\WP_Error
	 */
	private function send_via_provider( $phone_number, $message ) {
		$provider = apply_filters( 'ppms_sms_provider', $this->provider );

		switch ( $provider ) {
			case 'twilio':
				return $this->send_via_twilio( $phone_number, $message );
			
			default:
				return apply_filters( 'ppms_sms_send_custom_provider', new \WP_Error( 'no_provider', __( 'SMS provider not configured', 'practicerx' ) ), $phone_number, $message, $provider );
		}
	}

	/**
	 * Send SMS via Twilio.
	 *
	 * @param string $phone_number Phone number.
	 * @param string $message Message text.
	 * @return array|\WP_Error
	 */
	private function send_via_twilio( $phone_number, $message ) {
		$account_sid = ppms_get_option( 'twilio_account_sid' );
		$auth_token  = ppms_get_option( 'twilio_auth_token' );
		$from_number = ppms_get_option( 'twilio_phone_number' );

		if ( empty( $account_sid ) || empty( $auth_token ) || empty( $from_number ) ) {
			return new \WP_Error( 'twilio_not_configured', __( 'Twilio credentials not configured', 'practicerx' ) );
		}

		// Make API call to Twilio
		$response = wp_remote_post( "https://api.twilio.com/2010-04-01/Accounts/{$account_sid}/Messages.json", array(
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( "{$account_sid}:{$auth_token}" ),
			),
			'body' => array(
				'From' => $from_number,
				'To'   => $phone_number,
				'Body' => $message,
			),
		) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $body['error_code'] ) ) {
			return new \WP_Error( 'twilio_error', $body['message'] ?? __( 'Twilio API error', 'practicerx' ) );
		}

		return array(
			'message_id' => $body['sid'] ?? '',
			'status'     => $body['status'] ?? 'sent',
		);
	}

	/**
	 * Format phone number to E.164 format.
	 *
	 * @param string $phone Phone number.
	 * @return string
	 */
	private function format_phone_number( $phone ) {
		// Remove all non-numeric characters
		$phone = preg_replace( '/[^0-9]/', '', $phone );

		// Add +1 for US numbers if not present
		if ( strlen( $phone ) === 10 ) {
			$phone = '+1' . $phone;
		} elseif ( strlen( $phone ) === 11 && substr( $phone, 0, 1 ) === '1' ) {
			$phone = '+' . $phone;
		} elseif ( substr( $phone, 0, 1 ) !== '+' ) {
			$phone = '+' . $phone;
		}

		return $phone;
	}

	/**
	 * Log SMS to database.
	 *
	 * @param array $data SMS data.
	 * @return int Log ID.
	 */
	private function log_sms( $data ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ppms_sms_logs';

		$wpdb->insert( $table, $data );
		return $wpdb->insert_id;
	}

	/**
	 * Update SMS log.
	 *
	 * @param int   $log_id Log ID.
	 * @param array $data Update data.
	 */
	private function update_sms_log( $log_id, $data ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ppms_sms_logs';

		$wpdb->update( $table, $data, array( 'id' => $log_id ) );
	}

	/**
	 * Send appointment reminder SMS.
	 *
	 * @param int $appointment_id Appointment ID.
	 * @return array|\WP_Error
	 */
	public function send_appointment_reminder( $appointment_id ) {
		$appointment = \PracticeRx\Models\Appointment::get( $appointment_id );
		if ( ! $appointment ) {
			return new \WP_Error( 'not_found', __( 'Appointment not found', 'practicerx' ) );
		}

		$client = Client::get( $appointment->patient_id );
		if ( ! $client ) {
			return new \WP_Error( 'client_not_found', __( 'Client not found', 'practicerx' ) );
		}

		$message = sprintf(
			__( 'Reminder: You have an appointment on %s. Reply CONFIRM to confirm.', 'practicerx' ),
			date( 'M j, Y \a\t g:i A', strtotime( $appointment->start_time ) )
		);

		$message = apply_filters( 'ppms_appointment_reminder_sms', $message, $appointment, $client );

		return $this->send_to_client( $client->id, $message );
	}
}
