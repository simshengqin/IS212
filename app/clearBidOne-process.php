<?php
  require_once 'include/common.php';
  require_once 'include/protect.php';

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
    $section = $sectionDAO->retrieveSectionByCourse($value['code'],$value['section']);
    $sectionsize = $section->getSize();

    // var_dump($section);
    // var_dump($sectionsize);

    $sectionStudent = new sectionStudentDAO();
  
    # access each key & value pair 
    for ($i=0; $i<$sectionsize;$i++){
      $student_data = $allStudents[$i];
      $userid = $student_data->getUserid();
      $sectionStudentData = $sectionStudent->retrieveByCourseSectionUser($value['code'], $value['section'], $userid);

      $amount = $student_data->getAmount();
      $section = $student_data->getSection();
      $course=$student_data->getCode();
      $bidDAO->updateStatus($userid,$course,$section,"success");
      // To prevent duplicates 
      if ($student_data != $sectionStudentData){
        $sectionStudent->add($userid,$course,$section,$amount);
      }
    }

    for ($j = $sectionsize; $j < sizeof($allStudents); $j++){
      $student_data = $allStudents[$j];
      $userid = $student_data->getUserid();
      $section = $student_data->getSection();
      $course=$student_data->getCode();
      $bidDAO->updateStatus($userid,$course,$section,"fail");
    }
}

}


?>