<?php

class CourseCompleted {
    private $userid;
    private $course;

    public function __construct($userid, $course) {
        $this->userid = $userid;
        $this->course = $course;
    }

    public function getUserid() {
        return $this->userid;
    }
    public function getCourse() {
        return $this->course;
    }

}



?>