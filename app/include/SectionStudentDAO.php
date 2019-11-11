<?php

class SectionStudentDAO {

    public function add($userid, $course, $section, $amount) {
        $sql = 'INSERT INTO section_student (userid, course, section, amount) 
                    VALUES (:userid, :course, :section, :amount)';
        
        $connMgr = new ConnectionManager();       
        $conn = $connMgr->getConnection();
         
        $stmt = $conn->prepare($sql); 
    
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
        $stmt->bindParam(':amount', $amount, PDO::PARAM_STR);
        
        $isAddOK = False;
        if ($stmt->execute()) {
            $isAddOK = True;
        }

        return $isAddOK;
    }


    public function retrieveAll(){
        $sql = 'SELECT * from section_student';

        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        $result = [];

        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = new SectionStudent($row['userid'], $row['course'], $row['section'], $row['amount']);
        }
        return $result;
    }

    public function removeAll(){
        $sql = 'TRUNCATE TABLE section_student';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        
        $stmt->execute();
        $count = $stmt->rowCount();
    }

    public function retrieveByID($userid){
        $sql = 'SELECT * from section_student where userid=:userid';
        
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        $result = [];

        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = new SectionStudent($row['userid'], $row['course'], $row['section'], $row['amount']);
        }
        return $result;
    }

    public function retrieveByCourseSection($course, $section){
        $sql = 'SELECT * from section_student where course=:course AND section=:section';
        
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        $result = [];

        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = new SectionStudent($row['userid'], $row['course'], $row['section'], $row['amount']);
        }
        return $result;
    }

    public function retrieveByCourseUserID($course, $userid){
        $sql = 'SELECT * from section_student where course=:course AND userid=:userid';
        
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        $result = [];

        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = new SectionStudent($row['userid'], $row['course'], $row['section'], $row['amount']);
        }
        return $result;
    }

    public function retrieveByCourseSectionUser($course, $section, $userid){
        $sql = 'SELECT * from section_student where course=:course AND section=:section AND userid=:userid';
        
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        $result = "";

        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Bid class here for round 1 comparison
            $result = new Bid($row['userid'], $row['amount'] , $row['course'], $row['section']);
        }
        return $result;
    }

    public function removeByID($userid,$course){
        $sql = 'DELETE from section_student where userid=:userid and course = :course';
        
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        return $stmt->execute();
    }

    public function removeByIDCourseSection($userid,$course, $section){
        $sql = 'DELETE from section_student where userid=:userid and course = :course and section = :section';
        
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':userid', $userid, PDO::PARAM_STR);
        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        return $stmt->execute();
    }

    public function retrieveStudentEnrolledWithInfo($userid){
        $sql = "SELECT sc.userid, sc.amount, sc.course, sc.section, s.day, s.start, s.end, `exam date`, `exam start`, `exam end`
                    FROM section_student sc INNER JOIN section s INNER JOIN course c 
                        WHERE sc.section = s.section AND sc.course = s.course AND s.course = c.course AND sc.userid = :userid";
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
                "code" => $row['course'],
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
}








?>