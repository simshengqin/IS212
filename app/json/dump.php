<?php
require_once '../include/common.php';
try {
    $result = [
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
        $result["course"][] = $temp;
    }
    
    $result["course"] = $sortclass->sort_it($result["course"], "course");         // Sort course


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
        $result["student"][] = $temp;
    }

    $result["student"] = $sortclass->sort_it($result["student"], "student");      // Sort student


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
        $result["section"][] = $temp;
    }

    $result["section"] = $sortclass->sort_it($result["section"], "section");      // Sort section


######################
## PrerequisiteList ##
######################

    $prerequisiteList = $prerequisiteDAO->retrieveAll();
    foreach ($prerequisiteList as $prerequisite) {                                
        $temp = [];
        $temp["course"] = $prerequisite->getCourse();
        $temp["prerequisite"] = $prerequisite->getPrerequisite();
        $result["prerequisite"][] = $temp;
    }

    $result["prerequisite"] = $sortclass->sort_it($result["prerequisite"], "prerequisite");      // Sort prerequisite
                                         
    
#########################
## CourseCompletedList ##
#########################

    $courseCompletedList = $courseCompletedDAO->retrieveAll();                          
    foreach ($courseCompletedList as $courseCompleted) {                                
        $temp = [];
        $temp["userid"] = $courseCompleted->getUserid();
        $temp["course"] = $courseCompleted->getCourse();
        
        $result["course_completed"][] = $temp;
    }

    $result["course_completed"] = $sortclass->sort_it($result["course_completed"], "course_completed");      // Sort course_completed


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
        $result["bid"][] = $temp;
    }

    $result["bid"] = $sortclass->sort_it($result["bid"], "bid");      // Sort prerequisite

    header('Content-Type: application/json');
    echo json_encode($result, JSON_PRETTY_PRINT);
}

catch (Exception $e) {                  // if there's any error from dumping table
    $result = [
        "status" => "error" 
    ];
    header('Content-Type: application/json');
    echo json_encode($result, JSON_PRETTY_PRINT);
}


?>