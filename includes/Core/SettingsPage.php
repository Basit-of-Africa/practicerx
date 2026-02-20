<?php
namespace PracticeRx\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SettingsPage {

    public static function init() {
        add_action( 'admin_menu', array( __CLASS__, 'add_menu' ) );
        add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
        add_action( 'admin_post_ppms_google_oauth_start', array( __CLASS__, 'oauth_start' ) );
        add_action( 'admin_post_ppms_google_oauth_callback', array( __CLASS__, 'oauth_callback' ) );
        add_action( 'admin_post_ppms_google_oauth_disconnect', array( __CLASS__, 'oauth_disconnect' ) );
        add_action( 'admin_post_ppms_test_calendar_event', array( __CLASS__, 'oauth_test_event' ) );
    }

    public static function add_menu() {
        add_submenu_page(
            'practicerx',
            __( 'PracticeRx Settings', 'practicerx' ),
            __( 'Settings', 'practicerx' ),
            'manage_options',
            'practicerx-settings',
            array( __CLASS__, 'render_page' )
        );
    }

    public static function register_settings() {
        register_setting( 'practicerx_settings', 'ppms_google_client_id' );
        register_setting( 'practicerx_settings', 'ppms_google_client_secret' );
        register_setting( 'practicerx_settings', 'ppms_google_redirect_uri' );
        register_setting( 'practicerx_settings', 'ppms_practitioner_calendars' );

        register_setting( 'practicerx_settings', 'ppms_email_templates' );

        register_setting( 'practicerx_settings', 'ppms_twilio_account_sid' );
        register_setting( 'practicerx_settings', 'ppms_twilio_auth_token' );
        register_setting( 'practicerx_settings', 'ppms_twilio_from_number' );
        register_setting( 'practicerx_settings', 'ppms_twilio_enabled' );
    }

    public static function render_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $tokens = get_option( 'ppms_google_tokens' );
        $connected = ! empty( $tokens ) && isset( $tokens['access_token'] );

        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'PracticeRx Settings', 'practicerx' ); ?></h1>
            <form method="post" action="options.php">
                <?php settings_fields( 'practicerx_settings' ); ?>
                <?php do_settings_sections( 'practicerx_settings' ); ?>

                <h2><?php esc_html_e( 'Google Calendar', 'practicerx' ); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="ppms_google_client_id"><?php esc_html_e( 'Client ID', 'practicerx' ); ?></label></th>
                        <td><input name="ppms_google_client_id" id="ppms_google_client_id" type="text" value="<?php echo esc_attr( get_option( 'ppms_google_client_id' ) ); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="ppms_google_client_secret"><?php esc_html_e( 'Client Secret', 'practicerx' ); ?></label></th>
                        <td><input name="ppms_google_client_secret" id="ppms_google_client_secret" type="text" value="<?php echo esc_attr( get_option( 'ppms_google_client_secret' ) ); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="ppms_google_redirect_uri"><?php esc_html_e( 'Redirect URI', 'practicerx' ); ?></label></th>
                        <td><input name="ppms_google_redirect_uri" id="ppms_google_redirect_uri" type="text" value="<?php echo esc_attr( get_option( 'ppms_google_redirect_uri' ) ); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Connection', 'practicerx' ); ?></th>
                        <td>
                            <?php if ( $connected ) : ?>
                                <strong style="color:green"><?php esc_html_e( 'Connected', 'practicerx' ); ?></strong>
                                <a class="button" href="<?php echo esc_url( admin_url( 'admin-post.php?action=ppms_google_oauth_disconnect' ) ); ?>"><?php esc_html_e( 'Disconnect', 'practicerx' ); ?></a>
                            <?php else : ?>
                                <a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin-post.php?action=ppms_google_oauth_start' ) ); ?>"><?php esc_html_e( 'Authorize with Google', 'practicerx' ); ?></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Practitioner Calendars', 'practicerx' ); ?></th>
                        <td>
                            <?php
                            $pract_map = get_option( 'ppms_practitioner_calendars', array() );
                            $practitioners = \PracticeRx\Models\Practitioner::get_active();
                            if ( ! empty( $practitioners ) ) :
                                echo '<table class="widefat">';
                                echo '<thead><tr><th>' . esc_html__( 'Practitioner', 'practicerx' ) . '</th><th>' . esc_html__( 'Google Calendar ID', 'practicerx' ) . '</th></tr></thead>';
                                echo '<tbody>';
                                foreach ( $practitioners as $p ) {
                                    $val = isset( $pract_map[ $p->id ] ) ? $pract_map[ $p->id ] : '';
                                    echo '<tr><td>' . esc_html( ! empty( $p->display_name ) ? $p->display_name : ( $p->first_name . ' ' . $p->last_name ) ) . '</td><td><input type="text" name="ppms_practitioner_calendars[' . esc_attr( $p->id ) . ']" value="' . esc_attr( $val ) . '" class="regular-text" /></td></tr>';
                                }
                                echo '</tbody></table>';
                                // Quick-connect per-practitioner OAuth controls
                                echo '<p style="margin-top:12px;">' . esc_html__( 'Connect practitioner to Google (enter practitioner ID then click Connect).', 'practicerx' ) . '</p>';
                                echo '<form method="get" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
                                echo '<input type="hidden" name="action" value="ppms_google_oauth_start" />';
                                echo '<select name="practitioner_id" style="margin-right:8px;">';
                                echo '<option value="">' . esc_html__( 'Select practitioner', 'practicerx' ) . '</option>';
                                foreach ( $practitioners as $pp ) {
                                    echo '<option value="' . esc_attr( $pp->id ) . '">' . esc_html( ! empty( $pp->display_name ) ? $pp->display_name : ( $pp->first_name . ' ' . $pp->last_name ) ) . ' (ID ' . esc_html( $pp->id ) . ')</option>';
                                }
                                echo '</select>';
                                echo '<button class="button" type="submit">' . esc_html__( 'Connect', 'practicerx' ) . '</button>';
                                echo '</form>';
                            else:
                                esc_html_e( 'No practitioners found.', 'practicerx' );
                            endif;
                            ?>
                        </td>
                    </tr>
                </table>

                <h2><?php esc_html_e( 'Email Templates', 'practicerx' ); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="ppms_email_templates[appointment_client]"><?php esc_html_e( 'Appointment - Client', 'practicerx' ); ?></label></th>
                        <td><textarea name="ppms_email_templates[appointment_client]" id="ppms_email_templates[appointment_client]" rows="6" class="large-text"><?php echo esc_textarea( ( get_option( 'ppms_email_templates' )['appointment_client'] ?? '' ) ); ?></textarea></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="ppms_email_templates[appointment_practitioner]"><?php esc_html_e( 'Appointment - Practitioner', 'practicerx' ); ?></label></th>
                        <td><textarea name="ppms_email_templates[appointment_practitioner]" id="ppms_email_templates[appointment_practitioner]" rows="6" class="large-text"><?php echo esc_textarea( ( get_option( 'ppms_email_templates' )['appointment_practitioner'] ?? '' ) ); ?></textarea></td>
                    </tr>
                </table>

                <h2><?php esc_html_e( 'Twilio (SMS)', 'practicerx' ); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="ppms_twilio_account_sid"><?php esc_html_e( 'Account SID', 'practicerx' ); ?></label></th>
                        <td><input name="ppms_twilio_account_sid" id="ppms_twilio_account_sid" type="text" value="<?php echo esc_attr( get_option( 'ppms_twilio_account_sid' ) ); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="ppms_twilio_auth_token"><?php esc_html_e( 'Auth Token', 'practicerx' ); ?></label></th>
                        <td><input name="ppms_twilio_auth_token" id="ppms_twilio_auth_token" type="password" value="<?php echo esc_attr( get_option( 'ppms_twilio_auth_token' ) ); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="ppms_twilio_from_number"><?php esc_html_e( 'From Number', 'practicerx' ); ?></label></th>
                        <td><input name="ppms_twilio_from_number" id="ppms_twilio_from_number" type="text" value="<?php echo esc_attr( get_option( 'ppms_twilio_from_number' ) ); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Enable Twilio', 'practicerx' ); ?></th>
                        <td><label><input type="checkbox" name="ppms_twilio_enabled" value="1" <?php checked( 1, get_option( 'ppms_twilio_enabled', 0 ) ); ?>/> <?php esc_html_e( 'Enable SMS notifications', 'practicerx' ); ?></label></td>
                    </tr>
                </table>

                <?php submit_button(); ?>
            </form>

            <h2><?php esc_html_e( 'Calendar Tests', 'practicerx' ); ?></h2>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <?php wp_nonce_field( 'ppms_test_calendar_event', 'ppms_test_calendar_nonce' ); ?>
                <input type="hidden" name="action" value="ppms_test_calendar_event" />
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="ppms_test_practitioner"><?php esc_html_e( 'Practitioner', 'practicerx' ); ?></label></th>
                        <td>
                            <select id="ppms_test_practitioner" name="practitioner_id">
                                <option value=""><?php esc_html_e( 'Select practitioner', 'practicerx' ); ?></option>
                                <?php foreach ( $practitioners as $p ) : ?>
                                    <option value="<?php echo esc_attr( $p->id ); ?>"><?php echo esc_html( ! empty( $p->display_name ) ? $p->display_name : ( $p->first_name . ' ' . $p->last_name ) ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="ppms_test_email"><?php esc_html_e( 'Invite Email', 'practicerx' ); ?></label></th>
                        <td><input id="ppms_test_email" name="invite_email" type="email" class="regular-text" /></td>
                    </tr>
                </table>
                <?php submit_button( __( 'Send Test Event', 'practicerx' ) ); ?>
            </form>
        </div>
        <?php
    }

    public static function oauth_start() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'Forbidden', 'practicerx' ), '', array( 'response' => 403 ) );
        }

        $practitioner_id = isset( $_GET['practitioner_id'] ) ? absint( wp_unslash( $_GET['practitioner_id'] ) ) : 0;

        $auth_url = \PracticeRx\Services\Calendar\GoogleOAuth::get_auth_url( $practitioner_id );
        if ( is_wp_error( $auth_url ) ) {
            $redirect = add_query_arg( 'ppms_google_error', urlencode( $auth_url->get_error_message() ), admin_url( 'admin.php?page=practicerx-settings' ) );
            wp_redirect( $redirect );
            exit;
        }

        wp_redirect( $auth_url );
        exit;
    }

    public static function oauth_callback() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'Forbidden', 'practicerx' ), '', array( 'response' => 403 ) );
        }

        if ( empty( $_GET['code'] ) ) {
            $redirect = add_query_arg( 'ppms_google_error', urlencode( 'Missing code parameter' ), admin_url( 'admin.php?page=practicerx-settings' ) );
            wp_redirect( $redirect );
            exit;
        }

        $code = sanitize_text_field( wp_unslash( $_GET['code'] ) );

        // Try to extract practitioner id from state param (format: practitioner:ID)
        $practitioner_id = 0;
        if ( isset( $_GET['state'] ) ) {
            $state = wp_unslash( $_GET['state'] );
            if ( strpos( $state, 'practitioner:' ) === 0 ) {
                $practitioner_id = absint( substr( $state, strlen( 'practitioner:' ) ) );
            }
        }

        $res = \PracticeRx\Services\Calendar\GoogleOAuth::handle_callback( $code, $practitioner_id );
        if ( is_wp_error( $res ) ) {
            $redirect = add_query_arg( 'ppms_google_error', urlencode( $res->get_error_message() ), admin_url( 'admin.php?page=practicerx-settings' ) );
        } else {
            $redirect = add_query_arg( 'ppms_google_success', '1', admin_url( 'admin.php?page=practicerx-settings' ) );
        }

        wp_redirect( $redirect );
        exit;
    }

    public static function oauth_disconnect() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'Forbidden', 'practicerx' ), '', array( 'response' => 403 ) );
        }

        $practitioner_id = isset( $_GET['practitioner_id'] ) ? absint( wp_unslash( $_GET['practitioner_id'] ) ) : 0;
        \PracticeRx\Services\Calendar\GoogleOAuth::disconnect( $practitioner_id );
        $redirect = add_query_arg( 'ppms_google_disconnected', '1', admin_url( 'admin.php?page=practicerx-settings' ) );
        wp_redirect( $redirect );
        exit;
    }

    public static function oauth_test_event() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'Forbidden', 'practicerx' ), '', array( 'response' => 403 ) );
        }

        if ( ! isset( $_POST['ppms_test_calendar_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['ppms_test_calendar_nonce'] ), 'ppms_test_calendar_event' ) ) {
            wp_die( __( 'Invalid nonce', 'practicerx' ), '', array( 'response' => 400 ) );
        }

        $practitioner_id = isset( $_POST['practitioner_id'] ) ? absint( $_POST['practitioner_id'] ) : 0;
        $invite_email = isset( $_POST['invite_email'] ) ? sanitize_email( wp_unslash( $_POST['invite_email'] ) ) : '';

        $practitioner = null;
        if ( $practitioner_id ) {
            $practitioner = \PracticeRx\Models\Practitioner::get( $practitioner_id );
        }

        // Create a simple test appointment object
        $now = time();
        $start = date( 'c', $now + 300 ); // 5 minutes from now
        $end = date( 'c', $now + 1200 ); // +15 minutes

        $appt = new \stdClass();
        $appt->id = 0;
        $appt->title = __( 'PracticeRx Test Event', 'practicerx' );
        $appt->notes = __( 'This is a test calendar event generated from PracticeRx settings.', 'practicerx' );
        $appt->start_time = $start;
        $appt->end_time = $end;

        $calendar_map = get_option( 'ppms_practitioner_calendars', array() );
        $calendar_id = $practitioner && ! empty( $calendar_map[ $practitioner_id ] ) ? $calendar_map[ $practitioner_id ] : 'primary';

        $attendees = array();
        if ( $invite_email && is_email( $invite_email ) ) {
            $attendees[] = $invite_email;
        }
        if ( $practitioner && ! empty( $practitioner->user_email ) ) {
            $attendees[] = $practitioner->user_email;
        }

        $res = \PracticeRx\Services\Calendar\GoogleCalendar::create_event( $appt, $calendar_id, $attendees );

        $redirect = admin_url( 'admin.php?page=practicerx-settings' );
        if ( is_wp_error( $res ) ) {
            $redirect = add_query_arg( 'ppms_test_error', urlencode( $res->get_error_message() ), $redirect );
        } else {
            $redirect = add_query_arg( 'ppms_test_success', '1', $redirect );
        }

        wp_redirect( $redirect );
        exit;
    }
}
