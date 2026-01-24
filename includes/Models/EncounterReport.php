<?php
namespace PracticeRx\Models;

/**
 * Class EncounterReport
 *
 * Model for detailed encounter reports filled by practitioners.
 */
class EncounterReport extends AbstractModel {

	/**
	 * Table name (without prefix).
	 *
	 * @var string
	 */
	protected static $table = 'ppms_encounter_reports';

	/**
	 * Get report by encounter ID.
	 *
	 * @param int $encounter_id Encounter ID.
	 * @return object|null
	 */
	public static function get_by_encounter( $encounter_id ) {
		global $wpdb;
		$table = self::get_table();

		return $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE encounter_id = %d", $encounter_id )
		);
	}

	/**
	 * Get reports by client.
	 *
	 * @param int $client_id Client ID.
	 * @return array
	 */
	public static function get_by_client( $client_id ) {
		global $wpdb;
		$table = self::get_table();

		return $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE client_id = %d ORDER BY report_date DESC", $client_id )
		);
	}

	/**
	 * Get reports by practitioner.
	 *
	 * @param int $practitioner_id Practitioner ID.
	 * @return array
	 */
	public static function get_by_practitioner( $practitioner_id ) {
		global $wpdb;
		$table = self::get_table();

		return $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE practitioner_id = %d ORDER BY report_date DESC", $practitioner_id )
		);
	}

	/**
	 * Get reports by status.
	 *
	 * @param string $status Status (draft, signed).
	 * @return array
	 */
	public static function get_by_status( $status ) {
		global $wpdb;
		$table = self::get_table();

		return $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE status = %s ORDER BY report_date DESC", $status )
		);
	}

	/**
	 * Get unsigned reports for practitioner.
	 *
	 * @param int $practitioner_id Practitioner ID.
	 * @return array
	 */
	public static function get_unsigned( $practitioner_id ) {
		global $wpdb;
		$table = self::get_table();

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE practitioner_id = %d AND status = 'draft' ORDER BY report_date DESC",
				$practitioner_id
			)
		);
	}

	/**
	 * Sign a report.
	 *
	 * @param int $id Report ID.
	 * @param int $practitioner_id Practitioner ID.
	 * @return bool
	 */
	public static function sign_report( $id, $practitioner_id ) {
		return self::update( $id, array(
			'status'    => 'signed',
			'signed_by' => $practitioner_id,
			'signed_at' => current_time( 'mysql' ),
		) );
	}

	/**
	 * Get reports by date range.
	 *
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @param int    $practitioner_id Optional practitioner ID.
	 * @return array
	 */
	public static function get_by_date_range( $start_date, $end_date, $practitioner_id = null ) {
		global $wpdb;
		$table = self::get_table();

		if ( $practitioner_id ) {
			return $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM {$table} WHERE report_date BETWEEN %s AND %s AND practitioner_id = %d ORDER BY report_date DESC",
					$start_date,
					$end_date,
					$practitioner_id
				)
			);
		}

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE report_date BETWEEN %s AND %s ORDER BY report_date DESC",
				$start_date,
				$end_date
			)
		);
	}
}
