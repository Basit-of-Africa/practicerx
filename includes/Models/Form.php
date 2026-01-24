<?php
/**
 * Form Model
 *
 * Dynamic form builder
 *
 * @package PracticeRx
 */

namespace PracticeRx\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Form extends AbstractModel {
	
	protected static $table = 'ppms_forms';
	
	/**
	 * Get forms by practitioner
	 *
	 * @param int $practitioner_id Practitioner ID
	 * @param bool $active_only Only active forms
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
			"SELECT * FROM {$table} WHERE {$where} ORDER BY title ASC"
		);
	}
	
	/**
	 * Get public forms
	 *
	 * @return array
	 */
	public static function get_public() {
		global $wpdb;
		$table = self::get_table();
		
		return $wpdb->get_results(
			"SELECT * FROM {$table} WHERE is_public = 1 AND is_active = 1 ORDER BY title ASC"
		);
	}
	
	/**
	 * Get forms by type
	 *
	 * @param string $type Form type
	 * @return array
	 */
	public static function get_by_type( $type ) {
		global $wpdb;
		$table = self::get_table();
		
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE form_type = %s AND is_active = 1 ORDER BY title ASC",
				$type
			)
		);
	}
	
	/**
	 * Count submissions for form
	 *
	 * @param int $form_id Form ID
	 * @return int
	 */
	public static function count_submissions( $form_id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ppms_form_submissions';
		
		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE form_id = %d",
				$form_id
			)
		);
	}
}
