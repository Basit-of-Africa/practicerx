<?php
namespace PracticeRx\Models;

use PracticeRx\Core\Constants;

/**
 * Class Appointment
 *
 * Model for Appointment data.
 */
class Appointment extends AbstractModel {

	/**
	 * Table name (without prefix).
	 *
	 * @var string
	 */
	protected static $table = 'ppms_appointments';

	/**
	 * Get appointments by range.
	 *
	 * @param string $start_date Start date (Y-m-d).
	 * @param string $end_date   End date (Y-m-d).
	 * @param int    $practitioner_id Optional practitioner ID.
	 * @return array
	 */
	public static function get_by_range( $start_date, $end_date, $practitioner_id = 0 ) {
		global $wpdb;
		$table = self::get_table();

		$sql = "SELECT * FROM {$table} WHERE start_time >= %s AND end_time <= %s";
		$args = array( $start_date, $end_date );

		if ( $practitioner_id ) {
			$sql .= " AND practitioner_id = %d";
			$args[] = $practitioner_id;
		}

		$sql .= " ORDER BY start_time ASC";

		return $wpdb->get_results( $wpdb->prepare( $sql, ...$args ) );
	}

	/**
	 * Get appointments for a practitioner.
	 *
	 * @param int $practitioner_id Practitioner ID.
	 * @return array
	 */
	public static function get_by_practitioner( $practitioner_id ) {
		global $wpdb;
		$table = self::get_table();

		return $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE practitioner_id = %d ORDER BY start_time DESC", $practitioner_id )
		);
	}

	/**
	 * Get appointments for a patient.
	 *
	 * @param int $patient_id Patient ID.
	 * @return array
	 */
	public static function get_by_patient( $patient_id ) {
		global $wpdb;
		$table = self::get_table();

		return $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE patient_id = %d ORDER BY start_time DESC", $patient_id )
		);
	}

	/**
	 * Get appointments by status.
	 *
	 * @param string $status Status.
	 * @return array
	 */
	public static function get_by_status( $status ) {
		global $wpdb;
		$table = self::get_table();

		return $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE status = %s ORDER BY start_time ASC", $status )
		);
	}

	/**
	 * Get upcoming appointments for practitioner.
	 *
	 * @param int    $practitioner_id Practitioner ID.
	 * @param string $from_date From date (default: now).
	 * @return array
	 */
	public static function get_upcoming( $practitioner_id, $from_date = null ) {
		global $wpdb;
		$table = self::get_table();

		if ( ! $from_date ) {
			$from_date = current_time( 'mysql' );
		}

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} 
				WHERE practitioner_id = %d 
				AND start_time >= %s 
				AND status IN (%s, %s)
				ORDER BY start_time ASC",
				$practitioner_id,
				$from_date,
				Constants::APPOINTMENT_STATUS_SCHEDULED,
				Constants::APPOINTMENT_STATUS_CONFIRMED
			)
		);
	}

	/**
	 * Check if time slot is available.
	 *
	 * @param int    $practitioner_id Practitioner ID.
	 * @param string $start_time Start time.
	 * @param string $end_time End time.
	 * @param int    $exclude_id Exclude appointment ID (for updates).
	 * @return bool
	 */
	public static function is_time_slot_available( $practitioner_id, $start_time, $end_time, $exclude_id = null ) {
		global $wpdb;
		$table = self::get_table();

		$query = "SELECT COUNT(*) FROM {$table} 
				WHERE practitioner_id = %d 
				AND ((start_time < %s AND end_time > %s) OR (start_time >= %s AND start_time < %s))
				AND status NOT IN (%s, %s)";
		$values = array(
			$practitioner_id,
			$end_time,
			$start_time,
			$start_time,
			$end_time,
			Constants::APPOINTMENT_STATUS_CANCELLED,
			Constants::APPOINTMENT_STATUS_NO_SHOW
		);

		if ( $exclude_id ) {
			$query .= " AND id != %d";
			$values[] = $exclude_id;
		}

		$count = $wpdb->get_var( $wpdb->prepare( $query, $values ) );

		return 0 === absint( $count );
	}

	/**
	 * Count appointments by status.
	 *
	 * @param string $status Status.
	 * @param int    $practitioner_id Optional practitioner ID.
	 * @return int
	 */
	public static function count_by_status( $status, $practitioner_id = null ) {
		global $wpdb;
		$table = self::get_table();

		if ( $practitioner_id ) {
			return absint( $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$table} WHERE status = %s AND practitioner_id = %d",
					$status,
					$practitioner_id
				)
			) );
		}

		return absint( $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE status = %s", $status )
		) );
	}

	/**
	 * Get today's appointments for practitioner.
	 *
	 * @param int $practitioner_id Practitioner ID.
	 * @return array
	 */
	public static function get_today( $practitioner_id ) {
		$today = current_time( 'Y-m-d' );
		return self::get_by_range( $today . ' 00:00:00', $today . ' 23:59:59', $practitioner_id );
	}
}
