<?php
namespace PracticeRx\Core;

use PracticeRx\Models\Patient;
use PracticeRx\Models\Practitioner;
use PracticeRx\Models\Appointment;
use PracticeRx\Models\Encounter;

/**
 * Class DemoDataSeeder
 *
 * Generates demo data for the system.
 */
class DemoDataSeeder {

	/**
	 * Run the seeder.
	 *
	 * @return int Number of items created.
	 */
	public static function run() {
		$count = 0;

		// 1. Create Practitioner
		$practitioner_id = self::create_practitioner();
		$count++;

		// 2. Create Patients
		$patient_ids = array();
		for ( $i = 0; $i < 5; $i++ ) {
			$patient_ids[] = self::create_patient( $i );
			$count++;
		}

		// 3. Create Appointments & Encounters
		foreach ( $patient_ids as $patient_id ) {
			self::create_appointment( $patient_id, $practitioner_id );
			self::create_encounter( $patient_id, $practitioner_id );
			$count += 2;
		}

		return $count;
	}

	private static function create_practitioner() {
		// Check if exists first
		$existing = Practitioner::all( array( 'limit' => 1 ) );
		if ( ! empty( $existing ) ) {
			return $existing[0]->id;
		}

		$user_id = 1; // Assume admin is practitioner for demo
		
		return Practitioner::create( array(
			'user_id' => $user_id,
			'specialty' => 'General Practice',
			'bio' => 'Experienced GP with a focus on holistic health.',
		) );
	}

	private static function create_patient( $index ) {
		$names = array( 'John Doe', 'Jane Smith', 'Alice Johnson', 'Bob Brown', 'Charlie Davis' );
		$name = $names[ $index ] ?? 'Test Patient ' . $index;
		
		// In a real scenario, we'd create WP Users too, but for custom tables we can mock the user_id or create them.
		// For simplicity, we'll just use random IDs for user_id since our current models don't strictly enforce FK constraints on WP users table in SQL (though they should).
		$fake_user_id = 100 + $index;

		return Patient::create( array(
			'user_id' => $fake_user_id,
			'phone' => '555-010' . $index,
			'gender' => $index % 2 === 0 ? 'Male' : 'Female',
			'dob' => '1980-01-01',
			'address' => '123 Demo St, City',
		) );
	}

	private static function create_appointment( $patient_id, $practitioner_id ) {
		$start = date( 'Y-m-d H:i:s', strtotime( '+' . rand( 1, 7 ) . ' days 10:00:00' ) );
		$end   = date( 'Y-m-d H:i:s', strtotime( $start . ' +30 minutes' ) );

		return Appointment::create( array(
			'patient_id' => $patient_id,
			'practitioner_id' => $practitioner_id,
			'service_id' => 1,
			'start_time' => $start,
			'end_time' => $end,
			'status' => 'scheduled',
			'notes' => 'Routine checkup',
		) );
	}

	private static function create_encounter( $patient_id, $practitioner_id ) {
		return Encounter::create( array(
			'patient_id' => $patient_id,
			'practitioner_id' => $practitioner_id,
			'type' => 'soap',
			'content' => "Subjective: Patient reports feeling well.\nObjective: Vitals normal.\nAssessment: Healthy.\nPlan: Follow up in 6 months.",
		) );
	}
}
