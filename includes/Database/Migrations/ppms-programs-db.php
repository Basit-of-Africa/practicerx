<?php
/**
 * Programs Table Migration
 *
 * Treatment packages that practitioners can sell to clients
 *
 * @package PracticeRx
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;
$table_name = $wpdb->prefix . 'ppms_programs';
$charset_collate = $wpdb->get_charset_collate();

$sql = "CREATE TABLE {$table_name} (
	id bigint(20) NOT NULL AUTO_INCREMENT,
	practitioner_id bigint(20) NOT NULL,
	title varchar(255) NOT NULL,
	description text DEFAULT '',
	price decimal(10,2) DEFAULT 0.00,
	currency varchar(10) DEFAULT 'USD',
	duration_weeks int(11) DEFAULT 0,
	duration_days int(11) DEFAULT 0,
	sessions_included int(11) DEFAULT 0,
	program_type varchar(50) DEFAULT 'package',
	features text DEFAULT '',
	includes text DEFAULT '',
	is_active tinyint(1) DEFAULT 1,
	order_number int(11) DEFAULT 0,
	created_at datetime DEFAULT CURRENT_TIMESTAMP,
	updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	KEY practitioner_id (practitioner_id),
	KEY is_active (is_active),
	KEY program_type (program_type)
) $charset_collate;";

ppms_maybe_create_table( $table_name, $sql );
