<?php
/**
 * Invoices Table Migration
 */

global $wpdb;

$charset_collate = $wpdb->get_charset_collate();
$table_name = $wpdb->prefix . 'ppms_invoices';

$sql = "CREATE TABLE {$table_name} (
	id bigint(20) NOT NULL AUTO_INCREMENT,
	patient_id bigint(20) NOT NULL,
	practitioner_id bigint(20) NOT NULL,
	appointment_id bigint(20) DEFAULT NULL,
	invoice_number varchar(50) NOT NULL,
	amount decimal(10,2) NOT NULL DEFAULT 0.00,
	currency varchar(3) NOT NULL DEFAULT 'USD',
	status varchar(50) NOT NULL DEFAULT 'pending',
	due_date datetime DEFAULT NULL,
	notes text DEFAULT '',
	created_at datetime DEFAULT CURRENT_TIMESTAMP,
	updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY  (id),
	KEY patient_id (patient_id),
	KEY practitioner_id (practitioner_id),
	KEY appointment_id (appointment_id),
	KEY invoice_number (invoice_number),
	KEY status (status)
) $charset_collate;";

ppms_maybe_create_table( $table_name, $sql );
