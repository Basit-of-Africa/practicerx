<?php
namespace PracticeRx\Services\Gateways;

/**
 * Class WooCommerceGateway
 *
 * Adapter to use WooCommerce checkout for payments.
 */
class WooCommerceGateway extends AbstractGateway {

	public function get_id() {
		return 'woocommerce';
	}

	public function get_title() {
		return 'WooCommerce';
	}

	public function process_payment( $amount, $currency, $customer, $reference ) {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return new \WP_Error( 'wc_missing', 'WooCommerce is not active.' );
		}

		// Logic to create a temporary WC Product or Order and redirect to checkout
		// For this MVP, we'll simulate a redirect URL to a custom checkout page
		
		return array(
			'success'      => true,
			'redirect_url' => wc_get_checkout_url() . '?ppms_invoice=' . $reference,
		);
	}

	public function verify_transaction( $transaction_id ) {
		// Check WC Order status
		return true;
	}
}
