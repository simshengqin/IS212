<?php

class BidStatusDAO {
    function updateBidStatus($round,$status){
            $connMgr = new ConnectionManager();
            $pdo = $connMgr->getConnection();
            $sql = "UPDATE bid_status set round = :round, status = :status where id = 1";
            $stmt = $pdo->prepare($sql);
            $stmt-> bindParam(':round',$round, PDO::PARAM_STR);
            $stmt-> bindParam(':status',$status, PDO::PARAM_STR);
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $status = $stmt->execute(); //boolean
        }

    function getBidStatus(){
        $connMgr = new ConnectionManager();
        $pdo = $connMgr->getConnection();
        $sql = "SELECT * FROM bid_status";
        $stmt = $pdo->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute(); //boolean
        $result = '';
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result = new BidStatus($row['round'], $row['status']);
        }
        return $result;
    }
}

?>