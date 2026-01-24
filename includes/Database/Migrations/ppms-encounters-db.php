<?php
/**
 * Encounters Table Migration
 */

global $wpdb;

$charset_collate = $wpdb->get_charset_collate();
$table_name = $wpdb->prefix . 'ppms_encounters';

$sql = "CREATE TABLE {$table_name} (
	id bigint(20) NOT NULL AUTO_INCREMENT,
	appointment_id bigint(20) DEFAULT NULL,
	practitioner_id bigint(20) NOT NULL,
	patient_id bigint(20) NOT NULL,
	type varchar(50) NOT NULL DEFAULT 'general',
	content longtext DEFAULT '',
	created_at datetime DEFAULT CURRENT_TIMESTAMP,
	updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY  (id),
	KEY appointment_id (appointment_id),
	KEY patient_id (patient_id),
	KEY practitioner_id (practitioner_id)
) $charset_collate;";

ppms_maybe_create_table( $table_name, $sql );
