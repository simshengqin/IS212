<?php
require_once '../include/common.php';
try {
    $output = [
        "status" => "success",
        "course" => [],
        "student" => [],
        "section" => [],
        "prerequisite" => [],
        "course_completed" => [],
        "bid" => [],
        "section-student" => []
    ];

########################
## DAO Initialization ##
########################

    $courseDAO = new CourseDAO();
    $studentDAO = new StudentDAO();
    $sectionDAO = new SectionDAO();
    $prerequisiteDAO = new PrerequisiteDAO();
    $courseCompletedDAO = new CourseCompletedDAO();
    $bidDAO = new BidDAO();
    // $sectionStudentDAO = new SectionStudentDAO();
    $sortclass = new Sort();


################
## CourseList ##
################

    $courseList = $courseDAO->retrieveAll();                                    
    foreach ($courseList as $course) {                                         
        $temp = [];
        $temp["course"] = $course->getCourse();
        $temp["school"] = $course->getSchool();
        $temp["title"] = $course->getTitle();
        $temp["description"] = $course->getDescription();
        $temp["exam date"] = $course->getExamdate();
        $temp["exam start"] = $course->getExamstart();
        $temp["exam end"] = $course->getExamend();
        $output["course"][] = $temp;
    }
    
    $output["course"] = $sortclass->sort_it($output["course"], "course");         // Sort course


#################
## StudentList ##   
#################

    $studentList = $studentDAO->retrieveAll();                                    
    foreach ($studentList as $student) {                                          
        $temp = [];
        $temp["userid"] = $student->getUserid();
        $temp["password"] = $student->getPassword();
        $temp["name"] = $student->getName();
        $temp["school"] = $student->getSchool();
        $temp["edollar"] = $student->getEdollar();
        $output["student"][] = $temp;
    }

    $output["student"] = $sortclass->sort_it($output["student"], "student");      // Sort student


#################
## SectionList ##
#################

    $sectionList = $sectionDAO->retrieveAll();                                    
    foreach ($sectionList as $section) {                                          
        $temp = [];
        $temp["course"] = $section->getCourse();
        $temp["section"] = $section->getSection();
        $temp["day"] = $section->getDay();
        $temp["start"] = $section->getStart();
        $temp["end"] = $section->getEnd();
        $temp["instructor"] = $section->getInstructor();
        $temp["venue"] = $section->getVenue();
        $temp["size"] = $section->getSize();
        $output["section"][] = $temp;
    }

    $output["section"] = $sortclass->sort_it($output["section"], "section");      // Sort section


######################
## PrerequisiteList ##
######################

    $prerequisiteList = $prerequisiteDAO->retrieveAll();
    foreach ($prerequisiteList as $prerequisite) {                                
        $temp = [];
        $temp["course"] = $prerequisite->getCourse();
        $temp["prerequisite"] = $prerequisite->getPrerequisite();
        $output["prerequisite"][] = $temp;
    }

    $output["prerequisite"] = $sortclass->sort_it($output["prerequisite"], "prerequisite");      // Sort prerequisite
                                         
    
#########################
## CourseCompletedList ##
#########################

    $courseCompletedList = $courseCompletedDAO->retrieveAll();                          
    foreach ($courseCompletedList as $courseCompleted) {                                
        $temp = [];
        $temp["userid"] = $courseCompleted->getUserid();
        $temp["course"] = $courseCompleted->getCourse();
        
        $output["course_completed"][] = $temp;
    }

    $output["course_completed"] = $sortclass->sort_it($output["course_completed"], "course_completed");      // Sort course_completed


#############
## BidList ##
#############

    $bidList = $bidDAO->retrieveAll();                                                  
    foreach ($bidList as $bid) {                                                        
        $temp = [];
        $temp["userid"] = $bid->getUserid();
        $temp["amount"] = $bid->getAmount();
        $temp["course"] = $bid->getCode();
        $temp["section"] = $bid->getSection();
        $output["bid"][] = $temp;
    }

    $output["bid"] = $sortclass->sort_it($output["bid"], "bid");      // Sort prerequisite

    header('Content-Type: application/json');
    echo json_encode($output, JSON_PRETTY_PRINT);
}

catch (Exception $e) {                  // if there's any error from dumping table
    $output = [
        "status" => "error" 
    ];
    header('Content-Type: application/json');
    echo json_encode($output, JSON_PRETTY_PRINT);
}


?>