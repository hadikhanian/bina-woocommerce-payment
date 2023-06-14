<?php

namespace Bina\WoocommercePayment\Core;

use WC_Payment_Gateway;

class Bina_Woocommerce_Payment_Pasargad extends WC_Payment_Gateway
{
	use Bina_Woocommerce_Payment_Core;

	public function __construct()
	{
		// Create the payment gateway
		$this->id                 = 'bina_woocommerce_payment_pasargad';
		$this->method_title       = __('Bina Woocommerce Payment Method', 'bina-woocommerce-payment') . ' – ' . __('Pasargad', 'bina-woocommerce-payment');
		$this->method_description = __('Bina Woocommerce Payment Method', 'bina-woocommerce-payment') . ' – ' . __('Pasargad', 'bina-woocommerce-payment');
		$this->construct();
	}

	public function form_fields(): array
	{
		$settings = $this->settings();

		$config = [
			'terminalId'   => array(
				'title'       => __('Terminal ID', 'bina-woocommerce-payment'),
				'type'        => 'text',
				'description' => __('Insert your payment gateway information.', 'bina-woocommerce-payment'),
				'desc_tip'    => true,
			),
			'terminalCode' => array(
				'title'       => __('Terminal Code', 'bina-woocommerce-payment'),
				'type'        => 'textarea',
				'description' => __('Insert your payment gateway information.', 'bina-woocommerce-payment'),
				'desc_tip'    => true,
			),
			'certificate'  => array(
				'title'       => __('Certificate', 'bina-woocommerce-payment'),
				'type'        => 'textarea',
				'description' => __('Insert your payment gateway xml certificate string', 'bina-woocommerce-payment'),
				'desc_tip'    => true,
			),
		];

		return array_merge($settings, $config);
	}

	public function process_admin_options()
	{
		parent::process_admin_options();
		$pasargad_settings = get_option('woocommerce_bina_woocommerce_payment_pasargad_settings');
		$pasargad_settings['certificate'] = $_POST['woocommerce_bina_woocommerce_payment_pasargad_certificate'] ?? null;
		update_option('woocommerce_bina_woocommerce_payment_pasargad_settings', $pasargad_settings);
	}
}
