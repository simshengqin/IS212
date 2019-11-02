<?php

class SectionDAO {

    public function add($course, $section, $day, $start, $end, $instructor, $venue, $size, $vacancy) {
        $sql = 'INSERT INTO section (course, section, day, start, end, instructor, venue, size, vacancy) 
                    VALUES (:course, :section, :day, :start, :end, :instructor, :venue, :size, :vacancy)';
        
        $connMgr = new ConnectionManager();       
        $conn = $connMgr->getConnection();
         
        $stmt = $conn->prepare($sql); 

        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
        $stmt->bindParam(':day', $day, PDO::PARAM_STR);
        $stmt->bindParam(':start', $start, PDO::PARAM_STR);
        $stmt->bindParam(':end', $end, PDO::PARAM_STR);
        $stmt->bindParam(':instructor', $instructor, PDO::PARAM_STR);
        $stmt->bindParam(':venue', $venue, PDO::PARAM_STR);
        $stmt->bindParam(':size', $size, PDO::PARAM_STR);
        $stmt->bindParam(':vacancy', $vacancy, PDO::PARAM_STR);
        
        $isAddOK = False;
        if ($stmt->execute()) {
            $isAddOK = True;
        }

        return $isAddOK;
    }


    public function retrieveAll(){
        $sql = 'select * from section';

        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        $result = [];

        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = new Section($row['course'], $row['section'],$row['day'], $row['start'], 
                            $row['end'], $row['instructor'], $row['venue'], $row['size'], $row['minbid'], $row['vacancy']);
        }
        return $result;
    }

    public function retrieveSection($section){
        #$sql = 'select * from section';
        $sql = "SELECT * FROM section WHERE section = :section";
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        $result = [];

        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = new Section($row['course'], $row['section'],$row['day'], $row['start'], 
                            $row['end'], $row['instructor'], $row['venue'], $row['size'], $row['minbid'], $row['vacancy']);
        }
        return $result;
    }
    
    public function retrieveSectionByCourse($course,$section){
        #$sql = 'select * from section';
        $sql = "SELECT * FROM section WHERE course = :course AND section = :section";
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        $result = [];

        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result = new Section($row['course'], $row['section'],$row['day'], $row['start'], 
                            $row['end'], $row['instructor'], $row['venue'], $row['size'], $row['minbid'], $row['vacancy']);
        }
        return $result;
    }
    public function retrieveSectionByFilter($course="", $day="", $time=""){
        $timeArr = explode("-",$time);
        if (empty($course) && empty($day) && empty($time))
            $sql = "SELECT * FROM section";
        else
            $sql = "SELECT * FROM section WHERE ";
        $count = 0;
        if ($course != "") {
            $sql .= "course = :course";
            $count++;
        }
        if ($day != "" ) {
            if ($count == 0)
                $sql .= "day = :day";
            else
                $sql .= " and day = :day";
            $count++;
        }
        if ($time != "") {
            if ($count == 0)
                $sql .= " start = :start and end = :end";
            else
                $sql .= " and start = :start and end = :end";
        }
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();
        $stmt = $conn->prepare($sql);
        if ($course != "")
            $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        if ($day != "") 
            $stmt->bindParam(':day', $day, PDO::PARAM_STR);
        if ($time != "") {
            $stmt->bindParam(':start', $timeArr[0], PDO::PARAM_STR);
            $stmt->bindParam(':end', $timeArr[1], PDO::PARAM_STR);
        }
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();
        $result = [];

        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = new Section($row['course'], $row['section'],$row['day'], $row['start'], 
                            $row['end'], $row['instructor'], $row['venue'], $row['size'], $row['minbid'], $row['vacancy']);
        }
        return $result;
    }

    public function retrieveMinBid($course, $section){
        #$sql = 'select * from section';
        $sql = "SELECT * FROM section WHERE course =:course AND section = :section";
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result = $row['minbid'];
        }
        return $result;
    }

    public function retrieveVacancy($course, $section){
        #$sql = 'select * from section';
        $sql = "SELECT * FROM section WHERE course =:course AND section = :section";
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute();

        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result = $row['vacancy'];
        }
        return $result;
    }
    public function updateMinBid($course,$section,$minbid){
        $sql = "UPDATE section SET minbid =:minbid WHERE course =:course AND section =:section";
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
        $stmt->bindParam(':minbid', $minbid, PDO::PARAM_STR);
        $stmt->execute();
    }

    public function updateVacancy($course,$section,$vacancy){
        $sql = "UPDATE section SET vacancy =:vacancy WHERE course =:course AND section =:section";
        $connMgr = new ConnectionManager();      
        $conn = $connMgr->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':course', $course, PDO::PARAM_STR);
        $stmt->bindParam(':section', $section, PDO::PARAM_STR);
        $stmt->bindParam(':vacancy', $vacancy, PDO::PARAM_STR);
        $stmt->execute();
    }
    public function removeAll(){
        $sql = 'TRUNCATE TABLE section';
        
        $connMgr = new ConnectionManager();
        $conn = $connMgr->getConnection();
        
        $stmt = $conn->prepare($sql);
        
        $stmt->execute();
        $count = $stmt->rowCount();
    }



}






?>