<?php

namespace Bina\WoocommercePayment\Includes;

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://www.binacity.com
 * @since      1.0.0
 *
 * @package    Bina_Woocommerce_Payment
 * @subpackage Bina_Woocommerce_Payment/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Bina_Woocommerce_Payment
 * @subpackage Bina_Woocommerce_Payment/includes
 * @author     Hadi Khanian <hadi.khanian@gmail.com>
 */
class Bina_Woocommerce_Payment_i18n
{
    /**
     * Load the plugin text domain for translation.
     *
     * @since    1.0.0
     */
    public function load_plugin_textdomain()
    {

        load_plugin_textdomain(
            'bina-woocommerce-payment',
            false,
            dirname(plugin_basename(__FILE__), 2).'/languages/'
        );

    }


}
