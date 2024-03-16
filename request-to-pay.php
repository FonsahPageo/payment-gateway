<?php

include_once('create-access-token.php');

$payment_url = 'https://sandbox.momodeveloper.mtn.com/collection/v1_0/requesttopay';
$content_type = 'application/json';
$cache_control = 'no-cache';
$x_target_environment = 'sandbox';

$headers = array(
    'Authorization: Bearer ' . $access_token,
    'X-Reference-Id: ' . $x_reference_id,
    'X-Target-Environment:' . $x_target_environment,
    'Content-Type:'  . $content_type,
    'Cache-Control:' . $cache_control,
    'Ocp-Apim-Subscription-Key: ' . $subscription_key
);

$body = array(
    'amount' => '100',
    'currency' => 'EUR',
    'externalId' => 'ash101',
    'payer' => array(
        'partyIdType' => 'MSISDN',
        'partyId' => '675416098'
    ),
    'payerMessage' => 'Validate your transaction',
    'payeeNote' => 'Payment for product'
);

$curl = curl_init();

// Set cURL options
curl_setopt($curl, CURLOPT_URL, $payment_url);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($body));
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

// Send the request and get the response
$response = curl_exec($curl);

// Get the response code
$response_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

// Check for errors
if ($response === false) {
    $error = curl_error($curl);
    echo 'cURL Error: ' . $error;
} else {
    // Process the response
    echo "\n" . 'Response Code: ' . $response_code . "\n";
    if ($response_code === 202) {
        echo 'Successful payment: ' . $response;
    } else if ($response_code === 409) {
        echo 'User already exists: ' . $response . "\n";
    }
}
// Close cURL
curl_close($curl);
