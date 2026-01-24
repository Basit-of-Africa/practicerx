<?php
namespace PracticeRx\Api;

use WP_REST_Controller;
use WP_REST_Server;

/**
 * Class ApiController
 *
 * Base class for API controllers.
 */
class ApiController extends WP_REST_Controller {

	/**
	 * Namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'ppms/v1';

	/**
	 * Register routes.
	 */
	public function register_routes() {
		// Override in child classes
	}

	/**
	 * Check permissions.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return bool|\WP_Error
	 */
	public function check_permissions( $request ) {
		if ( ! current_user_can( 'read' ) ) {
			return new \WP_Error( 'rest_forbidden', __( 'You cannot view this resource.', 'practicerx' ), array( 'status' => 403 ) );
		}
		return true;
	}
}
