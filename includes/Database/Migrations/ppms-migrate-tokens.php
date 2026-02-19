<?php
/**
 * Migration: Convert legacy auth token options to hashed id.secret entries.
 *
 * This migration creates new token options keyed by a short id and storing
 * a hashed secret (`secret_hash`) while keeping legacy options intact to
 * avoid breaking existing clients. Admins can later revoke legacy entries
 * after clients rotate.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;

$like = $wpdb->esc_like( 'ppms_token_' ) . '%';
$option_table = $wpdb->options;

$rows = $wpdb->get_results( $wpdb->prepare( "SELECT option_name, option_value FROM {$option_table} WHERE option_name LIKE %s", 'ppms_token_%' ) );

if ( ! empty( $rows ) ) {
    foreach ( $rows as $row ) {
        $opt_name = $row->option_name;
        $suffix = substr( $opt_name, strlen( 'ppms_token_' ) );

        // Unserialize value safely
        $opt_value = maybe_unserialize( $row->option_value );

        // If this option looks like a legacy token (no secret_hash stored), create a new id-based entry
        if ( is_array( $opt_value ) && ! isset( $opt_value['secret_hash'] ) ) {
            // raw token is stored as the option name suffix
            $raw_token = $suffix;

            // generate a short id for the migrated entry
            $id = wp_generate_password( 12, false, false );
            $new_key = 'ppms_token_' . $id;

            // Do not overwrite if new key already exists
            if ( get_option( $new_key ) === false ) {
                $new_data = array(
                    'user_id'     => isset( $opt_value['user_id'] ) ? absint( $opt_value['user_id'] ) : 0,
                    'secret_hash' => wp_hash_password( $raw_token ),
                    'exp'         => isset( $opt_value['exp'] ) ? $opt_value['exp'] : 0,
                    'migrated_from' => $opt_name,
                    'created'     => time(),
                );
                add_option( $new_key, $new_data );
            }
        }
    }
}
