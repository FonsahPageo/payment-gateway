<?php

$api_key = include_once('create-api-key.php');
// $api_key = '7f8f1a8f95f14be6bfbfcb06992c5930';
// $x_reference_id = '1174754a-e8f6-4ef3-ad03-ec2bdfd7be02';

// $subscription_key = '85e74c865f50406abdd2b54a4800cf92';
$access_token = null;
$auth = $x_reference_id . ':' . $api_key;
$authorization = 'Basic ' . base64_encode($auth);

$create_token_url = 'https://sandbox.momodeveloper.mtn.com/collection/token/';

$headers = array(
    'Authorization: ' . $authorization,
    'Ocp-Apim-Subscription-Key: ' . $subscription_key,
    'Cache-Control: no-cache'
);

$curl = curl_init();

// Set cURL options
curl_setopt($curl, CURLOPT_URL, $create_token_url);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
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
    // echo 'Response: ' . $response;

    $response_data = json_decode($response, true);

    if (isset($response_data['access_token'])) {
        $access_token = $response_data['access_token'];
        echo 'Access Token: ' . $access_token;
    } else {
        echo 'Error: Unable to retrieve access token from the response.';
    }
}
// Close cURL
curl_close($curl);

return $access_token;
