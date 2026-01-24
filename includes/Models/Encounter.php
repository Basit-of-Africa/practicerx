<?php
namespace PracticeRx\Models;

use PracticeRx\Core\Constants;

/**
 * Class Encounter
 *
 * Model for Clinical Encounters (Notes).
 */
class Encounter extends AbstractModel {

	/**
	 * Table name (without prefix).
	 *
	 * @var string
	 */
	protected static $table = 'ppms_encounters';

	/**
	 * Get encounters by Patient ID.
	 *
	 * @param int $patient_id Patient ID.
	 * @return array
	 */
	public static function get_by_patient( $patient_id ) {
		global $wpdb;
		$table = self::get_table();

		return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE patient_id = %d ORDER BY created_at DESC", $patient_id ) );
	}

	/**
	 * Get encounters by practitioner.
	 *
	 * @param int $practitioner_id Practitioner ID.
	 * @return array
	 */
	public static function get_by_practitioner( $practitioner_id ) {
		global $wpdb;
		$table = self::get_table();

		return $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE practitioner_id = %d ORDER BY encounter_date DESC", $practitioner_id )
		);
	}

	/**
	 * Get encounters by date range.
	 *
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @param int    $patient_id Optional patient ID.
	 * @return array
	 */
	public static function get_by_date_range( $start_date, $end_date, $patient_id = null ) {
		global $wpdb;
		$table = self::get_table();

		if ( $patient_id ) {
			return $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM {$table} WHERE encounter_date BETWEEN %s AND %s AND patient_id = %d ORDER BY encounter_date DESC",
					$start_date,
					$end_date,
					$patient_id
				)
			);
		}

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE encounter_date BETWEEN %s AND %s ORDER BY encounter_date DESC",
				$start_date,
				$end_date
			)
		);
	}

	/**
	 * Get encounters by status.
	 *
	 * @param string $status Status.
	 * @return array
	 */
	public static function get_by_status( $status ) {
		global $wpdb;
		$table = self::get_table();

		return $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE status = %s ORDER BY encounter_date DESC", $status )
		);
	}

	/**
	 * Get recent encounters.
	 *
	 * @param int $limit Limit.
	 * @param int $patient_id Optional patient ID.
	 * @return array
	 */
	public static function get_recent( $limit = 10, $patient_id = null ) {
		global $wpdb;
		$table = self::get_table();

		if ( $patient_id ) {
			return $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM {$table} WHERE patient_id = %d ORDER BY encounter_date DESC LIMIT %d",
					$patient_id,
					$limit
				)
			);
		}

		return $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM {$table} ORDER BY encounter_date DESC LIMIT %d", $limit )
		);
	}

	/**
	 * Search encounters by content.
	 *
	 * @param string $search_term Search term.
	 * @param int    $patient_id Optional patient ID.
	 * @return array
	 */
	public static function search( $search_term, $patient_id = null ) {
		global $wpdb;
		$table = self::get_table();

		$search_term = '%' . $wpdb->esc_like( $search_term ) . '%';

		if ( $patient_id ) {
			return $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM {$table} WHERE patient_id = %d AND (content LIKE %s OR notes LIKE %s) ORDER BY encounter_date DESC",
					$patient_id,
					$search_term,
					$search_term
				)
			);
		}

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE content LIKE %s OR notes LIKE %s ORDER BY encounter_date DESC",
				$search_term,
				$search_term
			)
		);
	}

	/**
	 * Count encounters by patient.
	 *
	 * @param int $patient_id Patient ID.
	 * @return int
	 */
	public static function count_by_patient( $patient_id ) {
		global $wpdb;
		$table = self::get_table();

		return absint( $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE patient_id = %d", $patient_id )
		) );
	}

	/**
	 * Get encounter by appointment ID.
	 *
	 * @param int $appointment_id Appointment ID.
	 * @return object|null
	 */
	public static function get_by_appointment( $appointment_id ) {
		global $wpdb;
		$table = self::get_table();

		return $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE appointment_id = %d", $appointment_id )
		);
	}
}

	/**
	 * Get encounters by Appointment ID.
	 *
	 * @param int $appointment_id Appointment ID.
	 * @return array
	 */
	public static function get_by_appointment( $appointment_id ) {
		global $wpdb;
		$table = self::get_table();

		return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE appointment_id = %d ORDER BY created_at DESC", $appointment_id ) );
	}
}
