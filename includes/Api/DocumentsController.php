<?php
/**
 * Documents API Controller
 *
 * REST API endpoints for document library
 *
 * @package PracticeRx
 */

namespace PracticeRx\Api;

use PracticeRx\Models\Document;
use PracticeRx\Core\Helper;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DocumentsController extends ApiController {
	
	protected $resource_name = 'documents';
	
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
		
		register_rest_route( $this->namespace, '/' . $this->resource_name . '/(?P<id>[\d]+)/share', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'share_document' ),
			'permission_callback' => array( $this, 'check_permissions' ),
		) );
		
		register_rest_route( $this->namespace, '/' . $this->resource_name . '/search', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'search_documents' ),
			'permission_callback' => array( $this, 'check_permissions' ),
		) );
		
		register_rest_route( $this->namespace, '/' . $this->resource_name . '/client/(?P<client_id>[\d]+)', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_client_documents' ),
			'permission_callback' => array( $this, 'check_permissions' ),
		) );
		
		register_rest_route( $this->namespace, '/' . $this->resource_name . '/upload', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'upload_document' ),
			'permission_callback' => array( $this, 'check_permissions' ),
		) );
	}
	
	/**
	 * Get all documents
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_items( $request ) {
		$practitioner_id = $request->get_param( 'practitioner_id' );
		$category = $request->get_param( 'category' );
		$public_only = $request->get_param( 'public_only' ) === 'true';
		
		if ( $public_only ) {
			$documents = Document::get_public();
		} elseif ( $practitioner_id ) {
			$documents = Document::get_by_practitioner( $practitioner_id, $category );
		} elseif ( $category ) {
			$documents = Document::get_by_category( $category );
		} else {
			$documents = Document::get_all();
		}
		
		return Helper::format_response( $documents );
	}
	
	/**
	 * Get single document
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_item( $request ) {
		$document = Document::get( $request['id'] );
		
		if ( ! $document ) {
			return new WP_Error( 'document_not_found', __( 'Document not found', 'practicerx' ), array( 'status' => 404 ) );
		}
		
		return Helper::format_response( $document );
	}
	
	/**
	 * Create document record (after upload)
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_item( $request ) {
		$data = array(
			'practitioner_id' => $request->get_param( 'practitioner_id' ),
			'title'           => sanitize_text_field( $request->get_param( 'title' ) ),
			'description'     => wp_kses_post( $request->get_param( 'description' ) ),
			'file_name'       => sanitize_file_name( $request->get_param( 'file_name' ) ),
			'file_path'       => esc_url_raw( $request->get_param( 'file_path' ) ),
			'file_type'       => sanitize_text_field( $request->get_param( 'file_type' ) ),
			'file_size'       => intval( $request->get_param( 'file_size' ) ),
			'category'        => sanitize_text_field( $request->get_param( 'category' ) ?: 'general' ),
			'is_public'       => $request->get_param( 'is_public' ) === true ? 1 : 0,
			'uploaded_by'     => get_current_user_id(),
		);
		
		$id = Document::create( $data );
		
		if ( ! $id ) {
			return new WP_Error( 'creation_failed', __( 'Failed to create document record', 'practicerx' ), array( 'status' => 500 ) );
		}
		
		$document = Document::get( $id );
		return Helper::format_response( $document, 201 );
	}
	
	/**
	 * Upload document file
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function upload_document( $request ) {
		// Check if file was uploaded
		if ( empty( $_FILES['file'] ) ) {
			return new WP_Error( 'no_file', __( 'No file uploaded', 'practicerx' ), array( 'status' => 400 ) );
		}
		
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/media.php' );
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		
		$file = $_FILES['file'];
		
		// Handle upload
		$upload = wp_handle_upload( $file, array( 'test_form' => false ) );
		
		if ( isset( $upload['error'] ) ) {
			return new WP_Error( 'upload_failed', $upload['error'], array( 'status' => 500 ) );
		}
		
		// Create document record
		$data = array(
			'practitioner_id' => $request->get_param( 'practitioner_id' ) ?: get_current_user_id(),
			'title'           => sanitize_text_field( $request->get_param( 'title' ) ?: $file['name'] ),
			'description'     => wp_kses_post( $request->get_param( 'description' ) ?: '' ),
			'file_name'       => basename( $upload['file'] ),
			'file_path'       => $upload['url'],
			'file_type'       => $upload['type'],
			'file_size'       => filesize( $upload['file'] ),
			'category'        => sanitize_text_field( $request->get_param( 'category' ) ?: 'general' ),
			'is_public'       => $request->get_param( 'is_public' ) === 'true' ? 1 : 0,
			'uploaded_by'     => get_current_user_id(),
		);
		
		$id = Document::create( $data );
		
		if ( ! $id ) {
			// Delete uploaded file if database insert fails
			wp_delete_file( $upload['file'] );
			return new WP_Error( 'creation_failed', __( 'Failed to create document record', 'practicerx' ), array( 'status' => 500 ) );
		}
		
		$document = Document::get( $id );
		return Helper::format_response( $document, 201 );
	}
	
	/**
	 * Update document
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_item( $request ) {
		$document = Document::get( $request['id'] );
		
		if ( ! $document ) {
			return new WP_Error( 'document_not_found', __( 'Document not found', 'practicerx' ), array( 'status' => 404 ) );
		}
		
		$data = array();
		$fields = array( 'title', 'description', 'category', 'is_public' );
		
		foreach ( $fields as $field ) {
			if ( $request->has_param( $field ) ) {
				$data[ $field ] = $request->get_param( $field );
			}
		}
		
		$updated = Document::update( $request['id'], $data );
		
		if ( ! $updated ) {
			return new WP_Error( 'update_failed', __( 'Failed to update document', 'practicerx' ), array( 'status' => 500 ) );
		}
		
		$document = Document::get( $request['id'] );
		return Helper::format_response( $document );
	}
	
	/**
	 * Share document with clients
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function share_document( $request ) {
		$document = Document::get( $request['id'] );
		
		if ( ! $document ) {
			return new WP_Error( 'document_not_found', __( 'Document not found', 'practicerx' ), array( 'status' => 404 ) );
		}
		
		$client_ids = $request->get_param( 'client_ids' ) ?: array();
		
		if ( ! is_array( $client_ids ) ) {
			return new WP_Error( 'invalid_clients', __( 'Invalid client IDs', 'practicerx' ), array( 'status' => 400 ) );
		}
		
		$shared = Document::share_with_clients( $request['id'], $client_ids );
		
		if ( ! $shared ) {
			return new WP_Error( 'share_failed', __( 'Failed to share document', 'practicerx' ), array( 'status' => 500 ) );
		}
		
		return Helper::format_response( array( 'shared' => true, 'client_ids' => $client_ids ) );
	}
	
	/**
	 * Delete document
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_item( $request ) {
		$document = Document::get( $request['id'] );
		
		if ( ! $document ) {
			return new WP_Error( 'document_not_found', __( 'Document not found', 'practicerx' ), array( 'status' => 404 ) );
		}
		
		// Delete file from server
		$file_path = str_replace( wp_upload_dir()['baseurl'], wp_upload_dir()['basedir'], $document->file_path );
		if ( file_exists( $file_path ) ) {
			wp_delete_file( $file_path );
		}
		
		$deleted = Document::delete( $request['id'] );
		
		if ( ! $deleted ) {
			return new WP_Error( 'deletion_failed', __( 'Failed to delete document', 'practicerx' ), array( 'status' => 500 ) );
		}
		
		return Helper::format_response( array( 'deleted' => true ) );
	}
	
	/**
	 * Search documents
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function search_documents( $request ) {
		$term = $request->get_param( 'q' );
		$practitioner_id = $request->get_param( 'practitioner_id' ) ?: 0;
		
		if ( empty( $term ) ) {
			return new WP_Error( 'no_search_term', __( 'Search term required', 'practicerx' ), array( 'status' => 400 ) );
		}
		
		$documents = Document::search( $term, $practitioner_id );
		
		return Helper::format_response( $documents );
	}
	
	/**
	 * Get documents shared with client
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_client_documents( $request ) {
		$documents = Document::get_shared_with_client( $request['client_id'] );
		
		return Helper::format_response( $documents );
	}
}
