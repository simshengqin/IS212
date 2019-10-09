<?php
  require_once 'include/common.php';
  require_once 'include/protect.php';

  if (isset($_SESSION["user"]))
  {
    $student = $_SESSION["user"]; // retrive the student's information.
  }
  var_dump($student);
  $userid = $student->getUserid();
  var_dump($userid);
  $bidDAO = new BidDAO();


?>
