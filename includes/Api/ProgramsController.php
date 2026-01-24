<?php
/**
 * Programs API Controller
 *
 * REST API endpoints for treatment programs/packages
 *
 * @package PracticeRx
 */

namespace PracticeRx\Api;

use PracticeRx\Models\Program;
use PracticeRx\Core\Helper;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ProgramsController extends ApiController {
	
	protected $resource_name = 'programs';
	
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
		
		register_rest_route( $this->namespace, '/' . $this->resource_name . '/practitioner/(?P<practitioner_id>[\d]+)', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_by_practitioner' ),
			'permission_callback' => array( $this, 'check_permissions' ),
		) );
	}
	
	/**
	 * Get all programs
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_items( $request ) {
		$practitioner_id = $request->get_param( 'practitioner_id' );
		$type = $request->get_param( 'type' );
		$active_only = $request->get_param( 'active_only' ) !== 'false';
		
		if ( $practitioner_id ) {
			$programs = Program::get_by_practitioner( $practitioner_id, $active_only );
		} elseif ( $type ) {
			$programs = Program::get_by_type( $type );
		} elseif ( $active_only ) {
			$programs = Program::get_active();
		} else {
			$programs = Program::get_all();
		}
		
		return Helper::format_response( $programs );
	}
	
	/**
	 * Get single program
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_item( $request ) {
		$program = Program::get( $request['id'] );
		
		if ( ! $program ) {
			return new WP_Error( 'program_not_found', __( 'Program not found', 'practicerx' ), array( 'status' => 404 ) );
		}
		
		// Add enrollment count
		$program->enrollments = Program::count_enrollments( $program->id );
		
		return Helper::format_response( $program );
	}
	
	/**
	 * Create program
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_item( $request ) {
		$data = array(
			'practitioner_id'    => $request->get_param( 'practitioner_id' ),
			'title'              => sanitize_text_field( $request->get_param( 'title' ) ),
			'description'        => wp_kses_post( $request->get_param( 'description' ) ),
			'price'              => floatval( $request->get_param( 'price' ) ),
			'currency'           => sanitize_text_field( $request->get_param( 'currency' ) ?: 'USD' ),
			'duration_weeks'     => intval( $request->get_param( 'duration_weeks' ) ?: 0 ),
			'duration_days'      => intval( $request->get_param( 'duration_days' ) ?: 0 ),
			'sessions_included'  => intval( $request->get_param( 'sessions_included' ) ?: 0 ),
			'program_type'       => sanitize_text_field( $request->get_param( 'program_type' ) ?: 'package' ),
			'features'           => wp_kses_post( $request->get_param( 'features' ) ?: '' ),
			'includes'           => wp_kses_post( $request->get_param( 'includes' ) ?: '' ),
			'is_active'          => $request->get_param( 'is_active' ) !== false ? 1 : 0,
			'order_number'       => intval( $request->get_param( 'order_number' ) ?: 0 ),
		);
		
		$id = Program::create( $data );
		
		if ( ! $id ) {
			return new WP_Error( 'creation_failed', __( 'Failed to create program', 'practicerx' ), array( 'status' => 500 ) );
		}
		
		$program = Program::get( $id );
		return Helper::format_response( $program, 201 );
	}
	
	/**
	 * Update program
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_item( $request ) {
		$program = Program::get( $request['id'] );
		
		if ( ! $program ) {
			return new WP_Error( 'program_not_found', __( 'Program not found', 'practicerx' ), array( 'status' => 404 ) );
		}
		
		$data = array();
		$fields = array( 'title', 'description', 'price', 'currency', 'duration_weeks', 'duration_days', 
			'sessions_included', 'program_type', 'features', 'includes', 'is_active', 'order_number' );
		
		foreach ( $fields as $field ) {
			if ( $request->has_param( $field ) ) {
				$data[ $field ] = $request->get_param( $field );
			}
		}
		
		$updated = Program::update( $request['id'], $data );
		
		if ( ! $updated ) {
			return new WP_Error( 'update_failed', __( 'Failed to update program', 'practicerx' ), array( 'status' => 500 ) );
		}
		
		$program = Program::get( $request['id'] );
		return Helper::format_response( $program );
	}
	
	/**
	 * Delete program
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_item( $request ) {
		$program = Program::get( $request['id'] );
		
		if ( ! $program ) {
			return new WP_Error( 'program_not_found', __( 'Program not found', 'practicerx' ), array( 'status' => 404 ) );
		}
		
		$deleted = Program::delete( $request['id'] );
		
		if ( ! $deleted ) {
			return new WP_Error( 'deletion_failed', __( 'Failed to delete program', 'practicerx' ), array( 'status' => 500 ) );
		}
		
		return Helper::format_response( array( 'deleted' => true ) );
	}
	
	/**
	 * Get programs by practitioner
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_by_practitioner( $request ) {
		$programs = Program::get_by_practitioner( $request['practitioner_id'] );
		return Helper::format_response( $programs );
	}
}
