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
  $initialize = false; # To determine whether to show confirm dopped bids dialogue box

  var_dump($_POST);

  #Remove checkboxed value from database if submitted 
  if(!empty($_POST)){
    if (isset($_POST)){
        //var_dump($_POST);
        $userid = $student->getUserid();
        $codeList = [];
        foreach($_POST as $code){
          $bidDAO = new BidDAO();
          $bidDAO->removeBidByUseridAndCode($userid, $code);
          $codeList[] = $code; # capture the list of bid(s) 
        }
      informStudentOfConfirmation();
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

  <?php
    function informStudentOfConfirmation(){
      ?>
        <script type="text/javascript">
            $(document).ready(function()
            {
                $("#initialize-User-Info2").modal('show');
            });
        </script>
      <?php
  }
  ?>
  

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
                    <td> <input type='checkbox' class='form-check-input' name = '$code' value = '$code'></td>
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
  <!-- -->


        <div class='modal fade' id='initialize-User-Info2'>
          <div class='modal-dialog modal-dialog-centered'>
            <div class='modal-content'>

              <div class='modal-header'>
                <h4 class='modal-title'> Welcome  </h4>
                <button type='button' class='close' data-dismiss='modal'> &times; </button>
              </div>

              <div class='modal-body'>
                You currently have e$ in your account.
              </div>

              <div class='modal-footer'>
                <button type='button' class='btn btn-danger' data-dismiss='modal'> Close </button>
              </div>

            </div>
          </div>
        </div>
  </div>
  </html>

 







