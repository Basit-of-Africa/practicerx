<?php
/**
 * Form Submissions Table Migration
 *
 * Client responses to forms
 *
 * @package PracticeRx
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;
$table_name = $wpdb->prefix . 'ppms_form_submissions';
$charset_collate = $wpdb->get_charset_collate();

$sql = "CREATE TABLE {$table_name} (
	id bigint(20) NOT NULL AUTO_INCREMENT,
	form_id bigint(20) NOT NULL,
	client_id bigint(20) DEFAULT NULL,
	practitioner_id bigint(20) DEFAULT NULL,
	responses longtext NOT NULL,
	status varchar(20) DEFAULT 'completed',
	ip_address varchar(45) DEFAULT '',
	submitted_at datetime DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	KEY form_id (form_id),
	KEY client_id (client_id),
	KEY practitioner_id (practitioner_id),
	KEY status (status)
) $charset_collate;";

ppms_maybe_create_table( $table_name, $sql );
