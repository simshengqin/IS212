<?php
require_once 'include/common.php';
require_once 'include/protect.php';
require_once 'clearBidOne-process.php';



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
    # upon clearing round 1
    if (!($bidStatus->getRound() == '2' && $bidStatus->getStatus() == 'cleared'))
      $bidStatusDAO->updateBidStatus($_POST['round'], $_POST['status']);
    if ($bidStatus->getRound() == '1' && $bidStatus->getStatus() == 'closed')
    {
      doRoundOne();
    }

  }
  $bidStatus = $bidStatusDAO->getBidStatus();
  
  
  // if the round 1 is not started yet
  if (($bidStatus->getRound() == '0' && $bidStatus->getStatus() == 'closed') || ($bidStatus->getRound() == '2' && ($bidStatus->getStatus() == 'closed' || $bidStatus->getStatus() == 'cleared'))){ 
    echo '
      <form id="bootstrap-form" action="bootstrap-process.php" method="post" enctype="multipart/form-data">
        Bootstrap file:  
        &nbsp;<input id="bootstrap-file" type="file" name="bootstrap-file"><br>
        <input type="submit" name="submit" value="Import">
      </form>
      ';
    //////Round 2 clearing takes place here. Only takes place once, will convert status from closed to cleared
    if ($bidStatus->getRound() == '2' && $bidStatus->getStatus() == 'closed') {
        $bidStatusDAO->updateBidStatus('2', 'cleared');
        $bidDAO = new BidDAO();
        $allBids = $bidDAO->retrieveAll();
        $sectionDAO = new SectionDAO();
        $bidDAO = new BidDAO();
        $sectionStudentDAO = new SectionStudentDAO();
        $studentDAO = new StudentDAO();
        foreach($allBids as $value)
        {
          $userid = $value->getUserid();
          $course = $value->getCode();
          $section = $value->getSection();
          $amount = $value->getAmount();
          $status = "Pending";
          //Round 2 clearing logic. Real-time check of the min bid value. If the bid is unsuccessful, reflect it.
          //Get the total number of seats available for this specific course-section pair
          $sectionObj = $sectionDAO->retrieveSectionByCourse($course,$section);
          $seatsAvailable = $sectionObj->getSize();
          //Get the total number of bids for the same specific course-section pair, which is also sorted in descending order
          $biddedCourses = $bidDAO->retrieveStudentBidsByCourseAndSectionOrderDesc($course,$section);

          $bidCount = sizeof($biddedCourses);
      
          // N is the seatsavailable
          if ($seatsAvailable > $bidCount) {
            $minBid = 10;   
            $status = "success";             
          }
          else {
            //Min bid amount is equal to the Nth bid amount + 1
            $nthBid = $biddedCourses[$seatsAvailable - 1];
            $multipleSimilarMinBids = False;
            if ($seatsAvailable < $bidCount) {
                $nthPlusOneBid = $biddedCourses[$seatsAvailable];
                //If there are more then one course with the same min bid amount, reject all of them
                if ($nthBid->getAmount() == $nthPlusOneBid->getAmount()) {
                    $multipleSimilarMinBids = True;
                }
            }
            $oldMinBid = $sectionDAO -> retrieveMinBid($course, $section);
            if ( ($nthBid->getAmount() + 1) > $oldMinBid) {
              $minBid = $nthBid->getAmount() + 1;
              $sectionDAO -> updateMinBid($course,$section,$minBid);
            }
            else {
              $minBid = $oldMinBid;
            }
            //2 scenarios for the bid to be considered unsuccessful
            //if bid amount is equal to minBid and it is not the nthBid, it means there are multiple courses with the same minbid. No space left=>Reject
            //if bid amount is smaller than minBid => Automatically rejected
            if ( ($amount == ($minBid - 1) && $multipleSimilarMinBids == True) || $amount < ($minBid - 1)){
                $status = "fail";                  
            }
            else {
                $status = "success";
                
            }
            
          }
          $bidDAO->updateStatus($userid,$course,$section,$status);
          //New
          if ($status == "success") {
            $sectionStudentDAO->add($userid, $course, $section, $amount);
            $student = $studentDAO->retrieveStudent($userid);
            $edollar = $student->getEdollar();
            $studentDAO->updateEDollar($userid,$edollar - $amount);          
          }
        }
    }
  }
  // After round 1 starts
  else {
    $status = ucfirst($bidStatus->getStatus());     // capitalize the first letter of status 
    $round = $bidStatus->getRound();

    var_dump($status);
    var_dump($round);
    //var_dump($_POST['round']);
    //var_dump($_POST['status']);

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
