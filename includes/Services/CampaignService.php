<?php
/**
 * Email Campaign Service
 *
 * Drip email campaign automation
 *
 * @package PracticeRx
 */

namespace PracticeRx\Services;

use PracticeRx\Models\EmailCampaign;
use PracticeRx\Models\CampaignSubscriber;
use PracticeRx\Models\Client;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CampaignService {
	
	/**
	 * Subscribe client to campaign
	 *
	 * @param int $campaign_id Campaign ID
	 * @param int $client_id Client ID
	 * @return int|false Subscription ID or false
	 */
	public static function subscribe( $campaign_id, $client_id ) {
		// Check if already subscribed
		$existing = CampaignSubscriber::get_subscription( $campaign_id, $client_id );
		if ( $existing ) {
			return $existing->id;
		}
		
		$data = array(
			'campaign_id' => $campaign_id,
			'client_id'   => $client_id,
			'status'      => 'active',
			'current_step' => 0,
		);
		
		$subscription_id = CampaignSubscriber::create( $data );
		
		// Send first email immediately
		if ( $subscription_id ) {
			self::send_campaign_email( $subscription_id, 0 );
		}
		
		return $subscription_id;
	}
	
	/**
	 * Unsubscribe client from campaign
	 *
	 * @param int $campaign_id Campaign ID
	 * @param int $client_id Client ID
	 * @return bool
	 */
	public static function unsubscribe( $campaign_id, $client_id ) {
		$subscription = CampaignSubscriber::get_subscription( $campaign_id, $client_id );
		if ( ! $subscription ) {
			return false;
		}
		
		return CampaignSubscriber::update( $subscription->id, array(
			'status' => 'unsubscribed',
		) );
	}
	
	/**
	 * Process campaign emails (called by cron)
	 *
	 * @return int Number of emails sent
	 */
	public static function process_campaigns() {
		$sent_count = 0;
		
		// Get all active subscriptions
		$subscriptions = CampaignSubscriber::get_active();
		
		foreach ( $subscriptions as $subscription ) {
			$campaign = EmailCampaign::get( $subscription->campaign_id );
			if ( ! $campaign || ! $campaign->is_active ) {
				continue;
			}
			
			$emails = json_decode( $campaign->emails, true );
			if ( empty( $emails ) ) {
				continue;
			}
			
			$current_step = $subscription->current_step;
			$next_step = $current_step + 1;
			
			// Check if there's a next email
			if ( ! isset( $emails[ $next_step ] ) ) {
				// Campaign completed
				CampaignSubscriber::update( $subscription->id, array(
					'status'       => 'completed',
					'completed_at' => current_time( 'mysql' ),
				) );
				continue;
			}
			
			$email_config = $emails[ $next_step ];
			$delay_days = $email_config['delay_days'] ?? 0;
			
			// Check if enough time has passed
			$started_at = strtotime( $subscription->started_at );
			$days_elapsed = floor( ( time() - $started_at ) / DAY_IN_SECONDS );
			
			if ( $days_elapsed >= $delay_days ) {
				// Send email
				if ( self::send_campaign_email( $subscription->id, $next_step ) ) {
					$sent_count++;
				}
			}
		}
		
		return $sent_count;
	}
	
	/**
	 * Send campaign email
	 *
	 * @param int $subscription_id Subscription ID
	 * @param int $step Email step
	 * @return bool
	 */
	private static function send_campaign_email( $subscription_id, $step ) {
		$subscription = CampaignSubscriber::get( $subscription_id );
		if ( ! $subscription ) {
			return false;
		}
		
		$campaign = EmailCampaign::get( $subscription->campaign_id );
		if ( ! $campaign ) {
			return false;
		}
		
		$client = Client::get( $subscription->client_id );
		if ( ! $client ) {
			return false;
		}
		
		$emails = json_decode( $campaign->emails, true );
		if ( ! isset( $emails[ $step ] ) ) {
			return false;
		}
		
		$email_config = $emails[ $step ];
		
		// Get client user
		$user = get_user_by( 'id', $client->user_id );
		if ( ! $user ) {
			return false;
		}
		
		// Replace merge tags
		$subject = self::replace_merge_tags( $email_config['subject'], $client, $user );
		$body = self::replace_merge_tags( $email_config['body'], $client, $user );
		
		// Send email
		$sent = wp_mail( $user->user_email, $subject, $body, array(
			'Content-Type: text/html; charset=UTF-8',
		) );
		
		if ( $sent ) {
			// Update subscription step
			CampaignSubscriber::update_step( $subscription_id, $step );
			
			// Log email
			EmailService::log_email( $user->user_email, $subject, 'campaign', 'sent' );
		}
		
		return $sent;
	}
	
	/**
	 * Replace merge tags in content
	 *
	 * @param string $content Content with merge tags
	 * @param object $client Client object
	 * @param object $user User object
	 * @return string
	 */
	private static function replace_merge_tags( $content, $client, $user ) {
		$replacements = array(
			'{{first_name}}' => $client->first_name,
			'{{last_name}}'  => $client->last_name,
			'{{email}}'      => $user->user_email,
			'{{phone}}'      => $client->phone,
		);
		
		return str_replace( array_keys( $replacements ), array_values( $replacements ), $content );
	}
	
	/**
	 * Trigger campaign by event
	 *
	 * @param string $event Event name
	 * @param int $client_id Client ID
	 * @return int Number of campaigns triggered
	 */
	public static function trigger_by_event( $event, $client_id ) {
		$campaigns = EmailCampaign::get_by_trigger( 'event', $event );
		$triggered = 0;
		
		foreach ( $campaigns as $campaign ) {
			if ( self::subscribe( $campaign->id, $client_id ) ) {
				$triggered++;
			}
		}
		
		return $triggered;
	}
}
