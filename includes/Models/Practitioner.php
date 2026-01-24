<?php
namespace PracticeRx\Models;

use PracticeRx\Core\Constants;

/**
 * Class Practitioner
 *
 * Model for Practitioner data.
 */
class Practitioner extends AbstractModel {

	/**
	 * Table name (without prefix).
	 *
	 * @var string
	 */
	protected static $table = 'ppms_practitioners';

	/**
	 * Get practitioner by User ID.
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
	 * Get practitioners by specialty.
	 *
	 * @param string $specialty Specialty.
	 * @return array
	 */
	public static function get_by_specialty( $specialty ) {
		global $wpdb;
		$table = self::get_table();

		return $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE specialty = %s ORDER BY created_at DESC", $specialty )
		);
	}

	/**
	 * Get active practitioners.
	 *
	 * @return array
	 */
	public static function get_active() {
		global $wpdb;
		$table = self::get_table();

		return $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE status = %s ORDER BY created_at DESC", Constants::PRACTITIONER_STATUS_ACTIVE )
		);
	}

	/**
	 * Search practitioners by name or specialty.
	 *
	 * @param string $search_term Search term.
	 * @return array
	 */
	public static function search( $search_term ) {
		global $wpdb;
		$table = self::get_table();

		$search_term = '%' . $wpdb->esc_like( $search_term ) . '%';

		$query = "
			SELECT p.*, u.display_name, u.user_email
			FROM {$table} p
			LEFT JOIN {$wpdb->users} u ON p.user_id = u.ID
			WHERE u.display_name LIKE %s
			OR p.specialty LIKE %s
			OR p.license_number LIKE %s
			ORDER BY u.display_name ASC
		";

		return $wpdb->get_results(
			$wpdb->prepare( $query, $search_term, $search_term, $search_term )
		);
	}

	/**
	 * Get practitioner with user data.
	 *
	 * @param int $id Practitioner ID.
	 * @return object|null
	 */
	public static function get_with_user( $id ) {
		global $wpdb;
		$table = self::get_table();

		$query = "
			SELECT p.*, u.display_name, u.user_email, u.user_login
			FROM {$table} p
			LEFT JOIN {$wpdb->users} u ON p.user_id = u.ID
			WHERE p.id = %d
		";

		return $wpdb->get_row( $wpdb->prepare( $query, $id ) );
	}

	/**
	 * Count practitioners by status.
	 *
	 * @param string $status Status.
	 * @return int
	 */
	public static function count_by_status( $status ) {
		global $wpdb;
		$table = self::get_table();

		return absint( $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE status = %s", $status )
		) );
	}
}
