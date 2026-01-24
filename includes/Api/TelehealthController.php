<?php
/**
 * Telehealth API Controller
 *
 * REST API endpoints for video consultations
 *
 * @package PracticeRx
 */

namespace PracticeRx\Api;

use PracticeRx\Models\TelehealthSession;
use PracticeRx\Services\TelehealthService;
use PracticeRx\Core\Helper;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TelehealthController extends ApiController {
	
	protected $resource_name = 'telehealth';
	
	/**
	 * Register routes
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->resource_name . '/sessions', array(
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_sessions' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			),
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'create_session' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			),
		) );
		
		register_rest_route( $this->namespace, '/' . $this->resource_name . '/sessions/(?P<id>[\d]+)', array(
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_session' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			),
			array(
				'methods'             => 'PUT',
				'callback'            => array( $this, 'update_session' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			),
			array(
				'methods'             => 'DELETE',
				'callback'            => array( $this, 'delete_session' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			),
		) );
		
		register_rest_route( $this->namespace, '/' . $this->resource_name . '/sessions/(?P<id>[\d]+)/end', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'end_session' ),
			'permission_callback' => array( $this, 'check_permissions' ),
		) );
		
		register_rest_route( $this->namespace, '/' . $this->resource_name . '/upcoming', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_upcoming' ),
			'permission_callback' => array( $this, 'check_permissions' ),
		) );
	}
	
	/**
	 * Get sessions
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_sessions( $request ) {
		$client_id = $request->get_param( 'client_id' );
		$practitioner_id = $request->get_param( 'practitioner_id' );
		$status = $request->get_param( 'status' );
		
		if ( $client_id ) {
			$sessions = TelehealthSession::get_by_client( $client_id, $status );
		} elseif ( $practitioner_id ) {
			$sessions = TelehealthSession::get_by_practitioner( $practitioner_id, $status );
		} else {
			$sessions = TelehealthSession::get_all();
		}
		
		return Helper::format_response( $sessions );
	}
	
	/**
	 * Get single session
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_session( $request ) {
		$session = TelehealthSession::get( $request['id'] );
		
		if ( ! $session ) {
			return new WP_Error( 'session_not_found', __( 'Telehealth session not found', 'practicerx' ), array( 'status' => 404 ) );
		}
		
		// Decode provider data
		$session->provider_data = json_decode( $session->provider_data, true );
		
		return Helper::format_response( $session );
	}
	
	/**
	 * Create session
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_session( $request ) {
		$data = array(
			'appointment_id'  => $request->get_param( 'appointment_id' ),
			'client_id'       => $request->get_param( 'client_id' ),
			'practitioner_id' => $request->get_param( 'practitioner_id' ),
			'provider'        => $request->get_param( 'provider' ) ?: 'zoom',
			'start_time'      => $request->get_param( 'start_time' ) ?: current_time( 'mysql' ),
			'duration'        => intval( $request->get_param( 'duration' ) ?: 60 ),
			'topic'           => $request->get_param( 'topic' ) ?: 'Telehealth Consultation',
		);
		
		$session_id = TelehealthService::create_session( $data );
		
		if ( ! $session_id ) {
			return new WP_Error( 'creation_failed', __( 'Failed to create telehealth session', 'practicerx' ), array( 'status' => 500 ) );
		}
		
		$session = TelehealthSession::get( $session_id );
		$session->provider_data = json_decode( $session->provider_data, true );
		
		return Helper::format_response( $session, 201 );
	}
	
	/**
	 * Update session
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_session( $request ) {
		$session = TelehealthSession::get( $request['id'] );
		
		if ( ! $session ) {
			return new WP_Error( 'session_not_found', __( 'Telehealth session not found', 'practicerx' ), array( 'status' => 404 ) );
		}
		
		$data = array();
		$fields = array( 'start_time', 'duration', 'status', 'recording_url' );
		
		foreach ( $fields as $field ) {
			if ( $request->has_param( $field ) ) {
				$data[ $field ] = $request->get_param( $field );
			}
		}
		
		$updated = TelehealthSession::update( $request['id'], $data );
		
		if ( ! $updated ) {
			return new WP_Error( 'update_failed', __( 'Failed to update session', 'practicerx' ), array( 'status' => 500 ) );
		}
		
		$session = TelehealthSession::get( $request['id'] );
		return Helper::format_response( $session );
	}
	
	/**
	 * End session
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function end_session( $request ) {
		$ended = TelehealthService::end_session( $request['id'] );
		
		if ( ! $ended ) {
			return new WP_Error( 'end_failed', __( 'Failed to end session', 'practicerx' ), array( 'status' => 500 ) );
		}
		
		$session = TelehealthSession::get( $request['id'] );
		return Helper::format_response( $session );
	}
	
	/**
	 * Delete session
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_session( $request ) {
		$session = TelehealthSession::get( $request['id'] );
		
		if ( ! $session ) {
			return new WP_Error( 'session_not_found', __( 'Telehealth session not found', 'practicerx' ), array( 'status' => 404 ) );
		}
		
		$deleted = TelehealthSession::delete( $request['id'] );
		
		if ( ! $deleted ) {
			return new WP_Error( 'deletion_failed', __( 'Failed to delete session', 'practicerx' ), array( 'status' => 500 ) );
		}
		
		return Helper::format_response( array( 'deleted' => true ) );
	}
	
	/**
	 * Get upcoming sessions
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_upcoming( $request ) {
		$practitioner_id = $request->get_param( 'practitioner_id' ) ?: 0;
		$sessions = TelehealthSession::get_upcoming( $practitioner_id );
		
		return Helper::format_response( $sessions );
	}
}
