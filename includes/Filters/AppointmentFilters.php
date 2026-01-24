<?php
/**
 * Appointment Filters
 * 
 * Handles appointment-related filters and hooks.
 */

namespace PracticeRx\Filters;

use PracticeRx\Core\Helper;
use PracticeRx\Models\Appointment;

class AppointmentFilters {

	public function __construct() {
		// Validate appointment data before save
		add_filter( 'ppms_before_appointment_save', array( $this, 'validate_appointment' ), 10, 1 );
		
		// Send notifications after appointment creation
		add_action( 'ppms_after_appointment_created', array( $this, 'send_appointment_notifications' ), 10, 1 );
		
		// Check time slot availability
		add_filter( 'ppms_check_appointment_availability', array( $this, 'check_availability' ), 10, 3 );
		
		// Modify appointment query
		add_filter( 'ppms_appointment_query_args', array( $this, 'filter_query_args' ), 10, 2 );
	}

	/**
	 * Validate appointment data
	 *
	 * @param array $data Appointment data
	 * @return array|\WP_Error
	 */
	public function validate_appointment( $data ) {
		return Helper::validate_appointment_data( $data );
	}

	/**
	 * Send notifications after appointment is created
	 *
	 * @param int $appointment_id Appointment ID
	 * @return void
	 */
	public function send_appointment_notifications( $appointment_id ) {
		$appointment = Appointment::get( $appointment_id );
		
		if ( ! $appointment ) {
			return;
		}

		// Hook for email notifications
		do_action( 'ppms_send_appointment_email', $appointment );
		
		// Hook for SMS notifications
		do_action( 'ppms_send_appointment_sms', $appointment );
	}

	/**
	 * Check if time slot is available
	 *
	 * @param bool   $available Current availability status
	 * @param int    $practitioner_id Practitioner ID
	 * @param array  $time_data Start and end time
	 * @return bool
	 */
	public function check_availability( $available, $practitioner_id, $time_data ) {
		if ( ! $available ) {
			return false;
		}

		$start_time = $time_data['start_time'] ?? '';
		$end_time = $time_data['end_time'] ?? '';
		$exclude_id = $time_data['exclude_appointment_id'] ?? 0;

		if ( empty( $start_time ) || empty( $end_time ) ) {
			return false;
		}

		return Helper::is_time_slot_available( $practitioner_id, $start_time, $end_time, $exclude_id );
	}

	/**
	 * Filter appointment query arguments based on user role
	 *
	 * @param array  $args Query arguments
	 * @param string $context Query context
	 * @return array
	 */
	public function filter_query_args( $args, $context ) {
		$user_role = Helper::get_current_user_role();

		// Patients can only see their own appointments
		if ( $user_role === 'ppms_patient' ) {
			global $wpdb;
			$patient_table = ppms_get_table( 'ppms_patients' );
			$patient = $wpdb->get_row( $wpdb->prepare(
				"SELECT * FROM {$patient_table} WHERE user_id = %d",
				get_current_user_id()
			) );

			if ( $patient ) {
				$args['patient_id'] = $patient->id;
			}
		}

		return $args;
	}
}
