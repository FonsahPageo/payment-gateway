<?php

class MTN_Payment_API
{
    private $subscription_key;
    private $callback_url;
    private $x_reference_id;
    private $authorization;
    private $api_key;
    private $access_token;

    public function __construct($subscription_key, $callback_url, $x_reference_id, $api_key, $access_token)
    {
        $this->subscription_key = $subscription_key;
        $this->callback_url = $callback_url;
        $this->x_reference_id = $x_reference_id;
        $this->api_key = $api_key;
        $this->access_token = $access_token;

        // Hook the initialization method to the appropriate action
        add_action('woocommerce_init', array($this, 'initialize'));
    }

    public function initialize()
    {
        $this->create_api_user();
        $this->get_api_key();
        $this->get_access_token();
        $this->process_payment();
    }

    public function log_error($message, $display_error = true)
    {
        $log_path = plugin_dir_path(__FILE__) . '../logs/error.log';
        $timestamp = date('d-m-y H:i:s');
        $log_message = "[{$timestamp}] {$message}\n";
        file_put_contents($log_path, $log_message, FILE_APPEND);

        $log_message .= "API Request: " . wp_json_encode($this->last_request) . "\n";
        $log_message .= "API Response: " . wp_json_encode($this->last_response) . "\n";

        if ($display_error) {
            wc_add_notice($message, 'error');
        }
    }
    public function create_api_user()
    {
        $create_user_url = 'https://sandbox.momodeveloper.mtn.com/v1_0/apiuser';
        $headers = array(
            'Ocp-Apim-Subscription-Key' => $this->subscription_key,
            'X-Reference-Id' => $this->x_reference_id,
            'Content-Type: application/json'
        );
        $body = array(
            'providerCallbackHost' => $this->callback_url
        );

        $response = wp_remote_post($create_user_url, array(
            'method' => 'POST',
            'headers' => $headers,
            'body' => wp_json_encode($body)
        ));

        $this->last_request = array(
            'url' => $create_user_url,
            'method' => 'POST',
            'headers' => $headers,
            'body' => $body
        );
        $this->last_response = array(
            'status' => wp_remote_retrieve_response_code($response),
            'body' => wp_remote_retrieve_body($response)
        );

        if (wp_remote_retrieve_response_code($response) === 201) {
            $body = wp_json_decode(wp_remote_retrieve_body($response), true);
            return $body;
        } else {
            $log_message = 'Unable to create API user';
            $this->log_error($log_message);
            wc_add_notice($log_message, 'error');
        }
    }

    public function get_api_key()
    {
        $create_key_url = "https://sandbox.momodeveloper.mtn.com/v1_0/apiuser/{$this->x_reference_id}/apikey";
        $headers = array(
            'Ocp-Apim-Subscription-Key' => $this->subscription_key,
            'X-Reference-Id' => $this->x_reference_id,
            'Cache-control: no-cache'
        );

        $response = wp_remote_get($create_key_url, array(
            'method' => 'POST',
            'headers' => $headers
        ));

        $this->last_request = array(
            'url' => $create_key_url,
            'method' => 'POST',
            'headers' => $headers,
        );
        $this->last_response = array(
            'status' => wp_remote_retrieve_response_code($response),
            'body' => wp_remote_retrieve_body($response)
        );

        if (wp_remote_retrieve_response_code($response) === 201) {
            $body = wp_json_decode(wp_remote_retrieve_body($response), true);
            $api_key = $body['apiKey'];
            $this->api_key = $api_key;
            return $api_key;
        } else {
            $log_message = 'Unable to get API Key';
            $this->log_error($log_message);
            wc_add_notice($log_message, 'error');
        }
    }

    public function get_access_token()
    {
        if (!$this->api_key) {
            $this->get_api_key();
        }

        $auth = $this->x_reference_id . $this->api_key;
        $this->authorization = 'Basic ' . base64_encode($auth);

        $create_token_url = 'https://sandbox.momodeveloper.mtn.com/collection/token/';
        $headers = array(
            'Authorization' => $this->authorization,
            'Ocp-Apim-Subscription-Key' => $this->subscription_key,
            'Cache-Control: no-cache'
        );

        $response = wp_remote_get($create_token_url, array(
            'method' => 'POST',
            'headers' => $headers
        ));

        $this->last_request = array(
            'url' => $create_token_url,
            'method' => 'POST',
            'headers' => $headers,
            'body' => $body
        );
        $this->last_response = array(
            'status' => wp_remote_retrieve_response_code($response),
            'body' => wp_remote_retrieve_body($response)
        );

        if (wp_remote_retrieve_response_code($response) === 200) {
            $body = wp_json_decode(wp_remote_retrieve_body($response), true);
            $access_token = $body['access_token'];
            $this->access_token = $access_token;
            return $access_token;
        } else {
            $log_message = 'Unable to create access token';
            $this->log_error($log_message);
            wc_add_notice($log_message, 'error');
        }
    }

    public function process_payment($amount, $mtn_payment_field)
    {
        if (!$this->access_token) {
            $this->get_access_token();
        }

        $payment_url = 'https://sandbox.momodeveloper.mtn.com/collection/v1_0/requesttopay';
        $headers = array(
            'Authorization' => 'Bearer ' . $this->access_token,
            'X-Reference-Id' => $this->x_reference_id,
            'X-Target-Environment: sandbox',
            'Content-Type: application/json',
            'Cache-Control: no-cache',
        );

        $body = array(
            'amount' => $amount,
            'currency' => 'EUR',
            'externalId' => 'ash101',
            'payer' => array(
                'partyIdType' => 'MSISDN',
                'partyId' => $mtn_payment_field,
            ),
            'payerMessage' => 'Validate your payment',
            'payeeNote' => 'Payment for product',
        );

        $response = wp_remote_post($payment_url, array(
            'method' => 'POST',
            'headers' => $headers,
            'body' => wp_json_encode($body),
        ));

        $this->last_request = array(
            'url' => $payment_url,
            'method' => 'POST',
            'headers' => $headers,
            'body' => $body
        );
        $this->last_response = array(
            'status' => wp_remote_retrieve_response_code($response),
            'body' => wp_remote_retrieve_body($response)
        );

        if (wp_remote_retrieve_response_code($response) === 202) {
            $body = wp_json_decode(wp_remote_retrieve_body($response), true);
            $transaction_reference = $body['transactionReference'];
            // Handle the successful payment initiation
            return $transaction_reference;
        } else {
            $log_message = 'Unable to complete payment transaction';
            $this->log_error($log_message);
            wc_add_notice($log_message, 'error');
        }
    }
}
