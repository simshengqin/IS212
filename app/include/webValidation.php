<?php 

function commmonValidation($data, $row, $header, $file){
    $message = [];
    $error = [];
    for ($i=0; $i<count($data); $i++) {     //loop through all the columns and add it to the error message if any of the column is empty.
        if ($data[$i] == "") 
        {
            $message[] = "blank {$header[$i]}";  
        }
        
    }
    if (sizeof($message)!=0) {  // if there is/are error(s) in $message, add filename and row
        $error['file'] = $file;
        $error['line'] = $row;
        $error['message'] = $message;
    }
    return $error;
}

function validateBid($bid_data, $row, $allStudentInfo, $allCourseInfo, $sectionsInfo){
    
    // Retrieve necessary data for validation
    $error = [];
    $message = [];
    $userid = $bid_data[0];
    $bidAmount = $bid_data[1];
    $bidCode = $bid_data[2];
    $bidSection = $bid_data[3];
    $studentList = [];
    $bidDAO = new BidDAO();
    $bidStatusDAO = new BidStatusDAO();
    $bidStatus = $bidStatusDAO->getBidStatus();
    $bidInfo = $bidDAO->retrieveStudentBidsWithInfo($userid);  // Retrieve INNER JOIN table of bid and section and course
    $courseCompletedDAO = new CourseCompletedDAO();
    $sectionStudentDAO = new SectionStudentDAO();
    $sectionDAO = new SectionDAO();
    $enrolledStudent = $sectionStudentDAO->retrieveByCourseUserID($bidCode, $userid);
    $sectionInfo = $sectionDAO->retrieveSectionByCourse($bidCode,$bidSection);
    $enrolledClasses = $sectionStudentDAO->retrieveStudentEnrolledWithInfo($userid);
    

    // UserID Validation 
    $useridList = [];
    foreach($allStudentInfo as $val){
        if ($userid == $val->getUserid())           
            $student = $val;                        // Retrieve 'student' class for logic validation 
        $useridList[] = $val->getUserid();          // store all userid in $useridList
    }
    if (!in_array($userid, $useridList)){                 // Check if inputted user id exist in current student database
        $message[] = "Course: $bidCode Section: $bidSection<br>Error: Invalid userid";
    }

    // Bid Amount Validation
    if(!is_numeric($bidAmount) || $bidAmount < 10.0){                // check if edollar is not numerical value or less than e$10
        $message[] = "Course: $bidCode Section: $bidSection<br>Error: Invalid amount";
    }
    else {
        if ((intval($bidAmount) != $bidAmount) && (strlen($bidAmount) - strrpos($bidAmount, '.') - 1 > 2)) {    // check that the edollar is not more than 2 decimal places
            $message[] = "Course: $bidCode Section: $bidSection<br>Error: Invalid amount";
        }
    }

    // Course Validation
    $courseList = [];
    foreach($allCourseInfo as $val){
        if ($bidCode == $val->getCourse())
            $course = $val;                         // Retrieve 'course' class for logic validation 
        $courseList[] = $val->getCourse();          // Store all course in $courseList
    }
    if (in_array($bidCode, $courseList)){                 // Check if inputted course exist in current course database
    //     $message[] = "invalid course";                           
    // }
    // else {                                                
        // Section Validation                              // Check ONLY if course validation is valid
        $sectionList = [];
        foreach($sectionsInfo as $val){
            if ($bidSection == $val->getSection())
                $section = $val;                         // Retrieve 'section' class for logic validation 
            $sectionList[] = $val->getSection();        // Store all section filtered by course in $sectionList
        }
        if (!in_array($bidSection, $sectionList)){         // Check if bidSection is in section list (after filtered with course)
            $message[] = "Course: $bidCode Section: $bidSection<br>Error: Invalid section";  
        }
    }

    $bidList = $bidDAO->retrieveStudentBidsByCourse($userid, $bidCode);
    if (!empty($bidList) && (sizeof($message)==0))
        return $message;

    //------------------//
    // Logic Validation //
    //------------------//
                                                        
    if (isset($student) && isset($course) && $bidStatus->getRound() == '1' && $student->getSchool() != $course->getSchool() ){ // if student bid is not from their own school
        $message[] = "Course: $bidCode Section: $bidSection<br>Error: Not own school course";  
    }
    
    //---------------------------------//
    // Check for class timetable clash //
    //---------------------------------//
    foreach ($bidInfo as $bid) {
        if (isset($section) && ($bid['day'] == $section->getDay())  && ($bid['start'] == $section->getStart() || $bid['end'] == $section->getEnd() || 
                ($section->getStart() < $bid['end'] && $section->getStart() > $bid['start']) || ($section->getEnd() < $bid['end'] && $section->getEnd() > $bid['start']))){
            $message[] = "Course: $bidCode Section: $bidSection<br>Error: Class Timetable Clash";
        }
    }
    
    if (!in_array("Course: $bidCode Section: $bidSection<br>Error: Class Timetable Clash", $message)){
        foreach($enrolledClasses as $class) {
            if (isset($section) && ($class['day'] == $section->getDay())  && ($class['start'] == $section->getStart() || $class['end'] == $section->getEnd() || 
                ($section->getStart() < $class['end'] && $section->getStart() > $class['start']) || ($section->getEnd() < $class['end'] && $section->getEnd() > $class['start']))){
            $message[] = "Course: $bidCode Section: $bidSection<br>Error: Class Timetable Clash";
            }
        }
    }


    //--------------------------------//
    // Check for exam timetable clash //
    //--------------------------------//
    foreach ($bidInfo as $bid) {
        if (($bid['exam date'] == $course->getExamdate()) && ($bid['exam start'] == $course->getExamstart() || $bid['exam end'] == $course->getExamend() || 
            ($course->getExamstart() < $bid['exam end'] && $course->getExamstart() > $bid['exam start']) || ($course->getExamend() < $bid['exam end'] && $course->getExamend() > $bid['exam start']))){
            $message[] = "Course: $bidCode Section: $bidSection<br>Error: Exam Timetable Clash";
        }
    }

    if (!in_array("Course: $bidCode Section: $bidSection<br>Error: Exam Timetable Clash", $message)){
        foreach($enrolledClasses as $class) {
            if (($class['exam date'] == $course->getExamdate()) && ($class['exam start'] == $course->getExamstart() || $class['exam end'] == $course->getExamend() || 
            ($course->getExamstart() < $class['exam end'] && $course->getExamstart() > $class['exam start']) || ($course->getExamend() < $class['exam end'] && $course->getExamend() > $class['exam start']))){
            $message[] = "Course: $bidCode Section: $bidSection<br>Error: Exam Timetable Clash";
            }
        }
    }



    //------------------------------------//
    // Check for incomplete prerequisites //
    //------------------------------------//
    $coursesCompleted = $courseCompletedDAO->retrieveCourseCompletedByUserId($userid);
    $prerequisiteDAO = new PrerequisiteDAO();
    $prerequisites = $prerequisiteDAO -> retrievePrerequisiteByCourse($bidCode);
    if (!empty($prerequisites)){                    // If there is prerequisites
        if (array_diff($prerequisites, $coursesCompleted)) {
            $message[] = "Course: $bidCode Section: $bidSection<br>Error: Incomplete Prerequisities";
        }
    }

    //----------------------------//
    // Check for course completed //
    //----------------------------//
    if (in_array($bidCode,$coursesCompleted)) {
        $message[] = "Course: $bidCode Section: $bidSection<br>Error: Course Completed";
    }
    


    //---------------------------------------------------------------//
    // Check for Section limit (Student can only bid for 5 sections) //
    //---------------------------------------------------------------//
    $num = 0;
    foreach($bidInfo as $bid) {
        if ($bid['userid'] == $userid) {
            $num++;
        }
    }
    if ($num >= 5) {
        $message[] = "Course: $bidCode Section: $bidSection<br>Error: Section Limit Reached";
    }

    if (!empty($enrolledStudent))
        $message[] = "Course: $bidCode Section: $bidSection <br>Error: You have enrolled in this course";

    if ($bidAmount < $sectionInfo->getMinbid()){
        $message[] = "Course: $bidCode Section: $bidSection <br>Error: Please bid higher than the minimum bid";
    }
    // if (sizeof($message) == 0) {
    //     $bidDAO->add($bid_data[0], $bid_data[1], $bid_data[2], $bid_data[3]);
    
    //     // Check if student has enough e-dollars 
    //     $studentDAO = new StudentDAO();
    //     $student = $studentDAO->retrieveStudent($userid);
    //     if($bidAmount <= $student->getEdollar()){                           
    //         $eDollar = $student->getEdollar()-$bidAmount;   
    //         foreach($bidInfo as $bid) {    
    
    if (sizeof($message)!=0) {  // if there is/are error(s) in $message, add filename and row
        $error['file'] = 'bid.csv';
        $error['line'] = $row;
        $error['message'] = $message;
    }
    return $error;
    
}





?>