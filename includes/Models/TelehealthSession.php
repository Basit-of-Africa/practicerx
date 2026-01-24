<?php
/**
 * Telehealth Session Model
 *
 * Video consultation sessions
 *
 * @package PracticeRx
 */

namespace PracticeRx\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TelehealthSession extends AbstractModel {
	
	protected static $table = 'ppms_telehealth_sessions';
	
	/**
	 * Get sessions by client
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
			"SELECT * FROM {$table} WHERE {$where} ORDER BY start_time DESC"
		);
	}
	
	/**
	 * Get sessions by practitioner
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
			"SELECT * FROM {$table} WHERE {$where} ORDER BY start_time DESC"
		);
	}
	
	/**
	 * Get upcoming sessions
	 *
	 * @param int $practitioner_id Optional practitioner filter
	 * @return array
	 */
	public static function get_upcoming( $practitioner_id = 0 ) {
		global $wpdb;
		$table = self::get_table();
		
		$where = "start_time > NOW() AND status = 'scheduled'";
		if ( $practitioner_id > 0 ) {
			$where .= $wpdb->prepare( ' AND practitioner_id = %d', $practitioner_id );
		}
		
		return $wpdb->get_results(
			"SELECT * FROM {$table} WHERE {$where} ORDER BY start_time ASC"
		);
	}
	
	/**
	 * Get session by appointment
	 *
	 * @param int $appointment_id Appointment ID
	 * @return object|null
	 */
	public static function get_by_appointment( $appointment_id ) {
		global $wpdb;
		$table = self::get_table();
		
		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE appointment_id = %d",
				$appointment_id
			)
		);
	}
}
