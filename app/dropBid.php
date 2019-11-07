<?php
  require_once 'include/common.php';
  require_once 'include/protect.php';

#Get basic information about the student to populate the page 

  if (isset($_SESSION["user"]))
  {
    $student = $_SESSION["user"]; // retrive the student's information.
  }
//   var_dump($student);
  $userid = $student->getUserid();
//   var_dump($userid);
  $bidDAO = new BidDAO();
  
//   var_dump($bids);
  $initialize = false; # To determine whether to show confirm dopped bids dialogue box

// Find the round 
  $BidStatusDAO = new BidStatusDAO();
  $BidStatus = $BidStatusDAO->getBidStatus();
  $round = $BidStatus->getRound();

  #Remove checkboxed value from database if submitted 
  if(!empty($_POST)){
    if (isset($_POST)){
        // var_dump($_POST);
        $userid = $student->getUserid();
        $codeList = [];
        foreach($_POST as $code){
          
          #If in round 2, increase vacancy 
          if ($round == 2){
            #find the section number of the student 
            $bid = $bidDAO->retrieveStudentBidsByCourse($userid, $code);
            // var_dump($bid);
            $section = $bid->getSection();
            #Retrieve vacancy of the bid 
             
            $SectionDAO = new SectionDAO ;
            $vacancy = $SectionDAO->retrieveVacancy($code, $section);
            // var_dump($vacancy);

            $SectionDAO->updateVacancy($code,$section,$vacancy+1);
          }
          $bidDAO->removeBidByUseridAndCode($userid, $code);
          $codeList[] = $code; # capture the list of bid(s) 
        }
    }
  }

?>

  

  <html>

  <head>
    <title> Drop Bids </title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
  </head>



  

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
          <a class="nav-link" href="landingPage.php"> HOME </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="addBidPage.php"> ADD BID(s)</a>
        </li>
        <li class="nav-item active">
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

<div class='modal fade' id='initialize-User-Info2' tabindex="-1" role="dialog">
  <div class='modal-dialog modal-dialog-centered'>
    <div class='modal-content'>

      <div class='modal-header'>
        <h4 class='modal-title'> Welcome  </h4>
        <button type='button' class='close' data-dismiss='modal'> &times; </button>
      </div>

      <div class='modal-body'>
        You currently have e$<?$student->getEdollar()?> in your account.
      </div>

      <div class='modal-footer'>
        <button type='button' class='btn btn-danger' data-dismiss='modal'> Close </button>
      </div>

    </div>
  </div>
</div>

  <!-- Display all of student's bids-->
  <div class = "row">
   <div class="col-sm-12" style='margin-top: 7.5vh'>
    <form action = 'dropBid.php' method = 'POST'>
      <table class="table table-striped">
        <h3> Your Bid(s) </h3>
        <thead>
          <tr>
            <th> Course ID </th>
            <th> Section Number </th>
            <th> Bid amount </th>
            <th> Drop bid? </th>
          </tr>
          </thead>
          <tbody>
            <?php
              $bids = $bidDAO->retrieveStudentBids($userid);
              if(count($bids) == 0)
              {
                echo"<tr> <td colspan='4'> <h4 style='text-align: center;'> You currently have no bids </h4> </td> </tr>";
              }
              else
              {
                foreach ($bids as $bid){
                  $code = $bid->getCode();
                  $section_number = $bid->getSection();
                  $bid_amount = $bid->getAmount();
                  echo "<tr> 
                        <td> $code  </td>
                        <td> $section_number </td>
                        <td> $bid_amount </td>
                        <td> <input type='checkbox' class='form-check-input' name = '$code' value = '$code'></td>
                      </tr> ";
                }
              }
            ?>
          </tbody>
      </table>
      <button type='submit' class="btn btn-primary" >Drop Bid(s)</button>
    </form>
   </div>
  </div>
  </div>
  </html>

 







