<?php
/**
 * Documents Table Migration
 *
 * File library for sharing documents with clients
 *
 * @package PracticeRx
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;
$table_name = $wpdb->prefix . 'ppms_documents';
$charset_collate = $wpdb->get_charset_collate();

$sql = "CREATE TABLE {$table_name} (
	id bigint(20) NOT NULL AUTO_INCREMENT,
	practitioner_id bigint(20) NOT NULL,
	title varchar(255) NOT NULL,
	description text DEFAULT '',
	file_name varchar(255) NOT NULL,
	file_path varchar(500) NOT NULL,
	file_type varchar(50) DEFAULT '',
	file_size bigint(20) DEFAULT 0,
	category varchar(100) DEFAULT 'general',
	is_public tinyint(1) DEFAULT 0,
	shared_with text DEFAULT '',
	uploaded_by bigint(20) NOT NULL,
	created_at datetime DEFAULT CURRENT_TIMESTAMP,
	updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	KEY practitioner_id (practitioner_id),
	KEY category (category),
	KEY is_public (is_public)
) $charset_collate;";

ppms_maybe_create_table( $table_name, $sql );
