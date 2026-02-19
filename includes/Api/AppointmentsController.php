<?php
namespace PracticeRx\Api;

use WP_REST_Server;
use PracticeRx\Models\Appointment;
use PracticeRx\Core\Constants;
use PracticeRx\Core\Helper;

/**
 * Class AppointmentsController
 */
class AppointmentsController extends ApiController {

	/**
	 * Register routes.
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/appointments/client', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_client_items' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			),
		) );

		register_rest_route( $this->namespace, '/appointments', array(
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

		register_rest_route( $this->namespace, '/appointments/(?P<id>\d+)', array(
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
	 * Get appointments for the current authenticated client user.
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_client_items( $request ) {
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return new \WP_Error( 'not_authenticated', __( 'Not authenticated', 'practicerx' ), array( 'status' => 401 ) );
		}

		$client = \PracticeRx\Models\Client::get_by_user_id( $user_id );
		if ( ! $client ) {
			return rest_ensure_response( array() );
		}

		// Optional date range
		$start_date = $request->get_param( 'start_date' );
		$end_date = $request->get_param( 'end_date' );

		if ( $start_date && $end_date ) {
			$items = \PracticeRx\Models\Appointment::get_by_range( $start_date, $end_date );
		} else {
			$items = \PracticeRx\Models\Appointment::get_by_patient( $client->id );
		}

		return rest_ensure_response( $items );
	}

	/**
	 * Get appointments.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_items( $request ) {
		$start_date = $request->get_param( 'start_date' );
		$end_date   = $request->get_param( 'end_date' );
		
		if ( $start_date && $end_date ) {
			$items = Appointment::get_by_range( $start_date, $end_date );
		} else {
			$page     = $request->get_param( 'page' ) ? absint( $request->get_param( 'page' ) ) : 1;
			$per_page = $request->get_param( 'per_page' ) ? absint( $request->get_param( 'per_page' ) ) : 20;

			$args = array(
				'limit'   => $per_page,
				'offset'  => ( $page - 1 ) * $per_page,
				'orderby' => 'start_time',
				'order'   => 'DESC',
			);

			// Apply filters
			$args = apply_filters( 'ppms_appointment_query_args', $args, 'list' );

			$items = Appointment::all( $args );
		}

		return rest_ensure_response( $items );
	}

	/**
	 * Get single appointment.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_item( $request ) {
		$id = $request->get_param( 'id' );
		$item = Appointment::get( $id );

		if ( ! $item ) {
			return new \WP_Error( 'not_found', __( 'Appointment not found', 'practicerx' ), array( 'status' => 404 ) );
		}

		return rest_ensure_response( $item );
	}

	/**
	 * Create appointment.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function create_item( $request ) {
		$data = $request->get_json_params();
		
		// Validate data
		$validated = Helper::validate_appointment_data( $data );

		if ( is_wp_error( $validated ) ) {
			return $validated;
		}

		// Check time slot availability
		$is_available = apply_filters( 'ppms_check_appointment_availability', true, $validated['practitioner_id'], array(
			'start_time' => $validated['start_time'],
			'end_time'   => $validated['end_time'],
		) );

		if ( ! $is_available ) {
			return new \WP_Error( 'time_slot_unavailable', __( 'This time slot is not available', 'practicerx' ), array( 'status' => 400 ) );
		}

		// Apply filter before save
		$validated = apply_filters( 'ppms_before_appointment_save', $validated, 'create' );

		$appointment_id = Appointment::create( $validated );

		if ( ! $appointment_id ) {
			return new \WP_Error( 'create_failed', __( 'Failed to create appointment', 'practicerx' ), array( 'status' => 500 ) );
		}

		do_action( 'ppms_after_appointment_created', $appointment_id, $validated );

		return rest_ensure_response( Helper::format_response( true, Appointment::get( $appointment_id ), __( 'Appointment created successfully', 'practicerx' ) ) );
	}

	/**
	 * Update appointment.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function update_item( $request ) {
		$id = $request->get_param( 'id' );
		$data = $request->get_json_params();

		$appointment = Appointment::get( $id );
		if ( ! $appointment ) {
			return new \WP_Error( 'not_found', __( 'Appointment not found', 'practicerx' ), array( 'status' => 404 ) );
		}

		// Validate data
		$validated = Helper::validate_appointment_data( $data );

		if ( is_wp_error( $validated ) ) {
			return $validated;
		}

		// Check time slot availability (exclude current appointment)
		if ( isset( $validated['start_time'] ) && isset( $validated['end_time'] ) ) {
			$is_available = apply_filters( 'ppms_check_appointment_availability', true, $validated['practitioner_id'], array(
				'start_time'              => $validated['start_time'],
				'end_time'                => $validated['end_time'],
				'exclude_appointment_id'  => $id,
			) );

			if ( ! $is_available ) {
				return new \WP_Error( 'time_slot_unavailable', __( 'This time slot is not available', 'practicerx' ), array( 'status' => 400 ) );
			}
		}

		// Apply filter before save
		$validated = apply_filters( 'ppms_before_appointment_save', $validated, 'update' );

		$updated = Appointment::update( $id, $validated );

		if ( false === $updated ) {
			return new \WP_Error( 'update_failed', __( 'Failed to update appointment', 'practicerx' ), array( 'status' => 500 ) );
		}

		do_action( 'ppms_after_appointment_updated', $id, $validated );

		return rest_ensure_response( Helper::format_response( true, Appointment::get( $id ), __( 'Appointment updated successfully', 'practicerx' ) ) );
	}

	/**
	 * Delete appointment.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function delete_item( $request ) {
		$id = $request->get_param( 'id' );

		$appointment = Appointment::get( $id );
		if ( ! $appointment ) {
			return new \WP_Error( 'not_found', __( 'Appointment not found', 'practicerx' ), array( 'status' => 404 ) );
		}

		$deleted = Appointment::delete( $id );

		if ( ! $deleted ) {
			return new \WP_Error( 'delete_failed', __( 'Failed to delete appointment', 'practicerx' ), array( 'status' => 500 ) );
		}

		do_action( 'ppms_after_appointment_deleted', $id );

		return rest_ensure_response( Helper::format_response( true, array(), __( 'Appointment deleted successfully', 'practicerx' ) ) );
	}

	/**
	 * Check list permissions.
	 */
	public function check_list_permissions( $request ) {
		return ppms_user_can( Constants::CAP_APPOINTMENT_LIST );
	}

	/**
	 * Check view permissions.
	 */
	public function check_view_permissions( $request ) {
		return ppms_user_can( Constants::CAP_APPOINTMENT_VIEW );
	}

	/**
	 * Check create permissions.
	 */
	public function check_create_permissions( $request ) {
		return ppms_user_can( Constants::CAP_APPOINTMENT_ADD );
	}

	/**
	 * Check edit permissions.
	 */
	public function check_edit_permissions( $request ) {
		return ppms_user_can( Constants::CAP_APPOINTMENT_EDIT );
	}

	 /**
	  * Check delete permissions.
	  */
	public function check_delete_permissions( $request ) {
		return ppms_user_can( Constants::CAP_APPOINTMENT_DELETE );
	}
}
