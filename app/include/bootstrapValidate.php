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

function validateCourse($course_data, $row){

    // Retrieve necessary data for validation
    $message = [];
    $error = [];
    $title = $course_data[2];
    $description = $course_data[3];
    $exam_date = $course_data[4];
    $exam_start = $course_data[5];
    $exam_end = $course_data[6];

    // Validation for Exam Date 
    $exam_date_year = substr($exam_date, 0, 4);      // seperate exam_date into substrings to validate the date
    $exam_date_month = substr($exam_date, 4, 2);
    $exam_date_day = substr($exam_date, 6, 2);
    if (!checkdate($exam_date_month, $exam_date_day, $exam_date_year)){
        $message[] = "invalid exam date";
    }


    // Validation for Exam Time (exam start and end)
    $exam_start_timing = [
        '8:30' => 0, 
        '12:00' => 1, 
        '15:30' => 2
    ];
    $exam_end_timing = [
        '11:45' => 0, 
        '15:15' => 1, 
        '18:45' => 2
    ];

    // Validation for Exam Start
    if (!array_key_exists($exam_start,$exam_start_timing)){  // Check if exam start is 8:30, 12:00, or 15:30
        $message[] = "invalid exam start";          // return error if not
    }

    // Validation for Exam End
    if (!array_key_exists($exam_end, $exam_end_timing)){     // Check if exam end is 11:45, 15:15, or 18:45
        $message[] = "invalid exam end";            // return error if not
    }
    elseif (array_key_exists($exam_start,$exam_start_timing)){   // To prevent exam start 'key' not found where comparing timing
        if ($exam_start_timing[$exam_start] > $exam_end_timing[$exam_end])
            $message[] = "invalid exam end";
    }

    // Validation for Title
    if(strlen($title) > 100){
        $message[] = "invalid title";
    } 

    // Validation for Description
    if(strlen($description) > 1000){
        $message[] = "invalid description";
    } 

    if (sizeof($message)!=0) {  // if there is/are error(s) in $message, add filename and row
        $error['file'] = 'course.csv';
        $error['line'] = $row;
        $error['message'] = $message;
    }

    return $error;
}

function validateSection($section_data, $row, $allCourseInfo){

    // Retrieve necessary data for validation
    $error = [];
    $message = [];
    $course = $section_data[0];
    $section = $section_data[1];
    $section_day = $section_data[2]; 
    $section_start = $section_data[3];
    $section_end = $section_data[4];
    $instructor = $section_data[5];
    $venue = $section_data[6];
    $size = $section_data[7]; 

    // Course Validation (Check if Course Exist)

    $course_list = [];                              
    foreach ($allCourseInfo as $val){
        $course_list[] = $val->getCourse();         // Store all course code into one array
    }
    if (!in_array($course, $course_list)){          // Check if inputted course exist in current course database
        $message[] = "invalid course";
    }

    // Section Validation (Only check if Course is valid)
    if (in_array($course, $course_list)){
        if (!preg_match("/^[S](\d?[1-9]|[1-9]0)$/", $section)){       // Check if first character have a 'S' followed by 1-99
            $message[] = "invalid section";
        }
    }

    // Section Day Validation (check between 1 to 7)
    if($section_day > 7 || $section_day < 1){
        $message[] = "invalid day";        // Check day value between 1 to 7  
    }

    // Section Start  Validation 
    $section_start_timing = [
        '8:30' => 0, 
        '12:00' => 1, 
        '15:30' => 2
    ];
    $section_end_timing = [
        '11:45' => 0, 
        '15:15' => 1, 
        '18:45' => 2
    ];

    // Validation for Section Start
    if (!array_key_exists($section_start,$section_start_timing)){  // Check if section start is 8:30, 12:00, or 15:30
        $message[] = "invalid start";                     // return error if not
    }

    // Validation for Section End
    if (!array_key_exists($section_end, $section_end_timing)){     // Check if section end is 11:45, 15:15, or 18:45
        $message[] = "invalid end";                       // return error if not
    }
    elseif (array_key_exists($section_start,$section_start_timing)){     // To prevent section start 'key' not found where comparing timing
        if ($section_start_timing[$section_start] > $section_end_timing[$section_end])
            $message[] = "invalid end";
    }

    // Section instructor Validation 
    if (strlen($instructor) > 100){
        $message[] = "invalid instructor";
    } 

    // Section venue Validation 
    if (strlen($venue) > 100){
        $message[] =  "invalid venue";
    } 

    // Section size Validation 
    if ($size < 1){
        $message[] = "invalid size";
    }

    if (sizeof($message)!=0) {  // if there is/are error(s) in $message, add filename and row
        $error['file'] = 'section.csv';
        $error['line'] = $row;
        $error['message'] = $message;
    }

    return $error;
}

function validateStudent($student_data, $row, $allStudentInfo){

    // Retrieve necessary data for validation
    $error = [];
    $message = [];
    $userid = $student_data[0];
    $password = $student_data[1];
    $name = $student_data[2]; 
    $school = $student_data[3];
    $edollar = $student_data[4];


    // Student userid and duplicate userid Validation 
    if (strlen($userid) > 128){
        $message[] = "invalid userid";
    }
    else{
        $useridList = [];
        foreach ($allStudentInfo as $val){      // retrieve all userid from student info 
            $useridList[] = $val->getUserid(); 
        }
        if (in_array($userid, $useridList)){   // if userid within list, output error
            $message[] = "duplicate userid";
        }
    }

    // E-dollar Validation
    if($edollar < 0.0 || !is_numeric($edollar)){                // check if edollar is not negative or not numerical value
        $message[] = "invalid e-dollar";
    }
    else {
        if ((intval($edollar) != $edollar) && (strlen($edollar) - strrpos($edollar, '.') - 1 > 2)) {    // check that the edollar is not more than 2 decimal places
            $message[] = "invalid e-dollar";
        }
    }

    // Password Validation
    if (strlen($password) > 128){
        $message[] = "invalid password";
    }

    // Invalid Name
    if (strlen($name) > 100){
        $message[] = "invalid name";
    }

    if (sizeof($message)!=0) {  // if there is/are error(s) in $message, add filename and row
        $error['file'] = 'student.csv';
        $error['line'] = $row;
        $error['message'] = $message;
    }
    return $error;
}

function validatePrerequisite($prerequisite_data, $row, $allCourseInfo){

    // Retrieve necessary data for validation
    $error = [];
    $message = [];
    $course = $prerequisite_data[0];
    $prerequisite = $prerequisite_data[1];

    $courseCodeList = [];
    foreach($allCourseInfo as $val)
    {
        $courseCodeList[] = $val->getCourse();
    }

    // Course code validation
    if (!in_array($course, $courseCodeList)){                 // Check if inputted course exist in current course database
        $message[] = "invalid course";
    }

    // Prerequisite code validation
    if (!in_array($prerequisite, $courseCodeList)){          // Check if inputted Prerequisite exist in current course database
        $message[] = "invalid prerequisite";
    }

    if (sizeof($message)!=0) {  // if there is/are error(s) in $message, add filename and row
        $error['file'] = 'prerequisite.csv';
        $error['line'] = $row;
        $error['message'] = $message;
    }

    return $error;
}

function validateCourseCompleted($courseCompletedData, $row, $allCourseInfo, $allStudentInfo, $allPrerequisiteInfo){
    
    // Retrieve necessary data for validation
    $error = [];
    $message = [];
    $userid = $courseCompletedData[0];
    $code = $courseCompletedData[1];

    // User Id Validation
    $useridList = [];
    foreach($allStudentInfo as $val){
        $useridList[] = $val->getUserid();
    }

    if (!in_array($userid, $useridList)){                 // Check if inputted user id exist in current student database
        $message[] = "invalid userid";
    }

    // Course Validation
    $courseList = [];
    foreach($allCourseInfo as $val){
        $courseList[] = $val->getCourse();
    }

    if (!in_array($code, $courseList)){                 // Check if inputted course exist in current course database
        $message[] = "invalid course";
    }

    // Logic Validation
    $prerequisiteCourse = [];
    foreach ($allPrerequisiteInfo as $val){             
        if ($code == $val->getCourse()){                // Check if there's any prerequisite for the course
            $prerequisiteCourse[] = $val->getPrerequisite();          // Store prerequisites in $prerequisiteCourse
        }
    }

    if (!empty($prerequisiteCourse)){                                                           // Do this if prerequisite is not empty 
        $courseCompletedDAO = new CourseCompletedDAO();       
        $courseCompletedList = $courseCompletedDAO->retrieveCourseCompletedByUserId($userid);   // Get list of course completed by user
        $check = 0;
        foreach ($prerequisiteCourse as $precourse){
            if (!in_array($precourse, $courseCompletedList) && $check == 0){            // if prerequisite not found in course completed, output error
                $message[] = "invalid course completed";
                $check = 1;
            }
        }

    }

    if (sizeof($message)!=0) {  // if there is/are error(s) in $message, add filename and row
        $error['file'] = 'course_completed.csv';
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
    $bidInfo = $bidDAO->retrieveStudentBidsWithInfo($userid);  // Retrieve INNER JOIN table of bid and section and course
    $courseCompletedDAO = new CourseCompletedDAO();
    // UserID Validation 
    $useridList = [];
    foreach($allStudentInfo as $val){
        if ($userid == $val->getUserid())           
            $student = $val;                        // Retrieve 'student' class for logic validation 
        $useridList[] = $val->getUserid();          // store all userid in $useridList
    }
    if (!in_array($userid, $useridList)){                 // Check if inputted user id exist in current student database
        $message[] = "invalid userid";
    }

    // Bid Amount Validation
    if(!is_numeric($bidAmount) || $bidAmount < 10.0){                // check if edollar is not numerical value or less than e$10
        $message[] = "invalid amount";
    }
    else {
        if ((intval($bidAmount) != $bidAmount) && (strlen($bidAmount) - strrpos($bidAmount, '.') - 1 > 2)) {    // check that the edollar is not more than 2 decimal places
            $message[] = "invalid amount";
        }
    }

    // Course Validation
    $courseList = [];
    foreach($allCourseInfo as $val){
        if ($bidCode == $val->getCourse())
            $course = $val;                         // Retrieve 'course' class for logic validation 
        $courseList[] = $val->getCourse();          // Store all course in $courseList
    }
    if (!in_array($bidCode, $courseList)){                 // Check if inputted course exist in current course database
        $message[] = "invalid course";                           
    }
    else {                                                
        // Section Validation                              // Check ONLY if course validation is valid
        $sectionList = [];
        foreach($sectionsInfo as $val){
            if ($bidSection == $val->getSection())
                $section = $val;                         // Retrieve 'section' class for logic validation 
            $sectionList[] = $val->getSection();        // Store all section filtered by course in $sectionList
        }
        if (!in_array($bidSection, $sectionList)){         // Check if bidSection is in section list (after filtered with course)
            $message[] = "invalid section";  
        }
    }


    // Logic Validation
                                                                ### Not yet implemented bid round checking ###
    if (isset($student) && isset($course) && $student->getSchool() != $course->getSchool()){         // if student bid is not from their own school
        $message[] = "not own school course";  
    }

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

    // Check for class timetable clash 
    foreach ($bidInfo as $bid) {
        if (isset($section) && ($bid['day'] == $section->getDay())  && ($start_timing[$bid['start']] == $start_timing[$section->getStart()] || $end_timing[$bid['end']] == $end_timing[$section->getEnd()])){
            $message[] = "class timetable clash";
        }
    }

    // Check for exam timetable clash
    foreach ($bidInfo as $bid) {
        if (($bid['examdate'] == $course->getExamdate())  && ($start_timing[$bid['examstart']] == $start_timing[$course->getExamstart()] || $end_timing[$bid['examend']] == $end_timing[$course->getExamend()])){
            $message[] = "exam timetable clash";
        }
    }

    // Check for incomplete prerequisites 
    $coursesCompleted = $courseCompletedDAO->retrieveCourseCompletedByUserId($userid);
    $prerequisiteDAO = new PrerequisiteDAO();
    $prerequisites = $prerequisiteDAO -> retrievePrerequisiteByCourse($bidCode);
    if (!empty($prerequisites)){                    // If there is prerequisites
        if (array_diff($prerequisites, $coursesCompleted)) {
            $message[] = "incomplete prerequisites";
        }
    }

    // Check for course completed 
    if (in_array($bidCode,$coursesCompleted)) {
        $message[] = "course completed";
    }

    // Check for Section limit (Student can only bid for 5 sections)
    $num = 0;
    foreach($bidInfo as $bid) {
        if ($bid['userid'] == $userid) {
            $num++;
        }
    }
    if ($num >= 5) {
        $message[] = "section limit reached";
    }

    // if (sizeof($message) == 0) {
    //     $bidDAO->add($bid_data[0], $bid_data[1], $bid_data[2], $bid_data[3]);
    
    //     // Check if student has enough e-dollars 
    //     $studentDAO = new StudentDAO();
    //     $student = $studentDAO->retrieveStudent($userid);
    //     if($bidAmount <= $student->getEdollar()){                           
    //         $eDollar = $student->getEdollar()-$bidAmount;   
    //         foreach($bidInfo as $bid) {    
    //             if ($bid['userid'] == $userid && $bid['code'] == $bidCode && $bid['section'] != $bidSection) {
    //                 $bidDAO->removeBidByUseridAndCode($userid,$bidCode);
    //                 $eDollar+=$bid['amount'];
    //             }
    //         }
    //         $studentDAO-> updateEDollar($userid,$eDollar);
    //     } else{
    //         $message[] = "not enough e-dollar";     
    //     }
    //     $bidDAO->removeBid($bid_data[0], $bid_data[1], $bid_data[2], $bid_data[3]);
    // }
    
    if (sizeof($message)!=0) {  // if there is/are error(s) in $message, add filename and row
        $error['file'] = 'bid.csv';
        $error['line'] = $row;
        $error['message'] = $message;
    }
    return $error;
    
}





?>