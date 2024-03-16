<?php
include_once 'create-api-user.php';

$api_key = null;
$create_key_url = "https://sandbox.momodeveloper.mtn.com/v1_0/apiuser/{$x_reference_id}/apikey";

$headers = array(
    'Ocp-Apim-Subscription-Key: ' . $subscription_key,
    'X-Reference-Id: ' . $x_reference_id,
    'Cache-control: no-cache'
);

$curl = curl_init();

// Set cURL options
curl_setopt($curl, CURLOPT_URL, $create_key_url);
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
    echo 'Response Code: ' . $response_code . "\n";
    echo 'Response: ' . $response;

    // Extract the API key from the response
    $response_data = json_decode($response, true);
    $api_key = $response_data['apiKey'];

    // echo 'API Key: ' . $api_key;
}
// Close cURL
curl_close($curl);

return $api_key;
