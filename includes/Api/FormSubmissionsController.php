<?php
/**
 * Form Submissions API Controller
 *
 * REST API endpoints for form submissions
 *
 * @package PracticeRx
 */

namespace PracticeRx\Api;

use PracticeRx\Models\FormSubmission;
use PracticeRx\Models\Form;
use PracticeRx\Core\Helper;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FormSubmissionsController extends ApiController {
	
	protected $resource_name = 'form-submissions';
	
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
				'permission_callback' => '__return_true', // Public submissions allowed
			),
		) );
		
		register_rest_route( $this->namespace, '/' . $this->resource_name . '/(?P<id>[\d]+)', array(
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			),
			array(
				'methods'             => 'DELETE',
				'callback'            => array( $this, 'delete_item' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			),
		) );
		
		register_rest_route( $this->namespace, '/' . $this->resource_name . '/form/(?P<form_id>[\d]+)', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_by_form' ),
			'permission_callback' => array( $this, 'check_permissions' ),
		) );
		
		register_rest_route( $this->namespace, '/' . $this->resource_name . '/client/(?P<client_id>[\d]+)', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_by_client' ),
			'permission_callback' => array( $this, 'check_permissions' ),
		) );
	}
	
	/**
	 * Get all submissions
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_items( $request ) {
		$form_id = $request->get_param( 'form_id' );
		$client_id = $request->get_param( 'client_id' );
		$practitioner_id = $request->get_param( 'practitioner_id' );
		
		if ( $form_id ) {
			$submissions = FormSubmission::get_by_form( $form_id );
		} elseif ( $client_id ) {
			$submissions = FormSubmission::get_by_client( $client_id );
		} elseif ( $practitioner_id ) {
			$submissions = FormSubmission::get_by_practitioner( $practitioner_id );
		} else {
			$submissions = FormSubmission::get_recent();
		}
		
		// Decode responses and add form titles
		foreach ( $submissions as &$submission ) {
			$submission->responses = json_decode( $submission->responses, true );
			$form = Form::get( $submission->form_id );
			if ( $form ) {
				$submission->form_title = $form->title;
			}
		}
		
		return Helper::format_response( $submissions );
	}
	
	/**
	 * Get single submission
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_item( $request ) {
		$submission = FormSubmission::get( $request['id'] );
		
		if ( ! $submission ) {
			return new WP_Error( 'submission_not_found', __( 'Form submission not found', 'practicerx' ), array( 'status' => 404 ) );
		}
		
		$submission->responses = json_decode( $submission->responses, true );
		
		// Add form details
		$form = Form::get( $submission->form_id );
		if ( $form ) {
			$form->fields = json_decode( $form->fields, true );
			$submission->form_details = $form;
		}
		
		return Helper::format_response( $submission );
	}
	
	/**
	 * Submit form
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_item( $request ) {
		$form_id = $request->get_param( 'form_id' );
		$form = Form::get( $form_id );
		
		if ( ! $form ) {
			return new WP_Error( 'form_not_found', __( 'Form not found', 'practicerx' ), array( 'status' => 404 ) );
		}
		
		// Check if form is active
		if ( ! $form->is_active ) {
			return new WP_Error( 'form_inactive', __( 'Form is not accepting submissions', 'practicerx' ), array( 'status' => 403 ) );
		}
		
		$responses = $request->get_param( 'responses' );
		
		$data = array(
			'form_id'         => $form_id,
			'client_id'       => $request->get_param( 'client_id' ) ?: null,
			'practitioner_id' => $form->practitioner_id,
			'responses'       => is_array( $responses ) ? wp_json_encode( $responses ) : $responses,
			'status'          => 'completed',
			'ip_address'      => $_SERVER['REMOTE_ADDR'] ?? '',
		);
		
		$id = FormSubmission::create( $data );
		
		if ( ! $id ) {
			return new WP_Error( 'creation_failed', __( 'Failed to submit form', 'practicerx' ), array( 'status' => 500 ) );
		}
		
		$submission = FormSubmission::get( $id );
		$submission->responses = json_decode( $submission->responses, true );
		
		return Helper::format_response( $submission, 201 );
	}
	
	/**
	 * Delete submission
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_item( $request ) {
		$submission = FormSubmission::get( $request['id'] );
		
		if ( ! $submission ) {
			return new WP_Error( 'submission_not_found', __( 'Form submission not found', 'practicerx' ), array( 'status' => 404 ) );
		}
		
		$deleted = FormSubmission::delete( $request['id'] );
		
		if ( ! $deleted ) {
			return new WP_Error( 'deletion_failed', __( 'Failed to delete submission', 'practicerx' ), array( 'status' => 500 ) );
		}
		
		return Helper::format_response( array( 'deleted' => true ) );
	}
	
	/**
	 * Get submissions by form
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_by_form( $request ) {
		$submissions = FormSubmission::get_by_form( $request['form_id'] );
		
		foreach ( $submissions as &$submission ) {
			$submission->responses = json_decode( $submission->responses, true );
		}
		
		return Helper::format_response( $submissions );
	}
	
	/**
	 * Get submissions by client
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_by_client( $request ) {
		$submissions = FormSubmission::get_by_client( $request['client_id'] );
		
		foreach ( $submissions as &$submission ) {
			$submission->responses = json_decode( $submission->responses, true );
			$form = Form::get( $submission->form_id );
			if ( $form ) {
				$submission->form_title = $form->title;
			}
		}
		
		return Helper::format_response( $submissions );
	}
}
