<?php

namespace Bina\WoocommercePayment\Core;

use WC_Order;
use WC_Payment_Gateway;

class Bina_WooCommerce_Payment_Core extends WC_Payment_Gateway
{
	public function __construct()
	{
		$this->id                 = 'bina_woocommerce_payment';
		$this->method_title       = __('Bina Woocommerce Payment', 'bina-woocommerce-payment');
		$this->method_description = __('Bina Woocommerce Payment Method', 'bina-woocommerce-payment');
		$this->has_fields         = true;
		$this->new_method_label   = 'new_method_label';
		$this->form_fields        = $this->form_fields();

		$this->init_form_fields();
		$this->init_settings();

		$this->title       = $this->get_option('title');
		$this->description = $this->get_option('description');

		add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
	}

	public function form_fields(): array
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
				'title'    => __('title', 'woocommerce'),
				'type'     => 'text',
				'desc_tip' => true,
			),
			'description' => array(
				'title'   => __('description', 'woocommerce'),
				'type'    => 'textarea',
				'default' => '',
			),
			'driver'      => [
				'title'             => __('Payment Method', 'bina-woocommerce-payment'),
				'type'              => 'select',
				'desc_tip'          => true,
				'description'       => __('Select a Payment Method Driver', 'bina-woocommerce-shipping'),
				'default'           => "local",
				'options'           => [
					"local"  => __('Test Driver', 'bina-woocommerce-shipping'),
					"mellat" => __('Mellat Driver', 'bina-woocommerce-shipping'),
					"up"     => __('Up Driver', 'bina-woocommerce-shipping'),
				],
				'sanitize_callback' => "sanitize_text_field",
			],
		];
	}

	public function process_payment($order_id)
	{
		$order = new WC_Order($order_id);

		if ($this->check_payment()) {
			$order->payment_complete();
			WC()->cart->empty_cart();
			return array(
				'result'   => 'success',
				'redirect' => $this->get_return_url($order)
			);
		} else {
			wc_add_notice(__('Payment error: invalid response.', 'woocommerce'), 'error');
			return;
		}
	}

	private function check_payment()
	{
		$response = wp_remote_post('https://payment-gateway.com/pay', array(
			'headers' => array('Content-Type' => 'application/x-www-form-urlencoded'),
			'body'    => array(
				'amount'      => WC()->cart->total,
				'card_number' => $_POST['card_number'],
				'expiry_date' => $_POST['expiry_date'],
				'cvv'         => $_POST['cvv']
			)
		));

		if (!is_wp_error($response)) {
			$body = wp_remote_retrieve_body($response);
			if (strtolower($body) === 'success') {
				return true;
			}
		}
		return false;
	}

	private function instance_form_fields()
	{
		$settings = array(
			'title'          => array(
				'title'             => __('Title', 'woocommerce'),
				'type'              => 'text',
				'default'           => __('Bina Woocommerce Shipping', 'bina-woocommerce-shipping'),
				'sanitize_callback' => "sanitize_text_field",
			),
			'tax_status'     => array(
				'title'             => __('Taxable', 'woocommerce'),
				'type'              => 'select',
				'default'           => 'none',
				'options'           => array(
					'taxable' => __('Yes', 'woocommerce'),
					'none'    => __('No', 'woocommerce'),
				),
				'sanitize_callback' => "sanitize_text_field",
			),
			'cost'           => array(
				'title'             => __('Cost', 'woocommerce') . " (" . get_woocommerce_currency_symbol() . ")",
				'type'              => 'text',
				'default'           => '0',
				'sanitize_callback' => "absint",
			),
			'free'           => array(
				'title'             => __('Free From', 'bina-woocommerce-shipping') . " (" . get_woocommerce_currency_symbol() . ")",
				'type'              => 'text',
				'desc_tip'          => true,
				'description'       => __('Enter the minimum purchase amount to get free shipping. ', 'bina-woocommerce-shipping'),
				'sanitize_callback' => "absint",
			),
			'include_cities' => array(
				'title'             => __('Include Cities Name', 'bina-woocommerce-shipping'),
				'type'              => 'text',
				'desc_tip'          => true,
				'description'       => __('Empty for Include All Cities. Please Enter Comma Separated City Names', 'bina-woocommerce-shipping'),
				'sanitize_callback' => "sanitize_text_field",
			),
			'exclude_cities' => array(
				'title'             => __('Exclude Cities Name', 'bina-woocommerce-shipping'),
				'type'              => 'text',
				'desc_tip'          => true,
				'description'       => __('Please Enter Comma Separated City Names', 'bina-woocommerce-shipping'),
				'sanitize_callback' => "sanitize_text_field",
			),
			'max_weight'     => array(
				'title'             => __('Max Weight', 'bina-woocommerce-shipping') . ' (' . $weight_unit . ')',
				'type'              => 'number',
				'desc_tip'          => true,
				'description'       => __('Maximum allowed weight. 0 = Disable', 'bina-woocommerce-shipping'),
				'default'           => "0",
				'custom_attributes' => array(
					'min'  => 0,
					'step' => 1,
				),
				'sanitize_callback' => "absint",
			)
		);
		return $settings;
	}
}