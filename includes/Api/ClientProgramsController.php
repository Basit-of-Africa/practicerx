<?php
/**
 * Client Programs API Controller
 *
 * REST API endpoints for client program enrollments
 *
 * @package PracticeRx
 */

namespace PracticeRx\Api;

use PracticeRx\Models\ClientProgram;
use PracticeRx\Models\Program;
use PracticeRx\Core\Helper;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ClientProgramsController extends ApiController {
	
	protected $resource_name = 'client-programs';
	
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
		
		register_rest_route( $this->namespace, '/' . $this->resource_name . '/(?P<id>[\d]+)/progress', array(
			'methods'             => 'PUT',
			'callback'            => array( $this, 'update_progress' ),
			'permission_callback' => array( $this, 'check_permissions' ),
		) );
		
		register_rest_route( $this->namespace, '/' . $this->resource_name . '/client/(?P<client_id>[\d]+)', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_by_client' ),
			'permission_callback' => array( $this, 'check_permissions' ),
		) );
	}
	
	/**
	 * Get all client programs
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_items( $request ) {
		$client_id = $request->get_param( 'client_id' );
		$practitioner_id = $request->get_param( 'practitioner_id' );
		$status = $request->get_param( 'status' );
		
		if ( $client_id ) {
			$programs = ClientProgram::get_by_client( $client_id, $status );
		} elseif ( $practitioner_id ) {
			$programs = ClientProgram::get_by_practitioner( $practitioner_id, $status );
		} else {
			$programs = ClientProgram::get_all();
		}
		
		// Enrich with program details
		foreach ( $programs as &$client_program ) {
			$program = Program::get( $client_program->program_id );
			if ( $program ) {
				$client_program->program_title = $program->title;
				$client_program->program_type = $program->program_type;
			}
		}
		
		return Helper::format_response( $programs );
	}
	
	/**
	 * Get single client program
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_item( $request ) {
		$client_program = ClientProgram::get( $request['id'] );
		
		if ( ! $client_program ) {
			return new WP_Error( 'client_program_not_found', __( 'Client program not found', 'practicerx' ), array( 'status' => 404 ) );
		}
		
		// Add program details
		$program = Program::get( $client_program->program_id );
		if ( $program ) {
			$client_program->program_details = $program;
		}
		
		return Helper::format_response( $client_program );
	}
	
	/**
	 * Enroll client in program
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_item( $request ) {
		$program = Program::get( $request->get_param( 'program_id' ) );
		
		if ( ! $program ) {
			return new WP_Error( 'program_not_found', __( 'Program not found', 'practicerx' ), array( 'status' => 404 ) );
		}
		
		$start_date = $request->get_param( 'start_date' ) ?: current_time( 'Y-m-d' );
		
		// Calculate end date based on program duration
		$end_date = null;
		if ( $program->duration_weeks > 0 || $program->duration_days > 0 ) {
			$total_days = ( $program->duration_weeks * 7 ) + $program->duration_days;
			$end_date = date( 'Y-m-d', strtotime( $start_date . ' +' . $total_days . ' days' ) );
		}
		
		$data = array(
			'client_id'           => $request->get_param( 'client_id' ),
			'program_id'          => $request->get_param( 'program_id' ),
			'practitioner_id'     => $request->get_param( 'practitioner_id' ),
			'status'              => 'active',
			'start_date'          => $start_date,
			'end_date'            => $end_date,
			'sessions_completed'  => 0,
			'sessions_remaining'  => $program->sessions_included,
			'progress_percentage' => 0,
			'invoice_id'          => $request->get_param( 'invoice_id' ) ?: null,
		);
		
		$id = ClientProgram::create( $data );
		
		if ( ! $id ) {
			return new WP_Error( 'creation_failed', __( 'Failed to enroll client in program', 'practicerx' ), array( 'status' => 500 ) );
		}
		
		$client_program = ClientProgram::get( $id );
		return Helper::format_response( $client_program, 201 );
	}
	
	/**
	 * Update client program
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_item( $request ) {
		$client_program = ClientProgram::get( $request['id'] );
		
		if ( ! $client_program ) {
			return new WP_Error( 'client_program_not_found', __( 'Client program not found', 'practicerx' ), array( 'status' => 404 ) );
		}
		
		$data = array();
		$fields = array( 'status', 'start_date', 'end_date', 'notes' );
		
		foreach ( $fields as $field ) {
			if ( $request->has_param( $field ) ) {
				$data[ $field ] = $request->get_param( $field );
			}
		}
		
		$updated = ClientProgram::update( $request['id'], $data );
		
		if ( ! $updated ) {
			return new WP_Error( 'update_failed', __( 'Failed to update client program', 'practicerx' ), array( 'status' => 500 ) );
		}
		
		$client_program = ClientProgram::get( $request['id'] );
		return Helper::format_response( $client_program );
	}
	
	/**
	 * Update program progress
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_progress( $request ) {
		$sessions_completed = intval( $request->get_param( 'sessions_completed' ) );
		
		$updated = ClientProgram::update_progress( $request['id'], $sessions_completed );
		
		if ( ! $updated ) {
			return new WP_Error( 'update_failed', __( 'Failed to update progress', 'practicerx' ), array( 'status' => 500 ) );
		}
		
		$client_program = ClientProgram::get( $request['id'] );
		return Helper::format_response( $client_program );
	}
	
	/**
	 * Delete client program
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_item( $request ) {
		$client_program = ClientProgram::get( $request['id'] );
		
		if ( ! $client_program ) {
			return new WP_Error( 'client_program_not_found', __( 'Client program not found', 'practicerx' ), array( 'status' => 404 ) );
		}
		
		$deleted = ClientProgram::delete( $request['id'] );
		
		if ( ! $deleted ) {
			return new WP_Error( 'deletion_failed', __( 'Failed to delete client program', 'practicerx' ), array( 'status' => 500 ) );
		}
		
		return Helper::format_response( array( 'deleted' => true ) );
	}
	
	/**
	 * Get programs by client
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_by_client( $request ) {
		$programs = ClientProgram::get_by_client( $request['client_id'] );
		
		// Enrich with program details
		foreach ( $programs as &$client_program ) {
			$program = Program::get( $client_program->program_id );
			if ( $program ) {
				$client_program->program_details = $program;
			}
		}
		
		return Helper::format_response( $programs );
	}
}
