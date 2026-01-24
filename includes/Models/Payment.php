<?php
namespace PracticeRx\Models;

/**
 * Class Payment
 *
 * Model for Payment data.
 */
class Payment extends AbstractModel {

	/**
	 * Table name (without prefix).
	 *
	 * @var string
	 */
	protected static $table = 'ppms_payments';

	/**
	 * Get payments by invoice ID.
	 *
	 * @param int $invoice_id Invoice ID
	 * @return array
	 */
	public static function get_by_invoice( $invoice_id ) {
		return self::find_by( array( 'invoice_id' => $invoice_id ), array( 'order_by' => 'created_at', 'order' => 'DESC' ) );
	}

	/**
	 * Get payment by transaction ID.
	 *
	 * @param string $transaction_id Transaction ID
	 * @return object|null
	 */
	public static function get_by_transaction( $transaction_id ) {
		$results = self::find_by( array( 'transaction_id' => $transaction_id ), array( 'limit' => 1 ) );
		return ! empty( $results ) ? $results[0] : null;
	}

	/**
	 * Get successful payments for an invoice.
	 *
	 * @param int $invoice_id Invoice ID
	 * @return array
	 */
	public static function get_successful_by_invoice( $invoice_id ) {
		return self::find_by(
			array(
				'invoice_id' => $invoice_id,
				'status'     => 'completed',
			)
		);
	}

	/**
	 * Calculate total paid amount for an invoice.
	 *
	 * @param int $invoice_id Invoice ID
	 * @return float
	 */
	public static function get_total_paid( $invoice_id ) {
		global $wpdb;
		$table = self::get_table();

		$total = $wpdb->get_var( $wpdb->prepare(
			"SELECT SUM(amount) FROM {$table} WHERE invoice_id = %d AND status = 'completed'",
			$invoice_id
		) );

		return $total ? (float) $total : 0.0;
	}
}
