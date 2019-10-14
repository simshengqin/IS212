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

    $bidDAO = new BidDAO();
    $stuBids = $bidDAO->retrieveStudentBids($stuID);

    // calculate remaining amount
    foreach($stuBids as $value)
    {
      
      $stuEdollar -= $value->getAmount();
    }
   ?>

   <?php
   var_dump($initialize);
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
      <a class="navbar-brand" href="#">BIOS</a>
      <!-- Links -->
      <ul class="navbar-nav mr-auto"> <!-- left align-->
        <li class="nav-item active">
          <a class="nav-link" href="landingPage.php"> HOME </a>
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
  echo "<h3> Hello! $stuName</h3>";
  echo "<h3> e$: $stuEdollar </h3>";
  echo "<br/>";
 ?> 


 <div class = "row">
   <div class="col-sm-12" style='margin-top: 15vh'>
     <table class="table table-striped">
       <h3> Bid(s) </h3>
       <thead>
         <tr>
           <th>Course Code</th>
           <th>Section Code</th>
           <th>Amount Bid</th>
           <th> Status </th>
         </tr>
         </thead>
         <tbody>
           <?php
             if(count($stuBids) == 0)
             {
               echo "<tr> <td colspan='4'> <h4 style='text-align: center;'> You currently have no bids </h4> </td> </tr>";
             }
             else
             {
               echo "<tbody>";
               foreach($stuBids as $value)
               {
                 echo "<tr>";
                   echo"<td>" . $value->getCode() . "</td>";
                   echo"<td>". $value->getSection() . "</td>";
                   echo"<td>" .$value->getAmount(). "</td>";
                   echo"<td> Pending </td>";
                 echo "</tr>";
               }
               echo "</tbody>";
             }
           ?>
         </tbody>
     </table>
   </div>
  </div>




  <div class = "row">
    <div class="col-sm-12" style='margin-top: 15vh'>
      <table class="table table-striped">
        <h3> Sections(s) </h3>
        <thead>
          <tr>
            <th>Course Code</th>
            <th>Section Code</th>
            <th>Amount Bid</th>
            <th> Status </th>
          </tr>
        </thead>
          <tbody>
            <?php
            if(count($stuSections) == 0)
            {
              echo "<tr> <td colspan='4'> <h4 style='text-align: center;'> You are currently not enrolled in any course </h4> </td> </tr>";
            }
            else
            {
              /*
              echo "<tbody>";
              foreach($stuSections as $value)
              {
                echo "<tr>";
                  echo"<td>$value[2]</td>";
                  echo"<td>$value[3]</td>";
                  echo"<td>$value[1]</td>";
                echo "</tr>";
              }
              echo "</tbody>";
              */
            }

            ?>
          </tbody>
      </table>
    </div>
   </div>


</div>


</body>
</html>
