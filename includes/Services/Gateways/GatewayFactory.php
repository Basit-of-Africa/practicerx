<?php
namespace PracticeRx\Services\Gateways;

/**
 * Class GatewayFactory
 */
class GatewayFactory {

	/**
	 * Get a gateway instance.
	 *
	 * @param string $id Gateway ID.
	 * @return PaymentGatewayInterface|\WP_Error
	 */
	public static function get( $id ) {
		switch ( $id ) {
			case 'stripe':
				return new StripeGateway();
			case 'woocommerce':
				return new WooCommerceGateway();
			// Add Paystack and Flutterwave here
			default:
				return new \WP_Error( 'invalid_gateway', 'Gateway not found.' );
		}
	}

	/**
	 * Get all available gateways.
	 *
	 * @return array
	 */
	public static function get_all() {
		return array(
			'stripe'      => 'Stripe',
			'paystack'    => 'Paystack',
			'flutterwave' => 'Flutterwave',
			'woocommerce' => 'WooCommerce',
		);
	}
}
