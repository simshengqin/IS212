<?php 
require_once '../include/bootstrap.php';
require_once '../include/protect_json.php';

if (!empty($result)){ # Print error message from protect_json.php
  header('Content-Type: application/json');
  echo json_encode($result, JSON_PRETTY_PRINT);
}
else {
  $result = doBootstrap();
  header('Content-Type: application/json');
  echo json_encode($result, JSON_PRETTY_PRINT);
}

?>


