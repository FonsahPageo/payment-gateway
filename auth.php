<?php

$API_User = '4c7935df-f55d-4855-a244-a8f8b4c68a38';
$API_Key = '42b22a360f54423993ef109bc7bcfb62';

$auth = $API_User.':'.$API_Key;
$credentials = base64_encode($auth);
// echo $auth;
echo "\n\n".$credentials;

            // 'api_user' => array(
            //     'title' => 'API User',
            //     'type' => 'text',
            //     'description' => 'Unique character string for identifying the API user',
            //     'default' => '',
            //     'desc_tip'    => true
            // ),
            // 'api_key' => array(
            //     'title' => 'API Key',
            //     'type' => 'text',
            //     'description' => 'API key for authenticating the user when making API requests',
            //     'default' => '',
            //     'desc_tip'    => true
            // ),
            // 'subscription_key' => array(
            //     'title' => 'Ocp-Apim-Subscription-Key',
            //     'type' => 'text',
            //     'description' => 'Ocp-Apim-Subscription-Key for Collection product',
            //     'default' => '',
            //     'desc_tip'    => true
            // ),
            // 'target_environment' => array(
            //     'title' => 'Target Environment',
            //     'type' => 'text',
            //     'description' => 'Environment for making API requests',
            //     'default' => 'sandbox',
            //     'desc_tip'    => true
            // )

// php -S localhost:7000 -t . auth.php to serve the file on a php server