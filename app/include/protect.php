<?php
require_once 'token.php';
require_once 'common.php';

$token = '';
if (isset($_REQUEST['token'])) {
	$token = $_REQUEST['token'];
}
elseif (isset($_SESSION['token'])) {
	$token = $_SESSION['token'];
}

# check if token is not valid
# send user back to the login page with the appropirate message

# add your code here
if (!verify_token($token) || !isset($_SESSION['user']))

	header('location: login.php?error=Please login');



# this bit below might be useful for the last part of the lab and your project
# it will help to check for more conditions such as

# if the user is not an admin and trying to access admin pages

# if the user is trying to access json services and is not doing it properly

# $pathSegments = explode('/',$_SERVER['PHP_SELF']); # Current url
# $numSegment = count($pathSegments);
# $currentFolder = $pathSegments[$numSegment - 2]; # Current folder
# $page = $pathSegments[$numSegment -1]; # Current page

# you can do things like If ($page == "bootstrap-view.php) {   or
# if ($currentfolder == "json") {

?>
