<?php
/**
 * Helper Class
 * 
 * Core helper methods for PracticeRx plugin operations.
 */

namespace PracticeRx\Core;

use PracticeRx\Auth\RoleManager;

class Helper {

	/**
	 * Get plugin option with namespace
	 *
	 * @param string $name Option name (without ppms_ prefix)
	 * @param mixed  $default Default value
	 * @return mixed
	 */
	public static function get_option( $name, $default = false ) {
		return get_option( 'ppms_' . $name, $default );
	}

	/**
	 * Update plugin option with namespace
	 *
	 * @param string $name Option name (without ppms_ prefix)
	 * @param mixed  $value Option value
	 * @param string $autoload Autoload option
	 * @return bool
	 */
	public static function update_option( $name, $value, $autoload = 'no' ) {
		return update_option( 'ppms_' . $name, $value, $autoload );
	}

	/**
	 * Delete plugin option
	 *
	 * @param string $name Option name (without ppms_ prefix)
	 * @return bool
	 */
	public static function delete_option( $name ) {
		return delete_option( 'ppms_' . $name );
	}

	/**
	 * Get current logged-in user ID
	 *
	 * @return int
	 */
	public static function get_current_user_id() {
		return get_current_user_id();
	}

	/**
	 * Get current user role
	 *
	 * @return string|null
	 */
	public static function get_current_user_role() {
		$user = wp_get_current_user();
		
		if ( RoleManager::is_practitioner() ) {
			return Constants::ROLE_PRACTITIONER;
		}
		
		if ( RoleManager::is_patient() ) {
			return Constants::ROLE_PATIENT;
		}
		
		if ( in_array( 'administrator', $user->roles, true ) ) {
			return 'administrator';
		}
		
		return null;
	}

	/**
	 * Check if user has permission
	 *
	 * @param string $capability Capability to check
	 * @param int    $user_id User ID (0 for current user)
	 * @return bool
	 */
	public static function user_can( $capability, $user_id = 0 ) {
		if ( ! $user_id ) {
			$user_id = self::get_current_user_id();
		}
		return user_can( $user_id, $capability );
	}

	/**
	 * Validate and sanitize appointment data
	 *
	 * @param array $data Appointment data
	 * @return array|\WP_Error
	 */
	public static function validate_appointment_data( $data ) {
		$errors = new \WP_Error();

		// Required fields
		if ( empty( $data['patient_id'] ) ) {
			$errors->add( 'missing_patient', __( 'Patient ID is required.', 'practicerx' ) );
		}

		if ( empty( $data['practitioner_id'] ) ) {
			$errors->add( 'missing_practitioner', __( 'Practitioner ID is required.', 'practicerx' ) );
		}

		if ( empty( $data['start_time'] ) ) {
			$errors->add( 'missing_start_time', __( 'Start time is required.', 'practicerx' ) );
		}

		if ( empty( $data['end_time'] ) ) {
			$errors->add( 'missing_end_time', __( 'End time is required.', 'practicerx' ) );
		}

		// Validate datetime format
		if ( ! empty( $data['start_time'] ) && ! strtotime( $data['start_time'] ) ) {
			$errors->add( 'invalid_start_time', __( 'Invalid start time format.', 'practicerx' ) );
		}

		if ( ! empty( $data['end_time'] ) && ! strtotime( $data['end_time'] ) ) {
			$errors->add( 'invalid_end_time', __( 'Invalid end time format.', 'practicerx' ) );
		}

		// Check if end time is after start time
		if ( ! empty( $data['start_time'] ) && ! empty( $data['end_time'] ) ) {
			if ( strtotime( $data['end_time'] ) <= strtotime( $data['start_time'] ) ) {
				$errors->add( 'invalid_time_range', __( 'End time must be after start time.', 'practicerx' ) );
			}
		}

		if ( $errors->has_errors() ) {
			return $errors;
		}

		// Sanitize data
		return array(
			'patient_id'       => absint( $data['patient_id'] ),
			'practitioner_id'  => absint( $data['practitioner_id'] ),
			'service_id'       => ! empty( $data['service_id'] ) ? absint( $data['service_id'] ) : 0,
			'start_time'       => sanitize_text_field( $data['start_time'] ),
			'end_time'         => sanitize_text_field( $data['end_time'] ),
			'status'           => ! empty( $data['status'] ) ? ppms_sanitize_status( $data['status'] ) : Constants::APPOINTMENT_STATUS_SCHEDULED,
			'notes'            => ! empty( $data['notes'] ) ? wp_kses_post( $data['notes'] ) : '',
			'meeting_link'     => ! empty( $data['meeting_link'] ) ? esc_url_raw( $data['meeting_link'] ) : '',
		);
	}

	/**
	 * Validate and sanitize patient data
	 *
	 * @param array $data Patient data
	 * @return array|\WP_Error
	 */
	public static function validate_patient_data( $data ) {
		$errors = new \WP_Error();

		// Required fields
		if ( empty( $data['user_id'] ) ) {
			$errors->add( 'missing_user_id', __( 'User ID is required.', 'practicerx' ) );
		}

		if ( $errors->has_errors() ) {
			return $errors;
		}

		// Sanitize data
		return array(
			'user_id'                 => absint( $data['user_id'] ),
			'dob'                     => ! empty( $data['dob'] ) ? sanitize_text_field( $data['dob'] ) : '',
			'gender'                  => ! empty( $data['gender'] ) ? sanitize_text_field( $data['gender'] ) : '',
			'phone'                   => ! empty( $data['phone'] ) ? sanitize_text_field( $data['phone'] ) : '',
			'address'                 => ! empty( $data['address'] ) ? sanitize_textarea_field( $data['address'] ) : '',
			'emergency_contact'       => ! empty( $data['emergency_contact'] ) ? wp_json_encode( $data['emergency_contact'] ) : '',
			'medical_history_summary' => ! empty( $data['medical_history_summary'] ) ? wp_kses_post( $data['medical_history_summary'] ) : '',
		);
	}

	/**
	 * Format response for REST API
	 *
	 * @param bool   $success Success status
	 * @param mixed  $data Response data
	 * @param string $message Response message
	 * @return array
	 */
	public static function format_response( $success, $data = array(), $message = '' ) {
		return array(
			'status'  => $success,
			'message' => $message,
			'data'    => $data,
		);
	}

	/**
	 * Get currency symbol
	 *
	 * @param string $currency Currency code
	 * @return string
	 */
	public static function get_currency_symbol( $currency = 'USD' ) {
		$symbols = array(
			'USD' => '$',
			'EUR' => '€',
			'GBP' => '£',
			'JPY' => '¥',
			'INR' => '₹',
			'NGN' => '₦',
			'ZAR' => 'R',
			'AUD' => 'A$',
			'CAD' => 'C$',
		);

		return isset( $symbols[ $currency ] ) ? $symbols[ $currency ] : $currency;
	}

	/**
	 * Log message to WordPress debug log
	 *
	 * @param string $message Message to log
	 * @param string $level Log level (info, warning, error)
	 * @return void
	 */
	public static function log( $message, $level = 'info' ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			$formatted_message = sprintf(
				'[PracticeRx] [%s] %s',
				strtoupper( $level ),
				$message
			);
			error_log( $formatted_message );
		}
	}

	/**
	 * Check if appointment time slot is available
	 *
	 * @param int    $practitioner_id Practitioner ID
	 * @param string $start_time Start time
	 * @param string $end_time End time
	 * @param int    $exclude_appointment_id Appointment ID to exclude from check
	 * @return bool
	 */
	public static function is_time_slot_available( $practitioner_id, $start_time, $end_time, $exclude_appointment_id = 0 ) {
		global $wpdb;
		$table = ppms_get_table( Constants::TABLE_APPOINTMENTS );

		$query = $wpdb->prepare(
			"SELECT COUNT(*) FROM {$table} 
			WHERE practitioner_id = %d 
			AND status NOT IN ('cancelled', 'no_show')
			AND (
				(start_time < %s AND end_time > %s)
				OR (start_time < %s AND end_time > %s)
				OR (start_time >= %s AND end_time <= %s)
			)",
			$practitioner_id,
			$end_time,
			$start_time,
			$start_time,
			$start_time,
			$start_time,
			$end_time
		);

		if ( $exclude_appointment_id ) {
			$query .= $wpdb->prepare( ' AND id != %d', $exclude_appointment_id );
		}

		$count = $wpdb->get_var( $query );

		return $count == 0;
	}
}
