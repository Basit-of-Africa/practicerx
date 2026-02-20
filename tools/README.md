simulate_booking.php

Usage:

php simulate_booking.php --site="http://your-wp-site" [--practitioner=ID] [--email=invite@example.com] [--token="ID.SECRET"] [--nonce=WP_REST_NONCE]

- `--site` (required): base URL of the WordPress site (no trailing slash).
- `--practitioner`: practitioner user ID to associate the appointment with (optional).
- `--email`: invitee email address (optional).
- `--token`: Bearer token to send in Authorization header (optional).
- `--nonce`: X-WP-Nonce header value (optional).

The script will:
- Create a guest appointment via the REST API
- PATCH the appointment with an `attendees` array to simulate RSVP
- Fetch the appointment and print stored `meeting_attendees` and any linked Google event id

Note: for full calendar syncing a valid Google OAuth configuration and practitioner connection is required in PracticeRx settings.