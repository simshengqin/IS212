<?php
  require_once 'include/common.php';
  require_once 'include/protect.php';

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

    var_dump($allStudents);
    #access each key & value pair 
    for ($i=0; $i<$sectionsize;$i++){
        $student_data = $allStudents[$i];
        $id = $student_data->getUserid();
        $amount = $student_data->getAmount();
        $section = $student_data->getSection();
        $course=$student_data->getCode();
        $sectionStudent->add($id,$course,$section,$amount);
    }
}

header("Location: admin.php");
return;


?>