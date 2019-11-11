<?php
  require_once 'include/common.php';
  require_once 'include/protect.php';

#Get basic information about the student to populate the page 

  if (isset($_SESSION["user"]))
  {
    $student = $_SESSION["user"]; // retrive the student's information.
  }
  // var_dump($student);
  $userid = $student->getUserid();
  //  var_dump($userid);


//   var_dump($bids);
  $initialize = false; # To determine whether to show confirm dopped bids dialogue box

#Remove checkboxed value from database if submitted 
if (isset($_POST['dropsection'])){
  $SectionStudentDAO = new SectionStudentDAO();
  $dropsection = $_POST['dropsection'];
  // var_dump($dropsection);
  foreach ($dropsection as $code){
    $SectionStudentDAO->removeByID($userid,$code);
  }
}
      

?>

<html>

<head>
  <title> Drop Section </title>
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
      <li class="nav-item">
        <a class="nav-link" href="dropBid.php"> DROP BID(s)</a>
      </li>
      <li class="nav-item active">
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

<!-- Display all of student's bids-->
<div class = "row">
  <div class="col-sm-12" style='margin-top: 7.5vh'>
  <form action = 'dropSection.php' method = 'POST'>
    <table class="table table-striped">
      <h3> Your Section(s) </h3>
      <thead>
        <tr>
          <th> Course ID </th>
          <th> Section Number </th>
          <th> Bid amount </th>
          <th> Drop Section? </th>
        </tr>
        </thead>
        <tbody>
          <?php
            $SectionStudentDAO = new SectionStudentDAO();
            $SectionInformation = $SectionStudentDAO->retrieveByID($userid);
            // var_dump($SectionInformation);
            
            foreach($SectionInformation as $sectionrow){

              $course = $sectionrow->getCourse();
              $section = $sectionrow->getSection();
              $amount = $sectionrow->getAmount();
            
            echo "
            <tr>
              <td> $course </td>
              <td> $section </td>
              <td> $amount </td>
              <td> <input type=checkbox name = dropsection[] value = $course> </td>
            </tr>";

            }

          ?>
        </tbody>
        <?php
          if (isset($_POST['dropsection'])){
            echo "<font color='red'> You successfully dropped your class! </font>";
          }

        ?>
    </table>
    <input type='submit' class="btn btn-primary" value='Drop Section(s)'>
  </form>
  </div>
</div>


</div>


</html>









