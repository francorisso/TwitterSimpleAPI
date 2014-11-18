<?php

include("../bootstrap.php");
include("../config.php");

header('Content-Type: application/json');

$parameters = array_merge( array( //default values
		"max_id" => 0,
		"count"	 => $config["tweets_count"],
	), array(
		"max_id" => $_GET["max_id"],
		"count"	 => intval( $_GET["count"] ),
	));

$db = new Database( $config["database_info"][ $config["database_type"] ] );
$settings = array(
	"consumer_key" 		=> $config["consumer_key"],
	"consumer_secret" 	=> $config["consumer_secret"],
	"db"				=> $db
);
$twitterAPI = new TwitterAPI( $settings );
$twitterAPI->cache_clean(); // this should be done in a maintenance task
$result = $twitterAPI->get_timeline($config["screen_name"], $parameters["count"], $parameters["max_id"]);

echo $result;