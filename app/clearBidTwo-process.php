<?php

function doRoundTwo($convertsection = false){
    // Round 2 clearing takes place here. Only takes place once, 
    //Will not convert status from closed to cleared. Do it after this function!!
    $bidStatusDAO = new BidStatusDAO();
    $bidStatus = $bidStatusDAO->getBidStatus();
    $bidDAO = new BidDAO();
    $allBids = $bidDAO->retrieveAll();
    $sectionDAO = new SectionDAO();
    $bidDAO = new BidDAO();
    $sectionStudentDAO = new SectionStudentDAO();
    $studentDAO = new StudentDAO();
    foreach($allBids as $value)
    {
        $userid = $value->getUserid();
        $course = $value->getCode();
        $section = $value->getSection();
        $amount = $value->getAmount();
        $status = "pending";
        //Round 2 clearing logic. Real-time check of the min bid value. If the bid is unsuccessful, reflect it.
        //Get the total number of seats available for this specific course-section pair
        $sectionObj = $sectionDAO->retrieveSectionByCourse($course,$section);
        $seatsAvailable = $sectionObj->getVacancy();
        $sectionSize = $sectionObj->getSize();
        //Get the total number of bids for the same specific course-section pair, which is also sorted in descending order
        $biddedCourses = $bidDAO->retrieveStudentBidsByCourseAndSectionOrderDesc($course,$section);

        $biddedCoursesWithEnrolled = $bidDAO->retrieveStudentBidsWithEnrolled($course, $section);

        $enrolledStudents = $sectionStudentDAO->retrieveByCourseSection($course, $section);
        $noOfEnrolledStudents = sizeof($enrolledStudents);
        $numberOfBids = sizeof($biddedCourses);
        $currentMinBid = $sectionObj->getMinbid();

        // $seatsAvailable = $sectionSize - $noOfEnrolledStudents;
        
       
        if ($seatsAvailable == 0){
            $nthBid = $biddedCoursesWithEnrolled[$sectionSize - 1]; 
            $sectionDAO -> updateMinBid($course,$section, $nthBid->getAmount() + 1);
            if (sizeof($biddedCoursesWithEnrolled) <= $sectionSize)
                $status = 'success';
            elseif ($amount < $nthBid->getAmount() + 1)
                $status = "fail";
            else
                $status = "success";
        }
        else {
            if (sizeof($biddedCoursesWithEnrolled) <= $sectionSize) { 
                if ($amount >= $currentMinBid)
                    $status = "success";
                if (sizeof($biddedCoursesWithEnrolled) == $sectionSize){  
                    $nthBid = $biddedCoursesWithEnrolled[$sectionSize - 1];
                    if ($nthBid->getAmount() >= $currentMinBid){
                        $sectionDAO -> updateMinBid($course,$section, $nthBid->getAmount() + 1);
                    }
                }
            } 
            else {
                $nthBid = $biddedCoursesWithEnrolled[$sectionSize - 1];  
                $multipleSimilarMinBids = False;
                $nthPlusOneBid = $biddedCoursesWithEnrolled[$sectionSize];
                if ($nthBid->getAmount() == $nthPlusOneBid->getAmount())
                    $multipleSimilarMinBids = True;
                if ($nthBid->getAmount() >= $currentMinBid)
                    $sectionDAO -> updateMinBid($course,$section, $nthBid->getAmount() + 1);
                if ($multipleSimilarMinBids)
                    $status = "fail";
                else
                    $status = "success";
            }
        }
        
        $bidDAO->updateStatus($userid,$course,$section,$status);    
        
    
        //New
        if ($convertsection == true && $status == "success") {
            $sectionStudentDAO->add($userid, $course, $section, $amount);
            // $student = $studentDAO->retrieveStudent($userid);
            // $edollar = $student->getEdollar();
            // $studentDAO->updateEDollar($userid,$edollar - $amount);   #05/11/2019: now e$ deduction is done the monent user place a bid
        }
    }
}



?>