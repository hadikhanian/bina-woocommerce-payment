<?php

namespace Bina\WoocommercePayment\Admin;

use Bina\WoocommercePayment\Core\Bina_WooCommerce_Payment_Core;

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

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Bina_Woocommerce_Payment_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Bina_Woocommerce_Payment_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/bina-woocommerce-payment-admin.css', array(), $this->version, 'all');

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Bina_Woocommerce_Payment_Loader as all the hooks are defined
		 * in that particular class.
		 *
		 * The Bina_Woocommerce_Payment_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/bina-woocommerce-payment-admin.js', array('jquery'), $this->version, false);

	}

	public function plugins_loaded()
	{
		add_action('woocommerce_payment_init', array($this, 'woocommerce_shipping_init'));
		add_filter('woocommerce_payment_methods', array($this, 'payment_gateways'));
	}

	public function payment_gateways(array $methods): array
	{
		$methods[] = Bina_WooCommerce_Payment_Core::class;
		return $methods;
	}

}
