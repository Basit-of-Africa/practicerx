<?php
namespace PracticeRx\Core;

/**
 * Class AdminPage
 *
 * Handles the main admin page for the React app.
 */
class AdminPage
{

	/**
	 * Initialize hooks.
	 */
	public static function init()
	{
		add_action('admin_menu', array(__CLASS__, 'add_menu_page'));
		add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_assets'));
	}

	/**
	 * Add the menu page.
	 */
	public static function add_menu_page()
	{
		add_menu_page(
			__('PracticeRx', 'practicerx'),
			__('PracticeRx', 'practicerx'),
			'read', // Capability required
			'practicerx', // Menu slug
			array(__CLASS__, 'render_page'),
			'dashicons-heart', // Icon
			30 // Position
		);
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public static function enqueue_assets($hook)
	{
		if ('toplevel_page_practicerx' !== $hook) {
			return;
		}
		$asset_path  = PRACTICERX_PATH . 'build/index.asset.php';
		$script_path = PRACTICERX_PATH . 'build/index.js';
		$style_path  = PRACTICERX_PATH . 'assets/css/app.css';

		if ( ! file_exists( $asset_path ) || ! file_exists( $script_path ) ) {
			add_action( 'admin_notices', function() {
				echo '<div class="notice notice-error"><p>' . esc_html__( 'PracticeRx build files are missing. Run `npm run build` in the plugin root.', 'practicerx' ) . '</p></div>';
			} );

			return;
		}

		$asset_file = include $asset_path;

		if ( is_array( $asset_file ) ) {
			wp_enqueue_script(
				'practicerx-app',
				PRACTICERX_URL . 'build/index.js',
				isset( $asset_file['dependencies'] ) ? $asset_file['dependencies'] : array(),
				isset( $asset_file['version'] ) ? $asset_file['version'] : PRACTICERX_VERSION,
				true
			);

			if ( file_exists( $style_path ) ) {
				wp_enqueue_style(
					'practicerx-app',
					PRACTICERX_URL . 'assets/css/app.css',
					array('wp-components'),
					isset( $asset_file['version'] ) ? $asset_file['version'] : PRACTICERX_VERSION
				);
			}

			wp_localize_script(
				'practicerx-app',
				'practicerxSettings',
				array(
					'root' => esc_url_raw(rest_url('ppms/v1/')),
					'nonce' => wp_create_nonce('wp_rest'),
				)
			);
		}
	}

	/**
	 * Render the page container.
	 */
	public static function render_page()
	{
		echo '<div id="practicerx-root"></div>';
	}
}
