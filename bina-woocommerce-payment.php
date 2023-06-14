<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.binacity.com
 * @since             1.0.0
 * @package           Bina_Woocommerce_Payment
 *
 * @wordpress-plugin
 * Plugin Name:       Bina WooCommerce Payment
 * Plugin URI:        https://www.binacity.com
 * Description:       This plugin will help you connect all Iranian, cryptocurrency, and international payment gateways to your WooCommerce store with just one plugin.
 * Version:           1.0.5
 * Author:            Hadi Khanian
 * Author URI:        https://www.binacity.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       bina-woocommerce-payment
 * Domain Path:       /languages
 */

// Add PSR-4 Autoload
use Bina\WoocommercePayment\Includes\Bina_Woocommerce_Payment;
use Bina\WoocommercePayment\Includes\Bina_Woocommerce_Payment_Activator;
use Bina\WoocommercePayment\Includes\Bina_Woocommerce_Payment_Deactivation;

require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
const BINA_WOOCOMMERCE_PAYMENT_VERSION = '1.0.5';

/**
 * The code that runs during plugin activation.
 */
function activate_bina_woocommerce_payment()
{
	Bina_Woocommerce_Payment_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_bina_woocommerce_payment()
{
	Bina_Woocommerce_Payment_Deactivation::deactivate();
}

register_activation_hook(__FILE__, 'activate_bina_woocommerce_payment');
register_deactivation_hook(__FILE__, 'deactivate_bina_woocommerce_payment');

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_bina_woocommerce_payment()
{
	$plugin = new Bina_Woocommerce_Payment();
	$plugin->run();
}

run_bina_woocommerce_payment();
