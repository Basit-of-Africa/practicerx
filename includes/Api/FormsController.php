<?php
/**
 * Forms API Controller
 *
 * REST API endpoints for form builder
 *
 * @package PracticeRx
 */

namespace PracticeRx\Api;

use PracticeRx\Models\Form;
use PracticeRx\Core\Helper;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FormsController extends ApiController {
	
	protected $resource_name = 'forms';
	
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
		
		register_rest_route( $this->namespace, '/' . $this->resource_name . '/public', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_public_forms' ),
			'permission_callback' => '__return_true',
		) );
	}
	
	/**
	 * Get all forms
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_items( $request ) {
		$practitioner_id = $request->get_param( 'practitioner_id' );
		$type = $request->get_param( 'type' );
		
		if ( $practitioner_id ) {
			$forms = Form::get_by_practitioner( $practitioner_id );
		} elseif ( $type ) {
			$forms = Form::get_by_type( $type );
		} else {
			$forms = Form::get_all();
		}
		
		// Add submission counts
		foreach ( $forms as &$form ) {
			$form->submissions_count = Form::count_submissions( $form->id );
		}
		
		return Helper::format_response( $forms );
	}
	
	/**
	 * Get single form
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_item( $request ) {
		$form = Form::get( $request['id'] );
		
		if ( ! $form ) {
			return new WP_Error( 'form_not_found', __( 'Form not found', 'practicerx' ), array( 'status' => 404 ) );
		}
		
		// Decode JSON fields
		$form->fields = json_decode( $form->fields, true );
		$form->settings = json_decode( $form->settings, true );
		$form->submissions_count = Form::count_submissions( $form->id );
		
		return Helper::format_response( $form );
	}
	
	/**
	 * Create form
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_item( $request ) {
		$fields = $request->get_param( 'fields' );
		$settings = $request->get_param( 'settings' );
		
		$data = array(
			'practitioner_id' => $request->get_param( 'practitioner_id' ),
			'title'           => sanitize_text_field( $request->get_param( 'title' ) ),
			'description'     => wp_kses_post( $request->get_param( 'description' ) ),
			'form_type'       => sanitize_text_field( $request->get_param( 'form_type' ) ?: 'questionnaire' ),
			'fields'          => is_array( $fields ) ? wp_json_encode( $fields ) : $fields,
			'settings'        => is_array( $settings ) ? wp_json_encode( $settings ) : $settings,
			'is_active'       => $request->get_param( 'is_active' ) !== false ? 1 : 0,
			'is_public'       => $request->get_param( 'is_public' ) === true ? 1 : 0,
		);
		
		$id = Form::create( $data );
		
		if ( ! $id ) {
			return new WP_Error( 'creation_failed', __( 'Failed to create form', 'practicerx' ), array( 'status' => 500 ) );
		}
		
		$form = Form::get( $id );
		$form->fields = json_decode( $form->fields, true );
		$form->settings = json_decode( $form->settings, true );
		
		return Helper::format_response( $form, 201 );
	}
	
	/**
	 * Update form
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_item( $request ) {
		$form = Form::get( $request['id'] );
		
		if ( ! $form ) {
			return new WP_Error( 'form_not_found', __( 'Form not found', 'practicerx' ), array( 'status' => 404 ) );
		}
		
		$data = array();
		
		if ( $request->has_param( 'title' ) ) {
			$data['title'] = sanitize_text_field( $request->get_param( 'title' ) );
		}
		if ( $request->has_param( 'description' ) ) {
			$data['description'] = wp_kses_post( $request->get_param( 'description' ) );
		}
		if ( $request->has_param( 'form_type' ) ) {
			$data['form_type'] = sanitize_text_field( $request->get_param( 'form_type' ) );
		}
		if ( $request->has_param( 'fields' ) ) {
			$fields = $request->get_param( 'fields' );
			$data['fields'] = is_array( $fields ) ? wp_json_encode( $fields ) : $fields;
		}
		if ( $request->has_param( 'settings' ) ) {
			$settings = $request->get_param( 'settings' );
			$data['settings'] = is_array( $settings ) ? wp_json_encode( $settings ) : $settings;
		}
		if ( $request->has_param( 'is_active' ) ) {
			$data['is_active'] = $request->get_param( 'is_active' ) ? 1 : 0;
		}
		if ( $request->has_param( 'is_public' ) ) {
			$data['is_public'] = $request->get_param( 'is_public' ) ? 1 : 0;
		}
		
		$updated = Form::update( $request['id'], $data );
		
		if ( ! $updated ) {
			return new WP_Error( 'update_failed', __( 'Failed to update form', 'practicerx' ), array( 'status' => 500 ) );
		}
		
		$form = Form::get( $request['id'] );
		$form->fields = json_decode( $form->fields, true );
		$form->settings = json_decode( $form->settings, true );
		
		return Helper::format_response( $form );
	}
	
	/**
	 * Delete form
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_item( $request ) {
		$form = Form::get( $request['id'] );
		
		if ( ! $form ) {
			return new WP_Error( 'form_not_found', __( 'Form not found', 'practicerx' ), array( 'status' => 404 ) );
		}
		
		$deleted = Form::delete( $request['id'] );
		
		if ( ! $deleted ) {
			return new WP_Error( 'deletion_failed', __( 'Failed to delete form', 'practicerx' ), array( 'status' => 500 ) );
		}
		
		return Helper::format_response( array( 'deleted' => true ) );
	}
	
	/**
	 * Get public forms
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_public_forms( $request ) {
		$forms = Form::get_public();
		
		foreach ( $forms as &$form ) {
			$form->fields = json_decode( $form->fields, true );
		}
		
		return Helper::format_response( $forms );
	}
}
