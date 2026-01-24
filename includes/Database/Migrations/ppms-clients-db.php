<?php
/**
 * Clients Table Migration
 *
 * @package PracticeRx
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;
$table_name = $wpdb->prefix . 'ppms_clients';
$charset_collate = $wpdb->get_charset_collate();

$sql = "CREATE TABLE {$table_name} (
	id bigint(20) NOT NULL AUTO_INCREMENT,
	user_id bigint(20) NOT NULL,
	first_name varchar(100) NOT NULL DEFAULT '',
	last_name varchar(100) NOT NULL DEFAULT '',
	email varchar(100) NOT NULL DEFAULT '',
	phone varchar(20) DEFAULT '',
	date_of_birth date DEFAULT NULL,
	gender varchar(20) DEFAULT '',
	address text DEFAULT '',
	city varchar(100) DEFAULT '',
	state varchar(100) DEFAULT '',
	zip_code varchar(20) DEFAULT '',
	country varchar(100) DEFAULT '',
	emergency_contact_name varchar(100) DEFAULT '',
	emergency_contact_phone varchar(20) DEFAULT '',
	medical_history text DEFAULT '',
	allergies text DEFAULT '',
	current_medications text DEFAULT '',
	status varchar(20) DEFAULT 'active',
	practitioner_id bigint(20) DEFAULT NULL,
	assigned_date datetime DEFAULT NULL,
	notes text DEFAULT '',
	created_at datetime DEFAULT CURRENT_TIMESTAMP,
	updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	KEY user_id (user_id),
	KEY practitioner_id (practitioner_id),
	KEY status (status),
	KEY email (email)
) $charset_collate;";

ppms_maybe_create_table( $table_name, $sql );
