<?php

class BidDAO {

    public function add($userid, $amount, $code, $section){
        $sql = 'INSERT INTO bid (userid, amount, code, section)
                    VALUES (:userid, :amount, :code, :section)';

        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->bindParam(':amount', $amount, PDO::PARAM_STR);
        $stmt->bindParam(':code', $code, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
        
        $isAddOK = False;
        if ($stmt->execute()) {
            $isAddOK = True;
        }

        return $isAddOK;
    }


    public function retrieveAll(){
        $sql = 'select * from bid';

        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();
        
        $result = [];

        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = new Bid($row['userid'], $row['amount'],$row['code'], $row['section']);
        }
        return $result;
    }

    public function retrieveStudentBids($userid){
    $sql = 'select userid, amount, code, section from bid where userid=:userid';

    $connMgr = new ConnectionManager();
    $conn = $connMgr->getConnection();

    $stmt = $conn->prepare($sql);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
    $stmt->execute();

    $result = array();

    while($row = $stmt->fetch(PDO::FETCH_ASSOC))
    {
        $result[] = new Bid($row['userid'], $row['amount'],$row['code'], $row['section']);
    }

    return $result;
    }

    public function retrieveStudentBidsWithInfo($userid){
        $sql = "SELECT b.userid, b.amount, b.code, b.section, s.day, s.start, s.end, c.examdate, c.examstart, c.examend 
                    FROM bid b INNER JOIN section s INNER JOIN course c 
                        WHERE b.section = s.section AND b.code = s.course AND s.course = c.course AND b.userid = :userid";
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->execute();

        $result = [];   

        while($row = $stmt->fetch())
        {
            $result[] = [
                "userid" => $row['userid'], 
                "amount" => $row['amount'],
                "code" => $row['code'],
                "section" => $row['section'],
                "day" => $row['day'],
                "start" => $row['start'],
                "end" => $row['end'],
                "examdate" => $row['examdate'],
                "examstart" => $row['examstart'],
                "examend" => $row['examend']
            ];
        }
        return $result;
    }

    

    public function removeAll(){
        $sql = 'TRUNCATE TABLE bid';

        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);

        $stmt->execute();
        $count = $stmt->rowCount();
    }

    public function removeBid($userid, $code){
        $sql = 'DELETE FROM bid WHERE userid =:userid AND code =:code';

        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);

        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->bindParam(':code', $code,PDO::PARAM_STR);
        $stmt->execute();
    }



}





?>
