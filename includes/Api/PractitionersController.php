<?php
namespace PracticeRx\Api;

use WP_REST_Server;
use PracticeRx\Models\Practitioner;
use PracticeRx\Core\Constants;
use PracticeRx\Core\Helper;

/**
 * Class PractitionersController
 */
class PractitionersController extends ApiController {

	/**
	 * Register routes.
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/practitioners', array(
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

		register_rest_route( $this->namespace, '/practitioners/(?P<id>\d+)', array(
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
	}

	/**
	 * Get practitioners.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_items( $request ) {
		$page     = $request->get_param( 'page' ) ? absint( $request->get_param( 'page' ) ) : 1;
		$per_page = $request->get_param( 'per_page' ) ? absint( $request->get_param( 'per_page' ) ) : 20;

		$args = array(
			'limit'   => $per_page,
			'offset'  => ( $page - 1 ) * $per_page,
			'orderby' => 'created_at',
			'order'   => 'DESC',
		);

		// Apply filters
		$args = apply_filters( 'ppms_practitioner_query_args', $args, $request );

		$items = Practitioner::all( $args );
		$total = Practitioner::count();

		$response = rest_ensure_response( $items );
		$response->header( 'X-WP-Total', $total );
		$response->header( 'X-WP-TotalPages', ceil( $total / $per_page ) );

		return $response;
	}

	/**
	 * Get single practitioner.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_item( $request ) {
		$id = $request->get_param( 'id' );
		$item = Practitioner::get( $id );

		if ( ! $item ) {
			return new \WP_Error( 'not_found', __( 'Practitioner not found', 'practicerx' ), array( 'status' => 404 ) );
		}

		return rest_ensure_response( $item );
	}

	/**
	 * Create practitioner.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function create_item( $request ) {
		$data = $request->get_json_params();

		// Validate required fields
		$required = array( 'user_id', 'specialty' );
		foreach ( $required as $field ) {
			if ( empty( $data[ $field ] ) ) {
				return new \WP_Error( 'missing_field', sprintf( __( '%s is required', 'practicerx' ), $field ), array( 'status' => 400 ) );
			}
		}

		// Check if user exists
		$user = get_userdata( $data['user_id'] );
		if ( ! $user ) {
			return new \WP_Error( 'invalid_user', __( 'Invalid user ID', 'practicerx' ), array( 'status' => 400 ) );
		}

		// Check if practitioner already exists for this user
		$existing = Practitioner::get_by_user_id( $data['user_id'] );
		if ( $existing ) {
			return new \WP_Error( 'already_exists', __( 'Practitioner already exists for this user', 'practicerx' ), array( 'status' => 400 ) );
		}

		// Apply filter before saving
		$data = apply_filters( 'ppms_before_practitioner_save', $data );

		$practitioner_id = Practitioner::create( $data );

		if ( ! $practitioner_id ) {
			return new \WP_Error( 'create_failed', __( 'Failed to create practitioner', 'practicerx' ), array( 'status' => 500 ) );
		}

		do_action( 'ppms_after_practitioner_created', $practitioner_id, $data );

		return rest_ensure_response( Helper::format_response( true, Practitioner::get( $practitioner_id ), __( 'Practitioner created successfully', 'practicerx' ) ) );
	}

	/**
	 * Update practitioner.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function update_item( $request ) {
		$id = $request->get_param( 'id' );
		$data = $request->get_json_params();

		$practitioner = Practitioner::get( $id );
		if ( ! $practitioner ) {
			return new \WP_Error( 'not_found', __( 'Practitioner not found', 'practicerx' ), array( 'status' => 404 ) );
		}

		// Apply filter before saving
		$data = apply_filters( 'ppms_before_practitioner_save', $data );

		$updated = Practitioner::update( $id, $data );

		if ( false === $updated ) {
			return new \WP_Error( 'update_failed', __( 'Failed to update practitioner', 'practicerx' ), array( 'status' => 500 ) );
		}

		do_action( 'ppms_after_practitioner_updated', $id, $data );

		return rest_ensure_response( Helper::format_response( true, Practitioner::get( $id ), __( 'Practitioner updated successfully', 'practicerx' ) ) );
	}

	/**
	 * Delete practitioner.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function delete_item( $request ) {
		$id = $request->get_param( 'id' );

		$practitioner = Practitioner::get( $id );
		if ( ! $practitioner ) {
			return new \WP_Error( 'not_found', __( 'Practitioner not found', 'practicerx' ), array( 'status' => 404 ) );
		}

		$deleted = Practitioner::delete( $id );

		if ( ! $deleted ) {
			return new \WP_Error( 'delete_failed', __( 'Failed to delete practitioner', 'practicerx' ), array( 'status' => 500 ) );
		}

		do_action( 'ppms_after_practitioner_deleted', $id );

		return rest_ensure_response( Helper::format_response( true, array(), __( 'Practitioner deleted successfully', 'practicerx' ) ) );
	}

	/**
	 * Check list permissions.
	 */
	public function check_list_permissions( $request ) {
		return ppms_user_can( Constants::CAP_PRACTITIONER_LIST );
	}

	/**
	 * Check view permissions.
	 */
	public function check_view_permissions( $request ) {
		return ppms_user_can( Constants::CAP_PRACTITIONER_VIEW );
	}

	/**
	 * Check create permissions.
	 */
	public function check_create_permissions( $request ) {
		return ppms_user_can( Constants::CAP_PRACTITIONER_ADD );
	}

	/**
	 * Check edit permissions.
	 */
	public function check_edit_permissions( $request ) {
		return ppms_user_can( Constants::CAP_PRACTITIONER_EDIT );
	}

	/**
	 * Check delete permissions.
	 */
	public function check_delete_permissions( $request ) {
		return ppms_user_can( Constants::CAP_PRACTITIONER_DELETE );
	}
}
