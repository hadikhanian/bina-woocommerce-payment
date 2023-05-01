<?php

namespace Bina\WoocommercePayment\Includes;

use Bina\WoocommercePayment\Admin\Bina_Woocommerce_Payment_Admin;
use Bina\WoocommercePayment\Public\Bina_Woocommerce_Payment_Public;

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.binacity.com
 * @since      1.0.0
 *
 * @package    Bina_Woocommerce_Payment
 * @subpackage Bina_Woocommerce_Payment/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Bina_Woocommerce_Payment
 * @subpackage Bina_Woocommerce_Payment/includes
 * @author     Hadi Khanian <hadi.khanian@gmail.com>
 */
class Bina_Woocommerce_Payment
{

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Bina_Woocommerce_Payment_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected Bina_Woocommerce_Payment_Loader $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected string $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected string $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct()
	{
		if ( defined('BINA_WOOCOMMERCE_PAYMENT_VERSION') ) {
			$this->version = BINA_WOOCOMMERCE_PAYMENT_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'bina-woocommerce-payment';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @return    string    The name of the plugin.
	 * @since     1.0.0
	 */
	public function get_plugin_name() : string
	{
		return $this->plugin_name;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return    string    The version number of the plugin.
	 * @since     1.0.0
	 */
	public function get_version() : string
	{
		return $this->version;
	}

	/**
	 * Run the loader to execute all the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run()
	{
		$this->loader->run();
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return    Bina_Woocommerce_Payment_Loader    Orchestrates the hooks of the plugin.
	 * @since     1.0.0
	 */
	public function get_loader() : Bina_Woocommerce_Payment_Loader
	{
		return $this->loader;
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Bina_Woocommerce_Payment_Loader. Orchestrates the hooks of the plugin.
	 * - Bina_Woocommerce_Payment_i18n. Defines internationalization functionality.
	 * - Bina_Woocommerce_Payment_Admin. Defines all hooks for the admin area.
	 * - Bina_Woocommerce_Payment_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies()
	{
		$this->loader = new Bina_Woocommerce_Payment_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Bina_Woocommerce_Payment_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale()
	{
		$plugin_i18n = new Bina_Woocommerce_Payment_i18n();
		$this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
	}

	/**
	 * Register all the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks()
	{
		$plugin_admin = new Bina_Woocommerce_Payment_Admin($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action('plugins_loaded', $plugin_admin, 'plugins_loaded');
		$this->loader->add_action('woocommerce_payment_gateways', $plugin_admin, 'payment_gateways');
	}

	/**
	 * Register all the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks()
	{
		$plugin_public = new Bina_Woocommerce_Payment_Public($this->get_plugin_name(), $this->get_version());
	}

}
