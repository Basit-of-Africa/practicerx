<?php
/**
 * Client Programs Table Migration
 *
 * Programs enrolled by clients
 *
 * @package PracticeRx
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;
$table_name = $wpdb->prefix . 'ppms_client_programs';
$charset_collate = $wpdb->get_charset_collate();

$sql = "CREATE TABLE {$table_name} (
	id bigint(20) NOT NULL AUTO_INCREMENT,
	client_id bigint(20) NOT NULL,
	program_id bigint(20) NOT NULL,
	practitioner_id bigint(20) NOT NULL,
	status varchar(20) DEFAULT 'active',
	start_date date NOT NULL,
	end_date date DEFAULT NULL,
	sessions_completed int(11) DEFAULT 0,
	sessions_remaining int(11) DEFAULT 0,
	progress_percentage decimal(5,2) DEFAULT 0.00,
	notes text DEFAULT '',
	invoice_id bigint(20) DEFAULT NULL,
	created_at datetime DEFAULT CURRENT_TIMESTAMP,
	updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	KEY client_id (client_id),
	KEY program_id (program_id),
	KEY practitioner_id (practitioner_id),
	KEY status (status)
) $charset_collate;";

ppms_maybe_create_table( $table_name, $sql );
