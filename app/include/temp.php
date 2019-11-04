<?php

## For displaying different type of messages for the web UI ##

function validateBid($bid_data, $row, $allStudentInfo, $allCourseInfo, $bidSectionsInfo){
    
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
    $bidCodeCompletedDAO = new CourseCompletedDAO();
    // UserID Validation 
    $useridList = [];
    foreach($allStudentInfo as $val){
        if ($userid == $val->getUserid())           
            $student = $val;                        // Retrieve 'student' class for logic validation 
        $useridList[] = $val->getUserid();          // store all userid in $useridList
    }
    if (!in_array($userid, $useridList)){                 // Check if inputted user id exist in current student database
        $message[] = "Course: $bidCode Section: $bidSection Error: invalid userid";
    }

    // Bid Amount Validation
    if(!is_numeric($bidAmount) || $bidAmount < 10.0){                // check if edollar is not numerical value or less than e$10
        $message[] = "Course: $bidCode Section: $bidSection Error: invalid amount";
    }
    else {
        if ((intval($bidAmount) != $bidAmount) && (strlen($bidAmount) - strrpos($bidAmount, '.') - 1 > 2)) {    // check that the edollar is not more than 2 decimal places
            $message[] = "Course: $bidCode Section: $bidSection Error: invalid amount";
        }
    }

    // Course Validation
    $bidCodeList = [];
    foreach($allCourseInfo as $val){
        if ($bidCode == $val->getCourse())
            $bidCode = $val;                         // Retrieve 'course' class for logic validation 
        $bidCodeList[] = $val->getCourse();          // Store all course in $bidCodeList
    }
    if (!in_array($bidCode, $bidCodeList)){ 
        var_dump($bidCode);
        var_dump($bidSection);
        $course = $bidCode->getCourse();
        var_dump($course);
        
        $message[] = "Course: " . $course.  " Section: ". $bidSection . " Error: invalid course";  
        var_dump($message);                         
    }
    else {                                                
        // Section Validation                              // Check ONLY if course validation is valid
        $bidSectionList = [];
        foreach($bidSectionsInfo as $val){
            if ($bidSection == $val->getSection())
                $bidSection = $val;                         // Retrieve 'section' class for logic validation 
            $bidSectionList[] = $val->getSection();        // Store all section filtered by course in $bidSectionList
        }
        if (!in_array($bidSection, $bidSectionList)){         // Check if bidSection is in section list (after filtered with course)
            $message[] = "Course: $bidCode Section: $bidSection Error: invalid section";  
        }
    }



    // Logic Validation
    $bidStatusDAO = new BidStatusDAO();
    $bidStatus = $bidStatusDAO->getBidStatus();

    // if student bid is not from their own school
    if (isset($student) && isset($bidCode) && $student->getSchool() != $bidCode->getSchool() && $bidStatus->getRound() == 1){  
        $message[] = "Course: $bidCode Section: $bidSection Error: Can only bid from same school during Round 1";  
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
        if (isset($bidSection) && ($bid['day'] == $bidSection->getDay())  && ($start_timing[$bid['start']] == $start_timing[$bidSection->getStart()] || $end_timing[$bid['end']] == $end_timing[$bidSection->getEnd()])){
            echo $bid['day'];
            $message[] = "Course: $bidCode Section: $bidSection Error: You have a class timetable clash";
        }
    }

    // Check for exam timetable clash
    foreach ($bidInfo as $bid) {
        if (($bid['exam date'] == $bidCode->getExamdate())  && ($start_timing[$bid['exam start']] == $start_timing[$bidCode->getExamstart()] || $end_timing[$bid['exam end']] == $end_timing[$bidCode->getExamend()])){
            $message[] = "Course: $bidCode Section: $bidSection Error: You have a exam timetable clash";
        }
    }

    // Check for incomplete prerequisites 
    $bidCodesCompleted = $bidCodeCompletedDAO->retrieveCourseCompletedByUserId($userid);
    $prerequisiteDAO = new PrerequisiteDAO();
    $prerequisites = $prerequisiteDAO -> retrievePrerequisiteByCourse($course);
    if (!empty($prerequisites)){                    // If there is prerequisites
        if (array_diff($prerequisites, $bidCodesCompleted)) {
            $message[] = "Course: $bidCode Section: $bidSection Error: You have incomplete prerequisites";
        }
    }

    // Check for course completed 
    if (in_array($bidCode,$bidCodesCompleted)) {
        $message[] = "Course: $bidCode Section: $bidSection Error: The course is completed already";
    }

    // Check for Section limit (Student can only bid for 5 sections)
    $num = 0;
    foreach($bidInfo as $bid) {
        if ($bid['userid'] == $userid) {
            $num++;
        }
    }
    if ($num >= 5) {
        $message[] = "Course: $bidCode Section: $bidSection Error: Section limit reached";
    }
    

    return $message;
}

?>