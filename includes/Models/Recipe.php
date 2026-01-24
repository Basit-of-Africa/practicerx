<?php
/**
 * Recipe Model
 *
 * Recipe library for meal plans
 *
 * @package PracticeRx
 */

namespace PracticeRx\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Recipe extends AbstractModel {
	
	protected static $table = 'ppms_recipes';
	
	/**
	 * Get recipes by practitioner
	 *
	 * @param int $practitioner_id Practitioner ID
	 * @return array
	 */
	public static function get_by_practitioner( $practitioner_id ) {
		global $wpdb;
		$table = self::get_table();
		
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE practitioner_id = %d ORDER BY title ASC",
				$practitioner_id
			)
		);
	}
	
	/**
	 * Get public recipes
	 *
	 * @return array
	 */
	public static function get_public() {
		global $wpdb;
		$table = self::get_table();
		
		return $wpdb->get_results(
			"SELECT * FROM {$table} WHERE is_public = 1 ORDER BY title ASC"
		);
	}
	
	/**
	 * Get recipes by meal type
	 *
	 * @param string $meal_type Meal type (breakfast, lunch, dinner, snack)
	 * @return array
	 */
	public static function get_by_meal_type( $meal_type ) {
		global $wpdb;
		$table = self::get_table();
		
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE meal_type = %s ORDER BY title ASC",
				$meal_type
			)
		);
	}
	
	/**
	 * Search recipes
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
			$where .= $wpdb->prepare( ' AND (practitioner_id = %d OR is_public = 1)', $practitioner_id );
		} else {
			$where .= ' AND is_public = 1';
		}
		
		return $wpdb->get_results(
			"SELECT * FROM {$table} WHERE {$where} ORDER BY title ASC"
		);
	}
	
	/**
	 * Get recipes by tags
	 *
	 * @param array $tags Tags to filter by
	 * @return array
	 */
	public static function get_by_tags( $tags ) {
		global $wpdb;
		$table = self::get_table();
		
		$tag_conditions = array();
		foreach ( $tags as $tag ) {
			$tag_conditions[] = $wpdb->prepare( 'tags LIKE %s', '%' . $wpdb->esc_like( $tag ) . '%' );
		}
		
		$where = '(' . implode( ' OR ', $tag_conditions ) . ')';
		
		return $wpdb->get_results(
			"SELECT * FROM {$table} WHERE {$where} ORDER BY title ASC"
		);
	}
}
