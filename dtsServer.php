<?php

	function main(){
		//Get terms from URL
		$terms = $_REQUEST["terms"];

		if($terms==""){
			echo "";
			return;
		}

		$searchSettings = array("q"=>$terms, "count"=>100000, "lang"=>"en", "result_type"=>"recent");
		$url = "https://api.twitter.com/1.1/search/tweets.json";
		$headers = array("Authorization"=>buildAuthHeader($url, $searchSettings));
		$tweets = request($url, $searchSettings, $headers);

		$connection = mysqli_connect("localhost", "root");
		init_db($connection);

		foreach($tweets["statuses"] as $status){
			$text = str_replace("'","''", $status["text"]);
			$text = str_replace("\n"," ", $text);
			$count = $status["retweet_count"];
			$sql = "INSERT INTO twitter_db.tweets (text, retweet_count) VALUES('".$text."', $count);";
			mysqli_query($connection, $sql);
		}

		$sql = "SELECT DISTINCT text, retweet_count FROM twitter_db.tweets ORDER BY retweet_count DESC LIMIT 5";
		$results = mysqli_query($connection, $sql);
		$result = "";

		while($temp = $results->fetch_array(MYSQLI_ASSOC)){
			$result .= $temp["text"]." (retweet count =".$temp["retweet_count"].")\n";
		}

		term_db($connection);
		echo $result;
	}

	function init_db($connection){
		if(mysqli_connect_errno()){// Check connection
			echo "Failed to connect to MySQL: " . mysqli_connect_error();
		}

		$sql = "CREATE DATABASE twitter_db";

		if(!mysqli_query($connection, $sql)){
			//echo "Error creating database: " . mysqli_error($connection);
		}

		$sql = "CREATE TABLE twitter_db.tweets(ID INT NOT NULL AUTO_INCREMENT, PRIMARY KEY(ID), text CHAR(140), retweet_count INT)";

		if(!mysqli_query($connection, $sql)){
			//echo "Error creating table: " . mysqli_error($connection);
		}
	}

	function term_db($connection){
		$sql = "DELETE FROM twitter_db.tweets";
		mysqli_query($connection, $sql);
		mysqli_close($connection);
	}

	function request($url, $params, $headers){
		//Send search request to twitter API
		$httpContext = array("method" => "GET");
		$url .= "?".http_build_query($params);
		$contextHeader = array();

		foreach($headers as $key => $value) {
		    $contextHeader[] = "$key: $value";
		}

		$httpContext["header"] = implode("\r\n", $contextHeader)."\r\n";
		$context = stream_context_create(array("http" => $httpContext));
		return json_decode(file_get_contents($url, false, $context), true);
	}

	function buildAuthHeader($baseUrl, $reqParams){
		$oauth_token =  "1633379330-qdEjRKgYpr7j5OgVSA9CFlFo1hqv6NhpVb2FBAf";
		$oauth_token_secret = "Vf1ur8WTNXqvYrKjVVEcQXWz3veqSEXijOsZfo7GSZS06";
		$consumer_key = "7RQb5Q7qydNzkxpFRbBjpRkur";
		$consumer_secret = "fDernaZ9A9iQwumHKW48TFTRNEZsNDhjwZQIaQLd5XvGJ6QE3T";

		$oauthParams = array(
		    "oauth_consumer_key" => $consumer_key,
		    "oauth_nonce" => uniqid(),
		    "oauth_signature_method" => "HMAC-SHA1",
		    "oauth_timestamp" => time(),
		    "oauth_token" => $oauth_token,
		    "oauth_version" => "1.0",
		);

		$encParams = array_merge($oauthParams, $reqParams);
		ksort($encParams);
		$paramSb = array();

		foreach($encParams as $key => $value) {
		    $paramSb[] = rawurlencode($key)."=".rawurlencode($value);
		}

		$paramStr = implode("&", $paramSb);
		$sigBaseStr = "GET"."&".rawurlencode($baseUrl)."&".rawurlencode($paramStr);
		$signingKey = rawurlencode($consumer_secret)."&".rawurlencode($oauth_token_secret);
		$oauthParams["oauth_signature"] = base64_encode(hash_hmac("sha1", $sigBaseStr, $signingKey, true));
		$authSb = array();

		foreach($oauthParams as $key => $value) {
		    $authSb[] = rawurlencode($key)."=\"".rawurlencode($value)."\"";
		}

		return "OAuth ".implode(", ", $authSb);
	}

	main();
?>
