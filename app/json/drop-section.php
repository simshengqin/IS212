<?php
require_once '../include/common.php';
require_once '../include/protect_json.php';


########################
## DAO Initialization ##
########################
$courseDAO = new CourseDAO();
$sectionDAO = new SectionDAO();
$studentDAO = new StudentDAO();
$sectionStudentDAO = new SectionStudentDAO();
$sortclass = new Sort();
$bidStatusDAO = new BidStatusDAO();


###########################
## Get data from request ##
###########################
/*
-- JSON Request Example --
http://<host>/app/json/drop-section.php?r={
   "userid": "ada.goh.2012",
   "course": "IS100",
   "section": "S1"
}
*/
$request = $_GET['r'];
$data = json_decode($request, true);


###############################
## Initialize Necessary Data ##
###############################
# Course
$courseInfo = $courseDAO->retrieveCourse($data['course']);
# Section
$sectionInfo = $sectionDAO->retrieveSectionByCourse($data['course'],$data['section']);
# Student
$studentInfo = $studentDAO->retrieveStudent($data['userid']);
# Bid Round Status
$bidRoundStatus = $bidStatusDAO->getBidStatus();
# Section Student
$sectionStudentInfo = $sectionStudentDAO->retrieveByCourseSectionUser($data['course'], $data['section'], $data['userid']);


#######################
## Errors Validation ##
#######################
/*
    "invalid course"	Course code does not exist in the system's records
    "invalid userid"	userid does not exist in the system's records
    "invalid section"	No such section ID exists for the particular course. Only check if course is valid
    "round not active"	There is currently no active round.
*/
$errors = [];
if (empty($courseInfo))
    $errors[] = 'invalid course';
elseif (empty($sectionInfo))
    $errors[] = "invalid section";
if (empty($studentInfo))
    $errors[] = 'invalid userid';
if ($bidRoundStatus->getStatus() != 'open')
    $errors[] = 'round not active';


########################
## Section Drop Logic ##
########################
/*
    This web service will allow an administrator to drop a user's enrollment in a section. 
    User will receive e$ refund. This web service allows the administrator to drop the section of a user.
*/
if (empty($errors)) {

    $result = [
        "status" => "success"
    ];

    //------------------------//
    // Drop User's Enrollment //
    //------------------------//
    $sectionStudentDAO->removeByIDCourseSection($data['userid'],$data['course'], $data['section']);

    //-----------//
    // Refund e$ //
    //-----------//
    if (!empty($sectionStudentInfo)){
        $refundAmount = $sectionStudentInfo->getAmount();
        $edollar = $studentInfo->getEdollar() + $refundAmount;
        $studentDAO->updateEDollar($data['userid'],$edollar);
    }
}
else {
    $result = [
        "status" => "error",
        "message" => $errors
    ];
}

header('Content-Type: application/json');
$result = json_encode($result, JSON_PRETTY_PRINT | JSON_PRESERVE_ZERO_FRACTION);
echo $result;

?>