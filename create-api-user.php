<?php

$create_user_url = 'https://sandbox.momodeveloper.mtn.com/v1_0/apiuser';

$subscription_key = '85e74c865f50406abdd2b54a4800cf92';
$callback_url = 'https://webhook.site/f17a4674-fb42-431f-b2d0-743197bcbef2';
$x_reference_id = '0b4de2fd-bd66-49f6-a123-16726c8dc381';

$headers = array(
    'Ocp-Apim-Subscription-Key: ' . $subscription_key,
    'X-Reference-Id: ' . $x_reference_id,
    'Content-Type: application/json',
);
$body = array(
    'providerCallbackHost' => $callback_url
);

$curl = curl_init();

// Set cURL options
curl_setopt($curl, CURLOPT_URL, $create_user_url);
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
    echo 'Response Code: ' . $response_code . "\n";
    if ($response_code === 201) {
        echo 'User created succesfully: ' . $response . "\n";
    } else if ($response_code === 409) {
        echo 'User already exists: ' . $response . "\n";
    }
}
curl_close($curl);
