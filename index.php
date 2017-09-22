<?php 
session_start();
echo "<html><body><h1>Destiny 2 Clan Stats</h1>";

define("API_URI", 'https://www.bungie.net/Platform');

$config = array(
  'apikey' => "",
  'client_id' => "",
);

require_once "config.php";

if (empty($_SESSION['access_token']) || time() > $_SESSION['access_token_expiration']){
  //either we don't have a token, or we need to reauth, either way, go auth.
  header('Location: bungoauth2.php');
}

// otherwise we're authenticated. let's do stuff.
