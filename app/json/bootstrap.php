<?php 
require_once '../include/bootstrap.php';
require_once '../include/protect_json.php';


$result = doBootstrap();
header('Content-Type: application/json');
echo json_encode($result, JSON_PRETTY_PRINT);


?>


