<?php
/**
 * Client Meal Plans API Controller
 *
 * REST API endpoints for meal plans assigned to clients
 *
 * @package PracticeRx
 */

namespace PracticeRx\Api;

use PracticeRx\Models\ClientMealPlan;
use PracticeRx\Models\MealPlan;
use PracticeRx\Core\Helper;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ClientMealPlansController extends ApiController {
	
	protected $resource_name = 'client-meal-plans';
	
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
		
		register_rest_route( $this->namespace, '/' . $this->resource_name . '/client/(?P<client_id>[\d]+)/active', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_active_plan' ),
			'permission_callback' => array( $this, 'check_permissions' ),
		) );
	}
	
	/**
	 * Get all client meal plans
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_items( $request ) {
		$client_id = $request->get_param( 'client_id' );
		$practitioner_id = $request->get_param( 'practitioner_id' );
		$status = $request->get_param( 'status' );
		
		if ( $client_id ) {
			$plans = ClientMealPlan::get_by_client( $client_id, $status );
		} elseif ( $practitioner_id ) {
			$plans = ClientMealPlan::get_by_practitioner( $practitioner_id, $status );
		} else {
			$plans = ClientMealPlan::get_all();
		}
		
		// Enrich with meal plan details
		foreach ( $plans as &$client_plan ) {
			$plan = MealPlan::get( $client_plan->meal_plan_id );
			if ( $plan ) {
				$client_plan->plan_title = $plan->title;
				$client_plan->plan_type = $plan->plan_type;
			}
			$client_plan->customizations = json_decode( $client_plan->customizations, true );
		}
		
		return Helper::format_response( $plans );
	}
	
	/**
	 * Get single client meal plan
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_item( $request ) {
		$client_plan = ClientMealPlan::get( $request['id'] );
		
		if ( ! $client_plan ) {
			return new WP_Error( 'plan_not_found', __( 'Client meal plan not found', 'practicerx' ), array( 'status' => 404 ) );
		}
		
		// Add meal plan details
		$plan = MealPlan::get( $client_plan->meal_plan_id );
		if ( $plan ) {
			$plan->macros = json_decode( $plan->macros, true );
			$plan->meals = json_decode( $plan->meals, true );
			$client_plan->meal_plan_details = $plan;
		}
		
		$client_plan->customizations = json_decode( $client_plan->customizations, true );
		
		return Helper::format_response( $client_plan );
	}
	
	/**
	 * Assign meal plan to client
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_item( $request ) {
		$plan = MealPlan::get( $request->get_param( 'meal_plan_id' ) );
		
		if ( ! $plan ) {
			return new WP_Error( 'plan_not_found', __( 'Meal plan not found', 'practicerx' ), array( 'status' => 404 ) );
		}
		
		$start_date = $request->get_param( 'start_date' ) ?: current_time( 'Y-m-d' );
		$end_date = $request->get_param( 'end_date' );
		
		// Calculate end date if not provided
		if ( ! $end_date && $plan->duration_days > 0 ) {
			$end_date = date( 'Y-m-d', strtotime( $start_date . ' +' . $plan->duration_days . ' days' ) );
		}
		
		$customizations = $request->get_param( 'customizations' );
		
		$data = array(
			'client_id'       => $request->get_param( 'client_id' ),
			'meal_plan_id'    => $request->get_param( 'meal_plan_id' ),
			'practitioner_id' => $request->get_param( 'practitioner_id' ),
			'start_date'      => $start_date,
			'end_date'        => $end_date,
			'status'          => 'active',
			'customizations'  => is_array( $customizations ) ? wp_json_encode( $customizations ) : $customizations,
			'notes'           => wp_kses_post( $request->get_param( 'notes' ) ?: '' ),
		);
		
		$id = ClientMealPlan::create( $data );
		
		if ( ! $id ) {
			return new WP_Error( 'creation_failed', __( 'Failed to assign meal plan', 'practicerx' ), array( 'status' => 500 ) );
		}
		
		$client_plan = ClientMealPlan::get( $id );
		$client_plan->customizations = json_decode( $client_plan->customizations, true );
		
		return Helper::format_response( $client_plan, 201 );
	}
	
	/**
	 * Update client meal plan
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_item( $request ) {
		$client_plan = ClientMealPlan::get( $request['id'] );
		
		if ( ! $client_plan ) {
			return new WP_Error( 'plan_not_found', __( 'Client meal plan not found', 'practicerx' ), array( 'status' => 404 ) );
		}
		
		$data = array();
		
		if ( $request->has_param( 'status' ) ) {
			$data['status'] = sanitize_text_field( $request->get_param( 'status' ) );
		}
		if ( $request->has_param( 'start_date' ) ) {
			$data['start_date'] = $request->get_param( 'start_date' );
		}
		if ( $request->has_param( 'end_date' ) ) {
			$data['end_date'] = $request->get_param( 'end_date' );
		}
		if ( $request->has_param( 'customizations' ) ) {
			$customizations = $request->get_param( 'customizations' );
			$data['customizations'] = is_array( $customizations ) ? wp_json_encode( $customizations ) : $customizations;
		}
		if ( $request->has_param( 'notes' ) ) {
			$data['notes'] = wp_kses_post( $request->get_param( 'notes' ) );
		}
		
		$updated = ClientMealPlan::update( $request['id'], $data );
		
		if ( ! $updated ) {
			return new WP_Error( 'update_failed', __( 'Failed to update client meal plan', 'practicerx' ), array( 'status' => 500 ) );
		}
		
		$client_plan = ClientMealPlan::get( $request['id'] );
		$client_plan->customizations = json_decode( $client_plan->customizations, true );
		
		return Helper::format_response( $client_plan );
	}
	
	/**
	 * Delete client meal plan
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_item( $request ) {
		$client_plan = ClientMealPlan::get( $request['id'] );
		
		if ( ! $client_plan ) {
			return new WP_Error( 'plan_not_found', __( 'Client meal plan not found', 'practicerx' ), array( 'status' => 404 ) );
		}
		
		$deleted = ClientMealPlan::delete( $request['id'] );
		
		if ( ! $deleted ) {
			return new WP_Error( 'deletion_failed', __( 'Failed to delete client meal plan', 'practicerx' ), array( 'status' => 500 ) );
		}
		
		return Helper::format_response( array( 'deleted' => true ) );
	}
	
	/**
	 * Get meal plans by client
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_by_client( $request ) {
		$status = $request->get_param( 'status' );
		$plans = ClientMealPlan::get_by_client( $request['client_id'], $status );
		
		foreach ( $plans as &$client_plan ) {
			$plan = MealPlan::get( $client_plan->meal_plan_id );
			if ( $plan ) {
				$client_plan->meal_plan_details = $plan;
			}
			$client_plan->customizations = json_decode( $client_plan->customizations, true );
		}
		
		return Helper::format_response( $plans );
	}
	
	/**
	 * Get active meal plan for client
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_active_plan( $request ) {
		$client_plan = ClientMealPlan::get_active_for_client( $request['client_id'] );
		
		if ( ! $client_plan ) {
			return new WP_Error( 'no_active_plan', __( 'No active meal plan found', 'practicerx' ), array( 'status' => 404 ) );
		}
		
		// Add meal plan details
		$plan = MealPlan::get( $client_plan->meal_plan_id );
		if ( $plan ) {
			$plan->macros = json_decode( $plan->macros, true );
			$plan->meals = json_decode( $plan->meals, true );
			$client_plan->meal_plan_details = $plan;
		}
		
		$client_plan->customizations = json_decode( $client_plan->customizations, true );
		
		return Helper::format_response( $client_plan );
	}
}
