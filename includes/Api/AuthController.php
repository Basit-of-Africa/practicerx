<?php
namespace PracticeRx\Api;

use WP_REST_Server;
use PracticeRx\Models\Client;
use PracticeRx\Core\Helper;

/**
 * Class AuthController
 *
 * Provides simple token-based auth endpoints for the client portal.
 */
class AuthController extends ApiController {

    public function register_routes() {
        register_rest_route( $this->namespace, '/auth/login', array(
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array( $this, 'login' ),
                'permission_callback' => '__return_true',
            ),
        ) );

        register_rest_route( $this->namespace, '/auth/register', array(
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array( $this, 'register' ),
                'permission_callback' => '__return_true',
            ),
        ) );

        register_rest_route( $this->namespace, '/auth/logout', array(
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array( $this, 'logout' ),
                'permission_callback' => array( $this, 'check_permissions' ),
            ),
        ) );
    }

    public function login( \WP_REST_Request $request ) {
        $data = $request->get_json_params();
        $email = isset( $data['email'] ) ? sanitize_email( $data['email'] ) : '';
        $password = isset( $data['password'] ) ? $data['password'] : '';

        if ( empty( $email ) || empty( $password ) ) {
            return new \WP_Error( 'missing_credentials', __( 'Email and password required', 'practicerx' ), array( 'status' => 400 ) );
        }

        $user = get_user_by( 'email', $email );
        if ( ! $user ) {
            return new \WP_Error( 'invalid_user', __( 'Invalid credentials', 'practicerx' ), array( 'status' => 401 ) );
        }

        $creds = array(
            'user_login' => $user->user_login,
            'user_password' => $password,
            'remember' => false,
        );

        $signed_in = wp_signon( $creds, is_ssl() );
        if ( is_wp_error( $signed_in ) ) {
            return new \WP_Error( 'invalid_credentials', __( 'Invalid credentials', 'practicerx' ), array( 'status' => 401 ) );
        }

        // Create token
        $token = ppms_create_auth_token( $user->ID );

        $response = array(
            'token' => $token,
            'user'  => array(
                'ID' => $user->ID,
                'display_name' => $user->display_name,
                'user_email' => $user->user_email,
            ),
        );

        return rest_ensure_response( $response );
    }

    public function register( \WP_REST_Request $request ) {
        $data = $request->get_json_params();

        $required = array( 'email', 'first_name', 'last_name', 'password' );
        foreach ( $required as $f ) {
            if ( empty( $data[ $f ] ) ) {
                return new \WP_Error( 'missing_field', sprintf( __( '%s is required', 'practicerx' ), $f ), array( 'status' => 400 ) );
            }
        }

        if ( ! is_email( $data['email'] ) ) {
            return new \WP_Error( 'invalid_email', __( 'Invalid email address', 'practicerx' ), array( 'status' => 400 ) );
        }

        if ( email_exists( $data['email'] ) ) {
            return new \WP_Error( 'email_exists', __( 'A user with this email already exists', 'practicerx' ), array( 'status' => 400 ) );
        }

        $user_id = wp_create_user( sanitize_email( $data['email'] ), $data['password'], sanitize_email( $data['email'] ) );
        if ( is_wp_error( $user_id ) ) {
            return $user_id;
        }

        wp_update_user( array(
            'ID' => $user_id,
            'first_name' => sanitize_text_field( $data['first_name'] ),
            'last_name' => sanitize_text_field( $data['last_name'] ),
            'display_name' => sanitize_text_field( $data['first_name'] . ' ' . $data['last_name'] ),
        ) );

        $user = new \WP_User( $user_id );
        $user->set_role( 'ppms_client' );

        // Create client record
        $client_data = array_merge( $data, array( 'user_id' => $user_id ) );
        $client_id = Client::create( $client_data );

        // Create token
        $token = ppms_create_auth_token( $user_id );

        $response = array(
            'token' => $token,
            'user'  => array(
                'ID' => $user_id,
                'display_name' => $user->display_name,
                'user_email' => $user->user_email,
                'client_id' => $client_id,
            ),
        );

        return rest_ensure_response( $response );
    }

    public function logout( \WP_REST_Request $request ) {
        $auth_header = $request->get_header( 'authorization' );
        if ( $auth_header && preg_match( '/Bearer\s+(.+)/i', $auth_header, $m ) ) {
            $token = trim( $m[1] );
            ppms_revoke_auth_token( $token );
        }
        return rest_ensure_response( array( 'success' => true ) );
    }
}
