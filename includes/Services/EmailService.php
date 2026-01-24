<?php
namespace PracticeRx\Services;

use PracticeRx\Models\Client;

/**
 * Class EmailService
 *
 * Handles email notifications and templates.
 */
class EmailService {

	/**
	 * Send email to client.
	 *
	 * @param int    $client_id Client ID.
	 * @param string $subject Email subject.
	 * @param string $message Email message.
	 * @param string $template Optional template name.
	 * @return array|\WP_Error
	 */
	public function send_to_client( $client_id, $subject, $message, $template = '' ) {
		$client = Client::get( $client_id );
		if ( ! $client || empty( $client->email ) ) {
			return new \WP_Error( 'invalid_recipient', __( 'Client not found or no email address', 'practicerx' ) );
		}

		return $this->send( $client->email, $subject, $message, $client_id, 'client', $template );
	}

	/**
	 * Send email.
	 *
	 * @param string $email_address Email address.
	 * @param string $subject Email subject.
	 * @param string $message Email message.
	 * @param int    $recipient_id Optional recipient ID.
	 * @param string $recipient_type Recipient type.
	 * @param string $template Template name.
	 * @return array|\WP_Error
	 */
	public function send( $email_address, $subject, $message, $recipient_id = null, $recipient_type = 'client', $template = '' ) {
		// Validate email
		if ( ! is_email( $email_address ) ) {
			return new \WP_Error( 'invalid_email', __( 'Invalid email address', 'practicerx' ) );
		}

		// Apply filters
		$subject = apply_filters( 'ppms_email_subject', $subject, $recipient_id, $template );
		$message = apply_filters( 'ppms_email_message', $message, $recipient_id, $template );

		// Wrap message in template
		$html_message = $this->get_email_template( $message, $subject );

		// Log the email attempt
		$log_id = $this->log_email( array(
			'recipient_id'   => $recipient_id,
			'recipient_type' => $recipient_type,
			'email_address'  => $email_address,
			'subject'        => $subject,
			'message'        => $message,
			'template'       => $template,
			'status'         => 'pending',
			'sent_by'        => get_current_user_id(),
		) );

		// Send email
		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . get_option( 'blogname' ) . ' <' . get_option( 'admin_email' ) . '>',
		);

		$sent = wp_mail( $email_address, $subject, $html_message, $headers );

		if ( ! $sent ) {
			$this->update_email_log( $log_id, array(
				'status'        => 'failed',
				'error_message' => __( 'Failed to send email', 'practicerx' ),
			) );
			return new \WP_Error( 'send_failed', __( 'Failed to send email', 'practicerx' ) );
		}

		// Update log with success
		$this->update_email_log( $log_id, array(
			'status'  => 'sent',
			'sent_at' => current_time( 'mysql' ),
		) );

		do_action( 'ppms_email_sent', $log_id, $email_address, $subject );

		return array(
			'success' => true,
			'log_id'  => $log_id,
		);
	}

	/**
	 * Get email template wrapper.
	 *
	 * @param string $content Email content.
	 * @param string $subject Email subject.
	 * @return string
	 */
	private function get_email_template( $content, $subject ) {
		$template = '
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>' . esc_html( $subject ) . '</title>
	<style>
		body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; margin: 0; padding: 0; }
		.container { max-width: 600px; margin: 20px auto; background: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
		.header { text-align: center; padding-bottom: 20px; border-bottom: 2px solid #007cba; margin-bottom: 20px; }
		.header h1 { margin: 0; color: #007cba; font-size: 24px; }
		.content { padding: 20px 0; }
		.footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; font-size: 12px; color: #666; }
	</style>
</head>
<body>
	<div class="container">
		<div class="header">
			<h1>' . esc_html( get_option( 'blogname' ) ) . '</h1>
		</div>
		<div class="content">
			' . wpautop( $content ) . '
		</div>
		<div class="footer">
			<p>&copy; ' . date( 'Y' ) . ' ' . esc_html( get_option( 'blogname' ) ) . '. All rights reserved.</p>
		</div>
	</div>
</body>
</html>';

		return apply_filters( 'ppms_email_template', $template, $content, $subject );
	}

	/**
	 * Log email to database.
	 *
	 * @param array $data Email data.
	 * @return int Log ID.
	 */
	private function log_email( $data ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ppms_email_logs';

		$wpdb->insert( $table, $data );
		return $wpdb->insert_id;
	}

	/**
	 * Update email log.
	 *
	 * @param int   $log_id Log ID.
	 * @param array $data Update data.
	 */
	private function update_email_log( $log_id, $data ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ppms_email_logs';

		$wpdb->update( $table, $data, array( 'id' => $log_id ) );
	}

	/**
	 * Send appointment confirmation email.
	 *
	 * @param int $appointment_id Appointment ID.
	 * @return array|\WP_Error
	 */
	public function send_appointment_confirmation( $appointment_id ) {
		$appointment = \PracticeRx\Models\Appointment::get( $appointment_id );
		if ( ! $appointment ) {
			return new \WP_Error( 'not_found', __( 'Appointment not found', 'practicerx' ) );
		}

		$client = Client::get( $appointment->patient_id );
		if ( ! $client ) {
			return new \WP_Error( 'client_not_found', __( 'Client not found', 'practicerx' ) );
		}

		$subject = __( 'Appointment Confirmation', 'practicerx' );
		$message = sprintf(
			__( 'Your appointment has been confirmed for %s.', 'practicerx' ),
			date( 'F j, Y \a\t g:i A', strtotime( $appointment->start_time ) )
		);

		$message = apply_filters( 'ppms_appointment_confirmation_email', $message, $appointment, $client );

		return $this->send_to_client( $client->id, $subject, $message, 'appointment_confirmation' );
	}

	/**
	 * Send appointment reminder email.
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

		$subject = __( 'Appointment Reminder', 'practicerx' );
		$message = sprintf(
			__( 'This is a reminder that you have an appointment on %s.', 'practicerx' ),
			date( 'F j, Y \a\t g:i A', strtotime( $appointment->start_time ) )
		);

		$message = apply_filters( 'ppms_appointment_reminder_email', $message, $appointment, $client );

		return $this->send_to_client( $client->id, $subject, $message, 'appointment_reminder' );
	}

	/**
	 * Send welcome email to new client.
	 *
	 * @param int $client_id Client ID.
	 * @return array|\WP_Error
	 */
	public function send_welcome_email( $client_id ) {
		$client = Client::get_with_user( $client_id );
		if ( ! $client ) {
			return new \WP_Error( 'client_not_found', __( 'Client not found', 'practicerx' ) );
		}

		$subject = sprintf( __( 'Welcome to %s', 'practicerx' ), get_option( 'blogname' ) );
		$message = sprintf(
			__( 'Welcome %s! Your account has been created. You can now book appointments and manage your health records.', 'practicerx' ),
			$client->first_name
		);

		$message = apply_filters( 'ppms_welcome_email', $message, $client );

		return $this->send_to_client( $client_id, $subject, $message, 'welcome' );
	}
}
