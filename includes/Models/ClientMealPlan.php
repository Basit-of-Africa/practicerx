<?php
/**
 * Client Meal Plan Model
 *
 * Meal plans assigned to clients
 *
 * @package PracticeRx
 */

namespace PracticeRx\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ClientMealPlan extends AbstractModel {
	
	protected static $table = 'ppms_client_meal_plans';
	
	/**
	 * Get meal plans by client
	 *
	 * @param int $client_id Client ID
	 * @param string $status Status filter
	 * @return array
	 */
	public static function get_by_client( $client_id, $status = '' ) {
		global $wpdb;
		$table = self::get_table();
		
		$where = $wpdb->prepare( 'client_id = %d', $client_id );
		if ( ! empty( $status ) ) {
			$where .= $wpdb->prepare( ' AND status = %s', $status );
		}
		
		return $wpdb->get_results(
			"SELECT * FROM {$table} WHERE {$where} ORDER BY start_date DESC"
		);
	}
	
	/**
	 * Get meal plans by practitioner
	 *
	 * @param int $practitioner_id Practitioner ID
	 * @param string $status Status filter
	 * @return array
	 */
	public static function get_by_practitioner( $practitioner_id, $status = '' ) {
		global $wpdb;
		$table = self::get_table();
		
		$where = $wpdb->prepare( 'practitioner_id = %d', $practitioner_id );
		if ( ! empty( $status ) ) {
			$where .= $wpdb->prepare( ' AND status = %s', $status );
		}
		
		return $wpdb->get_results(
			"SELECT * FROM {$table} WHERE {$where} ORDER BY start_date DESC"
		);
	}
	
	/**
	 * Get active plan for client
	 *
	 * @param int $client_id Client ID
	 * @return object|null
	 */
	public static function get_active_for_client( $client_id ) {
		global $wpdb;
		$table = self::get_table();
		
		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE client_id = %d AND status = 'active' ORDER BY start_date DESC LIMIT 1",
				$client_id
			)
		);
	}
}
