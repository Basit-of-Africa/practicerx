<?php
// Migration: add meeting_event_id and meeting_attendees to appointments table
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;
$table = ppms_get_table( 'ppms_appointments' );

// Add meeting_event_id
ppms_maybe_add_column( $table, 'meeting_event_id', "ALTER TABLE `{$table}` ADD `meeting_event_id` varchar(255) DEFAULT ''" );

// Add meeting_attendees (JSON)
ppms_maybe_add_column( $table, 'meeting_attendees', "ALTER TABLE `{$table}` ADD `meeting_attendees` text DEFAULT ''" );

// Add meeting_link if missing (some installs might already have it)
ppms_maybe_add_column( $table, 'meeting_link', "ALTER TABLE `{$table}` ADD `meeting_link` varchar(255) DEFAULT ''" );
