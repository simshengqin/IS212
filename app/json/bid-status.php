<?php
require_once '../include/common.php';
require_once '../include/protect_json.php';
require_once '../clearBidTwo-process.php';
########################
## DAO Initialization ##
########################

$bidDAO = new BidDAO();
$studentDAO = new StudentDAO();
$sectionDAO = new SectionDAO();
$courseDAO = new CourseDAO();
$courseCompletedDAO = new CourseCompletedDAO();
$bidStatusDAO = new BidStatusDAO();
$prerequisiteDAO = new PrerequisiteDAO();
$sectionStudentDAO = new SectionStudentDAO();
$sort = new Sort();
##########################
## Check Missing Fields ##
##########################

function checkMissingFields($data, $fields){
    $errors = [];
    if (sizeof($data) != sizeof($fields)){
        foreach ($fields as $field){
            if (!array_key_exists($field, $data)){
                $errors[] = "missing $field";
            }
        }
    }
    return $errors;
}

#######################################
##  Retrieve All Info from the DAOs  ##
#######################################

$allSectionData = $sectionDAO->retrieveAll();
$allStudentData = $studentDAO->retrieveAll();
$allCourseData = $courseDAO->retrieveAll();

$bidRoundStatus = $bidStatusDAO->getBidStatus();

$studentList = [];
$courseList = [];
$sectionList = [];

foreach ($allStudentData as $student){
    $studentList[] = $student->getUserid();
}

###########################
## Get data from request ##
###########################
/*
-- JSON Request Example --
http://<host>/app/json/bid-status?r={
         "course": "IS100",
         "section": "S1"
}&token=[tokenValue]

*/

$errors = []; 
$fields = ['course', 'section'];
if (isset($_GET['r'])) {
    $request = $_GET['r'];
    $data = json_decode($request, true);     
    ##############################
    ## Check for Missing Fields ##
    ##############################
    $errors = array_merge(checkMissingFields($data, $fields), $errors);
    if (!empty($errors)){
        $result = [
            "status" => "error",
            "message" => $errors
        ];
        header('Content-Type: application/json');
        echo json_encode($result, JSON_PRETTY_PRINT);
        exit();
    }
    //---------------------------------------//
    // Invalid Course and Section Validation //
    //---------------------------------------//
    /*
        Course code is not found in system records
        Section code is not found in system records. Only check if the course code is valid
    */  
    // Get course info (Course Class)
    $course = $courseDAO->retrieveCourse($data['course']);
    foreach($allCourseData as $val){
        if ($data['course'] == $val->getCourse())
            $course = $val;                         // Retrieve 'course' class for logic validation 
        $courseList[] = $val->getCourse();          // Store all course in $courseList
    }
    if (!in_array($data['course'], $courseList)){   // Check if inputted course exist in current course database
        $errors[] = "invalid course";                           
    }
    else {                                                
        // Section Validation                              // Check ONLY if course validation is valid
        if (empty($sectionDAO->retrieveSectionByCourse($data['course'], $data['section']))){
            $errors[] = "invalid section";  
        }
    }
}
// If request is not found
else {
    $errors[] = 'no request'; 
}
sort($errors);
if (sizeof($errors)==0){
    $result = [
        "status" => "success"
    ];
    ###################################################
    ## Retrieves vacancy,min-bid amount and students ##
    ###################################################
    #Different retrieval methods based on the bid round and status (open/closed)
    $course = $data['course'];
    $section = $data['section'];
    $round = $bidRoundStatus->getRound();
    $bidStatus = $bidRoundStatus->getStatus(); 
;
    $sectionObj = $sectionDAO->retrieveSectionByCourse($course, $section);
    $sectionSize = $sectionObj->getSize();
    $biddedCourses = $bidDAO->retrieveStudentBidsByCourseAndSectionOrderDesc($course, $section);

    $enrolledStudents = $sectionStudentDAO->retrieveByCourseSection($course, $section);
    $noOfEnrolledStudents = sizeof($enrolledStudents);   
    #Sort according to bid in descending order via retrieveStudentBidsByCourseAndSectionOrderDesc()

    $vacancy = 0;
    $minBidAmount = 0;
    $students = [];
    if ($round == 1 && $bidStatus == "open") {
        ###Get vacancy###
        $seatsAvailable = $sectionObj->getSize();
        ###Get Min-bid amount###
        # Min bid = 0 when no bids are made
        if (sizeof($biddedCourses) == 0) {
            $minBidAmount = 10.0;
        }
        # When number of bids are less than the vacancy, report the lowest bid amount, which will be the last bid as bids are sorted in descending order
        elseif (sizeof($biddedCourses) < $sectionSize) {
            $minBidAmount = end($biddedCourses)->getAmount();
        }
        elseif (sizeof($biddedCourses) >= $sectionSize){
            $minBidAmount = $biddedCourses[$sectionSize-1]->getAmount();
        }
        # Get userid,amount,edollar balance and status 
        foreach ($biddedCourses as $studentBid) {
            $student = [];
            $student["userid"] = $studentBid->getUserid();
            $student["amount"] = $studentBid->getAmount();
            $student["balance"] = (float) $studentDAO->retrieveStudent($studentBid->getUserid())->getEdollar();
            $student["status"] = $studentBid->getStatus();
            $students[] = $student;
        }

    }
    elseif ($round == 1 && $bidStatus == "closed") {
         ###Get vacancy###
         $seatsAvailable = $sectionSize - $noOfEnrolledStudents;
         ###Get Min-bid amount###
         # Min bid = 10 when no bids are made or no students are enrolled
         if (sizeof($biddedCourses) == 0 || sizeof($enrolledStudents) == 0) {
             $minBidAmount = 10.0;
         }
         # When number of bids are less than the vacancy, report the lowest bid amount, which will be the last bid as bids are sorted in descending order
         elseif (sizeof($biddedCourses) < $sectionSize) {
             $minBidAmount = end($biddedCourses)->getAmount();
         }
         elseif (sizeof($biddedCourses) >= $sectionSize){
             $minBidAmount = $biddedCourses[$sectionSize-1]->getAmount();
         }
         ###Get students Balance is wrong, still need to redo###
         foreach ($biddedCourses as $studentBid) {
             $student = [];
             $student["userid"] = $studentBid->getUserid();
             $student["amount"] = $studentBid->getAmount();
             $student["balance"] = (float) $studentDAO->retrieveStudent($studentBid->getUserid())->getEdollar();
             $student["status"] = $studentBid->getStatus();
             $students[] = $student;
         }       
    }
    elseif ($round == 2 && $bidStatus == "open") {
         ###Get vacancy###
         $seatsAvailable = $sectionSize - $noOfEnrolledStudents;
         ###Get Min-bid amount###
         $minBidAmount = $sectionDAO->retrieveMinBid($course, $section);
         ###Get all student bids###
         foreach ($biddedCourses as $studentBid) {
             $student = [];
             $student["userid"] = $studentBid->getUserid();
             $student["amount"] = $studentBid->getAmount();
             $student["balance"] = (float) $studentDAO->retrieveStudent($studentBid->getUserid())->getEdollar();
             $student["status"] = $studentBid->getStatus();
             $students[] = $student;
         }
    }
    elseif ($round == 2 && $bidStatus == "closed" ) {
         ###Get vacancy###
         $seatsAvailable = $sectionSize - $noOfEnrolledStudents;
         ###Get Min-bid amount###
         $minBidAmount = $sectionDAO->retrieveMinBid($course, $section);
         ###Get all student bids###
         foreach ($biddedCourses as $studentBid) {
             $student = [];
             $student["userid"] = $studentBid->getUserid();
             $student["amount"] = $studentBid->getAmount();
             $student["balance"] = (float) $studentDAO->retrieveStudent($studentBid->getUserid())->getEdollar();
             $student["status"] = $studentBid->getStatus();
             $students[] = $student;
         }        
    }
    $result["vacancy"] = $seatsAvailable;
    $result["min-bid-amount"] = (float) $minBidAmount;
    $result["students"] = $students;
}

else {
    $result = [
        "status" => "error",
        "message" => $errors
    ];

}
header('Content-Type: application/json');
echo json_encode($result, JSON_PRETTY_PRINT | JSON_PRESERVE_ZERO_FRACTION);

?>