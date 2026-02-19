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

        register_rest_route( $this->namespace, '/auth/refresh', array(
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array( $this, 'refresh' ),
                'permission_callback' => array( $this, 'check_permissions' ),
            ),
        ) );

        register_rest_route( $this->namespace, '/auth/tokens', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array( $this, 'list_tokens' ),
                'permission_callback' => array( $this, 'check_permissions' ),
            ),
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array( $this, 'revoke_token' ),
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

    /**
     * Refresh the current auth token: issue a new token and revoke the old one.
     */
    public function refresh( \WP_REST_Request $request ) {
        // check_permissions has already set the current user
        $current_user_id = get_current_user_id();
        if ( ! $current_user_id ) {
            return new \WP_Error( 'not_authenticated', __( 'Not authenticated', 'practicerx' ), array( 'status' => 401 ) );
        }

        $auth_header = $request->get_header( 'authorization' );
        $old_token = null;
        if ( $auth_header && preg_match( '/Bearer\s+(.+)/i', $auth_header, $m ) ) {
            $old_token = trim( $m[1] );
        }

        // Create new token and revoke old one
        $new_token = ppms_create_auth_token( $current_user_id );
        if ( $old_token ) {
            ppms_revoke_auth_token( $old_token );
        }

        return rest_ensure_response( array( 'token' => $new_token ) );
    }

    /**
     * List tokens for the current user (admins see all tokens).
     */
    public function list_tokens( \WP_REST_Request $request ) {
        global $wpdb;

        $current_user = get_current_user_id();
        $is_admin = current_user_can( 'manage_options' );

        $option_table = $wpdb->options;
        $rows = $wpdb->get_results( $wpdb->prepare( "SELECT option_name, option_value FROM {$option_table} WHERE option_name LIKE %s", 'ppms_token_%' ) );

        $out = array();
        foreach ( $rows as $row ) {
            $name = $row->option_name;
            $suffix = substr( $name, strlen( 'ppms_token_' ) );
            $value = maybe_unserialize( $row->option_value );

            $record = array(
                'key' => $name,
                'id'  => $suffix,
                'is_legacy' => true,
                'user_id' => null,
                'exp' => null,
                'created' => null,
            );

            if ( is_array( $value ) ) {
                if ( isset( $value['secret_hash'] ) ) {
                    $record['is_legacy'] = false;
                    $record['user_id'] = isset( $value['user_id'] ) ? absint( $value['user_id'] ) : null;
                    $record['exp'] = isset( $value['exp'] ) ? intval( $value['exp'] ) : null;
                    $record['created'] = isset( $value['created'] ) ? intval( $value['created'] ) : null;
                } else {
                    // legacy option: user_id in value, token is in id (suffix)
                    $record['user_id'] = isset( $value['user_id'] ) ? absint( $value['user_id'] ) : null;
                    $record['exp'] = isset( $value['exp'] ) ? intval( $value['exp'] ) : null;
                }
            }

            // Only include token if admin or owner
            if ( $is_admin || ( $record['user_id'] && $record['user_id'] === $current_user ) ) {
                // mask id for safety when legacy (show last 6 chars)
                if ( $record['is_legacy'] ) {
                    $record['masked'] = substr( $suffix, -6 );
                }
                $out[] = $record;
            }
        }

        return rest_ensure_response( $out );
    }

    /**
     * Revoke a token. Accepts { token: "<id.secret>" } or legacy token string.
     */
    public function revoke_token( \WP_REST_Request $request ) {
        $data = $request->get_json_params();
        $token = isset( $data['token'] ) ? sanitize_text_field( $data['token'] ) : '';
        if ( empty( $token ) ) {
            return new \WP_Error( 'missing_token', __( 'Token is required', 'practicerx' ), array( 'status' => 400 ) );
        }

        $user_id = get_current_user_id();
        // Only allow revoking tokens owned by current user unless admin
        $is_admin = current_user_can( 'manage_options' );

        // Determine owner: try verify (returns user id) or lookup legacy option
        $owner = ppms_verify_auth_token( $token );
        if ( ! $owner ) {
            // If verify fails, also check legacy option by name
            $legacy_key = 'ppms_token_' . sanitize_text_field( $token );
            $legacy = get_option( $legacy_key );
            if ( $legacy && is_array( $legacy ) && isset( $legacy['user_id'] ) ) {
                $owner = absint( $legacy['user_id'] );
            }
        }

        if ( ! $owner ) {
            return new \WP_Error( 'token_not_found', __( 'Token not found', 'practicerx' ), array( 'status' => 404 ) );
        }

        if ( ! $is_admin && $owner !== $user_id ) {
            return new \WP_Error( 'forbidden', __( 'Not allowed to revoke this token', 'practicerx' ), array( 'status' => 403 ) );
        }

        $ok = ppms_revoke_auth_token( $token );
        return rest_ensure_response( array( 'success' => (bool) $ok ) );
    }
}
