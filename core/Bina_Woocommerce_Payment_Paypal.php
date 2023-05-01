<?php

namespace Bina\WoocommercePayment\Core;

use WC_Payment_Gateway;

class Bina_Woocommerce_Payment_Paypal extends WC_Payment_Gateway
{
	use Bina_Woocommerce_Payment_Core;

	public function __construct()
	{
		// Create the payment gateway
		$this->id                 = 'bina_woocommerce_payment_paypal';
		$this->method_title       = __('Bina Woocommerce Payment Method', 'bina-woocommerce-payment').' – '.__('Paypal', 'bina-woocommerce-payment');
		$this->method_description = __('Bina Woocommerce Payment Method', 'bina-woocommerce-payment').' – '.__('Paypal', 'bina-woocommerce-payment');
		$this->construct();
	}

	public function form_fields() : array
	{
		$settings = $this->settings();

		$config = [
			'accountId' => array(
				'title'       => __('Account ID', 'bina-woocommerce-payment'),
				'type'        => 'text',
				'description' => __('Insert your payment gateway information.', 'bina-woocommerce-payment'),
				'desc_tip'    => true,
			),
			'mode'      => array(
				'title'       => __('Mode', 'bina-woocommerce-payment'),
				'type'        => 'select',
				'default'     => 'normal',
				'options'     => array(
					'normal'  => __('Normal', 'bina-woocommerce-payment'),
					'sandbox' => __('Sandbox', 'bina-woocommerce-payment'),
				),
				'description' => __('Select a payment gateway mode.', 'bina-woocommerce-payment'),
				'desc_tip'    => true,
			),
		];

		return array_merge($settings, $config);
	}
}