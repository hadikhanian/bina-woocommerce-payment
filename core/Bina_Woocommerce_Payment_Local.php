<?php

namespace Bina\WoocommercePayment\Core;

use WC_Payment_Gateway;
use Shetabit\Multipay\Drivers\Local\Local;

class Bina_Woocommerce_Payment_Local extends WC_Payment_Gateway
{
	use Bina_Woocommerce_Payment_Core;

	public function __construct()
	{
		// Create the payment gateway
		$this->id                 = 'bina_woocommerce_payment_local';
		$this->method_title       = __('Bina Woocommerce Payment Method', 'bina-woocommerce-payment').' – '.__('Test Method', 'bina-woocommerce-payment');
		$this->method_description = __('Bina Woocommerce Payment Method', 'bina-woocommerce-payment').' – '.__('Test Method', 'bina-woocommerce-payment');
		$this->icon               = '/images/asanpardakht.png';
		$this->form_fields        = $this->form_fields();

		// Create Form Fields
		$this->init_form_fields();
		$this->init_settings();

		// Set Title and Description Values from Options
		$this->title       = $this->get_option('title');
		$this->description = $this->get_option('description');

		// Update Options Hook
		if ( version_compare(WOOCOMMERCE_VERSION, '2.0.0', '>=') ) {
			add_action('woocommerce_update_options_payment_gateways_'.$this->id, array($this, 'process_admin_options'));
		} else {
			add_action('woocommerce_update_options_payment_gateways', array($this, 'process_admin_options'));
		}

		// Send to Payment Gateway & Return From Gateway
		add_action('woocommerce_receipt_'.$this->id, [$this, 'send']);
		add_action('woocommerce_api_'.$this->id, [$this, 'verify']);
	}

	public function form_fields() : array
	{
		return [
			'header'      => [
				'type'  => 'title',
				'title' => __('Settings', 'bina-woocommerce-payment'),
			],
			'enabled'     => array(
				'title'       => __('Enable', 'woocommerce'),
				'label'       => __('Enable', 'woocommerce'),
				'type'        => 'checkbox',
				'description' => '',
				'default'     => 'no',
			),
			'title'       => array(
				'title' => __('Title', 'woocommerce'),
				'type'  => 'text',
			),
			'description' => array(
				'title'   => __('Description', 'woocommerce'),
				'type'    => 'textarea',
				'default' => '',
			),
		];
	}

	public function paymentConfig() : array
	{
		return [
			'default' => 'local',
			'drivers' => [
				'local' => [
					'callbackUrl'  => null,
					'title'        => 'درگاه پرداخت تست',
					'description'  => 'این درگاه *صرفا* برای تست صحت روند پرداخت و لغو پرداخت میباشد',
					'orderLabel'   => 'شماره سفارش',
					'amountLabel'  => 'مبلغ قابل پرداخت',
					'payButton'    => 'پرداخت موفق',
					'cancelButton' => 'پرداخت ناموفق',
				],
			],
			'map'     => [
				'local' => Local::class,
			],
		];
	}
}