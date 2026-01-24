<?php
namespace PracticeRx\Api;

use WP_REST_Server;
use PracticeRx\Models\Patient;
use PracticeRx\Core\Constants;
use PracticeRx\Core\Helper;

/**
 * Class PatientsController
 */
class PatientsController extends ApiController {

	/**
	 * Register routes.
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/patients', array(
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

		register_rest_route( $this->namespace, '/patients/(?P<id>\d+)', array(
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
	 * Get patients.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_items( $request ) {
		$page     = $request->get_param( 'page' ) ? absint( $request->get_param( 'page' ) ) : 1;
		$per_page = $request->get_param( 'per_page' ) ? absint( $request->get_param( 'per_page' ) ) : 20;
		$search   = $request->get_param( 'search' );

		$args = array(
			'limit'   => $per_page,
			'offset'  => ( $page - 1 ) * $per_page,
			'orderby' => 'id',
			'order'   => 'DESC',
		);

		// Apply filters
		$args = apply_filters( 'ppms_patient_query_args', $args, 'list' );

		$items = Patient::all( $args );
		$total = Patient::count();

		$response = rest_ensure_response( $items );
		$response->header( 'X-WP-Total', $total );
		$response->header( 'X-WP-TotalPages', ceil( $total / $per_page ) );

		return $response;
	}

	/**
	 * Get single patient.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_item( $request ) {
		$id = $request->get_param( 'id' );
		$item = Patient::get( $id );

		if ( ! $item ) {
			return new \WP_Error( 'not_found', __( 'Patient not found', 'practicerx' ), array( 'status' => 404 ) );
		}

		$item = apply_filters( 'ppms_patient_data', $item, $id );

		return rest_ensure_response( $item );
	}

	/**
	 * Create patient.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function create_item( $request ) {
		$data = $request->get_json_params();

		// Validate data
		$validated = Helper::validate_patient_data( $data );

		if ( is_wp_error( $validated ) ) {
			return $validated;
		}

		// Apply filter before save
		$validated = apply_filters( 'ppms_before_patient_save', $validated, 'create' );

		$patient_id = Patient::create( $validated );

		if ( ! $patient_id ) {
			return new \WP_Error( 'create_failed', __( 'Failed to create patient', 'practicerx' ), array( 'status' => 500 ) );
		}

		do_action( 'ppms_after_patient_created', $patient_id, $validated );

		return rest_ensure_response( Helper::format_response( true, Patient::get( $patient_id ), __( 'Patient created successfully', 'practicerx' ) ) );
	}

	/**
	 * Update patient.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function update_item( $request ) {
		$id = $request->get_param( 'id' );
		$data = $request->get_json_params();

		$patient = Patient::get( $id );
		if ( ! $patient ) {
			return new \WP_Error( 'not_found', __( 'Patient not found', 'practicerx' ), array( 'status' => 404 ) );
		}

		// Validate data
		$validated = Helper::validate_patient_data( $data );

		if ( is_wp_error( $validated ) ) {
			return $validated;
		}

		// Apply filter before save
		$validated = apply_filters( 'ppms_before_patient_save', $validated, 'update' );

		$updated = Patient::update( $id, $validated );

		if ( false === $updated ) {
			return new \WP_Error( 'update_failed', __( 'Failed to update patient', 'practicerx' ), array( 'status' => 500 ) );
		}

		do_action( 'ppms_after_patient_updated', $id, $validated );

		return rest_ensure_response( Helper::format_response( true, Patient::get( $id ), __( 'Patient updated successfully', 'practicerx' ) ) );
	}

	/**
	 * Delete patient.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function delete_item( $request ) {
		$id = $request->get_param( 'id' );

		$patient = Patient::get( $id );
		if ( ! $patient ) {
			return new \WP_Error( 'not_found', __( 'Patient not found', 'practicerx' ), array( 'status' => 404 ) );
		}

		$deleted = Patient::delete( $id );

		if ( ! $deleted ) {
			return new \WP_Error( 'delete_failed', __( 'Failed to delete patient', 'practicerx' ), array( 'status' => 500 ) );
		}

		do_action( 'ppms_after_patient_deleted', $id );

		return rest_ensure_response( Helper::format_response( true, array(), __( 'Patient deleted successfully', 'practicerx' ) ) );
	}

	/**
	 * Check list permissions.
	 */
	public function check_list_permissions( $request ) {
		return ppms_user_can( Constants::CAP_PATIENT_LIST );
	}

	/**
	 * Check view permissions.
	 */
	public function check_view_permissions( $request ) {
		return ppms_user_can( Constants::CAP_PATIENT_VIEW );
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
