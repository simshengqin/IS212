<?php

function doRoundTwo(){
    // Round 2 clearing takes place here. Only takes place once, will convert status from closed to cleared
    $bidStatusDAO = new BidStatusDAO();
    $bidStatus = $bidStatusDAO->getBidStatus();
    $bidStatusDAO->updateBidStatus('2', 'cleared');
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
        $seatsAvailable = $sectionObj->getSize();
        //Get the total number of bids for the same specific course-section pair, which is also sorted in descending order
        $biddedCourses = $bidDAO->retrieveStudentBidsByCourseAndSectionOrderDesc($course,$section);

        $bidCount = sizeof($biddedCourses);

        // N is the seatsAvailable
        if ($seatsAvailable > $bidCount) {
            $minBid = 10;   
            $status = "success";             
        }
        else {
        //Min bid amount is equal to the Nth bid amount + 1
        $nthBid = $biddedCourses[$seatsAvailable - 1];
        $multipleSimilarMinBids = False;
        if ($seatsAvailable < $bidCount) {
            $nthPlusOneBid = $biddedCourses[$seatsAvailable];
            //If there are more then one course with the same min bid amount, reject all of them
            if ($nthBid->getAmount() == $nthPlusOneBid->getAmount()) {
                $multipleSimilarMinBids = True;
            }
        }
        $oldMinBid = $sectionDAO -> retrieveMinBid($course, $section);
        if ( ($nthBid->getAmount() + 1) > $oldMinBid) {
            $minBid = $nthBid->getAmount() + 1;
            $sectionDAO -> updateMinBid($course,$section,$minBid);
        }
        else {
            $minBid = $oldMinBid;
        }
        //2 scenarios for the bid to be considered unsuccessful
        //if bid amount is equal to minBid and it is not the nthBid, it means there are multiple courses with the same minbid. No space left=>Reject
        //if bid amount is smaller than minBid => Automatically rejected
        if ( ($amount == ($minBid - 1) && $multipleSimilarMinBids == True) || $amount < ($minBid - 1)){
            $status = "fail";                  
        }
        else {
            $status = "success";
            
        }
        }
        $bidDAO->updateStatus($userid,$course,$section,$status);
        //New
        if ($status == "success") {
            $sectionStudentDAO->add($userid, $course, $section, $amount);
            $student = $studentDAO->retrieveStudent($userid);
            $edollar = $student->getEdollar();
            $studentDAO->updateEDollar($userid,$edollar - $amount);          
        }
    }
}



?>