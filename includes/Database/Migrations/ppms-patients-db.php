<?php
/**
 * Patients Table Migration
 */

global $wpdb;

$charset_collate = $wpdb->get_charset_collate();
$table_name = $wpdb->prefix . 'ppms_patients';

$sql = "CREATE TABLE {$table_name} (
	id bigint(20) NOT NULL AUTO_INCREMENT,
	user_id bigint(20) NOT NULL,
	dob date DEFAULT '0000-00-00',
	gender varchar(50) DEFAULT '',
	phone varchar(50) DEFAULT '',
	address text DEFAULT '',
	emergency_contact longtext DEFAULT '',
	medical_history_summary longtext DEFAULT '',
	PRIMARY KEY  (id),
	KEY user_id (user_id)
) $charset_collate;";

ppms_maybe_create_table( $table_name, $sql );
