<?php

namespace Bina\WoocommercePayment\Core;

use WC_Order;
use Throwable;
use Shetabit\Multipay\Invoice;
use Shetabit\Multipay\Payment;
use Shetabit\Multipay\RedirectionForm;

trait Bina_Woocommerce_Payment_Core_Trait
{
	public function construct()
	{
		$this->form_fields = $this->form_fields();

		// Create Form Fields
		$this->init_form_fields();
		$this->init_settings();

		// Set Title and Description Values from Options
		$this->title       = $this->get_option('title');
		$this->description = $this->get_option('description');
		$this->icon        = plugin_dir_url(__DIR__).'images/'.str_replace('bina_woocommerce_payment_', '', $this->id).'.png';

		// Update Options Hook
		add_action('woocommerce_update_options_payment_gateways', array($this, 'process_admin_options'));

		// Send to Payment Gateway & Return From Gateway
		add_action('woocommerce_receipt_'.$this->id, [$this, 'send']);
		add_action('woocommerce_api_'.$this->id, [$this, 'verify']);
	}

	public function process_payment($order_id) : array
	{
		$order = new WC_Order($order_id);

		return array(
			'result'   => 'success',
			'redirect' => $order->get_checkout_payment_url(true),
		);
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
			echo $this->render($payment->pay());
		} catch ( Throwable $e ) {
			wc_add_notice(__('Payment Error', 'bina-woocommerce-payment').' => '.$e->getMessage(), 'error');

			return $e->getMessage();
		}
	}

	public function verify()
	{
		// Get Request
		$order_id      = absint($_REQUEST['wc_order']) ?? 0;
		$transactionId = sanitize_text_field($_REQUEST['transactionId'] ?? $_REQUEST['code'] ?? $_REQUEST['transaction_id']);
		$cancel        = $_REQUEST['cancel'] ?? false;

		// Check Transaction ID
		if ( empty($transactionId) ) {
			wc_add_notice(__('Transaction ID is Empty.', 'bina-woocommerce-payment'), 'error');
			wp_redirect(wc_get_checkout_url());
			exit;
		}

		// Get Order
		$order = new WC_Order($order_id);

		// Check Order is Unpaid
		if ( $order->is_paid() ) {
			wp_redirect(wc_get_checkout_url());
			exit;
		}

		// Check Cancel Transaction
		if ( $cancel ) {
			wc_add_notice(__('Payment Cancel By User', 'bina-woocommerce-payment'), 'error');
			wp_redirect(wc_get_checkout_url());
			exit;
		}

		// Verify Transaction
		try {
			$payment = new Payment($this->paymentConfig());
			$receipt = $payment->amount($order->get_total())->transactionId($transactionId)->verify();
			$details = $receipt->getDetails();

			// Update Order Meta
			$trace_number = $details['traceNumber'] ?? $details['traceNo'] ?? null;
			$card_number  = $details['cardNumber'] ?? $details['cardNo'] ?? null;
			update_post_meta($order_id, '_bina_woocommerce_payment_trace_number', $trace_number);
			update_post_meta($order_id, '_bina_woocommerce_payment_card_number', $card_number);

			// Add Order Note
			$note = sprintf(__('The transaction was successful.<br>The ref number is %s.<br>& card holderpan is %s.<br>& trace number is %s.', 'bina-woocommerce-payment'), $receipt->getReferenceId(), $card_number, $trace_number);
			$order->add_order_note($note, 1);

			// Process Order Transaction
			$order->payment_complete($receipt->getReferenceId());
			$order->save();

			// Redirect to Thank You Message
			wc_add_notice(sprintf(__('The transaction was successful. The tracking number is %s', 'bina-woocommerce-payment'), $receipt->getReferenceId()));
			wp_redirect(add_query_arg('wc_status', 'success', $this->get_return_url($order)));
			exit;
		} catch ( Throwable $e ) {
			wc_add_notice($e->getMessage(), 'error');
			wp_redirect(wc_get_checkout_url());
			exit;
		}
	}

	public function make_invoice(WC_Order $order) : ?Invoice
	{
		try {
			// Create New Invoice Object
			$invoice = new Invoice;

			// Set Invoice Amount.
			$invoice->amount($order->get_total());

			// Set Invoice Details
			$invoice->detail([
				'orderId' => $order->get_id(),
				'name'    => $order->get_user()->display_name,
				'mobile'  => $order->get_billing_phone(),
				'email'   => $order->get_billing_email(),
			]);

			return $invoice;
		} catch ( Throwable $e ) {
			return null;
		}
	}

	public function render(RedirectionForm $redirectData) : string
	{
		$html = "<form id='bina_woocommerce_payment' action='{$redirectData->getAction()}' method='{$redirectData->getMethod()}'>";
		foreach ( $redirectData->getInputs() as $name => $value ) {
			$html .= "<input type='hidden' name='{$name}' value='$value'>";
		}
		$html .= "</form><script> setTimeout(function() { jQuery('#bina_woocommerce_payment').submit(); }, 500); </script>";
		$html .= "<p>".__('Dear user, you are connecting to the payment gateway, Please wait...', 'bina-woocommerce-payment')."</p>";

		return $html;
	}
}