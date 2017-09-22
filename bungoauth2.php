<?php
// bungo auth 2
// an implementation of bungie's slightly weird flavor of oauth2

session_start();

$config = array(
  'apikey' => "",
  'client_id' => "",
);

require_once "config.php";

if (!isset($_GET['code'])) {
  // request authorization code
  header('Location: https://www.bungie.net/en/OAuth/Authorize/?client_id=' . $config['client_id'] . '&response_type=code');
}
else {
  // trade the auth code for a token
  $auth_code = htmlspecialchars($_GET['code']);

  $postfields = http_build_query(array('client_id' => $config['client_id'], 'grant_type' => 'authorization_code', 'code' => $auth_code));

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, 'https://www.bungie.net/Platform/App/OAuth/Token/');
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
  curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);

  $response = json_decode(curl_exec($ch), TRUE);
  curl_close($ch);
  
  // shove the token and expiration in the session
  $_SESSION['access_token'] = $response['access_token'];
  $_SESSION['access_token_expiration'] = time() + (int)$response['expires_in'];
  
  header('Location: index.php');
}