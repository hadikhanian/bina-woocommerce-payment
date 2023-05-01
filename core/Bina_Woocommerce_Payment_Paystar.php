<?php

namespace Bina\WoocommercePayment\Core;

use WC_Payment_Gateway;

class Bina_Woocommerce_Payment_Paystar extends WC_Payment_Gateway
{
	use Bina_Woocommerce_Payment_Core;

	public function __construct()
	{
		// Create the payment gateway
		$this->id                 = 'bina_woocommerce_payment_digipay';
		$this->method_title       = __('Bina Woocommerce Payment Method', 'bina-woocommerce-payment').' – '.__('DigiPay', 'bina-woocommerce-payment');
		$this->method_description = __('Bina Woocommerce Payment Method', 'bina-woocommerce-payment').' – '.__('DigiPay', 'bina-woocommerce-payment');
		$this->construct();
	}

	public function form_fields() : array
	{
		$settings = $this->settings();

		$config = [
			'gatewayId' => array(
				'title'       => __('Gateway ID', 'bina-woocommerce-payment'),
				'type'        => 'text',
				'description' => __('Insert your payment gateway information.', 'bina-woocommerce-payment'),
				'desc_tip'    => true,
			),
			'signKey'   => array(
				'title'       => __('Sign Key', 'bina-woocommerce-payment'),
				'type'        => 'textarea',
				'description' => __('Insert your payment gateway information.', 'bina-woocommerce-payment'),
				'desc_tip'    => true,
			),
		];

		return array_merge($settings, $config);
	}
}