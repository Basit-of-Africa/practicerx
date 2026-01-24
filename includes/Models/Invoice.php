<?php
namespace PracticeRx\Models;

use PracticeRx\Core\Constants;

/**
 * Class Invoice
 *
 * Model for Invoice data.
 */
class Invoice extends AbstractModel {

	/**
	 * Table name (without prefix).
	 *
	 * @var string
	 */
	protected static $table = 'ppms_invoices';

	/**
	 * Get invoices by patient ID.
	 *
	 * @param int $patient_id Patient ID
	 * @return array
	 */
	public static function get_by_patient( $patient_id ) {
		return self::find_by( array( 'patient_id' => $patient_id ), array( 'order_by' => 'created_at', 'order' => 'DESC' ) );
	}

	/**
	 * Get invoices by practitioner ID.
	 *
	 * @param int $practitioner_id Practitioner ID
	 * @return array
	 */
	public static function get_by_practitioner( $practitioner_id ) {
		return self::find_by( array( 'practitioner_id' => $practitioner_id ), array( 'order_by' => 'created_at', 'order' => 'DESC' ) );
	}

	/**
	 * Get invoice by number.
	 *
	 * @param string $invoice_number Invoice number
	 * @return object|null
	 */
	public static function get_by_number( $invoice_number ) {
		$results = self::find_by( array( 'invoice_number' => $invoice_number ), array( 'limit' => 1 ) );
		return ! empty( $results ) ? $results[0] : null;
	}

	/**
	 * Get pending invoices for a patient.
	 *
	 * @param int $patient_id Patient ID
	 * @return array
	 */
	public static function get_pending_by_patient( $patient_id ) {
		return self::find_by(
			array(
				'patient_id' => $patient_id,
				'status'     => Constants::PAYMENT_STATUS_PENDING,
			),
			array(
				'order_by' => 'due_date',
				'order'    => 'ASC',
			)
		);
	}

	/**
	 * Generate unique invoice number.
	 *
	 * @return string
	 */
	public static function generate_invoice_number() {
		$prefix = 'INV';
		$timestamp = time();
		$random = wp_rand( 1000, 9999 );
		return sprintf( '%s-%s-%s', $prefix, $timestamp, $random );
	}
}
