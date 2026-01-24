<?php
/**
 * Plugin Constants
 * 
 * Centralized constants definition for PracticeRx plugin.
 */

namespace PracticeRx\Core;

class Constants {

	/**
	 * Table Names (without WordPress prefix)
	 */
	const TABLE_PRACTITIONERS = 'ppms_practitioners';
	const TABLE_PATIENTS = 'ppms_patients';
	const TABLE_SERVICES = 'ppms_services';
	const TABLE_APPOINTMENTS = 'ppms_appointments';
	const TABLE_ENCOUNTERS = 'ppms_encounters';
	const TABLE_INVOICES = 'ppms_invoices';
	const TABLE_PAYMENTS = 'ppms_payments';

	/**
	 * Option Keys (with prefix)
	 */
	const OPTION_THEME_COLOR = 'ppms_theme_color';
	const OPTION_THEME_MODE = 'ppms_theme_mode';
	const OPTION_CURRENCY = 'ppms_currency';
	const OPTION_PAYMENT_GATEWAY = 'ppms_payment_gateway';
	const OPTION_WOOCOMMERCE_ENABLED = 'ppms_woocommerce_enabled';
	const OPTION_DEMO_DATA_IMPORTED = 'ppms_demo_data_imported';
	const OPTION_PLUGIN_VERSION = 'ppms_plugin_version';

	/**
	 * User Roles
	 */
	const ROLE_PRACTITIONER = 'ppms_practitioner';
	const ROLE_PATIENT = 'ppms_patient';

	/**
	 * Capabilities - Practitioner
	 */
	const CAP_PRACTITIONER = 'ppms_practitioner';
	const CAP_VIEW_DASHBOARD = 'ppms_view_dashboard';
	const CAP_MANAGE_APPOINTMENTS = 'ppms_manage_appointments';
	const CAP_MANAGE_PATIENTS = 'ppms_manage_patients';
	const CAP_MANAGE_ENCOUNTERS = 'ppms_manage_encounters';
	const CAP_MANAGE_BILLING = 'ppms_manage_billing';

	/**
	 * Capabilities - Patient
	 */
	const CAP_PATIENT = 'ppms_patient';
	const CAP_VIEW_OWN_APPOINTMENTS = 'ppms_view_own_appointments';
	const CAP_BOOK_APPOINTMENTS = 'ppms_book_appointments';
	const CAP_VIEW_OWN_ENCOUNTERS = 'ppms_view_own_encounters';
	const CAP_VIEW_OWN_INVOICES = 'ppms_view_own_invoices';
	const CAP_PAY_INVOICES = 'ppms_pay_invoices';

	/**
	 * Granular Action Capabilities
	 */
	const CAP_PATIENT_LIST = 'ppms_patient_list';
	const CAP_PATIENT_ADD = 'ppms_patient_add';
	const CAP_PATIENT_EDIT = 'ppms_patient_edit';
	const CAP_PATIENT_DELETE = 'ppms_patient_delete';
	const CAP_PATIENT_VIEW = 'ppms_patient_view';

	const CAP_APPOINTMENT_LIST = 'ppms_appointment_list';
	const CAP_APPOINTMENT_ADD = 'ppms_appointment_add';
	const CAP_APPOINTMENT_EDIT = 'ppms_appointment_edit';
	const CAP_APPOINTMENT_DELETE = 'ppms_appointment_delete';
	const CAP_APPOINTMENT_VIEW = 'ppms_appointment_view';

	const CAP_ENCOUNTER_LIST = 'ppms_encounter_list';
	const CAP_ENCOUNTER_ADD = 'ppms_encounter_add';
	const CAP_ENCOUNTER_EDIT = 'ppms_encounter_edit';
	const CAP_ENCOUNTER_DELETE = 'ppms_encounter_delete';
	const CAP_ENCOUNTER_VIEW = 'ppms_encounter_view';

	const CAP_INVOICE_LIST = 'ppms_invoice_list';
	const CAP_INVOICE_ADD = 'ppms_invoice_add';
	const CAP_INVOICE_EDIT = 'ppms_invoice_edit';
	const CAP_INVOICE_DELETE = 'ppms_invoice_delete';
	const CAP_INVOICE_VIEW = 'ppms_invoice_view';

	/**
	 * Appointment Statuses
	 */
	const APPOINTMENT_STATUS_SCHEDULED = 'scheduled';
	const APPOINTMENT_STATUS_CONFIRMED = 'confirmed';
	const APPOINTMENT_STATUS_CANCELLED = 'cancelled';
	const APPOINTMENT_STATUS_COMPLETED = 'completed';
	const APPOINTMENT_STATUS_NO_SHOW = 'no_show';

	/**
	 * Payment Statuses
	 */
	const PAYMENT_STATUS_PENDING = 'pending';
	const PAYMENT_STATUS_COMPLETED = 'completed';
	const PAYMENT_STATUS_FAILED = 'failed';
	const PAYMENT_STATUS_REFUNDED = 'refunded';

	/**
	 * Get full table name with WordPress prefix
	 *
	 * @param string $table_constant Table constant name
	 * @return string
	 */
	public static function get_table( $table_constant ) {
		global $wpdb;
		return $wpdb->prefix . $table_constant;
	}

	/**
	 * Get all appointment statuses
	 *
	 * @return array
	 */
	public static function get_appointment_statuses() {
		return array(
			self::APPOINTMENT_STATUS_SCHEDULED => __( 'Scheduled', 'practicerx' ),
			self::APPOINTMENT_STATUS_CONFIRMED => __( 'Confirmed', 'practicerx' ),
			self::APPOINTMENT_STATUS_CANCELLED => __( 'Cancelled', 'practicerx' ),
			self::APPOINTMENT_STATUS_COMPLETED => __( 'Completed', 'practicerx' ),
			self::APPOINTMENT_STATUS_NO_SHOW   => __( 'No Show', 'practicerx' ),
		);
	}

	/**
	 * Get all payment statuses
	 *
	 * @return array
	 */
	public static function get_payment_statuses() {
		return array(
			self::PAYMENT_STATUS_PENDING   => __( 'Pending', 'practicerx' ),
			self::PAYMENT_STATUS_COMPLETED => __( 'Completed', 'practicerx' ),
			self::PAYMENT_STATUS_FAILED    => __( 'Failed', 'practicerx' ),
			self::PAYMENT_STATUS_REFUNDED  => __( 'Refunded', 'practicerx' ),
		);
	}
}
