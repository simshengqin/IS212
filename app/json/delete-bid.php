<?php
require_once '../include/common.php';
require_once '../include/protect_json.php';

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


###########################
## Get data from request ##
###########################
/*
-- JSON Request Example --
http://<host>/app/json/delete-bid.php?r={
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
# Bid Info
$bidInfo = $bidDAO->retrieveStudentBidsWithInfoByCourseSection($data['userid'], $data['course'], $data['section']);
$bidItem = $bidDAO->retrieveStudentBidsByCourse($data['userid'], $data['course']);

#######################
## Errors Validation ##
#######################
/*
    "invalid course"	Course code does not exist in the system's records
    "invalid userid"	userid does not exist in the system's records
    "invalid section"	No such section ID exists for the particular course. Only check if course is valid
    "round ended"	    The current bidding round has already ended.
    "no such bid"	    No such bid exists in the system's records. Check only if there is an 
                        (1) active bidding round, 
                        (2) course, userid and section are valid and 
                        (3) the round is currently active.
*/
$errors = [];
$result = [];
if (empty($courseInfo))
    $errors[] = 'invalid course';
elseif (empty($sectionInfo))
    $errors[] = "invalid section";
if (empty($studentInfo))
    $errors[] = 'invalid userid';
if ($bidRoundStatus->getStatus() != 'open')
    $errors[] = 'round ended';

if (empty($errors)){
    if(empty($bidInfo)){
        $errors[] = 'no such bid';
        $result = [
            "status" => "error",
            "message" => $errors
        ];
    }
    else{
        $result = [
            "status" => "success"
        ];
        $bidDAO->removeBidByUseridAndCode($data['userid'], $data['course']);
        $studentDAO->updateEdollar($data['userid'], $studentInfo->getEdollar() + $bidItem->getAmount());
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