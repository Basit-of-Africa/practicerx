<?php
/**
 * Form Submission Model
 *
 * Client form responses
 *
 * @package PracticeRx
 */

namespace PracticeRx\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FormSubmission extends AbstractModel {
	
	protected static $table = 'ppms_form_submissions';
	
	/**
	 * Get submissions by form
	 *
	 * @param int $form_id Form ID
	 * @return array
	 */
	public static function get_by_form( $form_id ) {
		global $wpdb;
		$table = self::get_table();
		
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE form_id = %d ORDER BY submitted_at DESC",
				$form_id
			)
		);
	}
	
	/**
	 * Get submissions by client
	 *
	 * @param int $client_id Client ID
	 * @return array
	 */
	public static function get_by_client( $client_id ) {
		global $wpdb;
		$table = self::get_table();
		
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE client_id = %d ORDER BY submitted_at DESC",
				$client_id
			)
		);
	}
	
	/**
	 * Get submissions by practitioner
	 *
	 * @param int $practitioner_id Practitioner ID
	 * @return array
	 */
	public static function get_by_practitioner( $practitioner_id ) {
		global $wpdb;
		$table = self::get_table();
		
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE practitioner_id = %d ORDER BY submitted_at DESC",
				$practitioner_id
			)
		);
	}
	
	/**
	 * Get recent submissions
	 *
	 * @param int $limit Limit
	 * @return array
	 */
	public static function get_recent( $limit = 10 ) {
		global $wpdb;
		$table = self::get_table();
		
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} ORDER BY submitted_at DESC LIMIT %d",
				$limit
			)
		);
	}
}
