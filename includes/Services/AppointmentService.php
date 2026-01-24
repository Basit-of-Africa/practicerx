<?php
namespace PracticeRx\Services;

use PracticeRx\Models\Appointment;
use PracticeRx\Models\Practitioner;
use PracticeRx\Core\Constants;
use PracticeRx\Core\Helper;

/**
 * Class AppointmentService
 *
 * Handles business logic for appointments.
 */
class AppointmentService {

	/**
	 * Create a new appointment.
	 *
	 * @param array $data Appointment data.
	 * @return int|\WP_Error Appointment ID or error.
	 */
	public function create_appointment( $data ) {
		// Validate appointment data
		$validation = Helper::validate_appointment_data( $data );
		if ( is_wp_error( $validation ) ) {
			return $validation;
		}

		// Check availability
		if ( ! $this->is_slot_available( $data['practitioner_id'], $data['start_time'], $data['end_time'] ) ) {
			return new \WP_Error( 'slot_unavailable', __( 'The selected slot is not available', 'practicerx' ), array( 'status' => 409 ) );
		}

		// Set default status
		if ( empty( $data['status'] ) ) {
			$data['status'] = Constants::APPOINTMENT_STATUS_SCHEDULED;
		}

		// Apply filter
		$data = apply_filters( 'ppms_before_appointment_create', $data );

		// Create appointment
		$id = Appointment::create( $data );

		if ( ! $id ) {
			return new \WP_Error( 'db_error', __( 'Could not save appointment', 'practicerx' ), array( 'status' => 500 ) );
		}

		do_action( 'ppms_appointment_created', $id, $data );

		return $id;
	}

	/**
	 * Update an appointment.
	 *
	 * @param int   $id Appointment ID.
	 * @param array $data Appointment data.
	 * @return bool|\WP_Error
	 */
	public function update_appointment( $id, $data ) {
		$appointment = Appointment::get( $id );
		if ( ! $appointment ) {
			return new \WP_Error( 'not_found', __( 'Appointment not found', 'practicerx' ), array( 'status' => 404 ) );
		}

		// Check availability if time is being changed
		if ( isset( $data['start_time'] ) && isset( $data['end_time'] ) ) {
			if ( ! $this->is_slot_available( $data['practitioner_id'] ?? $appointment->practitioner_id, $data['start_time'], $data['end_time'], $id ) ) {
				return new \WP_Error( 'slot_unavailable', __( 'The selected slot is not available', 'practicerx' ), array( 'status' => 409 ) );
			}
		}

		$data = apply_filters( 'ppms_before_appointment_update', $data, $id );

		$updated = Appointment::update( $id, $data );

		if ( false === $updated ) {
			return new \WP_Error( 'update_failed', __( 'Could not update appointment', 'practicerx' ), array( 'status' => 500 ) );
		}

		do_action( 'ppms_appointment_updated', $id, $data );

		return true;
	}

	/**
	 * Check if a slot is available.
	 *
	 * @param int    $practitioner_id Practitioner ID.
	 * @param string $start_time      Start time (Y-m-d H:i:s).
	 * @param string $end_time        End time (Y-m-d H:i:s).
	 * @param int    $exclude_id      Exclude appointment ID (for updates).
	 * @return bool
	 */
	public function is_slot_available( $practitioner_id, $start_time, $end_time, $exclude_id = null ) {
		// Allow filtering of availability
		$is_available = apply_filters( 'ppms_check_appointment_availability', true, $practitioner_id, $start_time, $end_time, $exclude_id );
		if ( ! $is_available ) {
			return false;
		}

		// Check database for conflicts
		return Appointment::is_time_slot_available( $practitioner_id, $start_time, $end_time, $exclude_id );
	}

	/**
	 * Cancel an appointment.
	 *
	 * @param int    $id Appointment ID.
	 * @param string $reason Cancellation reason.
	 * @return bool|\WP_Error
	 */
	public function cancel_appointment( $id, $reason = '' ) {
		$appointment = Appointment::get( $id );
		if ( ! $appointment ) {
			return new \WP_Error( 'not_found', __( 'Appointment not found', 'practicerx' ), array( 'status' => 404 ) );
		}

		if ( Constants::APPOINTMENT_STATUS_CANCELLED === $appointment->status ) {
			return new \WP_Error( 'already_cancelled', __( 'Appointment is already cancelled', 'practicerx' ), array( 'status' => 400 ) );
		}

		$updated = Appointment::update( $id, array(
			'status' => Constants::APPOINTMENT_STATUS_CANCELLED,
			'notes'  => $reason,
		) );

		if ( false === $updated ) {
			return new \WP_Error( 'cancel_failed', __( 'Could not cancel appointment', 'practicerx' ), array( 'status' => 500 ) );
		}

		do_action( 'ppms_appointment_cancelled', $id, $reason );

		return true;
	}

	/**
	 * Confirm an appointment.
	 *
	 * @param int $id Appointment ID.
	 * @return bool|\WP_Error
	 */
	public function confirm_appointment( $id ) {
		$appointment = Appointment::get( $id );
		if ( ! $appointment ) {
			return new \WP_Error( 'not_found', __( 'Appointment not found', 'practicerx' ), array( 'status' => 404 ) );
		}

		$updated = Appointment::update( $id, array(
			'status' => Constants::APPOINTMENT_STATUS_CONFIRMED,
		) );

		if ( false === $updated ) {
			return new \WP_Error( 'confirm_failed', __( 'Could not confirm appointment', 'practicerx' ), array( 'status' => 500 ) );
		}

		do_action( 'ppms_appointment_confirmed', $id );

		return true;
	}

	/**
	 * Get practitioner's schedule for a date.
	 *
	 * @param int    $practitioner_id Practitioner ID.
	 * @param string $date Date (Y-m-d).
	 * @return array
	 */
	public function get_daily_schedule( $practitioner_id, $date ) {
		$appointments = Appointment::get_by_range(
			$date . ' 00:00:00',
			$date . ' 23:59:59',
			$practitioner_id
		);

		return $appointments;
	}

	/**
	 * Get available time slots for a practitioner on a date.
	 *
	 * @param int    $practitioner_id Practitioner ID.
	 * @param string $date Date (Y-m-d).
	 * @param int    $duration Duration in minutes.
	 * @return array
	 */
	public function get_available_slots( $practitioner_id, $date, $duration = 30 ) {
		return Helper::get_available_time_slots( $practitioner_id, $date, $duration );
	}

	/**
	 * Mark appointment as completed.
	 *
	 * @param int $id Appointment ID.
	 * @return bool|\WP_Error
	 */
	public function complete_appointment( $id ) {
		$appointment = Appointment::get( $id );
		if ( ! $appointment ) {
			return new \WP_Error( 'not_found', __( 'Appointment not found', 'practicerx' ), array( 'status' => 404 ) );
		}

		$updated = Appointment::update( $id, array(
			'status' => Constants::APPOINTMENT_STATUS_COMPLETED,
		) );

		if ( false === $updated ) {
			return new \WP_Error( 'complete_failed', __( 'Could not complete appointment', 'practicerx' ), array( 'status' => 500 ) );
		}

		do_action( 'ppms_appointment_completed', $id );

		return true;
	}
}
		// TODO: Implement detailed working hours check from Practitioner settings

		// 2. Check for overlapping appointments
		$conflicts = Appointment::get_by_range( $start_time, $end_time, $practitioner_id );
		
		// Filter out appointments that don't actually overlap (get_by_range is broad)
		foreach ( $conflicts as $appointment ) {
			if ( $appointment->status === 'cancelled' ) {
				continue;
			}
			
			// Check overlap: (StartA < EndB) and (EndA > StartB)
			if ( $appointment->start_time < $end_time && $appointment->end_time > $start_time ) {
				return false;
			}
		}

		return true;
	}
}
