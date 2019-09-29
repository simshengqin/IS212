<?php 

function commmonValidation($data, $row){


}

function validateCourse($course_data, $row){

    // Retrieve necessary data for validation
    $errors = [];
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
        $errors["row: $row"] = "invalid exam date";
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
        $errors["row: $row"] = "invalid exam start";          // return error if not
    }

    // Validation for Exam End
    if (!array_key_exists($exam_end, $exam_end_timing)){     // Check if exam end is 11:45, 15:15, or 18:45
        $errors["row: $row"] = "invalid exam end";            // return error if not
    }
    elseif (array_key_exists($exam_start,$exam_start_timing)){   // To prevent exam start 'key' not found where comparing timing
        if ($exam_start_timing[$exam_start] > $exam_end_timing[$exam_end])
            $errors["row: $row"] = "invalid exam end";
    }

    // Validation for Title
    if(strlen($title) > 100){
        $errors["row: $row"] = "invalid title";
    } 

    // Validation for Description
    if(strlen($description) > 1000){
        $errors["row: $row"] = "invalid description";
    } 
    return $errors;
}

function validateSection($section_data, $row , $allCoursesInfo){

    // Retrieve necessary data for validation
    $errors = [];
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
    foreach ($allCoursesInfo as $val){
        $course_list[] = $val->getCourse();         // Store all course code into one array
    }
    if (!in_array($course, $course_list)){          // Check if inputted course exist in current course database
        $errors["row: $row"] = "invalid course";
    }

    // Section Validation (Only check if Course is valid)
    if (in_array($course, $course_list)){
        if (!preg_match("/^[S](\d?[1-9]|[1-9]0)$/", $section)){       // Check if first character have a 'S' followed by 1-99
            $errors["row: $row"] = "invalid section";
        }
    }

    // Section Day Validation (check between 1 to 7)
    if($section_day > 7 || $section_day < 1){
        $errors["row: $row"] = "invalid day";        // Check day value between 1 to 7  
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
        $errors["row: $row"] = "invalid start";                     // return error if not
    }

    // Validation for Section End
    if (!array_key_exists($section_end, $section_end_timing)){     // Check if section end is 11:45, 15:15, or 18:45
        $errors["row: $row"] = "invalid end";                       // return error if not
    }
    elseif (array_key_exists($section_start,$section_start_timing)){     // To prevent section start 'key' not found where comparing timing
        if ($section_start_timing[$section_start] > $section_end_timing[$section_end])
            $errors["row: $row"] = "invalid end";
    }

    // Section instructor Validation 
    if (strlen($instructor) > 100){
        $errors["row: $row"] = "invalid instructor";
    } 

    // Section venue Validation 
    if (strlen($venue) > 100){
        $errors[] = "row $row: invalid venue";
    } 

    // Section size Validation 
    if ($size < 1){
        $errors["row: $row"] = "invalid size";
    }
    return $errors;
}

function validateStudent($student_data, $row, $allStudentInfo){

    // Retrieve necessary data for validation
    $errors = [];
    $userid = $student_data[0];
    $password = $student_data[1];
    $name = $student_data[2]; 
    $school = $student_data[3];
    $edollar = $student_data[4];


    // Student userid and duplicate userid Validation 
    if (strlen($userid) > 128){
        $errors["row: $row"][] = "invalid userid";
    }
    else{
        $useridList = [];
        foreach ($allStudentInfo as $val){      // retrieve all userid from student info 
            $useridList[] = $val->getUserid(); 
        }
        if (in_array($userid, $useridList)){   // if userid within list, output error
            $errors["row: $row"][] = "duplicate userid";
        }
    }

    // E-dollar Validation
    if($edollar < 0.0 || !is_numeric($edollar)){                // check if edollar is not negative or not numerical value
        $errors["row: $row"][] = "invalid e-dollar";
    }
    else {
        if (strlen($edollar) - strrpos($edollar, '.') - 1 > 2) {    // check that the edollar is not more than 2 decimal places
            $errors["row: $row"][] = "invalid e-dollar";
        }
    }

    // Password Validation
    if (strlen($password) > 128){
        $errors["row: $row"][] = "invalid password";
    }

    // Invalid Name
    if (strlen($name) > 100){
        $errors["row: $row"][] = "invalid name";
    }

    return $errors;
}

function validatePrerequisite($prerequisite_data, $row, $allCourseInfo){

    // Retrieve necessary data for validation
    $errors = [];
    $course = $prerequisite_data[0];
    $prerequisite = $prerequisite_data[1];

    $courseCodeList = [];
    foreach($allCourseInfo as $val)
    {
        $courseCodeList[] = $val->getCourse();
    }

    // Course code validation
    if (!in_array($course, $courseCodeList)){                 // Check if inputted course exist in current course database
        $errors["row: $row"] = "invalid course";
    }

    // Prerequisite code validation
    if (!in_array($prerequisite, $courseCodeList)){          // Check if inputted Prerequisite exist in current course database
        $errors["row: $row"] = "invalid prerequisite";
    }

    return $errors;
}




?>