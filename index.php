<?php 
session_start();
echo "<html><body><h1>Destiny 2 Clan Stats</h1>";

define("API_URI", 'https://www.bungie.net/Platform');

$config = array(
  'apikey' => "",
  'client_id' => "",
);

$hashes = array();

require_once "config.php";
require_once "hashes.php";

if (empty($_SESSION['access_token']) || time() > $_SESSION['access_token_expiration']){
  //either we don't have a token, or we need to reauth, either way, go auth.
  header('Location: bungoauth2.php');
}

// otherwise we're authenticated. let's do stuff.

echo "<p>API Token: <pre>" . $_SESSION['access_token'] . "</pre></p>";
echo "<p>API Token Expires: " . date(DATE_RFC1123,$_SESSION['access_token_expiration']) . "</p>";
$default_options = array(
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_SSL_VERIFYHOST => 2,
  CURLOPT_SSL_VERIFYPEER => false,
  CURLOPT_HTTPHEADER => array(
    'X-API-Key: '.$config['apikey'],
    'Authorization: Bearer '.$_SESSION['access_token'],
  ),
);

// Get user data
$ch = curl_init();
curl_setopt_array($ch, $default_options);
curl_setopt_array($ch, array(
  CURLOPT_URL => API_URI."/User/GetMembershipsForCurrentUser/",
));
$current_user_result = json_decode(curl_exec($ch), TRUE);
$current_user_info = curl_getinfo($ch);
curl_close($ch);
if ($current_user_result['ErrorCode'] == 1){
  $current_user = $current_user_result['Response'];
}
else {
  die("User Lookup Error");
}

$authed_username = $current_user['destinyMemberships'][0]['displayName'];
$destiny_membership_type = $current_user['destinyMemberships'][0]['membershipType'];
$destiny_membership_id = $current_user['destinyMemberships'][0]['membershipId'];

echo "<h2>Authenticated As</h2>";
echo "<p>Username: $authed_username</p>";
echo "<p>Destiny Membership ID: $destiny_membership_id</p>";
//var_dump($current_user);

// Get user data
$ch = curl_init();
curl_setopt_array($ch, $default_options);
curl_setopt_array($ch, array(
  CURLOPT_URL => API_URI."/Destiny2/$destiny_membership_type/Profile/$destiny_membership_id/?components=Profiles",
));
$profile_result = json_decode(curl_exec($ch), TRUE);
$profile_info = curl_getinfo($ch);
curl_close($ch);

if ($profile_result['ErrorCode'] == 1){
  $profile = $profile_result['Response'];
}
else {
  die("Profile Lookup Error");
}
//var_dump($profile);
echo "<p>You Last Played: ".$profile["profile"]["data"]["dateLastPlayed"]."</p>";

// Get linked clan data
$ch = curl_init();
curl_setopt_array($ch, $default_options);
curl_setopt_array($ch, array(
  CURLOPT_URL => API_URI."/GroupV2/User/$destiny_membership_type/$destiny_membership_id/0/1/",
));
$linked_clan_result = json_decode(curl_exec($ch), TRUE);
$linked_clan_info = curl_getinfo($ch);
curl_close($ch);

if ($linked_clan_result['ErrorCode'] == 1){
  $linked_clan = $linked_clan_result['Response']['results'][0]['group'];
}
else {
  die("Linked Clan Lookup Error");
}
//var_dump($linked_clan);
echo "<p>Primary Clan: ".$linked_clan['name']." [".$linked_clan['clanInfo']['clanCallsign']."]</p>";
echo "<p>Primary Clan ID: ".$linked_clan['groupId']."</p>";

// Get Clan Reward State
$ch = curl_init();
curl_setopt_array($ch, $default_options);
curl_setopt_array($ch, array(
  CURLOPT_URL => API_URI."/Destiny2/Clan/".$linked_clan['groupId']."/WeeklyRewardState/",
));
$clan_rewards_result = json_decode(curl_exec($ch), TRUE);
$clan_rewards_info = curl_getinfo($ch);
curl_close($ch);

if ($clan_rewards_result['ErrorCode'] == 1){
  $current_clan_rewards = $clan_rewards_result['Response']['rewards'][0]['entries'];
  $past_clan_rewards = $clan_rewards_result['Response']['rewards'][1]['entries'];
}
else {
  die("Clan Rewards Lookup Error");
}

echo "<h2>This Week's Welfare</h2>";
foreach($current_clan_rewards as $reward){
  if (!empty($hashes[$reward['rewardEntryHash']])){
    echo "<p>".$hashes[$reward['rewardEntryHash']].": ". ($reward['earned']?"Earned":"Not Yet")."</p>";
  }
  else {
    echo "<p>Welfare Engram #".$reward['rewardEntryHash'].": ". ($reward['earned']?"Earned":"Not Yet")."</p>";
  }
}

echo "<h2>Last Week's Welfare</h2>";
foreach($past_clan_rewards as $reward){
  if (!empty($hashes[$reward['rewardEntryHash']])){
    echo "<p>".$hashes[$reward['rewardEntryHash']].": ". ($reward['earned']?"Yes":"No")."</p>";
  }
  else {
    echo "<p>Welfare Engram #".$reward['rewardEntryHash'].": ". ($reward['earned']?"Yes":"No")."</p>";
  }
}

//var_dump($clan_rewards);

//var_dump($clan_rewards_result);
/*
// Get Clan Members
$ch = curl_init();
curl_setopt_array($ch, $default_options);
curl_setopt_array($ch, array(
  CURLOPT_URL => API_URI."/GroupV2/".$linked_clan['groupId']."/Members/?currentpage=1",
));
$clan_result = json_decode(curl_exec($ch), TRUE);
$clan_info = curl_getinfo($ch);
curl_close($ch);

if ($clan_result['ErrorCode'] == 1){
  $clan = $clan_result['Response']['results'];
}
else {
  die("Clan Lookup Error");
}
//var_dump($clan);
$members = array();

foreach ($clan as $member){
  $members[] = array(
    'memberType' => $member['memberType'],
    'displayName' => $member['destinyUserInfo']['displayName'],
    'membershipId' => $member['destinyUserInfo']['membershipId'],
  );
}

var_dump($members);
*/