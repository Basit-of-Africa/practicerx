<?php
/**
 * Practitioners Table Migration
 */

global $wpdb;

$charset_collate = $wpdb->get_charset_collate();
$table_name = $wpdb->prefix . 'ppms_practitioners';

$sql = "CREATE TABLE {$table_name} (
	id bigint(20) NOT NULL AUTO_INCREMENT,
	user_id bigint(20) NOT NULL,
	specialty varchar(255) DEFAULT '',
	license_number varchar(100) DEFAULT '',
	bio text DEFAULT '',
	availability_settings longtext DEFAULT '',
	created_at datetime DEFAULT '0000-00-00 00:00:00',
	PRIMARY KEY  (id),
	KEY user_id (user_id)
) $charset_collate;";

ppms_maybe_create_table( $table_name, $sql );
