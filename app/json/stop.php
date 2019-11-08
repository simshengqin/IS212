<?php
require_once '../include/common.php';
require_once '../clearBidOne-process.php';
require_once '../clearBidTwo-process.php';
require_once '../include/protect_json.php';

####################
## Initialization ##
####################
$bidStatusDAO = new BidStatusDAO();
$bidRoundStatus = $bidStatusDAO->getBidStatus();
$result = [
    "status" => "",
    "message" => []
];


################
## Stop Round ##
################
/*
    This web service will allow an administrator to stop and cease all bidding for the 
    current bidding round. This does not start the next bidding round automatically. 
    Students who have placed successful bids will be enrolled into the respective sections.
*/
$result = [];
$errors = [];

//  The service will returns a success status if there is an active bidding round currently.
if ($bidRoundStatus->getStatus() == 'open'){
    if ($bidRoundStatus->getRound() == '1'){
        doRoundOne(); 
        $bidStatusDAO->updateBidStatus(1, 'closed');
    }
    elseif ($bidRoundStatus->getRound() == '2'){
        doRoundTwo(True); 
        $bidStatusDAO->updateBidStatus('2', 'cleared');
        $bidStatusDAO->updateBidStatus(2, 'closed');
    }
}
// If round has already ended
else 
    $errors[] = "round already ended";

if (empty($errors)){
    $result = [
        'status' => 'success',
    ];
}
else {
    $result = [
        'status' => 'error',
        'message' => $errors
    ];
}

header('Content-Type: application/json');
echo json_encode($result, JSON_PRETTY_PRINT);



?>