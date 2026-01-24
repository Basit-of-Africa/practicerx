<?php
namespace PracticeRx\Api;

use WP_REST_Server;
use PracticeRx\Models\Invoice;
use PracticeRx\Core\Constants;
use PracticeRx\Core\Helper;

/**
 * Class InvoicesController
 */
class InvoicesController extends ApiController {

	/**
	 * Register routes.
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/invoices', array(
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

		register_rest_route( $this->namespace, '/invoices/(?P<id>\d+)', array(
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

		register_rest_route( $this->namespace, '/invoices/patient/(?P<patient_id>\d+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_by_patient' ),
				'permission_callback' => array( $this, 'check_view_permissions' ),
			),
		) );
	}

	/**
	 * Get invoices.
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

		$items = Invoice::all( $args );
		$total = Invoice::count();

		$response = rest_ensure_response( $items );
		$response->header( 'X-WP-Total', $total );
		$response->header( 'X-WP-TotalPages', ceil( $total / $per_page ) );

		return $response;
	}

	/**
	 * Get single invoice.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function get_item( $request ) {
		$id = $request->get_param( 'id' );
		$item = Invoice::get( $id );

		if ( ! $item ) {
			return new \WP_Error( 'not_found', __( 'Invoice not found', 'practicerx' ), array( 'status' => 404 ) );
		}

		return rest_ensure_response( $item );
	}

	/**
	 * Get invoices by patient.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_by_patient( $request ) {
		$patient_id = $request->get_param( 'patient_id' );
		$items = Invoice::get_by_patient( $patient_id );

		return rest_ensure_response( $items );
	}

	/**
	 * Create invoice.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function create_item( $request ) {
		$data = $request->get_json_params();

		// Validate required fields
		$required = array( 'patient_id', 'practitioner_id', 'amount' );
		foreach ( $required as $field ) {
			if ( empty( $data[ $field ] ) ) {
				return new \WP_Error( 'missing_field', sprintf( __( '%s is required', 'practicerx' ), $field ), array( 'status' => 400 ) );
			}
		}

		// Generate invoice number
		if ( empty( $data['invoice_number'] ) ) {
			$data['invoice_number'] = Invoice::generate_invoice_number();
		}

		// Set default values
		if ( empty( $data['currency'] ) ) {
			$data['currency'] = ppms_get_option( 'currency', 'USD' );
		}

		if ( empty( $data['status'] ) ) {
			$data['status'] = Constants::PAYMENT_STATUS_PENDING;
		}

		$invoice_id = Invoice::create( $data );

		if ( ! $invoice_id ) {
			return new \WP_Error( 'create_failed', __( 'Failed to create invoice', 'practicerx' ), array( 'status' => 500 ) );
		}

		do_action( 'ppms_after_invoice_created', $invoice_id, $data );

		return rest_ensure_response( Helper::format_response( true, Invoice::get( $invoice_id ), __( 'Invoice created successfully', 'practicerx' ) ) );
	}

	/**
	 * Update invoice.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function update_item( $request ) {
		$id = $request->get_param( 'id' );
		$data = $request->get_json_params();

		$invoice = Invoice::get( $id );
		if ( ! $invoice ) {
			return new \WP_Error( 'not_found', __( 'Invoice not found', 'practicerx' ), array( 'status' => 404 ) );
		}

		$updated = Invoice::update( $id, $data );

		if ( false === $updated ) {
			return new \WP_Error( 'update_failed', __( 'Failed to update invoice', 'practicerx' ), array( 'status' => 500 ) );
		}

		do_action( 'ppms_after_invoice_updated', $id, $data );

		return rest_ensure_response( Helper::format_response( true, Invoice::get( $id ), __( 'Invoice updated successfully', 'practicerx' ) ) );
	}

	/**
	 * Delete invoice.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function delete_item( $request ) {
		$id = $request->get_param( 'id' );

		$invoice = Invoice::get( $id );
		if ( ! $invoice ) {
			return new \WP_Error( 'not_found', __( 'Invoice not found', 'practicerx' ), array( 'status' => 404 ) );
		}

		$deleted = Invoice::delete( $id );

		if ( ! $deleted ) {
			return new \WP_Error( 'delete_failed', __( 'Failed to delete invoice', 'practicerx' ), array( 'status' => 500 ) );
		}

		do_action( 'ppms_after_invoice_deleted', $id );

		return rest_ensure_response( Helper::format_response( true, array(), __( 'Invoice deleted successfully', 'practicerx' ) ) );
	}

	/**
	 * Check list permissions.
	 */
	public function check_list_permissions( $request ) {
		return ppms_user_can( Constants::CAP_INVOICE_LIST );
	}

	/**
	 * Check view permissions.
	 */
	public function check_view_permissions( $request ) {
		return ppms_user_can( Constants::CAP_INVOICE_VIEW );
	}

	/**
	 * Check create permissions.
	 */
	public function check_create_permissions( $request ) {
		return ppms_user_can( Constants::CAP_INVOICE_ADD );
	}

	/**
	 * Check edit permissions.
	 */
	public function check_edit_permissions( $request ) {
		return ppms_user_can( Constants::CAP_INVOICE_EDIT );
	}

	/**
	 * Check delete permissions.
	 */
	public function check_delete_permissions( $request ) {
		return ppms_user_can( Constants::CAP_INVOICE_DELETE );
	}
}
