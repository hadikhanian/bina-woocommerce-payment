<?php

namespace Bina\WoocommercePayment\Public;

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.binacity.com
 * @since      1.0.0
 *
 * @package    Bina_Woocommerce_Payment
 * @subpackage Bina_Woocommerce_Payment/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Bina_Woocommerce_Payment
 * @subpackage Bina_Woocommerce_Payment/public
 * @author     Hadi Khanian <hadi.khanian@gmail.com>
 */
class Bina_Woocommerce_Payment_Public
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
	 * @param string $plugin_name The name of the plugin.
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
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{
		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__).'css/bina-woocommerce-payment-public.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{
		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__).'js/bina-woocommerce-payment-public.js', array('jquery'), $this->version, false);
	}

}
