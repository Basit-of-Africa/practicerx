<?php
/**
 * Telehealth Sessions Table Migration
 *
 * Video consultation sessions
 *
 * @package PracticeRx
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;
$table_name = $wpdb->prefix . 'ppms_telehealth_sessions';
$charset_collate = $wpdb->get_charset_collate();

$sql = "CREATE TABLE {$table_name} (
	id bigint(20) NOT NULL AUTO_INCREMENT,
	appointment_id bigint(20) DEFAULT NULL,
	client_id bigint(20) NOT NULL,
	practitioner_id bigint(20) NOT NULL,
	provider varchar(20) DEFAULT 'zoom',
	meeting_id varchar(255) DEFAULT '',
	meeting_url varchar(500) DEFAULT '',
	meeting_password varchar(255) DEFAULT '',
	host_url varchar(500) DEFAULT '',
	start_time datetime NOT NULL,
	duration int(11) DEFAULT 60,
	status varchar(20) DEFAULT 'scheduled',
	recording_url varchar(500) DEFAULT '',
	provider_data longtext DEFAULT '',
	created_at datetime DEFAULT CURRENT_TIMESTAMP,
	updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	KEY appointment_id (appointment_id),
	KEY client_id (client_id),
	KEY practitioner_id (practitioner_id),
	KEY status (status),
	KEY start_time (start_time)
) $charset_collate;";

ppms_maybe_create_table( $table_name, $sql );
