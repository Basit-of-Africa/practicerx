<?php
/**
 * Client Portal Settings Table Migration
 *
 * Portal access and customization settings
 *
 * @package PracticeRx
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;
$table_name = $wpdb->prefix . 'ppms_portal_settings';
$charset_collate = $wpdb->get_charset_collate();

$sql = "CREATE TABLE {$table_name} (
	id bigint(20) NOT NULL AUTO_INCREMENT,
	practitioner_id bigint(20) NOT NULL,
	portal_name varchar(255) DEFAULT '',
	logo_url varchar(500) DEFAULT '',
	primary_color varchar(20) DEFAULT '#007cba',
	secondary_color varchar(20) DEFAULT '#333333',
	custom_domain varchar(255) DEFAULT '',
	welcome_message text DEFAULT '',
	features_enabled longtext DEFAULT '',
	email_settings longtext DEFAULT '',
	created_at datetime DEFAULT CURRENT_TIMESTAMP,
	updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	KEY practitioner_id (practitioner_id)
) $charset_collate;";

ppms_maybe_create_table( $table_name, $sql );
