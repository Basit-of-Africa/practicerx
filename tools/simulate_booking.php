<?php
/**
 * CLI helper to simulate a booking and RSVP update against a running WP site.
 * Usage (from command line):
 * php tools/simulate_booking.php --site="http://local.test" --practitioner=2 --email=test@example.com --token=ID.SECRET
 */

$options = getopt( '', array( 'site:', 'practitioner::', 'email::', 'token::', 'nonce::' ) );
if ( empty( $options['site'] ) ) {
    echo "Usage: php simulate_booking.php --site=SITE_URL [--practitioner=ID] [--email=EMAIL] [--token=ID.SECRET] [--nonce=NONCE]\n";
    exit(1);
}
$site = rtrim( $options['site'], '/' );
$practitioner = isset( $options['practitioner'] ) ? intval( $options['practitioner'] ) : 0;
$email = isset( $options['email'] ) ? $options['email'] : 'guest+' . time() . '@example.com';
$token = isset( $options['token'] ) ? $options['token'] : '';
$nonce = isset( $options['nonce'] ) ? $options['nonce'] : '';

function http_request( $method, $url, $body = null, $token = '', $nonce = '' ) {
    $ch = curl_init();
    $headers = array( 'Content-Type: application/json' );
    if ( $token ) {
        $headers[] = 'Authorization: Bearer ' . $token;
    }
    if ( $nonce ) {
        $headers[] = 'X-WP-Nonce: ' . $nonce;
    }
    curl_setopt( $ch, CURLOPT_URL, $url );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
    curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, $method );
    if ( $body !== null ) {
        curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $body ) );
    }
    $res = curl_exec( $ch );
    $code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
    if ( curl_errno( $ch ) ) {
        $err = curl_error( $ch );
        curl_close( $ch );
        return array( 'error' => $err );
    }
    curl_close( $ch );
    $decoded = json_decode( $res, true );
    return array( 'code' => $code, 'body' => $decoded, 'raw' => $res );
}

$start = date( 'c', time() + 3600 );
$end = date( 'c', time() + 5400 );
$payload = array(
    'practitioner_id' => $practitioner,
    'start_time' => $start,
    'end_time' => $end,
    'client_name' => 'CLI Test',
    'client_email' => $email,
    'notes' => 'Created by simulate_booking.php'
);

echo "Creating appointment on {$site}...\n";
$res = http_request( 'POST', $site . '/wp-json/ppms/v1/appointments', $payload, $token, $nonce );
if ( isset( $res['error'] ) ) {
    echo "Request error: " . $res['error'] . "\n";
    exit(2);
}

if ( $res['code'] < 200 || $res['code'] >= 300 ) {
    echo "Create failed (HTTP {$res['code']}):\n" . print_r( $res['body'], true ) . "\n";
    exit(3);
}

// Response uses Helper::format_response shape: {status,message,data}
$body = $res['body'];
if ( isset( $body['status'] ) && $body['status'] ) {
    $appt = $body['data'];
    $appt_id = isset( $appt['id'] ) ? $appt['id'] : ( isset( $appt['ID'] ) ? $appt['ID'] : 0 );
    echo "Appointment created with id: {$appt_id}\n";
} else {
    echo "Unexpected response:\n" . print_r( $body, true ) . "\n";
    exit(4);
}

// Simulate RSVP update: attendee responds YES
$attendees = array();
$attendees[] = array( 'email' => $email, 'responseStatus' => 'accepted' );
if ( $practitioner ) {
    // we can't easily fetch practitioner's email; include a placeholder attendee
    $attendees[] = array( 'email' => 'practitioner+' . $practitioner . '@example.com', 'responseStatus' => 'accepted' );
}

echo "Updating appointment with RSVP statuses...\n";
$res2 = http_request( 'PATCH', $site . '/wp-json/ppms/v1/appointments/' . $appt_id, array( 'attendees' => $attendees ), $token, $nonce );
if ( isset( $res2['error'] ) ) {
    echo "Request error: " . $res2['error'] . "\n";
    exit(5);
}

if ( $res2['code'] < 200 || $res2['code'] >= 300 ) {
    echo "Update failed (HTTP {$res2['code']}):\n" . print_r( $res2['body'], true ) . "\n";
    exit(6);
}

echo "RSVP update response: " . json_encode( $res2['body'] ) . "\n";

// Fetch appointment to verify attendees stored
echo "Fetching appointment to verify attendee storage...\n";
$res3 = http_request( 'GET', $site . '/wp-json/ppms/v1/appointments/' . $appt_id, null, $token, $nonce );
if ( isset( $res3['error'] ) ) {
    echo "Request error: " . $res3['error'] . "\n";
    exit(7);
}

if ( $res3['code'] < 200 || $res3['code'] >= 300 ) {
    echo "Fetch failed (HTTP {$res3['code']}):\n" . print_r( $res3['body'], true ) . "\n";
    exit(8);
}

$appt_data = $res3['body'];
if ( isset( $appt_data['meeting_attendees'] ) ) {
    echo "Stored meeting_attendees: " . print_r( $appt_data['meeting_attendees'], true ) . "\n";
} elseif ( isset( $appt_data['data']['meeting_attendees'] ) ) {
    echo "Stored meeting_attendees: " . print_r( $appt_data['data']['meeting_attendees'], true ) . "\n";
} else {
    echo "No meeting_attendees field found in appointment response.\n";
}

// Report potential calendar/event presence
$ev = null;
if ( isset( $appt_data['data']['meeting_event_id'] ) ) {
    $ev = $appt_data['data']['meeting_event_id'];
}
if ( isset( $appt_data['meeting_event_id'] ) ) {
    $ev = $appt_data['meeting_event_id'];
}
if ( $ev ) {
    echo "Appointment linked event id: {$ev}\n";
} else {
    echo "No linked Google event was found on the appointment. If you expected one, ensure Google OAuth tokens are configured.\n";
}

echo "Simulation complete.\n";
