<?php

namespace Bina\WoocommercePayment\Core;

use WC_Payment_Gateway;

class Bina_Woocommerce_Payment_Irankish extends WC_Payment_Gateway
{
	use Bina_Woocommerce_Payment_Core;

	public function __construct()
	{
		// Create the payment gateway
		$this->id                 = 'bina_woocommerce_payment_irankish';
		$this->method_title       = __('Bina Woocommerce Payment Method', 'bina-woocommerce-payment').' – '.__('Irankish', 'bina-woocommerce-payment');
		$this->method_description = __('Bina Woocommerce Payment Method', 'bina-woocommerce-payment').' – '.__('Irankish', 'bina-woocommerce-payment');
		$this->construct();
	}

	public function form_fields() : array
	{
		$settings = $this->settings();

		$config = [
			'terminalId' => array(
				'title'       => __('Terminal ID', 'bina-woocommerce-payment'),
				'type'        => 'text',
				'description' => __('Insert your payment gateway information.', 'bina-woocommerce-payment'),
				'desc_tip'    => true,
			),
			'password'   => array(
				'title'       => __('Password', 'bina-woocommerce-payment'),
				'type'        => 'text',
				'description' => __('Insert your payment gateway information.', 'bina-woocommerce-payment'),
				'desc_tip'    => true,
			),
			'acceptorId' => array(
				'title'       => __('Acceptor ID', 'bina-woocommerce-payment'),
				'type'        => 'text',
				'description' => __('Insert your payment gateway information.', 'bina-woocommerce-payment'),
				'desc_tip'    => true,
			),
			'pubKey'     => array(
				'title'       => __('Public Key', 'bina-woocommerce-payment'),
				'type'        => 'textarea',
				'description' => __('Insert your payment gateway information.', 'bina-woocommerce-payment'),
				'desc_tip'    => true,
			),
		];

		return array_merge($settings, $config);
	}
}