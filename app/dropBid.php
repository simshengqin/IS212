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


  #Creating table of current bids

  echo "
  <form action = 'dropBid.php' method = 'POST'>
  <table border = 1>
    <tr>
    <th> Course ID </th>
    <th> Section Number </th>
    <th> Bid amount </th>
    <th> Drop bid? </th>
    </tr>
  ";

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
  echo "</table>";
  echo "
  <input type='submit' value='Drop bids'>
  </form>";


    



?>
