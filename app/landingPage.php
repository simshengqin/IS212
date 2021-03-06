<?php
  require_once 'include/common.php';
  require_once 'include/protect.php';
  require_once 'clearBidTwo-process.php';
  
  if (isset($_SESSION["user"]))
  {
    $student = $_SESSION["user"]; // retrive the student's information.
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

  <?php

    $studentDAO = new StudentDAO();
    $stuName = $student->getName();
    $stuID = $student->getUserid();
    $stuBids = [];
    $stuSections = [];
    $studentInfo = $studentDAO->retrieveStudent($stuID);
    $stuEdollar = $studentInfo->getEdollar();

    $sectionStudentDAO = new SectionStudentDAO();
    $sectionInfo = $sectionStudentDAO->retrieveByID($stuID);

    $bidDAO = new BidDAO();
    $bidStatusDAO = new BidStatusDAO();
    $bidRoundStatus = $bidStatusDAO->getBidStatus();
    $stuBids = $bidDAO->retrieveStudentBids($stuID);
    $allBids = $bidDAO->retrieveAll();
    $round = $bidRoundStatus->getRound();
    $status = $bidRoundStatus->getStatus();
    if ($round == '2')
      doRoundTwo();
    // calculate remaining amount 

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
  echo "<h3> Round: $round </h3>";
  echo "<h3> Status: " . ucfirst($status) ."</h3>";
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
            
            
              
            //   $status = "pending";
            //   //Round 2 clearing logic. Real-time check of the min bid value. If the bid is unsuccessful, reflect it.
            //   //Get the total number of seats available for this specific course-section pair
            //   $sectionObj = $sectionDAO->retrieveSectionByCourse($course,$section);
            //   $seatsAvailable = $sectionObj->getSize();
            //   //Get the total number of bids for the same specific course-section pair, which is also sorted in descending order
            //   $biddedCourses = $bidDAO->retrieveStudentBidsByCourseAndSectionOrderDesc($course,$section);

            //   $bidCount = sizeof($biddedCourses);
            //   // N is the seatsavailable
            //   if ($seatsAvailable > $bidCount) {
            //     $minBid = 10;   
            //     $status = "success";
            //   }
            //   else {
            //     //Min bid amount is equal to the Nth bid amount + 1
            //     $nthBid = $biddedCourses[$seatsAvailable - 1];
            //     $multipleSimilarMinBids = False;
            //     if ($seatsAvailable < $bidCount) {
            //         $nthPlusOneBid = $biddedCourses[$seatsAvailable];
            //         //If there are more than one course with the same min bid amount, reject all of them
            //         if ($nthBid->getAmount() == $nthPlusOneBid->getAmount()) {
            //             $multipleSimilarMinBids = True;
            //         }
            //     }
            //     $oldMinBid = $sectionDAO->retrieveMinBid($course, $section);
            //     if ( ($nthBid->getAmount() + 1) > $oldMinBid) {
            //       $minBid = $nthBid->getAmount() + 1;
            //       $sectionDAO -> updateMinBid($course,$section,$minBid);
            //     }
            //     else {
            //       $minBid = $oldMinBid;
            //     }
            //     //2 scenarios for the bid to be considered unsuccessful
            //     //if bid amount is equal to minBid and it is not the nthBid, it means there are multiple courses with the same minbid. No space left=>Reject
            //     //if bid amount is smaller than minBid => Automatically rejected
            //     if ( ($amount == ($minBid - 1) && $multipleSimilarMinBids == True) || $amount < ($minBid - 1)){
            //         $status = "fail";                  
            //     }
            //     else {
            //         $status = "success";
                    
            //     }
                
            //   }
            //   $bidDAO->updateStatus($userid,$course,$section,$status);
            //   //Only print out the bids for the userid that is currently logged in
            
            $stuBids = $bidDAO->retrieveStudentBids($stuID);
            foreach($stuBids as $bid)
            {
              // var_dump($bid);
              $userid = $bid->getUserid();
              $course = $bid->getCode();
              $section = $bid->getSection();
              $amount = $bid->getAmount();
              $status = $bid->getStatus();
              $sectionDump = $sectionDAO->retrieveSectionByCourse($course,$section);
              $minBid = $sectionDump->getMinbid();
                echo "<tr>";
                  echo"<td>" . $course . "</td>";
                  echo"<td>". $section . "</td>";
                  echo"<td>" .$amount. "</td>";
                  echo "<td>" . ucfirst($status) . "</td>";
                  echo ($bidRoundStatus->getRound() == 2 ? "<td>" . $minBid . "</td>": "");
                echo "</tr>";
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
