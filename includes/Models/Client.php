<?php
namespace PracticeRx\Models;

use PracticeRx\Core\Constants;

/**
 * Class Client
 *
 * Model for Client data (renamed from Patient for all health professionals).
 */
class Client extends AbstractModel {

	/**
	 * Table name (without prefix).
	 *
	 * @var string
	 */
	protected static $table = 'ppms_clients';

	/**
	 * Get client by User ID.
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
	 * Get client by practitioner.
	 *
	 * @param int $practitioner_id Practitioner ID.
	 * @return array
	 */
	public static function get_by_practitioner( $practitioner_id ) {
		global $wpdb;
		$table = self::get_table();

		return $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE practitioner_id = %d ORDER BY last_name ASC", $practitioner_id )
		);
	}

	/**
	 * Search clients by name, email, or phone.
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
	 * Get clients by status.
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
	 * Get active clients.
	 *
	 * @return array
	 */
	public static function get_active() {
		return self::get_by_status( 'active' );
	}

	/**
	 * Get total active clients count.
	 *
	 * @return int
	 */
	public static function count_active() {
		global $wpdb;
		$table = self::get_table();

		return absint( $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE status = %s", 'active' )
		) );
	}

	/**
	 * Get clients registered in date range.
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

	/**
	 * Get client with full details including user data.
	 *
	 * @param int $id Client ID.
	 * @return object|null
	 */
	public static function get_with_user( $id ) {
		global $wpdb;
		$table = self::get_table();

		$query = "
			SELECT c.*, u.display_name, u.user_email, u.user_login
			FROM {$table} c
			LEFT JOIN {$wpdb->users} u ON c.user_id = u.ID
			WHERE c.id = %d
		";

		return $wpdb->get_row( $wpdb->prepare( $query, $id ) );
	}

	/**
	 * Count total appointments for client.
	 *
	 * @param int $client_id Client ID.
	 * @return int
	 */
	public static function count_appointments( $client_id ) {
		global $wpdb;
		$appointments_table = $wpdb->prefix . 'ppms_appointments';

		return absint( $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$appointments_table} WHERE patient_id = %d", $client_id )
		) );
	}

	/**
	 * Get new clients count for date range.
	 *
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return int
	 */
	public static function count_new_clients( $start_date, $end_date ) {
		global $wpdb;
		$table = self::get_table();

		return absint( $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE created_at BETWEEN %s AND %s",
				$start_date,
				$end_date
			)
		) );
	}
}
