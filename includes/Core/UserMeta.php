<?php
namespace PracticeRx\Core;

use PracticeRx\Models\Practitioner;
use PracticeRx\Models\Patient;
use PracticeRx\Auth\RoleManager;

/**
 * Class UserMeta
 *
 * Handles user meta fields and syncing with custom tables.
 */
class UserMeta {

	/**
	 * Initialize hooks.
	 */
	public static function init() {
		add_action( 'user_register', array( __CLASS__, 'on_user_register' ) );
		add_action( 'profile_update', array( __CLASS__, 'on_profile_update' ) );
	}

	/**
	 * Handle user registration.
	 *
	 * @param int $user_id User ID.
	 */
	public static function on_user_register( $user_id ) {
		self::sync_user_data( $user_id );
	}

	/**
	 * Handle profile update.
	 *
	 * @param int $user_id User ID.
	 */
	public static function on_profile_update( $user_id ) {
		self::sync_user_data( $user_id );
	}

	/**
	 * Sync WP User data to custom tables.
	 *
	 * @param int $user_id User ID.
	 */
	private static function sync_user_data( $user_id ) {
		if ( RoleManager::is_practitioner( $user_id ) ) {
			$exists = Practitioner::get_by_user_id( $user_id );
			if ( ! $exists ) {
				Practitioner::create( array( 'user_id' => $user_id ) );
			}
		}

		if ( RoleManager::is_patient( $user_id ) ) {
			$exists = Patient::get_by_user_id( $user_id );
			if ( ! $exists ) {
				Patient::create( array( 'user_id' => $user_id ) );
			}
		}
	}
}
