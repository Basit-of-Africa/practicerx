<?php
namespace PracticeRx\Api;

use WP_REST_Server;
use PracticeRx\Core\DemoDataSeeder;
use PracticeRx\Core\Helper;

/**
 * Class SystemController
 */
class SystemController extends ApiController {

	/**
	 * Register routes.
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/system/seed', array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'seed_data' ),
				'permission_callback' => array( $this, 'check_admin_permissions' ),
			),
		) );

		register_rest_route( $this->namespace, '/system/info', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_system_info' ),
				'permission_callback' => array( $this, 'check_admin_permissions' ),
			),
		) );

		register_rest_route( $this->namespace, '/system/settings', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_settings' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_settings' ),
				'permission_callback' => array( $this, 'check_admin_permissions' ),
			),
		) );
	}

	/**
	 * Seed demo data.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function seed_data( $request ) {
		$count = DemoDataSeeder::run();
		return rest_ensure_response( Helper::format_response( true, array( 'items_created' => $count ), __( 'Demo data seeded successfully', 'practicerx' ) ) );
	}

	/**
	 * Get system information.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_system_info( $request ) {
		global $wpdb;

		$info = array(
			'version'         => PRACTICERX_VERSION,
			'wp_version'      => get_bloginfo( 'version' ),
			'php_version'     => phpversion(),
			'db_version'      => $wpdb->db_version(),
			'tables_exist'    => $this->check_tables_exist(),
			'capabilities'    => ppms_is_practitioner() ? 'practitioner' : ( ppms_is_patient() ? 'patient' : 'none' ),
		);

		return rest_ensure_response( $info );
	}

	/**
	 * Get settings.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_settings( $request ) {
		$settings = array(
			'currency'               => ppms_get_option( 'currency', 'USD' ),
			'date_format'            => ppms_get_option( 'date_format', 'Y-m-d' ),
			'time_format'            => ppms_get_option( 'time_format', 'H:i' ),
			'appointment_duration'   => ppms_get_option( 'appointment_duration', 30 ),
			'business_hours_start'   => ppms_get_option( 'business_hours_start', '09:00' ),
			'business_hours_end'     => ppms_get_option( 'business_hours_end', '17:00' ),
			'enable_notifications'   => ppms_get_option( 'enable_notifications', true ),
			'enable_online_booking'  => ppms_get_option( 'enable_online_booking', true ),
		);

		return rest_ensure_response( $settings );
	}

	/**
	 * Update settings.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function update_settings( $request ) {
		$data = $request->get_json_params();

		foreach ( $data as $key => $value ) {
			ppms_update_option( $key, $value );
		}

		return rest_ensure_response( Helper::format_response( true, $this->get_settings( $request )->data, __( 'Settings updated successfully', 'practicerx' ) ) );
	}

	/**
	 * Check if all tables exist.
	 *
	 * @return bool
	 */
	private function check_tables_exist() {
		global $wpdb;
		
		$tables = array(
			'ppms_practitioners',
			'ppms_patients',
			'ppms_services',
			'ppms_appointments',
			'ppms_encounters',
			'ppms_invoices',
			'ppms_payments',
		);

		foreach ( $tables as $table ) {
			$table_name = $wpdb->prefix . $table;
			if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" ) !== $table_name ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Check admin permissions.
	 */
	public function check_admin_permissions( $request ) {
		return current_user_can( 'manage_options' );
	}
}
