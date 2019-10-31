<?php
  require_once 'include/common.php';
  require_once 'include/protect.php';
  require_once 'include/webValidation.php';
?>

<?php

$bidDAO = new BidDAO();
$student = $_SESSION["user"];
$userid = $student-> getUserid();
$edollar = $student -> getEdollar();
if (isset($_POST)) {
    //var_dump($_POST); 
    //print(array_sum($_POST));
    $errors = [];
    foreach($_POST as $key => $value)
    {
        $codeSectionList = [];
        $code ="";
        $section ="";
        $amount = "";
        if (array_sum($_POST) > $edollar) { //Does not allow user to bid if his total bid amount exceeds his current edollar amount
            $errors[] = "Insufficient edollars!";
            break;
        }
        elseif($key!= "Add_Bid") 
        {
            if ($value != "")
            {
                $codeSectionList = explode("_", $key);
                $code = $codeSectionList[0];
                $section = $codeSectionList[1];
                $amount = intval($value);
                //echo "$code $section $amount <br>"; 
                //$bidDAO->add($userid, $amount, $code, $section);
                $bid_data = [$userid, $amount, $code, $section];

                $studentDAO = new StudentDAO();
                $courseDAO = new CourseDAO();
                $sectionDAO = new SectionDAO();

                $allStudentInfo = $studentDAO->retrieveAll();
                $allCourseInfo = $courseDAO->retrieveAll();    // Get all course information (Course Class)
                $sectionsInfo = $sectionDAO->retrieveSectionByFilter($bid_data[2]); 	 // Get section list by the course 		

                $bidValidation = validateBid($bid_data, "NIL", $allStudentInfo, $allCourseInfo, $sectionsInfo);		
                if (sizeof($bidValidation)==0){
                    $bidDAO->add($bid_data[0], $bid_data[1], $bid_data[2], $bid_data[3]);
                }
                else {
                    $errors = array_merge($errors, $bidValidation);
                }
            }
        }
    }
}
/*
$sectionDAO = new SectionDAO();
$allSections = $sectionDAO -> retrieveAll();
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

$sectionCheckArray = [];
foreach($sections as $section)
{
    $sectionCheckArray[] = $section->getCourse() . $section->getSection();
}

$bidArray = [];

foreach($sectionCheckArray as $section)
{
    if(isset($_POST["$section"]))
    {
        $bidArray[] = $section;
    }
}
*/

?>