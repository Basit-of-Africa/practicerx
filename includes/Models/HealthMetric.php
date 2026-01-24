<?php
/**
 * Health Metric Model
 *
 * Track vitals, labs, and health measurements
 *
 * @package PracticeRx
 */

namespace PracticeRx\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class HealthMetric extends AbstractModel {
	
	protected static $table = 'ppms_health_metrics';
	
	/**
	 * Get metrics by client
	 *
	 * @param int $client_id Client ID
	 * @param string $metric_type Type filter
	 * @return array
	 */
	public static function get_by_client( $client_id, $metric_type = '' ) {
		global $wpdb;
		$table = self::get_table();
		
		$where = $wpdb->prepare( 'client_id = %d', $client_id );
		if ( ! empty( $metric_type ) ) {
			$where .= $wpdb->prepare( ' AND metric_type = %s', $metric_type );
		}
		
		return $wpdb->get_results(
			"SELECT * FROM {$table} WHERE {$where} ORDER BY recorded_date DESC"
		);
	}
	
	/**
	 * Get metric history (time series)
	 *
	 * @param int $client_id Client ID
	 * @param string $metric_name Metric name
	 * @param string $start_date Start date
	 * @param string $end_date End date
	 * @return array
	 */
	public static function get_metric_history( $client_id, $metric_name, $start_date = '', $end_date = '' ) {
		global $wpdb;
		$table = self::get_table();
		
		$where = $wpdb->prepare( 'client_id = %d AND metric_name = %s', $client_id, $metric_name );
		
		if ( ! empty( $start_date ) ) {
			$where .= $wpdb->prepare( ' AND recorded_date >= %s', $start_date );
		}
		if ( ! empty( $end_date ) ) {
			$where .= $wpdb->prepare( ' AND recorded_date <= %s', $end_date );
		}
		
		return $wpdb->get_results(
			"SELECT * FROM {$table} WHERE {$where} ORDER BY recorded_date ASC"
		);
	}
	
	/**
	 * Get latest metric value
	 *
	 * @param int $client_id Client ID
	 * @param string $metric_name Metric name
	 * @return object|null
	 */
	public static function get_latest_metric( $client_id, $metric_name ) {
		global $wpdb;
		$table = self::get_table();
		
		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE client_id = %d AND metric_name = %s ORDER BY recorded_date DESC LIMIT 1",
				$client_id, $metric_name
			)
		);
	}
	
	/**
	 * Get abnormal metrics
	 *
	 * @param int $client_id Client ID
	 * @return array
	 */
	public static function get_abnormal( $client_id ) {
		global $wpdb;
		$table = self::get_table();
		
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE client_id = %d AND is_abnormal = 1 ORDER BY recorded_date DESC",
				$client_id
			)
		);
	}
	
	/**
	 * Get metrics by type
	 *
	 * @param int $client_id Client ID
	 * @param string $metric_type Metric type (vital, lab, measurement)
	 * @param int $limit Limit
	 * @return array
	 */
	public static function get_by_type( $client_id, $metric_type, $limit = 50 ) {
		global $wpdb;
		$table = self::get_table();
		
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE client_id = %d AND metric_type = %s ORDER BY recorded_date DESC LIMIT %d",
				$client_id, $metric_type, $limit
			)
		);
	}
}
