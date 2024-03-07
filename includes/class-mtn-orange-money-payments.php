<?php
class Yogoo_Payments
{
    public function init()
    {
        // Load the payment gateway class.
        require_once plugin_dir_path(__FILE__) . 'class-mtn-orange-money-payment-gateway.php';

        // Add the payment gateway to WooCommerce.
        add_filter('woocommerce_payment_gateways', array($this, 'add_payment_gateway'));
    }

    public function add_payment_gateway($gateways)
    {
        $gateways[] = 'Yogoo_Payment_Gateway';
        return $gateways;
    }
}
