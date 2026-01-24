<?php
namespace PracticeRx\Core;

use PracticeRx\Database\Schema;

/**
 * Class Installer
 *
 * Handles plugin installation and updates.
 */
class Installer {

	/**
	 * Run the installation process.
	 */
	public static function install() {
		// Check for capability
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		// Create tables
		Schema::create_tables();

		// Add roles
		self::add_roles();

		// Flush rewrite rules
		flush_rewrite_rules();
	}

	/**
	 * Add custom roles with granular capabilities.
	 */
	private static function add_roles() {
		// Practitioner Role
		add_role(
			Constants::ROLE_PRACTITIONER,
			__( 'Practitioner', 'practicerx' ),
			array(
				'read'                      => true,
				// Dashboard
				Constants::CAP_VIEW_DASHBOARD => true,
				
				// Appointments
				Constants::CAP_APPOINTMENT_LIST   => true,
				Constants::CAP_APPOINTMENT_ADD    => true,
				Constants::CAP_APPOINTMENT_EDIT   => true,
				Constants::CAP_APPOINTMENT_DELETE => true,
				Constants::CAP_APPOINTMENT_VIEW   => true,
				
				// Patients
				Constants::CAP_PATIENT_LIST   => true,
				Constants::CAP_PATIENT_ADD    => true,
				Constants::CAP_PATIENT_EDIT   => true,
				Constants::CAP_PATIENT_VIEW   => true,
				
				// Encounters
				Constants::CAP_ENCOUNTER_LIST   => true,
				Constants::CAP_ENCOUNTER_ADD    => true,
				Constants::CAP_ENCOUNTER_EDIT   => true,
				Constants::CAP_ENCOUNTER_VIEW   => true,
				
				// Billing
				Constants::CAP_INVOICE_LIST => true,
				Constants::CAP_INVOICE_ADD  => true,
				Constants::CAP_INVOICE_EDIT => true,
				Constants::CAP_INVOICE_VIEW => true,
			)
		);

		// Patient Role
		add_role(
			Constants::ROLE_PATIENT,
			__( 'Patient', 'practicerx' ),
			array(
				'read'                             => true,
				Constants::CAP_VIEW_OWN_APPOINTMENTS => true,
				Constants::CAP_BOOK_APPOINTMENTS     => true,
				Constants::CAP_VIEW_OWN_ENCOUNTERS   => true,
				Constants::CAP_VIEW_OWN_INVOICES     => true,
				Constants::CAP_PAY_INVOICES          => true,
			)
		);

		// Add capabilities to admin role
		$admin_role = get_role( 'administrator' );
		if ( $admin_role ) {
			$admin_capabilities = array(
				Constants::CAP_VIEW_DASHBOARD,
				Constants::CAP_MANAGE_APPOINTMENTS,
				Constants::CAP_MANAGE_PATIENTS,
				Constants::CAP_MANAGE_ENCOUNTERS,
				Constants::CAP_MANAGE_BILLING,
				Constants::CAP_PATIENT_LIST,
				Constants::CAP_PATIENT_ADD,
				Constants::CAP_PATIENT_EDIT,
				Constants::CAP_PATIENT_DELETE,
				Constants::CAP_PATIENT_VIEW,
				Constants::CAP_APPOINTMENT_LIST,
				Constants::CAP_APPOINTMENT_ADD,
				Constants::CAP_APPOINTMENT_EDIT,
				Constants::CAP_APPOINTMENT_DELETE,
				Constants::CAP_APPOINTMENT_VIEW,
				Constants::CAP_ENCOUNTER_LIST,
				Constants::CAP_ENCOUNTER_ADD,
				Constants::CAP_ENCOUNTER_EDIT,
				Constants::CAP_ENCOUNTER_DELETE,
				Constants::CAP_ENCOUNTER_VIEW,
				Constants::CAP_INVOICE_LIST,
				Constants::CAP_INVOICE_ADD,
				Constants::CAP_INVOICE_EDIT,
				Constants::CAP_INVOICE_DELETE,
				Constants::CAP_INVOICE_VIEW,
			);

			foreach ( $admin_capabilities as $cap ) {
				$admin_role->add_cap( $cap );
			}
		}
	}
}
