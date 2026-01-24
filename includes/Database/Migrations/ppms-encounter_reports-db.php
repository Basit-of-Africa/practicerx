<?php
/**
 * Encounter Reports Table Migration
 *
 * @package PracticeRx
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;
$table_name = $wpdb->prefix . 'ppms_encounter_reports';
$charset_collate = $wpdb->get_charset_collate();

$sql = "CREATE TABLE {$table_name} (
	id bigint(20) NOT NULL AUTO_INCREMENT,
	encounter_id bigint(20) NOT NULL,
	client_id bigint(20) NOT NULL,
	practitioner_id bigint(20) NOT NULL,
	report_date datetime NOT NULL,
	chief_complaint text DEFAULT '',
	history_present_illness text DEFAULT '',
	review_of_systems text DEFAULT '',
	physical_exam text DEFAULT '',
	assessment text DEFAULT '',
	diagnosis_codes text DEFAULT '',
	treatment_plan text DEFAULT '',
	prescriptions text DEFAULT '',
	lab_orders text DEFAULT '',
	referrals text DEFAULT '',
	follow_up_instructions text DEFAULT '',
	vitals longtext DEFAULT '',
	custom_fields longtext DEFAULT '',
	status varchar(20) DEFAULT 'draft',
	signed_by bigint(20) DEFAULT NULL,
	signed_at datetime DEFAULT NULL,
	created_at datetime DEFAULT CURRENT_TIMESTAMP,
	updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	KEY encounter_id (encounter_id),
	KEY client_id (client_id),
	KEY practitioner_id (practitioner_id),
	KEY status (status),
	KEY report_date (report_date)
) $charset_collate;";

ppms_maybe_create_table( $table_name, $sql );
