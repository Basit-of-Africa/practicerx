<?php
/**
 * Email Campaigns API Controller
 *
 * REST API endpoints for drip campaigns
 *
 * @package PracticeRx
 */

namespace PracticeRx\Api;

use PracticeRx\Models\EmailCampaign;
use PracticeRx\Models\CampaignSubscriber;
use PracticeRx\Services\CampaignService;
use PracticeRx\Core\Helper;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CampaignsController extends ApiController {
	
	protected $resource_name = 'campaigns';
	
	/**
	 * Register routes
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->resource_name, array(
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_campaigns' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			),
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'create_campaign' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			),
		) );
		
		register_rest_route( $this->namespace, '/' . $this->resource_name . '/(?P<id>[\d]+)', array(
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_campaign' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			),
			array(
				'methods'             => 'PUT',
				'callback'            => array( $this, 'update_campaign' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			),
			array(
				'methods'             => 'DELETE',
				'callback'            => array( $this, 'delete_campaign' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			),
		) );
		
		register_rest_route( $this->namespace, '/' . $this->resource_name . '/(?P<id>[\d]+)/subscribe', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'subscribe_client' ),
			'permission_callback' => array( $this, 'check_permissions' ),
		) );
		
		register_rest_route( $this->namespace, '/' . $this->resource_name . '/(?P<id>[\d]+)/unsubscribe', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'unsubscribe_client' ),
			'permission_callback' => array( $this, 'check_permissions' ),
		) );
		
		register_rest_route( $this->namespace, '/' . $this->resource_name . '/(?P<id>[\d]+)/subscribers', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_subscribers' ),
			'permission_callback' => array( $this, 'check_permissions' ),
		) );
	}
	
	/**
	 * Get campaigns
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_campaigns( $request ) {
		$practitioner_id = $request->get_param( 'practitioner_id' );
		$active_only = $request->get_param( 'active_only' ) !== 'false';
		
		if ( $practitioner_id ) {
			$campaigns = EmailCampaign::get_by_practitioner( $practitioner_id, $active_only );
		} elseif ( $active_only ) {
			$campaigns = EmailCampaign::get_active();
		} else {
			$campaigns = EmailCampaign::get_all();
		}
		
		// Add subscriber counts
		foreach ( $campaigns as &$campaign ) {
			$campaign->subscribers_count = EmailCampaign::count_subscribers( $campaign->id );
		}
		
		return Helper::format_response( $campaigns );
	}
	
	/**
	 * Get single campaign
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_campaign( $request ) {
		$campaign = EmailCampaign::get( $request['id'] );
		
		if ( ! $campaign ) {
			return new WP_Error( 'campaign_not_found', __( 'Campaign not found', 'practicerx' ), array( 'status' => 404 ) );
		}
		
		// Decode emails
		$campaign->emails = json_decode( $campaign->emails, true );
		$campaign->subscribers_count = EmailCampaign::count_subscribers( $campaign->id );
		
		return Helper::format_response( $campaign );
	}
	
	/**
	 * Create campaign
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_campaign( $request ) {
		$emails = $request->get_param( 'emails' );
		
		$data = array(
			'practitioner_id' => $request->get_param( 'practitioner_id' ),
			'name'            => sanitize_text_field( $request->get_param( 'name' ) ),
			'description'     => wp_kses_post( $request->get_param( 'description' ) ),
			'trigger_type'    => sanitize_text_field( $request->get_param( 'trigger_type' ) ?: 'manual' ),
			'trigger_event'   => sanitize_text_field( $request->get_param( 'trigger_event' ) ?: '' ),
			'emails'          => is_array( $emails ) ? wp_json_encode( $emails ) : $emails,
			'is_active'       => $request->get_param( 'is_active' ) !== false ? 1 : 0,
		);
		
		$id = EmailCampaign::create( $data );
		
		if ( ! $id ) {
			return new WP_Error( 'creation_failed', __( 'Failed to create campaign', 'practicerx' ), array( 'status' => 500 ) );
		}
		
		$campaign = EmailCampaign::get( $id );
		$campaign->emails = json_decode( $campaign->emails, true );
		
		return Helper::format_response( $campaign, 201 );
	}
	
	/**
	 * Update campaign
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_campaign( $request ) {
		$campaign = EmailCampaign::get( $request['id'] );
		
		if ( ! $campaign ) {
			return new WP_Error( 'campaign_not_found', __( 'Campaign not found', 'practicerx' ), array( 'status' => 404 ) );
		}
		
		$data = array();
		
		if ( $request->has_param( 'name' ) ) {
			$data['name'] = sanitize_text_field( $request->get_param( 'name' ) );
		}
		if ( $request->has_param( 'description' ) ) {
			$data['description'] = wp_kses_post( $request->get_param( 'description' ) );
		}
		if ( $request->has_param( 'trigger_type' ) ) {
			$data['trigger_type'] = sanitize_text_field( $request->get_param( 'trigger_type' ) );
		}
		if ( $request->has_param( 'trigger_event' ) ) {
			$data['trigger_event'] = sanitize_text_field( $request->get_param( 'trigger_event' ) );
		}
		if ( $request->has_param( 'emails' ) ) {
			$emails = $request->get_param( 'emails' );
			$data['emails'] = is_array( $emails ) ? wp_json_encode( $emails ) : $emails;
		}
		if ( $request->has_param( 'is_active' ) ) {
			$data['is_active'] = $request->get_param( 'is_active' ) ? 1 : 0;
		}
		
		$updated = EmailCampaign::update( $request['id'], $data );
		
		if ( ! $updated ) {
			return new WP_Error( 'update_failed', __( 'Failed to update campaign', 'practicerx' ), array( 'status' => 500 ) );
		}
		
		$campaign = EmailCampaign::get( $request['id'] );
		$campaign->emails = json_decode( $campaign->emails, true );
		
		return Helper::format_response( $campaign );
	}
	
	/**
	 * Delete campaign
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_campaign( $request ) {
		$campaign = EmailCampaign::get( $request['id'] );
		
		if ( ! $campaign ) {
			return new WP_Error( 'campaign_not_found', __( 'Campaign not found', 'practicerx' ), array( 'status' => 404 ) );
		}
		
		$deleted = EmailCampaign::delete( $request['id'] );
		
		if ( ! $deleted ) {
			return new WP_Error( 'deletion_failed', __( 'Failed to delete campaign', 'practicerx' ), array( 'status' => 500 ) );
		}
		
		return Helper::format_response( array( 'deleted' => true ) );
	}
	
	/**
	 * Subscribe client to campaign
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function subscribe_client( $request ) {
		$client_id = $request->get_param( 'client_id' );
		
		if ( ! $client_id ) {
			return new WP_Error( 'missing_client', __( 'Client ID required', 'practicerx' ), array( 'status' => 400 ) );
		}
		
		$subscription_id = CampaignService::subscribe( $request['id'], $client_id );
		
		if ( ! $subscription_id ) {
			return new WP_Error( 'subscription_failed', __( 'Failed to subscribe client', 'practicerx' ), array( 'status' => 500 ) );
		}
		
		$subscription = CampaignSubscriber::get( $subscription_id );
		return Helper::format_response( $subscription, 201 );
	}
	
	/**
	 * Unsubscribe client from campaign
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function unsubscribe_client( $request ) {
		$client_id = $request->get_param( 'client_id' );
		
		if ( ! $client_id ) {
			return new WP_Error( 'missing_client', __( 'Client ID required', 'practicerx' ), array( 'status' => 400 ) );
		}
		
		$unsubscribed = CampaignService::unsubscribe( $request['id'], $client_id );
		
		if ( ! $unsubscribed ) {
			return new WP_Error( 'unsubscribe_failed', __( 'Failed to unsubscribe client', 'practicerx' ), array( 'status' => 500 ) );
		}
		
		return Helper::format_response( array( 'unsubscribed' => true ) );
	}
	
	/**
	 * Get campaign subscribers
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_subscribers( $request ) {
		$status = $request->get_param( 'status' );
		$subscribers = CampaignSubscriber::get_by_campaign( $request['id'], $status );
		
		return Helper::format_response( $subscribers );
	}
}
