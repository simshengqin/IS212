<?php
require_once 'include/common.php';
require_once 'include/protect.php';
require_once 'clearBidOne-process.php';
require_once 'clearBidTwo-process.php';

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
  $bidDAO = new BidDAO();
  $bidStatus = $bidStatusDAO->getBidStatus();
  if (isset($_POST['round']) && (isset($_POST['status']))){
    #update bids according to the $_POST response 
    if (!($bidStatus->getRound() == '2' && $bidStatus->getStatus() == 'cleared'))
      $bidStatusDAO->updateBidStatus($_POST['round'], $_POST['status']);

    ## upon clearing round 1
    // $bidStatus = $bidStatusDAO->getBidStatus();
    if ($bidStatus->getRound() == '1' && $bidStatus->getStatus() == 'open')
    {
      doRoundOne();
      $bidStatusDAO->updateBidStatus('1', 'closed');

      
    }
    if ($_POST['round'] == '2' && $_POST['status'] == 'open')
      $bidDAO->removeAll();
  }
  $bidStatus = $bidStatusDAO->getBidStatus();
  
  // if the round 1 is not started yet
  if (($bidStatus->getRound() == '0' && $bidStatus->getStatus() == 'closed') ||  ($bidStatus->getRound() == '2' && $bidStatus->getStatus() == 'cleared')){ 
    echo '  
      <h3>Bootstrap File: </h3><br>
      <form id="bootstrap-form" action="bootstrap-process.php" method="post" enctype="multipart/form-data">
        <input id="bootstrap-file" type="file" name="bootstrap-file">
      <br><br>
        <input type="submit" name="submit" value="Import">
      </form>
      ';
    }

    //////Round 2 clearing takes place here. Only takes place once, will convert status from closed to cleared
  elseif ($bidStatus->getRound() == '2' && $bidStatus->getStatus() == 'closed') {
    doRoundTwo(true);
    echo "<div class='container'>
    <div class='row'>";
      
      //Show that it is cleared 
    echo "
    <div class='col-md-6'>
    <h3>Current Round: 2 <br>
      Status: Closed </h3><br>
    </div>";
    
    // Put bootstrap option on the right
    echo '<div class="col-md-6">
    <h3>Bootstrap File: </h3><br>
      <form id="bootstrap-form" action="bootstrap-process.php" method="post" enctype="multipart/form-data">
      <div>
        <input id="bootstrap-file" type="file" name="bootstrap-file">
      <br><br>
        <input type="submit" name="submit" value="Import">
      </div>
      </form></div></div></div></div>
      ';

        $bidDAO->removeAll();
    }
  
  // After round 1 starts
  else {
    $status = ucfirst($bidStatus->getStatus());     // capitalize the first letter of status 
    $round = $bidStatus->getRound();
    echo "<div class='container'>
            <div class='row'>
              <div class='col-md'>";

    echo "<h3>Current Round: {$bidStatus->getRound()} <br>
          Status: $status</h3><br>
        <form method='POST' action='admin.php'>";
          
    if ($bidStatus->getStatus() == 'open'){
        echo "<input type='hidden' id='round' name='round' value='$round'>
              <br>
              <button type='submit' name='status' value='closed'>Close round </button>
              </form></div>";
    }
    elseif ($bidStatus->getStatus() == 'closed' || $bidStatus->getStatus() == 'cleared'){
      $round++;
        echo "<input type='hidden' id='round' name='round' value='$round'>
              <br>
              <button type='submit' name='status' value='open'>Open round </button>
              </form></div>";


    } 
    // Put bootstrap option on the right
    echo '<div class="col-md">
    <h3>Bootstrap File: </h3><br>
      <form id="bootstrap-form" action="bootstrap-process.php" method="post" enctype="multipart/form-data">
      <div>
        <input id="bootstrap-file" type="file" name="bootstrap-file">
      <br><br>
        <input type="submit" name="submit" value="Import">
      </div>
      </form></div></div></div>
      ';
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
    #Close table
    echo "
    </tbody>
    </table>
    </div>
    </div>";  
  }

  #Extract information from num-record-loaded only after bootstrap
  if($bidStatus->getRound() == '1' && $bidStatus->getStatus() == 'open' && isset($_SESSION['bootstrap_error'])){
    $tableSuccess = $_SESSION['bootstrap_error']['num-record-loaded'];
  $status = $_SESSION['bootstrap_error']['status'];
  // var_dump($tableSuccess);
  $tableSuccessExtracted = [];
  // var_dump($tableSuccess[0]);

  for ($index=0;$index<sizeof($tableSuccess);$index++){
    foreach ($tableSuccess[$index] as $key=>$value){
      $tableSuccessExtracted[$key] = $value;
    }
  }

  // var_dump($tableSuccessExtracted);

  #Show success information on a table

  #Header 
  echo "
  <div class = 'row'>
  <div class='col-sm-12' style='margin-top: 7.5vh'>
    <table class='table table-striped'>
      <h3>" .ucfirst($status)."! <br> Line(s) Processed </h3>
      <thead>
        <tr>
          <th>File</th>
          <th>Line</th>
        </tr>
        </thead>
        <tbody>";
  
  foreach ($tableSuccessExtracted as $key => $value){
    echo "
    <tr> 
    <td> $key </td>
    <td> $value </td>
    </tr>
    ";
  }
  
  #Close table
  echo "
  </tbody>
  </table>
  </div>
  </div>"; 
  }
  

?>

</div>
</html>