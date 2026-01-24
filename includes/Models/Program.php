<?php
/**
 * Program Model
 *
 * Treatment packages and programs
 *
 * @package PracticeRx
 */

namespace PracticeRx\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Program extends AbstractModel {
	
	protected static $table = 'ppms_programs';
	
	/**
	 * Get programs by practitioner
	 *
	 * @param int $practitioner_id Practitioner ID
	 * @param bool $active_only Only active programs
	 * @return array
	 */
	public static function get_by_practitioner( $practitioner_id, $active_only = true ) {
		global $wpdb;
		$table = self::get_table();
		
		$where = $wpdb->prepare( 'practitioner_id = %d', $practitioner_id );
		if ( $active_only ) {
			$where .= ' AND is_active = 1';
		}
		
		return $wpdb->get_results(
			"SELECT * FROM {$table} WHERE {$where} ORDER BY order_number ASC, title ASC"
		);
	}
	
	/**
	 * Get active programs
	 *
	 * @return array
	 */
	public static function get_active() {
		global $wpdb;
		$table = self::get_table();
		
		return $wpdb->get_results(
			"SELECT * FROM {$table} WHERE is_active = 1 ORDER BY order_number ASC, title ASC"
		);
	}
	
	/**
	 * Get programs by type
	 *
	 * @param string $type Program type
	 * @return array
	 */
	public static function get_by_type( $type ) {
		global $wpdb;
		$table = self::get_table();
		
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE program_type = %s AND is_active = 1 ORDER BY title ASC",
				$type
			)
		);
	}
	
	/**
	 * Count enrollments for program
	 *
	 * @param int $program_id Program ID
	 * @return int
	 */
	public static function count_enrollments( $program_id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ppms_client_programs';
		
		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE program_id = %d",
				$program_id
			)
		);
	}
}
