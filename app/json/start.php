<?php
require_once '../include/common.php';
require_once '../include/protect_json.php';


#################
## Start Round ##
#################
/*
    This web service will allow an administrator to start a bidding round and enable users to place bids.
*/
    $bidStatusDAO = new BidStatusDAO();
    $bidDAO = new BidDAO();
    $result = [];
    $errors = [];
    $bidStatus = $bidStatusDAO->getBidStatus();
    if ($bidStatus->getRound() == '2' && $bidStatus->getStatus() == 'closed'){
        $result["status"] = 'error';
        $errors[] = "round {$bidStatus->getRound()} ended";
        $result['message'] = $errors;
        header('Content-Type: application/json');
        echo json_encode($result, JSON_PRETTY_PRINT);
    }
    elseif ($bidStatus->getStatus() == 'closed') {
        $result["status"] = 'success';
        $result["round"] = $bidStatus->getRound() + 1;
        $bidStatusDAO->updateBidStatus($bidStatus->getRound() + 1, 'open');
        $bidDAO->removeAll();
        header('Content-Type: application/json');
        echo json_encode($result, JSON_PRETTY_PRINT);
    }
    elseif ($bidStatus->getStatus() == 'open') {
        $result["status"] = 'success';
        $result["round"] = $bidStatus->getRound();
        
        header('Content-Type: application/json');
        echo json_encode($result, JSON_PRETTY_PRINT);
    }


?>