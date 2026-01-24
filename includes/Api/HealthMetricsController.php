<?php
/**
 * Health Metrics API Controller
 *
 * REST API endpoints for health tracking
 *
 * @package PracticeRx
 */

namespace PracticeRx\Api;

use PracticeRx\Models\HealthMetric;
use PracticeRx\Core\Helper;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class HealthMetricsController extends ApiController {
	
	protected $resource_name = 'health-metrics';
	
	/**
	 * Register routes
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->resource_name, array(
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			),
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'create_item' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			),
		) );
		
		register_rest_route( $this->namespace, '/' . $this->resource_name . '/(?P<id>[\d]+)', array(
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			),
			array(
				'methods'             => 'PUT',
				'callback'            => array( $this, 'update_item' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			),
			array(
				'methods'             => 'DELETE',
				'callback'            => array( $this, 'delete_item' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			),
		) );
		
		register_rest_route( $this->namespace, '/' . $this->resource_name . '/client/(?P<client_id>[\d]+)', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_by_client' ),
			'permission_callback' => array( $this, 'check_permissions' ),
		) );
		
		register_rest_route( $this->namespace, '/' . $this->resource_name . '/client/(?P<client_id>[\d]+)/history', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_metric_history' ),
			'permission_callback' => array( $this, 'check_permissions' ),
		) );
		
		register_rest_route( $this->namespace, '/' . $this->resource_name . '/client/(?P<client_id>[\d]+)/abnormal', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_abnormal' ),
			'permission_callback' => array( $this, 'check_permissions' ),
		) );
	}
	
	/**
	 * Get all metrics
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_items( $request ) {
		$client_id = $request->get_param( 'client_id' );
		$metric_type = $request->get_param( 'metric_type' );
		
		if ( $client_id && $metric_type ) {
			$metrics = HealthMetric::get_by_type( $client_id, $metric_type );
		} elseif ( $client_id ) {
			$metrics = HealthMetric::get_by_client( $client_id );
		} else {
			$metrics = HealthMetric::get_all();
		}
		
		return Helper::format_response( $metrics );
	}
	
	/**
	 * Get single metric
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_item( $request ) {
		$metric = HealthMetric::get( $request['id'] );
		
		if ( ! $metric ) {
			return new WP_Error( 'metric_not_found', __( 'Health metric not found', 'practicerx' ), array( 'status' => 404 ) );
		}
		
		return Helper::format_response( $metric );
	}
	
	/**
	 * Create health metric
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_item( $request ) {
		$data = array(
			'client_id'       => $request->get_param( 'client_id' ),
			'practitioner_id' => $request->get_param( 'practitioner_id' ),
			'metric_type'     => sanitize_text_field( $request->get_param( 'metric_type' ) ),
			'metric_name'     => sanitize_text_field( $request->get_param( 'metric_name' ) ),
			'value'           => sanitize_text_field( $request->get_param( 'value' ) ),
			'unit'            => sanitize_text_field( $request->get_param( 'unit' ) ?: '' ),
			'recorded_date'   => $request->get_param( 'recorded_date' ) ?: current_time( 'mysql' ),
			'notes'           => wp_kses_post( $request->get_param( 'notes' ) ?: '' ),
			'reference_range' => sanitize_text_field( $request->get_param( 'reference_range' ) ?: '' ),
			'is_abnormal'     => $request->get_param( 'is_abnormal' ) === true ? 1 : 0,
		);
		
		$id = HealthMetric::create( $data );
		
		if ( ! $id ) {
			return new WP_Error( 'creation_failed', __( 'Failed to create health metric', 'practicerx' ), array( 'status' => 500 ) );
		}
		
		$metric = HealthMetric::get( $id );
		return Helper::format_response( $metric, 201 );
	}
	
	/**
	 * Update health metric
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_item( $request ) {
		$metric = HealthMetric::get( $request['id'] );
		
		if ( ! $metric ) {
			return new WP_Error( 'metric_not_found', __( 'Health metric not found', 'practicerx' ), array( 'status' => 404 ) );
		}
		
		$data = array();
		$fields = array( 'value', 'unit', 'recorded_date', 'notes', 'reference_range', 'is_abnormal' );
		
		foreach ( $fields as $field ) {
			if ( $request->has_param( $field ) ) {
				$data[ $field ] = $request->get_param( $field );
			}
		}
		
		$updated = HealthMetric::update( $request['id'], $data );
		
		if ( ! $updated ) {
			return new WP_Error( 'update_failed', __( 'Failed to update health metric', 'practicerx' ), array( 'status' => 500 ) );
		}
		
		$metric = HealthMetric::get( $request['id'] );
		return Helper::format_response( $metric );
	}
	
	/**
	 * Delete health metric
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_item( $request ) {
		$metric = HealthMetric::get( $request['id'] );
		
		if ( ! $metric ) {
			return new WP_Error( 'metric_not_found', __( 'Health metric not found', 'practicerx' ), array( 'status' => 404 ) );
		}
		
		$deleted = HealthMetric::delete( $request['id'] );
		
		if ( ! $deleted ) {
			return new WP_Error( 'deletion_failed', __( 'Failed to delete health metric', 'practicerx' ), array( 'status' => 500 ) );
		}
		
		return Helper::format_response( array( 'deleted' => true ) );
	}
	
	/**
	 * Get metrics by client
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_by_client( $request ) {
		$metric_type = $request->get_param( 'metric_type' );
		$metrics = HealthMetric::get_by_client( $request['client_id'], $metric_type );
		
		return Helper::format_response( $metrics );
	}
	
	/**
	 * Get metric history (time series)
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_metric_history( $request ) {
		$metric_name = $request->get_param( 'metric_name' );
		$start_date = $request->get_param( 'start_date' );
		$end_date = $request->get_param( 'end_date' );
		
		if ( empty( $metric_name ) ) {
			return new WP_Error( 'missing_metric', __( 'Metric name required', 'practicerx' ), array( 'status' => 400 ) );
		}
		
		$metrics = HealthMetric::get_metric_history( 
			$request['client_id'], 
			$metric_name, 
			$start_date, 
			$end_date 
		);
		
		return Helper::format_response( $metrics );
	}
	
	/**
	 * Get abnormal metrics
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_abnormal( $request ) {
		$metrics = HealthMetric::get_abnormal( $request['client_id'] );
		
		return Helper::format_response( $metrics );
	}
}
