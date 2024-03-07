<?php

$API_User = '4c7935df-f55d-4855-a244-a8f8b4c68a38';
$API_Key = '42b22a360f54423993ef109bc7bcfb62';

$auth = $API_User.':'.$API_Key;
$credentials = base64_encode($auth);
// echo $auth;
echo "\n\n".$credentials;

// php -S localhost:7000 -t . auth.php to serve the file on a php server