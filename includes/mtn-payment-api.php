<?php

class MTN_Payment_API
{
    private $subscription_key = '85e74c865f50406abdd2b54a4800cf92';
    private $api_key = 'c258f37314cb4185a8034ab3f3e3a0dc';
    private $callback_url = 'https://webhook.site/f17a4674-fb42-431f-b2d0-743197bcbef2';
    private $access_token = null;
    private $x_reference_id = 'bf525d7f-3758-4214-a740-27b8c0969a49';
    private $authorization;

    public function __construct()
    {
        $auth = $this->x_reference_id.$this->api_key;
        $this->authorization = 'Basic '. base64_encode($auth);
    }

    public function create_api_user()
    {
        $create_user_url = 'https://sandbox.momodeveloper.mtn.com/v1_0/apiuser';
        $headers = array(
            'Authorization: Basic ' . $this->subscription_key,
            'Ocp-Apim-Subscription-Key: ' . $this->subscription_key,
            'X-Reference-Id: ' . $this->x_reference_id,
            'Content-Type: application/json'
        );
        $body = array(
            'providerCallbackHost' => 'https://webhook.site'
        );

        $response = wp_remote_post($create_user_url, array(
            'headers' => $headers,
            'body' => wp_json_encode($data)
        ));

        if (wp_remote_retrieve_response_code($response) === 200) {
            $body = json_decode(wp_remote_retrieve_body($response), true);
            // Handle the response, e.g., store the newly created user's information
            return $body;
        } else {
            // Handle the error, e.g., log the error message
            return false;
        }
    }

    public function get_access_token()
    {
        $auth_url = 'https://sandbox.momodeveloper.mtn.com/collection/token/';
        $headers = array(
            'Authorization: ' . $this->authorization,
            'Ocp-Apim-Subscription-Key: ' . $this->subscription_key,
            'Cache-Control' => 'no-cache'
        );

        $response = wp_remote_post($auth_url, array(
            'method' => 'POST',
            'headers' => $headers
        ));

        if (wp_remote_retrieve_response_code($response) === 200) {
            $body = json_decode(wp_remote_retrieve_body($response), true);
            $access_token = $body['access_token'];
            $this->access_token = $access_token;
            return $access_token;
        } else {
            wc_add_notice('Unable to get access token', 'error');
        }
    }

    public function process_payment($amount, $mtn_payment_field)
    {
        if (!$this->access_token) {
            $this->get_access_token();
        }

        $payment_url = 'https://sandbox.momodeveloper.mtn.com/collection/v1_0/requesttopay';
        $headers = array(
            'Authorization: Bearer ' . $this->access_token,
            'X-Reference-Id: ' . $this->x_reference_id,
            'X-Callback-Url: ' . $this->callback_url,
            'X-Target-Environment' => 'sandbox'
        );
        $data = array(
            'amount' => $amount,
            'currency' => 'EUR',
            'externalId' => 'ash101',
            'payer' => array(
                'partyIdType' => 'MSISDN',
                'partyId' => $mtn_payment_field
            ),
            'payerMessage' => 'Validate your transaction',
            'payeeNote' => 'Payment for produc'
        );

        $response = wp_remote_post($payment_url, array(
            'headers' => $headers,
            'body' => wp_json_encode($data)
        ));

        if (wp_remote_retrieve_response_code($response) === 201) {
            // Payment request successfully submitted
            wc_add_notice('Transaction made succesfully', 'success');
            return true;
        } else {
            // Payment request failed
            wc_add_notice('Transaction failed', 'error');
            return false;
        }
    }

    public function validate_payment($payment_data)
    {
        $order_id = $payment_data['order_id']; // Assuming the payment data contains the order ID
        $order = wc_get_order($order_id);

        if ($payment_data['status'] === 'successful') {
            // Mark the order as completed and add an order note
            $order->update_status('completed');
            $order->add_order_note(__('MTN Mobile Money payment received.', 'yogoo'));
        } elseif ($payment_data['status'] === 'failed') {
            // Mark the order as failed and add an order note with the error message
            $order->update_status('failed');
            $order->add_order_note(__('MTN Mobile Money payment failed: ', 'yogoo') . $payment_data['error_message']);
        } else {
            // Handle other possible payment statuses, e.g., pending
        }
    }
}
