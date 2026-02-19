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
		// If already authenticated via normal WP login/cookie
		if ( current_user_can( 'read' ) ) {
			return true;
		}

		// Accept Bearer token for frontend clients: Authorization: Bearer <token>
		$auth_header = '';
		if ( is_a( $request, '\\WP_REST_Request' ) ) {
			$auth_header = $request->get_header( 'authorization' );
		}

		if ( $auth_header ) {
			if ( preg_match( '/Bearer\s+(.+)/i', $auth_header, $m ) ) {
				$token = trim( $m[1] );
				$user_id = \ppms_verify_auth_token( $token );
				if ( $user_id ) {
					// Set the current user for the request
					wp_set_current_user( $user_id );
					return true;
				}
			}
		}

		return new \WP_Error( 'rest_forbidden', __( 'You cannot view this resource.', 'practicerx' ), array( 'status' => 403 ) );
	}
}
