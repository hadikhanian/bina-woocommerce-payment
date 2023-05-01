<?php

namespace Bina\WoocommercePayment\Core;

use WC_Order;
use Throwable;
use WC_Payment_Gateway;
use Shetabit\Multipay\Payment;

class Bina_Woocommerce_Payment_Local extends WC_Payment_Gateway
{
	use Bina_Woocommerce_Payment_Core;

	public function __construct()
	{
		// Create the payment gateway
		$this->id                 = 'bina_woocommerce_payment_local';
		$this->method_title       = __('Bina Woocommerce Payment Method', 'bina-woocommerce-payment').' – '.__('Test Method', 'bina-woocommerce-payment');
		$this->method_description = __('Bina Woocommerce Payment Method', 'bina-woocommerce-payment').' – '.__('Test Method', 'bina-woocommerce-payment');
		$this->construct();
	}

	public function form_fields() : array
	{
		return $this->settings();
	}

	public function send($order_id)
	{
		$order    = new WC_Order($order_id);
		$callback = add_query_arg('wc_order', $order_id, WC()->api_request_url($this->id));

		try {
			$invoice = $this->make_invoice($order);
			$payment = new Payment($this->paymentConfig());
			$payment->callbackUrl($callback)->purchase($invoice, function($driver, $transactionId) use ($order) {
				update_post_meta($order->get_id(), '_bina_woocommerce_payment_transaction_id', $transactionId);
				update_post_meta($order->get_id(), '_bina_woocommerce_payment_driver', get_class($this));
			});
			echo $payment->pay()->render();
		} catch ( Throwable $e ) {
			wc_add_notice(__('Payment Error', 'bina-woocommerce-payment').' => '.$e->getMessage(), 'error');

			return $e->getMessage();
		}
	}
}