<?php


/**
 * Returns the ranking of the bids according to course and section.
 *
 * @param string      $userid    Userid of user making the bid
 * @param string      $course    Course that user is bidding for
 * @param string      $section   Section that user is bidding for
 *
 * @return int        $pos       The ranking of the bid (starts from 1)
 * 
 */
function findPosition($userid, $course, $section){
    $bidDAO = new BidDAO();
    $sectionStudentDAO = new SectionStudentDAO();
    $studentDAO = new StudentDAO();
    $pos = 1; 
    $biddedCourses = $bidDAO->retrieveStudentBidsByCourseAndSectionOrderDesc($course,$section);
    foreach ($biddedCourses as $bids){
        if ($bids->getUserid() != $userid)
            $pos++;
        else
            return $pos;
    }
}

/**
 * 
 * Does Round Two logic (updating of bids in real-time)
 * Clearing of Round Two bids (Successful bids will be placed in section-student after admin closes Round 2)
 *
 * @param bool        $convertsection    Will pass in True after admin closes round 2 
 * 
 * @return null
 *
 */
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
    $tempConvert = [];
    $refundList = [];
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
        $sectionSize = $sectionObj->getSize();
        //Get the total number of bids for the same specific course-section pair, which is also sorted in descending order
        $biddedCourses = $bidDAO->retrieveStudentBidsByCourseAndSectionOrderDesc($course,$section);
        $biddedCoursesWithEnrolled = $bidDAO->retrieveStudentBidsWithEnrolled($course, $section);
        $enrolledStudents = $sectionStudentDAO->retrieveByCourseSection($course, $section);
        $noOfEnrolledStudents = sizeof($enrolledStudents);
        $numberOfBids = sizeof($biddedCourses);
        $currentMinBid = $sectionObj->getMinbid();
        $seatsAvailable = $sectionSize - $noOfEnrolledStudents;
        $multipleSimilarMinBids = False;
        if ($seatsAvailable == 0){
            $status = 'fail';
        }
        // If there are still slots available  
        else {    
            if ($numberOfBids > $seatsAvailable){
                // check for multiple similar min bids
                $nthBid = $biddedCourses[$seatsAvailable - 1];
                $nthPlusOneBid = $biddedCourses[$seatsAvailable];
                if ($nthBid->getAmount() == $nthPlusOneBid->getAmount())
                    $multipleSimilarMinBids = True;
                
                // check if there's a need to update minbid
                if ($nthBid->getAmount() >= $currentMinBid)
                    $sectionDAO->updateMinBid($course, $section, $nthBid->getAmount() + 1);
                
                // condition to clear bids
                if ($amount >= $currentMinBid)
                    $status = "success";  
                elseif (findPosition($userid, $course, $section) > ($seatsAvailable))
                    $status = 'fail';
                else
                    $status = 'success';
            }
            elseif ($numberOfBids == $seatsAvailable){
                $status = 'success';
                $nthBid = $biddedCourses[$numberOfBids - 1];
                if ($nthBid->getAmount() >= $currentMinBid)
                    $sectionDAO->updateMinBid($course, $section, $nthBid->getAmount() + 1);
            }
            else{
                $status = 'success';
            }
        }
        $bidDAO->updateStatus($userid,$course,$section,$status);    
    
        if ($convertsection) {
            if ($status == 'success')
                $tempConvert[] = [$userid, $course, $section, $amount];   # As seatsAvailable will be calculated and differ through each iteration,
                                                                          # We need to store this in temp list first before adding in into sectionStudent
            elseif ($status == 'fail')
                $refundList[] = [$userid, $course, $section, $amount];
        }
    }                                                                   
    if ($convertsection){
        foreach ($tempConvert as $convert){
            $sectionStudentDAO->add($convert[0], $convert[1], $convert[2], $convert[3]);
        }
        foreach ($refundList as $refund){
            $student = $studentDAO->retrieveStudent($refund[0]);
            $studentDAO->updateEdollar($refund[0], $student->getEdollar() + $refund[3]);
        }
    }
    
}



?>