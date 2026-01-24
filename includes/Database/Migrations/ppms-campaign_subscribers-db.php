<?php
/**
 * Campaign Subscribers Table Migration
 *
 * Clients enrolled in email campaigns
 *
 * @package PracticeRx
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;
$table_name = $wpdb->prefix . 'ppms_campaign_subscribers';
$charset_collate = $wpdb->get_charset_collate();

$sql = "CREATE TABLE {$table_name} (
	id bigint(20) NOT NULL AUTO_INCREMENT,
	campaign_id bigint(20) NOT NULL,
	client_id bigint(20) NOT NULL,
	status varchar(20) DEFAULT 'active',
	current_step int(11) DEFAULT 0,
	started_at datetime DEFAULT CURRENT_TIMESTAMP,
	completed_at datetime DEFAULT NULL,
	PRIMARY KEY (id),
	KEY campaign_id (campaign_id),
	KEY client_id (client_id),
	KEY status (status),
	UNIQUE KEY unique_subscription (campaign_id, client_id)
) $charset_collate;";

ppms_maybe_create_table( $table_name, $sql );
