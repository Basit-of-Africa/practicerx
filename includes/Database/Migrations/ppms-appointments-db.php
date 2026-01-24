<?php
/**
 * Appointments Table Migration
 */

global $wpdb;

$charset_collate = $wpdb->get_charset_collate();
$table_name = $wpdb->prefix . 'ppms_appointments';

$sql = "CREATE TABLE {$table_name} (
	id bigint(20) NOT NULL AUTO_INCREMENT,
	patient_id bigint(20) NOT NULL,
	practitioner_id bigint(20) NOT NULL,
	service_id bigint(20) NOT NULL,
	start_time datetime NOT NULL,
	end_time datetime NOT NULL,
	status varchar(50) NOT NULL DEFAULT 'scheduled',
	notes text DEFAULT '',
	meeting_link varchar(255) DEFAULT '',
	created_at datetime DEFAULT CURRENT_TIMESTAMP,
	updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY  (id),
	KEY patient_id (patient_id),
	KEY practitioner_id (practitioner_id),
	KEY service_id (service_id),
	KEY start_time (start_time),
	KEY status (status)
) $charset_collate;";

ppms_maybe_create_table( $table_name, $sql );
