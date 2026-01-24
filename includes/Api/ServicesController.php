<?php
namespace PracticeRx\Api;

use WP_REST_Server;
use PracticeRx\Models\Service;
use PracticeRx\Core\Constants;
use PracticeRx\Core\Helper;

/**
 * Class ServicesController
 */
class ServicesController extends ApiController {

	/**
	 * Register routes.
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/services', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_item' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			),
		) );

		register_rest_route( $this->namespace, '/services/(?P<id>\d+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_item' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_item' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			),
		) );

		register_rest_route( $this->namespace, '/services/active', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_active' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			),
		) );

		register_rest_route( $this->namespace, '/services/practitioner/(?P<practitioner_id>\d+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_by_practitioner' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			),
		) );
	}

	/**
	 * Get services.
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
			'orderby' => 'name',
			'order'   => 'ASC',
		);

		$items = Service::all( $args );
		$total = Service::count();

		$response = rest_ensure_response( $items );
		$response->header( 'X-WP-Total', $total );
		$response->header( 'X-WP-TotalPages', ceil( $total / $per_page ) );

		return $response;
	}

	/**
	 * Get active services.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_active( $request ) {
		$items = Service::get_active();
		return rest_ensure_response( $items );
	}

	/**
	 * Get services by practitioner.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_by_practitioner( $request ) {
		$practitioner_id = $request->get_param( 'practitioner_id' );
		$items = Service::get_by_practitioner( $practitioner_id );

		return rest_ensure_response( $items );
	}

	/**
	 * Get single service.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_item( $request ) {
		$id = $request->get_param( 'id' );
		$item = Service::get( $id );

		if ( ! $item ) {
			return new \WP_Error( 'not_found', __( 'Service not found', 'practicerx' ), array( 'status' => 404 ) );
		}

		return rest_ensure_response( $item );
	}

	/**
	 * Create service.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function create_item( $request ) {
		$data = $request->get_json_params();

		// Validate required fields
		if ( empty( $data['name'] ) ) {
			return new \WP_Error( 'missing_field', __( 'Service name is required', 'practicerx' ), array( 'status' => 400 ) );
		}

		// Set defaults
		if ( ! isset( $data['duration_minutes'] ) ) {
			$data['duration_minutes'] = 30;
		}

		if ( ! isset( $data['price'] ) ) {
			$data['price'] = 0;
		}

		if ( empty( $data['currency'] ) ) {
			$data['currency'] = ppms_get_option( 'currency', 'USD' );
		}

		if ( ! isset( $data['is_active'] ) ) {
			$data['is_active'] = 1;
		}

		$service_id = Service::create( $data );

		if ( ! $service_id ) {
			return new \WP_Error( 'create_failed', __( 'Failed to create service', 'practicerx' ), array( 'status' => 500 ) );
		}

		return rest_ensure_response( Helper::format_response( true, Service::get( $service_id ), __( 'Service created successfully', 'practicerx' ) ) );
	}

	/**
	 * Update service.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function update_item( $request ) {
		$id = $request->get_param( 'id' );
		$data = $request->get_json_params();

		$service = Service::get( $id );
		if ( ! $service ) {
			return new \WP_Error( 'not_found', __( 'Service not found', 'practicerx' ), array( 'status' => 404 ) );
		}

		$updated = Service::update( $id, $data );

		if ( false === $updated ) {
			return new \WP_Error( 'update_failed', __( 'Failed to update service', 'practicerx' ), array( 'status' => 500 ) );
		}

		return rest_ensure_response( Helper::format_response( true, Service::get( $id ), __( 'Service updated successfully', 'practicerx' ) ) );
	}

	/**
	 * Delete service.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function delete_item( $request ) {
		$id = $request->get_param( 'id' );

		$service = Service::get( $id );
		if ( ! $service ) {
			return new \WP_Error( 'not_found', __( 'Service not found', 'practicerx' ), array( 'status' => 404 ) );
		}

		$deleted = Service::delete( $id );

		if ( ! $deleted ) {
			return new \WP_Error( 'delete_failed', __( 'Failed to delete service', 'practicerx' ), array( 'status' => 500 ) );
		}

		return rest_ensure_response( Helper::format_response( true, array(), __( 'Service deleted successfully', 'practicerx' ) ) );
	}
}
