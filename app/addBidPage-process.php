<?php
  require_once 'include/common.php';
  require_once 'include/protect.php';
  
?>

<?php

$bidDAO = new BidDAO();

$userid = "ben.ng.2009";

foreach($_POST as $key => $value)
{
    $codeSectionList = [];
    $code ="";
    $section ="";
    $amount = "";

    if($key!= "Add_Bid") 
    {
        if ($value != "")
        {
            //echo "Key: $key Value: $value <br>";
            //echo($key);
            //list($code, $section) = explode(" ", $key);
            $codeSectionList = explode("_", $key);
            $code = $codeSectionList[0];
            $section = $codeSectionList[1];
            $amount = intval($value);
            echo "$code $section $amount <br>";
            $bidDAO->add($userid, $amount, $code, $section);
        }
    }
}

/*
$sectionDAO = new SectionDAO();
$allSections = $sectionDAO -> retrieveAll();
if (!isset($_GET["course"]) && !isset($_GET["day"]) && !isset($_GET["start"]) && !isset($_GET["end"]))
    $sections = $sectionDAO -> retrieveAll();
else {
    $day = "";
    $start = "";
    $end = "";
    if (isset($_GET["day"]))
      $day = $_GET["day"];
    if (isset($_GET["time"]))
      $time = $_GET["time"];
    $sections = $sectionDAO -> retrieveSectionByFilter($_GET["course"], $day, $time);
}

$sectionCheckArray = [];
foreach($sections as $section)
{
    $sectionCheckArray[] = $section->getCourse() . $section->getSection();
}

$bidArray = [];

foreach($sectionCheckArray as $section)
{
    if(isset($_POST["$section"]))
    {
        $bidArray[] = $section;
    }
}
*/

?>