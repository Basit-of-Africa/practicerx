<?php
/**
 * Services Table Migration
 */

global $wpdb;

$charset_collate = $wpdb->get_charset_collate();
$table_name = $wpdb->prefix . 'ppms_services';

$sql = "CREATE TABLE {$table_name} (
	id bigint(20) NOT NULL AUTO_INCREMENT,
	name varchar(255) NOT NULL,
	duration_minutes int(11) NOT NULL DEFAULT 30,
	price decimal(10,2) NOT NULL DEFAULT 0.00,
	currency varchar(3) NOT NULL DEFAULT 'USD',
	practitioner_ids longtext DEFAULT '',
	is_active tinyint(1) NOT NULL DEFAULT 1,
	PRIMARY KEY  (id)
) $charset_collate;";

ppms_maybe_create_table( $table_name, $sql );
