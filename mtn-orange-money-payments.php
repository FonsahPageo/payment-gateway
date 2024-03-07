<?php
/**
 * Plugin Name: YOGO'O
 * Plugin URI: https://akc-invent.cm
 * Description: WooCommerce plugin for MTN and Orange Money payments.
 * Version: 1.0.0
 * Author: AKC Invent
 * Author URI: https://wa.me/237675416098
 */

// Prevent direct access to this file.
defined('ABSPATH') || exit;

// Include the main plugin class.
require_once plugin_dir_path(__FILE__) . 'includes/class-mtn-orange-money-payments.php';

// Initialize the plugin.
add_action('plugins_loaded', 'yogoo_payments_init');
function yogoo_payments_init()
{
    $yogoo_payments = new Yogoo_Payments();
    $yogoo_payments->init();
}
