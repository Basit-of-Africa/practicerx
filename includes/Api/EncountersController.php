<?php
namespace PracticeRx\Api;

use WP_REST_Server;
use PracticeRx\Models\Encounter;
use PracticeRx\Core\Constants;
use PracticeRx\Core\Helper;

/**
 * Class EncountersController
 */
class EncountersController extends ApiController {

	/**
	 * Register routes.
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/encounters', array(
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

		register_rest_route( $this->namespace, '/encounters/(?P<id>\d+)', array(
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

		register_rest_route( $this->namespace, '/patients/(?P<patient_id>\d+)/encounters', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items_for_patient' ),
				'permission_callback' => array( $this, 'check_list_permissions' ),
			),
		) );
	}

	/**
	 * Get encounters.
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
			'orderby' => 'encounter_date',
			'order'   => 'DESC',
		);

		$items = Encounter::all( $args );
		$total = Encounter::count();

		$response = rest_ensure_response( $items );
		$response->header( 'X-WP-Total', $total );
		$response->header( 'X-WP-TotalPages', ceil( $total / $per_page ) );

		return $response;
	}

	/**
	 * Get single encounter.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_item( $request ) {
		$id = $request->get_param( 'id' );
		$item = Encounter::get( $id );

		if ( ! $item ) {
			return new \WP_Error( 'not_found', __( 'Encounter not found', 'practicerx' ), array( 'status' => 404 ) );
		}

		return rest_ensure_response( $item );
	}

	/**
	 * Get encounters for a patient.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_items_for_patient( $request ) {
		$patient_id = $request->get_param( 'patient_id' );
		$items = Encounter::get_by_patient( $patient_id );

		return rest_ensure_response( $items );
	}

	/**
	 * Create encounter.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function create_item( $request ) {
		$data = $request->get_json_params();

		// Validate required fields
		$required = array( 'patient_id', 'practitioner_id', 'content' );
		foreach ( $required as $field ) {
			if ( empty( $data[ $field ] ) ) {
				return new \WP_Error( 'missing_field', sprintf( __( '%s is required', 'practicerx' ), $field ), array( 'status' => 400 ) );
			}
		}

		// Set defaults
		if ( empty( $data['encounter_date'] ) ) {
			$data['encounter_date'] = current_time( 'mysql' );
		}

		if ( empty( $data['status'] ) ) {
			$data['status'] = Constants::ENCOUNTER_STATUS_COMPLETED;
		}

		// Apply filter before saving
		$data = apply_filters( 'ppms_before_encounter_save', $data );

		$encounter_id = Encounter::create( $data );

		if ( ! $encounter_id ) {
			return new \WP_Error( 'create_failed', __( 'Failed to create encounter', 'practicerx' ), array( 'status' => 500 ) );
		}

		do_action( 'ppms_after_encounter_created', $encounter_id, $data );

		return rest_ensure_response( Helper::format_response( true, Encounter::get( $encounter_id ), __( 'Encounter created successfully', 'practicerx' ) ) );
	}

	/**
	 * Update encounter.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function update_item( $request ) {
		$id = $request->get_param( 'id' );
		$data = $request->get_json_params();

		$encounter = Encounter::get( $id );
		if ( ! $encounter ) {
			return new \WP_Error( 'not_found', __( 'Encounter not found', 'practicerx' ), array( 'status' => 404 ) );
		}

		// Apply filter before saving
		$data = apply_filters( 'ppms_before_encounter_save', $data );

		$updated = Encounter::update( $id, $data );

		if ( false === $updated ) {
			return new \WP_Error( 'update_failed', __( 'Failed to update encounter', 'practicerx' ), array( 'status' => 500 ) );
		}

		do_action( 'ppms_after_encounter_updated', $id, $data );

		return rest_ensure_response( Helper::format_response( true, Encounter::get( $id ), __( 'Encounter updated successfully', 'practicerx' ) ) );
	}

	/**
	 * Delete encounter.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function delete_item( $request ) {
		$id = $request->get_param( 'id' );

		$encounter = Encounter::get( $id );
		if ( ! $encounter ) {
			return new \WP_Error( 'not_found', __( 'Encounter not found', 'practicerx' ), array( 'status' => 404 ) );
		}

		$deleted = Encounter::delete( $id );

		if ( ! $deleted ) {
			return new \WP_Error( 'delete_failed', __( 'Failed to delete encounter', 'practicerx' ), array( 'status' => 500 ) );
		}

		do_action( 'ppms_after_encounter_deleted', $id );

		return rest_ensure_response( Helper::format_response( true, array(), __( 'Encounter deleted successfully', 'practicerx' ) ) );
	}

	/**
	 * Check list permissions.
	 */
	public function check_list_permissions( $request ) {
		return ppms_user_can( Constants::CAP_ENCOUNTER_LIST );
	}

	/**
	 * Check view permissions.
	 */
	public function check_view_permissions( $request ) {
		return ppms_user_can( Constants::CAP_ENCOUNTER_VIEW );
	}

	/**
	 * Check create permissions.
	 */
	public function check_create_permissions( $request ) {
		return ppms_user_can( Constants::CAP_ENCOUNTER_ADD );
	}

	/**
	 * Check edit permissions.
	 */
	public function check_edit_permissions( $request ) {
		return ppms_user_can( Constants::CAP_ENCOUNTER_EDIT );
	}

	/**
	 * Check delete permissions.
	 */
	public function check_delete_permissions( $request ) {
		return ppms_user_can( Constants::CAP_ENCOUNTER_DELETE );
	}
}
