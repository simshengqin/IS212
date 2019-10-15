<?php
require_once 'include/common.php';
// require_once 'include/protect.php';



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
    <a class="navbar-brand"><h4>BIOS</h4></a>
    <!-- Links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" href="landingPage.php"> HOME </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="addBidPage.php"> ADD BID(s)</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="dropBid.php"> DROP BID(s)</a>
      </li>
    </ul>
  </nav>

<br>

<h3> Welcome Admin! </h3>

<br>
<?php 
  $bidStatusDAO = new BidStatusDAO();
  if (isset($_POST['round']) && (isset($_POST['status']))){
    $bidStatusDAO->updateBidStatus($_POST['round'], $_POST['status']);
  }
  
  $bidStatus = $bidStatusDAO->getBidStatus();
  
  // if the round 1 is not started yet
  if (($bidStatus->getRound() == '0' && $bidStatus->getStatus() == 'closed') || ($bidStatus->getRound() == '2' && $bidStatus->getStatus() == 'closed')){ 
    echo '
      <form id="bootstrap-form" action="bootstrap-process.php" method="post" enctype="multipart/form-data">
        Bootstrap file:  
        &nbsp;<input id="bootstrap-file" type="file" name="bootstrap-file"><br>
        <input type="submit" name="submit" value="Import">
      </form>
      ';
  }
  // After round 1 starts
  else {
    $status = ucfirst($bidStatus->getStatus());
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
   
?>

</div>
</html>
