<?php
namespace PracticeRx\Services\Gateways;

/**
 * Class StripeGateway
 */
class StripeGateway extends AbstractGateway {

	public function get_id() {
		return 'stripe';
	}

	public function get_title() {
		return 'Stripe';
	}

	public function process_payment( $amount, $currency, $customer, $reference ) {
		// Mock implementation for now
		// In real life, use Stripe SDK to create PaymentIntent

		return array(
			'success'        => true,
			'transaction_id' => 'pi_mock_' . uniqid(),
			'redirect_url'   => '', // Stripe often uses client-side JS, but can do redirect
		);
	}

	public function verify_transaction( $transaction_id ) {
		return true;
	}
}
