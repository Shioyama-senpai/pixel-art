<?php
	ini_set('display_errors', '1');
	ini_set('display_startup_errors', '1');
	error_reporting(E_ALL);

	$isAction = true;

	require_once "controllers/PostManager.php";

	if (!isset($_POST["vote"])) {
		die(json_encode(array(
			"error" => "Please vote."
		)));
	}

	if (!isset($_POST["postid"])) {
		die(json_encode(array(
			"error" => "Please specify the post ID."
		)));
	}

	$vote = intval($_POST["vote"]);
	$postId = intval($_POST["postid"]);

	$response = PostManager::getInstance()->vote($postId, $vote > 0 ? 1 : -1);

	if (is_string($response)) {
		die(json_encode(array(
			"error" => $response
		)));
	}

	die(json_encode(true));
?>