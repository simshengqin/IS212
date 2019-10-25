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
    /*
    public function retrieveStatus($userid,$code,$section){
        #$sql = 'select * from section';
        $sql = "SELECT * FROM section WHERE userid = :userid AND code = :code AND section = :section";
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->bindParam(':code', $code, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        return $row['status'];
    }  
    */
    public function updateStatus($userid,$course,$section,$status){
        $sql = "UPDATE bid SET status =:status WHERE userid =:userid AND code =:course AND section =:section";
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->execute();
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

    public function retrieveStudentBidsByCourseAndSectionOrderDesc($code,$section){
        $sql = "SELECT userid, amount, code, section FROM bid WHERE code=:code AND section=:section ORDER BY amount DESC";
    
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
    
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':code', $code, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
        $stmt->execute();
    
        $result = array();
    
        while($row = $stmt->fetch(PDO::FETCH_ASSOC))
        {
            $result[] = new Bid($row['userid'], $row['amount'],$row['code'], $row['section']);
        }
    
        return $result;
        }

    public function retrieveStudentBidsWithInfo($userid){
        $sql = "SELECT b.userid, b.amount, b.code, b.section, s.day, s.start, s.end, `exam date`, `exam start`, `exam end`
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
                "exam date" => $row['exam date'],
                "exam start" => $row['exam start'],
                "exam end" => $row['exam end']
            ];
        }
        return $result;
    }

    public function retrieveStudentBidsWithInfoByCourseSection($userid, $course, $section){
        $sql = "SELECT b.userid, b.amount, b.code, b.section, s.day, s.start, s.end, `exam date`, `exam start`, `exam end`
                    FROM bid b INNER JOIN section s INNER JOIN course c 
                        WHERE b.section = s.section AND b.code = s.course AND s.course = c.course AND b.userid = :userid 
                                                    AND b.section = :section AND s.course = :course";
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
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
                "exam date" => $row['exam date'],
                "exam start" => $row['exam start'],
                "exam end" => $row['exam end']
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

    public function removeBidByUseridAndCode($userid, $code){
        $sql = 'DELETE FROM bid WHERE userid =:userid AND code =:code';

        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);

        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->bindParam(':code', $code,PDO::PARAM_STR);
        $stmt->execute();
    }

    public function removeBid($userid, $amount, $code, $section){
        $sql = 'DELETE FROM bid WHERE userid =:userid AND amount =:amount AND code =:code AND section =:section';

        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);

        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt-> bindParam(':amount', $amount,PDO::PARAM_STR);
        $stmt-> bindParam(':code', $code,PDO::PARAM_STR);
        $stmt-> bindParam(':section', $section,PDO::PARAM_STR);
        $stmt->execute();
    }

    public function getUniqueCodeAndSection(){
        $sql = "SELECT distinct code, section from bid";
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        $result = [];   

        while($row = $stmt->fetch())
        {
            $result[] = [
                "code" => $row['code'],
                "section" => $row['section'],
            ];
        }
        return $result;
    }


}








?>
