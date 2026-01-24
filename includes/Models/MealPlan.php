<?php
/**
 * Meal Plan Model
 *
 * Meal planning templates
 *
 * @package PracticeRx
 */

namespace PracticeRx\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MealPlan extends AbstractModel {
	
	protected static $table = 'ppms_meal_plans';
	
	/**
	 * Get meal plans by practitioner
	 *
	 * @param int $practitioner_id Practitioner ID
	 * @param bool $templates_only Only templates
	 * @return array
	 */
	public static function get_by_practitioner( $practitioner_id, $templates_only = false ) {
		global $wpdb;
		$table = self::get_table();
		
		$where = $wpdb->prepare( 'practitioner_id = %d', $practitioner_id );
		if ( $templates_only ) {
			$where .= ' AND is_template = 1';
		}
		
		return $wpdb->get_results(
			"SELECT * FROM {$table} WHERE {$where} ORDER BY title ASC"
		);
	}
	
	/**
	 * Get templates
	 *
	 * @return array
	 */
	public static function get_templates() {
		global $wpdb;
		$table = self::get_table();
		
		return $wpdb->get_results(
			"SELECT * FROM {$table} WHERE is_template = 1 ORDER BY title ASC"
		);
	}
	
	/**
	 * Get plans by type
	 *
	 * @param string $plan_type Plan type
	 * @return array
	 */
	public static function get_by_type( $plan_type ) {
		global $wpdb;
		$table = self::get_table();
		
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE plan_type = %s ORDER BY title ASC",
				$plan_type
			)
		);
	}
	
	/**
	 * Search meal plans
	 *
	 * @param string $term Search term
	 * @param int $practitioner_id Optional practitioner filter
	 * @return array
	 */
	public static function search( $term, $practitioner_id = 0 ) {
		global $wpdb;
		$table = self::get_table();
		
		$like = '%' . $wpdb->esc_like( $term ) . '%';
		
		$where = $wpdb->prepare(
			'(title LIKE %s OR description LIKE %s OR tags LIKE %s)',
			$like, $like, $like
		);
		
		if ( $practitioner_id > 0 ) {
			$where .= $wpdb->prepare( ' AND practitioner_id = %d', $practitioner_id );
		}
		
		return $wpdb->get_results(
			"SELECT * FROM {$table} WHERE {$where} ORDER BY title ASC"
		);
	}
}
