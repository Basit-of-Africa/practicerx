<?php
/**
 * Client Portal API Controller
 *
 * REST API endpoints for client portal settings
 *
 * @package PracticeRx
 */

namespace PracticeRx\Api;

use PracticeRx\Models\PortalSettings;
use PracticeRx\Core\Helper;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PortalController extends ApiController {
	
	protected $resource_name = 'portal';
	
	/**
	 * Register routes
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->resource_name . '/settings', array(
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_settings' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			),
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'update_settings' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			),
		) );
		
		register_rest_route( $this->namespace, '/' . $this->resource_name . '/settings/(?P<practitioner_id>[\d]+)', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_practitioner_settings' ),
			'permission_callback' => array( $this, 'check_permissions' ),
		) );
	}
	
	/**
	 * Get portal settings for current user
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_settings( $request ) {
		$practitioner_id = $request->get_param( 'practitioner_id' ) ?: get_current_user_id();
		
		$settings = PortalSettings::get_or_create( $practitioner_id );
		
		// Decode JSON fields
		$settings->features_enabled = json_decode( $settings->features_enabled, true );
		$settings->email_settings = json_decode( $settings->email_settings, true );
		
		return Helper::format_response( $settings );
	}
	
	/**
	 * Get settings by practitioner
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_practitioner_settings( $request ) {
		$settings = PortalSettings::get_or_create( $request['practitioner_id'] );
		
		// Decode JSON fields
		$settings->features_enabled = json_decode( $settings->features_enabled, true );
		$settings->email_settings = json_decode( $settings->email_settings, true );
		
		return Helper::format_response( $settings );
	}
	
	/**
	 * Update portal settings
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_settings( $request ) {
		$practitioner_id = $request->get_param( 'practitioner_id' ) ?: get_current_user_id();
		
		$settings = PortalSettings::get_or_create( $practitioner_id );
		
		$data = array();
		
		if ( $request->has_param( 'portal_name' ) ) {
			$data['portal_name'] = sanitize_text_field( $request->get_param( 'portal_name' ) );
		}
		if ( $request->has_param( 'logo_url' ) ) {
			$data['logo_url'] = esc_url_raw( $request->get_param( 'logo_url' ) );
		}
		if ( $request->has_param( 'primary_color' ) ) {
			$data['primary_color'] = sanitize_hex_color( $request->get_param( 'primary_color' ) );
		}
		if ( $request->has_param( 'secondary_color' ) ) {
			$data['secondary_color'] = sanitize_hex_color( $request->get_param( 'secondary_color' ) );
		}
		if ( $request->has_param( 'custom_domain' ) ) {
			$data['custom_domain'] = sanitize_text_field( $request->get_param( 'custom_domain' ) );
		}
		if ( $request->has_param( 'welcome_message' ) ) {
			$data['welcome_message'] = wp_kses_post( $request->get_param( 'welcome_message' ) );
		}
		if ( $request->has_param( 'features_enabled' ) ) {
			$features = $request->get_param( 'features_enabled' );
			$data['features_enabled'] = is_array( $features ) ? wp_json_encode( $features ) : $features;
		}
		if ( $request->has_param( 'email_settings' ) ) {
			$email = $request->get_param( 'email_settings' );
			$data['email_settings'] = is_array( $email ) ? wp_json_encode( $email ) : $email;
		}
		
		$updated = PortalSettings::update( $settings->id, $data );
		
		if ( ! $updated ) {
			return new WP_Error( 'update_failed', __( 'Failed to update portal settings', 'practicerx' ), array( 'status' => 500 ) );
		}
		
		$settings = PortalSettings::get( $settings->id );
		$settings->features_enabled = json_decode( $settings->features_enabled, true );
		$settings->email_settings = json_decode( $settings->email_settings, true );
		
		return Helper::format_response( $settings );
	}
}
