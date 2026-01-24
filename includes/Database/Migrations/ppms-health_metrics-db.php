<?php
/**
 * Health Metrics Table Migration
 *
 * Track vitals, labs, and health measurements
 *
 * @package PracticeRx
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;
$table_name = $wpdb->prefix . 'ppms_health_metrics';
$charset_collate = $wpdb->get_charset_collate();

$sql = "CREATE TABLE {$table_name} (
	id bigint(20) NOT NULL AUTO_INCREMENT,
	client_id bigint(20) NOT NULL,
	practitioner_id bigint(20) DEFAULT NULL,
	metric_type varchar(50) NOT NULL,
	metric_name varchar(100) NOT NULL,
	value varchar(255) NOT NULL,
	unit varchar(50) DEFAULT '',
	recorded_date datetime NOT NULL,
	notes text DEFAULT '',
	reference_range varchar(100) DEFAULT '',
	is_abnormal tinyint(1) DEFAULT 0,
	created_at datetime DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	KEY client_id (client_id),
	KEY metric_type (metric_type),
	KEY recorded_date (recorded_date),
	KEY is_abnormal (is_abnormal)
) $charset_collate;";

ppms_maybe_create_table( $table_name, $sql );
