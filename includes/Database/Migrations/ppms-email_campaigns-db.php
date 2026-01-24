<?php
/**
 * Email Campaigns Table Migration
 *
 * Drip email campaign definitions
 *
 * @package PracticeRx
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;
$table_name = $wpdb->prefix . 'ppms_email_campaigns';
$charset_collate = $wpdb->get_charset_collate();

$sql = "CREATE TABLE {$table_name} (
	id bigint(20) NOT NULL AUTO_INCREMENT,
	practitioner_id bigint(20) NOT NULL,
	name varchar(255) NOT NULL,
	description text DEFAULT '',
	trigger_type varchar(50) DEFAULT 'manual',
	trigger_event varchar(50) DEFAULT '',
	emails longtext NOT NULL,
	is_active tinyint(1) DEFAULT 1,
	created_at datetime DEFAULT CURRENT_TIMESTAMP,
	updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	KEY practitioner_id (practitioner_id),
	KEY trigger_type (trigger_type),
	KEY is_active (is_active)
) $charset_collate;";

ppms_maybe_create_table( $table_name, $sql );
