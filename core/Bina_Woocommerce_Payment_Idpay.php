<?php

namespace Bina\WoocommercePayment\Core;

use WC_Payment_Gateway;

class Bina_Woocommerce_Payment_Idpay extends WC_Payment_Gateway
{
	use Bina_Woocommerce_Payment_Core;

	public function __construct()
	{
		// Create the payment gateway
		$this->id                 = 'bina_woocommerce_payment_idpay';
		$this->method_title       = __('Bina Woocommerce Payment Method', 'bina-woocommerce-payment').' – '.__('ID Pay', 'bina-woocommerce-payment');
		$this->method_description = __('Bina Woocommerce Payment Method', 'bina-woocommerce-payment').' – '.__('ID Pay', 'bina-woocommerce-payment');
		$this->construct();
	}

	public function form_fields() : array
	{
		$settings = $this->settings();

		$config = [
			'merchantId' => array(
				'title'       => __('Merchant ID', 'bina-woocommerce-payment'),
				'type'        => 'text',
				'description' => __('Insert your payment gateway information.', 'bina-woocommerce-payment'),
				'desc_tip'    => true,
			),
			'sandbox'    => array(
				'title'       => __('Sandbox', 'bina-woocommerce-payment'),
				'label'       => __('Enable', 'bina-woocommerce-payment'),
				'type'        => 'checkbox',
				'default'     => 'no',
				'description' => __('Enable / Disable sandbox of payment gateway.', 'bina-woocommerce-payment'),
				'desc_tip'    => true,
			),
		];

		return array_merge($settings, $config);
	}
}