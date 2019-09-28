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

    $dao = new StudentDAO();
    $student = $dao->retrieveStudent($userid);

    if ( $student != null && $student->authenticate($password) ) {
        $_SESSION['token'] = generate_token($userid);
        $_SESSION['user'] = $student;
        header("Location: landingPage.php");
        return;

    } else {
        $error = 'Incorrect User ID or Password!';
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
        <nav class="navbar navbar-expand-sm bg-light navbar-light">
        <!-- Brand/logo -->
        <a class="navbar-brand" href="#">BIOS</a>
        <!-- Links
        <ul class="navbar-nav">
            <li class="nav-item">
            <a class="nav-link" href="landingPage.php">HOME</a>
            </li>
            <li class="nav-item">
            <a class="nav-link" href="#">SECTIONS</a>
            </li>
        </ul> -->
        </nav>
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
