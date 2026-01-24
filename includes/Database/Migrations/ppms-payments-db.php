<?php
/**
 * Payments Table Migration
 */

global $wpdb;

$charset_collate = $wpdb->get_charset_collate();
$table_name = $wpdb->prefix . 'ppms_payments';

$sql = "CREATE TABLE {$table_name} (
	id bigint(20) NOT NULL AUTO_INCREMENT,
	invoice_id bigint(20) NOT NULL,
	transaction_id varchar(255) DEFAULT '',
	gateway varchar(50) NOT NULL,
	amount decimal(10,2) NOT NULL DEFAULT 0.00,
	currency varchar(3) NOT NULL DEFAULT 'USD',
	status varchar(50) NOT NULL DEFAULT 'pending',
	payment_method varchar(50) DEFAULT '',
	notes text DEFAULT '',
	created_at datetime DEFAULT CURRENT_TIMESTAMP,
	updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY  (id),
	KEY invoice_id (invoice_id),
	KEY transaction_id (transaction_id),
	KEY status (status)
) $charset_collate;";

ppms_maybe_create_table( $table_name, $sql );
