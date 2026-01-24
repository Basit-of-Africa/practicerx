<?php
/**
 * Campaign Subscriber Model
 *
 * Client enrollment in email campaigns
 *
 * @package PracticeRx
 */

namespace PracticeRx\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CampaignSubscriber extends AbstractModel {
	
	protected static $table = 'ppms_campaign_subscribers';
	
	/**
	 * Get subscriptions by campaign
	 *
	 * @param int $campaign_id Campaign ID
	 * @param string $status Status filter
	 * @return array
	 */
	public static function get_by_campaign( $campaign_id, $status = '' ) {
		global $wpdb;
		$table = self::get_table();
		
		$where = $wpdb->prepare( 'campaign_id = %d', $campaign_id );
		if ( ! empty( $status ) ) {
			$where .= $wpdb->prepare( ' AND status = %s', $status );
		}
		
		return $wpdb->get_results(
			"SELECT * FROM {$table} WHERE {$where} ORDER BY started_at DESC"
		);
	}
	
	/**
	 * Get subscriptions by client
	 *
	 * @param int $client_id Client ID
	 * @return array
	 */
	public static function get_by_client( $client_id ) {
		global $wpdb;
		$table = self::get_table();
		
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE client_id = %d ORDER BY started_at DESC",
				$client_id
			)
		);
	}
	
	/**
	 * Get active subscriptions
	 *
	 * @return array
	 */
	public static function get_active() {
		global $wpdb;
		$table = self::get_table();
		
		return $wpdb->get_results(
			"SELECT * FROM {$table} WHERE status = 'active' ORDER BY started_at ASC"
		);
	}
	
	/**
	 * Get subscription
	 *
	 * @param int $campaign_id Campaign ID
	 * @param int $client_id Client ID
	 * @return object|null
	 */
	public static function get_subscription( $campaign_id, $client_id ) {
		global $wpdb;
		$table = self::get_table();
		
		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE campaign_id = %d AND client_id = %d",
				$campaign_id, $client_id
			)
		);
	}
	
	/**
	 * Update step progress
	 *
	 * @param int $id Subscription ID
	 * @param int $step Current step
	 * @return bool
	 */
	public static function update_step( $id, $step ) {
		return self::update( $id, array( 'current_step' => $step ) );
	}
}
