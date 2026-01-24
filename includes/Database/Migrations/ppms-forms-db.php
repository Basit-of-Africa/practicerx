<?php
/**
 * Forms Table Migration
 *
 * Dynamic form builder for intake forms and questionnaires
 *
 * @package PracticeRx
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;
$table_name = $wpdb->prefix . 'ppms_forms';
$charset_collate = $wpdb->get_charset_collate();

$sql = "CREATE TABLE {$table_name} (
	id bigint(20) NOT NULL AUTO_INCREMENT,
	practitioner_id bigint(20) DEFAULT NULL,
	title varchar(255) NOT NULL,
	description text DEFAULT '',
	form_type varchar(50) DEFAULT 'questionnaire',
	fields longtext NOT NULL,
	settings longtext DEFAULT '',
	is_active tinyint(1) DEFAULT 1,
	is_public tinyint(1) DEFAULT 0,
	created_at datetime DEFAULT CURRENT_TIMESTAMP,
	updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	KEY practitioner_id (practitioner_id),
	KEY form_type (form_type),
	KEY is_active (is_active)
) $charset_collate;";

ppms_maybe_create_table( $table_name, $sql );
