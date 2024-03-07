<?php

require_once 'mtn-payment-api.php';

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
        wp_enqueue_style('yogoo_styles', plugins_url('../assets/css/yogoo.css', __FILE__));
    }

    public function enqueue_scripts()
    {
        wp_enqueue_script('payment-gateway-script', plugin_dir_url(__FILE__) . '../assets/js/yogoo.js', array('jquery'), '1.0', true);
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
            // 'testmode' => array(
            //     'title'       => 'Test mode', 'YOGO\'O',
            //     'label'       => 'Enable test payments', 'YOGO\'O',
            //     'type'        => 'checkbox',
            //     'description' => 'Place the payment gateway in test mode using test server.', 'YOGO\'O',
            //     'default'     => 'no',
            //     'desc_tip'    => true,
            // ),
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
        wp_add_inline_script('payment-gateway-script', "
        jQuery(function($) {
            $('input[name=\'payment_gateway\']').change(function() {
                var selectedGateway = $(this).val();
                $('#payment_fields > div').hide();
                $('#' + selectedGateway + '_payment_fields').show();
            });
        });
    ");
        if ($this->is_available()) {
            // Display the payment gateway radio buttons and icons
            echo '<div id="payment_method_selector">';
            echo '<p>' . __('<b>Select Payment Method: </b>', 'your-text-domain') . '</p>';
            echo '<label><input type="radio" name="payment_gateway" value="mtn"> ';
            echo '<img src="' . plugin_dir_url(__FILE__) . '../assets/img/mtn-pay.png" alt=" MTN Icon" class="payment-icon" />';
            echo __(' MTN mobile money', 'your-text-domain') . '</label><br>';
            echo '<label><input type="radio" name="payment_gateway" value="orange"> ';
            echo '<img src="' . plugin_dir_url(__FILE__) . '../assets/img/orange-pay.jpeg" alt=" Orange Icon" class="payment-icon" />';
            echo __(' Orange money', 'your-text-domain') . '</label>';
            echo '</div>';

            // Display the specific fields for each payment gateway
            echo '<div id="payment_fields">';
            echo '<div id="mtn_payment_fields" style="display:none;">';
            echo '<p>' . __('<b>Enter MTN mobile money number</b>', 'your-text-domain') . '</p>';
            echo '<input type="tel" name="mtn_field" placeholder="' . __('671234567', 'your-text-domain') . '">';
            echo '<img src="' . plugin_dir_url(__FILE__) . '../assets/img/mtn-pay.png" alt="MTN Icon" class="payment-icon-small" />';
            echo '</div>';

            echo '<div id="orange_payment_fields" style="display:none;">';
            echo '<p>' . __('<b>Enter Orange money number</b>', 'your-text-domain') . '</p>';
            echo '<input type="tel" name="orange_field" placeholder="' . __('691234567', 'your-text-domain') . '">';
            echo '<img src="' . plugin_dir_url(__FILE__) . '../assets/img/orange-pay.jpeg" alt="Orange Icon" class="payment-icon-small" />';
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
                wc_add_notice(__('Please select a payment method.', 'your-text-domain'), 'error');
            } elseif ($payment_gateway === 'mtn' && empty($mtn_field)) {
                wc_add_notice(__('Please enter an MTN mobile number.', 'your-text-domain'), 'error');
            } elseif ($payment_gateway === 'orange' && empty($orange_field)) {
                wc_add_notice(__('Please enter an Orange mobile number.', 'your-text-domain'), 'error');
            }
        }
        return true;
    }

    public function process_payment($order_id)
    {
        $order = wc_get_order($order_id);
        // $amount = $order->get_total();
        // $currency = "CFA";
        // $description = "Payment from : " . site_url() . " for order : " . $order->get_id();
        // $external_reference = $this->guidv4();
        // $order_currency = $order->get_currency();
        // $order_currency = strtoupper($order_currency);
        $payment_gateway = $this->get_option('payment_gateway');


        if ($payment_gateway === 'mtn') {
            $payment_successful = $this->process_payment_via_mtn($order);
        } elseif ($payment_gateway === 'orange') {
            $payment_successful = $this->process_payment_via_orange($order);
        }

        if ($payment_successful) {
            $order->update_status('completed', __('Payment completed', 'text-domain'));

            $order->reduce_order_stock();

            WC()->cart->empty_cart();

            return array(
                'result'   => 'success',
                'redirect' => $this->get_return_url($order)
            );
        } else {
            $order->update_status('failed', __('Payment failed', 'text-domain'));

            return array(
                'result'   => 'fail',
                'redirect' => $this->get_return_url($order)
            );
        }
    }

    public function process_payment_via_mtn($order)
    {
        $mtn_payment_field = isset($_POST['mtn_field']) ? sanitize_text_field($_POST['mtn_field']) : '';

        $mtn_payment_api = new MTN_Payment_API();

        $payment_data = array(
            'order_id' => $order->get_id(),
            'amount' => $order->get_total(),
            'currency' => $order->get_currency(),
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
            $order->update_status('failed', __('MTN Payment failed: ' . $payment_response['error_message'], 'text-domain'));
            return false;
        }
    }

    private function process_payment_via_orange($order)
    {
        $orange_payment_field = isset($_POST['orange_field']) ? sanitize_text_field($_POST['orange_field']) : '';

        $payment_response = $orange_payment_api->process_payment($payment_data);

        $payment_data = array(
            'order_id' => $order->get_id(),
            'amount' => $order->get_total(),
            'orange_payment_field' => $orange_payment_field,
        );

        $payment_response = $orange_payment_api->send_payment_request($payment_data);

        if ($payment_response['success']) {
            $order->update_meta_data('orange_transaction_id', $payment_response['transaction_id']);
            $order->save();

            return true;
        } else {

            return false;
        }
    }
}
