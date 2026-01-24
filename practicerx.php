<?php
/**
 * Plugin Name: PracticeRx
 * Plugin URI:  https://practicerx.com
 * Description: A complete private-practice management system for WordPress.
 * Version:     1.0.0
 * Author:      PracticeRx Team
 * Author URI:  https://practicerx.com
 * Text Domain: practicerx
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define Plugin Constants
define( 'PRACTICERX_VERSION', '1.0.0' );
define( 'PRACTICERX_PATH', plugin_dir_path( __FILE__ ) );
define( 'PRACTICERX_URL', plugin_dir_url( __FILE__ ) );
define( 'PRACTICERX_BASENAME', plugin_basename( __FILE__ ) );

// Autoloader
spl_autoload_register( function ( $class ) {
	$prefix = 'PracticeRx\\';
	$base_dir = PRACTICERX_PATH . 'includes/';

	$len = strlen( $prefix );
	if ( strncmp( $prefix, $class, $len ) !== 0 ) {
		return;
	}

	$relative_class = substr( $class, $len );
	$file = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

	if ( file_exists( $file ) ) {
		require $file;
	}
} );

// Load global helper functions
require_once PRACTICERX_PATH . 'includes/helpers.php';

/**
 * Main Plugin Class
 */
final class PracticeRx {

	/**
	 * Instance of this class.
	 *
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * Return an instance of this class.
	 *
	 * @return object A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initialize the plugin.
	 */
	private function __construct() {
		$this->includes();
		$this->init_hooks();
	}

	/**
	 * Include required files.
	 */
	private function includes() {
		// Core includes are handled by Composer autoloader
	}

	/**
	 * Initialize hooks.
	 */
	private function init_hooks() {
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
		
		add_action( 'plugins_loaded', array( $this, 'on_plugins_loaded' ) );
		add_action( 'rest_api_init', array( $this, 'init_rest_api' ) );
	}

	/**
	 * Activation hook.
	 */
	public function activate() {
		\PracticeRx\Core\Installer::install();
	}

	/**
	 * Deactivation hook.
	 */
	public function deactivate() {
		// Cleanup if needed
	}

	/**
	 * Plugins loaded action.
	 */
	public function on_plugins_loaded() {
		// Load text domain
		load_plugin_textdomain( 'practicerx', false, dirname( PRACTICERX_BASENAME ) . '/languages' );
		
		// Initialize components
		\PracticeRx\Core\UserMeta::init();
		\PracticeRx\Auth\RoleManager::init();
		\PracticeRx\Core\AdminPage::init();
		
		// Initialize filter handler for modular features
		( new \PracticeRx\Core\FilterHandler() )->init();
	}

	/**
	 * Initialize REST API.
	 */
	public function init_rest_api() {
		$controllers = array(
			new \PracticeRx\Api\AppointmentsController(),
			new \PracticeRx\Api\ClientsController(),
			new \PracticeRx\Api\PatientsController(),
			new \PracticeRx\Api\PractitionersController(),
			new \PracticeRx\Api\EncountersController(),
			new \PracticeRx\Api\EncounterReportsController(),
			new \PracticeRx\Api\InvoicesController(),
			new \PracticeRx\Api\ServicesController(),
			new \PracticeRx\Api\ReportsController(),
			new \PracticeRx\Api\SystemController(),
		);

		foreach ( $controllers as $controller ) {
			$controller->register_routes();
		}
	}
}

/**
 * Returns the main instance of PracticeRx.
 *
 * @return PracticeRx
 */
function practicerx() {
	return PracticeRx::get_instance();
}

// Kick it off
practicerx();
