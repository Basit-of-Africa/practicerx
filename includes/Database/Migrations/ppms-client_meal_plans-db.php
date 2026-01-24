<?php
/**
 * Client Meal Plans Table Migration
 *
 * Meal plans assigned to clients
 *
 * @package PracticeRx
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;
$table_name = $wpdb->prefix . 'ppms_client_meal_plans';
$charset_collate = $wpdb->get_charset_collate();

$sql = "CREATE TABLE {$table_name} (
	id bigint(20) NOT NULL AUTO_INCREMENT,
	client_id bigint(20) NOT NULL,
	meal_plan_id bigint(20) NOT NULL,
	practitioner_id bigint(20) NOT NULL,
	start_date date NOT NULL,
	end_date date DEFAULT NULL,
	status varchar(20) DEFAULT 'active',
	customizations longtext DEFAULT '',
	notes text DEFAULT '',
	created_at datetime DEFAULT CURRENT_TIMESTAMP,
	updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	KEY client_id (client_id),
	KEY meal_plan_id (meal_plan_id),
	KEY practitioner_id (practitioner_id),
	KEY status (status)
) $charset_collate;";

ppms_maybe_create_table( $table_name, $sql );
