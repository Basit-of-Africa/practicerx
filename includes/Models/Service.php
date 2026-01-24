<?php
namespace PracticeRx\Models;

/**
 * Class Service
 *
 * Model for Service data.
 */
class Service extends AbstractModel {

	/**
	 * Table name (without prefix).
	 *
	 * @var string
	 */
	protected static $table = 'ppms_services';

	/**
	 * Get all active services.
	 *
	 * @return array
	 */
	public static function get_active() {
		return self::find_by( array( 'is_active' => 1 ), array( 'order_by' => 'name', 'order' => 'ASC' ) );
	}

	/**
	 * Get services by practitioner ID.
	 *
	 * @param int $practitioner_id Practitioner ID
	 * @return array
	 */
	public static function get_by_practitioner( $practitioner_id ) {
		global $wpdb;
		$table = self::get_table();

		return $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM {$table} 
			WHERE is_active = 1 
			AND (practitioner_ids = '' OR practitioner_ids LIKE %s)
			ORDER BY name ASC",
			'%"' . $practitioner_id . '"%'
		) );
	}

	/**
	 * Check if service is available for practitioner.
	 *
	 * @param int $service_id Service ID
	 * @param int $practitioner_id Practitioner ID
	 * @return bool
	 */
	public static function is_available_for_practitioner( $service_id, $practitioner_id ) {
		$service = self::get( $service_id );

		if ( ! $service || ! $service->is_active ) {
			return false;
		}

		// If practitioner_ids is empty, service is available for all
		if ( empty( $service->practitioner_ids ) ) {
			return true;
		}

		$practitioner_ids = json_decode( $service->practitioner_ids, true );

		if ( ! is_array( $practitioner_ids ) ) {
			return true;
		}

		return in_array( $practitioner_id, $practitioner_ids, true );
	}
}
