<?php
  require_once 'include/common.php';

function doRoundOne() {
  $bidDAO = new BidDAO();
  $allCodeandSection = $bidDAO->getUniqueCodeAndSection();
#sort code and section
//   var_dump($allCodeandSection);


  foreach($allCodeandSection as $key => $value)
  {
    // var_dump($value['code']);
    // var_dump($value['section']);

    #Sort according to bid in descending order via retrieveStudentBidsByCourseAndSectionOrderDesc()
    $allStudents = $bidDAO->retrieveStudentBidsByCourseAndSectionOrderDesc($value['code'], $value['section']);
    // var_dump($allStudents);

    #check class for number of slots (say number of slots = x)
    $sectionDAO = new SectionDAO();
    $sectionInfo = $sectionDAO->retrieveSectionByCourse($value['code'],$value['section']);
    $sectionsize = $sectionInfo->getSize();

    // var_dump($section);
    // var_dump($sectionsize);

    $sectionStudent = new sectionStudentDAO();
    $studentDAO = new StudentDAO();

    $multipleStudentWithClearingPrice = False;

    # get clearing price
    $clearingPrice = 0;
    if (sizeof($allStudents) >= $sectionsize){
      $clearingPrice = $allStudents[$sectionsize-1]->getAmount();
    }

    #check if there are multiple students with same clearing price
    $count = 0;
    foreach ($allStudents as $student){
      if ($clearingPrice == $student->getAmount())
        $count++;
    }
    if ($count > 1){
      $multipleStudentWithClearingPrice = True;
    }
    

    # access each key & value pair 
    for ($i=0; $i<$sectionsize;$i++){
      if (sizeof($allStudents) <= $i){
        break;
      }
      $student_data = $allStudents[$i];
      $userid = $student_data->getUserid();
      // $sectionStudentData = $sectionStudent->retrieveByCourseSectionUser($value['code'], $value['section'], $userid);
      $amount = $student_data->getAmount();
      $section = $student_data->getSection();
      $course=$student_data->getCode();
      $student = $studentDAO->retrieveStudent($userid);
      $studentEdollar = $student->getEdollar();
      // To prevent duplicates 
      if ($amount > $clearingPrice || ($amount == $clearingPrice && !$multipleStudentWithClearingPrice)){
        $sectionStudent->add($userid,$course,$section,$amount);
        $bidDAO->updateStatus($userid,$course,$section,"success");
        $sectionDAO->updateVacancy($course,$section, $sectionInfo->getVacancy()-1);
      }
      else{
        $bidDAO->updateStatus($userid,$course,$section,"fail");
        $studentDAO->updateEdollar($userid, $studentEdollar+$amount);
      }
    }

    for ($j = $sectionsize; $j < sizeof($allStudents); $j++){
      $student_data = $allStudents[$j];
      $userid = $student_data->getUserid();
      $section = $student_data->getSection();
      $course=$student_data->getCode();
      $studentEdollar = $student->getEdollar();
      $bidDAO->updateStatus($userid,$course,$section,"fail");
      $studentDAO->updateEdollar($userid, $studentEdollar+$amount);
    }
}

}


?>