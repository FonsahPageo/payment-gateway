<?php

// include_once('create-access-token.php');

$payment_url = 'https://sandbox.momodeveloper.mtn.com/collection/v1_0/requesttopay';

$subscription_key = '85e74c865f50406abdd2b54a4800cf92';
$api_key = '7f8f1a8f95f14be6bfbfcb06992c5930';
$callback_url = 'https://webhook.site/f17a4674-fb42-431f-b2d0-743197bcbef2';
$access_token = null;
$x_reference_id = 'ecdf7a26-81a1-47ae-bdac-04e23a8b7f9e';
$authorization;
$access_token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSMjU2In0.eyJjbGllbnRJZCI6IjExNzQ3NTRhLWU4ZjYtNGVmMy1hZDAzLWVjMmJkZmQ3YmUwMiIsImV4cGlyZXMiOiIyMDI0LTAzLTA3VDIwOjA5OjAxLjY3OSIsInNlc3Npb25JZCI6ImVjMGU0OGZjLTdmNzItNDMyMS05ZGY4LWJmNWYwYjZkZWMxZiJ9.ewptiHJa1VGbcgfcrszt45yxgwEqCbI9oZkYyye9TJa-BFESUZ6tZyXTan4CNvGvDlMp5dYx7GgURXK-utBItrT8999Me2mNFzYYIRBIzM-__7WauutwngluZfzvfLvTTLOt-nouzIB1ZRHk0YeJ3k6Y0XkMniQFJHad8o2bAyDJJ4_uWWdPmtWdXN4q-KeS13e9KIxlo1RbhtizMajgmU_hK2U60aab_Bb7bTUJa0V_S718eG-AEWDUEQwhDrJSqFPNk-uWAzMCJRDcrGR3kNvBicwUoh_RNqcEo5FVd2n0PmgdOEZqea8QAO_DEB4qA8PF8aDjPZzYvJiKSW7P9w';
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
