<?php
  require_once 'include/common.php';
  require_once 'include/protect.php';
  
  if (isset($_SESSION["user"]))
  {
    $student = $_SESSION["user"]; // retrive the student's information.
    if (isset($_SESSION["initialize"]))
    {
        $initialize = false;
    }
    else
    {
        $initialize = true;
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

<script type="text/javascript">
    $(document).ready(function()
    {
        $("#initialize-User-Info").modal('show');
    });
</script>

<body>

  <?php
  
    $stuName = $student->getName();
    $stuID = $student->getUserid();
    $stuBids = [];
    $stuSections = [];
    $stuEdollar = $student->getEdollar();

    $sectionStudentDAO = new SectionStudentDAO();
    $sectionInfo = $sectionStudentDAO->retrieveByID($stuID);

            
            

    $bidDAO = new BidDAO();
    $bidStatusDAO = new BidStatusDAO();
    $bidRoundStatus = $bidStatusDAO->getBidStatus();
    $stuBids = $bidDAO->retrieveStudentBids($stuID);
    $allBids = $bidDAO->retrieveAll();
    // calculate remaining amount
    if ($bidRoundStatus->getStatus() == 'open'){
      foreach($stuBids as $value)
      {
        
        $stuEdollar -= $value->getAmount();
      }
    }
   ?>

   <?php
    if($initialize == true)
    {
      echo
      "
        <div class='modal fade' id='initialize-User-Info'>
          <div class='modal-dialog modal-dialog-centered'>
            <div class='modal-content'>

              <div class='modal-header'>
                <h4 class='modal-title'> Welcome $stuName! </h4>
                <button type='button' class='close' data-dismiss='modal'> &times; </button>
              </div>

              <div class='modal-body'>
                You currently have e$$stuEdollar in your account.
              </div>

              <div class='modal-footer'>
                <button type='button' class='btn btn-danger' data-dismiss='modal'> Close </button>
              </div>

            </div>
          </div>
        </div>
      ";
    }
    $initialize = false;
    $_SESSION['initialize'] = $initialize;

   ?>

<div class="container">

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-sm bg-light navbar-light">
      <!-- Brand/logo -->
      <a class="navbar-brand" style= "padding: 1.5rem 0 0 0;">
        <img src="images/merlion.png" alt="Logo" style="width:200px; height:60px">
      </a>
      <a class="navbar-brand">BIOS</a>
      <!-- Links -->
      <ul class="navbar-nav mr-auto"> <!-- left align-->
        <li class="nav-item">
          <a class="nav-link active" href="landingPage.php"> HOME </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="addBidPage.php"> ADD BID(s)</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="dropBid.php"> DROP BID(s)</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="dropSection.php"> DROP SECTION(s)</a>
        </li>
      </ul>
      <ul class="navbar-nav"> <!-- right align-->
        <li class="nav-item">
          <a class="nav-link" href="login.php"> LOGOUT </a>
        </li>
      </ul>
  </nav>
<!-- End of Navigation Bar -->

 <br/>


<?php
  echo "<h3> Welcome $stuName</h3>";
  echo "<h3> Current e$: $stuEdollar </h3>";
 ?> 


 <div class = "row">
   <div class="col-sm-12" style='margin-top: 7.5vh'>
     <table class="table table-striped">
       <h3> <?= $bidRoundStatus->getStatus()=='closed' ? "Round {$bidRoundStatus->getRound()} Results" : 'Bid(s)'?> </h3>
       <thead>
         <tr>
           <th>Course Code</th>
           <th>Section Code</th>
           <th>Amount Bid</th>
           <th> Status </th>
           <?= $bidRoundStatus->getRound() == 2 ? "<th> Min Bid </th>": "" ?>
         </tr>  
         </thead>
         <tbody>
           <?php
             if(count($stuBids) == 0)
             {
                if ($bidRoundStatus->getRound() == 2)
                echo "<tr> <td colspan='5'> <h4 style='text-align: center;'> You currently have no bids </h4> </td> </tr>";
                else
                echo "<tr> <td colspan='4'> <h4 style='text-align: center;'> You currently have no bids </h4> </td> </tr>";
             }
            echo "<tbody>";
            $sectionDAO = new SectionDAO();
            $bidDAO = new BidDAO();

            
            foreach($allBids as $value)
            {
              $userid = $value->getUserid();
              $course = $value->getCode();
              $section = $value->getSection();
              $amount = $value->getAmount();
              $status = "pending";
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
                    //If there are more than one course with the same min bid amount, reject all of them
                    if ($nthBid->getAmount() == $nthPlusOneBid->getAmount()) {
                        $multipleSimilarMinBids = True;
                    }
                }
                $oldMinBid = $sectionDAO->retrieveMinBid($course, $section);
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
              //Only print out the bids for the userid that is currently logged in
              if ($userid == $stuID) {
                echo "<tr>";
                  echo"<td>" . $course . "</td>";
                  echo"<td>". $section . "</td>";
                  echo"<td>" .$amount. "</td>";
                  echo (($bidRoundStatus->getRound() == 1 && $bidRoundStatus->getStatus() == 'open') ? "<td>Pending</td>": "<td>" . ucfirst($status) . "</td>");
                  echo ($bidRoundStatus->getRound() == 2 ? "<td>" . $minBid . "</td>": "");
                echo "</tr>";
              }
            }
            echo "</tbody>";
           ?>
         </tbody>
     </table>
   </div>
  </div>




  <div class = "row">
    <div class="col-sm-12" style='margin-top: 7.5vh'>
      <table class="table table-striped">
        <h3> Section(s) </h3>
        <thead>
          <tr>
            <th>Course Code</th>
            <th>Section Code</th>
            <th>Amount Bid</th>
          </tr>
        </thead>
          <tbody>
            <?php 
            if(count($sectionInfo) == 0)
            {
              echo "<tr> <td colspan='4'> <h4 style='text-align: center;'> You are currently not enrolled in any course </h4> </td> </tr>";
            }
            else
            {
              
              echo "<tbody>";
              foreach($sectionInfo as $sectionRow){

                $course = $sectionRow->getCourse();
                $section = $sectionRow->getSection();
                $amount = $sectionRow->getAmount();
              
              echo "
              <tr>
                <td> $course </td>
                <td> $section </td>
                <td> $amount </td>
              </tr>";
              echo "</tbody>";
              
            }
          }

            ?>
          </tbody>
      </table>
    </div>
   </div>


</div>


</body>
</html>
