<?php
namespace PracticeRx\Auth;

/**
 * Class RoleManager
 *
 * Manages user roles and capabilities.
 */
class RoleManager {

	/**
	 * Initialize roles and capabilities.
	 */
	public static function init() {
		// This is handled on activation, but we can add runtime checks here if needed.
	}

	/**
	 * Check if user is a practitioner.
	 *
	 * @param int $user_id User ID.
	 * @return bool
	 */
	public static function is_practitioner( $user_id = 0 ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}
		return user_can( $user_id, 'ppms_practitioner' ) || user_can( $user_id, 'administrator' );
	}

	/**
	 * Check if user is a patient.
	 *
	 * @param int $user_id User ID.
	 * @return bool
	 */
	public static function is_patient( $user_id = 0 ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}
		return user_can( $user_id, 'ppms_patient' );
	}
}
