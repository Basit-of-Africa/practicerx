<?php
/**
 * Document Model
 *
 * File library and document sharing
 *
 * @package PracticeRx
 */

namespace PracticeRx\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Document extends AbstractModel {
	
	protected static $table = 'ppms_documents';
	
	/**
	 * Get documents by practitioner
	 *
	 * @param int $practitioner_id Practitioner ID
	 * @param string $category Category filter
	 * @return array
	 */
	public static function get_by_practitioner( $practitioner_id, $category = '' ) {
		global $wpdb;
		$table = self::get_table();
		
		$where = $wpdb->prepare( 'practitioner_id = %d', $practitioner_id );
		if ( ! empty( $category ) ) {
			$where .= $wpdb->prepare( ' AND category = %s', $category );
		}
		
		return $wpdb->get_results(
			"SELECT * FROM {$table} WHERE {$where} ORDER BY created_at DESC"
		);
	}
	
	/**
	 * Get documents shared with client
	 *
	 * @param int $client_id Client ID
	 * @return array
	 */
	public static function get_shared_with_client( $client_id ) {
		global $wpdb;
		$table = self::get_table();
		
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE is_public = 1 OR FIND_IN_SET(%d, shared_with) > 0 ORDER BY created_at DESC",
				$client_id
			)
		);
	}
	
	/**
	 * Share document with clients
	 *
	 * @param int $document_id Document ID
	 * @param array $client_ids Client IDs
	 * @return bool
	 */
	public static function share_with_clients( $document_id, $client_ids ) {
		$shared_with = implode( ',', array_map( 'intval', $client_ids ) );
		
		return self::update( $document_id, array(
			'shared_with' => $shared_with
		) );
	}
	
	/**
	 * Get public documents
	 *
	 * @return array
	 */
	public static function get_public() {
		global $wpdb;
		$table = self::get_table();
		
		return $wpdb->get_results(
			"SELECT * FROM {$table} WHERE is_public = 1 ORDER BY created_at DESC"
		);
	}
	
	/**
	 * Get documents by category
	 *
	 * @param string $category Category
	 * @return array
	 */
	public static function get_by_category( $category ) {
		global $wpdb;
		$table = self::get_table();
		
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE category = %s ORDER BY created_at DESC",
				$category
			)
		);
	}
	
	/**
	 * Search documents
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
			'(title LIKE %s OR description LIKE %s OR file_name LIKE %s)',
			$like, $like, $like
		);
		
		if ( $practitioner_id > 0 ) {
			$where .= $wpdb->prepare( ' AND practitioner_id = %d', $practitioner_id );
		}
		
		return $wpdb->get_results(
			"SELECT * FROM {$table} WHERE {$where} ORDER BY created_at DESC"
		);
	}
}
