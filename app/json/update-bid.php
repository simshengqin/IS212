<?php

require_once '../include/common.php';

##########################
### DAO Initialization ###
##########################

$bidDAO = new BidDAO();
$studentDAO = new StudentDAO();
$sectionDAO = new SectionDAO();
$courseDAO = new CourseDAO();
$courseCompletedDAO = new CourseCompletedDAO();
$bidStatusDAO = new BidStatusDAO();
$prerequisiteDAO = new PrerequisiteDAO();
$sectionStudentDAO = new SectionStudentDAO(); 



#######################################
##  Retrieve All Info from the DAOs  ##
#######################################

$allSectionData = $sectionDAO->retrieveAll();
$allStudentData = $studentDAO->retrieveAll();
$allCourseData = $courseDAO->retrieveAll();
$allEnrolledData = $SectionStudentDAO->retrieveAll();

$bidRoundStatus = $bidStatus->retrieveBidStatus();

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
http://<host>/app/json/update-bid.php?r={
   "userid": "ada.goh.2012",
   "amount": 11.0,
   "course": "IS100",
   "section": "S1"
}
*/
$request = $_GET['r'];
$data = json_decode($request, true);     


############################
## Get Specific User Info ##
############################

// Get student information (Student class)
$student = $studentDAO->retrieveStudent($data['userid']);
// Get edollar from student class
$studentEdollar = $student->getEdollar();
// Get a list of student bids (Bid Class)
$studentBids = $bidDAO->retrieveStudentBidsWithInfo($data['userid']);
// Get the section course info (Section Class)
$section = $sectionDAO->retrieveSectionByCourse($data['course'], $data['section']);
// Get course info (Course Class)
$course = $courseDAO->retrieveCourse($data['course']);
// Get a list of all the course completed of the user (List of Course)
$coursesCompleted = $courseCompletedDAO->retrieveCourseCompletedByUserId($userid);
// Get a list of all the prerequisites of the user (List of Prerequisites)
$prerequisites = $prerequisiteDAO -> retrievePrerequisiteByCourse($bidCode);

#################
## Validations ##
#################

//--------------//
// Check UserID // 
//--------------//

$errors = [];
if (!in_array($data['userid'], $studentList)){
    $errors[] = "invalid userid";
}

//------------------//
// Check Bid Amount //
//------------------//
/*
    Amount must be positive number >= e$10.00 and not more than 2 decimal places
*/

if(!is_numeric($data['amount']) || $data['amount'] < 10.0){       // check if edollar is not numerical value or less than e$10
    $errors[] = "invalid amount";
}
else {
    if ((intval($bidAmount) != $bidAmount) && (strlen($bidAmount) - strrpos($bidAmount, '.') - 1 > 2)) {    // check that the edollar is not more than 2 decimal places
        $errors[] = "invalid amount";
    }
}

//---------------------------------------//
// Invalid Course and Section Validation //
//---------------------------------------//
/*
    Course code is not found in system records
    Section code is not found in system records. Only check if the course code is valid
*/  
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


//-------------//
// Bid Too Low //
//-------------//
/*
    The amount must be more than the minimum bid (only applicable for round 2)
*/





//-----------------//
// Insufficient e$ //
//-----------------//
/*
    Student has not enough e-dollars to place the bid. If it is an 
    update of a previous bid, account for the extra e$ gained back 
    from the cancellation of the previous bid first.
*/
foreach ($studentBids as $studentBid) {
    if ($studentBid->getCode() == $data['course'] && $studentBid->getSection() == $data['section']){
        $studentEdollar+= $studentBid->getAmount();
    }
}
if (($studentEdollar - $data['amount']) < 0)+
    $errors[] = 'insufficient e$';



//-----------------------//
// Class Timetable Clash //
//-----------------------//
/*
    The class timeslot for the section clashes with that 
    of a previously bidded section
*/

$start_timing = [                           // Changed to 08:30:00 instead of 8:30 because it's converted to DATETIME format
    '08:30:00' => 0,                        // when it's uploaded to the database (PHPmyAdmin)
    '12:00:00' => 1, 
    '15:30:00' => 2
];
$end_timing = [
    '11:45:00' => 0, 
    '15:15:00' => 1, 
    '18:45:00' => 2
];

foreach ($studentBids as $bid) {
    if (isset($section) && ($bid['day'] == $section->getDay())  && ($start_timing[$bid['start']] == $start_timing[$section->getStart()] || $end_timing[$bid['end']] == $end_timing[$section->getEnd()])){
        $errors[] = "class timetable clash";
    }
}

//----------------------//
// Exam Timetable Clash //
//----------------------//
/*
    The exam timeslot for this section clashes with that
    of a previously bidded section
*/
foreach ($studentBids as $bid) {
    if (($bid['exam date'] == $course->getExamdate())  && ($start_timing[$bid['exam start']] == $start_timing[$course->getExamstart()] || $end_timing[$bid['exam end']] == $end_timing[$course->getExamend()])){
        $errors[] = "exam timetable clash";
    }
}


//--------------------------//
// Incomplete Prerequisites //
//--------------------------//
/*
    Student has not completed the prerequisites for this course
*/
if (!empty($prerequisites)){  // If there is prerequisites
    if (array_diff($prerequisites, $coursesCompleted)) {
        $errors[] = "incomplete prerequisites";
    }
}

//-------------//
// Round Ended //
//-------------//
/*
    Student cannot bid as there is no Active Round
*/
if ($bidRoundStatus->getStatus() != 'open')
    $errors[] = 'round ended';


//------------------//
// Course Completed //
//------------------//
/*
    Student has already completed this course
*/
if (in_array($data['course'],$coursesCompleted)) {
    $errors[] = "course completed";
}

//-----------------//
// Course Enrolled //
//-----------------//
/*
    Student has already won a bid for a section in this course in a previous round
*/
foreach ($allEnrolledData as $enrolledData){
    if ($enrolled)
}



//-----------------------//
// Section Limit Reached //
//-----------------------//
/*
    Student has already bidded for 5 sections. If it is an update of a 
    previous bid, account for the cancellation of the previous bid. 
*/




if (sizeof($errors)==0){
    $result = [
        "status" => "success"
    ];

}
else {
    $result = [
        "status" => "error",
        "message" => $errors
    ];
}






?>