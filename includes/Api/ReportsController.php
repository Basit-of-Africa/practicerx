<?php
namespace PracticeRx\Api;

use WP_REST_Server;
use PracticeRx\Services\ReportsService;
use PracticeRx\Core\Helper;

/**
 * Class ReportsController
 *
 * Provides analytics and reporting endpoints.
 */
class ReportsController extends ApiController {

	/**
	 * Reports service.
	 *
	 * @var ReportsService
	 */
	private $reports_service;

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct();
		$this->reports_service = new ReportsService();
	}

	/**
	 * Register routes.
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/reports/dashboard', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_dashboard_stats' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			),
		) );

		register_rest_route( $this->namespace, '/reports/revenue', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_revenue_report' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			),
		) );

		register_rest_route( $this->namespace, '/reports/appointments', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_appointment_stats' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			),
		) );

		register_rest_route( $this->namespace, '/reports/client-growth', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_client_growth' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			),
		) );

		register_rest_route( $this->namespace, '/reports/top-clients', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_top_clients' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			),
		) );

		register_rest_route( $this->namespace, '/reports/practitioner-performance', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_practitioner_performance' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			),
		) );
	}

	/**
	 * Get dashboard statistics.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_dashboard_stats( $request ) {
		$practitioner_id = $request->get_param( 'practitioner_id' );
		
		if ( ! $practitioner_id && ppms_is_practitioner() ) {
			$practitioner = \PracticeRx\Models\Practitioner::get_by_user_id( get_current_user_id() );
			$practitioner_id = $practitioner ? $practitioner->id : null;
		}

		$stats = $this->reports_service->get_dashboard_stats( $practitioner_id );

		return rest_ensure_response( $stats );
	}

	/**
	 * Get revenue report.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_revenue_report( $request ) {
		$start_date = $request->get_param( 'start_date' ) ?? date( 'Y-m-01 00:00:00' );
		$end_date = $request->get_param( 'end_date' ) ?? date( 'Y-m-t 23:59:59' );
		$practitioner_id = $request->get_param( 'practitioner_id' );

		$report = $this->reports_service->get_revenue_report( $start_date, $end_date, $practitioner_id );

		return rest_ensure_response( $report );
	}

	/**
	 * Get appointment statistics.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_appointment_stats( $request ) {
		$start_date = $request->get_param( 'start_date' ) ?? date( 'Y-m-01 00:00:00' );
		$end_date = $request->get_param( 'end_date' ) ?? date( 'Y-m-t 23:59:59' );
		$practitioner_id = $request->get_param( 'practitioner_id' );

		$stats = $this->reports_service->get_appointment_stats( $start_date, $end_date, $practitioner_id );

		return rest_ensure_response( $stats );
	}

	/**
	 * Get client growth report.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_client_growth( $request ) {
		$start_date = $request->get_param( 'start_date' ) ?? date( 'Y-m-01 00:00:00' );
		$end_date = $request->get_param( 'end_date' ) ?? date( 'Y-m-t 23:59:59' );

		$report = $this->reports_service->get_client_growth_report( $start_date, $end_date );

		return rest_ensure_response( $report );
	}

	/**
	 * Get top clients by revenue.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_top_clients( $request ) {
		$limit = $request->get_param( 'limit' ) ?? 10;
		$practitioner_id = $request->get_param( 'practitioner_id' );

		$clients = $this->reports_service->get_top_clients( absint( $limit ), $practitioner_id );

		return rest_ensure_response( $clients );
	}

	/**
	 * Get practitioner performance report.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_practitioner_performance( $request ) {
		$start_date = $request->get_param( 'start_date' ) ?? date( 'Y-m-01 00:00:00' );
		$end_date = $request->get_param( 'end_date' ) ?? date( 'Y-m-t 23:59:59' );

		$report = $this->reports_service->get_practitioner_performance( $start_date, $end_date );

		return rest_ensure_response( $report );
	}
}
