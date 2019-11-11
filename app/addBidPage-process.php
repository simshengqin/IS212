<?php
  require_once 'include/common.php';
  require_once 'include/protect.php';
  require_once 'include/webValidation.php';
  require_once 'clearBidTwo-process.php';

?>

<?php

$bidDAO = new BidDAO();
$student = $_SESSION["user"];
$userid = $student-> getUserid();
$edollar = $student -> getEdollar();
if (isset($_POST)) {
    
    // var_dump($student);
    //print(array_sum($_POST));
    $errors = [];
    $check_repeats =[];
    // Cannot display the same error message twice fpr user bid vali 
    $sameSectionErrorTriggered = FALSE;

    foreach($_POST as $key => $value)
    {
        // var_dump($key);
        // var_dump($value);
        $codeSectionList = [];
        $code ="";
        $section ="";
        $amount = "";
        
        
        $codeSectionList = explode("_", $key);
        $code = $codeSectionList[0];
        $section = $codeSectionList[1];

        //Validation: Does not allow bid if the same course of a different section is already in the database
        $bidDAO = new BidDAO;
        $seeIfExist = $bidDAO->retrieveStudentBidsByCourse($userid, $code);
        var_dump($seeIfExist);

        if(is_string($seeIfExist) == FALSE && $value!=""){
            echo "hello";
            $errors['message'][] = "Error: Already bidded for $code!";
            break;
        }

        // Validation: Check if the user bids for multiple sections of the same course 
        if($value != ""){
            $check_repeats[] = $code;
        }
        // var_dump($check_repeats);
        $counts = array_count_values($check_repeats);
        
        if (!empty($counts) && $counts[$code] > 1 ){
            // echo "Triggered!";
            if($sameSectionErrorTriggered == false){
                $sameSectionErrorTriggered = TRUE;
                // Check round 
                $bidStatusDAO = new BidStatusDAO();
                $bidStatus = $bidStatusDAO->getBidStatus();
                $round = $bidStatus->getRound();

                // Only update vacancy if in round 2 
                if($round == 2){
                    
                    // Remove from the current bid database 
                    
                    $bidDAO = new BidDAO;
                    $sectionDAO = new SectionDAO();
                    // var_dump($userid);
                    // var_dump($code);

                    // Retrieve class that is currently in database
                    $sectionToRemove = $bidDAO->retrieveStudentBidsByCourse($userid, $code);
                    //Retrieve vacancy of the database 
                    // var_dump($sectionToRemove);
                    $sectionToRemove = $sectionToRemove->getSection();

                    $vacancy = $sectionDAO->retrieveVacancy($code, $sectionToRemove);
                    $bidDAO->removeBidByUseridAndCode($userid, $code);
                    $sectionDAO->updateVacancy($code,$sectionToRemove,$vacancy + 1);
                    
                }

                $bidDAO = new BidDAO;
                $bidDAO->removeBidByUseridAndCode($userid, $code);
                $errors['message'][] = "Error: The following course is bidded in two or more sections: $code. All sections of this course will not be bidded.";
                continue;
            }
            continue;
        }



        //Validation: Does not allow user to bid if his total bid amount exceeds his current edollar amount
        if (array_sum($_POST) > $edollar) { 
            $errors['message'][] = "Error: Insufficient edollars!";
            break;
        }
        elseif($key!= "Add_Bid") 
        {
            if ($value != "")
            {
                $codeSectionList = explode("_", $key);
                $code = $codeSectionList[0];
                $section = $codeSectionList[1];
                $amount = floatval($value);
                //echo "$code $section $amount <br>"; 
                //$bidDAO->add($userid, $amount, $code, $section);
                $bid_data = [$userid, $amount, $code, $section];

                $studentDAO = new StudentDAO();
                $courseDAO = new CourseDAO();
                $sectionDAO = new SectionDAO();
                $sectionStudentDAO = new SectionStudentDAO();

                $allStudentInfo = $studentDAO->retrieveAll();
                $allCourseInfo = $courseDAO->retrieveAll();    // Get all course information (Course Class)
                $sectionsInfo = $sectionDAO->retrieveSectionByFilter($bid_data[2]); 	 // Get section list by the course 		

                $bidValidation = validateBid($bid_data, "NIL", $allStudentInfo, $allCourseInfo, $sectionsInfo);		
                if (sizeof($bidValidation)==0){
                    $userid = $bid_data[0];
                    $amount = $bid_data[1];
                    $course = $bid_data[2];
                    $section = $bid_data[3]; 
                    $bidDAO->add($userid, $amount, $course, $section);
                    $studentInfo = $studentDAO->retrieveStudent($userid);
                    $studentDAO->updateEDollar($userid, $studentInfo->getEdollar() - $amount);
                    
                    #retrieve the round 
                    $bidStatusDAO = new BidStatusDAO();
                    $bidStatus = $bidStatusDAO->getBidStatus();
                    $round = $bidStatus->getRound();
                    
                    #Update vacancy: Only done if it is round 2.
                    if ($round == '2'){
                        
                        //Validate if vacancy above 0. If not, throw an error 
                        //Retrieve Vacancy
                        $sectionInfo = $sectionDAO->retrieveSectionByCourse($course,$section);
                        $sectionSize = $sectionInfo->getSize();
                        $enrolledStudents = $sectionStudentDAO->retrieveByCourseSection($course, $section);
                        $vacancy = $sectionSize - sizeof($enrolledStudents);
                        // $vacancy = $sectionDAO->retrieveVacancy($course, $section);
                        // var_dump($vacancy);

                        // Error message for vacancy
                        if ($vacancy == 0 ){
                            $errors['message'][] = "Course: $course Section: $section Error: No Vacancy";
                            // Removing Bid 
                            $bidDAO->removeBidByUseridAndCode($userid, $course);
                            continue;
                        }

                        $sectionDAO->updateVacancy($course,$section,$sectionDAO->retrieveVacancy($course, $section) - 1);
                        doRoundTwo();
                    }
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