<?php
require_once 'include/common.php';
require_once 'include/protect.php';



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

<div class="container">


  <nav class="navbar navbar-expand-sm bg-light navbar-light">
    <!-- Brand/logo -->
    <a class="navbar-brand" style= "padding: 1.5rem 0 0 0;">
      <img src="images/merlion.png" alt="Logo" style="width:200px; height:60px">
    </a>
    <a class="navbar-brand">BIOS</a>
    <!-- Links -->
    <ul class="navbar-nav mr-auto">
      <li class="nav-item">
        <a class="nav-link active" href="admin.php"> HOME </a>
      </li>
    </ul>
    <ul class="navbar-nav"> <!-- right align-->
      <li class="nav-item">
        <a class="nav-link" href="login.php"> LOGOUT </a>
      </li>
    </ul>
  </nav>

<br>

<h3> Welcome Admin! </h3>

<br>
<?php 
  $bidStatusDAO = new BidStatusDAO();
  $bidStatus = $bidStatusDAO->getBidStatus();
  if (isset($_POST['round']) && (isset($_POST['status']))){
    if (!($bidStatus->getRound() == '2' && $bidStatus->getStatus() == 'cleared'))
      $bidStatusDAO->updateBidStatus($_POST['round'], $_POST['status']);
  }
  
  
  
  // if the round 1 is not started yet
  if (($bidStatus->getRound() == '0' && $bidStatus->getStatus() == 'closed') || ($bidStatus->getRound() == '2' && ($bidStatus->getStatus() == 'closed' || $bidStatus->getStatus() == 'cleared'))){ 
    echo '
      <form id="bootstrap-form" action="bootstrap-process.php" method="post" enctype="multipart/form-data">
        Bootstrap file:  
        &nbsp;<input id="bootstrap-file" type="file" name="bootstrap-file"><br>
        <input type="submit" name="submit" value="Import">
      </form>
      ';
    //Round 2 clearing takes place here. Only takes place once, will convert status from closed to cleared
    if ($bidStatus->getRound() == '2' && $bidStatus->getStatus() == 'closed') {
      $bidStatusDAO->updateBidStatus('2', 'cleared');
      
    }
  }
  // After round 1 starts
  else {
    $status = ucfirst($bidStatus->getStatus());     // capitalize the first letter of status 
    $round = $bidStatus->getRound();
    echo "<h3>Current Round: {$bidStatus->getRound()} <br>
          Status: $status</h3><br>
        <form method='POST' action='admin.php'>";
          
    if ($bidStatus->getStatus() == 'open'){
        echo "<input type='hidden' id='round' name='round' value='$round'>
              <br>
              <button type='submit' name='status' value='closed'>Close round </button>
              </form>";
    }
    elseif ($bidStatus->getStatus() == 'closed'){
      $round++;
        echo "<input type='hidden' id='round' name='round' value='$round'>
              <br>
              <button type='submit' name='status' value='open'>Open round </button>
              </form>";
    }
  }
   

  if (isset($_SESSION['bootstrap_error']['error']) && sizeof($_SESSION['bootstrap_error']['error']) != 0 
              && ($bidStatus->getRound() == '1' && $bidStatus->getStatus() == 'open')) {    // To prevent it from constantly appearing
    echo
      "<div class = 'row'>
        <div class='col-sm-12' style='margin-top: 7.5vh'>
          <table class='table table-striped'>
            <h3> Error(s) in Bootstrap </h3>
            <thead>
              <tr>
                <th>File</th>
                <th>Line</th>
                <th>Message</th>
              </tr>
              </thead>
              <tbody>";
    foreach ($_SESSION['bootstrap_error']['error'] as $error){
      echo '<tr><td>'.$error['file'].'</td>
                <td>'.$error['line'].'</td>
                <td>'.implode(', ', $error['message']).'</td></tr>';
    }
  
  }
?>

</div>
</html>
