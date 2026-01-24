<?php
/**
 * Portal Settings Model
 *
 * Client portal customization
 *
 * @package PracticeRx
 */

namespace PracticeRx\Models;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PortalSettings extends AbstractModel {
	
	protected static $table = 'ppms_portal_settings';
	
	/**
	 * Get settings by practitioner
	 *
	 * @param int $practitioner_id Practitioner ID
	 * @return object|null
	 */
	public static function get_by_practitioner( $practitioner_id ) {
		global $wpdb;
		$table = self::get_table();
		
		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE practitioner_id = %d",
				$practitioner_id
			)
		);
	}
	
	/**
	 * Get default settings
	 *
	 * @return array
	 */
	public static function get_defaults() {
		return array(
			'portal_name' => get_bloginfo( 'name' ) . ' Client Portal',
			'logo_url' => '',
			'primary_color' => '#007cba',
			'secondary_color' => '#333333',
			'custom_domain' => '',
			'welcome_message' => 'Welcome to your client portal!',
			'features_enabled' => json_encode( array(
				'appointments' => true,
				'programs' => true,
				'forms' => true,
				'documents' => true,
				'health_tracking' => true,
				'messaging' => true,
				'invoices' => true
			) ),
			'email_settings' => json_encode( array(
				'send_welcome_email' => true,
				'appointment_reminders' => true,
				'form_notifications' => true
			) )
		);
	}
	
	/**
	 * Get or create settings for practitioner
	 *
	 * @param int $practitioner_id Practitioner ID
	 * @return object
	 */
	public static function get_or_create( $practitioner_id ) {
		$settings = self::get_by_practitioner( $practitioner_id );
		
		if ( ! $settings ) {
			$defaults = self::get_defaults();
			$defaults['practitioner_id'] = $practitioner_id;
			
			$id = self::create( $defaults );
			$settings = self::get( $id );
		}
		
		return $settings;
	}
}
