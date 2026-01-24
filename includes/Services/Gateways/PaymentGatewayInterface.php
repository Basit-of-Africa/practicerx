<?php
namespace PracticeRx\Services\Gateways;

/**
 * Interface PaymentGatewayInterface
 *
 * Contract for all payment gateways.
 */
interface PaymentGatewayInterface {

	/**
	 * Get the gateway ID.
	 *
	 * @return string
	 */
	public function get_id();

	/**
	 * Get the gateway title.
	 *
	 * @return string
	 */
	public function get_title();

	/**
	 * Process a payment.
	 *
	 * @param float  $amount      Amount to charge.
	 * @param string $currency    Currency code.
	 * @param array  $customer    Customer details (email, name).
	 * @param string $reference   Unique transaction reference.
	 * @return array|\WP_Error    Result with 'success', 'transaction_id', 'redirect_url'.
	 */
	public function process_payment( $amount, $currency, $customer, $reference );

	/**
	 * Verify a transaction.
	 *
	 * @param string $transaction_id Transaction ID from provider.
	 * @return bool
	 */
	public function verify_transaction( $transaction_id );
}
