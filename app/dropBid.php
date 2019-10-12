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
  $bids = $bidDAO->retrieveStudentBids($userid);
//   var_dump($bids);

  #Remove checkboxed value from database if submitted 
  if (isset($_POST)){
    //   var_dump($_POST);
      $userid = $student->getUserid();
      foreach($_POST as $code){
        $bidDAO = new BidDAO();
        $bidDAO->removeBidByUseridAndCode($userid, $code);
          
      }
  }
?>

  

  <html>

  <head>
    <title> drop bids </title>
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
      <a class="navbar-brand" href="#">BIOS</a>
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
    <!-- End of Navigation Bar -->

  <!-- Display all of student's bids-->
  <div class = "row">
   <div class="col-sm-12" style='margin-top: 15vh'>
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
                    <td> <input type='checkbox' name = '$code' value = '$code'></td>
                  </tr> ";
                }
              }
            ?>
          </tbody>
      </table>
      <input type='submit' class="btn btn-primary" value='Drop Bid(s)'>
    </form>
   </div>
  </div>

  </div>

 







