<?php 
require_once '../include/common.php';
require_once '../include/protect_json.php';


$studentDAO = new StudentDAO();
$request = $_REQUEST['r'];
$data = json_decode($request, true);
$userid = $data['userid'];

$student = $studentDAO->retrieveStudent($userid);
if (empty($student)){
    $result = [
        'status' => 'error',
        'message' => ['invalid userid']
    ];
}

else {
    $result = [
        'status' => 'success',
        'userid' => $student->getUserid(),
        'password' => $student->getPassword(),
        'name' => $student->getName(),
        'school' => $student->getSchool(),
        'edollar' => (float) $student->getEdollar()
    ];
}

header('Content-Type: application/json');
echo json_encode($result, JSON_PRETTY_PRINT | JSON_PRESERVE_ZERO_FRACTION);

?>
