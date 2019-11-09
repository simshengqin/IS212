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
    foreach ($fields as $field){
        if (!array_key_exists($field, $data)){
            $errors[] = "missing $field";
        }
        elseif ($data[$field] == ""){
            $errors[] = "blank $field";
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
http://<host>/app/json/update-bid.php?r={
   "userid": "ada.goh.2012",
   "amount": 11.0,
   "course": "IS100",
   "section": "S1"
}
*/
$errors = [];
$fields = ['amount', 'course', 'section','userid'];
if (isset($_GET['r'])) {
    $request = $_GET['r'];
    $data = json_decode($request, true);     
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

############################
## Get Specific User Info ##
############################

    // Get student information (Student class)
    $student = $studentDAO->retrieveStudent($data['userid']);
    // Get edollar from student class
    if (!empty($student))
        $studentEdollar = $student->getEdollar();
    // Get a list of student bids (Associative Array)
    $studentBids = $bidDAO->retrieveStudentBidsWithInfo($data['userid']);
    // Get the section course info (Section Class)
    $section = $sectionDAO->retrieveSectionByCourse($data['course'], $data['section']);
    // Get course info (Course Class)
    $course = $courseDAO->retrieveCourse($data['course']);
    // Get a list of all the course completed of the user (List of Course class)
    $coursesCompleted = $courseCompletedDAO->retrieveCourseCompletedByUserId($data['userid']);
    // Get a list of all the prerequisites of the user (List of Prerequisites class)
    $prerequisites = $prerequisiteDAO -> retrievePrerequisiteByCourse($data['course']);
    // Get a list of enrolled courses of the user (List of SectionStudent class)
    $enrolledClasses = $sectionStudentDAO->retrieveByID($data['userid']);

    //---------------------------------//
    // Check if user has bidded before //
    //---------------------------------//
    $bidList = $bidDAO->retrieveStudentBidsByCourse($data['userid'], $data['course']);
    $existSameCourseSameUser = !empty($bidList);

    $enrolledClassesWithInfo = $sectionStudentDAO->retrieveStudentEnrolledWithInfo($data['userid']);

#################
## Validations ##
#################

    //-------------//
    // Round Ended //
    //-------------//
    /*
        Student cannot bid as there is no Active Round
    */
    if ($bidRoundStatus->getStatus() != 'open') {
        $errors[] = 'round ended';
    }
    // if round is still open
    else {  
        //--------------//
        // Check UserID // 
        //--------------//
        /*
            The userid is not found in the system records
        */
        if (empty($student)){
            $errors[] = "invalid userid";
        }
        else {

        //------------------//
        // Check Bid Amount //
        //------------------//
        /*
            Amount must be positive number >= e$10.00 and not more than 2 decimal places
        */
        $invalidAmount = False;
        if(!is_numeric($data['amount']) || $data['amount'] < 10.0){       // check if edollar is not numerical value or less than e$10
            $errors[] = "invalid amount";
            $invalidAmount = True;
        }
        else {
            if ((intval($data['amount']) != $data['amount']) && (strlen($data['amount']) - strrpos($data['amount'], '.') - 1 > 2)) {    // check that the edollar is not more than 2 decimal places
                $errors[] = "invalid amount";
                $invalidAmount = True;
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



        //-----------------//
        // Insufficient e$ //
        //-----------------//
        /*
            Student has not enough e-dollars to place the bid. If it is an 
            update of a previous bid, account for the extra e$ gained back 
            from the cancellation of the previous bid first.
        */
        $temp = 0;
        if (!$invalidAmount){
            if ($existSameCourseSameUser)
                $studentEdollar += $bidList->getAmount();

            if (($studentEdollar - $data['amount']) < 0)
                $errors[] = 'insufficient e$';
        }

        if (!$existSameCourseSameUser){

        //-----------------------//
        // Class Timetable Clash //
        //-----------------------//
        /*
            The class timeslot for the section clashes with that 
            of a previously bidded section
        */
        if (!in_array("invalid section", $errors)){
            foreach ($studentBids as $bid) {
                if (isset($section) && ($bid['day'] == $section->getDay())  && ($bid['start'] == $section->getStart() || $bid['end'] == $section->getEnd() || 
                    ($section->getStart() < $bid['end'] && $section->getStart() > $bid['start']) || ($section->getEnd() < $bid['end'] && $section->getEnd() > $bid['start']))){
                $errors[] = "class timetable clash";
                }
            }

            if (!in_array("class timetable clash", $errors)){
                foreach($enrolledClassesWithInfo as $class) {
                    if (isset($section) && ($class['day'] == $section->getDay())  && ($class['start'] == $section->getStart() || $class['end'] == $section->getEnd() || 
                        ($section->getStart() < $class['end'] && $section->getStart() > $class['start']) || ($section->getEnd() < $class['end'] && $section->getEnd() > $class['start']))){
                    $errors[] = "class timetable clash";
                    }
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
                if (($bid['exam date'] == $course->getExamdate()) && ($bid['exam start'] == $course->getExamstart() || $bid['exam end'] == $course->getExamend() || 
                ($course->getExamstart() < $bid['exam end'] && $course->getExamstart() > $bid['exam start']) || ($course->getExamend() < $bid['exam end'] && $course->getExamend() > $bid['exam start']))){
                    $errors[] = "exam timetable clash";
                }
            }

            if (!in_array("exam timetable clash", $errors)){
                foreach($enrolledClassesWithInfo as $class) {
                    if (($class['exam date'] == $course->getExamdate()) && ($class['exam start'] == $course->getExamstart() || $class['exam end'] == $course->getExamend() || 
                    ($course->getExamstart() < $class['exam end'] && $course->getExamstart() > $class['exam start']) || ($course->getExamend() < $class['exam end'] && $course->getExamend() > $class['exam start']))){
                    $errors[] = "exam timetable clash";
                    }
                }
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
        foreach ($enrolledClasses as $sectionStudent){
            if ($sectionStudent->getCourse() == $data['course'] && $sectionStudent->getSection() == $data['section']){
                $errors[] = "course enrolled";
            }
        }

        //-----------------------//
        // Section Limit Reached // 
        //-----------------------//
        /*
            Student has already bidded for 5 sections. If it is an update of a 
            previous bid, account for the cancellation of the previous bid. 
        */
        $num = 0;
        foreach($studentBids as $bid) {
            if ($bid['userid'] == $data['userid'] && $bid['code'] != $data['course']) {   // Would not count if it's an update of previous bid.
                $num++;
            }
        }
        if ($num >= 5) {
            $errors[] = "section limit reached";
        }

        

        //-----------------------//
        // Not own School Course //
        //-----------------------//
        /*
            This only happens in round 1 where students are
            allowed to bid for modules from their own school.
        */
        if (isset($student) && isset($course) && $bidRoundStatus->getRound() == '1' && $student->getSchool() != $course->getSchool() ){ // if student bid is not from their own school
            $errors[] = "not own school course";  
        }
    }

        //------------//
        // No Vacancy //
        //------------//
        /*
            There is 0 vacancy for the section that the user is bidding.
        */  
        $enrolledStudents = $sectionStudentDAO->retrieveByCourseSection($data['course'], $data['section']);
        if (!empty($section)){
            if (sizeof($enrolledStudents) == $section->getSize()){
                $errors[] = "no vacancy";
            }
        }
    }

     

        } // if round is still open
    } 
    // If request is not found
    else {
        $errors[] = 'no request'; 
    }
sort($errors);
if (sizeof($errors)==0){
    

    // The amount must be more than the minimum bid (only applicable for round 2)
    if ($bidRoundStatus->getRound() == 2 && $data['amount'] < $sectionDAO->retrieveMinBid($data['course'], $data['section'])){
        $errors[] = "bid too low";
        $result = [
            "status" => "error",
            "message" => $errors
        ];
        doRoundTwo();
        header('Content-Type: application/json');
        echo json_encode($result, JSON_PRETTY_PRINT);    
    } 
    else {
        if (!$existSameCourseSameUser){    
            $bidDAO->add($data['userid'], $data['amount'], $data['course'], $data['section']);
            $studentDAO->updateEDollar($data['userid'], $student->getEdollar() - $data['amount']);
            if ($section->getVacancy() > 0)
                $sectionDAO->updateVacancy($data['course'], $data['section'], $section->getVacancy() - 1);   
        }
        else{
            $bidDAO->updateBid($data['userid'], $data['amount'], $data['course'], $data['section']);
            $studentDAO->updateEDollar($data['userid'], $student->getEdollar() + $bidList->getAmount() - $data['amount']);
            $sectionDAO->updateVacancy($data['course'], $bidList->getSection(), $section->getVacancy() + 1);
            $sectionDAO->updateVacancy($data['course'], $data['section'], $section->getVacancy() - 1);
        }
        doRoundTwo();
        $result = [
            "status" => "success"
        ];
        header('Content-Type: application/json');
        echo json_encode($result, JSON_PRETTY_PRINT);
    }
}
else {
    $result = [
        "status" => "error",
        "message" => $errors
    ];
    header('Content-Type: application/json');
    echo json_encode($result, JSON_PRETTY_PRINT);
}






?>