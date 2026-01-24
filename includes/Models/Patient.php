<?php
namespace PracticeRx\Models;

use PracticeRx\Core\Constants;

/**
 * Class Patient
 *
 * Model for Patient data.
 */
class Patient extends AbstractModel {

	/**
	 * Table name (without prefix).
	 *
	 * @var string
	 */
	protected static $table = 'ppms_patients';

	/**
	 * Get patient by User ID.
	 *
	 * @param int $user_id WP User ID.
	 * @return object|null
	 */
	public static function get_by_user_id( $user_id ) {
		global $wpdb;
		$table = self::get_table();

		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE user_id = %d", $user_id ) );
	}

	/**
	 * Search patients by name, email, or phone.
	 *
	 * @param string $search_term Search term.
	 * @param array  $args Additional arguments.
	 * @return array
	 */
	public static function search( $search_term, $args = array() ) {
		global $wpdb;
		$table = self::get_table();

		$limit  = isset( $args['limit'] ) ? absint( $args['limit'] ) : 20;
		$offset = isset( $args['offset'] ) ? absint( $args['offset'] ) : 0;

		$search_term = '%' . $wpdb->esc_like( $search_term ) . '%';

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} 
				WHERE first_name LIKE %s 
				OR last_name LIKE %s 
				OR email LIKE %s 
				OR phone LIKE %s 
				ORDER BY first_name ASC 
				LIMIT %d OFFSET %d",
				$search_term,
				$search_term,
				$search_term,
				$search_term,
				$limit,
				$offset
			)
		);
	}

	/**
	 * Get patients by status.
	 *
	 * @param string $status Status.
	 * @return array
	 */
	public static function get_by_status( $status ) {
		global $wpdb;
		$table = self::get_table();

		return $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE status = %s ORDER BY created_at DESC", $status )
		);
	}

	/**
	 * Get active patients.
	 *
	 * @return array
	 */
	public static function get_active() {
		return self::get_by_status( Constants::PATIENT_STATUS_ACTIVE );
	}

	/**
	 * Get total active patients count.
	 *
	 * @return int
	 */
	public static function count_active() {
		global $wpdb;
		$table = self::get_table();

		return absint( $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE status = %s", Constants::PATIENT_STATUS_ACTIVE )
		) );
	}

	/**
	 * Get patients registered in date range.
	 *
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return array
	 */
	public static function get_by_date_range( $start_date, $end_date ) {
		global $wpdb;
		$table = self::get_table();

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE created_at BETWEEN %s AND %s ORDER BY created_at DESC",
				$start_date,
				$end_date
			)
		);
	}
}
