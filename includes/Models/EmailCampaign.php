<?php
/**
 * Email Campaign Model
 *
 * Drip email campaigns
 *
 * @package PracticeRx
 */

namespace PracticeRx\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EmailCampaign extends AbstractModel {
	
	protected static $table = 'ppms_email_campaigns';
	
	/**
	 * Get campaigns by practitioner
	 *
	 * @param int $practitioner_id Practitioner ID
	 * @param bool $active_only Only active campaigns
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
			"SELECT * FROM {$table} WHERE {$where} ORDER BY name ASC"
		);
	}
	
	/**
	 * Get active campaigns
	 *
	 * @return array
	 */
	public static function get_active() {
		global $wpdb;
		$table = self::get_table();
		
		return $wpdb->get_results(
			"SELECT * FROM {$table} WHERE is_active = 1 ORDER BY name ASC"
		);
	}
	
	/**
	 * Get campaigns by trigger
	 *
	 * @param string $trigger_type Trigger type
	 * @param string $trigger_event Trigger event
	 * @return array
	 */
	public static function get_by_trigger( $trigger_type, $trigger_event = '' ) {
		global $wpdb;
		$table = self::get_table();
		
		$where = $wpdb->prepare( 'trigger_type = %s AND is_active = 1', $trigger_type );
		if ( ! empty( $trigger_event ) ) {
			$where .= $wpdb->prepare( ' AND trigger_event = %s', $trigger_event );
		}
		
		return $wpdb->get_results(
			"SELECT * FROM {$table} WHERE {$where}"
		);
	}
	
	/**
	 * Count subscribers
	 *
	 * @param int $campaign_id Campaign ID
	 * @return int
	 */
	public static function count_subscribers( $campaign_id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ppms_campaign_subscribers';
		
		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE campaign_id = %d",
				$campaign_id
			)
		);
	}
}
