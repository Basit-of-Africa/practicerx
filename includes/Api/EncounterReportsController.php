<?php
namespace PracticeRx\Api;

use WP_REST_Server;
use PracticeRx\Models\EncounterReport;
use PracticeRx\Core\Constants;
use PracticeRx\Core\Helper;

/**
 * Class EncounterReportsController
 *
 * Manages detailed encounter reports for practitioners.
 */
class EncounterReportsController extends ApiController {

	/**
	 * Register routes.
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/encounter-reports', array(
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

		register_rest_route( $this->namespace, '/encounter-reports/(?P<id>\d+)', array(
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

		register_rest_route( $this->namespace, '/encounter-reports/(?P<id>\d+)/sign', array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'sign_report' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			),
		) );

		register_rest_route( $this->namespace, '/encounter-reports/client/(?P<client_id>\d+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_by_client' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			),
		) );

		register_rest_route( $this->namespace, '/encounter-reports/unsigned', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_unsigned' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			),
		) );
	}

	/**
	 * Get encounter reports.
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
			'orderby' => 'report_date',
			'order'   => 'DESC',
		);

		$items = EncounterReport::all( $args );
		$total = EncounterReport::count();

		$response = rest_ensure_response( $items );
		$response->header( 'X-WP-Total', $total );
		$response->header( 'X-WP-TotalPages', ceil( $total / $per_page ) );

		return $response;
	}

	/**
	 * Get single encounter report.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_item( $request ) {
		$id = $request->get_param( 'id' );
		$item = EncounterReport::get( $id );

		if ( ! $item ) {
			return new \WP_Error( 'not_found', __( 'Encounter report not found', 'practicerx' ), array( 'status' => 404 ) );
		}

		return rest_ensure_response( $item );
	}

	/**
	 * Get reports by client.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_by_client( $request ) {
		$client_id = $request->get_param( 'client_id' );
		$items = EncounterReport::get_by_client( $client_id );

		return rest_ensure_response( $items );
	}

	/**
	 * Get unsigned reports for current practitioner.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_unsigned( $request ) {
		$practitioner = \PracticeRx\Models\Practitioner::get_by_user_id( get_current_user_id() );
		if ( ! $practitioner ) {
			return rest_ensure_response( array() );
		}

		$items = EncounterReport::get_unsigned( $practitioner->id );
		return rest_ensure_response( $items );
	}

	/**
	 * Create encounter report.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function create_item( $request ) {
		$data = $request->get_json_params();

		// Validate required fields
		$required = array( 'encounter_id', 'client_id', 'practitioner_id' );
		foreach ( $required as $field ) {
			if ( empty( $data[ $field ] ) ) {
				return new \WP_Error( 'missing_field', sprintf( __( '%s is required', 'practicerx' ), $field ), array( 'status' => 400 ) );
			}
		}

		// Set defaults
		if ( empty( $data['report_date'] ) ) {
			$data['report_date'] = current_time( 'mysql' );
		}

		if ( empty( $data['status'] ) ) {
			$data['status'] = 'draft';
		}

		// Handle JSON fields
		if ( isset( $data['vitals'] ) && is_array( $data['vitals'] ) ) {
			$data['vitals'] = wp_json_encode( $data['vitals'] );
		}

		if ( isset( $data['custom_fields'] ) && is_array( $data['custom_fields'] ) ) {
			$data['custom_fields'] = wp_json_encode( $data['custom_fields'] );
		}

		$report_id = EncounterReport::create( $data );

		if ( ! $report_id ) {
			return new \WP_Error( 'create_failed', __( 'Failed to create encounter report', 'practicerx' ), array( 'status' => 500 ) );
		}

		do_action( 'ppms_after_encounter_report_created', $report_id, $data );

		return rest_ensure_response( Helper::format_response( true, EncounterReport::get( $report_id ), __( 'Encounter report created successfully', 'practicerx' ) ) );
	}

	/**
	 * Update encounter report.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function update_item( $request ) {
		$id = $request->get_param( 'id' );
		$data = $request->get_json_params();

		$report = EncounterReport::get( $id );
		if ( ! $report ) {
			return new \WP_Error( 'not_found', __( 'Encounter report not found', 'practicerx' ), array( 'status' => 404 ) );
		}

		// Cannot edit signed reports
		if ( 'signed' === $report->status ) {
			return new \WP_Error( 'report_signed', __( 'Cannot edit a signed report', 'practicerx' ), array( 'status' => 400 ) );
		}

		// Handle JSON fields
		if ( isset( $data['vitals'] ) && is_array( $data['vitals'] ) ) {
			$data['vitals'] = wp_json_encode( $data['vitals'] );
		}

		if ( isset( $data['custom_fields'] ) && is_array( $data['custom_fields'] ) ) {
			$data['custom_fields'] = wp_json_encode( $data['custom_fields'] );
		}

		$updated = EncounterReport::update( $id, $data );

		if ( false === $updated ) {
			return new \WP_Error( 'update_failed', __( 'Failed to update encounter report', 'practicerx' ), array( 'status' => 500 ) );
		}

		do_action( 'ppms_after_encounter_report_updated', $id, $data );

		return rest_ensure_response( Helper::format_response( true, EncounterReport::get( $id ), __( 'Encounter report updated successfully', 'practicerx' ) ) );
	}

	/**
	 * Sign encounter report.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function sign_report( $request ) {
		$id = $request->get_param( 'id' );

		$report = EncounterReport::get( $id );
		if ( ! $report ) {
			return new \WP_Error( 'not_found', __( 'Encounter report not found', 'practicerx' ), array( 'status' => 404 ) );
		}

		if ( 'signed' === $report->status ) {
			return new \WP_Error( 'already_signed', __( 'Report is already signed', 'practicerx' ), array( 'status' => 400 ) );
		}

		$practitioner = \PracticeRx\Models\Practitioner::get_by_user_id( get_current_user_id() );
		if ( ! $practitioner ) {
			return new \WP_Error( 'not_practitioner', __( 'Only practitioners can sign reports', 'practicerx' ), array( 'status' => 403 ) );
		}

		$signed = EncounterReport::sign_report( $id, $practitioner->id );

		if ( ! $signed ) {
			return new \WP_Error( 'sign_failed', __( 'Failed to sign report', 'practicerx' ), array( 'status' => 500 ) );
		}

		do_action( 'ppms_after_encounter_report_signed', $id, $practitioner->id );

		return rest_ensure_response( Helper::format_response( true, EncounterReport::get( $id ), __( 'Report signed successfully', 'practicerx' ) ) );
	}

	/**
	 * Delete encounter report.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function delete_item( $request ) {
		$id = $request->get_param( 'id' );

		$report = EncounterReport::get( $id );
		if ( ! $report ) {
			return new \WP_Error( 'not_found', __( 'Encounter report not found', 'practicerx' ), array( 'status' => 404 ) );
		}

		// Cannot delete signed reports
		if ( 'signed' === $report->status ) {
			return new \WP_Error( 'report_signed', __( 'Cannot delete a signed report', 'practicerx' ), array( 'status' => 400 ) );
		}

		$deleted = EncounterReport::delete( $id );

		if ( ! $deleted ) {
			return new \WP_Error( 'delete_failed', __( 'Failed to delete encounter report', 'practicerx' ), array( 'status' => 500 ) );
		}

		do_action( 'ppms_after_encounter_report_deleted', $id );

		return rest_ensure_response( Helper::format_response( true, array(), __( 'Encounter report deleted successfully', 'practicerx' ) ) );
	}
}
