<?php 

function commmonValidation($data, $row){


}

function validateCourse($course_data, $row){
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
        $errors[] = "row $row: invalid exam date";
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
        $errors[] = "row $row: invalid exam start";          // return error if not
    }

    // Validation for Exam End
    if (!array_key_exists($exam_end, $exam_end_timing)){     // Check if exam end is 11:45, 15:15, or 18:45
        $errors[] = "row $row: invalid exam end";            // return error if not
    }
    elseif (array_key_exists($exam_start,$exam_start_timing)){   // To prevent exam start 'key' not found where comparing timing
        if ($exam_start_timing[$exam_start] > $exam_end_timing[$exam_end])
            $errors[] = "row $row: invalid exam end";
    }

    // Validation for Title
    if(strlen($title) > 100){
        $errors[] = "row $row: invalid title";
    } 

    // Validation for Description
    if(strlen($description) > 1000){
        $errors[] = "row $row: invalid description";
    } 




    return $errors;
}

?>