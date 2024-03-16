<?php

/**
 * Plugin Name: YOGO'O
 * Plugin URI: https://akc-invent.cm
 * Description: A WordPress plugin that allow vendors to accept payments through MTN and Orange money.
 * Text Domain: yogoo
 * Version: 1.2.0
 * Author: AKC Invent
 * Author URI: https://akc-invent.cm
 */

defined('ABSPATH') || exit;

// Initialize the plugin.
add_action('woocommerce_loaded', 'yogoo_payment_gateway_init');

function add_yogoo_payment_gateway($gateways)
{
    $gateways[] = 'Yogoo_Payment_Gateway';
    return $gateways;
}

function yogoo_payment_gateway_init()
{
    $yogoo_payment_gateway = new Yogoo_Payment_Gateway();
    add_action('woocommerce_checkout_order_processed', array($yogoo_payment_gateway, 'process_payment'), 10, 3);
    add_filter('woocommerce_payment_gateways', 'add_yogoo_payment_gateway');
}

require_once 'includes/mtn-payment-api.php';

class Yogoo_Payment_Gateway extends WC_Payment_Gateway
{
    public function __construct()
    {
        $this->id = 'yogoo';
        $this->has_fields = true;
        $this->method_title = 'YOGO\'O';
        $this->method_description = 'Pay for products with MTN or Orange Money.';
        $this->supports = array(
            'products',
            'refunds'
        );
        $this->init_form_fields();
        $this->init_settings();
        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    public function enqueue_styles()
    {
        wp_enqueue_style('yogoo_styles', plugins_url('assets/css/yogoo.css', __FILE__));
    }

    public function enqueue_scripts()
    {
        wp_enqueue_script('yogoo_script', plugin_dir_url(__FILE__) . 'assets/js/yogoo.js', array('jquery'), '1.0', true);
    }

    public function init_form_fields()
    {
        $this->form_fields = array(
            'enabled' => array(
                'title' => 'Enable/Disable',
                'type' => 'checkbox',
                'label' => 'Enable Mobile Money Payments with YOGO\'O',
                'default' => 'no'
            ),
            'title' => array(
                'title' => 'Title',
                'type' => 'text',
                'description' => 'YOGO\'O is a payment gateway that allows users to pay for products <and>
                <or></or> services with MTN or Orange. You can integrate with with your E-commerce site to facilitate payments',
                'default' => 'YOGO\'O Mobile Money',
                'desc_tip'    => true
            ),
            'description' => array(
                'title' => 'Description',
                'type' => 'text',
                'description' => 'Payment method description shown during checkout.',
                'default' => 'Pay for products with either MTN or Orange money through YOGO\'O',
                'desc_tip'    => true
            ),

        );
    }

    /**
     * Get the icon HTML based on the selected payment gateway
     */
    public function get_payment_icon_html()
    {
        $icon_url = $this->get_payment_icon_url();
        if (!empty($icon_url)) {
            return '<img src="' . esc_url($icon_url) . '" class="payment-gateway-icon" />';
        }
        return '';
    }

    public function payment_fields()
    {
        $script = "
        jQuery(function($) {
        $('input[name=\'payment_gateway\']').change(function() {
            var selectedGateway = $(this).val();
            $('#payment_fields > div').hide();
            $('#' + selectedGateway + '_payment_fields').show();
        });
    });
    ";
        wp_add_inline_script('payment-gateway-script', $script, 'after');

        if ($this->is_available()) {
            echo '<div id="payment_method_selector">';
            echo '<p>' . __('<b>Select Payment Method: </b>', 'yogoo') . '</p>';
            echo '<label><input type="radio" name="payment_gateway" value="mtn"> ';
            echo '<img src="' . plugin_dir_url(__FILE__) . 'assets/img/mtn-pay.png" alt=" MTN Icon" class="payment-icon" />';
            echo __(' MTN mobile money', 'yogoo') . '</label><br>';
            echo '<label><input type="radio" name="payment_gateway" value="orange"> ';
            echo '<img src="' . plugin_dir_url(__FILE__) . 'assets/img/orange-pay.jpeg" alt=" Orange Icon" class="payment-icon" />';
            echo __(' Orange money', 'yogoo') . '</label>';
            echo '</div>';

            echo '<div id="payment_fields">';
            echo '<div id="mtn_payment_fields" style="display:none;">';
            echo '<p>' . __('<b>Enter MTN MOMO number</b>', 'yogoo') . '</p>';
            echo '<input type="tel" name="mtn_field" placeholder="' . __('671234567', 'yogoo') . '">';
            echo '<img src="' . plugin_dir_url(__FILE__) . 'assets/img/mtn-pay.png" alt="MTN Icon" class="payment-icon-small" />';
            echo '</div>';

            echo '<div id="orange_payment_fields" style="display:none;">';
            echo '<p>' . __('<b>Enter Orange money number</b>', 'yogoo') . '</p>';
            echo '<input type="tel" name="orange_field" placeholder="' . __('691234567', 'yogoo') . '">';
            echo '<img src="' . plugin_dir_url(__FILE__) . 'assets/img/orange-pay.jpeg" alt="Orange Icon" class="payment-icon-small" />';
            echo '</div>';
            echo '</div>';

            // Script to show/hide payment fields based on the selected payment gateway
            echo '<script>
            jQuery(function($) {
                $("input[name=\'payment_gateway\']").change(function() {
                    var selectedGateway = $(this).val();
                    $("#payment_fields > div").hide();
                    $("#" + selectedGateway + "_payment_fields").show();
                });
            });
            </script>';
        }
    }

    public function validate_fields()
    {
        if ($this->enabled === 'yes') {
            $mtn_field = isset($_POST['mtn_field']) ? $_POST['mtn_field'] : '';
            $orange_field = isset($_POST['orange_field']) ? $_POST['orange_field'] : '';
            $payment_gateway = isset($_POST['payment_gateway']) ? $_POST['payment_gateway'] : '';

            if (empty($payment_gateway)) {
                wc_add_notice(__('Please select a payment method.', 'yogoo'), 'error');
            } elseif ($payment_gateway === 'mtn' && empty($mtn_field)) {
                wc_add_notice(__('Please enter an MTN mobile number.', 'yogoo'), 'error');
            } elseif ($payment_gateway === 'orange' && empty($orange_field)) {
                wc_add_notice(__('Please enter an Orange mobile number.', 'yogoo'), 'error');
            }
        }
    }

    public function process_payment($order_id)
    {
        $mtn_field = isset($_POST['mtn_field']) ? $_POST['mtn_field'] : '';
        $orange_field = isset($_POST['orange_field']) ? $_POST['orange_field'] : '';
        $mtn_field = sanitize_text_field($mtn_field);
        $orange_field = sanitize_text_field($orange_field);

        $order = wc_get_order($order_id);
        $payment_gateway = $this->get_option('payment_gateway');

        $payment_successful = false;

        if ($payment_gateway === 'mtn' && !empty($mtn_field)) {
            $payment_successful = $this->process_payment_via_mtn($order, $payment_gateway);
        } elseif ($payment_gateway === 'orange' && !empty($orange_field)) {
            $payment_successful = $this->process_payment_via_orange($order, $payment_gateway);
        }

        if ($payment_successful) {
            $order->update_status('completed', __('Payment completed', 'yogoo'));
            $order->reduce_order_stock();
            WC()->cart->empty_cart();

            return array(
                'result'   => 'Payment made successfully',
                'redirect' => $this->get_return_url($order)
            );
        } else {
            $order->update_status('failed', __('Payment failed', 'yogoo'));

            return array(
                'result'   => 'Failed to make payment',
                'redirect' => $this->get_return_url($order)
            );
        }
    }

    public function process_payment_via_mtn($order)
    {
        $mtn_payment_field = isset($_POST['mtn_field']) ? sanitize_text_field($_POST['mtn_field']) : '';

        $subscription_key = '85e74c865f50406abdd2b54a4800cf92';
        $callback_url = 'https://webhook.site/f17a4674-fb42-431f-b2d0-743197bcbef2';
        $x_reference_id = '3daabc95-24ee-4add-877e-9a09e8a4d8cc';

        $mtn_payment_api = new MTN_Payment_API($subscription_key, $callback_url, $x_reference_id);

        $mtn_payment_api->create_api_user();
        $mtn_payment_api->get_api_key();
        $mtn_payment_api->get_access_token();
        $amount = $order->get_total();
        $mtn_payment_api->process_payment($amount, $mtn_payment_field);

        $payment_data = array(
            'order_id' => $order->get_id(),
            'amount' => $order->get_total(),
            'number' => $mtn_payment_field,
        );

        $payment_response = $mtn_payment_api->process_payment($payment_data, $mtn_payment_field);

        if ($payment_response['success']) {
            $order->update_meta_data('mtn_transaction_id', $payment_response['transaction_id']);
            $order->add_order_note('MTN Payment successful. Transaction ID: ' . $payment_response['transaction_id']);
            $order->save();

            return true;
        } else {
            error_log('MTN Payment failed: ' . json_encode($payment_response));
            $order->update_status('failed', __('MTN Payment failed: ' . $payment_response['error_message'], 'yogoo'));
            return false;
        }
    }
}
