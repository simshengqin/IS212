<?php
require_once '../include/common.php';
require_once '../include/protect_json.php';

try {
    $result = [
        "status" => "success",
        "course" => [],
        "section" => [],
        "student" => [],
        "prerequisite" => [],
        "bid" => [],
        "completed-course" => [],
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
    $sectionStudentDAO = new SectionStudentDAO();
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
        $temp["exam start"] = DateTime::createFromFormat('H:i:s',$course->getExamstart())->format('Gi'); 
        $temp["exam end"] = DateTime::createFromFormat('H:i:s',$course->getExamend())->format('Gi'); 
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
        $temp["edollar"] = (float) $student->getEdollar();
        $result["student"][] = $temp;
    }

    $result["student"] = $sortclass->sort_it($result["student"], "student");      // Sort student


#################
## SectionList ##
#################
    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']; 

    $sectionList = $sectionDAO->retrieveAll();                                    
    foreach ($sectionList as $section) {                                          
        $temp = [];
        $temp["course"] = $section->getCourse();
        $temp["section"] = $section->getSection();
        $temp["day"] = $days[$section->getDay()-1];
        $temp["start"] = DateTime::createFromFormat('H:i:s',$section->getStart())->format('Gi'); 
        $temp["end"] = DateTime::createFromFormat('H:i:s',$section->getEnd())->format('Gi'); 
        $temp["instructor"] = $section->getInstructor();
        $temp["venue"] = $section->getVenue();
        $temp["size"] = (int) $section->getSize();
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
        
        $result["completed-course"][] = $temp;
    }

    $result["completed-course"] = $sortclass->sort_it($result["completed-course"], "course_completed");      // Sort completed-course

####################
## SectionStudent ##
####################

    $sectionStudentList = $sectionStudentDAO->retrieveAll();                          
    foreach ($sectionStudentList as $sectionStudent) {                                
        $temp = [];
        $temp["userid"] = $sectionStudent->getUserid();
        $temp["course"] = $sectionStudent->getCourse();
        $temp["section"] = $sectionStudent->getSection();
        $temp["amount"] = (float) $sectionStudent->getAmount();
        $result["section-student"][] = $temp;
    }

    $result["section-student"] = $sortclass->sort_it($result["section-student"], "section_student");

#############
## BidList ##
#############

    $bidList = $bidDAO->retrieveAll();                                                  
    foreach ($bidList as $bid) {                                                        
        $temp = [];
        $temp["userid"] = $bid->getUserid();
        $temp["amount"] = (float) $bid->getAmount();
        $temp["course"] = $bid->getCode();
        $temp["section"] = $bid->getSection();
        $result["bid"][] = $temp;
    }

    $result["bid"] = $sortclass->sort_it($result["bid"], "bid");      // Sort prerequisite

    header('Content-Type: application/json');
    $result = json_encode($result, JSON_PRETTY_PRINT | JSON_PRESERVE_ZERO_FRACTION); // JSON_PRESERVE_ZERO_FRACTION to preserve float value

    echo $result; 
}

catch (Exception $e) {                  // if there's any error from dumping table
    $result = [
        "status" => "error" 
    ];
    header('Content-Type: application/json');
    echo json_encode($result, JSON_PRETTY_PRINT );
}

?>