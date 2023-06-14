<?php

namespace Bina\WoocommercePayment\Core;

use Shetabit\Multipay\Invoice;
use Shetabit\Multipay\Payment;
use Throwable;
use WC_Order;
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

	public function make_invoice(WC_Order $order): ?Invoice
	{
		try {
			// Create New Invoice Object
			$invoice = new Invoice;

			// Set Invoice Amount.
			if (get_woocommerce_currency() === 'IRR') {
				$invoice->amount($order->get_total());
			} else {
				$invoice->amount($order->get_total() * 10);
			}

			// Set Invoice Details
			$invoice->detail([
				'orderId' => $order->get_id(),
				'name'    => $order->get_user()->first_name . ' ' . $order->get_user()->last_name,
				'mobile'  => $order->get_billing_phone() ?? $order->get_user()->user_login,
				'email'   => $order->get_billing_email() ?? $order->get_user()->user_email,
			]);

			return $invoice;
		} catch (Throwable $e) {
			return null;
		}
	}

	public function verify()
	{
		// Get Request
		$order_id = absint($_REQUEST['wc_order']) ?? 0;

		// Check Cancel Transaction
		if (empty($order_id)) {
			wc_add_notice(__('Order ID is Empty! System can`t find your order data.', 'bina-woocommerce-payment'), 'error');
			wp_redirect(wc_get_checkout_url());
			exit;
		}

		// Get Order
		$order = new WC_Order($order_id);

		// Check Order is Unpaid
		if ($order->is_paid()) {
			wp_redirect(wc_get_checkout_url());
			exit;
		}

		// Verify Transaction
		try {
			$payment = new Payment($this->paymentConfig());
			if (get_woocommerce_currency() === 'IRR') {
				$receipt = $payment->amount($order->get_total())->verify();
			} else {
				$receipt = $payment->amount($order->get_total() * 10)->verify();
			}

			// Add Order Note
			$note = sprintf(__('The transaction was successful. The tracking number is %s', 'bina-woocommerce-payment'), $receipt->getReferenceId());
			$order->add_order_note($note, 1);

			// Process Order Transaction
			$order->payment_complete($receipt->getReferenceId());
			$order->save();

			// Redirect to Thank You Message
			wc_add_notice(sprintf(__('The transaction was successful. The tracking number is %s', 'bina-woocommerce-payment'), $receipt->getReferenceId()));
			wp_redirect(add_query_arg('wc_status', 'success', $this->get_return_url($order)));
			exit;
		} catch (Throwable $e) {
			wc_add_notice($e->getMessage(), 'error');
			wp_redirect(wc_get_checkout_url());
			exit;
		}
	}
}
