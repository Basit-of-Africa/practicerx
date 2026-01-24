<?php
/**
 * Client Program Model
 *
 * Programs enrolled by clients
 *
 * @package PracticeRx
 */

namespace PracticeRx\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ClientProgram extends AbstractModel {
	
	protected static $table = 'ppms_client_programs';
	
	/**
	 * Get programs for client
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
	 * Get programs by practitioner
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
	 * Update progress
	 *
	 * @param int $id Client program ID
	 * @param int $sessions_completed Sessions completed
	 * @return bool
	 */
	public static function update_progress( $id, $sessions_completed ) {
		$program = self::get( $id );
		if ( ! $program ) {
			return false;
		}
		
		// Get original program details
		$original_program = Program::get( $program->program_id );
		if ( ! $original_program ) {
			return false;
		}
		
		$sessions_remaining = max( 0, $original_program->sessions_included - $sessions_completed );
		$progress = $original_program->sessions_included > 0 
			? ( $sessions_completed / $original_program->sessions_included ) * 100 
			: 0;
		
		return self::update( $id, array(
			'sessions_completed' => $sessions_completed,
			'sessions_remaining' => $sessions_remaining,
			'progress_percentage' => round( $progress, 2 )
		) );
	}
	
	/**
	 * Get active programs for client
	 *
	 * @param int $client_id Client ID
	 * @return array
	 */
	public static function get_active_for_client( $client_id ) {
		return self::get_by_client( $client_id, 'active' );
	}
}
