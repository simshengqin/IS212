<?php
require_once 'token.php';
require_once 'common.php';

$pathSegments = explode('/',$_SERVER['PHP_SELF']); # Current url
$numSegment = count($pathSegments);
$currentFolder = $pathSegments[$numSegment - 2]; # Current folder
$page = $pathSegments[$numSegment -1]; # Current page

if ($currentFolder != 'json'){
	$_REQUEST['status'] = 'error';
}
else {

	$token = '';
	if  (isset($_REQUEST['token'])) {
		$token = $_REQUEST['token'];
	}

	# check if token is not valid
	# reply with appropriate JSON error message

	# add your code here 
	if (verify_token($token))
		$_REQUEST['status'] = 'success';
	else	
		$_REQUEST['status'] = 'error';
}




?>