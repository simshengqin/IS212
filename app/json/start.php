<?php
require_once '../include/common.php';

$bidStatusDAO = new BidStatusDAO();
$output = [
    "status" => "",
    "message" => []
];
$bidStatus = $bidStatusDAO->getBidStatus();
if ($bidStatus->getRound() == '2' && $bidStatus->getStatus() == 'closed'){
    $output["status"] = 'error';
    $output["message"][] = "round {$bidStatus->getRound()} ended";

    header('Content-Type: application/json');
    echo  json_encode($output, JSON_PRETTY_PRINT);
}
elseif ($bidStatus->getStatus() == 'closed') {
    $output["status"] = 'success';
    $output["message"] = $bidStatus->getRound() + 1;
    $bidStatusDAO->updateBidStatus($bidStatus->getRound() + 1, 'open');

    header('Content-Type: application/json');
    echo json_encode($output, JSON_PRETTY_PRINT);
}
elseif ($bidStatus->getStatus() == 'open') {
    $output["status"] = 'error';
    $output["message"][] = "round {$bidStatus->getRound()} started";
    
    header('Content-Type: application/json');
    echo json_encode($output, JSON_PRETTY_PRINT);
}


?>