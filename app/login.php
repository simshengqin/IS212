<?php

require_once 'include/common.php';
require_once 'include/token.php';

if(isset($_SESSION['user']))
  session_destroy();

$error = '';

if (isset($_GET['error']) ) {
    $error = $_GET['error'];
}
elseif (isset($_POST['userid']) && isset($_POST['password']) ) {
    $userid = $_POST['userid'];
    $password = $_POST['password'];

    if ($userid == "admin" 
          && password_verify($password,'$2y$10$Y64OGHH.HcW17UTrWuxon.nvT6v0viYnQZEurtVN3jurVdT1YgCDW')){ //password is 'SPMisgreat!'
            $_SESSION['token'] = generate_token($userid);
            $_SESSION['user'] = $userid; 
            header("Location: admin.php");
    } 
    else {
        $dao = new StudentDAO();
        $student = $dao->retrieveStudent($userid);
    
        if ( $student != null && $student->getPassword() == $password) {
            $_SESSION['user'] = $student;
            header("Location: landingPage.php");
            return;
    
        } else {
            $error = 'Incorrect User ID or Password!';
        }
    }



}
?>


<html>
<head>
  <title> Welcome </title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</head>
<body>
    <div class="container" >
    <div class="col-sm-12" style='padding-left: 0px; padding-right:0px'>

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-sm bg-light navbar-light">
      <!-- Brand/logo -->
      <a class="navbar-brand" style= "padding: 1.5rem 0 0 0;">
        <img src="images/merlion.png" alt="Logo" style="width:200px; height:60px">
      </a>
      <a class="navbar-brand">BIOS LOGIN</a>
  </nav>
<!-- End of Navigation Bar -->

    </div>
    <br>
  <div class="row">
    <div class="col-sm-6">
        <form method='POST' action="login.php">
            <div class="form-group">
                <label for="userid">User ID:</label>
                <input type="userid" placeholder='Enter User ID' class="form-control" name="userid" id="userid">
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" placeholder='Enter Password' class="form-control" name="password" id="password">
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
            <?php
                echo "<div class='form-group' style='color:red'> $error </div>";
            ?>
        </form>
     </div>
  </div>
</body>
