<?php

class CourseCompletedDAO {

    public function add($userid, $code) {
        $sql = 'INSERT INTO course_completed (userid, code) 
                    VALUES (:userid, :code)';
        
        $connMgr = new ConnectionManager();       
        $conn = $connMgr->getConnection();
         
        $stmt = $conn->prepare($sql); 

        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->bindParam(':code', $code, PDO::PARAM_STR);


        $isAddOK = False;
        if ($stmt->execute()) {
            $isAddOK = True;
        }

        return $isAddOK;
    }


    public function retrieveAll(){
        $sql = 'select * from course_completed';

        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        $result = [];

        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = new CourseCompleted($row['userid'], $row['code']);
        }
        return $result;
    }

    public function retrieveCourseCompletedByUserId($userid)
    {
        $sql = 'select userid, code from course_completed where userid=:userid';

        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->execute();

        $result = array();

        while($row = $stmt->fetch(PDO::FETCH_ASSOC))
        {
            $result[] = $row['code'];
        }

        return $result;
    }

    public function removeAll(){
        $sql = 'TRUNCATE TABLE course_completed';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        
        $stmt->execute();
        $count = $stmt->rowCount();
    }



}






?>