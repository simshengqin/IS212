<?php
require_once '../include/common.php';
require_once '../include/protect_json.php';

if (!empty($result)){
    header('Content-Type: application/json');
    echo json_encode($result, JSON_PRETTY_PRINT);
}
else
    doStart();


function doStart() {

$bidStatusDAO = new BidStatusDAO();
$result = [
    "status" => "",
    "message" => []
];
$bidStatus = $bidStatusDAO->getBidStatus();
if ($bidStatus->getRound() == '2' && $bidStatus->getStatus() == 'closed'){
    $result["status"] = 'error';
    $result["message"][] = "round {$bidStatus->getRound()} ended";

    header('Content-Type: application/json');
    echo  json_encode($result, JSON_PRETTY_PRINT);
}
elseif ($bidStatus->getStatus() == 'closed') {
    $result["status"] = 'success';
    $result["message"] = $bidStatus->getRound() + 1;
    $bidStatusDAO->updateBidStatus($bidStatus->getRound() + 1, 'open');

    header('Content-Type: application/json');
    echo json_encode($result, JSON_PRETTY_PRINT);
}
elseif ($bidStatus->getStatus() == 'open') {
    $result["status"] = 'success';
    $result["message"] = $bidStatus->getRound();
    
    header('Content-Type: application/json');
    echo json_encode($result, JSON_PRETTY_PRINT);
}
}

?>