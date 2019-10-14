
<?php

  require_once 'include/common.php';
  require_once 'include/protect.php';
  require_once 'addBidPage-process.php';
  $studentDAO = new StudentDAO();
  $student = $_SESSION["user"];     //Student class
  $userid = $student-> getUserid();

  $bidDAO = new BidDAO();
  $stuBids = $bidDAO->retrieveStudentBids($userid);
  $stuEdollar = $student -> getEdollar();

  foreach($stuBids as $value)
    {
      $stuEdollar -= $value->getAmount();
    }
  
  $name= $student -> getName();
  
  
  
?>

<html>
<head>
  <title> Welcome <?=$name?> </title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</head>
<body>
  <?php

    // hardcoded data sure be retrive from login page
    
    //Get all Sections  
    $sectionDAO = new SectionDAO();
    $allSections = $sectionDAO ->retrieveAll();
    if (!isset($_GET["course"]) && !isset($_GET["day"]) && !isset($_GET["start"]) && !isset($_GET["end"]))
        $sections = $sectionDAO -> retrieveAll();
    else {
        $day = "";
        $start = "";
        $end = "";
        if (isset($_GET["day"]))
          $day = $_GET["day"];
        if (isset($_GET["time"]))
          $time = $_GET["time"];
        $sections = $sectionDAO -> retrieveSectionByFilter($_GET["course"], $day, $time);
    }

    // Get Student's bids
    $studentBid = new BidDAO();
    $stuBids = $studentBid->retrieveStudentBids($userid);

    $stuCurrentBids = [];

    foreach($stuBids as $item)
    {
      $stuCurrentBids[] = $item->getCode().$item->getSection();
    }
    // contains all of current student bids
    var_dump($stuCurrentBids);

    // Initialize arrays for 'section' table
    $courses = [];
    $sectionids = [];
    $days = [];
    $starts = [];
    $ends = [];
    $instructors = [];
    $venues = [];
    $sizes = [];

    
    $day_of_week = ['Monday','Tuesday','Wednesday','Thursday','Friday'];
    foreach ($allSections as $section) {
      if (!in_array($section->getCourse(),$courses)) {
        $courses[] = $section->getCourse();
      }
      if (!in_array($section->getSection(),$sectionids)) {
        $sectionids[] = $section->getSection();
      }
      if (!in_array($section->getDay(),$days)) {
        $days[] = $section->getDay();
        sort($days  );
      }
      if (!in_array($section->getStart(),$starts)) {
        $starts[] = $section->getStart();
        sort($starts);
      }
      
      if (!in_array($section->getEnd(),$ends)) {
        $ends[] = $section->getEnd();
        sort($ends);
      }
      
      if (!in_array($section->getInstructor(),$instructors)) {
        $instructors[] = $section->getInstructor();
      }
      
      if (!in_array($section->getVenue(),$venues)) {
        $venues[] = $section->getCourse();
      }
      
      if (!in_array($section->getSize(),$sizes)) {
        $sizes[] = $section->getSize();
      }
      

      /*
      echo $section->getCourse();
      echo $section->getSection();
      echo $section->getDay();
      echo $section->getStart();
      echo $section->getEnd();
      echo $section->getInstructor();
      echo $section->getVenue();
      echo $section->getSize();*/

    }
  ?>
<div class="container" >
  <div class="col-sm-12" style='padding-left: 0px; padding-right:0px'>
  </div>
  <div class="col-sm-12" style='padding-left: 0px; padding-right:0px'>
    
    
<!-- Navigation Bar -->
<nav class="navbar navbar-expand-sm bg-light navbar-light">
      <!-- Brand/logo -->
      <a class="navbar-brand" href="#">BIOS</a>
      <!-- Links -->
      <ul class="navbar-nav mr-auto"> <!-- left align-->
        <li class="nav-item">
          <a class="nav-link" href="landingPage.php"> HOME </a>
        </li>
        <li class="nav-item active">
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





  </div>
  <div class="col-sm-6" style='padding-left: 0px; padding-right:0px'>
    Welcome <?php echo " $name"; ?>
  </div>
  <div class="col-sm-6" style='padding-left: 0px; padding-right:0px'>
   Current e$: <?php echo " $stuEdollar"; ?>
  </div>
  
  <br>

  
  <div class="row">
    <div class="col-sm-6">
      <form>
        <select name="course" class="custom-select">
          <option value='' selected>Select Course</option>
          <?php
            foreach ($courses as $course) {
              $selected = "";
              if (isset($_GET["course"]) and $course == $_GET["course"])
                $selected = "selected";
              echo "<option value='$course' $selected>$course</option>";
            }
          ?>
        </select>
        <!-- Should only show when course is selected -->
        <!--
        <select name="section" class="custom-select">
        <option selected>Select Section</option>
        <?php
          /*
            foreach ($sectionids as $sectionid) {
              echo "<option value='$sectionid'>$sectionid</option>";
            }
            */
          ?>
        </select> 
          -->
    </div>
  </div>
  <div class="row">
    <div class="col-sm-3">
        <select name="day" class="custom-select">
          <option value='' selected>Select Day</option>
          <?php
          foreach ($days as $day) {
              $selected = "";
              if (isset($_GET["day"]) and $day == $_GET["day"])
                $selected = "selected";
              echo "<option value='$day' $selected>{$day_of_week[intval($day)-1]}</option>";
            }
          ?>
        </select>
    </div>
    <div class="col-sm-3">
        <select name="time" class="custom-select">
          <option value='' selected>Select Time</option>
          <?php

          for ($i=0; $i<sizeof($starts); $i++) {
            $selected = "";
            if (isset($_GET["time"]) && ($starts[$i].'-'.$ends[$i]) == $_GET["time"])
              $selected = "selected";
            echo "<option value='$starts[$i]-$ends[$i]' $selected>$starts[$i] - $ends[$i]</option>";
            }
          ?>
        </select> 
        
    </div>
    </div>
    <div class="row">  
    <div class="col-sm-6">
        <input type="submit" class="btn btn-primary" style="margin-top: 15px">
          </div>
    </div>
    </form>
  
<?php
  #echo "<h3> Hello! $stuDetails[2] </h3>";
  #echo "<h3> e$: $stuEdollar </h3>";
  #echo "<br/>";
 ?>
<div class = "row">
  <div class="col-sm-12" style='margin-top: 15vh'>           
    <table class="table table-striped">
      <h3>Add bid(s)</h3>
      <thead>
        <tr>
          <th>Course</th>
          <th>Section</th>
          <th>Day</th>
          <th>Start</th>
          <th>End</th>
          <th>Instructor</th>
          <th>Venue</th>
          <th>Size</th>
          <th>Bid ?</th>
        </tr>    
        </thead>
        <tbody>
          <form action="addBidPage.php" method="post">
            <?php
              foreach ($sections as $section) 
              {
                $checker = $section->getCourse().$section->getSection();
                $display = true;
                foreach($stuCurrentBids as $compare)
                {
                  if($compare == $checker)
                  {
                    $display = false;
                  }
                }
                if($display)
                {
                  echo "<tr>";
                  echo "<td>{$section->getCourse()}</td>";
                  echo "<td>{$section->getSection()}</td>";
                  echo "<td>{$day_of_week[intval($section->getDay())-1]}</td>";
                  echo "<td>{$section->getStart()}</td>";
                  echo "<td>{$section->getEnd()}</td>";
                  echo "<td>{$section->getInstructor()}</td>";
                  echo "<td>{$section->getVenue()}</td>";
                  echo "<td>{$section->getSize()}</td>";
                  echo "<td><input type='number' name={$section->getCourse()}.{$section->getSection()} min='10' ></td>";
                  echo "</tr>";
                }
            }
            
            ?>
        </tbody>
    </table>
    <?php
    echo"<input type='submit' name='Add Bid' class='btn btn-primary' style='margin-top: 15px, margin-bottom: 15px'> Add bid</input>";
    ?>
    </form>
  </div>
  </div>
</div>
</div>


</body>
</html>
