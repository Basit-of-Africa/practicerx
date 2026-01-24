<?php
namespace PracticeRx\Services;

use PracticeRx\Models\Client;
use PracticeRx\Models\Appointment;
use PracticeRx\Models\Invoice;
use PracticeRx\Models\Payment;
use PracticeRx\Models\Encounter;
use PracticeRx\Core\Constants;

/**
 * Class ReportsService
 *
 * Generates analytics and reports for the practice.
 */
class ReportsService {

	/**
	 * Get dashboard statistics.
	 *
	 * @param int $practitioner_id Optional practitioner ID.
	 * @return array
	 */
	public function get_dashboard_stats( $practitioner_id = null ) {
		$today = current_time( 'Y-m-d' );
		$this_month_start = date( 'Y-m-01 00:00:00' );
		$this_month_end = date( 'Y-m-t 23:59:59' );

		$stats = array(
			'total_clients'        => Client::count_active(),
			'new_clients_today'    => Client::count_new_clients( $today . ' 00:00:00', $today . ' 23:59:59' ),
			'new_clients_month'    => Client::count_new_clients( $this_month_start, $this_month_end ),
			'appointments_today'   => Appointment::count_by_status( Constants::APPOINTMENT_STATUS_SCHEDULED, $practitioner_id ),
			'appointments_pending' => Appointment::count_by_status( Constants::APPOINTMENT_STATUS_SCHEDULED, $practitioner_id ),
			'revenue_month'        => $this->get_revenue( $this_month_start, $this_month_end, $practitioner_id ),
			'pending_invoices'     => $this->count_pending_invoices( $practitioner_id ),
		);

		return apply_filters( 'ppms_dashboard_stats', $stats, $practitioner_id );
	}

	/**
	 * Get revenue for date range.
	 *
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @param int    $practitioner_id Optional practitioner ID.
	 * @return float
	 */
	public function get_revenue( $start_date, $end_date, $practitioner_id = null ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ppms_payments';

		$query = "SELECT SUM(amount) FROM {$table} 
				WHERE status = %s 
				AND payment_date BETWEEN %s AND %s";
		
		$params = array(
			Constants::PAYMENT_STATUS_COMPLETED,
			$start_date,
			$end_date,
		);

		if ( $practitioner_id ) {
			$query .= " AND practitioner_id = %d";
			$params[] = $practitioner_id;
		}

		$revenue = $wpdb->get_var( $wpdb->prepare( $query, $params ) );

		return floatval( $revenue );
	}

	/**
	 * Count pending invoices.
	 *
	 * @param int $practitioner_id Optional practitioner ID.
	 * @return int
	 */
	private function count_pending_invoices( $practitioner_id = null ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ppms_invoices';

		if ( $practitioner_id ) {
			return absint( $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$table} WHERE status = %s AND practitioner_id = %d",
					Constants::PAYMENT_STATUS_PENDING,
					$practitioner_id
				)
			) );
		}

		return absint( $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE status = %s", Constants::PAYMENT_STATUS_PENDING )
		) );
	}

	/**
	 * Get appointment statistics by status.
	 *
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @param int    $practitioner_id Optional practitioner ID.
	 * @return array
	 */
	public function get_appointment_stats( $start_date, $end_date, $practitioner_id = null ) {
		$statuses = Constants::get_appointment_statuses();
		$stats = array();

		foreach ( $statuses as $status => $label ) {
			$stats[ $status ] = Appointment::count_by_status( $status, $practitioner_id );
		}

		$stats['total'] = array_sum( $stats );

		return $stats;
	}

	/**
	 * Get revenue report by date range.
	 *
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @param int    $practitioner_id Optional practitioner ID.
	 * @return array
	 */
	public function get_revenue_report( $start_date, $end_date, $practitioner_id = null ) {
		global $wpdb;
		$payments_table = $wpdb->prefix . 'ppms_payments';
		$invoices_table = $wpdb->prefix . 'ppms_invoices';

		$query = "
			SELECT 
				DATE(p.payment_date) as date,
				SUM(p.amount) as revenue,
				COUNT(p.id) as payment_count
			FROM {$payments_table} p
			WHERE p.status = %s
			AND p.payment_date BETWEEN %s AND %s
		";

		$params = array(
			Constants::PAYMENT_STATUS_COMPLETED,
			$start_date,
			$end_date,
		);

		if ( $practitioner_id ) {
			$query .= " AND p.practitioner_id = %d";
			$params[] = $practitioner_id;
		}

		$query .= " GROUP BY DATE(p.payment_date) ORDER BY date ASC";

		$results = $wpdb->get_results( $wpdb->prepare( $query, $params ) );

		return $results;
	}

	/**
	 * Get client growth report.
	 *
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return array
	 */
	public function get_client_growth_report( $start_date, $end_date ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ppms_clients';

		$query = "
			SELECT 
				DATE(created_at) as date,
				COUNT(id) as new_clients
			FROM {$table}
			WHERE created_at BETWEEN %s AND %s
			GROUP BY DATE(created_at)
			ORDER BY date ASC
		";

		return $wpdb->get_results( $wpdb->prepare( $query, $start_date, $end_date ) );
	}

	/**
	 * Get top clients by revenue.
	 *
	 * @param int $limit Limit.
	 * @param int $practitioner_id Optional practitioner ID.
	 * @return array
	 */
	public function get_top_clients( $limit = 10, $practitioner_id = null ) {
		global $wpdb;
		$payments_table = $wpdb->prefix . 'ppms_payments';
		$clients_table = $wpdb->prefix . 'ppms_clients';

		$query = "
			SELECT 
				c.id,
				c.first_name,
				c.last_name,
				c.email,
				SUM(p.amount) as total_paid,
				COUNT(p.id) as payment_count
			FROM {$payments_table} p
			INNER JOIN {$clients_table} c ON p.patient_id = c.id
			WHERE p.status = %s
		";

		$params = array( Constants::PAYMENT_STATUS_COMPLETED );

		if ( $practitioner_id ) {
			$query .= " AND p.practitioner_id = %d";
			$params[] = $practitioner_id;
		}

		$query .= " GROUP BY c.id ORDER BY total_paid DESC LIMIT %d";
		$params[] = $limit;

		return $wpdb->get_results( $wpdb->prepare( $query, $params ) );
	}

	/**
	 * Get practitioner performance report.
	 *
	 * @param string $start_date Start date.
	 * @param string $end_date End date.
	 * @return array
	 */
	public function get_practitioner_performance( $start_date, $end_date ) {
		global $wpdb;
		$practitioners_table = $wpdb->prefix . 'ppms_practitioners';
		$appointments_table = $wpdb->prefix . 'ppms_appointments';
		$payments_table = $wpdb->prefix . 'ppms_payments';

		$query = "
			SELECT 
				pr.id,
				u.display_name,
				pr.specialty,
				COUNT(DISTINCT a.id) as total_appointments,
				SUM(CASE WHEN a.status = %s THEN 1 ELSE 0 END) as completed_appointments,
				SUM(CASE WHEN a.status = %s THEN 1 ELSE 0 END) as cancelled_appointments,
				COALESCE(SUM(p.amount), 0) as revenue
			FROM {$practitioners_table} pr
			LEFT JOIN {$wpdb->users} u ON pr.user_id = u.ID
			LEFT JOIN {$appointments_table} a ON pr.id = a.practitioner_id 
				AND a.start_time BETWEEN %s AND %s
			LEFT JOIN {$payments_table} p ON pr.id = p.practitioner_id 
				AND p.payment_date BETWEEN %s AND %s 
				AND p.status = %s
			GROUP BY pr.id
			ORDER BY revenue DESC
		";

		return $wpdb->get_results( $wpdb->prepare(
			$query,
			Constants::APPOINTMENT_STATUS_COMPLETED,
			Constants::APPOINTMENT_STATUS_CANCELLED,
			$start_date,
			$end_date,
			$start_date,
			$end_date,
			Constants::PAYMENT_STATUS_COMPLETED
		) );
	}

	/**
	 * Export report to CSV.
	 *
	 * @param array  $data Report data.
	 * @param string $filename Filename.
	 * @return string CSV content.
	 */
	public function export_to_csv( $data, $filename = 'report.csv' ) {
		if ( empty( $data ) ) {
			return '';
		}

		$csv = fopen( 'php://temp', 'r+' );

		// Add headers
		$headers = array_keys( (array) $data[0] );
		fputcsv( $csv, $headers );

		// Add data
		foreach ( $data as $row ) {
			fputcsv( $csv, (array) $row );
		}

		rewind( $csv );
		$output = stream_get_contents( $csv );
		fclose( $csv );

		return $output;
	}
}
