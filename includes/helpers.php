<?php
/**
 * Global Helper Functions
 * 
 * Lightweight utility functions for PracticeRx plugin.
 * These are global functions for quick access throughout the plugin.
 */

use PracticeRx\Core\Constants;
use PracticeRx\Auth\RoleManager;

/**
 * Get plugin option
 *
 * @param string $name Option name (without ppms_ prefix)
 * @param mixed  $default Default value if option doesn't exist
 * @return mixed
 */
function ppms_get_option( $name, $default = false ) {
	return get_option( 'ppms_' . $name, $default );
}

/**
 * Update plugin option
 *
 * @param string $name Option name (without ppms_ prefix)
 * @param mixed  $value Option value
 * @return bool
 */
function ppms_update_option( $name, $value ) {
	return update_option( 'ppms_' . $name, $value );
}

/**
 * Delete plugin option
 *
 * @param string $name Option name (without ppms_ prefix)
 * @return bool
 */
function ppms_delete_option( $name ) {
	return delete_option( 'ppms_' . $name );
}

/**
 * Get full table name with WordPress prefix
 *
 * @param string $table_name Table name without prefix
 * @return string
 */
function ppms_get_table( $table_name ) {
	global $wpdb;
	return $wpdb->prefix . $table_name;
}

/**
 * Check if current user is practitioner
 *
 * @param int $user_id User ID (0 for current user)
 * @return bool
 */
function ppms_is_practitioner( $user_id = 0 ) {
	return RoleManager::is_practitioner( $user_id );
}

/**
 * Check if current user is patient
 *
 * @param int $user_id User ID (0 for current user)
 * @return bool
 */
function ppms_is_patient( $user_id = 0 ) {
	return RoleManager::is_patient( $user_id );
}

/**
 * Check if user has specific capability
 *
 * @param string $capability Capability to check
 * @param int    $user_id User ID (0 for current user)
 * @return bool
 */
function ppms_user_can( $capability, $user_id = 0 ) {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}
	return user_can( $user_id, $capability );
}

/**
 * Format date for display
 *
 * @param string $date Date string
 * @param string $format Date format (defaults to WordPress setting)
 * @return string
 */
function ppms_format_date( $date, $format = '' ) {
	if ( empty( $format ) ) {
		$format = get_option( 'date_format' );
	}
	return date_i18n( $format, strtotime( $date ) );
}

/**
 * Format time for display
 *
 * @param string $time Time string
 * @param string $format Time format (defaults to WordPress setting)
 * @return string
 */
function ppms_format_time( $time, $format = '' ) {
	if ( empty( $format ) ) {
		$format = get_option( 'time_format' );
	}
	return date_i18n( $format, strtotime( $time ) );
}

/**
 * Format datetime for display
 *
 * @param string $datetime Datetime string
 * @return string
 */
function ppms_format_datetime( $datetime ) {
	$date_format = get_option( 'date_format' );
	$time_format = get_option( 'time_format' );
	return date_i18n( "$date_format $time_format", strtotime( $datetime ) );
}

/**
 * Format currency amount
 *
 * @param float  $amount Amount to format
 * @param string $currency Currency code
 * @return string
 */
function ppms_format_currency( $amount, $currency = 'USD' ) {
	$currency_symbols = array(
		'USD' => '$',
		'EUR' => '€',
		'GBP' => '£',
		'JPY' => '¥',
		'INR' => '₹',
		'NGN' => '₦',
		'ZAR' => 'R',
	);

	$symbol = isset( $currency_symbols[ $currency ] ) ? $currency_symbols[ $currency ] : $currency;
	return $symbol . number_format( $amount, 2 );
}

/**
 * Sanitize appointment status
 *
 * @param string $status Status to sanitize
 * @return string
 */
function ppms_sanitize_status( $status ) {
	$valid_statuses = array_keys( Constants::get_appointment_statuses() );
	return in_array( $status, $valid_statuses, true ) ? $status : Constants::APPOINTMENT_STATUS_SCHEDULED;
}

/**
 * Get current user role
 *
 * @return string|null
 */
function ppms_get_user_role() {
	$user = wp_get_current_user();
	if ( ppms_is_practitioner() ) {
		return Constants::ROLE_PRACTITIONER;
	}
	if ( ppms_is_patient() ) {
		return Constants::ROLE_PATIENT;
	}
	if ( in_array( 'administrator', $user->roles, true ) ) {
		return 'administrator';
	}
	return null;
}

/**
 * Create database table if it doesn't exist
 *
 * @param string $table_name Full table name with prefix
 * @param string $sql CREATE TABLE SQL statement
 * @return bool
 */
function ppms_maybe_create_table( $table_name, $sql ) {
	global $wpdb;
	
	if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) !== $table_name ) {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
		return true;
	}
	
	return false;
}

/**
 * Add column to table if it doesn't exist
 *
 * @param string $table_name Table name
 * @param string $column_name Column name
 * @param string $sql ALTER TABLE SQL statement
 * @return bool
 */
function ppms_maybe_add_column( $table_name, $column_name, $sql ) {
	global $wpdb;
	
	$column_exists = $wpdb->get_results( 
		$wpdb->prepare(
			"SHOW COLUMNS FROM `{$table_name}` LIKE %s",
			$column_name
		)
	);
	
	if ( empty( $column_exists ) ) {
		$wpdb->query( $sql );
		return true;
	}
	
	return false;
}

/**
 * Log debug message (only when WP_DEBUG is enabled)
 *
 * @param string $message Message to log
 * @param string $context Context/category
 * @return void
 */
function ppms_log( $message, $context = 'general' ) {
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
		error_log( sprintf( '[PracticeRx] [%s] %s', $context, $message ) );
	}
}

/**
 * Send JSON response
 *
 * @param bool   $success Success status
 * @param mixed  $data Response data
 * @param string $message Response message
 * @param int    $status_code HTTP status code
 * @return void
 */
function ppms_send_json( $success, $data = array(), $message = '', $status_code = 200 ) {
	$response = array(
		'status'  => $success,
		'message' => $message,
		'data'    => $data,
	);
	
	wp_send_json( $response, $status_code );
}

/**
 * Create a simple auth token for a user and store mapping in options.
 *
 * @param int $user_id
 * @param int $ttl Seconds until expiration (default 1 day)
 * @return string Token
 */
function ppms_create_auth_token( $user_id, $ttl = 86400 ) {
	$token = wp_generate_password( 40, false, true );
	$key = 'ppms_token_' . $token;
	$data = array(
		'user_id' => absint( $user_id ),
		'exp'     => time() + absint( $ttl ),
	);
	add_option( $key, $data );
	return $token;
}

/**
 * Verify auth token and return user_id or false.
 *
 * @param string $token
 * @return int|false
 */
function ppms_verify_auth_token( $token ) {
	if ( empty( $token ) ) {
		return false;
	}
	$key = 'ppms_token_' . sanitize_text_field( $token );
	$data = get_option( $key );
	if ( ! $data || ! isset( $data['user_id'] ) ) {
		return false;
	}
	if ( isset( $data['exp'] ) && time() > $data['exp'] ) {
		// expired, remove
		delete_option( $key );
		return false;
	}
	return absint( $data['user_id'] );
}

/**
 * Revoke an auth token.
 *
 * @param string $token
 * @return bool
 */
function ppms_revoke_auth_token( $token ) {
	if ( empty( $token ) ) {
		return false;
	}
	$key = 'ppms_token_' . sanitize_text_field( $token );
	return delete_option( $key );
}
