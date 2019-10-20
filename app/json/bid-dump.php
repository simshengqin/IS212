<?php 
require_once '../include/common.php';

$bidDAO = new BidDAO();
$request = $_GET['r'];
$data = json_decode($request, true);



?>