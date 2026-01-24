<?php
namespace PracticeRx\Database;

/**
 * Class Schema
 *
 * Handles database table creation and updates using modular migration files.
 */
class Schema {

	/**
	 * Create or update custom tables by running all migration files
	 */
	public static function create_tables() {
		$migrations_dir = PRACTICERX_PATH . 'includes/Database/Migrations/';
		
		if ( ! is_dir( $migrations_dir ) ) {
			return;
		}

		// Get all migration files
		$migration_files = glob( $migrations_dir . '*.php' );
		
		if ( empty( $migration_files ) ) {
			return;
		}

		// Include upgrade.php for dbDelta
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		// Run each migration file
		foreach ( $migration_files as $migration_file ) {
			include_once $migration_file;
		}
	}
}
