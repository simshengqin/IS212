<?php
  require_once 'include/common.php';
  require_once 'include/protect.php'; 

  if (isset($_SESSION["user"])){
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
    // Assuming that login page is sucessfull:

    $stuName = $student->getName();
    $stuID = $student->getUserid();
    $stuBids = [];
    $stuSections = [];
    $stuEdollar = $student->getEdollar();


    $studentBid = new BidDAO();
    $stuBids = $studentBid->retrieveStudentBids($stuID);
    
   ?>

<div class="container">


  <nav class="navbar navbar-expand-sm bg-light navbar-light">
    <!-- Brand/logo -->
    <a class="navbar-brand" href="#">BIOS</a>
    <!-- Links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" href="landingPage.php">HOME</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#">SECTIONS</a>
      </li>
    </ul>
  </nav>


 <br/>


<?php
  echo "<h3> Hello! $stuName</h3>";
  echo "<h3> e$: $stuEdollar </h3>";
  echo "<br/>";
 ?>


 <div class = "row">
   <div class="col-sm-12" style='margin-top: 15vh'>
     <table class="table table-striped">
       <h3> bid(s) </h3>
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
               echo"You currently have no bids";
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
        <h3> sections(s) </h3>
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
              echo "<tr>";
              echo"You currently not enrolled in any course";
              echo "</tr>";
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
