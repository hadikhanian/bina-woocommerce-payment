<?php

namespace Bina\WoocommercePayment\Core;

use WC_Order;
use Throwable;
use Shetabit\Multipay\Invoice;
use Shetabit\Multipay\Payment;
use Shetabit\Multipay\RedirectionForm;
use Shetabit\Multipay\Drivers\SEP\SEP;
use Shetabit\Multipay\Drivers\Azki\Azki;
use Shetabit\Multipay\Drivers\Payfa\Payfa;
use Shetabit\Multipay\Drivers\Zibal\Zibal;
use Shetabit\Multipay\Drivers\Saman\Saman;
use Shetabit\Multipay\Drivers\Sadad\Sadad;
use Shetabit\Multipay\Drivers\Payir\Payir;
use Shetabit\Multipay\Drivers\Idpay\Idpay;
use Shetabit\Multipay\Drivers\Local\Local;
use Shetabit\Multipay\Drivers\Vandar\Vandar;
use Shetabit\Multipay\Drivers\Sizpay\Sizpay;
use Shetabit\Multipay\Drivers\Yekpay\Yekpay;
use Shetabit\Multipay\Drivers\Sepehr\Sepehr;
use Shetabit\Multipay\Drivers\Poolam\Poolam;
use Shetabit\Multipay\Drivers\Paypal\Paypal;
use Shetabit\Multipay\Drivers\Atipay\Atipay;
use Shetabit\Multipay\Drivers\Walleta\Walleta;
use Shetabit\Multipay\Drivers\Paystar\Paystar;
use Shetabit\Multipay\Drivers\Payping\Payping;
use Shetabit\Multipay\Drivers\Parsian\Parsian;
use Shetabit\Multipay\Drivers\Nextpay\Nextpay;
use Shetabit\Multipay\Drivers\Digipay\Digipay;
use Shetabit\Multipay\Drivers\Rayanpay\Rayanpay;
use Shetabit\Multipay\Drivers\Sepordeh\Sepordeh;
use Shetabit\Multipay\Drivers\Zarinpal\Zarinpal;
use Shetabit\Multipay\Drivers\Pasargad\Pasargad;
use Shetabit\Multipay\Drivers\Irankish\Irankish;
use Shetabit\Multipay\Drivers\Etebarino\Etebarino;
use Shetabit\Multipay\Drivers\Fanavacard\Fanavacard;
use Shetabit\Multipay\Drivers\Behpardakht\Behpardakht;
use Shetabit\Multipay\Drivers\Asanpardakht\Asanpardakht;
use Shetabit\Multipay\Drivers\Aqayepardakht\Aqayepardakht;

trait Bina_Woocommerce_Payment_Core
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
		$this->icon        = $this->get_option('icon');

		// Update Options Hook
		add_action('woocommerce_update_options_payment_gateways_'.$this->id, array($this, 'process_admin_options'));

		// Send to Payment Gateway & Return From Gateway
		add_action('woocommerce_receipt_'.$this->id, [$this, 'send']);
		add_action('woocommerce_api_'.$this->id, [$this, 'verify']);
	}

	public function settings() : array
	{
		return [
			'header'      => [
				'type'  => 'title',
				'title' => __('Settings', 'bina-woocommerce-payment'),
			],
			'enabled'     => array(
				'title'       => __('Enable', 'bina-woocommerce-payment'),
				'label'       => __('Enable', 'bina-woocommerce-payment'),
				'type'        => 'checkbox',
				'default'     => 'no',
				'description' => __('Enable / Disable this payment method', 'bina-woocommerce-payment'),
				'desc_tip'    => true,
			),
			'title'       => array(
				'title'       => __('Title', 'bina-woocommerce-payment'),
				'type'        => 'text',
				'default'     => $this->method_title,
				'description' => __('Show this title in checkout page', 'bina-woocommerce-payment'),
				'desc_tip'    => true,
			),
			'description' => array(
				'title'       => __('Description', 'bina-woocommerce-payment'),
				'type'        => 'textarea',
				'default'     => $this->method_description,
				'description' => __('Show this text in payment method description in checkout page', 'bina-woocommerce-payment'),
				'desc_tip'    => true,
			),
			'icon'        => array(
				'title'       => __('Icon URL', 'bina-woocommerce-payment'),
				'type'        => 'url',
				'default'     => '',
				'placeholder' => 'https://yourwebsite.com/wp-content/uploads/icon.png',
				'description' => __('Insert payment gateway icon url (60px * 60px)', 'bina-woocommerce-payment'),
				'desc_tip'    => true,
			),
		];
	}

	public function process_payment($order_id) : array
	{
		$order = new WC_Order(absint($order_id));

		return array(
			'result'   => 'success',
			'redirect' => $order->get_checkout_payment_url(true),
		);
	}

	public function send($order_id)
	{
		$order    = new WC_Order(absint($order_id));
		$callback = add_query_arg('wc_order', $order_id, WC()->api_request_url($this->id));

		try {
			$invoice = $this->make_invoice($order);
			$payment = new Payment($this->paymentConfig());
			$payment->callbackUrl($callback)->purchase($invoice, function($driver, $transactionId) use ($order_id) {
				update_post_meta($order_id, '_bina_woocommerce_payment_transaction_id', $transactionId);
				update_post_meta($order_id, '_bina_woocommerce_payment_driver', $this->id);
			});
			echo $this->render($payment->pay());
		} catch ( Throwable $e ) {
			wc_add_notice(__('Payment Error', 'bina-woocommerce-payment').' - '.$e->getMessage(), 'error');
			wp_redirect(wc_get_checkout_url());
			exit;
		}
	}

	public function verify()
	{
		// Get Request
		$order_id = absint($_REQUEST['wc_order']) ?? 0;

		// Check Cancel Transaction
		if ( empty($order_id) ) {
			wc_add_notice(__('Order ID is Empty! System can`t find your order data.', 'bina-woocommerce-payment'), 'error');
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

		// Verify Transaction
		try {
			$payment = new Payment($this->paymentConfig());
			if ( get_woocommerce_currency() === 'IRR' ) {
				$receipt = $payment->amount($order->get_total() / 10)->verify();
			} else {
				$receipt = $payment->amount($order->get_total())->verify();
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
			if ( get_woocommerce_currency() === 'IRR' ) {
				$invoice->amount($order->get_total() / 10);
			} else {
				$invoice->amount($order->get_total());
			}

			// Set Invoice Details
			$invoice->detail([
				'orderId' => $order->get_id(),
				'name'    => $order->get_user()->first_name.' '.$order->get_user()->last_name,
				'mobile'  => $order->get_billing_phone() ?? $order->get_user()->user_login,
				'email'   => $order->get_billing_email() ?? $order->get_user()->user_email,
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
		$html .= "</form><script> setTimeout(function() { jQuery('#bina_woocommerce_payment').submit(); }, 300); </script>";
		$html .= "<p style='text-align: center; font-weight:bold; margin:50px auto;'>".__('Dear user, you are connecting to the payment gateway, Please wait...', 'bina-woocommerce-payment')."</p>";

		return $html;
	}

	public function paymentConfig() : array
	{
		return [
			'default' => str_replace('bina_woocommerce_payment_', '', $this->id),
			'drivers' => [
				'local'         => [
					'callbackUrl'  => '',
					'title'        => __('Test Payment Gateway', 'bina-woocommerce-payment'),
					'description'  => __('This gateway is only for testing the validity of the payment process and canceling payments.', 'bina-woocommerce-payment'),
					'orderLabel'   => __('Order Number', 'bina-woocommerce-payment'),
					'amountLabel'  => __('Payable amount', 'bina-woocommerce-payment'),
					'payButton'    => __('Successful payment', 'bina-woocommerce-payment'),
					'cancelButton' => __('Failed payment', 'bina-woocommerce-payment'),
				],
				'fanavacard'    => [
					'baseUri'             => 'https://fcp.shaparak.ir',
					'apiPaymentUrl'       => '_ipgw_//payment/',
					'apiPurchaseUrl'      => 'ref-payment/RestServices/mts/generateTokenWithNoSign/',
					'apiVerificationUrl'  => 'ref-payment/RestServices/mts/verifyMerchantTrans/',
					'apiReverseAmountUrl' => 'ref-payment/RestServices/mts/reverseMerchantTrans/',
					'username'            => $this->get_option('username'),
					'password'            => $this->get_option('password'),
					'callbackUrl'         => '',
				],
				'atipay'        => [
					'atipayTokenUrl'           => 'https://mipg.atipay.net/v1/get-token',
					'atipayRedirectGatewayUrl' => 'https://mipg.atipay.net/v1/redirect-to-gateway',
					'atipayVerifyUrl'          => 'https://mipg.atipay.net/v1/verify-payment',
					'apikey'                   => $this->get_option('apikey'),
					'currency'                 => (get_woocommerce_currency() === 'IRT') ? 'T' : 'R',
					'callbackUrl'              => '',
					'description'              => $this->description,
				],
				'asanpardakht'  => [
					'apiPaymentUrl'     => 'https://asan.shaparak.ir',
					'apiRestPaymentUrl' => 'https://ipgrest.asanpardakht.ir/v1/',
					'username'          => $this->get_option('username'),
					'password'          => $this->get_option('password'),
					'merchantConfigID'  => $this->get_option('merchantConfigID'),
					'currency'          => (get_woocommerce_currency() === 'IRT') ? 'T' : 'R',
					'callbackUrl'       => '',
					'description'       => $this->description,
				],
				'behpardakht'   => [
					'apiPurchaseUrl'     => 'https://bpm.shaparak.ir/pgwchannel/services/pgw?wsdl',
					'apiPaymentUrl'      => 'https://bpm.shaparak.ir/pgwchannel/startpay.mellat',
					'apiVerificationUrl' => 'https://bpm.shaparak.ir/pgwchannel/services/pgw?wsdl',
					'terminalId'         => $this->get_option('terminalId'),
					'username'           => $this->get_option('username'),
					'password'           => $this->get_option('password'),
					'callbackUrl'        => '',
					'description'        => $this->description,
				],
				'digipay'       => [
					'apiOauthUrl'        => 'https://api.mydigipay.com/digipay/api/oauth/token',
					'apiPurchaseUrl'     => 'https://api.mydigipay.com/digipay/api/businesses/ticket?type=0',
					'apiPaymentUrl'      => 'https://api.mydigipay.com/digipay/api/purchases/ipg/pay/',
					'apiVerificationUrl' => 'https://api.mydigipay.com/digipay/api/purchases/verify/',
					'username'           => $this->get_option('username'),
					'password'           => $this->get_option('password'),
					'client_id'          => $this->get_option('client_id'),
					'client_secret'      => $this->get_option('client_secret'),
					'callbackUrl'        => '',
				],
				'etebarino'     => [
					'apiPurchaseUrl'     => 'https://api.etebarino.com/public/merchant/request-payment',
					'apiPaymentUrl'      => 'https://panel.etebarino.com/gateway/public/ipg',
					'apiVerificationUrl' => 'https://api.etebarino.com/public/merchant/verify-payment',
					'merchantId'         => $this->get_option('merchantId'),
					'terminalId'         => $this->get_option('terminalId'),
					'username'           => $this->get_option('username'),
					'password'           => $this->get_option('password'),
					'callbackUrl'        => '',
					'description'        => $this->description,
				],
				'idpay'         => [
					'apiPurchaseUrl'       => 'https://api.idpay.ir/v1.1/payment',
					'apiPaymentUrl'        => 'https://idpay.ir/p/ws/',
					'apiSandboxPaymentUrl' => 'https://idpay.ir/p/ws-sandbox/',
					'apiVerificationUrl'   => 'https://api.idpay.ir/v1.1/payment/verify',
					'merchantId'           => $this->get_option('merchantId'),
					'callbackUrl'          => '',
					'description'          => $this->description,
					'sandbox'              => $this->get_option('sandbox'),
				],
				'irankish'      => [
					'apiPurchaseUrl'     => 'https://ikc.shaparak.ir/api/v3/tokenization/make',
					'apiPaymentUrl'      => 'https://ikc.shaparak.ir/iuiv3/IPG/Index/',
					'apiVerificationUrl' => 'https://ikc.shaparak.ir/api/v3/confirmation/purchase',
					'callbackUrl'        => '',
					'description'        => $this->description,
					'terminalId'         => $this->get_option('terminalId'),
					'password'           => $this->get_option('password'),
					'acceptorId'         => $this->get_option('acceptorId'),
					'pubKey'             => $this->get_option('pubKey'),
				],
				'nextpay'       => [
					'apiPurchaseUrl'     => 'https://nextpay.org/nx/gateway/token',
					'apiPaymentUrl'      => 'https://nextpay.org/nx/gateway/payment/',
					'apiVerificationUrl' => 'https://nextpay.org/nx/gateway/verify',
					'merchantId'         => $this->get_option('merchantId'),
					'callbackUrl'        => '',
					'description'        => $this->description,
				],
				'parsian'       => [
					'apiPurchaseUrl'     => 'https://pec.shaparak.ir/NewIPGServices/Sale/SaleService.asmx?wsdl',
					'apiPaymentUrl'      => 'https://pec.shaparak.ir/NewIPG/',
					'apiVerificationUrl' => 'https://pec.shaparak.ir/NewIPGServices/Confirm/ConfirmService.asmx?wsdl',
					'merchantId'         => $this->get_option('merchantId'),
					'callbackUrl'        => '',
					'description'        => $this->description,
				],
				'pasargad'      => [
					'apiPaymentUrl'          => 'https://pep.shaparak.ir/payment.aspx',
					'apiGetToken'            => 'https://pep.shaparak.ir/Api/v1/Payment/GetToken',
					'apiCheckTransactionUrl' => 'https://pep.shaparak.ir/Api/v1/Payment/CheckTransactionResult',
					'apiVerificationUrl'     => 'https://pep.shaparak.ir/Api/v1/Payment/VerifyPayment',
					'merchantId'             => $this->get_option('terminalId'),
					'terminalCode'           => $this->get_option('terminalCode'),
					'certificate'            => $this->get_option('certificate'), // can be string (and set certificateType to xml_string) or a xml file path (and set certificateType to xml_file)
					'certificateType'        => 'xml_string', // can be: xml_file, xml_string
					'callbackUrl'            => '',
				],
				'payir'         => [
					'apiPurchaseUrl'     => 'https://pay.ir/pg/send',
					'apiPaymentUrl'      => 'https://pay.ir/pg/',
					'apiVerificationUrl' => 'https://pay.ir/pg/verify',
					'merchantId'         => $this->get_option('merchantId'), // set it to `test` for test environments
					'callbackUrl'        => '',
					'description'        => $this->description,
				],
				'paypal'        => [
					'apiPurchaseUrl'     => 'https://www.paypal.com/cgi-bin/webscr', //normal api
					'apiPaymentUrl'      => 'https://www.zarinpal.com/pg/StartPay/',
					'apiVerificationUrl' => 'https://ir.zarinpal.com/pg/services/WebGate/wsdl',

					'sandboxApiPurchaseUrl'     => 'https://www.sandbox.paypal.com/cgi-bin/webscr', // sandbox api
					'sandboxApiPaymentUrl'      => 'https://sandbox.zarinpal.com/pg/StartPay/',
					'sandboxApiVerificationUrl' => 'https://sandbox.zarinpal.com/pg/services/WebGate/wsdl',

					'mode'        => $this->get_option('mode') ?? 'normal', // can be normal, sandbox
					'currency'    => get_woocommerce_currency(),
					'id'          => $this->get_option('accountId'), // Specify the email of the PayPal Business account
					'callbackUrl' => '',
					'description' => $this->description,
				],
				'payping'       => [
					'apiPurchaseUrl'     => 'https://api.payping.ir/v2/pay/',
					'apiPaymentUrl'      => 'https://api.payping.ir/v2/pay/gotoipg/',
					'apiVerificationUrl' => 'https://api.payping.ir/v2/pay/verify/',
					'merchantId'         => $this->get_option('merchantId'),
					'callbackUrl'        => '',
					'description'        => $this->description,
				],
				'paystar'       => [
					'apiPurchaseUrl'     => 'https://core.paystar.ir/api/pardakht/create/',
					'apiPaymentUrl'      => 'https://core.paystar.ir/api/pardakht/payment/',
					'apiVerificationUrl' => 'https://core.paystar.ir/api/pardakht/verify/',
					'gatewayId'          => $this->get_option('gatewayId'),
					'signKey'            => $this->get_option('signKey'),
					'callbackUrl'        => '',
					'description'        => $this->description,
				],
				'poolam'        => [
					'apiPurchaseUrl'     => 'https://poolam.ir/invoice/request/',
					'apiPaymentUrl'      => 'https://poolam.ir/invoice/pay/',
					'apiVerificationUrl' => 'https://poolam.ir/invoice/check/',
					'merchantId'         => $this->get_option('merchantId'),
					'callbackUrl'        => '',
					'description'        => $this->description,
				],
				'sadad'         => [
					'apiPaymentByIdentityUrl'  => 'https://sadad.shaparak.ir/api/v0/PaymentByIdentity/PaymentRequest',
					'apiPaymentUrl'            => 'https://sadad.shaparak.ir/api/v0/Request/PaymentRequest',
					'apiPurchaseByIdentityUrl' => 'https://sadad.shaparak.ir/vpg/api/v0/Request/PaymentRequest',
					'apiPurchaseUrl'           => 'https://sadad.shaparak.ir/Purchase',
					'apiVerificationUrl'       => 'https://sadad.shaparak.ir/VPG/api/v0/Advice/Verify',
					'key'                      => $this->get_option('key'),
					'merchantId'               => $this->get_option('merchantId'),
					'terminalId'               => $this->get_option('terminalId'),
					'callbackUrl'              => '',
					'mode'                     => 'normal', // can be normal and PaymentByIdentity,
					'PaymentIdentity'          => '',
					'description'              => $this->description,
				],
				'saman'         => [
					'apiPurchaseUrl'     => 'https://sep.shaparak.ir/Payments/InitPayment.asmx?WSDL',
					'apiPaymentUrl'      => 'https://sep.shaparak.ir/payment.aspx',
					'apiVerificationUrl' => 'https://sep.shaparak.ir/payments/referencepayment.asmx?WSDL',
					'merchantId'         => $this->get_option('merchantId'),
					'callbackUrl'        => '',
					'description'        => $this->description,
				],
				'sep'           => [
					'apiGetToken'        => 'https://sep.shaparak.ir/onlinepg/onlinepg',
					'apiPaymentUrl'      => 'https://sep.shaparak.ir/OnlinePG/OnlinePG',
					'apiVerificationUrl' => 'https://sep.shaparak.ir/verifyTxnRandomSessionkey/ipg/VerifyTransaction',
					'terminalId'         => $this->get_option('terminalId'),
					'callbackUrl'        => '',
					'description'        => $this->description,
				],
				'sepehr'        => [
					'apiGetToken'        => 'https://mabna.shaparak.ir:8081/V1/PeymentApi/GetToken',
					'apiPaymentUrl'      => 'https://mabna.shaparak.ir:8080/pay',
					'apiVerificationUrl' => 'https://mabna.shaparak.ir:8081/V1/PeymentApi/Advice',
					'terminalId'         => $this->get_option('terminalId'),
					'callbackUrl'        => '',
					'description'        => $this->description,
				],
				'walleta'       => [
					'apiPurchaseUrl'     => 'https://cpg.walleta.ir/payment/request.json',
					'apiPaymentUrl'      => 'https://cpg.walleta.ir/ticket/',
					'apiVerificationUrl' => 'https://cpg.walleta.ir/payment/verify.json',
					'merchantId'         => $this->get_option('merchantId'),
					'callbackUrl'        => '',
					'description'        => $this->description,
				],
				'yekpay'        => [
					'apiPurchaseUrl'     => 'https://gate.yekpay.com/api/payment/server?wsdl',
					'apiPaymentUrl'      => 'https://gate.yekpay.com/api/payment/start/',
					'apiVerificationUrl' => 'https://gate.yekpay.com/api/payment/server?wsdl',
					'fromCurrencyCode'   => 978,
					'toCurrencyCode'     => 364,
					'merchantId'         => $this->get_option('merchantId'),
					'callbackUrl'        => '',
					'description'        => $this->description,
				],
				'zarinpal'      => [
					'apiPurchaseUrl'     => 'https://api.zarinpal.com/pg/v4/payment/request.json', // normal api
					'apiPaymentUrl'      => 'https://www.zarinpal.com/pg/StartPay/',
					'apiVerificationUrl' => 'https://api.zarinpal.com/pg/v4/payment/verify.json',

					'sandboxApiPurchaseUrl'     => 'https://sandbox.zarinpal.com/pg/services/WebGate/wsdl', // sandbox api
					'sandboxApiPaymentUrl'      => 'https://sandbox.zarinpal.com/pg/StartPay/',
					'sandboxApiVerificationUrl' => 'https://sandbox.zarinpal.com/pg/services/WebGate/wsdl',

					'zaringateApiPurchaseUrl'     => 'https://ir.zarinpal.com/pg/services/WebGate/wsdl', // zarinGate api
					'zaringateApiPaymentUrl'      => 'https://www.zarinpal.com/pg/StartPay/:authority/ZarinGate',
					'zaringateApiVerificationUrl' => 'https://ir.zarinpal.com/pg/services/WebGate/wsdl',

					'mode'        => $this->get_option('mode') ?? 'normal', // can be normal, sandbox, zaringate
					'merchantId'  => $this->get_option('merchantId'),
					'callbackUrl' => '',
					'description' => $this->description,
				],
				'zibal'         => [
					'apiPurchaseUrl'     => 'https://gateway.zibal.ir/v1/request', // normal api
					'apiPaymentUrl'      => 'https://gateway.zibal.ir/start/',
					'apiVerificationUrl' => 'https://gateway.zibal.ir/v1/verify',
					'mode'               => $this->get_option('mode') ?? 'normal', // can be normal, direct
					'merchantId'         => $this->get_option('merchantId'),
					'callbackUrl'        => '',
					'description'        => $this->description,
				],
				'sepordeh'      => [
					'apiPurchaseUrl'      => 'https://sepordeh.com/merchant/invoices/add',
					'apiPaymentUrl'       => 'https://sepordeh.com/merchant/invoices/pay/id:',
					'apiDirectPaymentUrl' => 'https://sepordeh.com/merchant/invoices/pay/automatic:true/id:',
					'apiVerificationUrl'  => 'https://sepordeh.com/merchant/invoices/verify',
					'mode'                => $this->get_option('mode') ?? 'normal', // can be normal, direct
					'merchantId'          => $this->get_option('merchantId'),
					'callbackUrl'         => '',
					'description'         => $this->description,
				],
				'rayanpay'      => [
					'apiPurchaseUrl' => 'https://bpm.shaparak.ir/pgwchannel/startpay.mellat',
					'apiTokenUrl'    => 'https://pms.rayanpay.com/api/v1/auth/token/generate',
					'apiPayStart'    => 'https://pms.rayanpay.com/api/v1/ipg/payment/start',
					'apiPayVerify'   => 'https://pms.rayanpay.com/api/v1/ipg/payment/response/parse',
					'username'       => $this->get_option('username'),
					'client_id'      => $this->get_option('client_id'),
					'password'       => $this->get_option('password'),
					'callbackUrl'    => '',
				],
				'sizpay'        => [
					'apiPurchaseUrl'     => 'https://rt.sizpay.ir/KimiaIPGRouteService.asmx?WSDL',
					'apiPaymentUrl'      => 'https://rt.sizpay.ir/Route/Payment',
					'apiVerificationUrl' => 'https://rt.sizpay.ir/KimiaIPGRouteService.asmx?WSDL',
					'merchantId'         => $this->get_option('merchantId'),
					'terminal'           => $this->get_option('terminal'),
					'username'           => $this->get_option('username'),
					'password'           => $this->get_option('password'),
					'SignData'           => $this->get_option('SignData'),
					'callbackUrl'        => '',
				],
				'vandar'        => [
					'apiPurchaseUrl'     => 'https://ipg.vandar.io/api/v3/send',
					'apiPaymentUrl'      => 'https://ipg.vandar.io/v3/',
					'apiVerificationUrl' => 'https://ipg.vandar.io/api/v3/verify',
					'callbackUrl'        => '',
					'merchantId'         => $this->get_option('merchantId'),
					'description'        => $this->description,
				],
				'aqayepardakht' => [
					'apiPurchaseUrl'       => 'https://panel.aqayepardakht.ir/api/v2/create',
					'apiPaymentUrl'        => 'https://panel.aqayepardakht.ir/startpay/',
					'apiPaymentUrlSandbox' => 'https://panel.aqayepardakht.ir/startpay/sandbox/',
					'apiVerificationUrl'   => 'https://panel.aqayepardakht.ir/api/v2/verify',
					'mode'                 => $this->get_option('mode') ?? 'normal', //normal | sandbox
					'callbackUrl'          => '',
					'pin'                  => $this->get_option('pin'),
					'invoice_id'           => '',
					'mobile'               => '',
					'email'                => '',
					'description'          => $this->description,
				],
				'azki'          => [
					'apiPaymentUrl' => 'https://api.azkivam.com',
					'callbackUrl'   => '',
					'fallbackUrl'   => '',
					'merchantId'    => $this->get_option('merchantId'),
					'key'           => $this->get_option('key'),
					'description'   => $this->description,
				],
				'payfa'         => [
					'apiPurchaseUrl'     => 'https://payment.payfa.com/v2/api/Transaction/Request',
					'apiPaymentUrl'      => 'https://payment.payfa.ir/v2/api/Transaction/Pay/',
					'apiVerificationUrl' => 'https://payment.payfa.com/v2/api/Transaction/Verify/',
					'callbackUrl'        => '',
					'apiKey'             => $this->get_option('apiKey'),
				],
			],
			'map'     => [
				'local'         => Local::class,
				'fanavacard'    => Fanavacard::class,
				'asanpardakht'  => Asanpardakht::class,
				'atipay'        => Atipay::class,
				'behpardakht'   => Behpardakht::class,
				'digipay'       => Digipay::class,
				'etebarino'     => Etebarino::class,
				'idpay'         => Idpay::class,
				'irankish'      => Irankish::class,
				'nextpay'       => Nextpay::class,
				'parsian'       => Parsian::class,
				'pasargad'      => Pasargad::class,
				'payir'         => Payir::class,
				'paypal'        => Paypal::class,
				'payping'       => Payping::class,
				'paystar'       => Paystar::class,
				'poolam'        => Poolam::class,
				'sadad'         => Sadad::class,
				'saman'         => Saman::class,
				'sep'           => SEP::class,
				'sepehr'        => Sepehr::class,
				'walleta'       => Walleta::class,
				'yekpay'        => Yekpay::class,
				'zarinpal'      => Zarinpal::class,
				'zibal'         => Zibal::class,
				'sepordeh'      => Sepordeh::class,
				'rayanpay'      => Rayanpay::class,
				'sizpay'        => Sizpay::class,
				'vandar'        => Vandar::class,
				'aqayepardakht' => Aqayepardakht::class,
				'azki'          => Azki::class,
				'payfa'         => Payfa::class,
			],
		];
	}
}