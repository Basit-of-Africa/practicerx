<?php
/**
 * Filter Handler
 * 
 * Auto-loads and initializes all filter classes for modular feature organization.
 */

namespace PracticeRx\Core;

class FilterHandler {

	/**
	 * Initialize all filters
	 */
	public function init() {
		$filters_path = PRACTICERX_PATH . 'includes/Filters/';
		
		if ( ! is_dir( $filters_path ) ) {
			return;
		}

		$filter_files = scandir( $filters_path );
		
		if ( empty( $filter_files ) ) {
			return;
		}

		foreach ( $filter_files as $filter_file ) {
			if ( $filter_file === '.' || $filter_file === '..' ) {
				continue;
			}

			if ( pathinfo( $filter_file, PATHINFO_EXTENSION ) === 'php' ) {
				$filter_class = 'PracticeRx\\Filters\\' . pathinfo( $filter_file, PATHINFO_FILENAME );
				
				if ( class_exists( $filter_class ) ) {
					new $filter_class();
				}
			}
		}
	}
}
