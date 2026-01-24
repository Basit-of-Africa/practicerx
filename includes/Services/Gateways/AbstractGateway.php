<?php
namespace PracticeRx\Services\Gateways;

/**
 * Class AbstractGateway
 */
abstract class AbstractGateway implements PaymentGatewayInterface {
	
	/**
	 * API Key / Secret.
	 *
	 * @var string
	 */
	protected $api_key;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->init_settings();
	}

	/**
	 * Initialize settings.
	 */
	protected function init_settings() {
		// Load settings from WP Options
	}
}
