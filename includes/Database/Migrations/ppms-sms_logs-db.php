<?php
/**
 * SMS Logs Table Migration
 *
 * @package PracticeRx
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;
$table_name = $wpdb->prefix . 'ppms_sms_logs';
$charset_collate = $wpdb->get_charset_collate();

$sql = "CREATE TABLE {$table_name} (
	id bigint(20) NOT NULL AUTO_INCREMENT,
	recipient_id bigint(20) DEFAULT NULL,
	recipient_type varchar(20) DEFAULT 'client',
	phone_number varchar(20) NOT NULL,
	message text NOT NULL,
	provider varchar(50) DEFAULT 'twilio',
	status varchar(20) DEFAULT 'pending',
	provider_message_id varchar(255) DEFAULT '',
	error_message text DEFAULT '',
	sent_by bigint(20) DEFAULT NULL,
	sent_at datetime DEFAULT NULL,
	created_at datetime DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	KEY recipient_id (recipient_id),
	KEY status (status),
	KEY sent_at (sent_at)
) $charset_collate;";

ppms_maybe_create_table( $table_name, $sql );
