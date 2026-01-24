<?php
/**
 * Meal Plans API Controller
 *
 * REST API endpoints for meal planning
 *
 * @package PracticeRx
 */

namespace PracticeRx\Api;

use PracticeRx\Models\MealPlan;
use PracticeRx\Core\Helper;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MealPlansController extends ApiController {
	
	protected $resource_name = 'meal-plans';
	
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
		
		register_rest_route( $this->namespace, '/' . $this->resource_name . '/search', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'search_plans' ),
			'permission_callback' => array( $this, 'check_permissions' ),
		) );
		
		register_rest_route( $this->namespace, '/' . $this->resource_name . '/templates', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_templates' ),
			'permission_callback' => array( $this, 'check_permissions' ),
		) );
	}
	
	/**
	 * Get all meal plans
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_items( $request ) {
		$practitioner_id = $request->get_param( 'practitioner_id' );
		$plan_type = $request->get_param( 'plan_type' );
		$templates_only = $request->get_param( 'templates_only' ) === 'true';
		
		if ( $practitioner_id ) {
			$plans = MealPlan::get_by_practitioner( $practitioner_id, $templates_only );
		} elseif ( $plan_type ) {
			$plans = MealPlan::get_by_type( $plan_type );
		} else {
			$plans = MealPlan::get_all();
		}
		
		// Decode JSON fields
		foreach ( $plans as &$plan ) {
			$plan->macros = json_decode( $plan->macros, true );
			$plan->meals = json_decode( $plan->meals, true );
		}
		
		return Helper::format_response( $plans );
	}
	
	/**
	 * Get single meal plan
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_item( $request ) {
		$plan = MealPlan::get( $request['id'] );
		
		if ( ! $plan ) {
			return new WP_Error( 'plan_not_found', __( 'Meal plan not found', 'practicerx' ), array( 'status' => 404 ) );
		}
		
		// Decode JSON fields
		$plan->macros = json_decode( $plan->macros, true );
		$plan->meals = json_decode( $plan->meals, true );
		
		return Helper::format_response( $plan );
	}
	
	/**
	 * Create meal plan
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_item( $request ) {
		$macros = $request->get_param( 'macros' );
		$meals = $request->get_param( 'meals' );
		
		$data = array(
			'practitioner_id' => $request->get_param( 'practitioner_id' ),
			'title'           => sanitize_text_field( $request->get_param( 'title' ) ),
			'description'     => wp_kses_post( $request->get_param( 'description' ) ),
			'plan_type'       => sanitize_text_field( $request->get_param( 'plan_type' ) ?: 'weekly' ),
			'duration_days'   => intval( $request->get_param( 'duration_days' ) ?: 7 ),
			'calories_target' => intval( $request->get_param( 'calories_target' ) ?: 0 ),
			'macros'          => is_array( $macros ) ? wp_json_encode( $macros ) : $macros,
			'meals'           => is_array( $meals ) ? wp_json_encode( $meals ) : $meals,
			'is_template'     => $request->get_param( 'is_template' ) === true ? 1 : 0,
			'tags'            => sanitize_text_field( $request->get_param( 'tags' ) ?: '' ),
		);
		
		$id = MealPlan::create( $data );
		
		if ( ! $id ) {
			return new WP_Error( 'creation_failed', __( 'Failed to create meal plan', 'practicerx' ), array( 'status' => 500 ) );
		}
		
		$plan = MealPlan::get( $id );
		$plan->macros = json_decode( $plan->macros, true );
		$plan->meals = json_decode( $plan->meals, true );
		
		return Helper::format_response( $plan, 201 );
	}
	
	/**
	 * Update meal plan
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_item( $request ) {
		$plan = MealPlan::get( $request['id'] );
		
		if ( ! $plan ) {
			return new WP_Error( 'plan_not_found', __( 'Meal plan not found', 'practicerx' ), array( 'status' => 404 ) );
		}
		
		$data = array();
		
		if ( $request->has_param( 'title' ) ) {
			$data['title'] = sanitize_text_field( $request->get_param( 'title' ) );
		}
		if ( $request->has_param( 'description' ) ) {
			$data['description'] = wp_kses_post( $request->get_param( 'description' ) );
		}
		if ( $request->has_param( 'plan_type' ) ) {
			$data['plan_type'] = sanitize_text_field( $request->get_param( 'plan_type' ) );
		}
		if ( $request->has_param( 'duration_days' ) ) {
			$data['duration_days'] = intval( $request->get_param( 'duration_days' ) );
		}
		if ( $request->has_param( 'calories_target' ) ) {
			$data['calories_target'] = intval( $request->get_param( 'calories_target' ) );
		}
		if ( $request->has_param( 'macros' ) ) {
			$macros = $request->get_param( 'macros' );
			$data['macros'] = is_array( $macros ) ? wp_json_encode( $macros ) : $macros;
		}
		if ( $request->has_param( 'meals' ) ) {
			$meals = $request->get_param( 'meals' );
			$data['meals'] = is_array( $meals ) ? wp_json_encode( $meals ) : $meals;
		}
		if ( $request->has_param( 'is_template' ) ) {
			$data['is_template'] = $request->get_param( 'is_template' ) ? 1 : 0;
		}
		if ( $request->has_param( 'tags' ) ) {
			$data['tags'] = sanitize_text_field( $request->get_param( 'tags' ) );
		}
		
		$updated = MealPlan::update( $request['id'], $data );
		
		if ( ! $updated ) {
			return new WP_Error( 'update_failed', __( 'Failed to update meal plan', 'practicerx' ), array( 'status' => 500 ) );
		}
		
		$plan = MealPlan::get( $request['id'] );
		$plan->macros = json_decode( $plan->macros, true );
		$plan->meals = json_decode( $plan->meals, true );
		
		return Helper::format_response( $plan );
	}
	
	/**
	 * Delete meal plan
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_item( $request ) {
		$plan = MealPlan::get( $request['id'] );
		
		if ( ! $plan ) {
			return new WP_Error( 'plan_not_found', __( 'Meal plan not found', 'practicerx' ), array( 'status' => 404 ) );
		}
		
		$deleted = MealPlan::delete( $request['id'] );
		
		if ( ! $deleted ) {
			return new WP_Error( 'deletion_failed', __( 'Failed to delete meal plan', 'practicerx' ), array( 'status' => 500 ) );
		}
		
		return Helper::format_response( array( 'deleted' => true ) );
	}
	
	/**
	 * Search meal plans
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function search_plans( $request ) {
		$term = $request->get_param( 'q' );
		$practitioner_id = $request->get_param( 'practitioner_id' ) ?: 0;
		
		if ( empty( $term ) ) {
			return new WP_Error( 'no_search_term', __( 'Search term required', 'practicerx' ), array( 'status' => 400 ) );
		}
		
		$plans = MealPlan::search( $term, $practitioner_id );
		
		foreach ( $plans as &$plan ) {
			$plan->macros = json_decode( $plan->macros, true );
			$plan->meals = json_decode( $plan->meals, true );
		}
		
		return Helper::format_response( $plans );
	}
	
	/**
	 * Get meal plan templates
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_templates( $request ) {
		$templates = MealPlan::get_templates();
		
		foreach ( $templates as &$template ) {
			$template->macros = json_decode( $template->macros, true );
			$template->meals = json_decode( $template->meals, true );
		}
		
		return Helper::format_response( $templates );
	}
}
