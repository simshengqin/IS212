<?php
require_once 'token.php';
require_once 'common.php';

$pathSegments = explode('/',$_SERVER['PHP_SELF']); # Current url
$numSegment = count($pathSegments);
$currentFolder = $pathSegments[$numSegment - 2]; # Current folder
$page = $pathSegments[$numSegment -1]; # Current page e.g. 'authenticate.php'

$result = [];
if ($currentFolder != 'json'){
	$_REQUEST['status'] = 'error';
}
elseif ($page != 'authenticate.php'){	// Check for token from json requests other than authenticate
	$token = '';
	if  (!isset($_REQUEST['token'])) {
		$result = [
			"status"=> "error",
			"message"=> "missing token"
		];
	}
	else {
		$token = $_REQUEST['token'];
		if (!verify_token($token)){
			$result = [
				"status"=> "error",
				"message"=> "invalid token"
			];
		}
	}
}

if (!empty($result)){
	header('Content-Type: application/json');
	$result = json_encode($result, JSON_PRETTY_PRINT | JSON_PRESERVE_ZERO_FRACTION);
	echo $result;
	exit();
}



?>