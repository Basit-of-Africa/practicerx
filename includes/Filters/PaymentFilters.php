<?php
/**
 * Payment Filters
 * 
 * Handles payment-related filters and hooks.
 */

namespace PracticeRx\Filters;

use PracticeRx\Core\Helper;
use PracticeRx\Core\Constants;

class PaymentFilters {

	public function __construct() {
		// Process payment gateway selection
		add_filter( 'ppms_payment_gateway_options', array( $this, 'get_gateway_options' ), 10, 1 );
		
		// Validate payment data
		add_filter( 'ppms_before_payment_process', array( $this, 'validate_payment' ), 10, 1 );
		
		// After payment completed
		add_action( 'ppms_payment_completed', array( $this, 'handle_payment_completion' ), 10, 2 );
		
		// Payment failed
		add_action( 'ppms_payment_failed', array( $this, 'handle_payment_failure' ), 10, 2 );
	}

	/**
	 * Get available payment gateway options
	 *
	 * @param array $gateways Current gateways
	 * @return array
	 */
	public function get_gateway_options( $gateways ) {
		// Allow filtering of available gateways
		return apply_filters( 'ppms_active_payment_gateways', $gateways );
	}

	/**
	 * Validate payment data before processing
	 *
	 * @param array $data Payment data
	 * @return array|\WP_Error
	 */
	public function validate_payment( $data ) {
		$errors = new \WP_Error();

		if ( empty( $data['invoice_id'] ) ) {
			$errors->add( 'missing_invoice', __( 'Invoice ID is required.', 'practicerx' ) );
		}

		if ( empty( $data['amount'] ) || $data['amount'] <= 0 ) {
			$errors->add( 'invalid_amount', __( 'Invalid payment amount.', 'practicerx' ) );
		}

		if ( empty( $data['gateway'] ) ) {
			$errors->add( 'missing_gateway', __( 'Payment gateway is required.', 'practicerx' ) );
		}

		if ( $errors->has_errors() ) {
			return $errors;
		}

		return $data;
	}

	/**
	 * Handle successful payment
	 *
	 * @param int   $payment_id Payment ID
	 * @param array $payment_data Payment data
	 * @return void
	 */
	public function handle_payment_completion( $payment_id, $payment_data ) {
		Helper::log( sprintf( 'Payment completed: ID %d', $payment_id ), 'payment' );
		
		// Send receipt email
		do_action( 'ppms_send_payment_receipt', $payment_id, $payment_data );
		
		// Update invoice status
		do_action( 'ppms_update_invoice_status', $payment_data['invoice_id'], 'paid' );
	}

	/**
	 * Handle failed payment
	 *
	 * @param int   $payment_id Payment ID
	 * @param array $error_data Error data
	 * @return void
	 */
	public function handle_payment_failure( $payment_id, $error_data ) {
		Helper::log( sprintf( 'Payment failed: ID %d - %s', $payment_id, $error_data['message'] ?? 'Unknown error' ), 'payment' );
		
		// Send failure notification
		do_action( 'ppms_send_payment_failure_notification', $payment_id, $error_data );
	}
}
