<?php

class CourseDAO {

    public function add($course, $school, $title, $description, $examdate, $examstart, $examend) {
        $sql = 'INSERT INTO course (course, school, title, description, `exam date`, `exam start`, `exam end`) 
                    VALUES (:course, :school, :title, :description, :examdate, :examstart, :examend)';
        
        $connMgr = new ConnectionManager();       
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->bindParam(':school', $school, PDO::PARAM_STR);
        $stmt->bindParam(':title', $title, PDO::PARAM_STR);
        $stmt->bindParam(':description', $description, PDO::PARAM_STR);
        $stmt->bindParam(':examdate', $examdate, PDO::PARAM_STR);
        $stmt->bindParam(':examstart', $examstart, PDO::PARAM_STR);
        $stmt->bindParam(':examend', $examend, PDO::PARAM_STR);
        
        
        $isAddOK = False;
        if ($stmt->execute()) {
            $isAddOK = True;
        }

        return $isAddOK;
    }


    public function retrieveAll(){
        $sql = 'select * from course';

        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        $result = [];

        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = new Course($row['course'], $row['school'],$row['title'], $row['description'], 
                            $row['exam date'], $row['exam start'], $row['exam end']);
        }
        return $result;
    }

    public function removeAll(){
        $sql = 'TRUNCATE TABLE course';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        
        $stmt->execute();
        $count = $stmt->rowCount();
    }



}






?>