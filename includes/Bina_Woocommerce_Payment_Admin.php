<?php

namespace Bina\WoocommercePayment\Admin;

use Bina\WoocommercePayment\Core\Bina_Woocommerce_Payment_Sep;
use Bina\WoocommercePayment\Core\Bina_Woocommerce_Payment_Azki;
use Bina\WoocommercePayment\Core\Bina_Woocommerce_Payment_Local;
use Bina\WoocommercePayment\Core\Bina_Woocommerce_Payment_Idpay;
use Bina\WoocommercePayment\Core\Bina_Woocommerce_Payment_Payir;
use Bina\WoocommercePayment\Core\Bina_Woocommerce_Payment_Sadad;
use Bina\WoocommercePayment\Core\Bina_Woocommerce_Payment_Saman;
use Bina\WoocommercePayment\Core\Bina_Woocommerce_Payment_Zibal;
use Bina\WoocommercePayment\Core\Bina_Woocommerce_Payment_Payfa;
use Bina\WoocommercePayment\Core\Bina_Woocommerce_Payment_Atipay;
use Bina\WoocommercePayment\Core\Bina_Woocommerce_Payment_Paypal;
use Bina\WoocommercePayment\Core\Bina_Woocommerce_Payment_Poolam;
use Bina\WoocommercePayment\Core\Bina_Woocommerce_Payment_Sepehr;
use Bina\WoocommercePayment\Core\Bina_Woocommerce_Payment_Yekpay;
use Bina\WoocommercePayment\Core\Bina_Woocommerce_Payment_Sizpay;
use Bina\WoocommercePayment\Core\Bina_Woocommerce_Payment_Vandar;
use Bina\WoocommercePayment\Core\Bina_Woocommerce_Payment_Payping;
use Bina\WoocommercePayment\Core\Bina_Woocommerce_Payment_Digipay;
use Bina\WoocommercePayment\Core\Bina_Woocommerce_Payment_Nextpay;
use Bina\WoocommercePayment\Core\Bina_Woocommerce_Payment_Parsian;
use Bina\WoocommercePayment\Core\Bina_Woocommerce_Payment_Paystar;
use Bina\WoocommercePayment\Core\Bina_Woocommerce_Payment_Walleta;
use Bina\WoocommercePayment\Core\Bina_Woocommerce_Payment_Irankish;
use Bina\WoocommercePayment\Core\Bina_Woocommerce_Payment_Pasargad;
use Bina\WoocommercePayment\Core\Bina_Woocommerce_Payment_Zarinpal;
use Bina\WoocommercePayment\Core\Bina_Woocommerce_Payment_Sepordeh;
use Bina\WoocommercePayment\Core\Bina_Woocommerce_Payment_Rayanpay;
use Bina\WoocommercePayment\Core\Bina_Woocommerce_Payment_Etebarino;
use Bina\WoocommercePayment\Core\Bina_Woocommerce_Payment_Fanavacard;
use Bina\WoocommercePayment\Core\Bina_Woocommerce_Payment_Behpardakht;
use Bina\WoocommercePayment\Core\Bina_Woocommerce_Payment_Asanpardakht;
use Bina\WoocommercePayment\Core\Bina_Woocommerce_Payment_Aqayepardakht;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.binacity.com
 * @since      1.0.0
 *
 * @package    Bina_Woocommerce_Payment
 * @subpackage Bina_Woocommerce_Payment/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Bina_Woocommerce_Payment
 * @subpackage Bina_Woocommerce_Payment/admin
 * @author     Hadi Khanian <hadi.khanian@gmail.com>
 */
class Bina_Woocommerce_Payment_Admin
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private string $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private string $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct(string $plugin_name, string $version)
	{
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{
		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__).'css/bina-woocommerce-payment-admin.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{
		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__).'js/bina-woocommerce-payment-admin.js', array('jquery'), $this->version, false);
	}

	public function plugins_loaded()
	{
		add_action('woocommerce_payment_init', array($this, 'woocommerce_shipping_init'));
		add_filter('woocommerce_payment_methods', array($this, 'payment_gateways'));
	}

	public function payment_gateways(array $methods) : array
	{
		$methods[] = Bina_Woocommerce_Payment_Local::class;
		$methods[] = Bina_Woocommerce_Payment_Asanpardakht::class;
		$methods[] = Bina_Woocommerce_Payment_Payping::class;
		$methods[] = Bina_Woocommerce_Payment_Fanavacard::class;
		$methods[] = Bina_Woocommerce_Payment_Atipay::class;
		$methods[] = Bina_Woocommerce_Payment_Behpardakht::class;
		$methods[] = Bina_Woocommerce_Payment_Digipay::class;
		$methods[] = Bina_Woocommerce_Payment_Etebarino::class;
		$methods[] = Bina_Woocommerce_Payment_Idpay::class;
		$methods[] = Bina_Woocommerce_Payment_Irankish::class;
		$methods[] = Bina_Woocommerce_Payment_Nextpay::class;
		$methods[] = Bina_Woocommerce_Payment_Parsian::class;
		$methods[] = Bina_Woocommerce_Payment_Pasargad::class;
		$methods[] = Bina_Woocommerce_Payment_Payir::class;
		$methods[] = Bina_Woocommerce_Payment_Paypal::class;
		$methods[] = Bina_Woocommerce_Payment_Paystar::class;
		$methods[] = Bina_Woocommerce_Payment_Poolam::class;
		$methods[] = Bina_Woocommerce_Payment_Sadad::class;
		$methods[] = Bina_Woocommerce_Payment_Saman::class;
		$methods[] = Bina_Woocommerce_Payment_Sep::class;
		$methods[] = Bina_Woocommerce_Payment_Sepehr::class;
		$methods[] = Bina_Woocommerce_Payment_Walleta::class;
		$methods[] = Bina_Woocommerce_Payment_Yekpay::class;
		$methods[] = Bina_Woocommerce_Payment_Zarinpal::class;
		$methods[] = Bina_Woocommerce_Payment_Zibal::class;
		$methods[] = Bina_Woocommerce_Payment_Sepordeh::class;
		$methods[] = Bina_Woocommerce_Payment_Rayanpay::class;
		$methods[] = Bina_Woocommerce_Payment_Sizpay::class;
		$methods[] = Bina_Woocommerce_Payment_Vandar::class;
		$methods[] = Bina_Woocommerce_Payment_Aqayepardakht::class;
		$methods[] = Bina_Woocommerce_Payment_Azki::class;
		$methods[] = Bina_Woocommerce_Payment_Payfa::class;

		return $methods;
	}

}
