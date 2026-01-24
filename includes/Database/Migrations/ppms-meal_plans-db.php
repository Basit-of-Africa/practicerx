<?php
/**
 * Meal Plans Table Migration
 *
 * Meal planning templates
 *
 * @package PracticeRx
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;
$table_name = $wpdb->prefix . 'ppms_meal_plans';
$charset_collate = $wpdb->get_charset_collate();

$sql = "CREATE TABLE {$table_name} (
	id bigint(20) NOT NULL AUTO_INCREMENT,
	practitioner_id bigint(20) NOT NULL,
	title varchar(255) NOT NULL,
	description text DEFAULT '',
	plan_type varchar(50) DEFAULT 'weekly',
	duration_days int(11) DEFAULT 7,
	calories_target int(11) DEFAULT 0,
	macros longtext DEFAULT '',
	meals longtext NOT NULL,
	is_template tinyint(1) DEFAULT 1,
	tags text DEFAULT '',
	created_at datetime DEFAULT CURRENT_TIMESTAMP,
	updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	KEY practitioner_id (practitioner_id),
	KEY plan_type (plan_type),
	KEY is_template (is_template)
) $charset_collate;";

ppms_maybe_create_table( $table_name, $sql );
