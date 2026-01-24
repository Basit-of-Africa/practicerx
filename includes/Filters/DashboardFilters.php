<?php
/**
 * Dashboard Filters
 * 
 * Handles dashboard widget and data filters.
 */

namespace PracticeRx\Filters;

use PracticeRx\Core\Helper;
use PracticeRx\Core\Constants;

class DashboardFilters {

	public function __construct() {
		// Modify dashboard statistics
		add_filter( 'ppms_dashboard_stats', array( $this, 'calculate_dashboard_stats' ), 10, 1 );
		
		// Filter upcoming appointments for dashboard
		add_filter( 'ppms_dashboard_upcoming_appointments', array( $this, 'get_upcoming_appointments' ), 10, 2 );
		
		// Add dashboard widgets
		add_action( 'ppms_dashboard_widgets', array( $this, 'register_dashboard_widgets' ), 10, 1 );
	}

	/**
	 * Calculate dashboard statistics
	 *
	 * @param array $stats Current statistics
	 * @return array
	 */
	public function calculate_dashboard_stats( $stats ) {
		global $wpdb;
		
		$user_role = Helper::get_current_user_role();
		$appointments_table = ppms_get_table( Constants::TABLE_APPOINTMENTS );
		$patients_table = ppms_get_table( Constants::TABLE_PATIENTS );

		if ( $user_role === Constants::ROLE_PRACTITIONER ) {
			// Get practitioner's statistics
			$practitioner_table = ppms_get_table( Constants::TABLE_PRACTITIONERS );
			$practitioner = $wpdb->get_row( $wpdb->prepare(
				"SELECT * FROM {$practitioner_table} WHERE user_id = %d",
				get_current_user_id()
			) );

			if ( $practitioner ) {
				$stats['total_appointments'] = $wpdb->get_var( $wpdb->prepare(
					"SELECT COUNT(*) FROM {$appointments_table} WHERE practitioner_id = %d",
					$practitioner->id
				) );

				$stats['todays_appointments'] = $wpdb->get_var( $wpdb->prepare(
					"SELECT COUNT(*) FROM {$appointments_table} 
					WHERE practitioner_id = %d AND DATE(start_time) = CURDATE()",
					$practitioner->id
				) );

				$stats['total_patients'] = $wpdb->get_var( $wpdb->prepare(
					"SELECT COUNT(DISTINCT patient_id) FROM {$appointments_table} 
					WHERE practitioner_id = %d",
					$practitioner->id
				) );
			}
		} elseif ( $user_role === 'administrator' ) {
			// Get global statistics
			$stats['total_appointments'] = $wpdb->get_var(
				"SELECT COUNT(*) FROM {$appointments_table}"
			);

			$stats['todays_appointments'] = $wpdb->get_var(
				"SELECT COUNT(*) FROM {$appointments_table} WHERE DATE(start_time) = CURDATE()"
			);

			$stats['total_patients'] = $wpdb->get_var(
				"SELECT COUNT(*) FROM {$patients_table}"
			);
		}

		return $stats;
	}

	/**
	 * Get upcoming appointments for dashboard
	 *
	 * @param array $appointments Current appointments
	 * @param int   $limit Number of appointments to retrieve
	 * @return array
	 */
	public function get_upcoming_appointments( $appointments, $limit = 5 ) {
		global $wpdb;
		
		$user_role = Helper::get_current_user_role();
		$appointments_table = ppms_get_table( Constants::TABLE_APPOINTMENTS );

		$query = "SELECT * FROM {$appointments_table} 
				  WHERE start_time > NOW() 
				  AND status NOT IN ('cancelled', 'no_show')";

		if ( $user_role === Constants::ROLE_PRACTITIONER ) {
			$practitioner_table = ppms_get_table( Constants::TABLE_PRACTITIONERS );
			$practitioner = $wpdb->get_row( $wpdb->prepare(
				"SELECT * FROM {$practitioner_table} WHERE user_id = %d",
				get_current_user_id()
			) );

			if ( $practitioner ) {
				$query .= $wpdb->prepare( ' AND practitioner_id = %d', $practitioner->id );
			}
		} elseif ( $user_role === Constants::ROLE_PATIENT ) {
			$patients_table = ppms_get_table( Constants::TABLE_PATIENTS );
			$patient = $wpdb->get_row( $wpdb->prepare(
				"SELECT * FROM {$patients_table} WHERE user_id = %d",
				get_current_user_id()
			) );

			if ( $patient ) {
				$query .= $wpdb->prepare( ' AND patient_id = %d', $patient->id );
			}
		}

		$query .= $wpdb->prepare( ' ORDER BY start_time ASC LIMIT %d', $limit );

		return $wpdb->get_results( $query );
	}

	/**
	 * Register dashboard widgets
	 *
	 * @param array $widgets Current widgets
	 * @return void
	 */
	public function register_dashboard_widgets( $widgets ) {
		// Allow other plugins/themes to add dashboard widgets
		do_action( 'ppms_register_custom_dashboard_widgets' );
	}
}
