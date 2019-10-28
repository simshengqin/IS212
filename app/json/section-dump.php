<?php
require_once '../include/common.php';
require_once '../include/protect_json.php';

########################
## DAO Initialization ##
########################
$courseDAO = new CourseDAO();
$sectionDAO = new SectionDAO();
$sectionStudentDAO = new SectionStudentDAO();
$sortclass = new Sort();


###########################
## Get data from request ##
###########################
/*
-- JSON Request Example --
http://<host>/app/json/section-dump.php?r={
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


#######################
## Errors Validation ##
#######################
/*
    "invalid course"	Course code does not exist in the system's records
    "invalid section"	No such section ID exists for the particular course. Only check if course is valid
*/
$errors = [];
if (empty($courseInfo)){
    $errors[] = 'invalid course';
}
elseif (empty($sectionInfo)){
        $errors[] = "invalid section";
}


########################
## Section Dump Logic ##
########################
/*
    This web service will allow an administrator to retrieve the information for a section, 
    and it's enrolled students. During round 2, this should return the enrolled students bidded successfully in round 1. 
    After round 2 is closed, this should return the enrolled students who bidded successfully in round 1 & 2.
*/
if (empty($errors)) {
    $result = [
        "status" => "success",
        "students" => []
    ];
    $sectionStudentList = $sectionStudentDAO->retrieveByCourseSection($data['course'], $data['section']);
    foreach ($sectionStudentList as $sectionStudent){
        $temp = [];
        $temp['userid'] = $sectionStudent->getUserid();
        $temp['amount'] = (float) $sectionStudent->getAmount();
        $result['students'][] = $temp;
    }
    $result['students'] = $sortclass->sort_it($result['students'], 'student');
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