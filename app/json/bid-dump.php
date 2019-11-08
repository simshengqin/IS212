<?php 
require_once '../include/common.php';
require_once '../include/protect_json.php';
    
########################
## DAO Initialization ##
########################
$bidDAO = new BidDAO();
$courseDAO = new CourseDAO();
$sectionDAO = new SectionDAO();
$bidStatusDAO = new BidStatusDAO();
$bidRoundStatus = $bidStatusDAO->getBidStatus();


###########################
## Get data from request ##
###########################
/*
-- JSON Request Example --
http://<host>/app/json/bid-dump.php?r={
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


####################
## Bid Dump Logic ##
####################
/*
    This web service will allow an administrator to retrieve the bidding information of a specific section for the current 
    bidding round. If no bidding rounds are active, the information for the most recently concluded round is dumped.
*/
if (empty($errors)) {
    $result = [
        "status" => "success",
        "bids" => []
    ];
    $row = 1; 
    $bidList = $bidDAO->retrieveStudentBidsByCourseAndSectionOrderDesc($data['course'], $data['section']);
    foreach ($bidList as $bid){
        $temp = [];
        $temp['row'] = $row;
        $temp['userid'] = $bid->getUserid();
        $temp['amount'] = (float) $bid->getAmount();
        if ($bidRoundStatus->getStatus() == 'open')
            $temp['result'] = '-';
        else
            $temp['result'] = $bid->getStatus() == 'success' ? 'in' : 'out';
        $result['bids'][] = $temp;
        $row++;
    }
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