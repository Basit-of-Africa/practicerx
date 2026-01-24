<?php
namespace PracticeRx\Api;

use WP_REST_Server;
use PracticeRx\Models\Client;
use PracticeRx\Core\Constants;
use PracticeRx\Core\Helper;

/**
 * Class ClientsController
 *
 * Manages clients (renamed from patients for health professionals).
 */
class ClientsController extends ApiController {

	/**
	 * Register routes.
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/clients', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'check_list_permissions' ),
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_item' ),
				'permission_callback' => array( $this, 'check_create_permissions' ),
			),
		) );

		register_rest_route( $this->namespace, '/clients/(?P<id>\d+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'check_view_permissions' ),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_item' ),
				'permission_callback' => array( $this, 'check_edit_permissions' ),
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_item' ),
				'permission_callback' => array( $this, 'check_delete_permissions' ),
			),
		) );

		register_rest_route( $this->namespace, '/clients/search', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'search_items' ),
				'permission_callback' => array( $this, 'check_list_permissions' ),
			),
		) );
	}

	/**
	 * Get clients.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_items( $request ) {
		$page     = $request->get_param( 'page' ) ? absint( $request->get_param( 'page' ) ) : 1;
		$per_page = $request->get_param( 'per_page' ) ? absint( $request->get_param( 'per_page' ) ) : 20;
		$status   = $request->get_param( 'status' );

		$args = array(
			'limit'   => $per_page,
			'offset'  => ( $page - 1 ) * $per_page,
			'orderby' => 'created_at',
			'order'   => 'DESC',
		);

		// Apply filters
		$args = apply_filters( 'ppms_client_query_args', $args, $request );

		if ( $status ) {
			$items = Client::get_by_status( $status );
			$total = count( $items );
		} else {
			$items = Client::all( $args );
			$total = Client::count();
		}

		$response = rest_ensure_response( $items );
		$response->header( 'X-WP-Total', $total );
		$response->header( 'X-WP-TotalPages', ceil( $total / $per_page ) );

		return $response;
	}

	/**
	 * Search clients.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function search_items( $request ) {
		$search_term = $request->get_param( 's' );
		
		if ( empty( $search_term ) ) {
			return rest_ensure_response( array() );
		}

		$items = Client::search( $search_term );
		return rest_ensure_response( $items );
	}

	/**
	 * Get single client.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_item( $request ) {
		$id = $request->get_param( 'id' );
		$item = Client::get_with_user( $id );

		if ( ! $item ) {
			return new \WP_Error( 'not_found', __( 'Client not found', 'practicerx' ), array( 'status' => 404 ) );
		}

		return rest_ensure_response( $item );
	}

	/**
	 * Create client.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function create_item( $request ) {
		$data = $request->get_json_params();

		// Validate required fields
		$required = array( 'email', 'first_name', 'last_name' );
		foreach ( $required as $field ) {
			if ( empty( $data[ $field ] ) ) {
				return new \WP_Error( 'missing_field', sprintf( __( '%s is required', 'practicerx' ), $field ), array( 'status' => 400 ) );
			}
		}

		// Validate email
		if ( ! is_email( $data['email'] ) ) {
			return new \WP_Error( 'invalid_email', __( 'Invalid email address', 'practicerx' ), array( 'status' => 400 ) );
		}

		// Check if email already exists
		if ( email_exists( $data['email'] ) ) {
			$user = get_user_by( 'email', $data['email'] );
			$existing = Client::get_by_user_id( $user->ID );
			if ( $existing ) {
				return new \WP_Error( 'email_exists', __( 'A client with this email already exists', 'practicerx' ), array( 'status' => 400 ) );
			}
		}

		// Create WordPress user if user_id not provided
		if ( empty( $data['user_id'] ) ) {
			$user_id = wp_create_user(
				sanitize_email( $data['email'] ),
				wp_generate_password(),
				sanitize_email( $data['email'] )
			);

			if ( is_wp_error( $user_id ) ) {
				return $user_id;
			}

			wp_update_user( array(
				'ID'           => $user_id,
				'first_name'   => sanitize_text_field( $data['first_name'] ),
				'last_name'    => sanitize_text_field( $data['last_name'] ),
				'display_name' => sanitize_text_field( $data['first_name'] . ' ' . $data['last_name'] ),
			) );

			// Assign client role
			$user = new \WP_User( $user_id );
			$user->set_role( 'ppms_client' );

			$data['user_id'] = $user_id;
		}

		// Set default status
		if ( empty( $data['status'] ) ) {
			$data['status'] = 'active';
		}

		// Apply filter before saving
		$data = apply_filters( 'ppms_before_client_save', $data );

		$client_id = Client::create( $data );

		if ( ! $client_id ) {
			return new \WP_Error( 'create_failed', __( 'Failed to create client', 'practicerx' ), array( 'status' => 500 ) );
		}

		do_action( 'ppms_after_client_created', $client_id, $data );

		return rest_ensure_response( Helper::format_response( true, Client::get( $client_id ), __( 'Client created successfully', 'practicerx' ) ) );
	}

	/**
	 * Update client.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function update_item( $request ) {
		$id = $request->get_param( 'id' );
		$data = $request->get_json_params();

		$client = Client::get( $id );
		if ( ! $client ) {
			return new \WP_Error( 'not_found', __( 'Client not found', 'practicerx' ), array( 'status' => 404 ) );
		}

		// Apply filter before saving
		$data = apply_filters( 'ppms_before_client_save', $data );

		$updated = Client::update( $id, $data );

		if ( false === $updated ) {
			return new \WP_Error( 'update_failed', __( 'Failed to update client', 'practicerx' ), array( 'status' => 500 ) );
		}

		do_action( 'ppms_after_client_updated', $id, $data );

		return rest_ensure_response( Helper::format_response( true, Client::get( $id ), __( 'Client updated successfully', 'practicerx' ) ) );
	}

	/**
	 * Delete client.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function delete_item( $request ) {
		$id = $request->get_param( 'id' );

		$client = Client::get( $id );
		if ( ! $client ) {
			return new \WP_Error( 'not_found', __( 'Client not found', 'practicerx' ), array( 'status' => 404 ) );
		}

		$deleted = Client::delete( $id );

		if ( ! $deleted ) {
			return new \WP_Error( 'delete_failed', __( 'Failed to delete client', 'practicerx' ), array( 'status' => 500 ) );
		}

		do_action( 'ppms_after_client_deleted', $id );

		return rest_ensure_response( Helper::format_response( true, array(), __( 'Client deleted successfully', 'practicerx' ) ) );
	}

	/**
	 * Check list permissions.
	 */
	public function check_list_permissions( $request ) {
		return current_user_can( 'read' );
	}

	/**
	 * Check view permissions.
	 */
	public function check_view_permissions( $request ) {
		return current_user_can( 'read' );
	}

	/**
	 * Check create permissions.
	 */
	public function check_create_permissions( $request ) {
		return ppms_user_can( Constants::CAP_PATIENT_ADD );
	}

	/**
	 * Check edit permissions.
	 */
	public function check_edit_permissions( $request ) {
		return ppms_user_can( Constants::CAP_PATIENT_EDIT );
	}

	/**
	 * Check delete permissions.
	 */
	public function check_delete_permissions( $request ) {
		return ppms_user_can( Constants::CAP_PATIENT_DELETE );
	}
}
