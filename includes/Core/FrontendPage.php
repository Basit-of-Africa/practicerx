<?php
namespace PracticeRx\Core;

/**
 * Class FrontendPage
 *
 * Handles the frontend display of the PracticeRx app.
 * Uses shortcode [practicerx] to embed the app on any page.
 */
class FrontendPage
{

	/**
	 * Initialize hooks.
	 */
	public static function init()
	{
		add_shortcode('practicerx', array(__CLASS__, 'render_shortcode'));
		add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_assets'));
	}

	/**
	 * Enqueue scripts and styles on frontend pages that use the shortcode.
	 */
	public static function enqueue_assets()
	{
		global $post;
		
		// Only enqueue if shortcode is present on the page
		if (!is_a($post, 'WP_Post') || !has_shortcode($post->post_content, 'practicerx')) {
			return;
		}

		$asset_file = include(PRACTICERX_PATH . 'build/index.asset.php');

		wp_enqueue_script(
			'practicerx-app',
			PRACTICERX_URL . 'build/index.js',
			$asset_file['dependencies'],
			$asset_file['version'],
			true
		);

		// Enqueue custom frontend styles
		wp_enqueue_style(
			'practicerx-frontend',
			PRACTICERX_URL . 'assets/css/frontend.css',
			array(),
			$asset_file['version']
		);

		wp_localize_script(
			'practicerx-app',
			'practicerxSettings',
			array(
				'root' => esc_url_raw(rest_url('ppms/v1/')),
				'nonce' => wp_create_nonce('wp_rest'),
				'isFrontend' => true, // Flag to indicate frontend context
			)
		);
	}

	/**
	 * Render the shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public static function render_shortcode($atts)
	{
		$atts = shortcode_atts(array(
			'width' => '100%',
			'height' => 'auto',
		), $atts, 'practicerx');

		$style = sprintf(
			'width: %s; height: %s;',
			esc_attr($atts['width']),
			esc_attr($atts['height'])
		);

		return sprintf(
			'<div id="practicerx-root" style="%s"></div>',
			$style
		);
	}
}
