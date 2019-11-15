<?php

class StudentDAO {

    public function add($userid, $password, $name, $school, $edollar) {
        $sql = 'INSERT INTO student (userid, password, name, school, edollar) 
                    VALUES (:userid, :password, :name, :school, :edollar)';
        
        $connMgr = new ConnectionManager();       
        $conn = $connMgr->getConnection();
         
        $stmt = $conn->prepare($sql); 

        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->bindParam(':password', $password, PDO::PARAM_STR);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':school', $school, PDO::PARAM_STR);
        $stmt->bindParam(':edollar', $edollar, PDO::PARAM_STR);

        $isAddOK = False;
        if ($stmt->execute()) {
            $isAddOK = True;
        }

        return $isAddOK;
    }


    public function retrieveAll(){
        $sql = 'select * from student';

        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        $result = [];

        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = new Student($row['userid'], $row['password'],$row['name'], $row['school'], $row['edollar']);
        }
        return $result;
    }

    public function retrieveStudent($userid){
        $sql = "SELECT * FROM student WHERE userid = :userid";
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();
        $result = "";

        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result = new Student($row['userid'], $row['password'],$row['name'], $row['school'], $row['edollar']);
        }
        return $result;
    }


    public function removeAll(){
        $sql = 'TRUNCATE TABLE student';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        
        $stmt->execute();
        $count = $stmt->rowCount();
    }


    public function updateEDollar($userid,$edollar){
        $sql = "UPDATE student SET edollar =:edollar WHERE userid =:userid";
        $connMgr = new ConnectionManager();    
        $conn = $connMgr->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->bindParam(':edollar', $edollar, PDO::PARAM_STR);
        $stmt->execute();
    }

    
}






?>