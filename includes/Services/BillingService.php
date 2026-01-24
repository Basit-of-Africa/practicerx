<?php
namespace PracticeRx\Services;

use PracticeRx\Models\Invoice;
use PracticeRx\Models\Payment;
use PracticeRx\Models\Patient;
use PracticeRx\Services\Gateways\GatewayFactory;
use PracticeRx\Core\Constants;
use PracticeRx\Core\Helper;

/**
 * Class BillingService
 */
class BillingService {

	/**
	 * Process a payment for an invoice.
	 *
	 * @param int    $invoice_id Invoice ID.
	 * @param string $gateway_id Gateway ID.
	 * @param array  $payment_data Additional payment data.
	 * @return array|\WP_Error
	 */
	public function process_payment( $invoice_id, $gateway_id, $payment_data = array() ) {
		// Get Invoice
		$invoice = Invoice::get( $invoice_id );
		if ( ! $invoice ) {
			return new \WP_Error( 'invalid_invoice', __( 'Invoice not found', 'practicerx' ), array( 'status' => 404 ) );
		}

		// Check if invoice is already paid
		if ( Constants::PAYMENT_STATUS_COMPLETED === $invoice->status ) {
			return new \WP_Error( 'already_paid', __( 'Invoice is already paid', 'practicerx' ), array( 'status' => 400 ) );
		}

		// Get patient email
		$patient = Patient::get( $invoice->patient_id );
		$customer_email = $patient ? $patient->email : '';

		// Get Gateway
		$gateway = GatewayFactory::get( $gateway_id );
		if ( is_wp_error( $gateway ) ) {
			return $gateway;
		}

		// Process payment
		$result = $gateway->process_payment(
			$invoice->amount,
			$invoice->currency,
			array_merge( array( 'email' => $customer_email ), $payment_data ),
			$invoice->invoice_number
		);

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Create payment record
		$payment_id = Payment::create( array(
			'invoice_id'       => $invoice_id,
			'patient_id'       => $invoice->patient_id,
			'practitioner_id'  => $invoice->practitioner_id,
			'amount'           => $invoice->amount,
			'currency'         => $invoice->currency,
			'gateway'          => $gateway_id,
			'transaction_id'   => $result['transaction_id'] ?? '',
			'status'           => Constants::PAYMENT_STATUS_COMPLETED,
			'payment_date'     => current_time( 'mysql' ),
		) );

		if ( ! $payment_id ) {
			return new \WP_Error( 'payment_record_failed', __( 'Payment processed but record creation failed', 'practicerx' ), array( 'status' => 500 ) );
		}

		// Update invoice status
		Invoice::update( $invoice_id, array(
			'status'     => Constants::PAYMENT_STATUS_COMPLETED,
			'paid_date'  => current_time( 'mysql' ),
		) );

		do_action( 'ppms_payment_processed', $payment_id, $invoice_id, $result );

		return array(
			'success'        => true,
			'payment_id'     => $payment_id,
			'invoice_id'     => $invoice_id,
			'transaction_id' => $result['transaction_id'] ?? '',
			'message'        => __( 'Payment processed successfully', 'practicerx' ),
		);
	}

	/**
	 * Create invoice from appointment.
	 *
	 * @param int $appointment_id Appointment ID.
	 * @return int|\WP_Error Invoice ID or error.
	 */
	public function create_invoice_from_appointment( $appointment_id ) {
		$appointment = \PracticeRx\Models\Appointment::get( $appointment_id );
		if ( ! $appointment ) {
			return new \WP_Error( 'invalid_appointment', __( 'Appointment not found', 'practicerx' ), array( 'status' => 404 ) );
		}

		// Get service details if service_id exists
		$amount = 0;
		if ( ! empty( $appointment->service_id ) ) {
			$service = \PracticeRx\Models\Service::get( $appointment->service_id );
			$amount = $service ? $service->price : 0;
		}

		$invoice_data = array(
			'patient_id'      => $appointment->patient_id,
			'practitioner_id' => $appointment->practitioner_id,
			'appointment_id'  => $appointment_id,
			'amount'          => $amount,
			'currency'        => ppms_get_option( 'currency', 'USD' ),
			'status'          => Constants::PAYMENT_STATUS_PENDING,
			'invoice_number'  => Invoice::generate_invoice_number(),
			'due_date'        => date( 'Y-m-d H:i:s', strtotime( '+30 days' ) ),
		);

		return Invoice::create( $invoice_data );
	}

	/**
	 * Refund a payment.
	 *
	 * @param int    $payment_id Payment ID.
	 * @param string $reason Refund reason.
	 * @return array|\WP_Error
	 */
	public function refund_payment( $payment_id, $reason = '' ) {
		$payment = Payment::get( $payment_id );
		if ( ! $payment ) {
			return new \WP_Error( 'invalid_payment', __( 'Payment not found', 'practicerx' ), array( 'status' => 404 ) );
		}

		if ( Constants::PAYMENT_STATUS_REFUNDED === $payment->status ) {
			return new \WP_Error( 'already_refunded', __( 'Payment is already refunded', 'practicerx' ), array( 'status' => 400 ) );
		}

		// Get gateway and process refund
		$gateway = GatewayFactory::get( $payment->gateway );
		if ( is_wp_error( $gateway ) ) {
			return $gateway;
		}

		// Update payment status
		Payment::update( $payment_id, array(
			'status' => Constants::PAYMENT_STATUS_REFUNDED,
			'notes'  => $reason,
		) );

		// Update invoice if needed
		if ( $payment->invoice_id ) {
			Invoice::update( $payment->invoice_id, array(
				'status' => Constants::PAYMENT_STATUS_REFUNDED,
			) );
		}

		do_action( 'ppms_payment_refunded', $payment_id, $reason );

		return array(
			'success' => true,
			'message' => __( 'Payment refunded successfully', 'practicerx' ),
		);
	}

	/**
	 * Get payment summary for patient.
	 *
	 * @param int $patient_id Patient ID.
	 * @return array
	 */
	public function get_patient_payment_summary( $patient_id ) {
		$invoices = Invoice::get_by_patient( $patient_id );
		$payments = Payment::get_by_patient( $patient_id );

		$total_invoiced = 0;
		$total_paid = 0;
		$total_pending = 0;

		foreach ( $invoices as $invoice ) {
			$total_invoiced += floatval( $invoice->amount );
			if ( Constants::PAYMENT_STATUS_PENDING === $invoice->status ) {
				$total_pending += floatval( $invoice->amount );
			}
		}

		foreach ( $payments as $payment ) {
			if ( Constants::PAYMENT_STATUS_COMPLETED === $payment->status ) {
				$total_paid += floatval( $payment->amount );
			}
		}

		return array(
			'total_invoiced' => $total_invoiced,
			'total_paid'     => $total_paid,
			'total_pending'  => $total_pending,
			'balance'        => $total_invoiced - $total_paid,
		);
	}
}
