<?php
/**
 * Recipes Table Migration
 *
 * Recipe library for meal plans
 *
 * @package PracticeRx
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;
$table_name = $wpdb->prefix . 'ppms_recipes';
$charset_collate = $wpdb->get_charset_collate();

$sql = "CREATE TABLE {$table_name} (
	id bigint(20) NOT NULL AUTO_INCREMENT,
	practitioner_id bigint(20) DEFAULT NULL,
	title varchar(255) NOT NULL,
	description text DEFAULT '',
	image_url varchar(500) DEFAULT '',
	meal_type varchar(50) DEFAULT 'lunch',
	prep_time int(11) DEFAULT 0,
	cook_time int(11) DEFAULT 0,
	servings int(11) DEFAULT 1,
	calories int(11) DEFAULT 0,
	protein decimal(5,2) DEFAULT 0.00,
	carbs decimal(5,2) DEFAULT 0.00,
	fats decimal(5,2) DEFAULT 0.00,
	ingredients longtext DEFAULT '',
	instructions longtext DEFAULT '',
	tags text DEFAULT '',
	is_public tinyint(1) DEFAULT 0,
	created_at datetime DEFAULT CURRENT_TIMESTAMP,
	updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	KEY practitioner_id (practitioner_id),
	KEY meal_type (meal_type),
	KEY is_public (is_public)
) $charset_collate;";

ppms_maybe_create_table( $table_name, $sql );
